聊天室项目
=========

本项目是在LNMP环境下开发，采用了基于swoole扩展的ZPHP框架，服务端与客户端浏览器使用Websocket长连接的方法通讯。
项目运行时会自动依据客户端类别建立Http服务器以提供页面服务和Websocket服务器以提供数据服务。

项目着重于Websocket, redis, mysql, swoole相结合

### 目录结构

注：本项目将http服务器和websocket服务器解耦开来了，目录结构上与ZPHP框架推荐的目录结构略有不同，
因此，对于ZPHP框架做了一定修改。
   
    |- ws_chatroom
      |- app
         |- common  (公共文件，会被http服务器和websocket服务器同时用到)
            |- config
            |- ctrl
            |- dao
            |- database  (重要，务必将此文件导入Mysql)
            |- entity
            |- service
            |- utils
         |- http  (http服务器)
            |- apps
                |-ctrl  （定义了注册、登录、验证等方法）
                |-service  （读写mysql数据，用户信息）
            |- config  （http服务器配置文件）
            |- template （模板文件）
         |- webroot
            |- static
            |- main.php  (项目入口文件)
         |- websocket  (websocket服务器)
            |- apps
                |-ctrl  （定义了上线、下线、消息分发、验证、增删频道等方法）
                |-service  （被ctrl中的方法调用，对应的读写redis数据库，部分方法中会读取mysql数据，向客户端push数据）
                |-socket  （基于swoole的websocket服务端回调函数，监听客户端请求，分发到ctrl中）
            |- config  （websocket服务器配置文件）
      |- vendor  
         |- composer  (自动加载目录)
         |- zphp  (zphp框架，框架内部代码已经针对本项目做了一定修改，如类自动加载函数、根目录配置函数、框架底层redis操作方法等)
         |- ...  
         


### 需求的扩展及服务：


1: swoole: https://github.com/swoole/swoole-src

2: redis: http://redis.io

3: phpredis: http://github.com/phpredis/phpredis

### 项目简介

1) 浏览器访问时，作为http服务器，利用Mysql数据库可以注册、登录、验证以访问聊天页面
2) 聊天页面上使用js建立websocket客户端，并同时监听浏览器事件，将请求发送给服务端，并同时接收服务端数据并实时更新在页面上；
3) 服务端websocket监听端口接收客户端js请求，并分发处理，读取redis数据处理后将数据push到对应客户端。
4) 目前实现的功能有:
    * 群聊、独聊；
    * 上线、下线提醒并实时更新在线列表；
    * "重复登录"提醒，强制下线；
    * 新增、删减频道提醒并实时更新频道列表；
    * 任何人都可以创建频道，只有创建者能删减频道，且删减后服务器主动清除该频道内redis数据，推送提醒信息并断开该频道下连接；
    * 频道内群聊、独聊；
    * 频道内上线、下线提醒，频道间互不干扰；
5) 正在开发的功能有:
    * 一个用户同时维持多个频道连接;
    * 消息记录持久化;
    * 添加好友，好友列表;
    
    
### 运行：

1) cd 程序目录

2) php ws_chatroom/app/webroot/main.php websocket   //websocket服务

3) cache,conn需要redis支持 (app/common/config目录下cache.php和connection.php上相对应的配置)

4) 导入common/database下的sql  (app/common/config目录下pdo是相对应的配置)

5) webserver绑定域名到 ws_chatroom/app/webroot ，运行 http://host


### url route说明


以nginx为例：

    server {
            listen       80;
            server_name  wschat.com;
            root /home/to/yourpath/ws_chatroom/app/webroot;
            index  main.php main.html;
    	    
    
            location / {              
    	        if (!-e $request_filename) {
    	            rewrite ^/(.*)$ /main.php/$1 last;
           	    }
            }
    
    
            error_page   403 404 /404.html;
            error_page   500 502 503 504  /50x.html;
            
    
            location ~ [^/]\.php(/|$) {
    	        fastcgi_split_path_info ^(.+?\.php)(/.*)$;         
           	        if (!-f $document_root$fastcgi_script_name) {
    	            return 404;
           		}
    
                fastcgi_pass   127.0.0.1:9000;
                fastcgi_index  main.php;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                include      /etc/nginx/fastcgi_params;
                fastcgi_param  PATH_INFO $fastcgi_path_info;
            }
    }
