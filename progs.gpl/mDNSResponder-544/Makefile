-include ../arch.mk
-include ../../path.mk
all:
	@echo -e "\033[32mStart building mDNSResponder-544 ...\033[0m"
	make -C mDNSPosix os=linux SAResponder;

install:
	@echo -e "\033[32mInstalling mDNSResponder-544 ...\033[0m"
	cp mDNSPosix/build/prod/mDNSResponderPosix $(TARGET)/bin/mDNSResponderPosix

clean:
	@echo -e "\033[32mStart cleaning mDNSResponder-544 ...\033[0m"
	make -C mDNSPosix os=linux clean;

.PHONY: all romfs clean

