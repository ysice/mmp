### 安装必需的包

### 管理节点基础服务

>[danger] **管理节点** 执行

```bash
# 安装数据存储etcd
更改<local_ip>为机器的真实内网ip

LOCAL_IP=<your_ip> apt-get install gr-etcd

# 在本例中LOCAL_IP 是 10.19.36.221
LOCAL_IP=10.19.36.221 apt-get install gr-etcd

# 确认etcd服务启动成功
pgrep etcd
2577  ----> etcd 服务已经运行

#查看etcd监听端口
netstat -tulnp | grep etcd
tcp        0      0 127.0.0.1:4001          0.0.0.0:*               LISTEN      2577/etcd
tcp        0      0 10.19.36.221:4001       0.0.0.0:*               LISTEN      2577/etcd
tcp        0      0 127.0.0.1:2379          0.0.0.0:*               LISTEN      2577/etcd
tcp        0      0 10.19.36.221:2379       0.0.0.0:*               LISTEN      2577/etcd
tcp        0      0 10.19.36.221:2380       0.0.0.0:*               LISTEN      2577/etcd

# 安装命令行控制程序和webserver
apt-get install dc-ctl dc-web
```


### 安装agent服务
>[danger] **管理节点** 和 **计算节点** 都需要执行

```bash
# 安装agent服务
apt-get install dc-agent
```
