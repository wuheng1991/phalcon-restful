<?php

namespace Api\models;
use Api\Models\GalaxyWechatClient;
use Api\Models\GalaxyAdmin;
use Api\Models\GalaxyCrmClient;
use Api\Models\GalaxyReorder;

class GalaxyWechatActivityClient extends \Phalcon\Mvc\Model
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
    public $galaxy_wechat_activity_id;

    /**
     *
     * @var integer
     */
    public $galaxy_wechat_client_id;

    /**
     *
     * @var string
     */
    public $sign_up_time;

    /**
     *
     * @var string
     */
    public $source;

    /**
     *
     * @var string
     */
    public $is_invited;

    /**
     *
     * @var string
     */
    public $is_sign_up;

    /**
     *
     * @var string
     */
    public $is_contracted;

    /**
     *
     * @var string
     */
    public $present_time;

    /**
     *
     * @var string
     */
    public $departure_time;

    /**
     *
     * @var integer
     */
    public $sort;

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

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'galaxy_wechat_activity_client';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxyWechatActivityClient[]|GalaxyWechatActivityClient|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxyWechatActivityClient|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
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
    public function findall($where="",$field="*",$order="id DESC",$page=array()){

        //封装数据库的条件查询
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
            return  $this ->id;
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



    //==============================================================================分割线==========================================================================

    public function searchData($id, $params){
//        $conditions = "";
//        $bind = [];
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['page_size']) ? (int)$params['page_size'] : 10;
        $offset = ($page - 1) * $limit;

        $alaxyWechatActivityClientModel = GalaxyWechatActivityClient::class;
        $galaxyWechatClientModel = GalaxyWechatClient::class;
        $galaxyAdminModel = GalaxyAdmin::class;

//        $countPhql="SELECT A.id from {$alaxyWechatActivityClientModel} as A WHERE A.galaxy_wechat_activity_id=".$id;
//        $countRet = $this->modelsManager->executeQuery($countPhql);
//        $count = count($countRet);
        $count= self::count(array('columns' => 'id','conditions'=>'galaxy_wechat_activity_id = '.$id));


        $where = "A.galaxy_wechat_activity_id = {$id}";

        //受邀状态
        if(isset($params['joinStatus']) && $params['joinStatus']>-1){
            $params['joinStatus'] = (string)$params['joinStatus'];
            $where .= " and A.is_invited = '{$params['joinStatus']}'";
        }

        //签到状态
        if(isset($params['signInStatus']) && $params['signInStatus']>-1){
            $params['signInStatus'] = (string)$params['signInStatus'];
            $where .= " and A.is_sign_up = '{$params['signInStatus']}'";
        }

        //客户名称
        if(isset($params['username']) && !empty($params['username'])){
            $where .= " and A.username like '%{$params['username']}%'";
        }

        //来源
        if(isset($params['origin']) && !empty($params['origin'])){
            $where .= " and C.username like '%{$params['origin']}%'";
        }

        //手机号码
        if(isset($params['iphone']) && !empty($params['iphone'])){
            $where .= " and A.phone like '%{$params['iphone']}%'";
        }
        $phql="SELECT A.id,A.galaxy_wechat_activity_id, A.galaxy_wechat_client_id,A.galaxy_admin_id,A.sign_up_time,A.is_invited,A.is_sign_up,A.is_contracted, A.present_time, A.departure_time,A.username,A.phone,B.nick_name, B.sex,C.username AS admin_name from {$alaxyWechatActivityClientModel} as A LEFT JOIN {$galaxyWechatClientModel} AS B ON A.galaxy_wechat_client_id=B.id LEFT JOIN {$galaxyAdminModel} AS C ON A.galaxy_admin_id=C.id  WHERE ".$where." ORDER BY A.sign_up_time DESC LIMIT ".$offset.",".$limit;        
        $ret = $this->modelsManager->executeQuery($phql);
        $array  = $ret->toArray();

        $newCount = 0;
        if($array){
            foreach ($array as $key => $value){
                $newCount ++;
                $array[$key]['id'] = (int)$value['id'];
                $array[$key]['galaxy_wechat_activity_id'] = (int)$value['galaxy_wechat_activity_id'];
                $array[$key]['galaxy_wechat_client_id'] = (int)$value['galaxy_wechat_client_id'];
                $array[$key]['galaxy_admin_id'] = (int)$value['galaxy_admin_id'];
                $array[$key]['is_invited'] = (int)$value['is_invited'];
                $array[$key]['is_sign_up'] = (int)$value['is_sign_up'];
                $array[$key]['is_contracted'] = (int)$value['is_contracted'];
                if($value['galaxy_admin_id']==0){
                    $array[$key]['admin_name'] = '后台';
                }

                $array[$key]['sex'] = ($value['sex'] == '1') ? '男' : (($value['sex'] == '2') ? '女' : '未知');

                #获取预约签单信息
                $crm_reorder_info = $this->getReOrderInfo($value['phone']);
                #签约时间
                $array[$key]['contract_date'] = !empty($crm_reorder_info) ? $crm_reorder_info['ordertime'] : '';
                #签约项目
                $array[$key]['contract_project'] = !empty($crm_reorder_info) ? $crm_reorder_info['project_name'] : '';
            }
        }

        $result=[
            'msg' => '客户活动预约列表',
            'code' => 200,
            'count' => $newCount,
            'data' => $array,
        ];

        return $result;
    }

    #获取客户预约签单信息
    public function getReOrderInfo($phone){
        $galaxyReorderModel = new GalaxyReorder();
        $galaxyCrmClientModel = new GalaxyCrmClient();

        if(empty($phone)){
            return '';
        }

        $crm_client_where['where'] = 'mobile = :mobile:';
        $crm_client_where['value']['mobile'] = $phone;

        $galaxyCrmClientModel->getFindOne('id, mobile', $crm_client_where);
        $crm_client_ret = $galaxyCrmClientModel->getSucceedResult(1);
        if(!$crm_client_ret){
             return '';
        }

        #获取crm客户ID
        $crm_client_id = $crm_client_ret[0]['id'];
        #获取crm项目信息
        $crm_project_info = di('redis')->setIndex(1)->get('initialize:contract:key');
        $crm_project_info = json_decode($crm_project_info,true);

        #查询客户签约订单信息
        $reorderRet = $this->getCrmRecordData($crm_client_id)->toArray();
        if($reorderRet){
            $data['project_name'] = isset($crm_project_info[$reorderRet[0]['projectid']]) ? $crm_project_info[$reorderRet[0]['projectid']] : '';
            $data['ordertime'] = isset($reorderRet[0]['ordertime']) ? $reorderRet[0]['ordertime'] : '';
            return $data;
        }

        return '';
    }

    #获取客户签约订单信息
    public function getCrmRecordData($crm_client_id){
        $galaxyReorderModel = GalaxyReorder::class; #crm订单表
        $phql = "SELECT A.projectid, A.ordertime FROM {$galaxyReorderModel} AS A  WHERE A.clientid = ".$crm_client_id." LIMIT 1";
        $ret = $this->modelsManager->executeQuery($phql);
        return $ret;
    }

    public function exportExcelData($id,$params){
        #1:导出全部；2:导出已选择
        $type = isset($params['type'])? (int)$params['type'] : 1;
        $values = isset($params['values']) ? trim($params['values']) : '';
        $conditions = "";

        if($type == 2){
             if($values){
                 $conditions .= " AND A.galaxy_wechat_client_id IN (".$values.")";
             }
        }

        $alaxyWechatActivityClientModel = GalaxyWechatActivityClient::class;
        $galaxyWechatClientModel = GalaxyWechatClient::class;
        $galaxyAdminModel = GalaxyAdmin::class;

        $phql="SELECT A.id,A.galaxy_wechat_activity_id, A.galaxy_wechat_client_id,A.galaxy_admin_id,A.sign_up_time,A.is_invited,A.is_sign_up,A.is_contracted, A.present_time, A.departure_time,A.username,A.phone,B.nick_name,B.sex, C.username AS admin_name,C.company from {$alaxyWechatActivityClientModel} as A LEFT JOIN {$galaxyWechatClientModel} AS B ON A.galaxy_wechat_client_id=B.id LEFT JOIN {$galaxyAdminModel} AS C ON A.galaxy_admin_id=C.id  WHERE A.galaxy_wechat_activity_id=".$id.$conditions;
//        $phql="SELECT A.id,A.galaxy_wechat_activity_id, A.galaxy_wechat_client_id,A.sign_up_time,A.source,A.is_invited,A.is_sign_up,A.is_contracted, A.present_time, A.departure_time,B.name,B.phone,B.nick_name from {$alaxyWechatActivityClientModel} as A LEFT JOIN {$galaxyWechatClientModel} AS B ON A.galaxy_wechat_client_id=B.id WHERE A.galaxy_wechat_activity_id=".$id.$conditions;
        $ret = $this->modelsManager->executeQuery($phql);

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
            ->setCellValue('A1', '昵称')
            ->setCellValue('B1', '性别')
            ->setCellValue('C1', '真实姓名')
            ->setCellValue('D1', '手机号码')
            ->setCellValue('E1', '报名时间')
            ->setCellValue('F1', '来源')
            ->setCellValue('G1', '是否受邀')
            ->setCellValue('H1', '是否签到')
            ->setCellValue('I1', '是否完成签约')
            ->setCellValue('J1', '签约时间')
            ->setCellValue('K1', '签约项目')
            ->setCellValue('L1', '到场签到时间')
            ->setCellValue('M1', '离场签到时间')
            ->setCellValue('N1', '所属地区');
        if($ret){
            $array  = $ret->toArray();
            if($array){
                $i = 2;
                foreach($array as $key => $value){
                    $is_invited = ($value['is_invited'] == '1' ? '已受邀' : '未受邀');
                    $is_sign_up = ($value['is_sign_up'] == '1' ? '已签到' : '未签到');
                    $is_contracted = ($value['is_contracted'] == '1' ? '已签约' : '未签约');
                    $admin_name = ($value['galaxy_admin_id'] > 0 ? trim($value['admin_name']) : '后台');

                    $nick_name = json_encode($value['nick_name']);
                    $nick_name = preg_replace("/\\\u[ed][0-9a-f]{3}\\\u[ed][0-9a-f]{3}/","*",$nick_name);//替换成*
                    $nick_name = json_decode($nick_name);

                    $sex = ($value['sex'] == '1') ? '男' : (($value['sex'] == '2') ? '女' : '未知');
                    #获取预约签单信息
                    $crm_reorder_info = $this->getReOrderInfo($value['phone']);
                    #签约时间
                    $contract_date = !empty($crm_reorder_info) ? $crm_reorder_info['ordertime'] : '';
                    #签约项目
                    $contract_project = !empty($crm_reorder_info) ? $crm_reorder_info['project_name'] : '';

                    $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$i,$nick_name);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('B'.$i,$sex);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('C'.$i,trim($value['username']));
                    $objPHPExcel->getActiveSheet(0)->setCellValue('D'.$i,$value['phone']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('E'.$i,$value['sign_up_time']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('F'.$i,$admin_name);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('G'.$i,$is_invited);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('H'.$i,$is_sign_up);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('I'.$i,$is_contracted);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('J'.$i,$contract_date);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('K'.$i,$contract_project);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('L'.$i,$value['present_time']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('M'.$i,$value['departure_time']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('N'.$i,$value['company']);

                    $i ++;
                }
            }
        }
        //sheet命名
        $objPHPExcel->getActiveSheet()->setTitle('wechat-client-activity-table');

        $savename='wechat-client-activity-table-'.date("Ymd");
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
