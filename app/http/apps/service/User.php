<?php
namespace service;

use common\utils,
    common\service,
    common\entity;

class User extends service\Base
{

    public function __construct()
    {
        $this->dao = utils\loadClass::getDao('common\\dao\\User');
    }

    public function checkUser($username, $password)
    {
        $userInfo = $this->fetchAll(array(
                "username"=>"'{$username}'",
                "password"=>"'{$password}'",
            )
        );
        if(empty($userInfo)) {
            return false;
        } else {
            return $userInfo[0];
        }
    }

    public function addUser($username, $password, $icon)
    {
        if($this->checkUser($username, $password)) {
            return false;
        }
        $entity = new entity\User();
        $entity->username = $username;
        $entity->password = $password;
        $entity->icon = $icon;
        return $this->add($entity);
    }
} 