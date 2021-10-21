#
# Makefile for libmtd
#

SUBDIRS =

# CFLAGS += -Werror
CPPFLAGS += -I./include
LIBS = liblzo2
override TARGETS = liblzo2.a

include ../common.mk

$(BUILDDIR)/liblzo.a: $(addprefix $(BUILDDIR)/,\
		lzo1_99.o   lzo1b_7.o   lzo1b_xx.o  lzo1c_8.o   lzo1f_1.o   lzo1x_d1.o  lzo1y_o.o   lzo_init.o \
		lzo1a_99.o  lzo1b_8.o   lzo1.o      lzo1c_99.o  lzo1f_9x.o  lzo1x_d2.o  lzo1z_9x.o  lzo_ptr.o \
		lzo1a.o     lzo1b_99.o  lzo1c_1.o   lzo1c_9.o   lzo1f_d1.o  lzo1x_d3.o  lzo1z_d1.o  lzo_str.o \
		lzo1b_1.o   lzo1b_9.o   lzo1c_2.o   lzo1c_9x.o  lzo1f_d2.o  lzo1x_o.o   lzo1z_d2.o  lzo_util.o \
		lzo1b_2.o   lzo1b_9x.o  lzo1c_3.o   lzo1c_cc.o  lzo1x_1.o   lzo1y_1.o   lzo1z_d3.o \
		lzo1b_3.o   lzo1b_cc.o  lzo1c_4.o   lzo1c_d1.o  lzo1x_1k.o  lzo1y_9x.o  lzo2a_9x.o \
		lzo1b_4.o   lzo1b_d1.o  lzo1c_5.o   lzo1c_d2.o  lzo1x_1l.o  lzo1y_d1.o  lzo2a_d1.o \
		lzo1b_5.o   lzo1b_d2.o  lzo1c_6.o   lzo1c_rr.o  lzo1x_1o.o  lzo1y_d2.o  lzo2a_d2.o \
		lzo1b_6.o   lzo1b_rr.o  lzo1c_7.o   lzo1c_xx.o  lzo1x_9x.o  lzo1y_d3.o  lzo_crc.o)

clean::
	rm -f $(addsuffix .a, $(LIBS))

install::

uninstall:
