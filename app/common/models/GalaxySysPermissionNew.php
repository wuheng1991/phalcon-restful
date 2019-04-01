<?php

namespace Api\models;
use Api\Models\GalaxyRoleAuth;

class GalaxySysPermissionNew extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $pid;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $checked;

    /**
     *
     * @var string
     */
    public $open;

    /**
     *
     * @var string
     */
    public $type;

    /**
     *
     * @var integer
     */
    public $status;

    /**
     *
     * @var string
     */
    public $url;

    /**
     *
     * @var integer
     */
    public $level;

    /**
     *
     * @var string
     */
    public $platform;

    /**
     *
     * @var string
     */
    public $title;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'galaxy_sys_permission_new';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxySysPermissionNew[]|GalaxySysPermissionNew|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxySysPermissionNew|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function getAttributes(){

        return $this -> getModelsMetaData() -> getAttributes($this);
    }

    public function listData(){
        $obj = self::find(array(
//            'columns' => 'id',
            'conditions' => "status = 1 AND platform = '1'",
        ));
        if($obj){
            return $obj->toArray();
        }else{
            return [];
        }
    }

    /**
     * 新增数据
     * @param $data
     * @param bool $return_id
     * @return bool
     */
    public function add($data,$return_id = false){
        if(empty($data)){
            return false;
        }

        $add_res = $this->create($data);
        if( $add_res && $return_id){
            return  $insertId = $this ->id;
        }else{
            return $add_res;
        }
    }

    public function saveData($data){
        if($this->save($data)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 查询单条记录
     * @param string $where
     * @param string $field
     * @param string $type
     * @return GalaxyWechatClient|\Phalcon\Mvc\Model\ResultInterface
     */
    public function findone($where="",$field="*",$type="obj"){
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
        ];
        if($type == "array"){
            return  $this->findFirst($conditon_arr)->toArray();
        }
        return  $this->findFirst($conditon_arr);
    }

    /**
     * 查询多条记录
     * @param string $where
     * @param string $field
     * @param string $order
     * @param array $page
     * @return mixed
     */
    public static function findall($where="",$field="*",$order="id DESC",$page=array()){
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
        $ret = self::find($conditon_arr)->toArray();
        return $ret;
    }

//    public function addAuthAssignmentData($params){
//        $galaxyRoleAuthmodel = new GalaxyRoleAuth();
//
//        if($galaxyRoleAuthmodel->create($params)){
//            return true;
//        }else{
//            return false;
//        }
//    }

    public function getAuthAssignmentData($role_id){
        $where = array("roleid = $role_id AND platform = '1'");
        $galaxyRoleAuthmodel = GalaxyRoleAuth::findFirst($where);
        return $galaxyRoleAuthmodel;
    }

    public function getAuthAssignmentTreeData($authids){
        $array = explode(',',trim($authids));
        $conditions = "id IN ({idlist:array})";
        $bind = ['idlist'=> $array];
        $ret = GalaxySysPermissionNew::find(array(
            'columns' => 'id,pid,name,level,title',
            'conditions' => $conditions,
            'bind' => $bind,
        ));
        if($ret){
             return $ret->toArray();
        }else{
            return [];
        }
    }

    public function saveAuthAssignmentData($params){
        $ret = $this->getAuthAssignmentData($params['roleid']);
        if($ret){
            if($ret->save($params)){
                return true;
            }else{
                return false;
            }
        }else{
            $galaxyRoleAuthmodel = new GalaxyRoleAuth();
            if($galaxyRoleAuthmodel->create($params)){
                return true;
            }else{
                return false;
            }
        }
    }

}
