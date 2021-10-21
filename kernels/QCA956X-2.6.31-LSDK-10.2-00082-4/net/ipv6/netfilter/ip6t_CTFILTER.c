#include <net/netfilter/nf_conntrack.h>
#include <net/netfilter/nf_conntrack_core.h>
#include <linux/netfilter_ipv6/ip6_tables.h>
#include <linux/netfilter_ipv6/ip6t_CTFILTER.h>
#include <net/ipv6.h>
#include <linux/version.h>

MODULE_DESCRIPTION("Xtables: packet \"ctfilter\" target for IPv6");
MODULE_LICENSE("GPL");

#if 0
	#define DEBUGP(format, args...) printk(format, ##args)
#else
	#define DEBUGP(format, args...) do { } while (0)
#endif

#define IP6_HDRLEN			sizeof(struct ipv6hdr)
#define ICMP6_HDRLEN		sizeof(struct icmp6hdr)
#define IP6_ICMP6_HDRLEN	sizeof(struct ipv6hdr) + sizeof(struct icmp6hdr)
#define UDP_HDRLEN			sizeof(struct udphdr)
#define TCP_HDRLEN			sizeof(struct tcphdr)

#if (LINUX_VERSION_CODE >= KERNEL_VERSION(2,6,35))
static unsigned int
ctfilter_tg6(struct sk_buff *skb, const struct xt_action_param *par)
#else
static unsigned int
ctfilter_tg6(struct sk_buff *skb, const struct xt_target_param *par)
#endif
{
	const struct ip6t_ctfilter_info *ctfilter_info = par->targinfo;
	u_int8_t want_proto;
	int found = 0;

	struct net *net;
	struct nf_conn *ct = NULL;
	enum ip_conntrack_info ct_info;
	struct nf_conntrack_tuple *tuple = NULL;

	// point to appropriate incoming tuple (or skb)
	struct in6_addr *sip6, *dip6;
	__be16 dport, payload_len;

	// for parse skb to icmpv6 header
	struct ipv6hdr *hdr;
	u8 nexthdr;

	// parse skb point to icmpv6 content
	struct in6_addr *c_sip6, *c_dip6;
	struct icmp6hdr *c_icmp6;
	struct udphdr *c_udp;
	struct tcphdr *c_tcp;
	u_int8_t c_proto, c_type, c_code;
	__be16 c_sport, c_dport;

	// ituple for each all conntrack
	unsigned int i;
	struct nf_conntrack_tuple_hash *ih;
	struct hlist_nulls_node *in;
	struct nf_conn *ict;
	struct nf_conntrack_tuple *ituple;

	// point to appropriate ituple
	struct in6_addr *isip6, *idip6;
	u_int8_t iproto;
	__be16 isport;

	/*
	  Notice: some incoming icmpv6 ct is NULL, thus we can't use ct when want_proto == IPPROTO_ICMPV6
	*/
	ct = nf_ct_get(skb, &ct_info);
	tuple = &ct->tuplehash[IP_CT_DIR_ORIGINAL].tuple;

	switch (ctfilter_info->proto_type) {
	case IP6T_UDP:
		want_proto = IPPROTO_UDP;
		break;
	case IP6T_TCP:
		want_proto = IPPROTO_TCP;
		break;
	case IP6T_ICMPV6:
		want_proto = IPPROTO_ICMPV6;
		break;
	default:
		return IP6T_CONTINUE; // Unknown type not support
	}
	
	// Check incoming tuple(or skb) proto is I want
	if (want_proto == IPPROTO_ICMPV6) {
		hdr = ipv6_hdr(skb);
		nexthdr = hdr->nexthdr;
		
		if (nexthdr != IPPROTO_ICMPV6) {
			DEBUGP("ctfilter_tg6: want_proto=%d, tuple protonum=%d, return IP6T_CONTINUE\n", 
				want_proto, nexthdr);
			return IP6T_CONTINUE;
		}
	} else if (ct == NULL || tuple->dst.protonum != want_proto) {
		DEBUGP("ctfilter_tg6: want_proto=%d, tuple protonum=%d, return IP6T_CONTINUE\n", 
			want_proto, tuple->dst.protonum);
		return IP6T_CONTINUE;
	}
	
	// set pointer to appropriate incoming tuple (or skb)
	if (want_proto == IPPROTO_ICMPV6) {
		sip6 = &hdr->saddr;
		dip6 = &hdr->daddr;
		payload_len = hdr->payload_len;

		DEBUGP("ctfilter_tg6: IPv6 Header sip6=%pI6 dip6=%pI6 c_proto=%04x len=%d IP6_ICMP6_HDRLEN=%d\n",
			sip6, dip6, want_proto, payload_len, IP6_ICMP6_HDRLEN);

		if ((payload_len -= IP6_ICMP6_HDRLEN) > 0) {
			// set pointer to icmpv6 content
			hdr = (struct ipv6hdr *)((u8*)hdr + IP6_ICMP6_HDRLEN);
			c_sip6 = &hdr->saddr;
			c_dip6 = &hdr->daddr;
			c_proto = hdr->nexthdr;

			DEBUGP("ctfilter_tg6: ICMPv6 content IPv6 Header c_sip6=%pI6 c_dip6=%pI6 c_proto=%04x\n",
				c_sip6, c_dip6, c_proto);

			switch (c_proto) {
			case IPPROTO_ICMPV6:
				if (payload_len >= ICMP6_HDRLEN) {
					c_icmp6 = (struct icmp6hdr *)((u8*)hdr + IP6_HDRLEN);
					c_type = c_icmp6->icmp6_type;
					c_code = c_icmp6->icmp6_code;
					DEBUGP("ctfilter_tg6: ICMPv6 content is ICMPv6 c_type=%04x c_code=%04x\n",
						c_type, c_code);
				} else {
					c_sip6 = NULL;
				}
				break;
			case IPPROTO_UDP:
				if (payload_len >= UDP_HDRLEN) {
					c_udp = (struct udphdr *)((u8*)hdr + IP6_HDRLEN);
					c_sport = c_udp->source;
					c_dport = c_udp->dest;
					DEBUGP("ctfilter_tg6: ICMPv6 content is UDP c_sport=%04x c_dport=%04x\n",
						c_sport, c_dport);
				} else {
					c_sip6 = NULL;
				}
				break;
			case IPPROTO_TCP:
				if (payload_len >= TCP_HDRLEN) {
					c_tcp = (struct tcphdr *)((u8*)hdr + IP6_HDRLEN);
					c_sport = c_tcp->source;
					c_dport = c_tcp->dest;
					DEBUGP("ctfilter_tg6: ICMPv6 content is TCP c_sport=%04x c_dport=%04x\n",
						c_sport, c_dport);
				} else {
					c_sip6 = NULL;
				}
				break;
			default:
				c_sip6 = NULL;
			}
		} else {
			c_sip6 = NULL;
		}
	} else {
		if (ct_info != IP_CT_NEW && ct_info != IP_CT_RELATED)
			return IP6T_CONTINUE;

		sip6 = &tuple->src.u3.in6;
		dip6 = &tuple->dst.u3.in6;
		dport = tuple->dst.u.tcp.port;
	}
	
	DEBUGP("ctfilter_tg6: s=%pI6 d=%pI6 ct_info=%d\n", sip6, dip6, ct_info);

#ifdef CONFIG_NET_NS
#error "CTFILTER not support define CONFIG_NET_NS" // if CONFIG_NET_NS=y raise a compile error
#else
	/*
	  Notice: I can't use "net=nf_ct_net(ct)", 
	          because ct is NULL when some incoming icmpv6 is not support conntrack
	*/
	net = &init_net;
#endif

	spin_lock_bh(&nf_conntrack_lock);
	
	for (i = 0; i < nf_conntrack_htable_size; i++) {
		hlist_nulls_for_each_entry(ih, in, &net->ct.hash[i], hnnode) {
			/* we only want IP_CT_DIR_ORIGINAL */
			if (NF_CT_DIRECTION(ih))
				continue;

			ict = nf_ct_tuplehash_to_ctrack(ih);
			ituple = &ih->tuple;

			// check ict is AF_INET6
			if (ituple->src.l3num != AF_INET6)
				continue;

			/*
			                                 WAN               LAN
			FORWARD_IN
			In Interface(WAN) :  tuple  (sip6: sport)  -> (dip6: dport)
			ORIGINAL(LAN->WAN):  ituple (idip6:idport) <- (isip6:isport)
			
			FORWARD_OUT
			In Interface(LAN) :  tuple  (dip6: dport)  <- (sip6: sport)
			ORIGINAL(WAN->LAN):  ituple (isip6:isport) -> (idip6:idport)
			*/

			isip6 = &ituple->src.u3.in6;
			idip6 = &ituple->dst.u3.in6;
			iproto = ituple->dst.protonum;
			isport = ituple->src.u.tcp.port;
			
			// we only want dest addr is global address and dest addr is not in the same scope
			if ((idip6->s6_addr32[0] & htonl(0xE0000000)) != htonl(0x20000000) ||
				 ipv6_prefix_equal(idip6, isip6, 64)) {
				DEBUGP("ctfilter_tg6: ituple dip=%pI6\n", idip6);
				continue;
			}

			// if want_proto is ICMPV6, ituple can be ICMPV6, UDP or TCP
			if (want_proto == IPPROTO_ICMPV6) {
				DEBUGP("ctfilter_tg6: ituple sip=%pI6\n", ituple->src.u3.ip6);
				// if not find (dip6==isip6) , try next ituple
				if (memcmp(dip6, isip6, sizeof(struct in6_addr)) != 0)
					continue;
				
				// compare icmpv6 content
				if (c_sip6 == NULL ||
					memcmp(c_sip6, isip6, sizeof(struct in6_addr)) != 0 ||
					memcmp(c_dip6, idip6, sizeof(struct in6_addr)) != 0 ||
					c_proto != iproto) {
					continue;
				} else {
					switch (c_proto) {
					case IPPROTO_ICMPV6:
						if (c_type != ituple->dst.u.icmp.type || c_code != ituple->dst.u.icmp.code)
							continue;
						break;
					case IPPROTO_UDP:
						if (c_sport != ituple->src.u.udp.port || c_dport != ituple->dst.u.udp.port)
							continue;
						break;
					case IPPROTO_TCP:
						if (c_sport != ituple->src.u.tcp.port || c_dport != ituple->dst.u.tcp.port)
							continue;
						break;
					}
				}
			} else if (want_proto == ituple->dst.protonum) {
				DEBUGP("ctfilter_tg6: ituple sip=%pI6, sport=%d\n", ituple->src.u3.ip6, ituple->src.u.tcp.port);
				
				// if proto is TCP && status is not IP_CT_ESTABLISHED, try next ituple
				/*
				if (want_proto == IPPROTO_TCP && !test_bit(IPS_SEEN_REPLY_BIT, &ict->status)) {
					DEBUGP("ctfilter_tg6: ituple's status is not IP_CT_ESTABLISHED\n");
					continue;
				}
				*/
				
				// if not find (dip6==isip6 && dport==isport) , try next ituple
				if (!(memcmp(dip6, isip6, sizeof(struct in6_addr)) == 0 && dport == isport)) {
					DEBUGP("ctfilter_tg6: not find (ip6==isip6 && dport==isport)\n");
					continue;
				}
			} else {
				continue;
			}

			// if filter type is IP6T_ADDRESS_DEPENDENT
			if (ctfilter_info->type == IP6T_ADDRESS_DEPENDENT) {
				// also check sip6==idip6
				if (memcmp(sip6, idip6, sizeof(struct in6_addr)) == 0)
					found = 1;
			} else {
				found = 1;
			}

			if (found) {
				DEBUGP("ctfilter_tg6: found conntrack\n");
				goto exit_loop;
			}
		}
	}

exit_loop:
	spin_unlock_bh(&nf_conntrack_lock);
	if (found) {
		DEBUGP("ctfilter_tg6: found conntrack\n");
		if (want_proto == IPPROTO_ICMPV6) {
			DEBUGP("ctfilter_tg6: ICMPV6 return NF_ACCEPT\n");
			return NF_ACCEPT;
		} else {
			DEBUGP("ctfilter_tg6: UDP/TCP return IP6T_CONTINUE\n");
			return IP6T_CONTINUE;
		}
	} else {
		DEBUGP("ctfilter_tg6: return NF_DROP\n");
		return NF_DROP;
	}
}

static bool ctfilter_tg6_check(const struct xt_tgchk_param *par)
{
	unsigned int hook_mask = par->hook_mask;
	const struct ip6t_ctfilter_info *ctfilter_info = par->targinfo;

	if (ctfilter_info->type == IP6T_ADDRESS_PORT_DEPENDENT) {
		DEBUGP("ip6t_CTFILTER: For Port and Address Restricted. this rule is needless\n");
#if (LINUX_VERSION_CODE >= KERNEL_VERSION(2,6,35))
		return -EINVAL;
#else
		return 0;
#endif
	} else if (ctfilter_info->type == IP6T_ADDRESS_DEPENDENT) {
		DEBUGP("ip6t_CTFILTER: IP6T_ADDRESS_DEPENDENT.\n");
	} else if (ctfilter_info->type == IP6T_ENDPOINT_INDEPENDENT) {
		DEBUGP("ip6t_CTFILTER: Type = IP6T_ENDPOINT_INDEPENDENT.\n");
	}

	if (hook_mask & ~(1 << NF_INET_FORWARD)) {
		DEBUGP("ip6t_CTFILTER: bad hooks %x.\n", hook_mask);
#if (LINUX_VERSION_CODE >= KERNEL_VERSION(2,6,35))
		return -EINVAL;
#else
		return 0;
#endif
	}

#if (LINUX_VERSION_CODE >= KERNEL_VERSION(2,6,35))
	return 0;
#else
	return 1;
#endif
}

static struct xt_target ctfilter_tg6_reg __read_mostly = {
	.name		= "CTFILTER",
	.family		= NFPROTO_IPV6,
	.target		= ctfilter_tg6,
	.targetsize	= sizeof(struct ip6t_ctfilter_info),
	.checkentry	= ctfilter_tg6_check,
	.me		= THIS_MODULE
};

static int __init ctfilter_tg6_init(void)
{
	return xt_register_target(&ctfilter_tg6_reg);
}

static void __exit ctfilter_tg6_exit(void)
{
	xt_unregister_target(&ctfilter_tg6_reg);
}

module_init(ctfilter_tg6_init);
module_exit(ctfilter_tg6_exit);
