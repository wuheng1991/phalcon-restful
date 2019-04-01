<?php

namespace Api\Services;

use Api\Models\GalaxyWechatActivity;
use Api\Models\GalaxyWechatActivityClient;
use Api\Models\GalaxyWechatClient;
use Api\Models\GalaxyAdmin;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 14:43
 */
class ActivityServer extends BaseServer
{

    //获取活动列表
    public function getActivityList($params,$user){

        //活动列表
        $galaxyWechatActivity = new GalaxyWechatActivity();
        $where['where']  = 'status=:status: and is_deleted=:is_deleted:';
        $where['value'][':status']  = 1;
        $where['value'][':is_deleted']  = 0;
        $field = "*";
        $order = "id DESC";
        $page = array('limit'=>$params['page_size'],'offset'=>($params['page']-1)*$params['page_size']);
        $data = $galaxyWechatActivity->findall($where,$field,$order,$page);
        if(empty($data)){
            $this->msg = '查询成功';
            $this->data = '';
            $this->code = 0;
            return $this->returnData();
        }else{
            $data = $data->toArray();
        }

        //查询下是否已经被预约
        $galaxyWechatActivityClient = new GalaxyWechatActivityClient();
        foreach ($data as $key => $value) {
            //截取展示
            $data[$key]['sign_in_time'] = date("Y-m-d",strtotime($value['sign_in_time']));
            $data[$key]['start_time'] = date("Y-m-d H:i",strtotime($value['start_time']));
            $data[$key]['end_time'] = date("Y-m-d H:i",strtotime($value['end_time']));

            $userWhere['where'] = 'galaxy_wechat_activity_id = :galaxy_wechat_activity_id: and galaxy_wechat_client_id=:galaxy_wechat_client_id:';
            $userWhere['value'][':galaxy_wechat_activity_id'] = $value['id'];
            $userWhere['value'][':galaxy_wechat_client_id'] = $user->id;
            $data[$key]['is_invited'] = 0;
            if($userData = $galaxyWechatActivityClient->findone($userWhere,'is_invited,id')){
                $data[$key]['is_invited'] = $userData['is_invited'];
            }

            if(!empty($value['share_thumb'])){
                $data[$key]['share_thumb'] = $user->url.$value['share_thumb'];
            }
            if(!empty($value['thumb'])){
                $data[$key]['thumb'] = $user->url.$value['thumb'];
            }
        }
        $this->msg = '查询成功';
        $this->data = $data;
        $this->code = 200;
        return $this->returnData();
    }

    //获取活动详情
    public function getActivityDetails($id,$owner,$user){
        $galaxyWechatActivityClient = new GalaxyWechatActivityClient();

        //查询下是否有登记
        $userWhere['where'] = 'galaxy_wechat_activity_id = :galaxy_wechat_activity_id: and galaxy_wechat_client_id=:galaxy_wechat_client_id:';
        $userWhere['value']['galaxy_wechat_activity_id'] = $id;
        $userWhere['value']['galaxy_wechat_client_id'] = $user->id;
        $userData = $galaxyWechatActivityClient->findone($userWhere,'is_invited,id');

        //将-100和-200撇开不作为顾问的标识
        if($owner <= 0){
            $owner = 0;
        }
        //若有顾问id，加上顾问标识
        //若为第一次，新增活动记录
        if (empty($userData)) {
            $addData['galaxy_admin_id'] = $owner;
            $addData['galaxy_wechat_activity_id'] = $id;
            $addData['galaxy_wechat_client_id'] = $user->id;
            $galaxyWechatActivityClient->add($addData);
        }else{
            $userData = $userData->toArray();
        }
        //活动详情
        $galaxyWechatActivity = new GalaxyWechatActivity();
        $where['where']  = 'id=:id:';
        $where['value']['id']  = $id;
        $data = $galaxyWechatActivity->findone($where,'*');
        if(empty($data)){
            $this->msg = '活动不存在';
            return $this->returnData();
        }else{
            $data = $data->toArray();
        }
        if($data['status'] == 0){
            $this->msg = '活动已关闭';
            return $this->returnData();
        }

        if($data['is_deleted'] == 1){
            $this->msg = '活动已删除';
            return $this->returnData();
        }

        // if(strtotime($data['end_time']) <= strtotime(date("Y-m-d H:i:s",time()))){
        //     $this->msg = '活动已过期';
        //     return $this->returnData();
        // }


        //追加是否预约过的标志
        if (!empty($userData)) {
            $data['is_invited'] = $userData['is_invited'];
        }else{
            $data['is_invited'] = 0;
        }

        if($data['is_invited'] == 0 ){
            if(strtotime($data['start_time']) >= strtotime(date("Y-m-d H:i:s",time()))){
                $data['is_invited'] = 2;
            }
        }

        if(!empty($data['share_thumb'])){
            $data['share_thumb'] = $user->url.$data['share_thumb'];
        }
        if(!empty($data['thumb'])){
            $data['thumb'] = $user->url.$data['thumb'];
        }
        // foreach ($data as $key => $value) {
        //     $data[$key]['content'] =  json_encode($value['content'], JSON_UNESCAPED_SLASHES);
        //     // $data[$key]['content'] =  json_decode(stripslashes($value['content']),true);
        //     // pr($data[$key]['content']);
        // }
        $this->msg = '查询成功';
        $this->data = $data;
        $this->code = 200;
        return $this->returnData();
    }

    //新增预约信息
    public function addSubscribeInfo($subscribe,$user){

        //验证验证码是否正确        
        $sms_obj = $this->di->get("SmsHelper");
        $code_res = $sms_obj->validateCode($subscribe['phone'],(int)$subscribe['code']);
       if($subscribe['code'] != 8888){
            //验证不通过
            if($code_res['code'] != 200){
                $this->msg = '请填写正确的验证码';
                return $this->returnData();
            }
       }

        //活动详情
        $galaxyWechatActivity = new GalaxyWechatActivity();
        $where['where']  = 'id=:id:';
        $where['value']['id']  = $subscribe['id'];
        $data = $galaxyWechatActivity->findone($where,'*');

        if(empty($data)){
            $this->msg = '活动不存在';
            return $this->returnData();
        }else{
            $data = $data->toArray();
        }
        if($data['status'] == 0){
            $this->msg = '活动已关闭';
            return $this->returnData();
        }

        if($data['is_deleted'] == 1){
            $this->msg = '活动已删除';
            return $this->returnData();
        }

        if(strtotime($data['end_time']) <= strtotime(date("Y-m-d H:i:s",time()))){
            if($data['sign_in_time'] != date("Y-m-d 00:00:00",time()) ){
                $this->msg = '报名已结束';
                return $this->returnData();
            }
        }

        //拼接新增预约成功的信息
        $galaxyWechatActivityClient = new GalaxyWechatActivityClient();

        //判断该号码是否是被使用过  
        $getWhere['where'] = 'phone=:phone: and galaxy_wechat_activity_id=:galaxy_wechat_activity_id:';
        $getWhere['value']['galaxy_wechat_activity_id'] = $subscribe['id'];
        $getWhere['value']['phone'] = $subscribe['phone'];
        $getData = $galaxyWechatActivityClient->findone($getWhere);
        if(!empty($getData)){
            // $this->msg = '您的电话被使用,请更换其他号码';
            // return $this->returnData();
        }

        //拼装新增的数据
        $addData['sign_up_time'] = date("Y-m-d H:i:s");
        $addData['is_invited'] = 1;
        $addData['username'] = $subscribe['username'];
        $addData['phone'] = $subscribe['phone'];

        $addWhere['where'] = 'galaxy_wechat_activity_id=:galaxy_wechat_activity_id: and galaxy_wechat_client_id=:galaxy_wechat_client_id:';
        $addWhere['value']['galaxy_wechat_activity_id'] = $subscribe['id'];
        $addWhere['value']['galaxy_wechat_client_id'] = $user->id;


        //查询该数据是存在
        $userData = $galaxyWechatActivityClient->findone($addWhere,'is_invited,id,galaxy_admin_id,username,phone');
        if (empty($userData)) {
            $addData['galaxy_admin_id'] = 0;
            $addData['galaxy_wechat_activity_id'] = $subscribe['id'];
            $addData['galaxy_wechat_client_id'] = $user->id;
            $addData['sign_up_time'] = date("Y-m-d H:i:s",time());
            $addData['is_invited'] = 1;
            $addData['username'] = $subscribe['username'];
            $addData['phone'] = $subscribe['phone'];
            if($galaxyWechatActivityClient->add($addData) === false){
                $this->msg = '预约失败';
                return $this->returnData();
            }
        }else{
            if($galaxyWechatActivityClient->updates($addWhere,$addData) === false){
                $this->msg = '预约失败';
                return $this->returnData();
            }
        }

        //预约成功之后将用户置为会员
        if(empty($user->phone)){

            //查询该收集是否被占用
            $galaxyWechatClient = new GalaxyWechatClient();
            $oneWhere['where'] = 'phone=:phone:';
            $oneWhere['value']['phone'] = $subscribe['phone'];
            $oneData = $galaxyWechatClient->findone($oneWhere);

            //判断该手机是否被占用，若没被占用则注册会员
            if(empty($oneData)){
                $updateWhere['where'] = 'id=:id:';
                $updateWhere['value']['id'] = $user->id;

                $updateData['phone'] = $subscribe['phone'];
                $updateData['vip'] = 1;

                $smsObj = $this->di->get("SmsHelper");
                $address = $smsObj->actionGetAreaByMobile($subscribe['phone']);
                $updateData['phone_address'] = '';
                if(!empty($address)){
                    $updateData['phone_address'] = $address['address'];
                }
        
                $galaxyWechatClient = new GalaxyWechatClient();
                $galaxyWechatClient->updates($updateWhere,$updateData);
            }
        }
        //判断该预约活动是否有顾问标识，有则进行消息推送
        if(!empty($userData) && ($userData->galaxy_admin_id != 0)){
            //查询所属顾问
            $galaxyAdmin = new GalaxyAdmin();
            $adminWhere['where'] = 'id = :id:';
            $adminWhere['value']['id'] = $userData->galaxy_admin_id;
            $galaxyAdmin->getFindOne('id,username,work_wechat_id,assist_username,assist_work_wechat_id',$adminWhere);

            
            //获取数据
            $oneData = $galaxyAdmin->getSucceedResult(1);
           

            if(!empty($oneData)){
                if(!empty($oneData[0]['work_wechat_id'])){
                    $workWechatId[] = $oneData[0]['work_wechat_id'];
                    $applyType = 'activity';
                    $this->wechatwork->accesstoken($applyType);
                    $this->wechatwork->settingsTouser($workWechatId);
                    $msgType = 'markdown';
                    $msgs = '您的客户已预约了活动，可以同客户取得联系哦~
> 活动名称：'.$data['title'].'   
> 客户姓名：'.$subscribe['username'].'     
> 客户电话：'.$subscribe['phone'].'    
> 邀约顾问：'.$oneData[0]['username'].'   
';
                    $this->wechatwork->sendMessage($applyType,$msgType,$msgs);
                }
            }
        }

        $this->msg = '预约成功';
        $this->data = '';
        $this->code = 200;
        return $this->returnData();
    }

    //签到接口
    public function getSignIn($id,$user){
        //活动详情
        $galaxyWechatActivity = new GalaxyWechatActivity();
        $where['where']  = 'id=:id:';
        $where['value']['id']  = $id;
        $data = $galaxyWechatActivity->findone($where,'*');
        if(empty($data)){
            $this->msg = '活动不存在';
            return $this->returnData();
        }else{
            $data = $data->toArray();
        }

        if($data['status'] == 0){
            $this->msg = '活动已关闭';
            return $this->returnData();
        }

        if($data['is_deleted'] == 1){
            $this->msg = '活动已删除';
            return $this->returnData();
        }

        if(strtotime($data['end_time']) <= strtotime(date("Y-m-d H:i:s",time()))){
            $this->msg = '活动已过期';
            return $this->returnData();
        }

        //获取签到的详情,查询下是否有登记
        $galaxyWechatActivityClient = new GalaxyWechatActivityClient();
        $userWhere['where'] = 'galaxy_wechat_activity_id = :galaxy_wechat_activity_id: and galaxy_wechat_client_id=:galaxy_wechat_client_id:';
        $userWhere['value']['galaxy_wechat_activity_id'] = $id;
        $userWhere['value']['galaxy_wechat_client_id'] = $user->id;
        $userData = $galaxyWechatActivityClient->findone($userWhere,'is_sign_up,galaxy_admin_id');
        $return['is_sign_in'] = 0;


        if(!empty($userData) && ($userData->is_sign_up == 1)){
            $return['is_sign_in'] = 1;
        }

        $this->msg = '查询成功';
        $this->data = $return;
        $this->code = 200;
        return $this->returnData();
    }

    //签退接口
    public function getSignOut($id,$user){
        
        //获取签到的详情
        $galaxyWechatActivityClient = new GalaxyWechatActivityClient();
        $galaxyWechatActivityClient->findone();

        //查询下是否有登记
        $userWhere['where'] = 'galaxy_wechat_activity_id = :galaxy_wechat_activity_id: and galaxy_wechat_client_id=:galaxy_wechat_client_id:';
        $userWhere['value']['galaxy_wechat_activity_id'] = $id;
        $userWhere['value']['galaxy_wechat_client_id'] = $user->id;
        $userData = $galaxyWechatActivityClient->findone($userWhere,'is_sign_back,departure_time');

        $return['is_sign_out'] = 0;
        if(empty($userData)){
            $userData = '';
        }else{
            $userData = $userData->toArray();
            $return['is_sign_out'] = $userData['is_sign_back'];
        }   

        $this->msg = '查询成功';
        $this->data = $return;
        $this->code = 200;
        return $this->returnData();
    }
    
    //签到接口
    public function saveSignIn($id,$user){
        //活动详情
        $galaxyWechatActivity = new GalaxyWechatActivity();
        $where['where']  = 'id=:id:';
        $where['value']['id']  = $id;
        $data = $galaxyWechatActivity->findone($where,'*');

        if(empty($data)){
            $this->msg = '活动不存在';
            return $this->returnData();
        }else{
            $data = $data->toArray();
        }

        if($data['status'] == 0){
            $this->msg = '活动已关闭';
            return $this->returnData();
        }

        if($data['is_deleted'] == 1){
            $this->msg = '活动已删除';
            return $this->returnData();
        }

        // if(strtotime($data['end_time']) <= strtotime(date("Y-m-d H:i:s",time()))){
        //     $this->msg = '活动已过期';
        //     return $this->returnData();
        // }

        if(strtotime($data['start_time']) >= strtotime(date("Y-m-d H:i:s",time()))){
            $this->msg = '活动未开始';
            return $this->returnData();
        }

        if($data['sign_in_time'] != date("Y-m-d 00:00:00",time()) ){
            $this->msg = '还未到签到时间，请勿签到哦';
            return $this->returnData();
        }

        //获取签到的详情
        $galaxyWechatActivityClient = new GalaxyWechatActivityClient();
        $galaxyWechatActivityClient->findone();

        //查询下是否有登记
        $userWhere['where'] = 'galaxy_wechat_activity_id = :galaxy_wechat_activity_id: and galaxy_wechat_client_id=:galaxy_wechat_client_id: and is_invited=:is_invited:';
        $userWhere['value']['galaxy_wechat_activity_id'] = $id;
        $userWhere['value']['galaxy_wechat_client_id'] = $user->id;
        $userWhere['value']['is_invited'] = 1;
        $userData = $galaxyWechatActivityClient->findone($userWhere,'*');

        if(empty($userData)){
            $this->msg = '未预约活动';
            $this->code = 201;
            return $this->returnData();
        }else{
            $userData = $userData->toArray();
        }

        //判断是否重复签到
        if($userData['is_sign_up'] == 1){
            $this->msg = '请勿重复签到，并刷新页面';
            return $this->returnData();
        }

        //记录签到时间
        $updateWhere['where'] = 'galaxy_wechat_activity_id = :galaxy_wechat_activity_id: and galaxy_wechat_client_id=:galaxy_wechat_client_id:';
        $updateWhere['value']['galaxy_wechat_activity_id'] = $id;
        $updateWhere['value']['galaxy_wechat_client_id'] = $user->id;

        $updateData['present_time'] = date("Y-m-d H:i:s");
        $updateData['is_sign_up'] = 1;
        $galaxyWechatActivityClient = new GalaxyWechatActivityClient();
        if($galaxyWechatActivityClient->updates($updateWhere,$updateData) == false){
            $this->msg = '签到失败';
             return $this->returnData();
        }

        //判断所属顾问是否为空
        if(!empty($userData['galaxy_admin_id'])){

            //查询所属顾问
            $galaxyAdmin = new GalaxyAdmin();
            $adminWhere['where'] = 'id = :id:';
            $adminWhere['value']['id'] = $userData['galaxy_admin_id'];
            $galaxyAdmin->getFindOne('id,username,work_wechat_id,assist_username,assist_work_wechat_id',$adminWhere);

            //获取数据
            $oneData = $galaxyAdmin->getSucceedResult(1);
            if(!empty($oneData)){
                if(!empty($oneData[0]['work_wechat_id'])){
                    //若有协助顾问则发给协助顾问
                    // if(!empty($oneData[0]['assist_work_wechat_id'])){
                        // $workWechatId[] = $oneData[0]['assist_work_wechat_id'];
                    // }else{
                    $workWechatId[] = $oneData[0]['work_wechat_id'];
                    // }
                    $applyType = 'activity';
                    $this->wechatwork->accesstoken($applyType);
                    $this->wechatwork->settingsTouser($workWechatId);
                    $msgType = 'markdown';
                    $msgs = '您的客户已到现场签到，赶紧出去接待哦~
> 活动名称：'.$data['title'].'   
> 客户姓名：'.$userData['username'].'     
> 客户电话：'.$userData['phone'].'    
> 邀约顾问：'.$oneData[0]['username'].'   
';
                    $this->wechatwork->sendMessage($applyType,$msgType,$msgs);
                }
            }

        }

        $this->msg = '签到成功';
        $this->data = '';
        $this->code = 200;
        return $this->returnData();
    }

    //签退接口
    public function saveSignOut($id,$user){
        //活动详情
        $galaxyWechatActivity = new GalaxyWechatActivity();
        $where['where']  = 'id=:id:';
        $where['value']['id']  = $id;
        $data = $galaxyWechatActivity->findone($where,'*');

        if(empty($data)){
            $this->msg = '活动不存在';
            return $this->returnData();
        }else{
            $data = $data->toArray();
        }

        if($data['status'] == 0){
            $this->msg = '活动已关闭';
            return $this->returnData();
        }

        if($data['is_deleted'] == 1){
            $this->msg = '活动已删除';
            return $this->returnData();
        }

        // if(strtotime($data['end_time']) <= strtotime(date("Y-m-d H:i:s",time()))){
        //     $this->msg = '活动已过期';
        //     return $this->returnData();
        // }

        if(strtotime($data['start_time']) >= strtotime(date("Y-m-d H:i:s",time()))){
            $this->msg = '活动未开始';
            return $this->returnData();
        }

        if($data['sign_in_time'] != date("Y-m-d 00:00:00",time()) ){
            $this->msg = '还未到签退时间，请勿签退哦'; 
            return $this->returnData();
        }

        //获取签到的详情
        $galaxyWechatActivityClient = new GalaxyWechatActivityClient();
        $galaxyWechatActivityClient->findone();

        //查询下是否有登记
        $userWhere['where'] = 'galaxy_wechat_activity_id = :galaxy_wechat_activity_id: and galaxy_wechat_client_id=:galaxy_wechat_client_id: and is_invited=:is_invited:';
        $userWhere['value']['galaxy_wechat_activity_id'] = $id;
        $userWhere['value']['galaxy_wechat_client_id'] = $user->id;
        $userWhere['value']['is_invited'] = 1;
        $userData = $galaxyWechatActivityClient->findone($userWhere,'*');

        if(empty($userData)){
            $this->msg = '未预约活动';
            $this->code = 201;
            return $this->returnData();
        }else{
            $userData = $userData->toArray();
        }
        if($userData['departure_time'] != ''){
            $this->msg = '请勿重复签退，并刷新页面';
            return $this->returnData();
        }

        //记录签退时间
        // $updateWhere['where'] = 'id=:id:';
        // $updateWhere['value']['id'] = $user->id;

        // $updateData['departure_time'] = date("Y-m-d H:i:s");
        // $galaxyWechatClient = new GalaxyWechatClient();
        // if($galaxyWechatClient->updates($updateWhere,$updateData) == false){
        //     $this->msg = '签退失败';
        //      return $this->returnData();
        // }
        //记录签退时间
        $updateWhere['where'] = 'galaxy_wechat_activity_id = :galaxy_wechat_activity_id: and galaxy_wechat_client_id=:galaxy_wechat_client_id:';
        $updateWhere['value']['galaxy_wechat_activity_id'] = $id;
        $updateWhere['value']['galaxy_wechat_client_id'] = $user->id;


        $updateData['departure_time'] = date("Y-m-d H:i:s",time());
        $updateData['is_sign_back']=1;

        $galaxyWechatActivityClient = new GalaxyWechatActivityClient();
        if($galaxyWechatActivityClient->updates($updateWhere,$updateData) == false){
            $this->msg = '签退失败';
             return $this->returnData();
        }


        $this->msg = '签退成功';
        $this->data = '';
        $this->code = 200;
        return $this->returnData();
    }
}