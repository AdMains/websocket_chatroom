<?php

    return array(
        'route'=>array(
            'static' => array(                  //静态路由
                '/reg' => array(
                    'main\\main',               //ctrl类
                    'reg'                       //具体执行的方法
                ),
                '/login' => array(
                    'main\\main',
                    'login'
                ),
                '/savereg' => array(
                    'main\\main',
                    'savereg'
                ),
                '/check' => array(
                    'main\\main',
                    'check'
                ),
            ),
            'dynamic' => array(                     //动态路由
                '/^\/(\d+)\/(.*?)$/iU' => array(    //匹配 http://host/uid/token
                    'main\\main',                   //ctrl类
                    'main',                         //具体执行的方法
                    array('uid', 'token'),          //对应的参数名
                    '/{uid}/{token}'                //反向返回的格式, 通过内置的
                ),
            ),
//            'cache'=>true,//开启路由缓存，路由缓存配置在cache.php中locale中设置
//            'ext'=>'.html'//配置后缀
        ),
    );
