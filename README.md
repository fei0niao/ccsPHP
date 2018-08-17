## 相关文档
- [Laravel 5.5中文文档](https://d.laravel-china.org/docs/5.5/routing)
- [vuejs2中文文档](https://cn.vuejs.org/v2/guide/installation.html)
- [vue-router中文文档](https://router.vuejs.org/zh-cn/)
- [laravel注释跳转生成包](https://github.com/barryvdh/laravel-ide-helper)

## 提交代码注意事项
- 提交代码之前需要使用phpstorm格式化代码
- commit 之后需要rebase 在push到远程分支
- master分支正式部署的代码. dev分支是团队开发提交的代码
- api文档请写到相应的gitlab仓库wiki里面去
- 全部api都使用post,URI地址使用驼峰
- 参数名称,函数名称,变量,类名称,使用驼峰,尽量使用表达意思的单词组合

## 使用Laravel5.5框架,vuejs2前端框架
Laravel 框架对系统有一些要求。所有这些要求 Laravel Homestead 虚拟机都能满足，因此强烈建议你使用 Homestead 作为你本地的 Laravel 开发环境。
- PHP >= 7.0.0
- PHP OpenSSL 扩展
- PHP PDO 扩展
- PHP redis 扩展
- PHP Mbstring 扩展
- PHP Tokenizer 扩展
- PHP XML 扩展
- 跨域支持:服务器域名配置文件中添加跨域支持[nginx](https://enable-cors.org/server_nginx.html)[apache](https://enable-cors.org/server_apache.html)

## 环境

- mysql 版本5.7
- redis 版本5.7
- nginx-php-fpm框架
- composer PHP依赖管理工具
- 前端页面使用vue-cli工具生成前端页面vuejs + vue-router + webpack ...

## 本地开发环境

- php版本:7.1.9
- phpstorm安装相应的断点调试工具

## 第三方composer包
- [Laravel 的 API 认证系统 Passport](https://d.laravel-china.org/docs/5.5/passport)

## Apache 跨域 vhost.conf
```
<VirtualHost *:80>
    #跨域支持
    DocumentRoot "/Users/ericzhou/webServer/yingli-api/public"
    ServerName   yl.qmmian.cn
    <Directory "/Users/ericzhou/webServer/yingli-api/public"> 
        Require all granted   
        Header set Access-Control-Allow-Origin "*"
        Header set Access-Control-Allow-Methods "GET,PUT,POST,DELETE,PATCH,OPTIONS"
        Header set Access-Control-Allow-Headers "DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Content-Range,Range,Authorization"
    </Directory> 
</VirtualHost>

```
## nginx 跨域 enable-php-cors.conf
```
location ~ [^/]\.php(/|$)
{


    # CORS settings
    # http://enable-cors.org/server_nginx.html
    # http://10.10.0.64 - It's my front end application
     if ($request_method = 'OPTIONS') {
        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS';
        #
        # Custom headers and headers various browsers *should* be OK with but aren't
        #
        add_header 'Access-Control-Allow-Headers' 'DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Content-Range,Range,Authorization';
        #
        # Tell client that this pre-flight info is valid for 20 days
        #
        add_header 'Access-Control-Max-Age' 1728000;
        add_header 'Content-Type' 'text/plain; charset=utf-8';
        add_header 'Content-Length' 0;
        return 204;
     }
     if ($request_method = 'POST') {
        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS';
        add_header 'Access-Control-Allow-Headers' 'DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Content-Range,Range';
        add_header 'Access-Control-Expose-Headers' 'DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Content-Range,Range,Authorization';
     }
     if ($request_method = 'GET') {
        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS';
        add_header 'Access-Control-Allow-Headers' 'DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Content-Range,Range';
        add_header 'Access-Control-Expose-Headers' 'DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Content-Range,Range,Authorization';
     }
     try_files $uri =404;
     fastcgi_pass  127.0.0.1:9000;
     fastcgi_index index.php;
     include fastcgi.conf;
}
```