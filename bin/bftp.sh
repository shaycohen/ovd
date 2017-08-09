#!/bin/bash

. functions
DL="N"
UL="N"
DEL="N"
LIST="N" #Changed LIST default to YES
FPATH=""
LPATH=""
SCRIPT=""
usage="$0 -u USER -p PASSWORD -s SERVER -f FILE -a APPENDFILE -P PATH -L LOCALPATH -c CUSTOMER -C CUSTOMSCRIPT -t TESTFILE -U[pload] -D[ownload] -d[elete]"
while getopts "C:a:lL:P:hu:p:s:f:c:t:UDd" options; do
  case $options in
    u ) USER=$OPTARG;;
    p ) PASS=$OPTARG;;
    s ) SERVER=$OPTARG;;
    f ) FILES=$( echo $OPTARG | sed -e 's/;/ /g');;
    P ) FPATH=$OPTARG;;
    L ) LPATH=$OPTARG;;
    c ) CUSTOMER=$OPTARG;;
    C ) SCRIPT=$OPTARG;;
    a ) APPEND=$OPTARG;;
    U ) UL="Y";;
    D ) DL="Y";;
    d ) DEL="Y";;
    l ) LIST="Y";;
    t ) TSTFILE=$OPTARG;;
    h ) echo $usage;;
    * ) echo $usage
          exit 1;;
  esac
done
[[ $# == 0 ]] && {
        echo $usage
        exit 1
}
echo $FPATH
echo >> /vagrant/log/ftp.out.$CUSTOMER
echo "---$(date)---">> /vagrant/log/ftp.out.$CUSTOMER
EXT=`date +%d%h%H%M%S`
[[ -z $APPEND ]] || {
        APPENDLOCAL=`echo $APPEND | awk -F"_" '{print $1}'`
        APPENDREMOT=`echo $APPEND | awk -F"_" '{print $2}'`
        echo "AppendLocal: $APPENDLOCAL | AppendRemote: $APPENDREMOT"
}

[[ -z $SCRIPT ]] || [[ -e $SCRIPT ]] || {
        echo "Script file $SCRIPT was not found. Skipping."
        unset SCRIPT
}
[[ -z $SCRIPT ]] || {
        echo "Running Script: $SCRIPT: "
        cat $SCRIPT
# 13-Apr Uri Requested to remove this functionality
        cp $SCRIPT $INCOMING/$(echo $SCRIPT | awk -F"/" '{print $NF}').$EXT
}
[[ -z $LPATH ]] || cd $LPATH
[[ -z $APPEND ]] || mv $APPENDLOCAL $APPENDLOCAL.$EXT
[[ -z $TSTFILE ]] || date >> $TEMP/ts$EXT
echo "Server: $SERVER"
echo "Files: $FILES"
ftp -nv $SERVER <<END_SCRIPT 2>&1 | tee $TEMP/bftp.$EXT
quote USER $USER
quote PASS $PASS
prompt
$([[ $FPATH != "" ]] && echo "cd $FPATH")
$([[ $LPATH != "" ]] && echo "lcd $LPATH")
$([[ -z $TSTFILE ]] || echo "put $TSTFILE")
$([[ -z $TSTFILE ]] || echo "append $TEMP/ts$EXT $TSTFILE")
$([[ -z $APPEND ]] || echo "append $APPENDLOCAL.$EXT $APPENDREMOT")
$([[ $UL == "Y" ]] && echo "mput $(echo $FILES | sed -e 's/WALLW/*/g')")
$([[ $DL == "Y" ]] && { 
	echo "mget $(echo $FILES | sed -e 's/WALLW/*/g')"
})
$([[ $DEL == "Y" ]] && {
        for DELFILE in  $(echo $FILES | sed -e 's/WALLW/\\*/g')
        do
                echo "mdelete $DELFILE"
        done
}
)
$([[ -z $SCRIPT ]] || cat $SCRIPT)
$([[ -z $TSTFILE ]] || echo "get $TSTFILE")
$([[ -z $TSTFILE ]] || echo "del $TSTFILE")
$([[ $LIST == "Y" ]] && echo "ls -ltr")
quit
END_SCRIPT
FTPSTAT=$?
# 13-Apr Uri Requested to remove this functionality
[[ -z $SCRIPT ]] || rm $SCRIPT
[[ $UL == "Y" ]] && {
        for FILE in $(echo $FILES | sed -e 's/WALLW/*/g')
        do
                printf "Verifying $FILE : "
                [[ -e $FILE ]] || {
                        echo "File $FILE not found locally. Skipping."
                        continue
                }
                egrep -A3 '^local:' $TEMP/bftp.$EXT | grep -A3 $FILE | egrep '^226' || {
                        echo "File transfer for $FILE failed"
                        FTPSTAT=2
                }
        done
}
rm $TEMP/bftp.$EXT

echo "---$(date)---">> /vagrant/log/ftp.out.$CUSTOMER
[[ "$FTPSTAT" == "0" ]] && {
        [[ -z $APPEND ]] || mv $APPENDLOCAL.$EXT $COMPLETE/sent/
        exit 0
}||{
        echo "Error occured during transaction, Please consult log file in /vagrant/log/ to review."
        exit 2
}

