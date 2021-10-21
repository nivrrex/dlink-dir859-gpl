#!/bin/sh
echo "[$0]: $#($@)" > /dev/console

tag=$1
queue_filename=$2
tmp_filename=/var/run/tr069_$tag.tmp
# needs to stop execution and remove the previous file.
if [ -f "$tmp_filename" ] ; then
	xmldbc -k "$tag"
	rm `cat $tmp_filename` 2>/dev/null
	rm $tmp_filename 2>/dev/null
fi
echo $queue_filename > $tmp_filename
xmldbc -t "$tag:5:sh $queue_filename; rm `cat $tmp_filename`; rm $tmp_filename"