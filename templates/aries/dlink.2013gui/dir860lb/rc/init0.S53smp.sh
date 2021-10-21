#!/bin/sh
#=============================================================================
# smp_affinity: 1 = CPU1, 2 = CPU2, 3 = CPU3, 4 = CPU4
# rps_cpus: wxyz = CPU3 CPU2 CPU1 CPU0 (ex:0xd = 0'b1101 = CPU1, CPU3, CPU4)
#=============================================================================
#optimited for wifi

echo 2 > /proc/irq/3/smp_affinity  #GMAC
echo 4 > /proc/irq/4/smp_affinity  #PCIe0
echo 8 > /proc/irq/24/smp_affinity #PCIe1
echo 8 > /proc/irq/25/smp_affinity #PCIe2
echo 8 > /proc/irq/22/smp_affinity #USB
echo 3 > /sys/class/net/ra0/queues/rx-0/rps_cpus
echo 3 > /sys/class/net/rai0/queues/rx-0/rps_cpus
echo 3 > /sys/class/net/eth2/queues/rx-0/rps_cpus
echo 3 > /sys/class/net/eth3/queues/rx-0/rps_cpus

