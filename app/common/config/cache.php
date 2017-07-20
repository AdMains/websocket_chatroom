<?php
    return array(
        'cache'=>array(
            'locale' => array(//路由缓存配置，本机共享内存
//                'adapter' => 'Yac',
                'adapter' => 'Redis',
                'name' => 'lc',
            ),
            'net' => array( //网络cache配置，
                //存放登录验证的缓存数据，$cacheHelper->set($key, $token)
                'adapter' => 'Redis',
                'name' => 'nc',
                'pconnect' => true,
                'host' => '127.0.0.1',
                'port' => 6379,
                'timeout' => 5
            ),
        ),
    );
