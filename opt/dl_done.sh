#!/bin/bash
LOGFILE="/var/log/downloads/all.log"
TIMESTAMP=$(date +%Y%m%d%H%M%S)
if [ ! "$TR_APP_VERSION" = "" ]; then
	DL_NAME=$TR_TORRENT_NAME
	DL_DIR=${TR_TORRENT_DIR%/}
	TYPE=${DL_DIR##*/}
	APP="Transmission"
else
	DL_NAME=$3
	DL_DIR=${1%/*}
	TYPE=$5
	APP="SABnzbd"
fi
if ! grep -Fq "$DL_NAME" "$LOGFILE"; then
	echo "$TIMESTAMP: $DL_NAME" >> "$LOGFILE"
	chmod 666 "$LOGFILE"
	if [ ! "$(echo "$DL_DIR" | egrep -i 'leech')" ]; then
		if [ "$TYPE" = "tv" ]; then
			DST=/var/ftp/TV/
		fi
		if [ "$TYPE" = "movies" ]; then
			DST=/var/ftp/Movies/
		fi
		if [ "$TYPE" = "mp3" ]; then
			DST=/var/ftp/MP3/
		fi
		if [ "$TYPE" = "software" ]; then
			DST=/var/ftp/Software/
		fi
		if [ "$TYPE" = "audiobooks" ]; then
			DST=/var/ftp/Audiobooks/
		fi
		if [ "$TYPE" = "other" ]; then
			DST=/var/ftp/Other/
		fi
        #scp -r "$DL_DIR/$DL_NAME/" debian-transmission@remote-host:"$DST"
        #ssh debian-transmission@remote-host "/opt/download/unrarall.sh --clean=rar $DST"
	fi
fi
MAX=2000
counter=$(wc -l < "$LOGFILE" )
if [ "$counter" -gt "$MAX" ]; then
    tail -n $MAX "$LOGFILE" > "/tmp/all_tmp.log"
    mv "/tmp/all_tmp.log" "$LOGFILE"
	chmod 666 "$LOGFILE"
fi
