/*
 *  Copyright (c) 2013 Qualcomm Atheros, Inc.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 */

/*
 * This file contains glue for Atheros ath spi flash interface
 * Primitives are ath_spi_*
 * mtd flash implements are ath_flash_*
 */
#include <linux/kernel.h>
#include <linux/module.h>
#include <linux/types.h>
#include <linux/errno.h>
#include <linux/slab.h>
#include <linux/semaphore.h>
#include <linux/mtd/mtd.h>
#include <linux/mtd/partitions.h>
#include <asm/delay.h>
#include <asm/io.h>
#include <asm/div64.h>

#include <atheros.h>
#include "ath_flash.h"

/* this is passed in as a boot parameter by bootloader */
extern int __ath_flash_size;

/*
 * statics
 */
static void ath_spi_write_enable(void);
static void ath_spi_poll(void);
#if !defined(ATH_SST_FLASH)
static void ath_spi_write_page(uint32_t addr, uint8_t * data, int len);
#endif
static void ath_spi_sector_erase(uint32_t addr);
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE

static void ath_spi_read_page(uint32_t addr, u_char *data, int len);
#endif

static const char *part_probes[] __initdata = { "cmdlinepart", "RedBoot", NULL };

static DECLARE_MUTEX(ath_flash_sem);

/* GLOBAL FUNCTIONS */
void
ath_flash_spi_down(void)
{
	down(&ath_flash_sem);
}

void
ath_flash_spi_up(void)
{
	up(&ath_flash_sem);
}

EXPORT_SYMBOL(ath_flash_spi_down);
EXPORT_SYMBOL(ath_flash_spi_up);

#define ATH_FLASH_SIZE_2MB          (2*1024*1024)
#define ATH_FLASH_SIZE_4MB          (4*1024*1024)
#define ATH_FLASH_SIZE_8MB          (8*1024*1024)
#define ATH_FLASH_SIZE_16MB         (16*1024*1024)
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
#define ATH_FLASH_SIZE_32MB         (32*1024*1024)
#endif
#define ATH_FLASH_SECTOR_SIZE_64KB  (64*1024)
#define ATH_FLASH_PG_SIZE_256B       256
#define ATH_FLASH_NAME               "ath-nor0"
/*
 * bank geometry
 */
typedef struct ath_flash_geom {
	uint32_t size;
	uint32_t sector_size;
	uint32_t nsectors;
	uint32_t pgsize;
} ath_flash_geom_t;

ath_flash_geom_t flash_geom_tbl[ATH_FLASH_MAX_BANKS] = {
	{
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
		.size		= ATH_FLASH_SIZE_32MB,
#else
		.size		= ATH_FLASH_SIZE_16MB,	/* ALPHA patch */
#endif
		.sector_size	= ATH_FLASH_SECTOR_SIZE_64KB,
		.pgsize		= ATH_FLASH_PG_SIZE_256B
	}
};

static int
ath_flash_probe(void)
{
	return 0;
}

#if defined(ATH_SST_FLASH)
void
ath_spi_flash_unblock(void)
{
	ath_spi_write_enable();
	ath_spi_bit_banger(ATH_SPI_CMD_WRITE_SR);
	ath_spi_bit_banger(0x0);
	ath_spi_go();
	ath_spi_poll();
}
#endif

static int
ath_flash_erase(struct mtd_info *mtd, struct erase_info *instr)
{
	int nsect, s_curr, s_last;
	uint64_t  res;

	if (instr->addr + instr->len > mtd->size)
		return (-EINVAL);

	ath_flash_spi_down();

	res = instr->len;
	do_div(res, mtd->erasesize);
	nsect = res;

	if (((uint32_t)instr->len) % mtd->erasesize)
		nsect ++;

	res = instr->addr;
	do_div(res,mtd->erasesize);
	s_curr = res;

	s_last  = s_curr + nsect;

	do {
		ath_spi_sector_erase(s_curr * ATH_SPI_SECTOR_SIZE);
	} while (++s_curr < s_last);

	ath_spi_done();

	ath_flash_spi_up();

	if (instr->callback) {
		instr->state |= MTD_ERASE_DONE;
		instr->callback(instr);
	}

	return 0;
}

static int
ath_flash_read(struct mtd_info *mtd, loff_t from, size_t len,
		  size_t *retlen, u_char *buf)
{
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
	uint32_t addr = from ; //| 0xbf000000;
#else
	uint32_t addr = from | 0x9f000000;
#endif
	if (!len)
		return (0);
	if (from + len > mtd->size)
		return (-EINVAL);

	ath_flash_spi_down();
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
	ath_spi_read_page(addr, buf, len); //memcpy(buf, (uint8_t *)(addr), len);
#else
	memcpy(buf, (uint8_t *)(addr), len);
#endif
	*retlen = len;

	ath_flash_spi_up();

	return 0;
}

#if defined(ATH_SST_FLASH)
static int
ath_flash_write(struct mtd_info *mtd, loff_t dst, size_t len,
		   size_t * retlen, const u_char * src)
{
	uint32_t val;

	//printk("write len: %lu dst: 0x%x src: %p\n", len, dst, src);

	*retlen = len;

	for (; len; len--, dst++, src++) {
		ath_spi_write_enable();	// dont move this above 'for'
		ath_spi_bit_banger(ATH_SPI_CMD_PAGE_PROG);
		ath_spi_send_addr(dst);

		val = *src & 0xff;
		ath_spi_bit_banger(val);

		ath_spi_go();
		ath_spi_poll();
	}
	/*
	 * Disable the Function Select
	 * Without this we can't re-read the written data
	 */
	ath_reg_wr(ATH_SPI_FS, 0);

	if (len) {
		*retlen -= len;
		return -EIO;
	}
	return 0;
}
#else
static int
ath_flash_write(struct mtd_info *mtd, loff_t to, size_t len,
		   size_t *retlen, const u_char *buf)
{
	int total = 0, len_this_lp, bytes_this_page;
	uint32_t addr = 0;
	u_char *mem;

	ath_flash_spi_down();

	while (total < len) {
		mem = (u_char *) (buf + total);
		addr = to + total;
		bytes_this_page =
		    ATH_SPI_PAGE_SIZE - (addr % ATH_SPI_PAGE_SIZE);
		len_this_lp = min(((int)len - total), bytes_this_page);

		ath_spi_write_page(addr, mem, len_this_lp);
		total += len_this_lp;
	}

	ath_spi_done();

	ath_flash_spi_up();

	*retlen = len;
	return 0;
}
#endif

/*
 * sets up flash_info and returns size of FLASH (bytes)
 */
#if 0/* ALPHA patch */
static int __init ath_flash_init(void)
#else
struct mtd_info * ath_get_mtd_info (void)
#endif
{
	int i, np;
	ath_flash_geom_t *geom;
	struct mtd_info *mtd;
	struct mtd_partition *mtd_parts;
	uint8_t index;

	init_MUTEX(&ath_flash_sem);

#if !(defined(CONFIG_MACH_AR934x) || defined(CONFIG_MACH_QCA955x) || defined(CONFIG_MACH_QCA953x) || defined(CONFIG_MACH_QCA956x))
#if defined(ATH_SST_FLASH)
	ath_reg_wr_nf(ATH_SPI_CLOCK, 0x3);
	ath_spi_flash_unblock();
	ath_reg_wr(ATH_SPI_FS, 0);
#else
	ath_reg_wr_nf(ATH_SPI_CLOCK, 0x43);
#endif
#endif
	for (i = 0; i < ATH_FLASH_MAX_BANKS; i++) {

		index = ath_flash_probe();

#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
		geom = &flash_geom_tbl[i];
#else
		geom = &flash_geom_tbl[index];
#endif
		/* set flash size to value from bootloader if it passed valid value */
		/* otherwise use the default 4MB.                                   */
		if (__ath_flash_size >= 4 && __ath_flash_size <= 16)
			geom->size = __ath_flash_size * 1024 * 1024;

		mtd = kmalloc(sizeof(struct mtd_info), GFP_KERNEL);
		if (!mtd) {
			printk("Cant allocate mtd stuff\n");
			return -1;
		}
		memset(mtd, 0, sizeof(struct mtd_info));

		mtd->name		= ATH_FLASH_NAME;
		mtd->type		= MTD_NORFLASH;
		mtd->flags		= MTD_CAP_NORFLASH | MTD_WRITEABLE;
		mtd->size		= geom->size;
		mtd->erasesize		= geom->sector_size;
		mtd->numeraseregions	= 0;
		mtd->eraseregions	= NULL;
		mtd->owner		= THIS_MODULE;
		mtd->erase		= ath_flash_erase;
		mtd->read		= ath_flash_read;
		mtd->write		= ath_flash_write;
		mtd->writesize		= 1;
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
             mtd->size = 0x02000000;
             mtd->erasesize = 0x00010000;
#endif
		np = parse_mtd_partitions(mtd, part_probes, &mtd_parts, 0);
		if (np > 0) {
			add_mtd_partitions(mtd, mtd_parts, np);
		} else {
			printk("No partitions found on flash bank %d\n", i);
		}
	}
#if 0/* ALPHA patch */
	return 0;
#else
	return mtd;
#endif 
}
/* ALPHA patch */
static int __init ath_flash_init (void)
{
	struct mtd_info *mtd;
	mtd = ath_get_mtd_info();
	if (!mtd) return mtd;

	return 0;
}
static void __exit ath_flash_exit(void)
{
	/*
	 * nothing to do
	 */
}

/*
 * Primitives to implement flash operations
 */
static void
ath_spi_write_enable()
{
	ath_reg_wr_nf(ATH_SPI_FS, 1);
	ath_reg_wr_nf(ATH_SPI_WRITE, ATH_SPI_CS_DIS);
	ath_spi_bit_banger(ATH_SPI_CMD_WREN);
	ath_spi_go();
}
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
static void
ath_spi_write_enable_idx(int idx)
{
        ath_reg_wr_nf(ATH_SPI_FS, 1);
        ath_reg_wr_nf(ATH_SPI_WRITE, ATH_SPI_CS_DIS);
        ath_spi_bit_banger_idx(idx, ATH_SPI_CMD_WREN);
        ath_spi_go_idx(idx);
}

#endif
static void
ath_spi_poll()
{
	int rd;

	do {
		ath_reg_wr_nf(ATH_SPI_WRITE, ATH_SPI_CS_DIS);
		ath_spi_bit_banger(ATH_SPI_CMD_RD_STATUS);
		ath_spi_delay_8();
		rd = (ath_reg_rd(ATH_SPI_RD_STATUS) & 1);
	} while (rd);
}
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
static void
ath_spi_poll_idx(int idx)
{
        int rd;

        do {
                ath_reg_wr_nf(ATH_SPI_WRITE, ATH_SPI_CS_DIS);
                ath_spi_bit_banger_idx(idx, ATH_SPI_CMD_RD_STATUS);
                ath_spi_delay_8_idx(idx);
                rd = (ath_reg_rd(ATH_SPI_RD_STATUS) & 1);
        } while (rd);
}
#endif
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
static void
ath_spi_write_page(uint32_t addr, uint8_t *data, int len)
{
	int i;
	uint8_t ch;
        uint32_t idx;

    	idx = (addr < 0x1000000) ? 0 : 1; 
    	if(idx==1)
		{
       	   addr -= 0x1000000;
		   }

	ath_spi_write_enable_idx(idx);
	ath_spi_bit_banger_idx(idx, ATH_SPI_CMD_PAGE_PROG);
	ath_spi_send_addr_idx(idx, addr);

	for (i = 0; i < len; i++) {
		ch = *(data + i);
		ath_spi_bit_banger_idx(idx, ch);
	}

	ath_spi_go_idx(idx);
	ath_spi_poll_idx(idx);
}

static void
ath_spi_sector_erase(uint32_t addr)
{
    	uint32_t idx; 
	idx = (addr < 0x1000000) ? 0 : 1;
  
    	if(idx==1)
           addr -= 0x1000000;
  
	ath_spi_write_enable_idx(idx);
	ath_spi_bit_banger_idx(idx, ATH_SPI_CMD_SECTOR_ERASE);
	ath_spi_send_addr_idx(idx, addr);
	ath_spi_go_idx(idx);
#if 0
	/*
	 * Do not touch the GPIO's unnecessarily. Might conflict
	 * with customer's settings.
	 */
	display(0x7d);
#endif
	ath_spi_poll_idx(idx);
}

#else
static void
ath_spi_write_page(uint32_t addr, uint8_t *data, int len)
{
	int i;
	uint8_t ch;

	ath_spi_write_enable();
	ath_spi_bit_banger(ATH_SPI_CMD_PAGE_PROG);
	ath_spi_send_addr(addr);

	for (i = 0; i < len; i++) {
		ch = *(data + i);
		ath_spi_bit_banger(ch);
	}

	ath_spi_go();
	ath_spi_poll();
}

static void
ath_spi_sector_erase(uint32_t addr)
{
	ath_spi_write_enable();
	ath_spi_bit_banger(ATH_SPI_CMD_SECTOR_ERASE);
	ath_spi_send_addr(addr);
	ath_spi_go();
#if 0
	/*
	 * Do not touch the GPIO's unnecessarily. Might conflict
	 * with customer's settings.
	 */
	display(0x7d);
#endif
	ath_spi_poll();
}
#endif
#ifdef CONFIG_MTD_DUAL_RUNTIME_IMAGE
static void
ath_spi_read_page(uint32_t addr, u_char *data, int len)
{
    	int i;
    	uint32_t idx; 

    	idx = (addr < 0x1000000) ? 0 : 1;
    	if(idx==1)
		{
       	   addr -= 0x1000000;
		   }

        
    	ath_reg_wr_nf(ATH_SPI_FS, 1);
    	ath_reg_wr_nf(ATH_SPI_WRITE, ATH_SPI_CS_DIS);
    	ath_spi_bit_banger_idx(idx, 0x03);
    	ath_spi_send_addr_idx(idx, addr);
    	for(i = 0; i < len; i++) {
            ath_spi_delay_8_idx(idx);
            *(data + i) = (ath_reg_rd(ATH_SPI_RD_STATUS)) & 0xff;
    	}
    	ath_spi_go_idx(idx);
    	ath_spi_done();
}
#endif
module_init(ath_flash_init);
module_exit(ath_flash_exit);
