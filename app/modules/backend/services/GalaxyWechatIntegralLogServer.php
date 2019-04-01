<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/27
 * Time: 12:57
 */
namespace Backend\Services;

use Api\Models\GalaxyWechatIntegralLog;
use Api\Models\GalaxyWechatClient;
use Api\Models\GalaxyWechatIntegralOrder;

class GalaxyWechatIntegralLogServer extends BaseServer
{
    /**
     * 客户可用积分调整
     * @return mixed
     */
    public function addDataService($id, $params, $userinfo){
        $galaxyWechatIntegralLogModel = new GalaxyWechatIntegralLog();
        $galaxyWechatClientModel = new GalaxyWechatClient();
        $time = time();
        $sum = 0;#计算可用积分

        #判断客户是否存在
        $client_where['where']='id = :id: AND is_deleted = :is_deleted:';
        $client_where['value']['id'] = $id;
        $client_where['value']['is_deleted'] = '0';
        $clientData = $galaxyWechatClientModel->findone($client_where,$field="id, nick_name, phone, points");

        if(!$clientData){
            $this->msg = "该客户信息不存在或已删除";
            return $this->returnData();
        }

        #获取客户可用积分
        $points = (int)$clientData['points'];

        if(isset($params['operation_type']) && !in_array($params['operation_type'], ['0', '1'])){
            $this->msg = "操作积分类型不在范围内或不能为空";
            return $this->returnData();
        }

        if(isset($params['integral']) && !preg_match("/^[1-9][0-9]*$/",$params['integral'])){
            $this->msg = "操作的积分数值必须为正整数";
            return $this->returnData();
        }

        if(isset($params['remarks']) && empty($params['remarks'])){
            $this->msg = "备注说明不能为空";
            return $this->returnData();
        }

//        $where['where'] = 'galaxy_wechat_client_id = :galaxy_wechat_client_id: AND integral_state IN ({states:array})';
//        $where['value']['galaxy_wechat_client_id'] = $id;
//        # 字段 integral_state 积分当前状态,0为待生效,1为已收入,2为已支出,默认为0
//        $where['value']['states'] = ['1', '2'];
//
//        # 获积分记录数据
//        $data = $galaxyWechatIntegralLogModel->findall($where,$field="id, integral_type, integral, operation_type, front_integral, behind_integral",$order="id DESC",$page=[])->toArray();
//        # 获取可用积分
//        $sum = $this->getAvailableIntegral($data);

        # 判断 扣减积分不能大于可用积分
        if(isset($params['operation_type']) && isset($params['integral']) && ($params['operation_type'] == 1) && ($params['integral'] > $points)){
            $this->msg = "扣减积分不能大于当前可用积分";
            return $this->returnData();
        }

        # 计算获取修改后积分
        if($params['operation_type'] == 0){
            $behind_integral = $points + (int)$params['integral'];
        }else{
            $behind_integral = $points  - (int)$params['integral'];
        }

        $params['operation_type'] = (int)$params['operation_type'];
        $params['integral'] = (int)$params['integral'];
        $params['remarks'] = trim($params['remarks']);

        # 积分类型,0为默认,1为推荐注册,2为推荐签约,3为积分兑换,4位签到赠送,100为系统调整
        $params['integral_type'] = 100;
        # 积分当前状态,0为待生效,1为已收入,2为已支出,默认为0
        $params['integral_state'] = $params['operation_type'] == 0 ? 1 : 2;
        # 微信昵称
        $params['nick_name'] = $clientData->nick_name;
        # 手机号
        $params['gather_phone'] = $clientData->phone;
        # 操作人名称
        $params['galaxy_admin_name'] = isset($userinfo['username']) ? $userinfo['username'] : '';
        # 修改前积分
        $params['front_integral'] = $points;
        # 修改后积分
        $params['behind_integral'] = $behind_integral;
        # 操作IP
        $params['ip'] = di('request')->getClientAddress();
        # 操作人关联ID
        $params['galaxy_admin_id'] = isset($userinfo['id']) ? $userinfo['id'] : 0;
        # 用户ID
        $params['galaxy_wechat_client_id'] = (int)$clientData->id;
        # execute_time
        $params['execute_time'] = date("Y-m-d H:i:s", $time);

        $ret = $galaxyWechatIntegralLogModel->createData($params);
        if($ret){
            #更新客户表galaxy_wechat_client中字段points
            $clientUpdateWhere['where']='id = :id: AND is_deleted = :is_deleted:';
            $clientUpdateWhere['value']['id'] = $id;
            $clientUpdateWhere['value']['is_deleted'] = '0';
            $clientUpdateData['points'] = $behind_integral;
            $clientUpdateData['update_time'] = date('Y-m-d H:i:s',$time);

            $clientRet = $galaxyWechatClientModel->updates($clientUpdateWhere, $clientUpdateData);
            if(!$clientRet){
                $this->msg = "客户积分更新失败";
                $this->code = 0;
                $this->data = false;
            }

            $this->msg = "客户可用积分调整成功";
            $this->code = 200;
            $this->data = true;
        }

        return $this->returnData();
    }

    /**
     * 客户积分记录列表
     * @return mixed
     */
    public function searchDataService($id, $params){
        $galaxyWechatIntegralLogModel = new GalaxyWechatIntegralLog();
        $galaxyWechatClientModel = new GalaxyWechatClient();

        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $page_size = isset($params['page_size']) ? (int)$params['page_size'] : 10;
        $page_where["offset"] = ($page - 1) * $page_size;
        $page_where["limit"] = $page_size;

        #$toLimit["page"] = isset($params['page']) ? (int)$params['page'] : 1;
        #$toLimit["page_size"] = isset($params['page_size']) ? (int)$params['page_size'] : 10;
        $where['where'] = '';
        $where['value'] = [];

        #如id=0,表示获取所有客户积分信息；id>0表获取单个客户积分记录信息
        if($id > 0){
            $client_where['where']='id = :id: AND is_deleted = :is_deleted:';
            $client_where['value']['id'] = $id;
            $client_where['value']['is_deleted'] = '0';
            $clientData = $galaxyWechatClientModel->findone($client_where,$field="id");
            if(!$clientData){
                $this->msg = "该客户信息不存在或已删除";
                return $this->returnData();
            }

            #客户id约束条件
            $where['where'] .= (!empty($where['where']) ? ' AND galaxy_wechat_client_id = :galaxy_wechat_client_id:' : 'galaxy_wechat_client_id = :galaxy_wechat_client_id:');
            $where['value']['galaxy_wechat_client_id'] = $id;
        }

        #积分类型,0为默认,1为推荐注册,2为推荐签约,3为积分兑换,4位签到赠送,100为系统调整
        if(isset($params['integral_type']) && in_array($params['integral_type'], ['1', '2', '3', '4', '100'])){
            $where['where'] .= (!empty($where['where']) ? ' AND integral_type = :integral_type:' : 'integral_type = :integral_type:');
            $where['value']['integral_type'] = $params['integral_type'];
        }

        #积分当前状态,0为待生效,1为已收入,2为已支出,默认为0
        if(isset($params['integral_state']) && in_array($params['integral_state'], ['0', '1', '2'])){
            $where['where'] .= (!empty($where['where']) ? ' AND integral_state = :integral_state:' : 'integral_state = :integral_state:');
            $where['value']['integral_state'] = $params['integral_state'];
        }

        #日期筛选：支持日期段筛选，不需要具体到时分秒
        if(isset($params['start_date']) && isset($params['end_date']) && !empty($params['start_date']) && !empty($params['end_date'])){
            $start_date = date("Y-m-d H:i:s", strtotime($params['start_date']));
            $end_date = date("Y-m-d H:i:s", strtotime($params['end_date']) + 24 * 60 * 60 - 1);

            $where['where'] .= (!empty($where['where']) ? ' AND create_time >= :start_date: AND create_time <= :end_date:' : 'create_time >= :start_date: AND create_time <= :end_date:');
            $where['value']['start_date'] = $start_date;
            $where['value']['end_date'] = $end_date;
        }

        #手机号码
        if(isset($params['gather_phone']) && !empty($params['gather_phone'])){
            $where['where'] .= (!empty($where['where']) ? " AND gather_phone like '%".trim($params['gather_phone'])."%'" : "gather_phone like '%".trim($params['gather_phone'])."%'");
            //$where['value']['gather_phone'] = "'%".$params['gather_phone']."%'";
        }

        # 获积分记录数据
        $data = $galaxyWechatIntegralLogModel->findall($where,$field="*",$order="id DESC",$page_where)->toArray();


        //$galaxyWechatIntegralLogModel->getFindAll($select = '*',$where,$toLimit,$toBy = array());
        //$data = $galaxyWechatIntegralLogModel->getSucceedResult(1);

        if(empty($data)){
            return ['code'=>0, 'msg'=>'该积分记录没有相关信息', 'count'=> 0, 'data'=>[]];
        }

        if($data){
            foreach($data as $k => $v){
                $data[$k]['id'] = (int)$v['id'];
                $data[$k]['integral_type'] = (int)$v['integral_type'];
                # 积分类型,0为默认,1为推荐注册,2为推荐签约,3为积分兑换,4位签到赠送,100为系统调整
                $data[$k]['integral_type_value'] = $this->getIntegralTypeValue($v['integral_type']);
                $data[$k]['integral'] = (int)$v['integral'];
                $data[$k]['operation_type'] = (int)$v['operation_type'];
                # 积分显示数值 格式如“+30”
                $data[$k]['integral_value'] = $this->getIntegralValue($v['operation_type'], $v['integral']);
                $data[$k]['integral_state'] = (int)$v['integral_state'];
                # 积分当前状态,0为待生效,1为已收入,2为已支出,默认为0
                $data[$k]['integral_state_value'] = $this->getIntegralStateValue($v['integral_state']);
                $data[$k]['front_integral'] = (int)$v['front_integral'];
                $data[$k]['behind_integral'] = (int)$v['behind_integral'];
                $data[$k]['galaxy_admin_id'] = (int)$v['galaxy_admin_id'];
                $data[$k]['galaxy_wechat_client_id'] = (int)$v['galaxy_wechat_client_id'];
                $data[$k]['galaxy_wechat_integral_order_id'] = (int)$v['galaxy_wechat_integral_order_id'];
                # 积分相关订单(实体/虚拟) integral_type=3时显示
                #$data[$k]['integral_order_status'] = $v['integral_type'] == '3' ? 1 : 0;
                $data[$k]['integral_order_status_value'] = $this->getIntegralOrderStatusValue($v['integral_type'], $v['galaxy_wechat_integral_order_id']);
                #生效时间
                $data[$k]['execute_time'] = $v['execute_time'] != '0000-00-00 00:00:00' ? $v['execute_time'] : '-';
            }
        }

        #积分记录总量
        $logNum = $galaxyWechatIntegralLogModel->getCount($where);
        return ['code'=>200, 'msg'=>'积分记录列表', 'count'=> $logNum, 'data'=>$data];
    }

    /**
     * 客户可用积分/已兑换积分统计
     * @return mixed
     */
    public function availableIntegralDataService($id, $params){
        $galaxyWechatIntegralLogModel = new GalaxyWechatIntegralLog();
        $galaxyWechatClientModel = new GalaxyWechatClient();
        $data = ['available_integral_count' => 0, 'exchange_integral_count' => 0];

        $client_where['where']='id = :id: AND is_deleted = :is_deleted:';
        $client_where['value']['id'] = $id;
        $client_where['value']['is_deleted'] = '0';
        $clientData = $galaxyWechatClientModel->findone($client_where,$field="id");
        if(!$clientData){
            $this->msg = "该客户信息不存在或已删除";
            return $this->returnData();
        }

        # 可用积分统计
        $where['where'] = 'galaxy_wechat_client_id = :galaxy_wechat_client_id: AND integral_state IN ({states:array})';
        $where['value']['galaxy_wechat_client_id'] = $id;
        # 字段 integral_state 积分当前状态,0为待生效,1为已收入,2为已支出,默认为0
        $where['value']['states'] = ['1', '2'];

        # 获积分记录数据
        $ret = $galaxyWechatIntegralLogModel->findall($where,$field="id, integral_type, integral, operation_type,integral_state, front_integral, behind_integral",$order="id DESC",$page=[])->toArray();
        # 获取可用积分
        $data['available_integral_count'] = $this->getAvailableIntegral($ret);

        #已兑换积分统计
        $change_where['where'] = 'galaxy_wechat_client_id = :galaxy_wechat_client_id: AND integral_type = :integral_type:';
        $change_where['value']['galaxy_wechat_client_id'] = $id;
        # 字段 integral_type 积分类型,0为默认,1为推荐注册,2为推荐签约,3为积分兑换,4位签到赠送,100为系统调整
        $change_where['value']['integral_type'] = 3;
        $data['exchange_integral_count'] = (int)$galaxyWechatIntegralLogModel->getSum($change_where,$field='integral');

        $this->code = 200;
        $this->msg = "客户可用积分/已兑换积分统计";
        $this->data = $data;
        return $this->returnData();
    }

    /**
     * 积分记录下载
     * @return mixed
     */
    public function excelDataService($params){
        $galaxyWechatIntegralLogModel = new GalaxyWechatIntegralLog();
        $galaxyWechatIntegralLogModel = new GalaxyWechatIntegralLog();

        #$page = isset($params['page']) ? (int)$params['page'] : 1;
        #$page_size = isset($params['page_size']) ? (int)$params['page_size'] : 10;
        #$page_where["offset"] = ($page - 1) * $page_size;
        #$page_where["limit"] = $page_size;

        #$toLimit["page"] = isset($params['page']) ? (int)$params['page'] : 1;
        #$toLimit["page_size"] = isset($params['page_size']) ? (int)$params['page_size'] : 10;
        $where['where'] = '';
        $where['value'] = [];

        #积分类型,0为默认,1为推荐注册,2为推荐签约,3为积分兑换,4位签到赠送,100为系统调整
        if(isset($params['integral_type']) && in_array($params['integral_type'], ['1', '2', '3', '4', '100'])){
            $where['where'] .= (!empty($where['where']) ? ' AND integral_type = :integral_type:' : 'integral_type = :integral_type:');
            $where['value']['integral_type'] = $params['integral_type'];
        }

        #积分当前状态,0为待生效,1为已收入,2为已支出,默认为0
        if(isset($params['integral_state']) && in_array($params['integral_state'], ['0', '1', '2'])){
            $where['where'] .= (!empty($where['where']) ? ' AND integral_state = :integral_state:' : 'integral_state = :integral_state:');
            $where['value']['integral_state'] = $params['integral_state'];
        }

        #日期筛选：支持日期段筛选，不需要具体到时分秒
        if(isset($params['start_date']) && isset($params['end_date']) && !empty($params['start_date']) && !empty($params['end_date'])){
            $start_date = date("Y-m-d H:i:s", strtotime($params['start_date']));
            $end_date = date("Y-m-d H:i:s", strtotime($params['end_date']) + 24 * 60 * 60 - 1);

            $where['where'] .= (!empty($where['where']) ? ' AND create_time >= :start_date: AND create_time <= :end_date:' : 'create_time >= :start_date: AND create_time <= :end_date:');
            $where['value']['start_date'] = $start_date;
            $where['value']['end_date'] = $end_date;
        }

        #手机号码
        if(isset($params['gather_phone']) && !empty($params['gather_phone'])){
            $where['where'] .= (!empty($where['where']) ? " AND gather_phone like '%".trim($params['gather_phone'])."%'" : "gather_phone like '%".trim($params['gather_phone'])."%'");
            //$where['value']['gather_phone'] = "'%".$params['gather_phone']."%'";
        }

        # 获积分记录数据
        $ret = $galaxyWechatIntegralLogModel->findall($where,$field="*",$order="id DESC",$page_where=[]);

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
            ->setCellValue('A1', '积分分类')
            ->setCellValue('B1', '积分')
            ->setCellValue('C1', '当前状态')
            ->setCellValue('D1', '微信昵称')
            ->setCellValue('E1', '手机号')
            ->setCellValue('F1', '创建时间')
            ->setCellValue('G1', '生效时间')
            ->setCellValue('H1', '备注说明');

        if($ret){
            $array  = $ret->toArray();
            if($array){
                $i = 2;
                foreach($array as $key => $value){
                    # 积分类型,0为默认,1为推荐注册,2为推荐签约,3为积分兑换,4位签到赠送,100为系统调整
                    $integral_type_value = $this->getIntegralTypeValue($value['integral_type']);
                    # 积分显示数值 格式如“+30”
                    $integral_value = $this->getIntegralValue($value['operation_type'], $value['integral']);
                    # 积分当前状态,0为待生效,1为已收入,2为已支出,默认为0
                    $integral_state_value = $this->getIntegralStateValue($value['integral_state']);
                    #昵称
                    $nick_name = json_encode($value['nick_name']);
                    $nick_name = preg_replace("/\\\u[ed][0-9a-f]{3}\\\u[ed][0-9a-f]{3}/","*",$nick_name);//替换成*
                    $nick_name = json_decode($nick_name);

                    #生效时间
                    $execute_time = $value['execute_time'] != '0000-00-00 00:00:00' ? $value['execute_time'] : '-';

                    $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$i,$integral_type_value);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('B'.$i,$integral_value);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('C'.$i,$integral_state_value);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('D'.$i,$nick_name);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('E'.$i,trim($value['gather_phone']));
                    $objPHPExcel->getActiveSheet(0)->setCellValue('F'.$i,$value['create_time']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('G'.$i, $execute_time);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('H'.$i,trim($value['remarks']));

                    $i ++;
                }
            }
        }
        //sheet命名
        $objPHPExcel->getActiveSheet()->setTitle('积分记录');

        $savename=date("YmdHis",time());

        $headers = di('request')->getHeaders();
        #$ua = $_SERVER["HTTP_USER_AGENT"];
        $ua = isset($headers['HTTP_USER_AGENT']) ? $headers['HTTP_USER_AGENT'] : '';
        if ($ua && preg_match("/MSIE/", $ua)) {
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

    /**
     * 获取可用积分
     * @return mixed
     */
    public function getAvailableIntegral($data){
        $sum = 0;
        if($data) {
            foreach ($data as $k => $v) {
                # 字段 integral 操作的积分数值
                # 字段 operation_type 操作积分类型,0为新增,1为减少,默认为0
                if ($v['operation_type'] == 0) {
                    $sum += (int)$v['integral'];
                } else {
                    $sum -= (int)$v['integral'];
                }
            }
        }

        return $sum;
    }

    /**
     * 通过积分类型为积分兑换，获取积分相关订单(实体/虚拟)
     * @return mixed
     */
    public function getIntegralOrderStatusValue($integral_type, $galaxy_wechat_integral_order_id){
        if($integral_type == '3'){
            $galaxyWechatIntegralOrderModel = new GalaxyWechatIntegralOrder();

            $where['where'] = 'id = :id:';
            $where['value']['id'] = $galaxy_wechat_integral_order_id;


            $galaxyWechatIntegralOrderModel->getFindOne('goods_type', $where);

            $ret = $galaxyWechatIntegralOrderModel->getSucceedResult(1);
            if($ret){
                return (int)$ret[0]['goods_type'];
            }

            return -1;
        }

        return -1;
    }

    /**
     * 获取积分分类对应类型值
     * @return mixed
     */
    public function getIntegralTypeValue($type){
        switch ($type){
            case 0:
                return '默认';
                break;
            case 1:
                return '推荐注册';
                break;
            case 2:
                return '推荐签约';
                break;
            case 3:
                return '积分兑换';
                break;
            case 4:
                return '签到赠送';
                break;
            case 100:
                return '系统调整';
                break;
            default:
                break;
        }
    }

    /**
     * 获取积分显示数值 格式如“+30”
     * @return mixed
     */
    public function getIntegralValue($type, $val){
        $op = '';
        switch ($type){
            case 0:
                $op = '+';
                break;
            case 1:
                $op = '-';
                break;
            default:
                $op = '';
                break;
        }

        return $op.$val;
    }

    /**
     * 获取积分状态对应类型值
     * @return mixed
     */
    public function getIntegralStateValue($state){
        switch ($state){
            case 0:
                return '待生效';
                break;
            case 1:
                return '已收入';
                break;
            case 2:
                return '已支出';
                break;
            default:
                break;
        }
    }

}