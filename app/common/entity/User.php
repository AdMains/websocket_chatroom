<?php

namespace common\entity;

/**
 * Class User
 * @package entity
 * 用户信息
 */
class User
{
    //下面的常量定义是必须的，数据库表名
    const TABLE_NAME = 'user';

    //下面属性定义是必须的，表中字段
    public $id;             //用户id
    public $username;       //用户名称
    public $password;       //用户密码
    public $icon;           //用户头像

    //返回数据到聊天室
    public function hash()
    {
        return array(
            $this->id,
            $this->username,
            $this->icon
        );
    }
}