<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/27
 * Time: 16:42
 */
namespace Backend\Services;

use Api\Models\GalaxyWechatIntegralOrder;
use Api\Models\GalaxyWechatClassify; # 礼品分类
use Api\Models\GalaxyAdmin;

class GalaxyWechatIntegralOrderServer extends BaseServer
{
    /**
     * 订单列表
     * @return mixed
     */
    public function searchDataService($params)
    {
        $galaxyWechatIntegralOrderModel = new GalaxyWechatIntegralOrder();

        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $page_size = isset($params['page_size']) ? (int)$params['page_size'] : 10;
        $page_where["offset"] = ($page - 1) * $page_size;
        $page_where["limit"] = $page_size;

        $where['where'] = '';
        $where['value'] = [];

        # 礼品类型:0为实体礼品,1为虚拟礼品;默认为0;
        if(isset($params['goods_type']) && in_array($params['goods_type'], ['0', '1'])){
            $where['where'] .= (!empty($where['where']) ? ' AND goods_type = :goods_type:' : 'goods_type = :goods_type:');
            $where['value']['goods_type'] = $params['goods_type'];
        }

        # 订单状态,1为待兑换、2为已兑换、3为已发货、4为已完成、5为已核销,100为已取消
        if(isset($params['order_type']) && in_array($params['order_type'], ['1', '2', '3', '4', '5', '100'])){
            $where['where'] .= (!empty($where['where']) ? ' AND order_type = :order_type:' : 'order_type = :order_type:');
            $where['value']['order_type'] = (int)$params['order_type'];
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
        if(isset($params['phone']) && !empty($params['phone'])){
            $where['where'] .= (!empty($where['where']) ? " AND phone like '%".trim($params['phone'])."%'" : "phone like '%".trim($params['phone'])."%'");
            //$where['value']['phone'] = "'%".$params['phone']."%'";
        }

        # 获积分记录数据
        $data = $galaxyWechatIntegralOrderModel->findall($where,$field="*",$order="id DESC",$page_where)->toArray();

        //$galaxyWechatIntegralLogModel->getFindAll($select = '*',$where,$toLimit,$toBy = array());
        //$data = $galaxyWechatIntegralLogModel->getSucceedResult(1);

        if(empty($data)){
            $this->msg = "该订单列表没有相关信息";
            $this->data = [];
            return $this->returnData();
        }

        if($data){
            foreach($data as $key => $value){
                $data[$key]['id'] = (int)$value['id'];
                $data[$key]['order_type'] = (int)$value['order_type'];
                # 订单状态,1为待兑换、2为已兑换、3为已发货、4为已完成、5为已核销,100为已取消
                $data[$key]['order_type_value'] = $this->getOrderTypeValue($value['order_type']);
                $data[$key]['amount'] = (int)$value['amount'];
                $data[$key]['goods_type'] = (int)$value['goods_type'];
                # 礼品类型:0为实体礼品,1为虚拟礼品;默认为0;
                #$data[$key]['goods_type_value'] = $this->getGoodsTypeValue($value['goods_type']);
                $data[$key]['galaxy_wechat_integral_goods_id'] = (int)$value['galaxy_wechat_integral_goods_id'];
                $data[$key]['getin_integral'] = (int)$value['getin_integral'];
                $data[$key]['galaxy_wechat_client_id'] = (int)$value['galaxy_wechat_client_id'];
            }
        }

        #积分记录总量
        $orderNum = $galaxyWechatIntegralOrderModel->getCount($where);

        return ['code'=>200, 'msg'=>'订单列表', 'count'=> $orderNum, 'data'=>$data];
    }

    /**
     * 订单详情
     * @return mixed
     */
    public function getDataService($id, $params)
    {
        $galaxyWechatIntegralOrderModel = new GalaxyWechatIntegralOrder();

        $where['where'] = 'id = :id:';
        $where['value']['id'] = $id;

        $galaxyWechatIntegralOrderModel->getFindOne('', $where);
        $ret = $galaxyWechatIntegralOrderModel->getSucceedResult(1);
        if(!$ret){
            $this->msg = "该订单不存在";
            return $this->returnData();
        }

        $orderArray = [];
        #获客户，订单信息，收/发货信息，核销信息
        $ret = $galaxyWechatIntegralOrderModel->getOrderDetailData($id)->toArray();
        $data = $ret[0];
        #订单状态,1为待兑换、2为已兑换、3为已发货、4为已完成、5为已核销,100为已取消
        $orderArray['order_type_value'] = $this->getOrderTypeValue($data['order_type']);

        #-------------------客户信息-----------------------
        #微信昵称
        $orderArray['nick_name'] = !empty($data['nick_name']) ? $data['nick_name'] : '-' ;
        #备注名称
        $orderArray['name'] = !empty($data['name']) ? $data['name'] : '-';
        #关注公众号
        $orderArray['is_care'] = $data['is_care'] == '1' ? '是': '否';
        #手机号
        $orderArray['phone'] = !empty($data['phone']) ? $data['phone'] : '-';

        #-------------------订单信息-----------------------
        #订单号
        $orderArray['orderid'] = !empty($data['orderid']) ? $data['orderid'] : '-';
        #订单状态
        #$orderArray['order_type_value'] = $this->getOrderTypeValue($data['order_type']);
        #礼品
        $orderArray['goods_name'] = !empty($data['goods_name']) ? $data['goods_name'] : '-';
        #数量
        $orderArray['amount'] = (int)$data['amount'];
        #礼品类型:0为实体礼品,1为虚拟礼品;默认为0;
        $orderArray['goods_type_value'] = $data['goods_type'] == 0 ? '实体礼品' : '虚拟礼品';
        #礼品分类
        $orderArray['goods_classify_value'] = !empty($data['classify_name']) ? $data['classify_name'] : '-';
        #支付方式
        $orderArray['pay_type_value'] = $this->getPayTypeValue($data['pay_type']);
        #兑换积分
        $orderArray['getin_integral'] = (int)$data['getin_integral'];
        #创建时间
        $orderArray['order_create_time'] = (($data['order_create_time'] != '0000-00-00 00:00:00') && !empty($data['order_create_time'])) ? $data['order_create_time'] : '-';
        #兑换时间
        $orderArray['getin_time'] = (($data['getin_time'] != '0000-00-00 00:00:00') && !empty($data['getin_time'])) ? $data['getin_time'] : '-';
        #取消时间
        $orderArray['order_cancel_time'] = (($data['order_cancel_time'] != '0000-00-00 00:00:00') && !empty($data['order_cancel_time'])) ? $data['order_cancel_time'] : '-';

        #-------------------收货信息-----------------------
        #收货人
        $orderArray['detailed_name'] = !empty($data['detailed_name']) ? $data['detailed_name'] : '-';
        #联系电话
        $orderArray['detailed_phone'] = !empty($data['detailed_phone']) ? $data['detailed_phone'] : '-';
        #收货地址
        $orderArray['detailed_address'] = !empty($data['detailed_address']) ? $data['detailed_address'] : '-';
        #收货备注
        $orderArray['detailed_remarks'] = !empty($data['detailed_remarks']) ? $data['detailed_remarks'] : '-';

        #-------------------发货信息-----------------------
        #快递公司关联ID
        $orderArray['express_company_id'] = (int)$data['express_company_id'];
        #快递公司
        $orderArray['express_company'] = !empty($data['express_company']) ? $data['express_company'] : '-';
        #快递单号
        $orderArray['express_number'] = !empty($data['express_number']) ? $data['express_number'] : '-';
        #发货内容
        $orderArray['deliver_message'] = !empty($data['deliver_message']) ? $data['deliver_message'] : '-';
        #发货人员
        #$orderArray['galaxy_admin_id'] = $data['galaxy_admin_id'];
        $orderArray['galaxy_admin_name'] = $this->getAdminName($data['galaxy_admin_id']);
        #发货时间
        $orderArray['deliver_create_time'] = (($data['deliver_update_time'] != '0000-00-00 00:00:00') && !empty($data['deliver_update_time'])) ? $data['deliver_update_time'] : '-';

        #-------------------核销信息-----------------------
        #核销凭证
        $orderArray['audit_img_url'] = !empty($data['audit_img_url']) ? $data['audit_img_url'] : '-';
        #备注说明
        $orderArray['audit_remarks'] = !empty($data['audit_remarks']) ? $data['audit_remarks'] : '-';
        #核销人员
        $orderArray['audit_name'] = !empty($data['audit_name']) ? $data['audit_name'] : '-';
        #核销时间
        $orderArray['audit_create_time'] = (($data['audit_update_time'] != '0000-00-00 00:00:00') && !empty($data['audit_update_time'])) ? $data['audit_update_time'] : '-';
        
        $this->code = 200;
        $this->msg = '订单详情信息';
        $this->data = $orderArray;
        return $this->returnData();
    }

    /**
     * 获取订单状态对应的数量统计
     * 1-待兑换，2-已兑换，4-已完成 100-已取消
     * @return mixed
     */
    public function opderTypeCountDataService($params){
        $galaxyWechatIntegralOrderModel = new GalaxyWechatIntegralOrder();

        $data = array(
            array('order_type'=> '1', 'order_type_value'=>'待兑换', 'num' => 0),
            array('order_type'=> '2,3', 'order_type_value'=>'已兑换', 'num' => 0),
            array('order_type'=> '4', 'order_type_value'=>'已完成', 'num'=> 0),
            array('order_type'=> '100', 'order_type_value'=>'已取消', 'num'=> 0),
        );

        foreach($data as $k => $v){
            if($v['order_type'] == '2,3'){
                $where['where'] = 'order_type in ('.$v['order_type'].')';
                $where['value'] = [];
            }else{
                $where['where'] = 'order_type = :order_type:';
                $where['value']['order_type'] = $v['order_type'];
            }

            $data[$k]['num'] = $galaxyWechatIntegralOrderModel->getCount($where);
        }

        $this->code = 200;
        $this->msg = '积分订单数量统计';
        $this->data = $data;
        return $this->returnData();
    }

    /**
     * 订单下载
     * @return mixed
     */
    public function excelDataService($params)
    {
        $galaxyWechatIntegralOrderModel = new GalaxyWechatIntegralOrder();
        $where['where'] = '';
        $where['value'] = [];

        # 礼品类型:0为实体礼品,1为虚拟礼品;默认为0;
        if(isset($params['goods_type']) && in_array($params['goods_type'], ['0', '1'])){
            $where['where'] .= (!empty($where['where']) ? ' AND goods_type = :goods_type:' : 'goods_type = :goods_type:');
            $where['value']['goods_type'] = $params['goods_type'];
        }

        # 订单状态,1为待兑换、2为已兑换、3为已发货、4为已完成、5为已核销,100为已取消
        if(isset($params['order_type']) && in_array($params['order_type'], ['1', '2', '3', '4', '5', '100'])){
            $where['where'] .= (!empty($where['where']) ? ' AND order_type = :order_type:' : 'order_type = :order_type:');
            $where['value']['order_type'] = (int)$params['order_type'];
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
        if(isset($params['phone']) && !empty($params['phone'])){
            $where['where'] .= (!empty($where['where']) ? " AND phone like '%".trim($params['phone'])."%'" : "phone like '%".trim($params['phone'])."%'");
            //$where['value']['phone'] = "'%".$params['phone']."%'";
        }

        # 获积分记录数据
        $ret = $galaxyWechatIntegralOrderModel->findall($where,$field="*",$order="id DESC",$page_where=[]);

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
            ->setCellValue('A1', '订单号')
            ->setCellValue('B1', '订单状态')
            ->setCellValue('C1', '微信昵称')
            ->setCellValue('D1', '客户姓名')
            ->setCellValue('E1', '手机号')
            ->setCellValue('F1', '礼品名称')
            ->setCellValue('G1', '数量')
            ->setCellValue('H1', '创建时间');
        if($ret){
            $array  = $ret->toArray();
            if($array){
                $i = 2;
                foreach($array as $key => $value){
                    # 订单状态,1为待兑换、2为已兑换、3为已发货、4为已完成、5为已核销,100为已取消
                    $order_type_value = $this->getOrderTypeValue($value['order_type']);

                    #昵称
                    $nick_name = json_encode($value['nick_name']);
                    $nick_name = preg_replace("/\\\u[ed][0-9a-f]{3}\\\u[ed][0-9a-f]{3}/","*",$nick_name);//替换成*
                    $nick_name = json_decode($nick_name);

                    $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$i,trim($value['orderid']));
                    $objPHPExcel->getActiveSheet(0)->setCellValue('B'.$i,$order_type_value);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('C'.$i,$nick_name);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('D'.$i,trim($value['name']));
                    $objPHPExcel->getActiveSheet(0)->setCellValue('E'.$i,trim($value['phone']));
                    $objPHPExcel->getActiveSheet(0)->setCellValue('F'.$i,trim($value['goods_name']));
                    $objPHPExcel->getActiveSheet(0)->setCellValue('G'.$i,$value['amount']);
                    $objPHPExcel->getActiveSheet(0)->setCellValue('H'.$i,$value['create_time']);

                    $i ++;
                }
            }
        }

        //sheet命名
        $objPHPExcel->getActiveSheet()->setTitle('订单列表');

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

    public function getAdminName($id){
        $str = '-';
        if($id > 0){
            $galaxyAdminModel = new GalaxyAdmin();

            $where['where'] = 'id = :id:';
            $where['value']['id'] = $id;
            $galaxyAdminModel->getFindOne('username', $where);
            $ret = $galaxyAdminModel->getSucceedResult(1);
            if($ret){
                $str = $ret[0]['username'];
            }
        }

        return $str;
    }

    /**
     * 获取商品分类
     * @return mixed
     */
    public function getGoodsClassifyValue($id){
        $galaxyWechatClassifyModel = new GalaxyWechatClassify();
        $str = '';
        if($id > 0){
            $where['where'] = 'id = :id: AND is_delete = :is_delete:';
            $where['value']['id'] = $id;
            $where['value']['is_delete'] = 0;

            $galaxyWechatClassifyModel->getFindOne('classify_name', $where);

            $ret = $galaxyWechatClassifyModel->getSucceedResult(1);
            if($ret){
                $str = $ret[0]['classify_name'];
            }
        }

        return $str;
    }

    /**
     * 获取支付方式
     * 支付方式:0为积分支付,1为金钱支付,2为混合支付;默认为0;（冗余）
     * @return mixed
     */
    public function getPayTypeValue($type){
        switch ($type){
            case 0:
                return '积分支付';
                break;
            case 1:
                return '金钱支付';
                break;
            case 2:
                return '混合支付';
                break;
            default:
                return '-';
                break;
        }
    }

    /**
     * 获取订单状态对应类型值
     * @return mixed
     */
    public function getOrderTypeValue($type){
        switch ($type){
            case 1:
                return '待兑换';
                break;
            case 2:
                return '已兑换';
                break;
            case 3:
                return '已发货';
                break;
            case 4:
                return '已完成';
                break;
            case 5:
                return '已核销';
                break;
            case 100:
                return '已取消';
                break;
            default:
                return '-';
                break;
        }
    }

    /**
     * 获取礼品类型对应类型值
     * @return mixed
     */
    public function getGoodsTypeValue($type){
        switch ($type){
            case 0:
                return '实体礼品';
                break;
            case 1:
                return '虚拟礼品';
                break;
            default:
                return '-';
                break;
        }
    }
}