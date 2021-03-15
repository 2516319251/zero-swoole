
1. 安装
   
```shell
./configure --prefix=/www/server/php/pro

make && make install

ln -s /www/server/php/pro/bin/php /usr/local/bin/php

php -i | grep php.ini

Configuration File (php.ini) Path => /www/server/php/pro/lib

cp php.ini-develment /www/server/php/pro/lib/php.ini
```

2. 扩展

```shell

ln -s /www/server/php/pro/bin/phpize /usr/local/bin/phpize

phpize

./configure --with-php-config=/www/server/php/pro/bin/php-config

make && make install

extension=swoole

php ext
cd php/ext/mbstring

/www/server/php/pro/bin/phpize

./configure --with-php-config=/www/server/php/pro/bin/php-config

make && make install

extension=mbstring
```

3. nginx

```shell
开启 php-fpm

cd /www/server/php/pro/etc
cp php-fpm.conf.default php-fpm.conf
cd /www/server/php/pro/etc/php-fpm.d
cp www.conf.default www.conf
cd /www/server/php/pro/sbin
./php-fpm

INT, TERM 立刻终止
QUIT 平滑终止
USR1 重新打开日志文件
USR2 平滑重载所有worker进程并重新载入配置和二进制模块
kill -QUIT `cat /www/server/php/pro/var/run/php-fpm.pid`


./configure  --prefix=/usr/local/nginx
  --sbin-path=/usr/local/nginx/sbin/nginx
   --conf-path=/usr/local/nginx/conf/nginx.conf
    --error-log-path=/var/log/nginx/error.log
      --http-log-path=/var/log/nginx/access.log
        --pid-path=/var/run/nginx/nginx.pid
         --lock-path=/var/lock/nginx.lock
           --user=nginx
            --group=nginx
             --with-http_ssl_module
              --with-http_stub_status_module
               --with-http_gzip_static_module
                --http-client-body-temp-path=/var/tmp/nginx/client/
                 --http-proxy-temp-path=/var/tmp/nginx/proxy/
                  --http-fastcgi-temp-path=/var/tmp/nginx/fcgi/
                   --http-uwsgi-temp-path=/var/tmp/nginx/uwsgi
                    --http-scgi-temp-path=/var/tmp/nginx/scgi
                     --with-pcre

```

4. 使用
```shell

php /cmd/zero http

```

5. 压测
```shell
yum -y install httpd-tools

ab -c 200 -n 200000 -k http://127.0.0.1:80/
```

6. 其他
```shell

查看防火墙某个端口是否开放
firewall-cmd --query-port=80/tcp

开放防火墙端口80
firewall-cmd --zone=public --add-port=80/tcp --permanent

关闭80端口

firewall-cmd --zone=public --remove-port=80/tcp --permanent  

配置立即生效
firewall-cmd --reload 
查看防火墙状态
systemctl status firewalld

关闭防火墙
systemctl stop firewalld

打开防火墙
systemctl start firewalld

开放一段端口
firewall-cmd --zone=public --add-port=8121-8124/tcp --permanent

查看开放的端口列表
firewall-cmd --zone=public --list-ports

```