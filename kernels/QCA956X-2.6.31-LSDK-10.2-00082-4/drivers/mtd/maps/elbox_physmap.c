
/*
 * Alpha mappings of chips in physical memory
 *
 * Copyright (C) 2003 MontaVista Software Inc.
 * Author: Jun Sun, jsun@mvista.com or jsun@junsun.net
 *
 */

#include <linux/module.h>
#include <linux/types.h>
#include <linux/kernel.h>
#include <linux/init.h>
#include <linux/slab.h>
#include <asm/io.h>
#include <linux/mtd/mtd.h>
#include <linux/mtd/map.h>
#include <linux/config.h>
#include <linux/mtd/partitions.h>
#include <linux/squashfs_fs.h>
#include <linux/sqmagic.h>  // jack add



#define WINDOW_ADDR	CONFIG_MTD_PHYSMAP_ELBOX_START
#define WINDOW_SIZE	CONFIG_MTD_PHYSMAP_ELBOX_LEN
#define BUSWIDTH	CONFIG_MTD_PHYSMAP_EBLOX_BANKWIDTH
#define KERNEL_SKIP	CONFIG_ELBOX_KERNEL_SKIP
#define SQUASHFS_MAGIC_V4		0x68737173
static struct mtd_info *mymtd;

#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
#define FLASH2_OFFSET	WINDOW_SIZE
#define FLASH2_SIZE		WINDOW_SIZE
#endif
/*jack add +++*/

static struct mtd_partition *mtd_parts;
static int                   mtd_parts_nb;

static int num_physmap_partitions;

static const char *part_probes[] __initdata = {"cmdlinepart", "RedBoot", NULL};
/* SEAMA */
#define SEAMA_MAGIC 0x5EA3A417
typedef struct seama_hdr seamahdr_t;
struct seama_hdr
{
	uint32_t	magic;		/* should always be SEAMA_MAGIC. */
	uint16_t	reserved;	/* reserved for  */
	uint16_t	metasize;	/* size of the META data */
	uint32_t	size;		/* size of the image */
} __attribute__ ((packed));

/*jack add ---*/



struct map_info physmap_map =
{
	.name = "ELBOX physically mapped flash",
	.phys		= CONFIG_MTD_PHYSMAP_ELBOX_START,
	.size		= CONFIG_MTD_PHYSMAP_ELBOX_LEN,
	.bankwidth	= CONFIG_MTD_PHYSMAP_EBLOX_BANKWIDTH,
};

#ifdef CONFIG_MTD_PARTITIONS

static int mtd_parts_nb = 0;
/* 16M: Bouble - 20111007
        0x9F000000 +------------------------------------+
		           |   u-boot (bootloader) (256KB)    	|
		0x9F040000 +------------------------------------+
		           | u-boot config (bdcfg)(64KB)		|
		0x9F050000 +------------------------------------+
				   | board config (devdata)(64KB)		|
		0x9F060000 +------------------------------------+
				   | User config (devconf)(64KB)		|
		0x9F070000 +------------------------------------+
		           |                     				|
		           |       kernel + rootfs (upgrade)    |
		           |        ( F80000KB )             	|
			       |                     				|
		0x9FFF0000 +------------------------------------+
				   |radio config (radiocfg) (64KB)  	|
				   --------------------------------------
				   
		Another, reserve 3 partitions for future use. And, their space will be overlay with "upgrade" partition.
		They are revdev1(64K), revdev2(64K), and revdev3(256K).
*/



//#ifdef CONFIG_MTD_LANG_PACK
//#define SIZE_LANGPACK	CONFIG_MTD_LANG_PACK_SIZE
//#else
//#define SIZE_LANGPACK	0
//#endif

//#ifdef CONFIG_MTD_CERTIFICATE
//#define SIZE_CERTCFG 	        CONFIG_MTD_CERTIFICATE_SIZE	
//#else
//#define SIZE_CERTCFG 	        0	
//#endif

#define SIZE_BOOTCODE	0x40000

#define SIZE_BDCFG		0x10000	/* Size of Board Data (bdcfg) */
#define SIZE_DEVDATA	0x10000	/* Size of Board permanent data (devdata) */
#define SIZE_DEVCONF	0x10000	/* Size of User Configuration (devconf) */
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
#define SIZE_ACTCFG	        0x10000	/* Size of ACTION IMAGE CONFIG (ACTCFG) */
#endif
#define SIZE_RADIOCFG	0x10000	/* Size of RADIO CONFIG (radiocfg) */
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE//ELBOX_PROGS_GPL_SNMP_TRAP_WARM_RESTART
#define SIZE_WARM_START 0x10000 /* Size of WARM START CONFIG*/
#endif


/* bdcfg: this block is used to soitre the board configuration. */
#define BDCFG_OFFSET	SIZE_BOOTCODE
#define BDCFG_SIZE		SIZE_BDCFG

/* devdata: this block is used to store permanent data. */
#define DEVDATA_OFFSET		BDCFG_OFFSET + BDCFG_SIZE
#define DEVDATA_SIZE		SIZE_DEVDATA

/* rgdb: this block is used to store the user configuration. */
#define DEVCONF_OFFSET		DEVDATA_OFFSET + DEVDATA_SIZE
#define DEVCONF_SIZE		SIZE_DEVCONF

/* upgradeL this block is used to store the runtime image. */
#define UPGRADE_OFFSET	DEVCONF_OFFSET + DEVCONF_SIZE
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
//#define UPGRADE_SIZE	(WINDOW_SIZE - SIZE_BOOTCODE - SIZE_LANGPACK - SIZE_RGDB - SIZE_BDCFG - SIZE_CERTCFG - SIZE_RADCFG - SIZE_ACTCFG)/2
//dual flash config
#define UPGRADE_SIZE	0xF00000 //WINDOW_SIZE-0x100000//15M,(WINDOW_SIZE - SIZE_BOOTCODE - SIZE_LANGPACK - SIZE_RGDB - SIZE_BDCFG - SIZE_CERTCFG - SIZE_RADCFG )

#define UPGRADE2_OFFSET	FLASH2_OFFSET //using 2nd flashSIZE_BOOTCODE + SIZE_BDCFG + SIZE_LANGPACK + SIZE_RGDB + UPGRADE_SIZE
#define PRIVATEDATA_OFFSET FLASH2_OFFSET+UPGRADE_SIZE
#define PRIVATEDATA_SIZE WINDOW_SIZE-UPGRADE_SIZE

#else
/* upgradeL this block is used to store the runtime image. */
#define UPGRADE_SIZE	(WINDOW_SIZE - SIZE_BOOTCODE - BDCFG_SIZE - DEVDATA_SIZE - DEVCONF_SIZE - SIZE_RADIOCFG )
#endif

/* radiocfg: this block is used to store the radiocfg pack. */
#define RADIOCFG_OFFSET	(WINDOW_SIZE - SIZE_RADIOCFG)
#define RADIOCFG_SIZE	SIZE_RADIOCFG
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE//ELBOX_PROGS_GPL_SNMP_TRAP_WARM_RESTART
/* warm start: this block is used for warm start. */
#define WARM_START_OFFSET (WINDOW_SIZE - SIZE_RADIOCFG - SIZE_ACTCFG - SIZE_WARM_START)
#define WARM_START_SIZE     SIZE_WARM_START
#endif

/* flash: the whole flash image */
#define FLASH_OFFSET	0
#define FLASH_SIZE		WINDOW_SIZE

#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
#define ACTCFG_OFFSET	(WINDOW_SIZE - SIZE_RADIOCFG -  SIZE_ACTCFG)
#define ACTCFG_SIZE	SIZE_ACTCFG
#endif
#if 0
/* below 3 partitions only reserve for future used only. */
#define DEVREV1_SIZE	0x10000
#define DEVREV1_OFFSET	(UPGRADE_OFFSET + UPGRADE_SIZE - DEVREV1_SIZE )

#define DEVREV2_SIZE	0x10000
#define DEVREV2_OFFSET	(DEVREV1_OFFSET - DEVREV2_SIZE )

#define DEVREV3_SIZE	0x20000
#define DEVREV3_OFFSET	(UPGRADE_OFFSET + UPGRADE_SIZE - DEVREV3_SIZE )
#endif

static struct mtd_partition physmap_partitions[] =
{
	{
		.name           = "rootfs",
		.offset         = 0x000000,
		.size           = 0x000000,
		.mask_flags     = MTD_WRITEABLE
	},
	{
		.name           = "upgrade",
		.offset         = UPGRADE_OFFSET,
		.size           = UPGRADE_SIZE
	},
	{
		.name           = "devdata",
		.offset         = DEVDATA_OFFSET ,
		.size           = DEVDATA_SIZE
	},
	{
		.name           = "devconf",
		.offset         = DEVCONF_OFFSET ,
		.size           = DEVCONF_SIZE
	},
	{
		.name           = "radiocfg",
		.offset         = RADIOCFG_OFFSET ,
		.size           = RADIOCFG_SIZE
	},
	{
		.name           = "flash",
		.offset         = FLASH_OFFSET,
		.size           = FLASH_SIZE,
		.mask_flags     = MTD_WRITEABLE
	},
	{
		.name           = "bootloader",
		.offset         = 0x000000,
		.size           = SIZE_BOOTCODE,
		.mask_flags     = MTD_WRITEABLE
	},
	{
		.name           = "bdcfg",
		.offset         = BDCFG_OFFSET ,
		.size           = BDCFG_SIZE
	},
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
	
	{
		.name           = "upgrade2",
		.offset         = FLASH2_OFFSET,
		.size           = UPGRADE_SIZE
	},
	{
		.name           = "action image config",
		.offset         = ACTCFG_OFFSET ,
		.size           = ACTCFG_SIZE
	},
	
	{
		.name           = "privatedata",
		.offset         = PRIVATEDATA_OFFSET,
		.size           = PRIVATEDATA_SIZE
	},
#endif
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE//ELBOX_PROGS_GPL_SNMP_TRAP_WARM_RESTART
	{
		.name           = "warm start",
		.offset         = WARM_START_OFFSET,
		.size           = WARM_START_SIZE
	},

	{
		.name           = "flash2",
		.offset         = FLASH2_OFFSET,
		.size           = FLASH_SIZE
	},
#endif	
#if 0
	{
		.name           = "devrev1",
		.offset         = DEVREV1_OFFSET ,
		.size           = DEVREV1_SIZE
	},
	{
		.name           = "devrev2",
		.offset         = DEVREV2_OFFSET ,
		.size           = DEVREV2_SIZE
	},
	{
		.name           = "devrev3",
		.offset         = DEVREV3_OFFSET ,
		.size           = DEVREV3_SIZE
	},
#endif
	{
		.name           = NULL
	}
};



/* the tag is 32 bytes octet,
 * first part is the tag string,
 * and the second half is reserved for future used. */
#define PACKIMG_TAG "--PaCkImGs--"
struct packtag
{
	char tag[16];
	unsigned long size;
	char reserved[12];
};

#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
extern char *region;
#endif
static struct mtd_partition * __init init_mtd_partitions(struct mtd_info * mtd, size_t size)
{
	struct squashfs_super_block *squashfsb;
	struct packtag *ptag = NULL;
	unsigned char buf[512];
	int off = physmap_partitions[1].offset;
	size_t len;
	seamahdr_t * seama;
#ifdef CONFIG_MTD_DEBUG
	printk("%s: size=0x%08x : off(physmap_partitions[1].offset)=0x%08x, UPGRADE_OFFSET:%x, UPGRADE_SIZE:%x \n", __FUNCTION__, size, off, UPGRADE_OFFSET, UPGRADE_SIZE);
	
#endif

#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
	if (strcmp(region,"2")==0)
		{
		
		off = physmap_partitions[8].offset;
		size=off+size;
		}
#endif
	/* Try to read the SEAMA header */
	memset(buf, 0xa5, sizeof(buf));
	if ((mtd->read(mtd, off, sizeof(seamahdr_t), &len, buf) == 0)
		&& (len == sizeof(seamahdr_t)))
	{
		seama = (seamahdr_t *)buf;
		if (ntohl(seama->magic) == SEAMA_MAGIC)
		{
			/* We got SEAMA, the offset should be shift. */
			off += sizeof(seamahdr_t);
			if (ntohl(seama->size) > 0) off += 16;
			off += ntohs(seama->metasize);
			printk("%s: the flash image has SEAMA header\n",mtd->name);
		}
	}
	
	squashfsb = (struct squashfs_super_block *) &(buf[32]);

	/* Skip kernel image, and look at every 64KB boundary */
	for (off  += (KERNEL_SKIP); off < size; off += (64*1024) )
	{
		memset(buf, 0xe5, sizeof(buf));

#ifdef CONFIG_MTD_DEBUG
		printk("look for offset : 0x%08x\n", off);
#endif
		/* Read block 0 to test for fs superblock */
		if (MTD_READ(mtd, off, sizeof(buf), &len, buf) || len != sizeof(buf))
		{
			continue;
		}

#ifdef CONFIG_MTD_DEBUG
		printk("%c%c%c%c%c%c%c%c ...\n", buf[0], buf[1], buf[2], buf[3], buf[4], buf[5], buf[6], buf[7]);
		printk("%02x %02x %02x %02x %02x %02x %02x %02x  ...\n", buf[0], buf[1], buf[2], buf[3], buf[4], buf[5], buf[6], buf[7]);
#endif
		/* Try to find the tag of packimgs, the tag is always at 64K boundary. */
		if (memcmp(buf, PACKIMG_TAG, 12))
		{	    
			continue;
		}

		/* yes, we found it, check for supported file system */

		/* squashfs is at block zero */
		//printk("squashfsb->s_magic:%x, SQUASHFS_MAGIC:%x\n",squashfsb->s_magic,SQUASHFS_MAGIC);
		if (squashfsb->s_magic == SQUASHFS_MAGIC_LZMA /*is 0x71736873*/ || squashfsb->s_magic == SQUASHFS_MAGIC_V4)
		{
			printk(KERN_NOTICE
				"%s: squashfs filesystem found at offset 0x%08x\n",
				mtd->name, off);
			ptag = (struct packtag *)buf;
			goto done;
		}
	}

	printk(KERN_NOTICE "%s: Couldn't find valid rootfs image!\n", mtd->name);

done:
	if (ptag)
	{
#ifdef CONFIG_MTD_DEBUG
		printk("ptag->tag = %s, ptag->size = 0x%x, ptag->reserved = %s\n", ptag->tag, ptag->size, ptag->reserved);
#endif
		physmap_partitions[0].offset = off + 32;
		physmap_partitions[0].size = ntohl(ptag->size);
	}
	else
	{
		physmap_partitions[0].offset = 0;
		physmap_partitions[0].size = 0;
	}

	return physmap_partitions;
}
#endif /* CONFIG_MTD_PARTITIONS */

//extern struct mtd_info * ar7240_get_mtd_info(void);
extern struct mtd_info * ath_get_mtd_info(void); //PaPa add
static int __init init_physmap(void)
{
#ifdef CONFIG_MTD_PARTITIONS
	struct mtd_partition * parts = 0;
#endif

	static const char *rom_probe_types[] = { "cfi_probe", "jedec_probe", "map_rom", NULL };
	const char **type;

	printk(KERN_NOTICE "ELBOX CFI physmap flash device: %lx at %lx\n", physmap_map.size, physmap_map.phys);
	physmap_map.virt = ioremap(physmap_map.phys, physmap_map.size);

	if (!physmap_map.virt)
	{
		printk("Failed to ioremap\n");
		return -EIO;
	}
#ifdef CONFIG_MTD_DEBUG
	printk("%s:%s: p1 physmap_map.bankwidth=%d\n",__FILE__,__FUNCTION__,physmap_map.bankwidth);
#endif
	simple_map_init(&physmap_map);

	mymtd = NULL;
	type = rom_probe_types;

	for(; !mymtd && *type; type++)	{
		mymtd = do_map_probe(*type, &physmap_map);
	}
	//printk(KERN_NOTICE "Alpha (AR7240) physmap flash device: %x at %x\n", CONFIG_MTD_PHYSMAP_LEN, CONFIG_MTD_PHYSMAP_START);
	physmap_map.virt = 0;
	//mymtd = ar7240_get_mtd_info();
	mymtd = ath_get_mtd_info(); // PaPa add
	
	
	if (!mymtd)
	{
		printk("No ATH serial flash !!\n");
		return -ENXIO;
	}
	else
	{
printk(" ATH serial flash !!\n");
	
	}

	if (mymtd)
	{
		mymtd->owner = THIS_MODULE;
               
#ifdef CONFIG_MTD_PARTITIONS
		parts = init_mtd_partitions(mymtd, physmap_map.size);
		for (mtd_parts_nb=0; parts[mtd_parts_nb].name; mtd_parts_nb++);
		if (mtd_parts_nb > 0)
	{
#ifdef CONFIG_MTD_DEBUG
			printk("%s: mtd_parts_nb=%d, parts[mtd_parts_nb].name=%s\n",__FUNCTION__,mtd_parts_nb,parts[mtd_parts_nb].name);
#endif
			add_mtd_partitions(mymtd, parts, mtd_parts_nb);
			return 0;
	}
#endif

		add_mtd_device(mymtd);
		return 0;
	}
	iounmap(physmap_map.virt);
	return -ENXIO;
}

static void __exit cleanup_physmap(void)
	{
#ifdef CONFIG_MTD_PARTITIONS
	if (mtd_parts_nb)       del_mtd_partitions(mymtd);
	else                    del_mtd_device(mymtd);
#else
		del_mtd_device(mymtd);
#endif
		map_destroy(mymtd);

	iounmap(physmap_map.virt);
	physmap_map.virt = NULL;
}

module_init(init_physmap);
module_exit(cleanup_physmap);

MODULE_LICENSE("GPL");
MODULE_AUTHOR("David Hsieh <david_hsieh@alphanetworks.com>");
MODULE_DESCRIPTION("ELBOX MTD map driver");

