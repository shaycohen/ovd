#!/bin/bash
DAYS_KEEP=7

echo "[$(date)] Starting $0"

TIMESTAMP=$(date +%s)

for LOC in oc ob oh
do
  DDIR="/vagrant/html/import/$LOC"
  ADIR="/vagrant/html/import/archive/$TIMESTAMP/$LOC"
  [[ -d $DDIR ]] || mkdir -p $DDIR
  [[ -d $ADIR ]] || mkdir -p $ADIR
  mv $DDIR/* $ADIR/
  ./bftp.sh -u dbc -p dbc -s dbcftp -P /ovrs/$LOC/taly -f 'TALLY_*.TXT' -D -L $DDIR && { 
    #YURI# ./bftp.sh -u dbc -p dbc -s dbcftp -P /ovrs/$LOC/taly -f 'TALLY_*.TXT' -d
    echo "# ./bftp.sh -u dbc -p dbc -s dbcftp -P /ovrs/$LOC/taly -f 'TALLY_*.TXT' -d"
  } || { 
    echo "ERROR DOWNLOADING FILES"
  }
done

curl -s localhost/import.php?debug_level=3

find /vagrant/html/import/archive -maxdepth 1 -type d -mtime +$DAYS_KEEP -name [0-9][0-9][0-9]\* -exec rm -r {} \;
rsync -az --delete /vagrant/html/import /DMG/import/
