## 配置软件仓库
>[warning] **管理节点**和**计算节点**都需要执行

### 将软件仓库修改为国内镜像

云帮安装过程中会下载一些必要的系统包，因此将源修改为国内镜像有助于加快安装速度。



```bash
# 备份原文件
mv /etc/yum.repos.d/CentOS-Base.repo /etc/yum.repos.d/CentOS-Base.repo.bak

# 修改为阿里云的镜像源
wget -O /etc/yum.repos.d/CentOS-Base.repo http://mirrors.aliyun.com/repo/Centos-7.repo
```


### 添加goodrain软件仓库

```bash
cat >/etc/yum.repos.d/goodrain.repo <<EOF
[goodrain-base]
name=goodrain CentOS-\$releasever - for x86_64
baseurl=http://repo.goodrain.com/centos/\$releasever/2017.02/\$basearch
enabled=1
gpgcheck=1
gpgkey=http://repo.goodrain.com/gpg/RPM-GPG-KEY-CentOS-goodrain

[goodrain-noarch]
name=goodrain CentOS-\$releasever - for noarch
baseurl=http://repo.goodrain.com/centos/\$releasever/2017.02/noarch
enabled=1
gpgcheck=1
gpgkey=http://repo.goodrain.com/gpg/RPM-GPG-KEY-CentOS-goodrain
EOF
```

### 更新源列表信息
```bash
yum makecache
```

### 安装 curl
```bash
which curl >/dev/null || yum install -y curl
```

