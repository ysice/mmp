#!/bin/bash
set -o errexit

echo deb http://repo.goodrain.com/ubuntu/14.04 2017.02 main | tee /etc/apt/sources.list.d/goodrain.list  && \
curl http://repo.goodrain.com/gpg/goodrain-C4CDA0B7 2>/dev/null | apt-key add - && \
apt-get update



apt-get install -y dc-agent < /dev/null

if [ -f "/var/run/dc-agent.pid" ];then
    pid=`cat /var/run/dc-agent.pid`
    kill -0 $pid || kill -9 $pid
fi
dc-agent -d -s http://$MANAGE_IP:4001


