<?php
namespace common\utils;
use ZPHP\Core\Config as ZConfig,
    ZPHP\Cache\Factory as ZCache,
    ZPHP\Common\Route as ZRoute,
    ZPHP\Conn\Factory as ZConn;
use ZPHP\Protocol\Request;


class Utils
{

    public static function checkToken($uid, $token)
    {
        if(empty($uid) || empty($token)) {
            return false;
        }
        $config = ZConfig::getField('cache', 'net');//配置缓存
        $cacheHelper = ZCache::getInstance($config['adapter'], $config);
        $key = "{$uid}_tk";
        $realToken = $cacheHelper->get($key);//从缓存中取出
        return $realToken === $token;
    }

    public static function setToken($uid)
    {
        $token = uniqid();
        $config = ZConfig::getField('cache', 'net');
        $cacheHelper = ZCache::getInstance($config['adapter'], $config);
        $key = "{$uid}_tk";
        if ($cacheHelper->set($key, $token)) {//存入redis
            return $token;
        }
        throw new \Exception("token set error", ERROR::TOKEN_ERROR);
    }

    public static function getViewMode()
    {
        if(Request::isLongServer()) {
            return ZConfig::getField('project', 'view_mode', 'Json');
        }
        if(\ZPHP\Common\Utils::isAjax()) {
            return 'Json';
        }
        return 'Php';
    }

    public static function jump($action, $method, $params)
    {
        $url = ZRoute::makeUrl($action, $method, $params);
        return array(
            '_view_mode'=> self::getViewMode(),
            '_tpl_file'=>'jump.php',
            'url'=>$url,
            'static_url'=>ZConfig::getField('project', 'static_url'),
        );
    }

    public static function makeUrl($action, $method, $params)
    {
        return ZRoute::makeUrl($action, $method, $params);
    }

    public static function showMsg($msg)
    {
        return array(
            '_view_mode'=>self::getViewMode(),
            '_tpl_file'=>'error.php',
            'msg'=>$msg,
            'static_url'=>ZConfig::getField('project', 'static_url'),
        );
    }

    public static function online($channel='ALL')
    {
        $config = ZConfig::get('connection');
        $connection = ZConn::getInstance($config['adapter'], $config);
        return $connection->getChannel($channel);//注意，这里并未考虑不同用户创建同名频道的情况，需在ZPHP框架里拓展代码
    }

    //用于Close回调时，向特定频道内push用户下线消息，注意，目前每一个$uid有且仅能同时在一个频道里
    //后期如果同时并存多个频道，可以存在hash结构里。
    public static function setChannel($uid,$channel = 'ALL', $ownerId = "")
    {
        $config = ZConfig::getField('cache', 'net');
        $cacheHelper = ZCache::getInstance($config['adapter'], $config);
        $key = $uid."_chr";
        if ($cacheHelper->set($key, $channel)) {
            return true;
        }
        throw new \Exception("Channel set error", ERROR::CHANNEL_ERROR);
    }

    public static function getChannel($uid, $ownerId = "")
    {
        $config = ZConfig::getField('cache', 'net');
        $cacheHelper = ZCache::getInstance($config['adapter'], $config);
        $key = $uid."_chr";
        $Channel = $cacheHelper->get($key);
        return $Channel;
    }
} 