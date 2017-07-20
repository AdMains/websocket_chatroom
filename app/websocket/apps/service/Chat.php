<?php
namespace service;

use common\service,
    common\utils;
use ctrl\Cmd;
use ZPHP\Protocol\Request;
use ZPHP\Conn\Factory as ZConn;
use ZPHP\Core\Config as ZConfig;

class Chat extends service\Base
{

    private function getConn()
    {
        return ZConn::getInstance('Redis', ZConfig::get('connection'));
    }

    public function check($uid, $token,$channel)
    {
        //目前每一个$uid有且只能同时在一个频道了，Redis中的值会在每次进入频道时重置
        //如果后期开发多个频道同时连接，这里可以加上一个参数$ownerId以区分
        utils\Utils::setChannel($uid, $channel);

        $fd = Request::getFd();
        if(utils\Utils::checkToken($uid, $token)) {
            $this->dao = utils\loadClass::getDao('common\\dao\\User');
            $uinfo = $this->fetchById($uid);
            if(!empty($uinfo)) {  //登录成功
                $oinfo = $this->getConn()->get($uinfo->id);
                if(!empty($oinfo) && $oinfo['fd']!=$fd ) {//重复登录
                    $this->sendOne($oinfo['fd'], Cmd::RELOGIN, []);
                    $this->getConn()->delete($oinfo['fd'], $uid);
                    $this->close($oinfo['fd']);
                }

                $this->getConn()->add($uid, $fd);//单用户维持多个频道连接的关键在于此处的$uid，需在ZPHP框架里扩展代码
                $this->getConn()->addFd($fd, $uid);

                //注：此处当从非ALL频道切换到另一个非ALL频道时，客户端首先会断开连接，执行offLine，从而执行delete($fd, $uid)
                if($channel !='ALL'){
                    $this->getConn()->addChannel($uid,$channel);
                    $this->getConn()->delChannel($uid,'ALL');
                }

                $this->sendToChannel(Cmd::LOGIN_SUCC, $uinfo->hash(),$channel);
                return null;
            }
        }
        $this->sendOne(Request::getFd(), Cmd::LOGIN_ERROR);
        Request::getSocket()->close($fd);
    }

    public function sendOne($fd, $cmd, $data=[])
    {
        if (empty($fd) || empty($cmd)) {
            return;
        }
        $data = json_encode(array($cmd, $data));
        //当刷新页面时，会向一个由于刷新页面而关闭的websocket客户端发送信息，此处使用@抑制该报错。
        return @ Request::getSocket()->push($fd, $data);
    }

    public function sendToChannel($cmd, $data, $channel = 'ALL')//$channel是哈希表的表名，即当前组名
    {
        $list = $this->getConn()->getChannel($channel);
        if (empty($list)) {
            return;
        }
        foreach ($list as $fd) {
            $this->sendOne($fd, $cmd, $data);
        }
    }

    public function getOnlineList($channel)
    {
        $olUids = utils\Utils::online($channel);
        if(empty($olUids)) {
            return array();
        }
        $idsArr = \array_keys($olUids);//所有的$uid
        $where = "id in (".implode(',', $idsArr).")";
        $this->dao = utils\loadClass::getDao('common\\dao\\User');
        $userInfo = $this->fetchWhere($where);
        $result = array();
        foreach($userInfo as $user) {
            $result[$user->id] = $user->hash();
        }
        $this->sendOne(Request::getFd(), Cmd::OLLIST, $result);
    }

    public function offline()
    {
        $fd = Request::getFd();
        $uid = $this->getConn()->getUid($fd);
        $channel = utils\Utils::getChannel($uid);

        $this->getConn()->delete($fd, $uid);
        $this->sendToChannel(Cmd::LOGOUT, array($uid),$channel);
    }

    public function close($fd)
    {
        Request::getSocket()->close($fd);
    }

    public function msg($toId, $msg, $channel)
    {
        $fd = Request::getFd();
        $uid = $this->getConn()->getUid($fd);
        if(empty($toId)) {  //公共聊天
            $this->sendToChannel(Cmd::CHAT, array($uid, $msg, $toId),$channel);
        } else { //私聊
            $toInfo = $this->getConn()->get($toId);
            if(!empty($toInfo)) {
                $this->sendOne($toInfo['fd'], Cmd::CHAT, array($uid, $msg, $toId));//服务器把信息发给接收者
                $this->sendOne($fd, Cmd::CHAT, array($uid, $msg, $toId));//服务器把信息发给发送者
            }
        }
    }

    /**
     * 两类set集合，
     * 第一类集合仅一个set集合，存放创建过频道的用户，channel=>(user1,user2,user3...)
     * 第二类集合包含若干个set集合，为每一个用户创建的频道信息，
     * user1=>(channel1,channel2,...)
     * user2=>(channel1,channel2,...)
     */
    public function newChannel($fromId, $channel)
    {
        $this->getConn()->addChannelOwner($fromId);
        $this->getConn()->addNewChanneltoOwner($fromId, $channel);//此处添加创建重名频道的判断
        $this->sendToChannel(Cmd::NEWCHANNEL,array($fromId, $channel));//此处添加向所有频道推送新频道创建的信息
    }

    public function getChannel()
    {
        $fd = Request::getFd();
        $owners = $this->getConn()->getChannelOwner();
        $rst = array();
        if(!empty($owners)){
            foreach($owners as $uid){
                $rst[$uid] = $this->getConn()->getNewChannel($uid);
            }
        }
        $this->sendOne($fd,Cmd::GETCHANNEL,$rst);
    }
    public function delChannel($ownerId, $channel)
    {
        if($this->getConn()->delNewChannel($ownerId, $channel)){

            $this->sendToChannel(Cmd::DELCHANNEL,array($ownerId, $channel));//发送到ALL频道

            //还需要断开该频道内所有的连接
            $list = $this->getConn()->getChannel($channel);
            if (empty($list)) {
                return;
            }
            foreach ($list as $fd) {
                $this->sendOne($fd, Cmd::DELCHANNEL, array($ownerId, $channel));//发送到频道内的每个用户之后再断开连接
                $uid = $this->getConn()->getUid($fd);
                $this->getConn()->delete($fd, $uid);
                $this->close($fd);
            }
        }
    }
} 