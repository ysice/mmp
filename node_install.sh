#!/bin/sh

nodename=`dc-ctl get node | grep -vE '(name|False|manage|compute)' | awk '{print $2}' `
if [  -z  $nodename ];then
	echo "please start dc-agent in compute"
	exit 0
else
	echo $nodename
	exit 0
	dc-ctl set node $nodename  --add-identity compute
	dc-ctl install compute --node $nodename
fi
