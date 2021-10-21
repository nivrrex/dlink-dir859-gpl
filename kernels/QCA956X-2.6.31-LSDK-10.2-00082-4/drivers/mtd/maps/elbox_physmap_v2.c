#include <linux/module.h>
#include <linux/types.h>
#include <linux/kernel.h>
#include <linux/mtd/mtd.h>
#include <linux/mtd/partitions.h>
#include <../../../fs/squashfs/squashfs_fs.h>
#include <linux/magic.h>
//#include <linux/sqmagic.h>


//+++ siyou 2012.02.16 ---//
//examples of mtdparts: mtdparts=ath-nor0:256k(bootloader),64k(bdcfg),64k(devdata),64k(devconf),888k(upgrade),888k(rootfs),64k(radiocfg)
// ath-nor0: is the flash driver's name. This flash driver is in driver/mtd/devices/ath_flash.c
//			 this name is assigned to mtd->name, so kernel can get the correct flash driver for the mtdparts that we declared.
// 888k(upgrade): this special size of "upgrade" partition is for auto size function, we will find out the real size for upgrade partition.
// 888k(rootfs):  this special size of "rootfs" partition is for auto rootfs function, we will find out the real size & start address for "rootfs" partition.
//				  And "rootfs" must be right after the "upgrade" partition otherwise we need to write down "radiocfg" partition's start offset.

// Note, you may ask why not use like 0k(upgrade) instead of 888k(upgrade) for auto size ?
// Because in that way, we need to patch mtdpart.c which has minimum 4KB requirement.
// I'd like to fixup everything here in one file only.
//
// mtdparts do support auto size (remaining size), which will need that partition be the last one.
// But it is not quite useful for our partition case.

static unsigned long find_rootfs(struct mtd_info *mtd, unsigned long *poffset, unsigned long *psize);

//+++ This function is to fixup the mtd block size of "upgrade" & "rootfs".
// for "upgrade" block, we need adjust size only, so you need to make sure the offset of "upgrade" is correct.
// for "rootfs", we need to find out the start address of squashfs begin from "upgrade" block.
// Also we need to adjust the start offset for each partition that come after "rootfs" partition.
// We also adjust mtd size (flash size) here instead of in flash driver.
// We will auto generate "flash" partition for whole flash.
void elbox_fixup_parts(struct mtd_info *mtd, struct mtd_partition **pmparts, int *nbparts)
{
	int i;
	unsigned long offset, size;
	struct mtd_partition *mparts = *pmparts;
	struct mtd_partition *newparts; //for replace, we need add additional partition.
	struct mtd_partition *upgrade_part, *rootfs_part;
	//unsigned long known_size;
	char *extra_mem;
	int np = *nbparts;


	upgrade_part = rootfs_part = NULL;
	//known_size = 0;

	for (i=0; i < np; i++)
	{
		if ( strcmp( mparts[i].name, "upgrade") == 0 ) 
		{
			upgrade_part = &mparts[i];
			continue;
		}
		if ( strcmp( mparts[i].name, "rootfs") == 0 )
		{
			rootfs_part = &mparts[i];
			continue;
		}

		//caculate all known size of partitions.
		//known_size += mparts[i].size;
	}

	//mtd->size = CONFIG_MTD_PHYSMAP_ELBOX_LEN;
	//fixup "flash" partition size.
	//fixup "upgrade" partition size.
	//upgrade_part->size = mtd->size - known_size;

	//fixup "rootfs" partition offset & size.
	//let try to locate squashfs position.
	offset = upgrade_part->offset;
	size =   upgrade_part->size; 
	if ( find_rootfs(mtd, &offset, &size) )
	{
		rootfs_part->offset = offset;
		rootfs_part->size = size;
		rootfs_part->mask_flags = MTD_WRITEABLE; //mask write = read only.
	}

	//fixup the partition after the "upgrade" partition.
	i = np-1;
	offset = mtd->size;
	while ( &mparts[i] > rootfs_part )
	{
		offset -= mparts[i].size;
		mparts[i].offset = offset;
		i--;
	}

	//finally, let's add a "flash" partition for whole flash.
	newparts = (struct mtd_partition*)kzalloc(sizeof(struct mtd_partition) * (np+1) + 256, GFP_KERNEL); //here 256 bytes is for partition name storage.
	//copy original parts.
	memcpy(newparts, mparts, sizeof(struct mtd_partition) * np);
	//copy original partition name.
	extra_mem = (char*)(newparts + (np+1));
	for (i=0; i < np; i++)
	{
		strlcpy(extra_mem, mparts[i].name, 16-1);
		newparts[i].name = extra_mem;
		extra_mem += 16;
	}
	//setup "flash" partition.
	strcpy(extra_mem, "flash");
	newparts[np].name = extra_mem;
	extra_mem += 16;
	if ( extra_mem > ((char*)(newparts + (np+1)) + 256) )
		panic("%s: extra_mem reserve size too small !\n", __FILE__);

	//free original mparts array.
	kfree(mparts);
	*nbparts += 1;
	*pmparts = newparts;
}



#define KERNEL_SKIP	0x60000
#define SEAMA_MAGIC 0x5EA3A417
typedef struct seama_hdr seamahdr_t;
struct seama_hdr
{
	uint32_t	magic;		/* should always be SEAMA_MAGIC. */
	uint16_t	reserved;	/* reserved for  */
	uint16_t	metasize;	/* size of the META data */
	uint32_t	size;		/* size of the image */
} __attribute__ ((packed));

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

static unsigned long find_rootfs(struct mtd_info *mtd, unsigned long *poffset, unsigned long *psize)
{
	unsigned char buf[256];
	int len;
	unsigned long offset = *poffset;
	unsigned long end = offset + *psize;
	seamahdr_t * seama;
	struct squashfs_super_block *squashfsb;
	struct packtag *ptag = NULL;

	/* Try to read the SEAMA header */
	memset(buf, 0, sizeof(buf));
	if ((mtd->read(mtd, offset, sizeof(seamahdr_t), &len, buf) == 0)
			&& (len == sizeof(seamahdr_t)))
	{
		seama = (seamahdr_t *)buf;
		if (ntohl(seama->magic) == SEAMA_MAGIC)
		{
			/* We got SEAMA, the offset should be shift. */
			offset += sizeof(seamahdr_t);
			if (ntohl(seama->size) > 0) offset += 16;
			offset += ntohs(seama->metasize);
			printk("%s: the flash image has SEAMA header\n",mtd->name);
		}
	}

	squashfsb = (struct squashfs_super_block *) &(buf[32]);

	/* Skip kernel image, and look at every 64KB boundary */
	for (offset  += (KERNEL_SKIP); offset < end; offset += (64*1024) )
	{
		memset(buf, 0, sizeof(buf));

#ifdef CONFIG_MTD_DEBUG
		printk("look for offset : 0x%08x\n", offset);
#endif
		/* Read block 0 to test for fs superblock */
		if (mtd->read(mtd, offset, sizeof(buf), &len, buf) || len != sizeof(buf))
			continue;

#ifdef CONFIG_MTD_DEBUG
		printk("%c%c%c%c%c%c%c%c ...\n", buf[0], buf[1], buf[2], buf[3], buf[4], buf[5], buf[6], buf[7]);
		printk("%02x %02x %02x %02x %02x %02x %02x %02x  ...\n", buf[0], buf[1], buf[2], buf[3], buf[4], buf[5], buf[6], buf[7]);
#endif
		/* Try to find the tag of packimgs, the tag is always at 64K boundary. */
		if (memcmp(buf, PACKIMG_TAG, 12))	    
			continue;

		/* yes, we found it, check for supported file system */

		/* squashfs is at block zero */
		squashfsb->s_magic = le32_to_cpu(squashfsb->s_magic);
#ifdef CONFIG_MTD_DEBUG
		printk("squashfsb->s_magic:%x, SQUASHFS_MAGIC:%x\n",squashfsb->s_magic,SQUASHFS_MAGIC);
#endif
		if (squashfsb->s_magic == SQUASHFS_MAGIC)
		{
			printk(KERN_NOTICE
					"%s: squashfs filesystem found at offset 0x%08lx\n",
					mtd->name, offset+32);
			ptag = (struct packtag *)buf;
			goto done;
		}
	}

	panic("%s: Couldn't find valid rootfs image!\n", mtd->name);

done:
	if (ptag)
	{
		*poffset = offset + 32; //skip ptag size
		*psize = ptag->size;
		return 1;
	}

	return 0;
}

