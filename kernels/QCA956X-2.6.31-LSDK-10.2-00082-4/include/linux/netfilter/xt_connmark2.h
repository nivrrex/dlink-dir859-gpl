#ifndef _XT_CONNMARK2_H
#define _XT_CONNMARK2_H

#include <linux/types.h>

/* Copyright (C) 2002,2004 MARA Systems AB <http://www.marasystems.com>
 * by Henrik Nordstrom <hno@marasystems.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

struct xt_connmark2_info {
    unsigned long mark, mask;
    __u8 invert;
};

struct xt_connmark2_mtinfo1 {
    __u32 mark, mask;
    __u8 invert;
};

#endif /*_XT_CONNMARK2_H*/
