#!/bin/bash
. ~/.bashrc
. ~/.profile
cd /home/lukas/lmeve/bin/
/usr/bin/php copy-public.php >>/home/lukas/lmeve/var/crontab.log 2>&1
