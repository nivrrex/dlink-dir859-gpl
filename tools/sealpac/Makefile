#
#

-include ../../path.mk
-include ../../.config

###################################################################
# source for sealpac
MYNAME		:= Seattle Language Pack
APPLET		:= sealpac
LOCAL_SRCS	:= sealpac.c ephp.c
CMLIB_SRCS	:= dtrace.c mem_helper.c strobj.c xstream.c md5.c
###################################################################
CFLAGS := -Wall
ELBOX_COMLIB_MEM_HELPER_DISABLE := y
###################################################################
# dtrace debug
ifeq ($(strip $(DEBUG)),y)
DDEBUG := y
DDEBUG_LEVEL := 0
endif
ifeq ($(strip $(DDEBUG)),y)
CFLAGS  += -DDDEBUG
ifdef DDEBUG_LEVEL
CFLAGS  += -DDBG_DEFAULT=$(DDEBUG_LEVEL)
endif
endif
#####################################################################
ifeq ($(strip $(ELBOX_ECOS)),)
-include $(TOPDIR)/Rules.mk
else
COMLIB:=$(TOPDIR)/comlib
COMINC:=$(TOPDIR)/include
LOCAL_OBJS:=$(patsubst %c,%o,$(LOCAL_SRCS))
CMLIB_OBJS:=$(patsubst %c,%o,$(CMLIB_SRCS))
all:
	make $(APPLET)

$(APPLET): $(LOCAL_OBJS) $(CMLIB_OBJS)
	$(Q)$(CC) $(LOCAL_OBJS) $(CMLIB_OBJS) $(LDFLAGS) -o $@

# build rule for source in comlib.
$(CMLIB_OBJS): %.o: $(COMLIB)/%.c
	$(Q)$(CC) -I$(COMINC) $(CFLAGS) -c -o $@ $<

# build rule for our sources.
$(LOCAL_OBJS): %.o: %.c
	$(Q)$(CC) -I$(COMINC) $(CFLAGS) -c -o $@ $<

clean_objs:
	@echo -e "\033[32mCleaning APPLET: $(APPLET) ...\033[0m"
	$(Q)rm -rf $(CPLUS_OBJS) $(LOCAL_OBJS) $(CMLIB_OBJS) $(APPLET) .depend
endif
###################################################################
showconfig:
	@echo "APPLET     = "$(APPLET)
	@echo "LOCAL_SRCS = "$(LOCAL_SRCS)
	@echo "CMLIB_SRCS = "$(CMLIB_SRCS)
	@echo "CFLAGS     = "$(CFLAGS)
	@echo "CMDS       = "$(CMDS)
	@echo "CGIS       = "$(CGIS)
	@echo "DIRS       = "$(DIRS)

clean:
	@echo -e "\033[32mcleaning $(MYNAME) ...\033[0m"
ifneq ($(strip $(TOPDIR)),)
	@$(Q)make clean_objs V=$(V) DEBUG=$(DEBUG)
else
	@rm -rf *.o .depend $(APPLET)
endif

.PHONY: showconfig clean
