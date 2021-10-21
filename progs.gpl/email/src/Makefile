# search file from current folder to parent folders
# cutoff at 14th parent folder
search_cutoff := ../../../../../../../../../../../../../../
define search-file
	$(if $(filter $(search_cutoff)%,$1),,$(if $(wildcard $1),$1,$(call search-file,../$1)))
endef

include $(call search-file,arch.mk)
include $(call search-file,path.mk)
include $(TOPDIR)/.config
-include /tmp/$(USER)/$(shell echo $(ELBOX_MODEL_NAME))_path.mk

MAKE = make
VERSION = @VERSION@
SRCDIR = .
EMAILPATH = $(TOPDIR)/progs.gpl/email
CFLAGS+=-I$(TOPDIR)/include
CFLAGS += -g -O2 -Wall -W -I$(OPENSSLDIR)/include -DUSE_GNU_STRFTIME -I$(EMAILPATH) -I$(EMAILPATH)/src -I$(EMAILPATH)/include -I../include -I$(EMAILPATH)/dlib/include -I../dlib/include -DEMAIL_VERSION='"3.1.2"' -DCOMPILE_DATE='"01/04/2009-03:11:58PM_EST"' -DEMAIL_DIR='"$(TARGET)/etc/email"' -DHAVE_CONFIG_H
ifeq ($(strip $(ELBOX_PROGS_PRIV_CYASSL)),y)
CFLAGS += -DUSE_CYASSL
endif
LDFLAGS += -L$(OPENSSLDIR)

# for special toolchain, we need to add arguments for using dynamic loading lib.
# I know this is very ugly, but it is a fast workaround (20140616).
ifeq "$(findstring arm-openwrt-linux-,$(CROSS_COMPILE))" "arm-openwrt-linux-"
LIBS = -lcrypto -lssl -lm -ldl
else
LIBS = -lcrypto -lssl -lm
endif

DLIB = ../dlib/libdlib.a

prefix = /usr/local
bindir = ${exec_prefix}/bin
sbindir = ${exec_prefix}/sbin
mandir = ${datarootdir}/man
sysconfdir = ${prefix}/etc
datarootdir = ${prefix}/share

FILES = email.o addr_parse.o addy_book.o conf.o error.o execgpg.o file_io.o \
        message.o mimeutils.o processmail.o progress_bar.o \
	remotesmtp.o sig_file.o smtpcommands.o utils.o ../../../comlib/libxmldbc.o
	
all: $(FILES)
	$(CC) $(CFLAGS) -o email $(FILES) $(OTHER_FILES) $(DLIB) $(LDFLAGS) $(LIBS)


clean:
	rm -f *.o *.d email

clean-all:

