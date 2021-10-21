#
# Makefile for libmtd
#

SUBDIRS =

# CFLAGS += -Werror
#CPPFLAGS += -I../include
LIBS = libuuid
override TARGETS = libuuid.a

include ../common.mk

$(BUILDDIR)/libuuid.a: $(addprefix $(BUILDDIR)/,\
		clear.o copy.o isnull.o parse.o unparse.o compare.o gen_uuid.o pack.o randutils.o unpack.o uuid_time.o)

clean::
	rm -f $(addsuffix .a, $(LIBS))

install::

uninstall:
