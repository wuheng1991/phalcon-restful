<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 16:11
 */

namespace Backend\Services;

use Api\Models\GalaxyWechatIntegralGoods;
use Api\Models\GalaxyWechatInventoryLog;
use Api\Models\GalaxyWechatClassify;

class GalaxyWechatIntegralGoodsServer extends BaseServer
{
    /**
     * 礼品列表
     * @return mixed
     */
    public function searchDataService($params){
        $galaxyWechatIntegralGoodsModel = new GalaxyWechatIntegralGoods();

        #0为下架,1为上架,2全部
        $params['state'] = isset($params['state']) ? (int) $params['state'] : 2;
        $params['page'] = isset($params['page']) ? (int)$params['page'] : 1;
        $params['page_size'] = isset($params['page_size']) ? (int)$params['page_size'] : 10;
        $params['offset'] = ($params['page'] - 1) * $params['page_size'];

        # 获取分类列表信息
        $data = $galaxyWechatIntegralGoodsModel->getIntegralGoodsClassesData($params)->toArray();
        if($data){
            foreach ($data as $k => $v){
                $data[$k]['id'] = (int)$v['id'];
                $data[$k]['goods_type'] = (int)$v['goods_type'];
                #礼品类型名称 （礼品类型:0为实体礼品,1为虚拟礼品;默认为0）
                $data[$k]['goods_type_value'] = $v['goods_type'] == 0 ? '实体礼品' : '虚体礼品';
                $data[$k]['galaxy_wechat_classify_id'] = (int)$v['galaxy_wechat_classify_id'];
                $data[$k]['inventory'] = (int)$v['inventory'];
                $data[$k]['pay_type'] = (int)$v['pay_type'];
                #支付方式名称 （支付方式:0为积分支付,1为金钱支付,2为混合支付;默认为0）
                $data[$k]['pay_type_value'] = $v['pay_type'] == 0 ? '积分支付' : ($v['pay_type'] == 1 ? '金钱支付' : '混合支付');
                $data[$k]['integral_price'] = (int)$v['integral_price'];
                $data[$k]['state'] = (int)$v['state'];
                #上架/下架名称 （上下架状态,0为下架,1为上架,默认为0）
                $data[$k]['state_value'] = $v['state'] == 0 ? '下架' : '上架';
                $data[$k]['sort'] = (int)$v['sort'];
                #读取库存信息redis
                $data[$k]['inventory'] = di('redis')->hget('goods:num', $v['id']);
            }
        }

        #礼品总量
        $where['where'] = 'is_delete = :is_delete:';
        $where['value']['is_delete'] = 0;
        if(in_array($params['state'], ['0', '1'])){
            $where['where'] .= ' AND state = :state:';
            $where['value']['state'] = $params['state'];
        }
        $goodsNum = $galaxyWechatIntegralGoodsModel->getCount($where);

        return ['code'=>200, 'msg'=>'礼品分类列表', 'count'=> $goodsNum, 'data'=>$data];
    }

    /**
     * 礼品详情
     * @return mixed
     */
    public function getDataService($id){
        $galaxyWechatIntegralGoodsModel = new GalaxyWechatIntegralGoods();

        $where['where'] = 'id = :id: AND is_delete = :is_delete:';
        $where['value']['id'] = $id;
        $where['value']['is_delete'] = 0;

        $galaxyWechatIntegralGoodsModel->getFindOne('', $where);
        if(!$galaxyWechatIntegralGoodsModel->getErrorResult()){
            $ret = $galaxyWechatIntegralGoodsModel->getSucceedResult(1);
            if($ret){
                $data = $ret[0];
                $data['id'] = (int)$data['id'];
                $data['goods_type'] = (int)$data['goods_type'];
                $data['galaxy_wechat_classify_id'] = (int)$data['galaxy_wechat_classify_id'];
                $data['inventory'] = (int)$data['inventory'];
                $data['pay_type'] = (int)$data['pay_type'];
                $data['state'] = (int)$data['state'];
                $data['sort'] = (int)$data['sort'];
                $data['delivery_type'] = (int)$data['delivery_type'];
                $data['is_delete'] = (int)$data['is_delete'];
                #读取库存信息redis
                $data['inventory'] = di('redis')->hget('goods:num', $id);
                # 商品图片地址集
                $goodsImgInfo = json_decode($data['goods_img_url'], true);
                $fileArray = [];
                if($goodsImgInfo){

                    foreach($goodsImgInfo as $k => $v){
                        $imgUrlArray = explode("/", $v);
                        $name = '';
                        if(isset($imgUrlArray['6']) && isset($imgUrlArray['7'])){
                            $name = $imgUrlArray['6'].'/'.$imgUrlArray['7'];
                        }
                        $imgArray = array(
                            'url' => $v,
                            'name' => $name,
                        );

                        array_push($fileArray, $imgArray);
                    }
                }
                $data['goods_img_url'] = $fileArray;
                #商品缩略图
                $litimgUrlArray = explode('/',$data['litimg_url']);
                $litiname = '';
                if(isset($litimgUrlArray['6']) && isset($litimgUrlArray['7'])){
                    $litiname = $litimgUrlArray['6'].'/'.$litimgUrlArray['7'];
                }
                $data['litimg_url'] = array(
                    'url' => $data['litimg_url'],
                    'name' => $litiname,
                );

                $this->msg = "礼品详情";
                $this->code = 200;
                $this->data = $data;
            }
        }

        return $this->returnData();
    }

    /**
     * 礼品增添
     * @return mixed
     */
    public function addDataService($params){
        $galaxyWechatIntegralGoodsModel = new GalaxyWechatIntegralGoods();
        #字段验证
        $validateData = $this->fieldValidate($params);
        if($validateData['code'] == 0){
            return $validateData;
        }

        # 数据库存储商品图片地址集,json格式
        if(isset($params['goods_img_url'])){
            $img_url_array = explode(',', $params['goods_img_url']);

            if($img_url_array){
                $img_url_array_temp = [];
                foreach($img_url_array as $k => $v){

                    $img_url_array_temp[$k+1] = $v;
                }
            }

            $params['goods_img_url'] = json_encode($img_url_array_temp);
        }

        $ret = $galaxyWechatIntegralGoodsModel->createData($params);
        if($ret) {
            #更新字段sort
            $where['where'] = 'id = :id:';
            $where['value']['id'] = (int)$ret;
            $update['sort'] = (int)$ret;
            $galaxyWechatIntegralGoodsModel->saveData($where, $update);
            #将库存信息放到redis
            #di('redis')->save('goods:goods:num:'.$ret, trim($params['inventory']));
            di('redis')->hSet('goods:num', $ret, trim($params['inventory']));

            $this->msg = "礼品添加成功";
            $this->code = 200;
            $this->data = true;
        }

        return $this->returnData();
    }

    /**
     * 礼品编辑
     * @return mixed
     */
    public function saveDataService($id, $params)
    {
        $galaxyWechatIntegralGoodsModel = new GalaxyWechatIntegralGoods();
        $galaxyWechatInventoryLogModel = new GalaxyWechatInventoryLog();
        $time = time();

        $where['where'] = 'id = :id: AND is_delete = :is_delete:';
        $where['value']['id'] = $id;
        $where['value']['is_delete'] = 0;

        $galaxyWechatIntegralGoodsModel->getFindOne('', $where);
        $ret = $galaxyWechatIntegralGoodsModel->getSucceedResult(1);
        if(!$ret){
            $this->msg = "该礼品不存在";
            return $this->returnData();
        }

        # 上下架状态,0为下架,1为上架
        if($ret[0]['state'] == 1){
            $this->msg = "已上架状态的商品不能编辑";
            return $this->returnData();
        }

        #字段验证
        $validateData = $this->fieldValidate($params);
        if($validateData['code'] == 0){
            return $validateData;
        }

        # 数据库存储商品图片地址集,json格式
        if(isset($params['goods_img_url'])){
            $img_url_array = explode(',', $params['goods_img_url']);

            if($img_url_array){
                $img_url_array_temp = [];
                foreach($img_url_array as $k => $v){

                    $img_url_array_temp[$k+1] = $v;
                }
            }

            $params['goods_img_url'] = json_encode($img_url_array_temp);
        }

        # 礼品编辑操作
        $op_where['where'] = 'id = :id: AND is_delete = :is_delete:';
        $op_where['value']['id'] = $id;
        $op_where['value']['is_delete'] = 0;
        $params['update_time'] = date('Y-m-d H:i:s',$time);

        # 判断是否有库存调整
        $front_inventory = $ret[0]['inventory']; # 修改前库存
        $behind_inventory = $front_inventory;
        if(isset($params['inventory_operation_type']) && isset($params['inventory_operation_value'])){
            # 操作库存类型(inventory_operation_type),0为新增,1为减少,默认为0
            if($params['inventory_operation_type'] == 0){
                $behind_inventory = (int)$front_inventory + (int)$params['inventory_operation_value']; # 修改后库存
            }else if($params['inventory_operation_type'] == 1){
                $behind_inventory= (int)$front_inventory - (int)$params['inventory_operation_value']; # 修改后库存
            }

            #礼品库存：不能修改，只能通过调整库存增加或减少
            $params['inventory'] = $behind_inventory;
            #库存为0的礼品需要自动下架
            if($behind_inventory == 0){
                $params['state'] = 0;
            }
        }

        $galaxyWechatIntegralGoodsModel->saveData($op_where, $params);
        if(!$galaxyWechatIntegralGoodsModel->getErrorResult()){
            # 若有库存调整,记录库存调整日志
            if(isset($params['inventory_operation_type']) && isset($params['inventory_operation_value'])){
                $log_params['front_inventory'] = $front_inventory;
                $log_params['behind_inventory'] = $behind_inventory;
                $log_params['operation_type'] = (int)$params['inventory_operation_type'];
                $log_params['inventory'] = (int)$params['inventory_operation_value'];
                $log_params['galaxy_wechat_integral_goods_id'] = $id;
                $galaxyWechatInventoryLogModel->createData($log_params);

                #将库存信息放到redis
                #di('redis')->save('goods:goods:num:'.$ret, trim($params['inventory']));
                di('redis')->hSet('goods:num', $id, $behind_inventory);
            }

            $this->msg = "礼品编辑成功";
            $this->code = 200;
            $this->data = true;
        }

        return $this->returnData();
    }

    /**
     * 礼品上架/下架
     * @return mixed
     */
    public function stateDataService($id, $params){
        $galaxyWechatIntegralGoodsModel = new GalaxyWechatIntegralGoods();
        $time = time();

        if(!isset($params['state']) || !in_array($params['state'], [0, 1])){
            $this->msg = "礼品上/下架状态异常";
            return $this->returnData();
        }

        $where['where'] = 'id = :id: AND is_delete = :is_delete:';
        $where['value']['id'] = $id;
        $where['value']['is_delete'] = 0;
        $galaxyWechatIntegralGoodsModel->getFindOne('', $where);
        $ret = $galaxyWechatIntegralGoodsModel->getSucceedResult(1);
        if(!$ret){
            $this->msg = "该礼品不存在";
            return $this->returnData();
        }

        switch ($params['state']){
            case 0: #下架操作
                if($ret[0]['state'] == 0){
                    $this->msg = "礼品已下架";
                    return $this->returnData();
                }

                $op_where['where'] = 'id = :id: AND is_delete = :is_delete:';
                $op_where['value']['id'] = $id;
                $op_where['value']['is_delete'] = 0;
                $updateData['state'] = 0;
                $updateData['update_time'] = date('Y-m-d H:i:s',$time);
                $galaxyWechatIntegralGoodsModel->saveData($op_where,$updateData);
                if(!$galaxyWechatIntegralGoodsModel->getErrorResult()){
                    $this->msg = "礼品下架成功";
                    $this->code = 200;
                    $this->data = true;
                    return $this->returnData();
                }

                break;
            case 1: #上架操作
                #判断礼品库存为0，不能上架
                if($ret[0]['inventory'] < 1){
                    $this->msg = "礼品库存小于1，不能上架";
                    return $this->returnData();
                }

                if($ret[0]['state'] == 1){
                    $this->msg = "礼品已上架";
                    return $this->returnData();
                }

                $op_where['where'] = 'id = :id: AND is_delete = :is_delete:';
                $op_where['value']['id'] = $id;
                $op_where['value']['is_delete'] = 0;
                $updateData['state'] = 1;
                $updateData['update_time'] = date('Y-m-d H:i:s',$time);
                $galaxyWechatIntegralGoodsModel->saveData($op_where,$updateData);
                if(!$galaxyWechatIntegralGoodsModel->getErrorResult()){
                    $this->msg = "礼品上架成功";
                    $this->code = 200;
                    $this->data = true;
                    return $this->returnData();
                }

                break;
            default:
                break;
        }
    }

    /**
     * 礼品排序
     * @return mixed
     */
    public function sortDataService($id, $params){
        $galaxyWechatIntegralGoodsModel = new GalaxyWechatIntegralGoods();
        #op 0:降序；1:升序
        $op = (int)$params['op'];
        $time = time();

//        if(isset($params['sort']) && !preg_match("/^[0-9][0-9]*$/",$params['sort'])){
//            $this->msg = "排序必须为大于或等于0的整数";
//            return $this->returnData();
//        }

        $where['where'] = 'id = :id: AND is_delete = :is_delete:';
        $where['value']['id'] = $id;
        $where['value']['is_delete'] = 0;
        $galaxyWechatIntegralGoodsModel->getFindOne('', $where);
        $ret = $galaxyWechatIntegralGoodsModel->getSucceedResult(1);
        if(!$ret){
            $this->msg = "该礼品不存在";
            return $this->returnData();
        }
        #当前排序
        $sort = $ret[0]['sort'];

        $fistAndLastSort = $this->getFistAndLastSortDataService(array());
        $firstSort = $fistAndLastSort['data']['first_sort']; #排序第一条sort值
        $lastSort = $fistAndLastSort['data']['end_sort'];#排序最后一条sort值
        
        # type 1: 只能降序; 2: 可升序或降序; 3:只能升序

        if($sort == $firstSort){ #只能降序
            if($op != 0){
                $this->msg = "第一条只能进行降序操作";
                return $this->returnData();
            }

            $data = $this->getChangeSortData($id, 1);
            #当前第一条操作
            $current_where['where'] = 'id = :id: AND is_delete = :is_delete:';
            $current_where['value']['id'] = $id;
            $current_where['value']['is_delete'] = 0;
            $current_update['sort'] = $data['sort'];
            $current_update['update_time'] = date('Y-m-d H:i:s',$time);
            $galaxyWechatIntegralGoodsModel->saveData($current_where,$current_update);

            #当前第一条操作的下一条
            $next_where['where'] = 'id = :id: AND is_delete = :is_delete:';
            $next_where['value']['id'] = $data['id'];
            $next_where['value']['is_delete'] = 0;
            $next_update['sort'] = $sort;
            $next_update['update_time'] = date('Y-m-d H:i:s',$time);
            $galaxyWechatIntegralGoodsModel->saveData($next_where, $next_update);

        }else if($sort < $firstSort && $sort > $lastSort){ #可升序或降序
            $sortRet = $this->getChangeSortData($id, 2);
            if($op == 0){ #降序
                #当前第一条操作
                $current_where['where'] = 'id = :id: AND is_delete = :is_delete:';
                $current_where['value']['id'] = $id;
                $current_where['value']['is_delete'] = 0;
                $current_update['sort'] = $sortRet[1]['sort'];
                $current_update['update_time'] = date('Y-m-d H:i:s',$time);
                $galaxyWechatIntegralGoodsModel->saveData($current_where,$current_update);

                #当前第一条操作的下一条
                #$sortRet = $this->getChangeSortData($id, 1);
                $op_where['where'] = 'id = :id: AND is_delete = :is_delete:';
                $op_where['value']['id'] = $sortRet[1]['id'];
                $op_where['value']['is_delete'] = 0;
                $op_update['sort'] = $sort;
                $op_update['update_time'] = date('Y-m-d H:i:s',$time);
                $galaxyWechatIntegralGoodsModel->saveData($op_where, $op_update);

            }else{ #升序
                #当前第一条操作
                $current_where['where'] = 'id = :id: AND is_delete = :is_delete:';
                $current_where['value']['id'] = $id;
                $current_where['value']['is_delete'] = 0;
                $current_update['sort'] = $sortRet[0]['sort'];
                $current_update['update_time'] = date('Y-m-d H:i:s',$time);
                $galaxyWechatIntegralGoodsModel->saveData($current_where,$current_update);

                #当前第一条操作的上一条
                #$data = $this->getChangeSortData($id, 3);
                $op_where['where'] = 'id = :id: AND is_delete = :is_delete:';
                $op_where['value']['id'] = $sortRet[0]['id'];
                $op_where['value']['is_delete'] = 0;
                $op_update['sort'] = $sort;
                $op_update['update_time'] = date('Y-m-d H:i:s',$time);
                $galaxyWechatIntegralGoodsModel->saveData($op_where, $op_update);
            }


        }else{ #只能升序
            if($op != 1){
                $this->msg = "最后一条只能进行升序操作";
                return $this->returnData();
            }

            $data = $this->getChangeSortData($id, 3);
            #当前第一条操作
            $current_where['where'] = 'id = :id: AND is_delete = :is_delete:';
            $current_where['value']['id'] = $id;
            $current_where['value']['is_delete'] = 0;
            $current_update['sort'] = $data['sort'];
            $current_update['update_time'] = date('Y-m-d H:i:s',$time);
            $galaxyWechatIntegralGoodsModel->saveData($current_where,$current_update);

            #当前第一条操作的上一条
            $front_where['where'] = 'id = :id: AND is_delete = :is_delete:';
            $front_where['value']['id'] = $data['id'];
            $front_where['value']['is_delete'] = 0;
            $front_update['sort'] = $sort;
            $front_update['update_time'] = date('Y-m-d H:i:s',$time);
            $galaxyWechatIntegralGoodsModel->saveData($front_where, $front_update);
        }

        $this->msg = "礼品排序成功";
        $this->code = 200;
        $this->data = true;

        return $this->returnData();
    }

    /**
     * 获取排序第一与排序最后的数据
     * @return mixed
     */
    public function getFistAndLastSortDataService($params){
        $galaxyWechatIntegralGoodsModel = new GalaxyWechatIntegralGoods();
        $where['where'] = 'is_delete = :is_delete:';
        $where['value']['is_delete'] = 0;
        $ret = $galaxyWechatIntegralGoodsModel->findall($where, $field="id, sort",$order="sort DESC")->toArray();
        $count = count($ret);
        $data = [];
        if($count > 0){
            $data = array(
                'first_id' => (int)$ret[0]['id'],
                'first_sort' => (int)$ret[0]['sort'],
                'end_id' => (int)$ret[$count - 1]['id'],
                'end_sort' => (int)$ret[$count - 1]['sort'],
            );
        }

        $this->msg = "获取排序第一与排序最后的数据";
        $this->code = 200;
        $this->data = $data;

        return $this->returnData();
    }

    /**
     * 获取当前排序相近的数据信息
     * @return mixed
     */
    public function getChangeSortData($id, $type){
        $galaxyWechatIntegralGoodsModel = new GalaxyWechatIntegralGoods();
        $where['where'] = 'is_delete = :is_delete:';
        $where['value']['is_delete'] = 0;
        $ret = $galaxyWechatIntegralGoodsModel->findall($where, $field="id, sort",$order="sort DESC")->toArray();
        $count = count($ret);
        $key = 0;
        if($ret){
            foreach($ret as $k => $v){
                if($v['id'] == $id){
                    $key=$k;
                }
            }
        }

        # type 1: 只能降序; 2: 可升序或降序; 3:只能升序
        if($type == 1) {
            return $ret[$key + 1];
        }else if($type == 2){
            return array(
                $ret[$key - 1],
                $ret[$key + 1],
            );
        }else{
            return $ret[$key - 1];
        }
    }

    /**
     * 积分礼品数量统计
     * 0-下架，1-上架
     * @return mixed
     */
    public function stateCountDataService($params){
        $galaxyWechatIntegralGoodsModel = new GalaxyWechatIntegralGoods();

        $data = array(
            array('state'=> 1, 'state_value'=>'上架', 'num' => 0),
            array('state'=> 0, 'state_value'=>'下架', 'num' => 0),
        );

        foreach($data as $k => $v){
            $where['where'] = 'state = :state: AND is_delete = :is_delete:';
            $where['value']['state'] = $v['state'];
            $where['value']['is_delete'] = 0;
            $data[$k]['num'] = $galaxyWechatIntegralGoodsModel->getCount($where);
        }

        $this->code = 200;
        $this->msg = '积分礼品数量统计';
        $this->data = $data;
        return $this->returnData();
    }

    /**
     * 礼品分类列表
     * @return mixed
     */
    public function classifyDataService(){
        $galaxyWechatClassifyModel = new GalaxyWechatClassify();
        $where['where'] = 'is_delete = :is_delete: AND state = :state:';
        $where['value']['is_delete'] = 0;
        $where['value']['state'] = 1;

        $galaxyWechatClassifyModel->getFindAll($select = 'id, classify_name',$where,$toLimit = array(),$toBy = array());
        $data = $galaxyWechatClassifyModel->getSucceedResult(1);
        if($data){
            foreach($data as $k => $v){
                $data[$k]['id'] = (int)$v['id'];
            }
        }

        $this->code = 200;
        $this->msg = '礼品分类列表';
        $this->data = $data;

        return $this->returnData();
    }

    /**
     * 字段验证
     * @return mixed
     */
    public function fieldValidate($params){

        # 一个汉字占3个字符
        if(isset($params['goods_name']) && (empty($params['goods_name']) || strlen($params['goods_name']) > 90)){
            $this->msg = "礼品名称不能为空或者长度不大于30个汉字";
            return $this->returnData();
        }

        if(isset($params['goods_outline']) && (empty($params['goods_outline']) || strlen($params['goods_outline']) > 180)){
            $this->msg = "礼品概要不能为空或者长度不大于60个汉字";
            return $this->returnData();
        }

        # 礼品类型:0为实体礼品,1为虚拟礼品;默认为0;
        if(isset($params['goods_type']) && !in_array($params['goods_type'],['0', '1'])){
            $this->msg = "礼品类型不能为空或不在选择范围内";
            return $this->returnData();
        }

        if(isset($params['galaxy_wechat_classify_id']) && empty($params['galaxy_wechat_classify_id'])){
            $this->msg = "礼品分类不能为空";
            return $this->returnData();
        }

        if(isset($params['inventory']) && ($params['inventory'] < 0)){
            $this->msg = "礼品库存必须不能为空或小于0";
            return $this->returnData();
        }

        if(!preg_match("/^[0-9][0-9]*$/",$params['inventory'])){
            $this->msg = "礼品库存必须为整数";
            return $this->returnData();
        }

        # 支付方式:0为积分支付,1为金钱支付,2为混合支付;默认为0;
        if(isset($params['pay_type']) && !in_array($params['pay_type'], ['0', '1', '2'])){
            $this->msg = "支付方式不能为空或不在选择范围内";
            return $this->returnData();
        }

        if(isset($params['integral_price']) && ($params['integral_price'] < 0 || $params['integral_price'] > 1000000)){
            $this->msg = "礼品单价不能为空或小于0且最大为1000000";
            return $this->returnData();
        }

        if(!preg_match("/^[1-9][0-9]*$/",$params['integral_price'])){
            $this->msg = "礼品单价必须为正整数";
            return $this->returnData();
        }

        if(isset($params['market_price']) && ($params['market_price'] < 0 || $params['market_price'] > 1000000)){
            $this->msg = "市场价值不能为空或小于0且最大为1000000";
            return $this->returnData();
        }

//        if (!preg_match('/^[1-9]+(.[0-9]{1,2})?$/', floatval(trim($params['market_price'])))) {
//            $this->msg = "市场价值为数字并且可保留2位小数 ";
//            return $this->returnData();
//        }
        if($this->getFloatLength($params['market_price'])>2){
            $this->msg = "市场价值为数字并且可保留2位小数 ";
            return $this->returnData();
        }

        # 配送方式:0为快递配送,1为上门自取
        if(isset($params['delivery_type']) && !in_array($params['delivery_type'], ['0', '1'])){
            $this->msg = "配送方式不能为空或不在选择范围内";
            return $this->returnData();
        }

        if(isset($params['goods_describe']) && empty($params['goods_describe'])){
            $this->msg = "礼品描述不能为空";
            return $this->returnData();
        }

        if(isset($params['litimg_url']) && empty($params['litimg_url'])){
            $this->msg = "礼品缩略图不能为空";
            return $this->returnData();
        }

        #多个用逗号隔开"a.php,b.png"
        if(isset($params['goods_img_url']) && empty($params['goods_img_url'])){
            $this->msg = "礼品图片不能为空";
            return $this->returnData();
        }

        #库存调整
        if(isset($params['inventory_operation_type']) && !in_array($params['inventory_operation_type'],['0', '1'])){
            $this->msg = "调整的礼品库存类型不能为空或不在选择范围内";
            return $this->returnData();
        }

        if(isset($params['inventory_operation_type']) && isset($params['inventory_operation_value']) && !preg_match("/^[1-9][0-9]*$/",$params['inventory_operation_value'])){
            $this->msg = "调整的礼品库存值必修为正整数";
            return $this->returnData();
        }

        # '操作库存类型,0为新增,1为减少,默认为0',
        if(isset($params['inventory_operation_type']) && isset($params['inventory_operation_value']) && isset($params['inventory'])){
            if(($params['inventory_operation_type'] == 1) && ($params['inventory_operation_value']) > $params['inventory']){
                $this->msg = "减少库存不能大于礼品原库存";
                return $this->returnData();
            }
        }

        $this->code = 200;
        $this->msg = '字段验证成功';
        $this->data = true;
        return $this->returnData();
    }

    public function getFloatLength($num) {
        $count = 0;
        $temp = explode('.', $num);
        if (sizeof($temp) > 1) {
            $decimal = end($temp);
            $count = strlen($decimal);
        }
        return $count;
    }

}