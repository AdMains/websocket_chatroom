<?php

namespace common\dao;

use ZPHP\Db\Pdo as ZPdo;

abstract class Base
{
    private $entity;
    /**
     * @var ZPdo
     */
    private $_db = null;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    public function useDb()
    {
        $this->_db =  ZPdo::getInstance();
        $this->_db->setClassName($this->entity);
        return $this->_db;
    }
    //检查数据库里的用户信息
    public function fetchAll(array $items=[])
    {
        $this->useDb();
        if(empty($items)) {
            return $this->_db->fetchAll();
        }
        $where = "1";
        foreach ($items as $k => $v) {
            $where .= " and {$k}={$v}";
        }
        return $this->_db->fetchAll($where);
    }
    //写入新用户信息到数据库
    public function add($attr)
    {
        $this->useDb();
        return $this->_db->replace($attr, \array_keys(\get_object_vars($attr)));
    }

    /*public function closeDb()
    {
        if(!empty($this->_db)) {
            $this->_db->close();
        }
    }*/

    public function fetchById($id)
    {
        $this->useDb();
        return $this->_db->fetchEntity("id={$id}");
    }



    public function fetchWhere($where='')
    {
        $this->useDb();
        return $this->_db->fetchAll($where);
    }

    /*public function update($attr)
    {
        $fields = array();
        $params = array();
        foreach ($attr as $key => $val) {
            $fields[] = $key;
            $params[$key] = $val;
        }
        $this->useDb();
        return $this->_db->update($fields, $params, 'id=' . $attr->id);
    }*/


    /*public function remove($where)
    {
        $this->useDb();
        $this->dbHelper->remove($where);
    }*/
}
