#!/bin/sh

#nodename=`dc-ctl get node | grep -vE '(name|False|manage|compute)' | awk '{print $2}' `
nodename=`ifconfig | grep ether | awk '{print $2}'`

if [  -z  $nodename ];then
	echo "please start dc-agent in compute or no node to add-identity."
	exit 0
else
	echo $nodename
	for node in $nodename
	do
		dc-ctl set node $node  --add-identity compute
		dc-ctl install compute --node $node
		echo "$node install compute."
	done
fi
