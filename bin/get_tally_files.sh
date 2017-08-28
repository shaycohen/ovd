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
    ./bftp.sh -u dbc -p dbc -s dbcftp -P /ovrs/$LOC/taly -f 'TALLY_*.TXT' -d 
  } || { 
    echo "ERROR DOWNLOADING FILES"
  }
done

curl -s localhost/import.php


