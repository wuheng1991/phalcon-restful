<?php

namespace Api\models;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Model\manager;
use Phalcon\Mvc\Model\Query;



class GalaxyWechatClient extends \Phalcon\Mvc\Model
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
    public $nick_name;

    /**
     *
     * @var string
     */
    public $thumb;

    /**
     *
     * @var string
     */
    public $openid;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var integer
     */
    public $phone;

    /**
     *
     * @var string
     */
    public $phone_address;

    /**
     *
     * @var string
     */
    public $is_care;

    /**
     *
     * @var string
     */
    public $is_deleted;

    /**
     *
     * @var integer
     */
    public $sort;

    /**
     *
     * @var string
     */
    public $login_ip;

    /**
     *
     * @var string
     */
    public $last_login_time;

    /**
     *
     * @var string
     */
    public $create_time;

    /**
     *
     * @var string
     */
    public $update_time;
    public $vip;
    public $access_token;
    public $refresh_token;
    public $access_create_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'galaxy_wechat_client';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxyWechatClient[]|GalaxyWechatClient|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
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

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxyWechatClient|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function getData($id){
        $galaxyWechatClient = self::findFirstByid($id);
        if($galaxyWechatClient){
            $galaxyWechatClient->id = (int)$galaxyWechatClient->id;
            $galaxyWechatClient->is_care = (int)$galaxyWechatClient->is_care;
            $galaxyWechatClient->is_deleted = (int)$galaxyWechatClient->is_deleted;
            $galaxyWechatClient->sort = (int)$galaxyWechatClient->sort;
            $result=[
                'msg' => '客户详情详情',
                'code' => 200,
                'data' => $galaxyWechatClient->toArray(),
            ];
        }else{
            $result=[
                'msg' => '客户id不存在',
                'code' => 0,
                'data' => false
            ];
        }

        return $result;
    }

    public function saveData($id, $params){
        $galaxyWechatClient = self::findFirstByid($id);
        if($galaxyWechatClient){
            $galaxyWechatClient->id = (int)$galaxyWechatClient->id;
            $galaxyWechatClient->name = trim($params);
            $galaxyWechatClient->is_care = (int)$galaxyWechatClient->is_care;
            $galaxyWechatClient->is_deleted = (int)$galaxyWechatClient->is_deleted;
            $galaxyWechatClient->sort = (int)$galaxyWechatClient->sort;

            if($galaxyWechatClient->save()){
                $result=[
                    'msg' => '客户备注修改成功',
                    'code' => 200,
                    'data' => $galaxyWechatClient->toArray(),
                    'params' => $params,
                ];
            }else{
                $result=[
                    'msg' => '客户备注修改失败',
                    'code' => 0,
                    'data' =>  false,
                    'params' => $params,
                ];
            }
        }else{
            $result=[
                'msg' => '客户id不存在',
                'code' => 0,
                'data' => false
            ];
        }

        return $result;
    }

    public function searchData($params){
        $conditions = "";
        $bind = [];
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['page_size']) ? (int)$params['page_size'] : 10;
        $offset = ($page - 1) * $limit;

        if(isset($params['is_care']) && $params['is_care'] == 1){
            $conditions .= "is_care = :is_care:";
            $bind['is_care'] = $params['is_care'];
        }
        if(isset($params['phone_address']) && !empty($params['phone_address'])){
            if(!empty($conditions)){
                $conditions .= " AND phone_address like '%".trim($params['phone_address'])."%'";
            }else{
                $conditions .= "phone_address like '%".trim($params['phone_address'])."%'";
            }
        }
        if(isset($params['nick_name']) && !empty($params['nick_name'])){
            if(!empty($conditions)){
                $conditions .= " AND nick_name like '%".trim($params['nick_name'])."%'";
            }else{
                $conditions .= "nick_name like '%".trim($params['nick_name'])."%'";
            }
        }

        if(isset($params['name']) && !empty($params['name'])){
            if(!empty($conditions)){
                $conditions .= " AND name like '%".trim($params['name'])."%'";
            }else{
                $conditions .= "name like '%".trim($params['name'])."%'";
            }
        }
        //总数
        $count = self::count(array(
            'columns' => 'id',
            'conditions' => $conditions,
            'bind' => $bind,
        ));

        $ret = self::find(array(
            'conditions' => $conditions,
            'bind' => $bind,
            "order" => "last_login_time DESC",
            'limit' => $limit,
            'offset' => $offset,
        ));

        if($ret){
            $array  = $ret->toArray();
            if($array){
                foreach ($array as $key => $value){
                    $array[$key]['id'] = (int)$value['id'];
                    $array[$key]['is_care'] = (int)$value['is_care'];
                    $array[$key]['is_deleted'] = (int)$value['is_deleted'];
                    $array[$key]['sort'] = (int)$value['sort'];
                }
            }
        }else{
            $array = [];
        }

        $result=[
            'msg' => '客户搜索列表',
            'code' => 200,
            'count' => $count,
            'data' => $array,
        ];

        return $result;
    }

    public function countData(){
        #客户总数
        $clientSum = self::count(array('columns' => 'id'));
        #微信客户总数
        $wechatSum = self::count(array('columns' => 'id','conditions'=>"is_care='1'"));
        #昨日新增
        $start_time = strtotime(date("Y-m-d",strtotime("-1 day"))); //昨天开始时间戳
        $end_time = $start_time+24 * 60 * 60-1;//昨天结束时间戳
        $start_date = date("Y-m-d H:i:s", $start_time);
        $end_date = date("Y-m-d H:i:s", $end_time);

        $addSum = self::count(array(
            'columns' => 'id',
            'conditions' => "create_time >= '".$start_date."' AND create_time <= '".$end_date."'",
        ));

        $result=[
            'msg' => '客户-微信-昨日新增:总数',
            'code' => 200,
            'data' => [
                'client_sum' => $clientSum,
                'wechat_sum' => $wechatSum,
                'add_sum' => $addSum,
            ],
        ];

        return $result;
    }

    public function trendData($params){
        #type (1: 时间段 ; 2: 今日 ;3: 昨日 ;4: 最近7天增势 ;5: 最近30天增势)
        #client_sum 客户新增趋势  /  #wechat_sum 微信新增趋势
        $data = [];
        $msg = '';
        $time = time();
        $type = isset($params['type']) ? (int)$params['type'] : 4;
        if($type){
            switch ($type){
                case '1':
                    ##时间段
                    $start_date = $params['start_date'];//搜索开始日期
                    $end_date = $params['end_date'];//搜索结束日期

                    $start_time = strtotime($start_date);//搜索开始时间戳
                    $end_time = strtotime($end_date);//搜索结束时间戳
                    if($end_time >= $start_time){

                        $start_search_date = date("Y-m-d H:i:s", $start_time);
                        $end_search_date = date("Y-m-d H:i:s", $end_time + 24 * 60 * 60 - 1);

                        $conditions = "create_time >= '".$start_search_date."' AND create_time <= '".$end_search_date."'";
                        $searchData = self::find(array(
                            'columns' => 'id,is_care,create_time',
                            'conditions' => $conditions,
                            'order' => 'create_time ASC',
                        ))->toArray();

                        $diff_days = round(($end_time-$start_time)/3600/24);
                        #初始化日期
                        $date_array = array();
                        for($i = 0; $i <= $diff_days; $i ++){
                            $start_init_time = strtotime(date("Y-m-d", $start_time + $i * 24 * 60 * 60)); //昨天开始时间戳
                            $start_date = date("Y-m-d", $start_init_time);
                            $date_array[$start_date]=0;
                        }

                        $client_array = $wechat_array = $date_array;
                        #遍历获取每天新增数量
                        if($searchData){
                            foreach ($searchData as $k => $v) {
                                $datetime = substr($v['create_time'],0,10);//得到年月日
                                //得到每日新增客户数
                                if(array_key_exists($datetime,$client_array)){
                                    $client_array[$datetime] +=1;
                                }else{
                                    $client_array[$datetime] =1;
                                }
                                //得到每日新增微信客户数
                                if(array_key_exists($datetime,$wechat_array) && $v['is_care']==1){
                                    $wechat_array[$datetime] +=1;
                                }
                            }
                        }

                        $data = [];
                        if($date_array){
                            foreach($date_array as $key=>$value){
                                $data[]=array(
                                    '日期'=>$key,
                                    '客户'=>$client_array[$key],
                                    '微信客户'=>$wechat_array[$key],
                                );
                            }
                        }
                        $code = 200;
//                        $data = array(
//                            'client_sum' =>$client_array,
//                            'wechat_sum' =>$wechat_array,
//                        );

                    }else{
                        $code = 0;
                        $data = '搜索开始日期不能大于搜索结束日期';
                    }

                    $msg = '时间段:'.$start_date.'到'.$end_date.'客户新增趋势';
                    $result=[
                        'msg' => $msg,
                        'code' => $code,
                        'data' => $data,
                    ];
                    break;
                case '2':
                    ##今日
                    $msg = '今日客户新增趋势';
                    $start_time = strtotime(date("Y-m-d", $time)); //今天开始时间戳
                    $end_time = $start_time+24 * 60 * 60-1;//今天结束时间戳
                    $start_date = date("Y-m-d H:i:s", $start_time);
                    $end_date = date("Y-m-d H:i:s", $end_time);

                    $data = $this->getTrendDayData($start_date, $end_date);
                    $result=[
                        'msg' => $msg,
                        'code' => 200,
                        'data' => $data,
                    ];
                    break;
                case '3':
                    ##昨日
                    $msg = '昨日客户新增趋势';
                    $start_time = strtotime(date("Y-m-d",strtotime("-1 day"))); //昨天开始时间戳
                    $end_time = $start_time+24 * 60 * 60-1;//昨天结束时间戳
                    $start_date = date("Y-m-d H:i:s", $start_time);
                    $end_date = date("Y-m-d H:i:s", $end_time);

                    $data = $this->getTrendDayData($start_date, $end_date);
                    $result=[
                        'msg' => $msg,
                        'code' => 200,
                        'data' => $data,
                    ];
                    break;
                case '4':
                    ##最近7天增势
                    $msg = '最近7天客户新增趋势';
                    $num = 7;
                    $data = $this->getTrendDateData($num);
                    $result=[
                        'msg' => $msg,
                        'code' => 200,
                        'data' => $data,
                    ];
                    break;
                case '5':
                    ##最近30天增势
                    $msg = '最近30天客户新增趋势';
                    $num = 30;
                    $data = $this->getTrendDateData($num);
                    $result=[
                        'msg' => $msg,
                        'code' => 200,
                        'data' => $data,
                    ];
                    break;

                default:
                    ##code
                    break;
            }
        }

        return $result;
    }

    public function getTrendDayData($start_date, $end_date){
        $conditions = "create_time >= '".$start_date."' AND create_time <= '".$end_date."'";
        $data = self::find(array(
            'columns' => 'id,is_care',
            'conditions' => $conditions,
            'order' => 'create_time ASC',
        ))->toArray();
        $temp = 0;
        if($data){
            foreach($data as $key => $value){
                if($value['is_care']==1){
                    $temp += 1;
                }
            }
        }
        $datetime = substr($start_date,0,10);//得到年月日
        $data = array(
            '日期' => $datetime,
            '客户' => count($data),
            '微信客户' =>  $temp,
        );
        return $data;
    }

    public function getTrendDateData($num){
        $start_time = strtotime(date("Y-m-d",strtotime("-$num day"))); //昨天开始时间戳
        $end_time = $start_time+ $num * 24 * 60 * 60-1;//昨天结束时间戳
        $start_date = date("Y-m-d H:i:s", $start_time);
        $end_date = date("Y-m-d H:i:s", $end_time);

        $conditions = "create_time >= '".$start_date."' AND create_time <= '".$end_date."'";
        $searchData = self::find(array(
            'columns' => 'id,is_care,create_time',
            'conditions' => $conditions,
            'order' => 'create_time ASC',
        ))->toArray();

        #初始化日期
        $date_array = array();
        for($i = $num; $i > 0; $i --){
            $day = "-".$i." day";
            $start_time = strtotime(date("Y-m-d",strtotime($day))); //昨天开始时间戳
            $start_date = date("Y-m-d", $start_time);
            $date_array[$start_date]=0;
        }

        $client_array = $wechat_array = $date_array;
        #遍历获取每天新增数量
        if($searchData){
            foreach ($searchData as $k => $v) {
                $datetime = substr($v['create_time'],0,10);//得到年月日
                //得到每日新增客户数
                if(array_key_exists($datetime,$client_array)){
                    $client_array[$datetime] +=1;
                }else{
                    $client_array[$datetime] =1;
                }
                //得到每日新增微信客户数
                if(array_key_exists($datetime,$wechat_array) && $v['is_care']==1){
                    $wechat_array[$datetime] +=1;
                }
            }
        }

        $data = [];
        if($date_array){
            foreach($date_array as $key=>$value){
                $data[]=array(
                    '日期'=>$key,
                    '客户'=>$client_array[$key],
                    '微信客户'=>$wechat_array[$key],
                );
            }
        }
//        $data = array(
//            'client_sum' =>$client_array,
//            'wechat_sum' =>$wechat_array,
//        );
        return $data;
    }

    public function exportExcelData($params){
        #1:导出全部；2:导出已选择
        $type = isset($params['type'])? (int)$params['type'] : 1;
        $values = isset($params['values']) ? trim($params['values']) : '';
        $conditions = "";
        $bind = [];

        if($type == 2){
            $value_array = explode(',',trim($values));#客户ids
            if($value_array){
                $conditions = "id IN ({idlist:array})";
                $bind = ['idlist'=> $value_array];
            }
        }

        $ret = self::find(array(
            'conditions' => $conditions,
            'bind' => $bind,
            "order" => "last_login_time DESC",
        ));

        //新建execl
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        // Set properties
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '微信昵称')
            ->setCellValue('B1', '备注名称')
            ->setCellValue('C1', '是否关注公众号')
            ->setCellValue('D1', '手机号')
            ->setCellValue('E1', '手机归属地')
            ->setCellValue('F1', '可用积分')
            ->setCellValue('G1', '创建时间')
            ->setCellValue('H1', '最后登陆时间');
        if($ret){
            $array  = $ret->toArray();
            if($array){
                $i = 2;
                foreach($array as $key => $value){
                    $is_case = ($value['is_care'] == '1' ? '已关注' : '未关注');
                    $nick_name = json_encode($value['nick_name']);
                    $nick_name = preg_replace("/\\\u[ed][0-9a-f]{3}\\\u[ed][0-9a-f]{3}/","*",$nick_name);//替换成*
                    $nick_name = json_decode($nick_name);

                    $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$i,$nick_name);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('B'.$i,trim($value['name']));
                    $objPHPExcel->getActiveSheet(0)->setCellValue('C'.$i,$is_case);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('D'.$i,$value['phone']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('E'.$i,trim($value['phone_address']));
                    $objPHPExcel->getActiveSheet(0)->setCellValue('F'.$i,$value['points']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('G'.$i,$value['create_time']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('H'.$i,$value['last_login_time']);

                    $i ++;
                }
            }
        }
        //sheet命名
        $objPHPExcel->getActiveSheet()->setTitle('wechat-client-table');

        $savename='wechat-client-table-'.date("Ymd");
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $datetime = date('Y-m-d', time());
        if (preg_match("/MSIE/", $ua)) {
            $savename = urlencode($savename); //处理IE导出名称乱码
        }

        //excel头参数
        header('Content-Type: application/vnd.ms-excel;charset=utf-8');
        header('Content-Disposition: attachment;filename="'.$savename.'.xls"');  //日期为文件名后缀
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  //excel5为xls格式，excel2007为xlsx格式
        $objWriter->save('php://output');
        exit;
    }

}
