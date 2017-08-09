echo "[$(date)] Starting $0"


for LOC in oc ob oh
do
  DDIR="/vagrant/html/import/$LOC"
  [[ -d $DDIR ]] || mkdir -p $DDIR
  rm -f $DDIR/TALLY*TXT
  ./bftp.sh -u dbc -p dbc -s dbcftp -P /ovrs/$LOC/taly -f 'TALLY_*.TXT' -D -L $DDIR
done

curl -s localhost/import.php


