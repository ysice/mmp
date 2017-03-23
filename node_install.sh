#!/bin/sh

nodename=`dc-ctl get node | grep -vE '(manage|name)' | awk '{print $2}' `
if [ ! -n  $nodename ];then
	echo "please start dc-agent in compute"
else
	dc-ctl set node $nodename  --add-identity compute
	dc-ctl install compute --node $nodename
fi
