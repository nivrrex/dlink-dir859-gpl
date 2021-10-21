# eCos tools
#

SRCDIR=$(CURDIR)/ecos_host/host
BUILD=$(CURDIR)/build

all:
	@mkdir -p $(BUILD)
	@if [ ! -f $(BUILD)/Makefile ]; then cd $(BUILD) && sh $(SRCDIR)/configure; fi
	@make -C $(BUILD) > /dev/null
	@make install

install:
	@cp -f $(BUILD)/tools/configtool/standalone/common/ecosconfig $(CURDIR)/.

clean:
	@rm -rf $(BUILD) $(CURDIR)/ecosconfig lzma

.PHONY: all clean

