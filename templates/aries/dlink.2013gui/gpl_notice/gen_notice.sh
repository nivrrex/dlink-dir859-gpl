#!/bin/bash

#$1:action(build/clean)
#$2:output directory
#$3:top directory

FW_DESC="firmware_description.txt"
OFFER_FILE="D-Link_GPL_Written_Offer.txt"
GPL_LIC="GPL_License.txt"
GPL_LIC_CONFIG="gpl_license.config"
CONFIG=".config"

ACTION="$1"
OUT_DIR="$2"
TOP_DIR="$3"

case "$ACTION" in 
clean)
	rm -f $OUT_DIR/$FW_DESC
	rm -f $OUT_DIR/$OFFER_FILE
	rm -f $OUT_DIR/$GPL_LIC
;;

build|*)

	. $TOP_DIR/build_gpl/$GPL_LIC_CONFIG

	LICENSE_DATE=${LICENSE_DATE:-$(date +"%m-%d-%Y")}
	COPYRIGHT_YEAR=${COPYRIGHT_YEAR:-$(date +%"Y")}
	. $TOP_DIR/$CONFIG
	MODEL_NAME=${MODEL_NAME:-$(echo $ELBOX_MODEL_NAME | tr a-z A-Z)}

	sed -e s/"@@MODELNAME@@"/"$MODEL_NAME"/ -e s/"@@DATE@@"/"$LICENSE_DATE"/ $FW_DESC > $OUT_DIR/$FW_DESC

	sed -e s/"@@MODELNAME@@"/"$MODEL_NAME"/ -e s/"@@YEAR@@"/"$COPYRIGHT_YEAR"/ $OFFER_FILE >  $OUT_DIR/$OFFER_FILE

	cp $GPL_LIC $OUT_DIR/$GPL_LIC
;;
esac





