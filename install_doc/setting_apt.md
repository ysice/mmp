## 配置软件仓库
>[warning] **管理节点**和**计算节点**都需要执行

### 将软件仓库修改为国内镜像

云帮安装过程中会下载一些必要的系统包，因此将源修改为国内镜像有助于加快安装速度。



```bash
# 备份原文件
mv /etc/apt/sources.list /etc/apt/sources.list.bak

# 修改为阿里云的镜像源
cat > /etc/apt/sources.list << END
deb http://mirrors.aliyun.com/ubuntu/ trusty main restricted universe multiverse
deb http://mirrors.aliyun.com/ubuntu/ trusty-security main restricted universe multiverse
deb http://mirrors.aliyun.com/ubuntu/ trusty-updates main restricted universe multiverse
deb http://mirrors.aliyun.com/ubuntu/ trusty-proposed main restricted universe multiverse
deb http://mirrors.aliyun.com/ubuntu/ trusty-backports main restricted universe multiverse
deb-src http://mirrors.aliyun.com/ubuntu/ trusty main restricted universe multiverse
deb-src http://mirrors.aliyun.com/ubuntu/ trusty-security main restricted universe multiverse
deb-src http://mirrors.aliyun.com/ubuntu/ trusty-updates main restricted universe multiverse
deb-src http://mirrors.aliyun.com/ubuntu/ trusty-proposed main restricted universe multiverse
deb-src http://mirrors.aliyun.com/ubuntu/ trusty-backports main restricted universe multiverse
END

# 更新源列表信息
apt-get update 

# 安装 curl
which curl >/dev/null || apt-get install -y curl

```
>[warning] 国内镜像源比较多，用户可以根据实际情况自行选择。
>  详细的源列表参见：[源列表](http://wiki.ubuntu.org.cn/%E6%A8%A1%E6%9D%BF:14.04source)

### 添加goodrain软件仓库

```bash
echo deb http://repo.goodrain.com/ubuntu/14.04 2017.02 main | tee /etc/apt/sources.list.d/goodrain.list  && \
curl http://repo.goodrain.com/gpg/goodrain-C4CDA0B7 2>/dev/null | apt-key add - && \
apt-get update
```
