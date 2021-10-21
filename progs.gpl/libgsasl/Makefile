# search file from current folder to parent folders
# cutoff at 14th parent folder
search_cutoff := ../../../../../../../../../../../../../../
define search-file
	$(if $(filter $(search_cutoff)%,$1),,$(if $(wildcard $1),$1,$(call search-file,../$1)))
endef

include $(call search-file,arch.mk)
include $(call search-file,path.mk)

# for special toolchain, we need special argument for building PIC object code.
pic_arg :=
ifeq "$(findstring arm-openwrt-linux-,$(CROSS_COMPILE))" "arm-openwrt-linux-"
pic_arg := -fPIC
endif

GSASLPATH=libgsasl-0.2.6
LIBS=$(shell echo -L$(CC_PATH)/lib/)

targets :=
targets += all
all:
	@if [ ! -f ./$(GSASLPATH)/Makefile ]; then \
		echo -e "\033[32mlibgsasl hasn't be config before, configuring...\033[0m"; \
		export LIBS=$(LIBS); \
		cd ./$(GSASLPATH); \
		export CFLAGS=$(pic_arg); ./configure --build=i386-pc-linux-gnu --host=$(HOST_TYPE) --target=$(HOST_TYPE) --disable-gssapi; \
	fi
	echo -e "\033[32mCompiling libgsasl ...\033[0m"
	make -C $(GSASLPATH)

targets += install
install:

targets += clean
clean:
	@if [ -f ./$(GSASLPATH)/Makefile ]; then \
	make -C $(GSASLPATH) distclean; \
	rm -f ./$(GSASLPATH)/Makefile; \
	fi

.PHONY: $(targets)
