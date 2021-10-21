#
# Makefile for encimg
#
#####################################################################
-include ../../path.mk
include $(TOPDIR)/.config
#####################################################################
HGCC :=gcc
CFLAGS := -Wall -I$(TOPDIR)/include
#####################################################################
# source files
MYNAME      := ENCIMG for Encrypt
APPLET      := encimg
LOCAL_SRCS  := encimg.c
CFLAGS	+= -DENCRYPT_STANDALONE
#####################################################################

all: $(APPLET)

####################################################################
#Objects
encimg:
	$(HGCC) -o $(APPLET) $(CFLAGS) $(TOPDIR)/progs.priv/encimg/$(LOCAL_SRCS)

clean:
	$(Q)rm -f $(APPLET) *.o .depend

.PHONY: all install clean
#####################################################################


