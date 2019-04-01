<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 11:41
 */

namespace Backend\Services;
use Api\Models\GalaxyWechatActivity;
use Api\Models\GalaxyWechatActivityUser;
use Api\Models\GalaxyAdmin;

class GalaxyWechatActivityServer extends BaseServer
{
    /**
     * 活动增添
     * @return mixed
     */
    public function addDataService($params){
        $result = ['msg' => '', 'code' => 0, 'data' => false];

        $galaxyWechatActivityModel = new GalaxyWechatActivity();
        $attributes = $galaxyWechatActivityModel->getAttributes();

        foreach($attributes as $key => $value){
            if(isset($params[$value])) {
                $params[$value] = isset($params[$value]) ? trim($params[$value]) : '';
            }

            if($value == 'thumb' || $value == 'share_thumb') {
                $thumbTempArray = explode('/', $params[$value]);
                $thumbImg = '';
                if($thumbTempArray){
                    $thumbImg = '/'.$thumbTempArray[3].'/'.$thumbTempArray[4].'/'.$thumbTempArray[5].'/'.$thumbTempArray[6];
                }
                $params[$value] = $thumbImg ? $thumbImg : '';
            }
        }

        //活动开始时间与结束时间的比较
        if(isset($params['start_time']) && isset($params['end_time'])){
            if(strtotime($params['start_time']) > strtotime($params['end_time'])){
                $result['msg'] = '活动开始时间不能小于结束时间';
                return $result;
            }
        }

        //活动ID
        $activity_id = $galaxyWechatActivityModel->add($params, true);
        if($activity_id){
            // type 1:活动顾问二维码(一个或多个);2:签到二维码(一个);3:签退二维码(一个);4:主题二维码(一个)

            // (签退，签到) 生产二维码
            // 设置活动产生的签到，签退二维码存放的文件夹
            $qrcode_dir= './img/backend/qrcode/';
            if(!file_exists($qrcode_dir)){
                mkdir($qrcode_dir,0777,true);
            }

            // 设置活动产生的顾问二维码存放的文件夹
            $admin_qrcode_dir = './img/backend/qrcode/activity-'.$activity_id.'/';
            if(!file_exists($admin_qrcode_dir)){
                mkdir($admin_qrcode_dir,0777,true);
            }

            $activity_title = isset($params['title']) ? $params['title'] : '';
            $galaxyWechatActivityModel->addOneQrcode([2,3,4],$activity_id, $activity_title, $qrcode_dir);

            // 客户id,username,company
            // 增添主管的活动二维码 cece-58，miranda-30，hera-114，cathy-158
            $master_setting = "30, 58, 114, 158";
            if(!empty($params['employee_setting'])){
                $params['employee_setting'] = $params['employee_setting'].",".$master_setting;
            }else{
                $params['employee_setting'] = $master_setting;
            }

            $employee_setting_array = $galaxyWechatActivityModel->getUserData($params['employee_setting']);
            if($employee_setting_array){
                //获取分公司项目信息
                $crm_region_info = di('redis')->setIndex(1)->get('initialize:region:key');
                $crm_region_info = json_decode($crm_region_info,true);
                foreach($employee_setting_array as $key => $value){
                    $employee_setting_array[$key]['company'] = isset($crm_region_info[$value['company']]) ? $crm_region_info[$value['company']] : '';
                }

                //pr($employee_setting_array);
                // 活动顾问 生产二维码
                $galaxyWechatActivityModel->addMoreQrcode([1],$activity_id, $admin_qrcode_dir,$employee_setting_array,trim($params['title']));

                $result=[
                    'msg' => '活动以及相关二维码信息创建成功',
                    'code' => 200,
                    'data' => true
                ];
            }else{
                $result['msg'] = '活动员工设置不能为空或不正确';
            }

        }else{
            $result['msg'] = '活动添加失败';
        }

        return $result;
    }

    /**
     * 活动修改
     * @return mixed
     */
    public function saveDataService($id, $params){
        $result = ['msg' => '', 'code' => 0, 'data' => false];

        $galaxyWechatActivityModel = GalaxyWechatActivity::findFirstByid($id);
        $employee_setting = $galaxyWechatActivityModel->employee_setting;
        $attributes = $galaxyWechatActivityModel->getAttributes();

        if(!$galaxyWechatActivityModel){
            $result['msg'] = '活动不存在';
            return $result;
        }

        if(($galaxyWechatActivityModel->has_open_status == '1') && (strtotime($galaxyWechatActivityModel->end_time) <= strtotime(date("Y-m-d H:i:s", time())))){
            $result['msg'] = '活动已开启并过期,不能修改';
            return $result;
        }

        foreach($attributes as $key => $value){
            if(isset($params[$value])) {
                if($value == 'thumb' || $value == 'share_thumb') {
                    $thumbTempArray = explode('/', $params[$value]);
                    $thumbImg = '';
                    if($thumbTempArray){
                        $thumbImg = '/'.$thumbTempArray[3].'/'.$thumbTempArray[4].'/'.$thumbTempArray[5].'/'.$thumbTempArray[6];
                    }

                    $params[$value] = $thumbImg ? $thumbImg : '';
                }else if($value == 'status'){
                    //判断活动是否开启过
                    if($galaxyWechatActivityModel->has_open_status == '0'){
                        $params['has_open_status'] = 1;
                    }
                 }else{
                    $params[$value] = isset($params[$value]) ? trim($params[$value]) : '';
                 }
            }
        }

        //活动开始时间与结束时间的比较
        if(isset($params['start_time']) && isset($params['end_time'])){
            if(strtotime($params['start_time']) > strtotime($params['end_time'])){
                $result['msg'] = '活动开始时间不能小于结束时间';
                return $result;
            }
        }



        $activityRet = $galaxyWechatActivityModel->saveData($params);
        if($activityRet['code'] == 200){

            //判断员工设置是否修改
            if(isset($params['employee_setting']) && ($params['employee_setting'] != $employee_setting)){
                $galaxyWechatActivityUserModel = new GalaxyWechatActivityUser();

                $employee_setting_old_array = explode(',', $employee_setting);
                $employee_setting_new_array = explode(',', $params['employee_setting']);

                $qrcode_dir = './img/backend/qrcode/activity-'.$galaxyWechatActivityModel->id.'/';//存放在当前目录文件夹下
                if(!file_exists($qrcode_dir)){
                    mkdir($qrcode_dir,0777,true);
                }

                //新增
                $employee_setting_array = $galaxyWechatActivityModel->getUserData($params['employee_setting']);
                if($employee_setting_array){
                    //获取分公司项目信息
                    $crm_region_info = di('redis')->setIndex(1)->get('initialize:region:key');
                    $crm_region_info = json_decode($crm_region_info,true);

                    foreach($employee_setting_array as $k=>$v){
                        if(!in_array($v['id'], $employee_setting_old_array)){
                            $company = isset($crm_region_info[$v['company']]) ? $crm_region_info[$v['company']] : '';
//                            $qrcode_link = '/activity/'.$galaxyWechatActivityModel->id.'/'.$v['id'];
                            $qrcode_link = '/activeEnroll/'.$galaxyWechatActivityModel->id.'/'.$v['id'];
                            $qrcode_address = ltrim($qrcode_dir, '.').$company.'-'.$v['username'].'.png';
                            $data = array(
                                'galaxy_wechat_activity_id' => $galaxyWechatActivityModel->id,
                                'galaxy_admin_id' => (int)$v['id'],
                                'qrcode_link' => $qrcode_link,
                                'qrcode_address'=> $qrcode_address,
                                'type' => 1,
                            );
                            $clone = clone $galaxyWechatActivityUserModel; //克隆一个新对象，使用新对象来调用create()函数
                            $result = $clone->create($data);
                            $galaxyWechatActivityModel->createQrcode($qrcode_link, $qrcode_address);
                        }
                    }
                }

                //软删除
                if($employee_setting_old_array){
                    $updateData = [];
                    foreach($employee_setting_old_array as $k1 => $v1){
                        if(!in_array($v1, $employee_setting_new_array)){
                             $updateData[] = $v1;
                        }
                    }

                    if($updateData){
                        $updateData = $galaxyWechatActivityModel->getUserData(implode(',',$updateData));
                        $updateRet = $galaxyWechatActivityUserModel->updateActivityData($galaxyWechatActivityModel->id, $updateData);
                    }
                }
            }

            return $activityRet;
        }

        return $result;
    }

    /**
     * 发送二维码
     * @return mixed
     */
    public function sendActivityQr($params){

        //判断活动标识是否存在
        if(!isset($params['activity_id'])){
            $this->msg = '活动标识异常';
            return $this->returnData();
        }else{
            $activityId = $params['activity_id'];
        }

        $galaxyWechatActivity = new GalaxyWechatActivity();
        //判断活动是否存在并且开启
        $where['where'] = 'id = :id: and status="1" and is_deleted = "0"';
        $where['value']['id'] = $activityId;
        $activityData = $galaxyWechatActivity->findone($where);

        if(empty($activityData)){
            $this->msg = '活动不存在';
            return $this->returnData();
        }


        //判断活动是否已发送过，若为单人发送则不限制
        if(empty($params['owner_id']) && $activityData->is_mail == 1){
            $this->msg = '该活动已群发';
            return $this->returnData();
        }

        $galaxyAdmin = new GalaxyAdmin();

        //获取顾问的所有邮箱地址
        if(empty($params['owner_id'])){
            $adminId = $activityData->employee_setting;
        }else{
            $adminId = $params['owner_id'];
        }
        $adminWhere['where'] = 'FIND_IN_SET(id,:id:)';
        $adminWhere['value']['id'] = $adminId;
        $galaxyAdmin->getFindAll('*',$adminWhere);
        $ownerData = $galaxyAdmin->getSucceedResult(1);
        $galaxyWechatActivityUser = new GalaxyWechatActivityUser();
        //获取顾问二维码
        $qrWhere['where'] = 'galaxy_wechat_activity_id = :galaxy_wechat_activity_id: and FIND_IN_SET(galaxy_admin_id,:galaxy_admin_id:)';
        $qrWhere['value']['galaxy_wechat_activity_id'] = $activityData->id;
        $qrWhere['value']['galaxy_admin_id'] = $adminId;
        $galaxyWechatActivityUser->getFindAll('*',$qrWhere);
        $qrData = $galaxyWechatActivityUser->getSucceedResult(1);

        foreach ($ownerData as $key => $value) {
            $ownerData[$key]['qr_imgurl'] = '';
            foreach ($qrData as $_key => $_value) {
                if($_value['galaxy_admin_id'] == $value['id']){
                    $ownerData[$key]['qr_imgurl'] = di('config')->wechat_back_url.$_value['qrcode_address'];
                    unset($qrData[$_key]);
                }
            }
        }

        //发送邮件
        foreach ($ownerData as $key => $value) {
            $msg = '<section style="width: 845px;margin: 20px auto;">
<div style="padding:20px;margin: 16px 0;background: #262626;box-shadow: 10px 10px 10px -5px #e1e1e1;" >
    <img title="银河移民为您服务" class="lf logo" alt="银河移民为您服务" src="http://cache.galaxy-immi.com/PC/img/public/footerlogo1.png" >
    
    
    <div style="margin-left: 32px;" style="color:#FFFFFF">
        <p style="color:#FFFFFF"><b>活动小助手给</b><span>'.$value["username"].'顾问送来二维码啦~</span></p>
        <p style="color:#FFFFFF"><b>本次活动为：</b><span>'.$activityData->title.'</span></p>
        <p style="color:#FFFFFF"><b>您的二维码是：</b></p>
        <img title="银河移民为您服务" class="lf logo" alt="银河移民为您服务" src="'.$value['qr_imgurl'].'" >

    </div>
</div>
</section>';
            $title = '活动小助手二维码发送';
            $toEmail = $value['email'];
            $this->mail->sendEmail($toEmail,$title,$msg);
        }

        //发送结束则将活动发送标识置为1
        $this->msg = "发送成功";
        $this->code = 200;
        $this->data = '';

        return $this->returnData();
    }
}