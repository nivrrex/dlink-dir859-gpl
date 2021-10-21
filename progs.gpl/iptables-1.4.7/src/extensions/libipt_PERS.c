/*
 * IP Personality
 *   libipt_PERS.c - Shared library extension of iptables for PERS
 *
 * Copyright (C) 2000, Ga?l Roualland <gael.roualland@iname.com>
 * Copyright (C) 2000, Jean-Marc Saffroy <saffroy@mail.com>   
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 * $Id: libipt_PERS.c,v 1.2 2007/10/31 06:04:02 hoo Exp $
 *
 */

#include <stdio.h>
#include <netdb.h>
#include <string.h>
#include <stdlib.h>
#include <sys/time.h>
#include <getopt.h>
#include <limits.h>
#include <iptables.h>
#include <linux/netfilter_ipv4/ip_tables.h>
#include <linux/netfilter_ipv4/ip_personality.h>
#include "pers.h"

/* Function which prints out usage message. */
static void
help(void)
{
	printf(
"PERS (personality) options:\n"
" --conf file			reads configuration from file.\n"
" --tweak {src|dst}		sets which part of the rule to tweak.\n"
" --local			destination is local, enables decoy for it.\n"
);
}

static const struct option opts[] = {
  { "conf", 1, 0, '1' },
  { "tweak", 1, 0, '2' },
  { "local", 0, 0, '3' },
  { "ipid", 0, 0, '4'},
  { "isn", 0, 0, '5'},
  { "win", 0, 0, '6'},
  { 0 }
};

struct optnames {
  char * text;
  u_int32_t code;
};

#if 0
static struct optnames tweak_opts[] = {
  { "src", IP_PERS_TWEAK_SRC },
  { "dst", IP_PERS_TWEAK_DST },
  { NULL, 0 }
};
#endif

/* Initialize the target. */
static void
init(struct xt_entry_target *t)
{
	struct ip_pers *pers = (struct ip_pers *)t->data;

	memset(pers, 0, sizeof(struct ip_pers));
	pers->isn_type = IP_PERS_ASIS;
	pers->ipid_type = IP_PERS_ASIS;

	/* Can't cache this ?? */
	//*nfcache |= NFC_UNKNOWN;
}

/* Function which parses command options; returns true if it
   ate an option */
static int 
parse(int c, char **argv, int invert, unsigned int *flags,
      const void *entry,
      struct xt_entry_target **target)
{
	//int i, alen;
	struct ip_pers *pers
		= (struct ip_pers *)(*target)->data;
	//struct asmbuf abuf;
	struct timeval now;
	unsigned int seed, res;


	//pers->tweak_type = IP_PERS_TWEAK_SRC;
	//pers->ipid_type = IP_PERS_FIXED_INC;
	//pers->current_ipid = 1000;
	//pers->ipid_param = 10;
	//pers->isn_type = IP_PERS_FIXED_INC;
	//pers->isn_param = 10;
	//pers->tcp_way = IP_PERS_TCP_OUT;
	//pers->current_isn = 0;
	pers->opt_prog.prog_len = 0;
	//printf("libipt_PERS parse switch %c\n", c);
	//return 1;
	switch (c) {
	case '1':
	 //hoo 
	 #if 0
	  if (pers->id[0])
	    exit_error(PARAMETER_PROBLEM,
		       "Only one configuration file allowed.");

	  yyfile = optarg;
	  yypers = pers;
	  yyin = fopen(yyfile, "r");

	  if (!yyin)
	    exit_error(PARAMETER_PROBLEM,
		       "Cannot read %s.", yyfile);
	  
	  yyparse();
	  fclose(yyin);
	  
	  if (!pers->id[0])
	    exit_error(PARAMETER_PROBLEM,
		       "Bad configuration file.");
	    
	  if (yycode[0] && ((alen = asm_gen(&abuf, yycode[0]))>0)) {
	    free_symtree(yycode[0]);
	    yycode[0] = NULL;	
	    
	    if (alen <= IP_PERS_MAX_CODE) { 
	      asm_optimize(abuf.code, alen);
	      pers->opt_prog.prog_len = alen;
	      memcpy(pers->opt_prog.instr, abuf.code, alen * sizeof(u_int32_t));
	    } else {
	      exit_error(PARAMETER_PROBLEM,
			 "Compiled code is too big. Increase IP_PERS_MAX_CODE.");
	    }
	  } else
	    pers->opt_prog.prog_len = 0;

	  if (yycode[1] && ((alen = asm_gen(&abuf, yycode[1]))>0)) {
	    free_symtree(yycode[1]);
	    yycode[1] = NULL;
	    
	    if (alen <= IP_PERS_MAX_CODE) { 
	      asm_optimize(abuf.code, alen);
	      pers->decoy_prog.prog_len = alen;
	      memcpy(pers->decoy_prog.instr, abuf.code, alen * sizeof(u_int32_t));
	    } else {
	      exit_error(PARAMETER_PROBLEM,
			 "Compiled code is too big. Increase IP_PERS_MAX_CODE.");
	    }
	  } else
	    pers->decoy_prog.prog_len = 0;
	  #endif
	  return 1;
	case '2':
	 //hoo 
	 #if 0
	  i = 0;
	  while (tweak_opts[i].text) {
	    if (!strcasecmp(tweak_opts[i].text, optarg)) {
	      pers->tweak_type = tweak_opts[i].code;
	      break;
	    }
	    i++;
	  }
	  if (!tweak_opts[i].text)
	    exit_error(PARAMETER_PROBLEM,
		       "Unknown TWEAK mode.\n");
	  #endif
	  return 1;
	case '3':
	 //hoo 
	 #if 0
	  pers->local = 1;
	 #endif
	  return 1;
	case '4':
	  	pers->tweak_type = IP_PERS_TWEAK_SRC;//POST_ROUTING
		pers->ipid_type = IP_PERS_FIXED_INC;
		pers->current_ipid = 1000;
		pers->ipid_param = 1;
		pers->tcp_way = IP_PERS_TCP_OUT;

		pers->current_isn = 0;
		pers->isn_param = 0;
		pers->isn_type = IP_PERS_FIXED_INC;
		pers->isn_switch = 0;
		pers->tcpwin_switch = 1;
		pers->tcp_maxwin = 2048;

		pers->optprog_switch = 1;
		pers->opt_prog.prog_len = 5;
		pers->opt_prog.instr[0] = 0x00000000|IP_PERS_SET|IP_PERS_SET_MSS|IP_PERS_SET_FROM_THIS;
		pers->opt_prog.instr[1] = 0x00000000|IP_PERS_PUT|IP_PERS_PUT_INS|TCPOPT_MSS;
		pers->opt_prog.instr[2] = 0x00000000|IP_PERS_PUT|IP_PERS_PUT_INS|TCPOPT_NOP;
		pers->opt_prog.instr[3] = 0x00000000|IP_PERS_PUT|IP_PERS_PUT_INS|TCPOPT_NOP;
		pers->opt_prog.instr[4] = 0x00000000|IP_PERS_PUT|IP_PERS_PUT_INS|TCPOPT_SACK_PERM;

		pers->ttl_switch = 1;
		pers->ttl_param = 128;
		//pers->http_hold_switch = 0;
		pers->http_hold_switch = 1;
		//printf("hold = 1 \n");
		return 1;
	case '5':
		gettimeofday(&now, 0);
		seed = (unsigned int)now.tv_sec + (unsigned int)now.tv_usec;
		srand(seed);
		res = random();
		//printf("isn %x \n",res&0xFFFFFFFF);

		pers->tweak_type = IP_PERS_TWEAK_SRC;//FORWARD
		//pers->isn_type = IP_PERS_FIXED_INC;//hoo ʹ??ʱ ע??pci->seq_offset????Ϊ??
		pers->isn_type = IP_PERS_TIME_INC;
		pers->isn_param = 10;
		pers->tcp_way = IP_PERS_TCP_OUT;
		pers->current_isn = res&0xFFFFFFFF;//0x0d030000;//hoo ???ø?Ϊ????????????ÿ?μ???ʱ???Եõ?һ????ͬ
		pers->isn_switch = 1;
		pers->tcpwin_switch = 0;
		pers->tcp_maxwin = 0;
		pers->ttl_switch = 0;
		pers->http_hold_switch = 0;
		//pers->http_hold_switch = 1;
		//printf("hold = 1 \n");
		return 1;
	case '6':
		pers->isn_switch = 0;
		pers->tcpwin_switch = 0;
		pers->tcp_maxwin = 0;
		pers->current_ipid = 0;
		pers->ttl_switch = 0;
		pers->http_hold_switch = 0;
	}
	return 0;
}

/* Final check; don't care. */
static void final_check(unsigned int flags)
{
}

/* Prints out the targinfo. */
static void
print(const void *ip,
      const struct xt_entry_target *target,
      int numeric)
{
	struct ip_pers *pers
		= (struct ip_pers *)target->data;

	switch(pers->tweak_type) {
	case IP_PERS_TWEAK_SRC:
	  printf("tweak:src ");
	  break;
	case IP_PERS_TWEAK_DST:
	  printf("tweak:dst ");
	  break;
	}
	if (pers->local)
	  printf("local ");
	if (pers->id[0])
	  printf("id:%s ", pers->id);
}

/* Saves the union ipt_targinfo in parsable form to stdout. */
static void
save(const void *ip, const struct xt_entry_target *target)
{
	struct ip_pers *pers
		= (struct ip_pers *)target->data;

	switch(pers->tweak_type) {
	case IP_PERS_TWEAK_SRC:
	  printf("--tweak src ");
	  break;
	case IP_PERS_TWEAK_DST:
	  printf("--tweak dst ");
	  break;
	}
	if (pers->local)
	  printf("--local ");
	if (pers->id[0])
	  printf("--conf %s.conf ", pers->id);
}

static struct xtables_target pers_target
= {
    .name = "PERS",
	.version	= XTABLES_VERSION,
	.family		= NFPROTO_IPV4,
    .size = IPT_ALIGN(sizeof(struct ip_pers)),
    .userspacesize = IPT_ALIGN(sizeof(struct ip_pers)),
    .help = help,
    .init = init,
    .parse = parse,
	.print = print,
	.save = save,
	.extra_opts = opts,
	.final_check = final_check
};

void _init(void)
{
	//printf("PERS_init\n");
	xtables_register_target(&pers_target);
}

