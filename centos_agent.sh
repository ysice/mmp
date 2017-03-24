#!/bin/bash
set -o errexit

if [ -z "$MANAGE_IP" ];then
	echo "Unknown MANAGE IP,please export MANAGE_IP=ip_address(manage ip)"
	exit 3
fi

function check() {
	for service in firewalld NetworkManager
	do
		cmd="systemctl is-active $service"
		echo $cmd
		eval $cmd && exit 1
		cmd="systemctl is-enabled $service"
		echo $cmd
		eval $cmd && exit 1 || continue
	done
}

check

cat >/etc/yum.repos.d/goodrain.repo <<EOF
[goodrain]
name=goodrain CentOS-\$releasever - for x86_64
baseurl=http://repo.goodrain.com/centos/\$releasever/2017.01/\$basearch
enabled=1
gpgcheck=1
gpgkey=http://repo.goodrain.com/gpg/RPM-GPG-KEY-CentOS-goodrain

[goodrain-noarch]
name=goodrain CentOS-\$releasever - for noarch
baseurl=http://repo.goodrain.com/centos/\$releasever/2017.01/noarch
enabled=1
gpgcheck=1
gpgkey=http://repo.goodrain.com/gpg/RPM-GPG-KEY-CentOS-goodrain
EOF

yum makecache



yum install -y dc-agent

if [ -f "/var/run/dc-agent.pid" ];then
    pid=`cat /var/run/dc-agent.pid`
    kill -0 $pid || kill -9 $pid
fi

dc-agent -d -s http://$MANAGE_IP:4001



