
CPPFLAGS += -I../include -I../ubi-utils/include
CPPFLAGS += $(ZLIBCPPFLAGS) $(LZOCPPFLAGS)
CPPFLAGS += -I../libuuid
CPPFLAGS += -I../liblzo/include

ALL_SOURCES=*.[ch] hashtable/*.[ch]

TARGETS = mkfs.ubifs

ifeq ($(WITHOUT_LZO), 1)
  CPPFLAGS += -DWITHOUT_LZO
else
  LZOLDLIBS = $(BUILDDIR)/../liblzo/liblzo2.a
endif

LDLIBS_mkfs.ubifs = -lz $(LZOLDLIBS) -lm -L$(BUILDDIR)/../ubi-utils/ -lubi
LDLIBS_mkfs.ubifs += -L$(BUILDDIR)/../lib -lmtd
LDLIBS_mkfs.ubifs += $(ZLIBLDFLAGS) $(LZOLDFLAGS)
LDLIBS_mkfs.ubifs += $(BUILDDIR)/../libuuid/libuuid.a

include ../common.mk

$(BUILDDIR)/mkfs.ubifs: $(addprefix $(BUILDDIR)/,\
	crc16.o lpt.o compr.o devtable.o \
	hashtable/hashtable.o hashtable/hashtable_itr.o)

clean::
	rm -f $(BUILDDIR)/hashtable/*.o cscope.*

cscope:
	@echo $(ALL_SOURCES) > cscope.files
	@cscope -bR
	@rm cscope.files

install:: ${TARGETS}
	mkdir -p ${DESTDIR}/${SBINDIR}
	install -m 0755 ${TARGETS} ${DESTDIR}/${SBINDIR}/
