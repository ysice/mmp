## 示例环境

本文的平台安装部分以最小化安装为示例进行说明，集群信息如下：

| 角色 | 主机名 |IP地址|
|------|---------|-------|
|管理节点|manage01|10.19.36.221|
|计算节点|compute01|10.19.183.101|

>[danger] 安装操作需要使用root权限


## 设置主机名并配置hosts
设置主机名是为了后文方便的执行集群配置命令，配置hosts是要保证计算节点和管理节点之间可以通过主机名互相访问。当然你可以设置dns记录。

### 设置主机名hostname
- 管理节点
```bash
hostname manage01
echo manage01 > /etc/hostname
```

- 计算节点
```bash
hostname compute01
echo compute01 > /etc/hostname
```

### 配置hosts

```bash
# 设置hosts文件
echo -e "10.19.36.221\tmanage01\n10.19.183.101\tcompute01" >> /etc/hosts

# 查看是否写入成功
tail -n 2 /etc/hosts
10.19.36.221   	manage01
10.19.183.101  	compute01
```
>[danger] 请根据实际情况配置hosts解析

