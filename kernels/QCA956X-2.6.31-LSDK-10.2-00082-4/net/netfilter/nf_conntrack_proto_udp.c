/* (C) 1999-2001 Paul `Rusty' Russell
 * (C) 2002-2004 Netfilter Core Team <coreteam@netfilter.org>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2 as
 * published by the Free Software Foundation.
 */

#include <linux/types.h>
#include <linux/timer.h>
#include <linux/module.h>
#include <linux/udp.h>
#include <linux/seq_file.h>
#include <linux/skbuff.h>
#include <linux/ipv6.h>
#include <net/ip6_checksum.h>
#include <net/checksum.h>

#include <linux/netfilter.h>
#include <linux/netfilter_ipv4.h>
#include <linux/netfilter_ipv6.h>
#include <net/netfilter/nf_conntrack_l4proto.h>
#include <net/netfilter/nf_conntrack_ecache.h>
#include <net/netfilter/nf_log.h>
#include <net/netfilter/ipv4/nf_conntrack_ipv4.h>
#include <net/netfilter/ipv6/nf_conntrack_ipv6.h>

#if defined(CONFIG_NF_CONNTRACK_IPSEC_PASSTHROUGH) || defined(CONFIG_NF_CONNTRACK_IPSEC_PASSTHROUGH_MODULE)
#include <net/netfilter/nf_conntrack_ike.h>

int (*redirect_to_real_peer_hook)(struct nf_conntrack_tuple *tuple , const struct sk_buff *skb , unsigned int dataoff);
EXPORT_SYMBOL_GPL(redirect_to_real_peer_hook);
#endif //CONFIG_NF_CONNTRACK_IPSEC_PASSTHROUGH

static unsigned int nf_ct_udp_timeout __read_mostly = 30*HZ;
static unsigned int nf_ct_udp_timeout_stream __read_mostly = 180*HZ;

static unsigned int nf_ct_udp6_timeout __read_mostly = 60*HZ;
static unsigned int nf_ct_udp6_timeout_out_wkport __read_mostly = 300*HZ;

static bool udp_pkt_to_tuple(const struct sk_buff *skb,
			     unsigned int dataoff,
			     struct nf_conntrack_tuple *tuple)
{
	const struct udphdr *hp;
	struct udphdr _hdr;

	/* Actually only need first 8 bytes. */
	hp = skb_header_pointer(skb, dataoff, sizeof(_hdr), &_hdr);
	if (hp == NULL)
		return false;

	tuple->src.u.udp.port = hp->source;
	tuple->dst.u.udp.port = hp->dest;

#if defined(CONFIG_NF_CONNTRACK_IPSEC_PASSTHROUGH) || defined(CONFIG_NF_CONNTRACK_IPSEC_PASSTHROUGH_MODULE)
	if(hp->source == htons(500))
	{
		//it may be IPSEC traffic, try redirecting to real peer
		//we don't use destination port to find real peer, we use 
		//ike cookie!
		typeof(redirect_to_real_peer_hook) redirect_to_real_peer;
		redirect_to_real_peer = rcu_dereference(redirect_to_real_peer_hook);
		if(redirect_to_real_peer)
			if(redirect_to_real_peer(tuple , skb , dataoff))
			{
				//#define LOC_FMT "[%s %d] "
				//#define LOC_ARG __FILE__ , __LINE__
				//printk(LOC_FMT"src: %u dst: %u -> src: %u dst: %u\n" , 
				//       LOC_ARG , ntohs(hp->source) , ntohs(hp->dest) , ntohs(tuple->src.u.udp.port) , ntohs(tuple->dst.u.udp.port));
			}
	}
#endif //CONFIG_NF_CONNTRACK_IPSEC_PASSTHROUGH

	return true;
}

static bool udp_invert_tuple(struct nf_conntrack_tuple *tuple,
			     const struct nf_conntrack_tuple *orig)
{
	tuple->src.u.udp.port = orig->dst.u.udp.port;
	tuple->dst.u.udp.port = orig->src.u.udp.port;
	return true;
}

/* Print out the per-protocol part of the tuple. */
static int udp_print_tuple(struct seq_file *s,
			   const struct nf_conntrack_tuple *tuple)
{
	return seq_printf(s, "sport=%hu dport=%hu ",
			  ntohs(tuple->src.u.udp.port),
			  ntohs(tuple->dst.u.udp.port));
}

/* Returns verdict for packet, and may modify conntracktype */
static int udp6_packet(struct nf_conn *ct,
		      const struct sk_buff *skb,
		      unsigned int dataoff,
		      enum ip_conntrack_info ctinfo,
		      u_int8_t pf,
		      unsigned int hooknum)
{
	struct nf_conntrack_tuple *tuple;
	unsigned int sport, dport;

	tuple = &ct->tuplehash[IP_CT_DIR_ORIGINAL].tuple;

	sport = tuple->src.u.udp.port;
	dport = tuple->dst.u.udp.port;

	if (sport > 1023 && dport > 1023)
		nf_ct_refresh_acct(ct, ctinfo, skb, nf_ct_udp6_timeout_out_wkport);
	else
		nf_ct_refresh_acct(ct, ctinfo, skb, nf_ct_udp6_timeout);

	return NF_ACCEPT;
}

/* Returns verdict for packet, and may modify conntracktype */
static int udp_packet(struct nf_conn *ct,
		      const struct sk_buff *skb,
		      unsigned int dataoff,
		      enum ip_conntrack_info ctinfo,
		      u_int8_t pf,
		      unsigned int hooknum)
{
#if defined(CONFIG_NF_CONNTRACK_IPSEC_PASSTHROUGH) || defined(CONFIG_NF_CONNTRACK_IPSEC_PASSTHROUGH_MODULE)
	struct nf_conn_ike *ike_ext = (struct nf_conn_ike*)nf_ct_ext_find(ct, NF_CT_EXT_IKE);
	if(ike_ext)
	{
		//ike traffic
		if (test_bit(IPS_SEEN_REPLY_BIT, &ct->status)) 
		{
			nf_ct_refresh_acct(ct, ctinfo, skb, ike_ext->timeout);
			if (!test_and_set_bit(IPS_ASSURED_BIT, &ct->status))
				nf_conntrack_event_cache(IPCT_STATUS, ct);
		}
		else
			nf_ct_refresh_acct(ct, ctinfo, skb, ike_ext->timeout);
	}
	else
	{
		//not ike traffic
#endif //CONFIG_NF_CONNTRACK_IPSEC_PASSTHROUGH

	/* If we've seen traffic both ways, this is some kind of UDP
	   stream.  Extend timeout. */
	if (test_bit(IPS_SEEN_REPLY_BIT, &ct->status)) {
		nf_ct_refresh_acct(ct, ctinfo, skb, nf_ct_udp_timeout_stream);
		/* Also, more likely to be important, and not a probe */
		if (!test_and_set_bit(IPS_ASSURED_BIT, &ct->status))
			nf_conntrack_event_cache(IPCT_STATUS, ct);
	} else
		nf_ct_refresh_acct(ct, ctinfo, skb, nf_ct_udp_timeout);

#if defined(CONFIG_NF_CONNTRACK_IPSEC_PASSTHROUGH) || defined(CONFIG_NF_CONNTRACK_IPSEC_PASSTHROUGH_MODULE)
	}//if(ike_ext)
#endif //CONFIG_NF_CONNTRACK_IPSEC_PASSTHROUGH

	return NF_ACCEPT;
}

/* Called when a new connection for this protocol found. */
static bool udp_new(struct nf_conn *ct, const struct sk_buff *skb,
		    unsigned int dataoff)
{
	return true;
}

static int udp_error(struct net *net, struct sk_buff *skb, unsigned int dataoff,
		     enum ip_conntrack_info *ctinfo,
		     u_int8_t pf,
		     unsigned int hooknum)
{
	unsigned int udplen = skb->len - dataoff;
	const struct udphdr *hdr;
	struct udphdr _hdr;

	/* Header is too small? */
	hdr = skb_header_pointer(skb, dataoff, sizeof(_hdr), &_hdr);
	if (hdr == NULL) {
		if (LOG_INVALID(net, IPPROTO_UDP))
			nf_log_packet(pf, 0, skb, NULL, NULL, NULL,
				      "nf_ct_udp: short packet ");
		return -NF_ACCEPT;
	}

	/* Truncated/malformed packets */
	if (ntohs(hdr->len) > udplen || ntohs(hdr->len) < sizeof(*hdr)) {
		if (LOG_INVALID(net, IPPROTO_UDP))
			nf_log_packet(pf, 0, skb, NULL, NULL, NULL,
				"nf_ct_udp: truncated/malformed packet ");
		return -NF_ACCEPT;
	}

	/* Packet with no checksum */
	if (!hdr->check)
		return NF_ACCEPT;

	/* Checksum invalid? Ignore.
	 * We skip checking packets on the outgoing path
	 * because the checksum is assumed to be correct.
	 * FIXME: Source route IP option packets --RR */
	if (net->ct.sysctl_checksum && hooknum == NF_INET_PRE_ROUTING &&
	    nf_checksum(skb, hooknum, dataoff, IPPROTO_UDP, pf)) {
		if (LOG_INVALID(net, IPPROTO_UDP))
			nf_log_packet(pf, 0, skb, NULL, NULL, NULL,
				"nf_ct_udp: bad UDP checksum ");
		return -NF_ACCEPT;
	}

	return NF_ACCEPT;
}

#ifdef CONFIG_SYSCTL
static unsigned int udp_sysctl_table_users;
static struct ctl_table_header *udp_sysctl_header;
static struct ctl_table udp_sysctl_table[] = {
	{
		.procname	= "nf_conntrack_udp_timeout",
		.data		= &nf_ct_udp_timeout,
		.maxlen		= sizeof(unsigned int),
		.mode		= 0644,
		.proc_handler	= proc_dointvec_jiffies,
	},
	{
		.procname	= "nf_conntrack_udp_timeout_stream",
		.data		= &nf_ct_udp_timeout_stream,
		.maxlen		= sizeof(unsigned int),
		.mode		= 0644,
		.proc_handler	= proc_dointvec_jiffies,
	},
	{
		.procname	= "nf_conntrack_udp6_timeout",
		.data		= &nf_ct_udp6_timeout,
		.maxlen		= sizeof(unsigned int),
		.mode		= 0644,
		.proc_handler	= proc_dointvec_jiffies,
	},
	{
		.procname	= "nf_conntrack_udp6_timeout_out_wkport",
		.data		= &nf_ct_udp6_timeout_out_wkport,
		.maxlen		= sizeof(unsigned int),
		.mode		= 0644,
		.proc_handler	= proc_dointvec_jiffies,
	},
	{
		.ctl_name	= 0
	}
};
#ifdef CONFIG_NF_CONNTRACK_PROC_COMPAT
static struct ctl_table udp_compat_sysctl_table[] = {
	{
		.procname	= "ip_conntrack_udp_timeout",
		.data		= &nf_ct_udp_timeout,
		.maxlen		= sizeof(unsigned int),
		.mode		= 0644,
		.proc_handler	= proc_dointvec_jiffies,
	},
	{
		.procname	= "ip_conntrack_udp_timeout_stream",
		.data		= &nf_ct_udp_timeout_stream,
		.maxlen		= sizeof(unsigned int),
		.mode		= 0644,
		.proc_handler	= proc_dointvec_jiffies,
	},
	{
		.ctl_name	= 0
	}
};
#endif /* CONFIG_NF_CONNTRACK_PROC_COMPAT */
#endif /* CONFIG_SYSCTL */

struct nf_conntrack_l4proto nf_conntrack_l4proto_udp4 __read_mostly =
{
	.l3proto		= PF_INET,
	.l4proto		= IPPROTO_UDP,
	.name			= "udp",
	.pkt_to_tuple		= udp_pkt_to_tuple,
	.invert_tuple		= udp_invert_tuple,
	.print_tuple		= udp_print_tuple,
	.packet			= udp_packet,
	.new			= udp_new,
#ifdef CONFIG_BCM_NAT
	.error			= NULL,
#else
	.error			= udp_error,
#endif
#if defined(CONFIG_NF_CT_NETLINK) || defined(CONFIG_NF_CT_NETLINK_MODULE)
	.tuple_to_nlattr	= nf_ct_port_tuple_to_nlattr,
	.nlattr_to_tuple	= nf_ct_port_nlattr_to_tuple,
	.nlattr_tuple_size	= nf_ct_port_nlattr_tuple_size,
	.nla_policy		= nf_ct_port_nla_policy,
#endif
#ifdef CONFIG_SYSCTL
	.ctl_table_users	= &udp_sysctl_table_users,
	.ctl_table_header	= &udp_sysctl_header,
	.ctl_table		= udp_sysctl_table,
#ifdef CONFIG_NF_CONNTRACK_PROC_COMPAT
	.ctl_compat_table	= udp_compat_sysctl_table,
#endif
#endif
};
EXPORT_SYMBOL_GPL(nf_conntrack_l4proto_udp4);

struct nf_conntrack_l4proto nf_conntrack_l4proto_udp6 __read_mostly =
{
	.l3proto		= PF_INET6,
	.l4proto		= IPPROTO_UDP,
	.name			= "udp",
	.pkt_to_tuple		= udp_pkt_to_tuple,
	.invert_tuple		= udp_invert_tuple,
	.print_tuple		= udp_print_tuple,
	.packet			= udp6_packet,
	.new			= udp_new,
	.error			= udp_error,
#if defined(CONFIG_NF_CT_NETLINK) || defined(CONFIG_NF_CT_NETLINK_MODULE)
	.tuple_to_nlattr	= nf_ct_port_tuple_to_nlattr,
	.nlattr_to_tuple	= nf_ct_port_nlattr_to_tuple,
	.nlattr_tuple_size	= nf_ct_port_nlattr_tuple_size,
	.nla_policy		= nf_ct_port_nla_policy,
#endif
#ifdef CONFIG_SYSCTL
	.ctl_table_users	= &udp_sysctl_table_users,
	.ctl_table_header	= &udp_sysctl_header,
	.ctl_table		= udp_sysctl_table,
#endif
};
EXPORT_SYMBOL_GPL(nf_conntrack_l4proto_udp6);
