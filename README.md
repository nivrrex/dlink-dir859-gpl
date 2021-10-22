# Dlink-dir859-GPL
Use GPL code to compile Dlink dir859 router firmware.

* 因官方固件不再更新和支持，便使用 https://tsd.dlink.com.tw/ 的GPL代码，编译 Dlink dir859 路由器固件.
* 源码略作调整，适应 Debian 11 环境编译
* 整体源码需要使用 make3.81 编译，已上传至源码目录下

# 编译步骤
#普通用户
```
cd ~
git clone https://github.com/nivrrex/dlink-dir859-gpl
cd dlink-dir859-gpl
```

#root
```
apt install libc6-i386 fakeroot -y
cp -rf mips-gcc-4.3.3-uClibc-0.9.30 /opt
```

#普通用户
```
source ./setupenv
./make3.81
./make3.81
./make3.81
```
