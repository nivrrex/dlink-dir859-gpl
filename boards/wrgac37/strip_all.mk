##########################################################################
#hendry add to STRIP all programs bfore making image
include path.mk
include arch.mk

PGRMS_STRIP := $(STRIP) -s
ALL_PGRMS := $(shell find "$(TARGET)/usr/sbin" -type f  | grep -v "fonts" | file -f - | grep ELF | cut -d':' -f1)
ALL_PGRMS += $(shell find "$(TARGET)/usr/bin" -type f | file -f - | grep ELF | cut -d':' -f1)
ALL_PGRMS += $(shell find "$(TARGET)/bin" -type f | file -f - | grep ELF | cut -d':' -f1)
ALL_PGRMS += $(shell find "$(TARGET)/sbin" -type f | file -f - | grep ELF | cut -d':' -f1)

LIB_STRIP := $(STRIP) --strip-unneeded
ALL_LIB := $(shell find "$(TARGET)/lib" -type f | file -f - | grep ELF | cut -d':' -f1)

test :
	@echo -e "\033[32m TARGET = $(TARGET) \033[0m"
	for i in $$ALL_PGRMS; do \
	file $$i; \
	done	
test2 :
	for i in $$ALL_LIB; do \
	file $$i ;\
	done

strip_all : strip_all_progs strip_all_libs

strip_all_progs :
	@echo -e "\033[32m STRIP ALL USER SPACE PROGRAMS !!!!\033[0m"
	$(Q)for i in $$ALL_PGRMS; do \
	$(PGRMS_STRIP) $$i; \
	done
strip_all_libs :
	@echo -e "\033[32m STRIP ALL LIBRARIES !!!!\033[0m"
	$(Q)for i in $$ALL_LIB; do \
	$(LIB_STRIP) $$i; \
	done

.PHONY: strip_all_progs strip_all_libs strip_all

##########################################################################
