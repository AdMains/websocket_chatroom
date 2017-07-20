<?php

namespace ctrl\main;

use common\ctrl;
use common\utils;

class chat extends ctrl\Base
{
    public function check()
    {
        $uid = $this->getInteger($this->params, 'uid');
        $token = $this->getString($this->params, 'token');
        $channel = $this->getString($this->params, 'channel');

        return utils\loadClass::getService('service\\Chat')->check($uid, $token,$channel);
    }

    public function msg()
    {
        $toId = $this->getInteger($this->params, 'toId');
        $msg = $this->getString($this->params, 'msg');
        $channel = $this->getString($this->params, 'channel');
        utils\loadClass::getService('service\\Chat')->msg($toId, $msg,$channel);
    }

    public function online()
    {
        $channel = $this->getString($this->params, 'channel');
        utils\loadClass::getService('service\\Chat')->getOnlineList($channel);
    }

    public function offline()
    {
        utils\loadClass::getService('service\\Chat')->offline();
    }

    public function newChannel()
    {
        $fromId = $this->getInteger($this->params, 'fromId');
        $channel = $this->getString($this->params, 'channel');
        utils\loadClass::getService('service\\Chat')->newChannel($fromId, $channel);
    }

    public function getChannel()
    {
        utils\loadClass::getService('service\\Chat')->getChannel();
    }

    public function delChannel()
    {
        $ownerId = $this->getInteger($this->params, 'ownerId');
        $channel = $this->getString($this->params, 'channel');
        utils\loadClass::getService('service\\Chat')->delChannel($ownerId, $channel);
    }
}
