<?php

namespace common\dao;


class User extends Base {

    public function __construct()
    {
        parent::__construct('common\\entity\\User');
    }

} 