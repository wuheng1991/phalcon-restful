<?php

namespace Api\Models;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Model\manager;
use Phalcon\Mvc\Model\Query;



class GalaxyMobilecodeLog extends \Phalcon\Mvc\Model
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
    public $mobile;

    /**
     *
     * @var string
     */
    public $send_time;

    /**
     *
     * @var string
     */
    public $is_used;

    /**
     *
     * @var string
     */
    public $result_code;

    /**
     *
     * @var integer
     */
    public $validate_code;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'galaxy_mobilecode_log';
    }


    /**
     * 查询单条记录
     * @param string $where
     * @param string $field
     * @param string $type
     * @return GalaxyWechatClient|\Phalcon\Mvc\Model\ResultInterface
     */
    public function findone($where="",$field="*"){
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
        return $this->findFirst($conditon_arr);
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
        $ret = self::find($conditon_arr)->toArray();
        return $ret;
    }

    /**
     * 获取查询结果数
     * @param string $where
     * @return mixed
     */
    public function getCount($where=""){
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
        $count = self::count([
            $conditons,
            'bind' => $parameters,
        ]);
        return $count;
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

    /**
     * 删除数据
     * @param $where
     * @return mixed
     */
    public function deletes($where){
        if(!empty($where["where"])){
            $conditons = $where["where"];
        }else{
            return false;
        }
        $parameters = array();
        if(!empty($where["value"])){
            foreach($where["value"] as $k=>$v){
                $parameters[$k] = $v;
            }
        }else{
            return false;
        }
        return  self::find([
            $conditons,
            'bind' => $parameters,
        ])->delete();
    }

    /**
     * 更新数据
     * @param $where
     * @param $update_data
     * @return bool
     */
    public function updates($where,$update_data){
        if(!empty($where["where"])){
            $conditons = $where["where"];
        }
        $parameters = array();
        if(!empty($where["value"])){
            foreach($where["value"] as $k=>$v){
                $parameters[$k] = $v;
            }
        }
        $data = $this->find([
            $conditons,
            'bind' => $parameters,
        ]);
        if(empty($data->toArray())){
            return false;
        }
        return $data->update($update_data);
    }

    /**
     * 执行原生sql
     * @param string $sql
     * @return bool
     */
    public function querysql($sql=""){
        if(empty($sql)){
            return false;
        }
        $result = $this->di->get("db")->fetchAll($sql);
        return $result;
    }

}
