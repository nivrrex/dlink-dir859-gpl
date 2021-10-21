#####################################################################

-include ../arch.mk
-include ../../path.mk

APPLET := libnl-tiny.so
objects := $(patsubst %.c,%.o,$(wildcard src/*.c))
PREFIX := /usr/lib

#####################################################################

CFLAGS += -I./src/include -fPIC

all:	$(APPLET)

$(APPLET): $(objects)
	@echo -e "\033[32mBuilding $(APPLET) ...\033[0m"
	$(Q)$(CC) -shared -o $@ $^

install:
	@echo -e "\033[32mInstalling $(APPLET) ...\033[0m"
	$(Q)[ -d $(TARGET)$(PREFIX) ] || mkdir -p $(TARGET)$(PREFIX)
	$(Q)install ./$(APPLET) $(TARGET)$(PREFIX)
	$(Q)chmod +x $(TARGET)$(PREFIX)/$(APPLET)

clean:
	@echo -e "\033[32mCleaning $(APPLET) ...\033[0m"
	$(Q)rm -rf src/*.o $(APPLET)

.PHONY:	all install clean
