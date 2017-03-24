## 运行 dc-agent 服务
>[danger] **管理节点** 和 **计算节点** 都需要执行

运行dc-agent连接到etcd服务
### 计算节点
```bash
dc-agent -d -s http://manage01:4001
generate new host_id
new node, need a little work to continue
check confict processes..
check confict ports..
Turn to background, output write into /var/log/dc-agent.log
```
>[success] 计算节点日志会记录到`/var/log/dc-agent.log` 文件中。


### 管理节点

运行dc-agent连接到etcd服务

```bash
dc-agent -d -s http://manage01:4001
generate new host_id
new node, need a little work to continue
check confict processes..
check confict ports..
Turn to background, output write into /var/log/dc-agent.log
```
>[success] 管理节点日志会记录到`/var/log/dc-agent.log` 文件中。


### dc-agent 的命令格式
```
dc-agent -d -s http://<ETCD_HOST>:<ETCD_PORT>
 -d: 转入后台运行
 <ETCD_HOST>: etcd服务的IP地址,本示例中为mange01主机
 <ETCD_PORT>: etcd服务端口,默认为4001
```

>[danger] **注意**：
> 安装过程中 **管理节点**  和 **计算节点** 都需要运行 dc-agent  程序，因此当安装过程没有结束时重启了服务器，需要重新运行dc-agent 程序。

## 集群节点管理
>[danger] 在 **管理节点**  执行


```bash
dc-ctl get node
 name            ipaddress   	id                                     	identity
 mange01 	10.19.36.221   	b64b96a8-7563-47bd-91f7-dc17fbe7cd8d
 compute01   10.19.183.101  	57fca9e2-d6bd-4e14-ba65-6d3befdcbb97
```

### 为节点设定身份

```bash
# 将10.19.36.221 设置为manage01
dc-ctl set node manage01 --add-identity manage
add identity 'manage' to node <manage01>

# 将10.19.183.101 设置为compute01
dc-ctl set node compute01 --add-identity compute
add identity 'compute' to node <compute01>
```

### 查看节点身份设置情况
~~~
dc-ctl get node
 name            	ipaddress      	id                                     	identity
 compute01   	10.19.183.101  	57fca9e2-d6bd-4e14-ba65-6d3befdcbb97   	compute
 manage01    	10.19.36.221   	b64b96a8-7563-47bd-91f7-dc17fbe7cd8d   	manage
~~~


## 导入已启动的dc_web和etcd服务
>[danger] 在 **管理节点**  执行
~~~
dc-ctl import dc_web --address manage01:8088

dc-ctl import etcd --address manage01:4001
~~~


### 配置应用域名与端口
>[danger] 以下所有操作都需要在**管理节点**执行

~~~
dc-ctl init
setup wild_domain for this region, leave blank will auto generate:
setup the wild_domain's port for this region, default '80':
set web_domain: 5kdh7.goodrain.io, and web_domain_port: 80
confirm(y/Y) or abort(n/N): y
initial successful.
~~~
>[success] 说明
- 云帮应用泛域名：在平台上运行起来的应用泛解析域名，默认使用 `<random>.goodrain.io` 作为泛域名解析地址。本例是 `5kdh7.goodrain.io`
- 云帮应用访问端口：云帮平台上运行起来的应用访问端口



### 安装存储服务
>[danger] 注意：
> 云帮需要一个分布式文件系统，社区版默认使用NFS作为共享存储，如果你的环境中有分布式文件存储系统，需要使用`dc-ctl set storage --mode custom` 命令安装存储管理服务，然后再将你系统中的分布式存储挂载到 `/grdata` 目录。
><h2>特别说明:</h2>
> 如果没有nfs服务执行默认安装即可，不需要执行自定义存储避免不必要的配置服务。

#### 默认安装nfs服务
~~~
dc-ctl install storage
zero nodes has identity storage
2016-08-24 16:19:25 [INFO] setup instance nfs-server on manage01
2016-08-24 16:20:18 [INFO] init service nfs on manage01
~~~
>[warning]如果没自定义存储，可跳过安装自定义存储步骤

#### 自定义存储[可选]
~~~
dc-ctl set storage --mode custom
~~~
>[warning] 使用已经存在的分布式文件系统，需要在各个管理节点和计算节点上, 将文件系统挂载到/grdata目录


* * * * *
>[info] 特别说明：为确保以下操作能够正常进行，确定所有节点必须处于启动状态,建议每安装完服务后都查看一下节点状态。
> 查看状态`dc-ctl get node`  
> 重启节点`systemctl start dc-agent`

### 安装网络服务
这个步骤会安装docker-engine和calico网络组件
如果你的物理网卡和预定的虚拟网络是同一个网段,
请输入其它的私有网络
~~~
dc-ctl install network
your physical ipaddress 172.17.3.4 is one member of virtual network 172.16.0.0/14,
need to assign an different privite network
enter a new privite network: 192.168.0.0/16
usable ipaddress count: 65536
choose virtual network: 192.168.0.0/16
2016-08-24 16:23:41 [INFO] do calico configure on host manage01
2016-08-24 16:23:41 [INFO] setup instance docker-engine on manage01
Done
2016-08-24 16:23:46 [INFO] init calico
2016-08-24 16:23:46 [INFO] node mange has no calico instance, create one
2016-08-24 16:23:46 [INFO] setup instance calico-manage on manage01
Done
~~~

### 安装管理服务
这一步会在manage01节点安装云帮管理端服务以及依赖的MySQL数据库、DNS服务等。
~~~
dc-ctl install manage
2016-08-24 16:54:00 [INFO] add runtime user rain
2016-08-24 16:54:02 [INFO] install docker-engine on all manage nodes
2016-08-24 16:54:02 [INFO] node manage01 has instance docker-engine
2016-08-24 16:54:02 [INFO] instance docker-engine on manage01 is ready
2016-08-24 16:54:02 [INFO] install dns services
2016-08-24 16:54:02 [INFO] instance skydns on manage01 is ready
2016-08-24 16:54:02 [INFO] waiting for dns_modify task on node manage01 finished
2016-08-24 16:54:03 [INFO] setup an mysql instance
2016-08-24 16:54:03 [INFO] setup instance mysql on manage01
2016-08-24 16:54:31 [INFO] setup service registry
2016-08-24 16:54:31 [INFO] setup instance registry on manage01
2016-08-24 16:54:57 [INFO] setup service beanstalkd
2016-08-24 16:54:57 [INFO] setup instance beanstalkd on manage01
2016-08-24 16:55:09 [INFO] setup service dalaran
2016-08-24 16:55:09 [INFO] setup instance dalaran_docker on manage01
2016-08-24 16:55:21 [INFO] setup instance dalaran_service on manage01
2016-08-24 16:55:24 [INFO] setup instance dalaran_cep on manage01
2016-08-24 16:55:27 [INFO] setup service artifactory
2016-08-24 16:55:27 [INFO] setup instance artifactory on manage01
2016-08-24 16:56:20 [INFO] setup service mpush
2016-08-24 16:56:20 [INFO] setup instance mpush on manage01
2016-08-24 16:57:02 [INFO] setup service openresty
2016-08-24 16:57:02 [INFO] setup instance openresty on manage01
2016-08-24 16:57:23 [INFO] install kubernetes services
2016-08-24 16:57:23 [INFO] install kube-apiserver
2016-08-24 16:57:24 [INFO] setup instance kube-apiserver on manage01
2016-08-24 16:57:40 [INFO] setup instance kube-scheduler on manage01
2016-08-24 16:57:48 [INFO] setup instance kube-controller-manager on manage01
2016-08-24 16:57:57 [INFO] settings vhosts
2016-08-24 16:57:57 [INFO] install nginx for manage01 nodes
2016-08-24 16:57:57 [INFO] setup instance nginx-manage on manage01
2016-08-24 16:58:07 [INFO] start setting domain goodrain.me
2016-08-24 16:58:07 [INFO] setup vhost goodrain.me on node manage01
2016-08-24 16:58:07 [INFO] waiting for setup vhost goodrain.me on node manage01 finished
2016-08-24 16:58:09 [INFO] start setting domain config.goodrain.me
2016-08-24 16:58:09 [INFO] setup vhost config.goodrain.me on node manage01
2016-08-24 16:58:09 [INFO] waiting for setup vhost config.goodrain.me on node manage01 finished
2016-08-24 16:58:12 [INFO] start setting domain lang.goodrain.me
2016-08-24 16:58:12 [INFO] setup vhost lang.goodrain.me on node manage01
2016-08-24 16:58:12 [INFO] waiting for setup vhost lang.goodrain.me on node manage01 finished
2016-08-24 16:58:16 [INFO] start setting domain maven.goodrain.me
2016-08-24 16:58:16 [INFO] setup vhost maven.goodrain.me on node manage01
2016-08-24 16:58:16 [INFO] waiting for setup vhost maven.goodrain.me on node manage01 finished
2016-08-24 16:58:18 [INFO] start setting domain download.goodrain.me
2016-08-24 16:58:18 [INFO] setup vhost download.goodrain.me on node manage01
2016-08-24 16:58:18 [INFO] waiting for setup vhost download.goodrain.me on node manage01 finished
2016-08-24 16:58:20 [INFO] start setting domain slug.goodrain.me
2016-08-24 16:58:20 [INFO] setup vhost slug.goodrain.me on node manage01
2016-08-24 16:58:20 [INFO] waiting for setup vhost slug.goodrain.me on node manage01 finished
2016-08-24 16:58:22 [INFO] pull images
2016-08-24 17:07:23 [INFO] setup service region_api
2016-08-24 17:07:23 [INFO] setup instance region_api on manage01
2016-08-24 17:08:12 [INFO] setup service_logger
2016-08-24 17:08:12 [INFO] setup instance service_logger on manage01
2016-08-24 17:08:30 [INFO] setup docker_logger
2016-08-24 17:08:30 [INFO] setup instance docker_logger on manage01
2016-08-24 17:08:35 [INFO] setup lb_work
2016-08-24 17:08:35 [INFO] setup instance lb_work on manage01
2016-08-24 17:08:40 [INFO] setup mq_work
2016-08-24 17:08:40 [INFO] setup instance mq_work on manage01
2016-08-24 17:08:45 [INFO] setup build_work
2016-08-24 17:08:45 [INFO] setup instance build_work on manage01
2016-08-24 17:08:50 [INFO] setup pods_clean
2016-08-24 17:08:50 [INFO] setup instance pods_clean on manage01
2016-08-24 17:08:55 [INFO] setup service_container_monitor
2016-08-24 17:08:55 [INFO] setup instance service_container_monitor on manage01
~~~

### 安装控制台
~~~
dc-ctl install console
2016-08-24 17:11:50 [INFO] setup instance memcached on mange01
2016-08-24 17:12:03 [INFO] setup instance console on mange
2016-08-24 17:12:25 [INFO] start setting domain console.goodrain.me
2016-08-24 17:12:25 [INFO] setup vhost console.goodrain.me on node mange
2016-08-24 17:12:25 [INFO] waiting for setup vhost console.goodrain.me on node mange finished
~~~

### 安装计算节点

~~~
dc-ctl install compute --node compute01
2016-08-24 17:14:10 [INFO] setup instance gr-docker-engine on compute01
2016-08-24 17:14:44 [INFO] setup instance calico-agent on compute01
2016-08-24 17:15:16 [INFO] setup instance nginx-compute on compute01
2016-08-24 17:15:27 [INFO] pull images
2016-08-24 17:16:16 [INFO] setup instance kubelet on compute01
~~~

<!--
### 安装性能监控组件
 性能分析需要足够大的内存，建议管理节点至少16G内存。
~~~
dc-ctl install analysis
~~~
-->

## 完成安装
截止到这一步，云帮社区版的所有安装部分就已经完成了，下面只需要进行简单的配置就可以完成所有的操作，登录到控制台页面了。
