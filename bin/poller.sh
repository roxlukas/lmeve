#!/bin/bash
LMEVEPATH=/home/lukas/lmeve
. ~/.bashrc
. ~/.profile
cd $LMEVEPATH/bin/
/usr/bin/php poller.php >>$LMEVEPATH/var/crontab.log 2>&1
