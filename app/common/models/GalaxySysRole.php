<?php

namespace Api\models;

class GalaxySysRole extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $description;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'galaxy_sys_role';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxySysRole[]|GalaxySysRole|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxySysRole|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * 查询多条记录
     * @param string $where
     * @param string $field
     * @param string $order
     * @param array $page
     * @return mixed
     */
    public function findall($where="",$field="*",$order="id DESC",$page=array()){
        $conditons = "";
        if(!empty($where["where"])){
            $conditons = $where["where"];
        }
        $parameters = array();
        if(!empty($where["value"])){
            foreach($where["value"] as $k=>$v){
                $parameters[$k] = $v;
            }
        }
        $conditon_arr = [
            $conditons,
            'bind' => $parameters,
            'columns' => $field,
            'order'=>$order,
        ];
        if(isset($page["offset"])){
            $conditon_arr["offset"] = $page["offset"];
        }
        if(isset($page["limit"])){
            $conditon_arr["limit"] = $page["limit"];
        }
        $ret = self::find($conditon_arr);
        if($ret){
            return $ret->toArray();
        }else{
            return [];
        }

    }

}
