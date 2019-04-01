<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/24
 * Time: 14:41
 */
namespace Backend\Services;
use Api\Models\GalaxyWechatClassify;
use Api\Models\GalaxyWechatClassifyLog;
use Api\Models\GalaxyWechatIntegralGoods;

class GalaxyWechatClassifyServer extends BaseServer
{
    /**
     * 分类列表
     * @return mixed
     */
    public function searchDataService($params){
        $galaxyWechatClassifyModel = new GalaxyWechatClassify();
        $galaxyWechatIntegralGoodsModel = new GalaxyWechatIntegralGoods();

        $params['page'] = isset($params['page']) ? (int)$params['page'] : 1;
        $params['page_size'] = isset($params['page_size']) ? (int)$params['page_size'] : 10;
        $params['offset'] = ($params['page'] - 1) * $params['page_size'];

        # 获取分类列表信息
        $data = $galaxyWechatClassifyModel->getGoodsClassesData($params)->toArray();
        $temp = [];
        if($data){
            foreach($data as $k => $v){
                $temp[] = $v['id'];
                $data[$k]['id'] = (int)$v['id'];
                $data[$k]['state'] = (int)$v['state'];
                $data[$k]['is_delete'] = (int)$v['is_delete'];
                $data[$k]['classify_num'] = (int)$v['classify_num'];
            }
        }

        #获取礼品删除信息
        if($temp){
            $goodsDeleteData = $galaxyWechatIntegralGoodsModel->getGoodsClassesDeleteData($temp)->toArray();
            if($goodsDeleteData){
                foreach($goodsDeleteData as $k1 => $v1){
                    foreach($data as $k2 => $v2){
                        if($v1['galaxy_wechat_classify_id'] == $v2['id']){
                            # 礼品数量减去处于删除状态的礼品
                            $data[$k2]['classify_num'] = $data[$k2]['classify_num'] - $v1['delete_num'];
                        }
                    }
                }
            }
        }

        #礼品总量
        $where['where'] = 'is_delete = :is_delete:';
        $where['value']['is_delete'] = 0;
        $goodsClassifyNum = $galaxyWechatClassifyModel->getCount($where);

        return ['msg'=>'礼品分类列表', 'code'=>200, 'count'=> $goodsClassifyNum, 'data'=>$data];
    }

    /**
     * 分类详情
     * @return mixed
     */
    public function getDataService($id){
        $galaxyWechatClassifyModel = new GalaxyWechatClassify();

        $where['where'] = 'id = :id: AND is_delete = :is_delete:';
        $where['value']['id'] = $id;
        $where['value']['is_delete'] = 0;

        $galaxyWechatClassifyModel->getFindOne('', $where);

        $ret = $galaxyWechatClassifyModel->getSucceedResult(1);
        if(!$ret){
            $this->msg = "该礼品分类不存在或已删除";
            return $this->returnData();
        }

        $data = [];
        foreach($ret as $k=>$v){
            $data['id'] = (int)$v['id'];
            $data['classify_name'] = $v['classify_name'];
            $data['classify_describe'] = $v['classify_describe'];
            $data['state'] = (int)$v['state'];
            $data['is_delete'] = (int)$v['is_delete'];
            $data['create_time'] = $v['create_time'];
            $data['update_time'] = $v['update_time'];
        }

        $this->msg = "礼品分类详情";
        $this->code = 200;
        $this->data = $data;

        return $this->returnData();
    }

    /**
     * 分类增添
     * @return mixed
     */
    public function addDataService($params){
        $galaxyWechatClassifyModel = new GalaxyWechatClassify();

        # 一个汉字占3个字符
        if(isset($params['classify_name']) && (empty($params['classify_name']) || strlen($params['classify_name']) > 30)){
            $this->msg = "类别名称不能为空或者长度不大于10个汉字";
            return $this->returnData();
        }

        if(isset($params['classify_describe']) && (empty($params['classify_describe']) || strlen($params['classify_describe']) > 60)){
            $this->msg = "类别描述不能为空或者长度不大于20个汉字";
            return $this->returnData();
        }

        $ret = $galaxyWechatClassifyModel->createData($params);
        if($ret) {
            $this->msg = "礼品分类添加成功";
            $this->code = 200;
            $this->data = true;
        }

        return $this->returnData();
    }

    /**
     * 分类编辑
     * @return mixed
     */
    public function saveDataService($id, $params){
        $galaxyWechatClassifyModel = new GalaxyWechatClassify();
        $time = time();

        $where['where'] = 'id = :id: AND is_delete = :is_delete:';
        $where['value']['id'] = $id;
        $where['value']['is_delete'] = 0;
        $galaxyWechatClassifyModel->getFindOne('', $where);
        $ret = $galaxyWechatClassifyModel->getSucceedResult(1);
        if(!$ret){
            $this->msg = "该礼品分类不存在";
            return $this->returnData();
        }

        # 一个汉字占3个字符
        if(isset($params['classify_name']) && (empty($params['classify_name']) || strlen($params['classify_name']) > 30)){
            $this->msg = "类别名称不能为空或者长度不大于10个汉字";
            return $this->returnData();
        }

        if(isset($params['classify_describe']) && (empty($params['classify_describe']) || strlen($params['classify_describe']) > 60)){
            $this->msg = "类别描述不能为空或者长度不大于20个汉字";
            return $this->returnData();
        }

        $where['where'] = 'id = :id: AND is_delete = :is_delete:';
        $where['value']['id'] = $id;
        $where['value']['is_delete'] = 0;
        $updateData['classify_name'] = isset($params['classify_name']) ? $params['classify_name'] : $ret[0]['classify_name'];
        $updateData['classify_describe'] = isset($params['classify_describe']) ? $params['classify_describe'] : $ret[0]['classify_describe'];
        $updateData['update_time'] = date('Y-m-d H:i:s',$time);

        $galaxyWechatClassifyModel->saveData($where,$updateData);
        if(!$galaxyWechatClassifyModel->getErrorResult()){
            $this->msg = "礼品分类编辑成功";
            $this->code = 200;
            $this->data = true;
        }

        return $this->returnData();
    }

    /**
     * 分类停用/启用
     * @return mixed
     */
    public function stateDataService($id, $params){
        $galaxyWechatClassifyModel = new GalaxyWechatClassify();
        $time = time();

        if(!isset($params['state']) || !in_array($params['state'], [0, 1])){
            $this->msg = "礼品分类状态异常";
            return $this->returnData();
        }

        $where['where'] = 'id = :id: AND is_delete = :is_delete:';
        $where['value']['id'] = $id;
        $where['value']['is_delete'] = 0;
        $galaxyWechatClassifyModel->getFindOne('', $where);
        $ret = $galaxyWechatClassifyModel->getSucceedResult(1);
        if(!$ret){
            $this->msg = "该礼品分类不存在";
            return $this->returnData();
        }

        # 启动状态,0为关闭,1为开启
        switch ($params['state']){
            case 0: #停用操作，需判断该分类是否含有礼品（有:提示;无:直接停用）
                if($ret[0]['state'] == 0){
                    $this->msg = "该礼品分类已停用";
                    return $this->returnData();
                }

                $galaxyWechatIntegralGoodsModel = new GalaxyWechatIntegralGoods();
                $close_where['where'] = 'galaxy_wechat_classify_id = :galaxy_wechat_classify_id: AND is_delete = :is_delete:';
                $close_where['value']['galaxy_wechat_classify_id'] = $id;
                $close_where['value']['is_delete'] = 0;
                # 礼品数量
                $goodsNum = $galaxyWechatIntegralGoodsModel->getCount($close_where);
                if($goodsNum > 0){
                    $this->msg = "该礼品分类下还有礼品，请将该分类的礼品移动到其它分类后再停用";
                    return $this->returnData();
                }

                #停用操作
                $op_where['where'] = 'id = :id: AND is_delete = :is_delete:';
                $op_where['value']['id'] = $id;
                $op_where['value']['is_delete'] = 0;
                $updateData['state'] = 0;
                $updateData['update_time'] = date('Y-m-d H:i:s',$time);
                $galaxyWechatClassifyModel->saveData($op_where,$updateData);
                if(!$galaxyWechatClassifyModel->getErrorResult()){
                    $this->msg = "礼品分类停用成功";
                    $this->code = 200;
                    $this->data = true;
                    return $this->returnData();
                }
                break;
            case 1: #启用操作
                if($ret[0]['state'] == 1){
                    $this->msg = "该礼品分类已启用";
                    return $this->returnData();
                }

                $op_where['where'] = 'id = :id: AND is_delete = :is_delete:';
                $op_where['value']['id'] = $id;
                $op_where['value']['is_delete'] = 0;
                $updateData['state'] = 1;
                $updateData['update_time'] = date('Y-m-d H:i:s',$time);
                $galaxyWechatClassifyModel->saveData($op_where,$updateData);
                if(!$galaxyWechatClassifyModel->getErrorResult()){
                    $this->msg = "礼品分类开启成功";
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
     * 分类移动
     * @return mixed
     */
    public function moveDataService($id, $params){
        $galaxyWechatClassifyModel = new GalaxyWechatClassify();
        $galaxyWechatIntegralGoodsModel = new GalaxyWechatIntegralGoods();
        $galaxyWechatClassifyLogModel = new GalaxyWechatClassifyLog();
        $time = time();

        # 判断移动的分类
        $where['where'] = 'id = :id: AND is_delete = :is_delete:';
        $where['value']['id'] = $id;
        $where['value']['is_delete'] = 0;
        $galaxyWechatClassifyModel->getFindOne('', $where);
        $ret = $galaxyWechatClassifyModel->getSucceedResult(1);
        if(!$ret){
            $this->msg = "该礼品分类不存在";
            return $this->returnData();
        }

        # 判断被移动的分类
        if(!isset($params['move_id']) || empty($params['move_id'])){
            $this->msg = "被移动礼品分类参数不存在或者不能为空";
            return $this->returnData();
        }

        $move_where['where'] = 'id = :id:';
        $move_where['value']['id'] = (int)$params['move_id'];
        $galaxyWechatClassifyModel->getFindOne('', $move_where);
        $moveRet = $galaxyWechatClassifyModel->getSucceedResult(1);

        if(!$moveRet){
            $this->msg = "被移动礼品分类不存在";
            return $this->returnData();
        }

        if($moveRet[0]['state'] != 1){
            $this->msg = "被移动礼品分类须是启用中的";
            return $this->returnData();
        }

        if($id == $params['move_id']){
            $this->msg = "礼品已在该礼品分类下";
            return $this->returnData();
        }

        # 礼品分类转移操作
        $op_where['where'] = 'galaxy_wechat_classify_id = :galaxy_wechat_classify_id: AND is_delete = :is_delete:';
        $op_where['value']['galaxy_wechat_classify_id'] = $id; #操作前的分类ID
        $op_where['value']['is_delete'] = 0;
        $updateData['galaxy_wechat_classify_id'] = (int)$params['move_id']; #操作后的分类ID
        $updateData['update_time'] = date('Y-m-d H:i:s',$time);

        # 获取转移的商品ID集合
        $galaxyWechatIntegralGoodsModel->getFindAll($select = 'id',$op_where,$toLimit = array(),$toBy = array());
        $logRet = $galaxyWechatIntegralGoodsModel->getSucceedResult(1);
        if(empty($logRet)){
            $this->msg = "该礼品分类下没有相关商品信息";
            return $this->returnData();
        }

        # 转移商品分类的操作
        $galaxyWechatIntegralGoodsModel->saveData($op_where,$updateData);
        if(!$galaxyWechatIntegralGoodsModel->getErrorResult()){
            # 添加转移的商品ID集合记录
            $log_params['former_classify_id'] = $id; #操作前的分类ID
            $log_params['behind_classify_id'] = (int)$params['move_id'];#操作后的分类ID
            $log_params['goods_group_id'] = json_encode($logRet);
            $galaxyWechatClassifyLogModel->createData($log_params);

            $this->msg = "礼品分类移动成功";
            $this->code = 200;
            $this->data = true;
        }

        return $this->returnData();
    }

    /**
     * 分类移动-礼品分类列表
     * @return mixed
     */
    public function moveClassifyDataService(){
        $galaxyWechatClassifyModel = new GalaxyWechatClassify();
        $where['where'] = 'state = :state: AND is_delete = :is_delete:';
        $where['value']['state'] = 1;
        $where['value']['is_delete'] = 0;

        $galaxyWechatClassifyModel->getFindAll($select = 'id, classify_name',$where,$toLimit = array(),$toBy = array());
        $data = $galaxyWechatClassifyModel->getSucceedResult(1);
        if($data){
            foreach($data as $k => $v){
               $data[$k]['id'] = (int)$v['id'];
            }
        }

        $this->code = 200;
        $this->msg = '分类移动-礼品分类列表';
        $this->data = $data;

        return $this->returnData();
    }
}