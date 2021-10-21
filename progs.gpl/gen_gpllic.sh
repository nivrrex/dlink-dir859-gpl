#!/bin/bash
#S1: directory name, output directory
#S2: file name, the list of GPL source code copyright notice
#S3: file name, the list of GPL directory lacking GPL_template.txt

OUT_DIR=$1
GPL_LIST=$2
GPL_WARNING=$3

CUR_DIR=$(pwd)
GPL_TMPFILE=GPL_template.txt
GPL_TMPFILELIST=$(find . -name $GPL_TMPFILE)

if [ "$GPL_TMPFILELIST" = "" ]; then
	[ -f "$OUT_DIR/$GPL_WARNING" ] || echo -e "NO $GPL_TMPFILE exists in following directory, please create it!!\n" > $OUT_DIR/$GPL_WARNING

	echo "$CUR_DIR" >> $OUT_DIR/$GPL_WARNING
	exit 0
fi

for i in $GPL_TMPFILELIST
do 
	DIR=$(echo $i | xargs dirname)
	MODDAY=$(svn log "$DIR" -l 1 | sed -n '2p' | cut -d' ' -f 5 | sed 's/\-/\//g')
	sed -e s\\"@@modday@@"\\"$MODDAY"\\g $DIR/$GPL_TMPFILE >> $OUT_DIR/$GPL_LIST

done
exit 0
