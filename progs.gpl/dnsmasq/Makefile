-include ../../arch.mk
-include ../../path.mk
-include ../../.config

ifeq ($(ELBOX_PROGS_GPL_DNSMASQ_2_45), y)
DNSMASQ:=dnsmasq-2.45
OBJS = cache.o rfc1035.o util.o option.o forward.o isc.o network.o \
       dnsmasq.o dhcp.o lease.o rfc2131.o netlink.o dbus.o bpf.o \
       helper.o tftp.o log.o

endif
	   
ifeq ($(ELBOX_PROGS_GPL_DNSMASQ_2_76), y)
DNSMASQ:=dnsmasq-2.76
OBJS = cache.o rfc1035.o util.o option.o forward.o network.o \
	   dnsmasq.o dhcp.o lease.o rfc2131.o netlink.o dbus.o bpf.o \
	   helper.o tftp.o log.o conntrack.o dhcp6.o rfc3315.o \
	   dhcp-common.o outpacket.o radv.o slaac.o auth.o ipset.o \
	   domain.o dnssec.o blockdata.o tables.o loop.o inotify.o \
	   poll.o rrfilter.o edns0.o arp.o
endif

ifeq ($(ELBOX_PROGS_GPL_DNSMASQ_2_78), y)
DNSMASQ:=dnsmasq-2.78
OBJS = cache.o rfc1035.o util.o option.o forward.o network.o \
	   dnsmasq.o dhcp.o lease.o rfc2131.o netlink.o dbus.o bpf.o \
	   helper.o tftp.o log.o conntrack.o dhcp6.o rfc3315.o \
	   dhcp-common.o outpacket.o radv.o slaac.o auth.o ipset.o \
	   domain.o dnssec.o blockdata.o tables.o loop.o inotify.o \
	   poll.o rrfilter.o edns0.o arp.o
endif

APPLET=dnsmasq
PREFIX=/usr/sbin
SRC	= $(DNSMASQ)/src
VPATH = ./$(SRC)

ifeq ($(strip $(ELBOX_PROGS_GPL_DNSMASQ_HAVE_BROKEN_RTC)),y)
COPTS += -DHAVE_BROKEN_RTC
endif

ifeq ($(strip $(ELBOX_PROGS_GPL_DNSMASQ_NO_TFTP)),y)
COPTS += -DNO_TFTP
endif

ifeq ($(strip $(ELBOX_PROGS_GPL_DNSMASQ_NOUSER_ALPHA)),y)
COPTS += -DALPHA_NO_USER_SUPPORT
endif


ifeq ($(strip $(ELBOX_PROGS_GPL_DNSMASQ_NOPRCTL_ALPHA)),y)
COPTS += -DALPHA_NO_PRCTL_SUPPORT
endif

ifeq ($(strip $(ELBOX_PROGS_GPL_DNSMASQ_HELPER_SUPPORT_ALPHA)),y)
COPTS += -DALPHA_DNS_HELPER_SUPPORT
endif


ifeq ($(strip $(ELBOX_PROGS_GPL_NETSNIPER)),y)
OBJS += url_decet.o
COPTS += -DHAVE_DNS_NETSNIPER
endif

#sam_pan add
ifeq ($(strip $(ELBOX_PROGS_GPL_DNSMASQ_ADVANCE_DNS_SERVICE_ALPHA)),y)
CFLAGS += -DADV_DNS_SERVICE -I$(TOPDIR)/include
endif
CFLAGS += -I$(TOPDIR)/include


all :$(APPLET)
	@echo -e "\033[32mBuilding dnsmasq ...\033[0m"
	
.c.o:
	$(Q)$(CC) $(CFLAGS) $(COPTS) -c $<
	

$(APPLET): $(OBJS)
	$(Q)$(CC) $(LDFLAGS) -o $@ $(OBJS) $(LIBS)
	$(Q)$(STRIP) ./dnsmasq
	$(Q)rm -f *.o 

install: all
	@echo -e "\033[32mInstalling dnsmasq ...\033[0m"	
	

ifeq ($(strip $(ELBOX_PROGS_GPL_NETSNIPER)),y)
	$(Q)cp -f ./$(DNSMASQ)/src/url.txt $(TARGET)/etc/
endif
	$(Q)cp -f ./dnsmasq $(TARGET)$(PREFIX) 
	$(Q)chmod +x $(TARGET)$(PREFIX)/dnsmasq

clean:
	@echo -e "\033[32mCleaning dnsmasq ...\033[0m"
	$(Q)rm -f *.o dnsmasq
	$(Q)rm -f *~ ./$(SRC)/*.mo contrib/*/*~ */*~ ./$(SRC)/*.pot	
	$(Q)rm -f ./$(SRC)/*.o ./$(SRC)/dnsmasq.a ./$(SRC)/dnsmasq core */core
	
	
	

