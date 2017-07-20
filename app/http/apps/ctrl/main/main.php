<?php
namespace ctrl\main;
use common\ctrl,
    common\utils,
    ZPHP\Core\Config  as ZConfig;

class main extends ctrl\Base
{
    public function main()
    {
        $token = $this->getString($this->params, 'token', '');
        $uid = $this->getString($this->params, 'uid', '');//当前请求用户

        $ownerId = $this->getString($this->params, 'ownerId', '0');//房主
        $channel = $this->getString($this->params, 'channelName', 'ALL');//频道，默认为ALL

        if(utils\Utils::checkToken($uid, $token)) {
            return array(
                'uid'=>$uid,
                'token'=>$token,
                'static_url'=>ZConfig::getField('project', 'static_url'),
                'app_host'=>ZConfig::getField('project', 'app_host'),
                'defaultChannel' => $channel,
                'ownerId' => $ownerId
            );
        }
        return utils\Utils::jump("main\\main", "login", array(
            "msg"=>"需要登录"
        ));
    }

    //目前为了避免重新创建一个模板文件，将下述方法暂时耦合到上述main方法中
    /*public function enterChannel()
    {
        $token = $this->getString($this->params, 'token', '');
        $uid = $this->getString($this->params, 'uid', '');
        $ownerId = $this->getString($this->params, 'ownerId', '');
        $channel = $this->getString($this->params, 'channelName', '');
        if(utils\Utils::checkToken($uid, $token)) {
            return array(
                'uid'=>$uid,
                'token'=>$token,
                'static_url'=>ZConfig::getField('project', 'static_url'),
                'app_host'=>ZConfig::getField('project', 'app_host'),
                'defaultChannel' => $channel,
                'ownerId' => $ownerId
            );
        }
        return utils\Utils::jump("main\\main", "login", array(
            "msg"=>"需要登录"
        ));
    }*/

    public function login()
    {
        return array(
            'static_url'=>ZConfig::getField('project', 'static_url'),
        );
    }

    public function reg()
    {
        return array(
            'static_url'=>ZConfig::getField('project', 'static_url'),
        );
    }

    public function savereg()
    {
        $username = $this->getString($this->params, 'username');
        $password = $this->getString($this->params, 'password');
        $icon = $this->getString($this->params, 'icon', 'icon.jpg');//可扩展上传图像

        $service = utils\loadClass::getService('service\\User');
        $result = $service->addUser($username, $password, $icon);
        if($result) {
            return utils\Utils::jump("main\\main", "main", array(
                "msg"=>"注册成功"
            ));
        }

        return utils\Utils::showMsg("注册失败");
    }

    public function check()
    {
        $username = $this->getString($this->params, 'username');
        $password = $this->getString($this->params, 'password');
        $service = utils\loadClass::getService('service\\User');
        $userInfo = $service->checkUser($username, $password);
        if(!empty($userInfo)) {
            $token = utils\Utils::setToken($userInfo->id);
            return utils\Utils::jump("main\\main", "main", array(
                'uid'=>$userInfo->id,
                'token'=>$token,
            ));
        }

        return utils\Utils::showMsg("登录失败，请重试");
    }

}

