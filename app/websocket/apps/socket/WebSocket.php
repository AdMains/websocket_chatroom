<?php
/**
 * 0、定义好客户端和服务器端通讯的格式
 * 1、把数据投递给task进程
 * 2、引入Cmd解析数据
 *    Request::parse(common\Cmd::parseData(json_decode($_data)));
 * 3、路由到Chat控制器里处理数据
 * $result = ZRoute::route();
 * 4、在Task进程直接里push数据
 */
namespace socket;
use ctrl\Cmd;
use ZPHP\Socket\Callback\SwooleWebSocket as ZSwooleWebSocket;
use ZPHP\Core\Config as ZConfig;
use ZPHP\Protocol\Request;
use ZPHP\Core\Route as ZRoute;

class WebSocket extends ZSwooleWebSocket
{
    private $buff = [];

    public function onOpen($server, $request)
    {
        $this->log($request->fd . " connect");

        $server->task([
            'cmd' => 'open',
            'fd' => $request->fd,
        ], 0);
    }

    public function onClose()
    {
        list($server, $fd, $fromId) = func_get_args();
        $this->log("{$fd} close");
        $server->task([
            'cmd' => 'close',
            'fd' => $fd
        ], 0);
    }

    public function onMessage($server, $frame)
    {
        if (empty($frame->finish)) { //数据未完
            if (empty($this->buff[$frame->fd])) {
                $this->buff[$frame->fd] = $frame->data;
            } else {
                $this->buff[$frame->fd] .= $frame->data;
            }
        } else {
            if (!empty($this->buff[$frame->fd])) {
                $frame->data = $this->buff[$frame->fd] . $frame->data;
                unset($this->buff[$frame->fd]);
            }
        }
        $server->task([
            'cmd' => 'message',
            'fd' => $frame->fd,
            'data' => $frame->data
        ], 0);
    }

    public function onTask($server, $taskId, $fromId, $data)
    {
        Request::setHttpServer(0);
        Request::setFd($data['fd']);
        switch ($data['cmd']) {
            case 'open'://欢迎信息，只给当前登录者发送
                $server->push($data['fd'], '{"0":'. Cmd::LOGIN . "}");
                break;
            case 'close'://下线提醒，告知除当前用户之外的所有人该用户下线
                Request::parse([
                    'a'=>'main/chat',
                    'm'=>'offline',
                ]);
                ZRoute::route();
                break;
            case 'message':
                Request::parse(Cmd::parseData(json_decode($data['data'], true)));
                ZRoute::route();
                break;
        }
    }

    public function log($msg)
    {
        if (!ZConfig::getField('socket', 'daemonize', 0)) {
            echo $msg . PHP_EOL;
        }
    }
}