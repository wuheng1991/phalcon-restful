<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/11
 * Time: 17:45
 */
namespace Api\models;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;

class GalaxyOrderReveivedRecord extends \Phalcon\Mvc\Model
{

    protected static $return; //存放正确的返回数据;
    protected static $error; //存放错误的返回数据;

    /**
     * [getFindAll 获取全部的数据]
     * @param  string $select [查詢的字段]接收的值,例子:$select = '*';
     * @param  array $where [查詢的條件]接收的值,例子:$where['where'] = '';$where['value'] = '';
     * @param  array $toLimit [分頁的數據]接收的值,例子:$toLimit["page"] = '';$toLimit["page_size"] = '';
     * @param  array $toBy [分組及排序]接收的值,例子:$toBy['orderby'] = array("id DESC","create_tiem DESC");$toBy['groupby'] = array("id");
     * @return [type]          [成功返回查詢結果]
     */
    public function getFindAll($select = '', $where = array(), $toLimit = array(), $toBy = array())
    {
        try {
            //检查参数
            $select = empty($select) ? '*' : $select;
            $robot = $this->query()->columns($select)->where($where["where"])->bind($where["value"]);
            //判断是否需要分页
            if (!empty($toLimit)) {
                $robot = $robot->limit($toLimit['page'], $toLimit['page_size']);
            }
            //判断是否有排序
            if (!empty($toBy['orderby'])) {

                //不为数据的操作方式
                if (!is_array($toBy['orderby'])) {
                    $robot = $robot->orderBy($toBy['orderby']);
                } else {
                    //数组的操作方式
                    foreach ($toBy['orderby'] as $key => $value) {
                        $robot = $robot->orderBy($key);
                    }
                }
            }

            //判断是否有分组
            if (!empty($toBy['groupby'])) {

                //不为数据的操作方式
                if (!is_array($toBy['groupby'])) {
                    $robot = $robot->groupBy($toBy['groupby']);
                } else {
                    //数组的操作方式
                    foreach ($toBy['groupby'] as $key => $value) {
                        $robot = $robot->groupBy($value);
                    }
                }
            }
            static::$return = $robot->execute();
            //每一次正确的查询初始化掉错误的记录
            static::$error = '';
        } catch (\PDOException $e) {
            //存放查询错误的数据
            static::$error = $e->getMessage();
        }

//        try{
//
//            //检查参数
//            $select = empty($select)?'*':$select;
//
//            $robot = $this->query()->columns($select)->where($where['where'])->bind($where['value']);
//
//            //判断是否需要分页
//            if(!empty($toLimit)){
//                $robot = $robot->limit($toLimit['page'],$toLimit['page_size']);
//            }
//
//            //判断是否有排序
//            if(!empty($toBy['groupby'])){
//
//                //不为数据的操作方式
//                if(is_array($toBy['orderby'])){
//                    $robot = $robot->order($toBy['orderby']);
//                }else{
//                    //数组的操作方式
//                    foreach ($toBy['orderby'] as $key => $value) {
//                        $robot = $robot->order($value);
//                    }
//                }
//            }
//
//            //判断是否有分组
//            if(!empty($toBy['groupby'])){
//
//                //不为数据的操作方式
//                if(!is_array($toBy['groupby'])){
//                    $robot = $robot->group($toBy['groupby']);
//                }else{
//                    //数组的操作方式
//                    foreach ($toBy['groupby'] as $key => $value) {
//                        $robot = $robot->group($value);
//                    }
//                }
//            }
//
//            static::$return =  $robot->execute();
//
//            //每一次正确的查询初始化掉错误的记录
//            static::$error = '';
//        }catch(\PDOException $e){
//            //存放查询错误的数据
//            static::$error = $e->getMessage();
//        }
    }

    /**
     * [getFindOne 获取一条查询结果]
     * @param  string $select [查詢的字段]接收的值,例子:$select = '*';
     * @param  array $where [查詢的條件]接收的值,例子:$where['where'] = '';$where['value'] = '';
     * @return [type]         [成功返回查詢結果]
     */
    public function getFindOne($select = '', $where = array())
    {
        try {

            //检查参数
            $select = empty($select) ? '*' : $select;

            $robot = $this->query()->columns($select)->where($where['where'])->bind($where['value']);

            static::$return = $robot->execute();

            //每一次正确的查询初始化掉错误的记录
            static::$error = '';
        } catch (\PDOException $e) {
            //存放查询错误的数据
            static::$error = $e->getMessage();
        }
    }

    /**
     * [createData 新增數據]
     * @param  array $data [新增的數據]接收的值,例子:$addData['field'] = 'test';
     * @return [type]       [成功返回新增后的id值]
     */
    public function createData($addData = array())
    {

        try {
            //获取表字段
            $field = $this->getFieldDate();

            //初始化表不存在的字段
            $noField = '';

            //判断新增参数是否包含表字段里
            foreach ($addData as $key => $value) {
                if (!in_array($key, $field)) {
                    $noField = $key;
                    break;
                }

                //重組對象，防止sql注入
                $this->$key = $value;
            }
            //判断是否有参数不存在的异常
            if (empty($noField)) {

                if (($create = $this->create()) === false) {

                    //循環組裝錯誤的數據
                    foreach ($this->getMessages() as $message) {
                        static::$error .= "Message: " . $message->getMessage() . '/';
                    }
                } else {
                    //若新增成功则返回新增成功的id
                    return static::$return = $this->id;
                    //每一次正确的新增初始化掉错误的记录
                    static::$error = '';
                }
            } else {
                static::$error = 'Message:Generated when a field part of a virtual foreign key [' . $noField . '] is trying to insert';
            }
            //获取表的数据
        } catch (\PDOException $e) {
            //存放查询错误的数据
            static::$error = $e->getMessage();
        }
    }

    /**
     * [saveData 保存數據]
     * @param  array $where [修改的條件]接收的值,例子:$where['where'] = '';$where['value'] = '';
     * @param  array $updateData [修改的值  ]接收的值,例子:$addData['field'] = 'test';
     * @return [type]             [description]
     */
    public function saveData($where = array(), $updateData = array())
    {

        try {
            //获取表字段
            $field = $this->getFieldDate();

            //檢索數據
            $robot = $this->query()->where($where['where'])->bind($where['value'])->execute();

            //判断是否有参数不存在的异常
            if (($update = $robot->update($updateData)) === false) {
                //循環組裝錯誤的數據
                foreach ($this->getMessages() as $message) {
                    static::$error .= "Message: " . $message->getMessage() . '/';
                }
            } else {
                //若新增成功则返回新增成功的id
                static::$return = 1;
                //每一次正确的新增初始化掉错误的记录
                static::$error = '';
            }

        } catch (\PDOException $e) {
            //存放查询错误的数据
            static::$error = $e->getMessage();
        }

    }

    /**
     * [getSucceedResult 获取正确的数据]
     * @param  integer $type 为1时转成数组的形式，为0时保持对象的形式
     * @return [type]        [description]
     */
    public function getSucceedResult($type = 0)
    {
        if ($type == 1) {
            $return = static::$return->toArray();
        } else {
            $return = static::$return;
        }
        return $return;
    }

    /**
     * [getErrorResult 获取错误的数据]
     * @return [type] [description]
     */
    public function getErrorResult()
    {
        return static::$error;
    }

    /**
     * [getFieldDate 获取表字段信息]
     * @return [type] [description]
     */
    public function getFieldDate()
    {
        return $this->getModelsMetaData()->getAttributes($this);
    }

    /**
     * 获取查询结果数
     * @param string $where
     * @return mixed
     */
    public function getCount($where = "")
    {
        $conditons = "";
        if (!empty($where["where"])) {
            $conditons = $where["where"];
        }
        $parameters = array();
        if (!empty($where["value"])) {
            foreach ($where["value"] as $k => $v) {
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
     * 查询多条记录
     * @param string $where
     * @param string $field
     * @param string $order
     * @param array $page
     * @return mixed
     */
    public function findall($where = "", $field = "*", $order = "id DESC", $page = array())
    {

        //封装数据库的条件查询
        $conditons = "";

        if (!empty($where["where"])) {
            $conditons = $where["where"];
        }
        $parameters = array();
        if (!empty($where["value"])) {
            foreach ($where["value"] as $k => $v) {
                $parameters[$k] = $v;
            }
        }

        $conditon_arr = [
            $conditons,
            'bind' => $parameters,
            'columns' => $field,
            'order' => $order,
        ];

        if (isset($page["offset"])) {
            $conditon_arr["offset"] = $page["offset"];
        }

        if (isset($page["limit"])) {
            $conditon_arr["limit"] = $page["limit"];
        }
        $ret = self::find($conditon_arr);
        return $ret;
    }

    /**
     * 获取查询结果数求和
     * @param string $where
     * @param string $field
     * @return mixed
     */
    public function getSum($where = "", $field = 'id')
    {
        $conditons = "";
        if (!empty($where["where"])) {
            $conditons = $where["where"];
        }
        $parameters = array();
        if (!empty($where["value"])) {
            foreach ($where["value"] as $k => $v) {
                $parameters[$k] = $v;
            }
        }
        $sum = self::sum([
            $conditons,
            'bind' => $parameters,
            'column' => $field,
        ]);
        return $sum;
    }
}