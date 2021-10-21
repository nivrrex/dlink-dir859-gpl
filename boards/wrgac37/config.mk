############################################################################
#
# Board dependent Makefile for WRGAC37
# this file is included by TOP level makefile
#
############################################################################

include .config
include lib.mk

MYNAME	:= WRGAC37
MKSQFS	:= ./tools/squashfs-tools-4.0/mksquashfs
SEAMA	:= ./tools/seama/seama
PIMGS	:= ./tools/buildimg/packimgs
LZMA	:= ./tools/lzma/lzma
MYMAKE	:= $(Q)make V=$(V) DEBUG=$(DEBUG)
CROSS_OBJCOPY := mips-linux-objcopy

#FWDEV	:= /dev/mtdblock/1
FWDEV	:= $(CONFIG_CGIBIN_ARIES_FWUPDATE_MTDBLOCK)

FIRMWARENAME := DIR859
FIRMWAREREV= $(shell echo $(ELBOX_FIRMWARE_VERSION) | cut -d. --output-delimiter=\"\" -f1,2)
BUILDNO	:=$(shell cat buildno)
RELIMAGE:=$(shell echo $(FIRMWARENAME)_FW$(FIRMWAREREV)$(ELBOX_FIRMWARE_REVISION)_$(BUILDNO))

#MKSQFS_BLOCK := -b 512k
MKSQFS_BLOCK := -b 64k

KERNELCONFIG := kernel.config
conf_targets := 

#############################################################################
conf_targets += fakeroot_rootfs_image

# This one will be make in fakeroot.
fakeroot_rootfs_image:
	@rm -f fakeroot.rootfs.img
	@./progs.board/makedevnodes rootfs
	$(Q)$(MKSQFS) rootfs fakeroot.rootfs.img $(MKSQFS_BLOCK)

.PHONY: rootfs_image

#############################################################################
# The real image files
$(ROOTFS_IMG): strip_all_file $(MKSQFS)
	$(call color_print,$(MYNAME): building squashfs (LZMA),green)
	$(Q)make clean_CVS
	$(Q)fakeroot make -f progs.board/config.mk fakeroot_rootfs_image
	$(Q)mv fakeroot.rootfs.img $(ROOTFS_IMG)
	$(Q)chmod 664 $(ROOTFS_IMG)

$(KERNEL_IMG): ./tools/lzma/lzma $(KERNELDIR)/vmlinux
	$(call color_print,$(MYNAME): building kernel uImage ...,green)
	$(Q)rm -f vmlinux.bin $(KERNEL_IMG)
	$(Q)$(CROSS_OBJCOPY) -O binary -R .note -R .comment -S $(KERNELDIR)/vmlinux vmlinux.bin
	$(Q)$(LZMA) -9 -f -S .lzma vmlinux.bin
	$(Q)mv vmlinux.bin.lzma $(KERNEL_IMG)

$(KERNELDIR)/vmlinux:
	$(MYMAKE) kernel

$(MKSQFS):
	$(Q)make -C ./tools/squashfs-tools-4.0

./tools/seama/seama:
	$(Q)make -C ./tools/seama

./tools/buildimg/packimgs:
	$(Q)make -C ./tools/buildimg

./tools/lzma/lzma:
	$(Q)make -C ./tools/lzma

strip_all_file: libcreduction_clean libcreduction
	make -f progs.board/strip_all.mk strip_all

##########################################################################

kernel_image:
	$(call color_print,m$(MYNAME): creating kernel image,green)
	$(Q)rm -f $(KERNEL_IMG)
	$(MYMAKE) $(KERNEL_IMG)

rootfs_image:
	$(call color_print,$(MYNAME): creating rootfs image ...,green)
	$(Q)rm -f $(ROOTFS_IMG)
	$(MYMAKE) $(ROOTFS_IMG)

.PHONY: rootfs_image kernel_image

##########################################################################
#
#	Major targets: kernel, kernel_clean, release & tftpimage
#
##########################################################################

conf_targets += kernel_clean
kernel_clean:
	$(call color_print,$(MYNAME): cleaning kernel ...,green)
	$(Q)make -C kernel mrproper

conf_targets += kernel
kernel: kernel_clean
	$(call color_print,$(MYNAME) Building kernel ...,green)
	$(Q)cp progs.board/$(KERNELCONFIG) kernel/.config
	$(Q)make -C kernel oldconfig
	$(Q)make -C kernel dep
	$(Q)make -C kernel

ifeq (buildno, $(wildcard buildno))
BUILDNO	:=$(shell cat buildno)

conf_targets += release
release: kernel_image rootfs_image ./tools/buildimg/packimgs ./tools/seama/seama
	@echo -e "\033[32m"; \
	echo "=====================================";	\
	echo "You are going to build release image.";	\
	echo "=====================================";	\
	echo -e "\033[32m$(MYNAME) make release image... \033[0m"
	$(Q)[ -d images ] || mkdir -p images
	@echo -e "\033[32m$(MYNAME) prepare image...\033[0m"
	$(Q)$(PIMGS) -o raw.img -i $(KERNEL_IMG) -i $(ROOTFS_IMG)
	$(Q)$(SEAMA) -i raw.img -m dev=$(FWDEV) -m type=firmware 
	$(Q)$(SEAMA) -s web.img -i raw.img.seama -m signature=$(ELBOX_SIGNATURE)
	$(Q)$(SEAMA) -d web.img
	$(Q)rm -f raw.img raw.img.seama
	$(Q)./tools/release.sh web.img $(RELIMAGE).bin

	$(Q)if [ -d /tftpboot/$(USER) ]; then cp -f images/$(RELIMAGE).bin /tftpboot/$(USER)/fw.bin; fi
	$(Q)make sealpac_template
	$(Q)if [ -f sealpac.slt ]; then ./tools/release.sh sealpac.slt $(RELIMAGE).slt; fi

conf_targets += magic_release
magic_release: kernel_image rootfs_image ./tools/buildimg/packimgs ./tools/seama/seama
	@echo -e "\033[32m"; \
	echo "===========================================";	\
	echo "You are going to build magic release image.";	\
	echo "===========================================";	\
	echo -e "\033[32m$(MYNAME) make magic release image... \033[0m"
	$(Q)[ -d images ] || mkdir -p images
	@echo -e "\033[32m$(MYNAME) prepare image...\033[0m"
	$(Q)$(PIMGS) -o raw.img -i $(KERNEL_IMG) -i $(ROOTFS_IMG)
	$(Q)$(SEAMA) -i raw.img -m dev=$(FWDEV) -m type=firmware 
	$(Q)$(SEAMA) -s web.img -i raw.img.seama -m signature=$(ELBOX_BOARD_NAME)_aLpHa
	$(Q)$(SEAMA) -d web.img
	$(Q)rm -f raw.img raw.img.seama
	$(Q)./tools/release.sh web.img $(RELIMAGE).magic.bin

#tftpimage: kernel_image rootfs_image ./tools/buildimg/packimgs ./tools/seama/seama
#	@echo -e "\033[32mThe tftpimage of $(MYNAME) is identical to the release image!\033[0m"
#	$(Q)$(PIMGS) -o raw.img -i $(KERNEL_IMG) -i $(ROOTFS_IMG)
#	$(Q)$(SEAMA) -i raw.img -m dev=$(FWDEV) -m type=firmware
#	$(Q)rm -f raw.img; mv raw.img.seama raw.img
#	$(Q)$(SEAMA) -d raw.img
#	$(Q)./tools/tftpimage.sh $(TFTPIMG)
#	$(Q)if [ -d /tftpboot/$(USER) ]; then cp -f images/$(RELIMAGE).bin /tftpboot/$(USER)/fw.bin; fi
#	$(Q)cp -f $(TFTPIMG) ./images/tftp_fw.bin

conf_targets += tftpimage
tftpimage:
	$(call color_print,tftp image not build!,green)
	$(call color_print,we do not want it!,green)
else
conf_targets += release tftpimage
release tftpimage:
	$(call color_print,$(MYNAME): Can not build image, ROOTFS is not created yet !,red)
endif

.PHONY: $(conf_targets)

###################################################################
ifeq ($(strip $(LIB_REDUCTION)), y)
libcreduction:
	$(Q)make -C ./tools/libcreduction install

libcreduction_clean:
	@echo -e "\033[32m libcreduction $(TARGET) !!!!\033[0m"
	$(Q)make -C ./tools/libcreduction clean
else
libcreduction:
libcreduction_clean:
endif

.PHONY: libcreduction libcreduction_clean
