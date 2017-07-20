<?php

namespace ctrl;


class Cmd
{
    const LOGIN = 1; //登录
    const LOGIN_SUCC = 2; //登录成功
    const RELOGIN = 3;      //重复登录
    const NEED_LOGIN = 4; //需要登录
    const LOGIN_ERROR = 5;  //登录失败
    const HB = 6;           //心跳
    const CHAT = 7;         //聊天
    const OLLIST = 8;       //获取在线列表
    const LOGOUT = 9;       //退出登录
    const TOKEN_ERROR = 10;       //校验失败
    const ERROR = -1;

    const NEWCHANNEL = 11;//创建新频道
    const GETCHANNEL = 12;//获取频道列表
    const DELCHANNEL = 13;

    public static function parseData($data)
    {
        switch ($data['type']) {
            case self::LOGIN:
                return [
                    'a'=>'main/chat',
                    'm'=>'check',
                    'uid'=>$data['uid'],
                    'token'=>$data['token'],
                    'channel'=>$data['channel'],
                ];
                break;
            case self::OLLIST:
                return [
                    'a'=>'main/chat',
                    'm'=>'online',
                    'ownerId'=>$data['message'][0],
                    'channel'=>$data['message'][1],
                ];
                break;
            case self::CHAT:
                return [
                    'a'=>'main/chat',
                    'm'=>'msg',
                    'toId'=>$data['message'][0],
                    'msg'=>$data['message'][1],
                    'channel'=>$data['message'][2],
                ];
                break;
            case self::NEWCHANNEL:
                return [
                    'a'=>'main/chat',
                    'm'=>'newChannel',
                    'fromId'=>$data['message'][0],
                    'channel'=>$data['message'][1],
                ];
                break;
            case self::GETCHANNEL:
                return [
                    'a'=>'main/chat',
                    'm'=>'getChannel',
                ];
                break;
            case self::DELCHANNEL:
                return [
                    'a'=>'main/chat',
                    'm'=>'delChannel',
                    'ownerId'=>$data['message'][0],
                    'channel'=>$data['message'][1],
                ];
            default:
                return null;
        }
    }

} 