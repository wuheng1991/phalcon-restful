<?php

namespace Api\models;
use Api\Models\GalaxyAdmin;
use Api\Models\GalaxyWechatClient;
use Api\Models\GalaxyWechatActivityClient;
use Api\Models\GalaxyWechatActivityUser;
use Phalcon\Config;

class GalaxyWechatActivity extends \Phalcon\Mvc\Model
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
    public $title;

    /**
     *
     * @var string
     */
    public $start_time;

    /**
     *
     * @var string
     */
    public $end_time;

    /**
     *
     * @var string
     */
    public $sign_in_time;

    /**
     *
     * @var string
     */
    public $sign_back_time;

    /**
     *
     * @var string
     */
    public $address;

    /**
     *
     * @var string
     */
    public $thumb;

    /**
     *
     * @var string
     */
    public $content;

    /**
     *
     * @var string
     */
    public $employee_setting;

    /**
     *
     * @var string
     */
    public $share_title;

    /**
     *
     * @var string
     */
    public $share_description;

    /**
     *
     * @var string
     */
    public $share_thumb;

    /**
     *
     * @var string
     */
    public $has_open_status;

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
    public $status;

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
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxyWechatActivity[]|GalaxyWechatActivity|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxyWechatActivity|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findFirstByid($id)
    {
        return parent::findFirstByid($id);
//        return self::findFirstByid($id);
    }

    public function getAttributes(){
        return $this -> getModelsMetaData() -> getAttributes($this);
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

    /**
     * description 对上传base64图片的处理
     * @param string $base64_img
     * @param string $dir
     * @param string $name
     * @return data
     */
    public function dealBase64Thumb($base64_img, $dir, $name){
        $ret = [ 'msg' => '', 'code' => 0, 'data' => false];
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                $new_file = $dir.$name.'-'.date('YmdHis').'.'.$type;
                $base64_new = str_replace($result[1],'', $base64_img);
                if(file_put_contents($new_file, base64_decode($base64_new))){
                    //$img_path = str_replace('.', '', $new_file);
                    $ret = array(
                        'msg' => '上传图片成功',
                        'code' => 200,
                        'data' => ltrim($new_file,'.')
                    );
                }else{
                    $ret['msg'] = '上传图片失败,字段：'.$name;
                }
            }else{
                $ret['msg'] = '上传文件类型错误,字段：'.$name;
            }
        }else{
            $ret['msg'] = '上传文件错误,字段：'.$name;
        }

        return $ret;
    }

    #产生二维码
    public function createQrcode($qrcode_link, $qrcode_address){
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 6;//生成图片大小
//        $apiUrl = $this->di->get('config')->qrcodeUrl;
        $apiUrl = di('config')->wechat_front_url;
        \QRcode::png($apiUrl.$qrcode_link,'.'.$qrcode_address, $errorCorrectionLevel, $matrixPointSize, 2);
    }

    /**
     * description 对产生的一个二维码处理
     * @param mixed $data
     * @param string $activity_id
     * @param string $dir
     * @return data
     */
    public function getOneQrcode($data, $activity_id, $dir){
        $galaxyWechatActivityUserModel = new GalaxyWechatActivityUser();
        if($data){
            foreach($data as $key => $value){
                $qrcode_link = '/activity/'.$activity_id.'/type/'.$value;
                $qrcode_address = ltrim($dir, '.').'activity-'.$activity_id.'-type-'.$value.'.png';
                $data = array(
                    'galaxy_wechat_activity_id' => $activity_id,
                    'galaxy_admin_id' => 0,
                    'qrcode_link' => $qrcode_link,
                    'qrcode_address'=> $qrcode_address,
                    'type' => (int)$value,
                );
                $clone = clone $galaxyWechatActivityUserModel; //克隆一个新对象，使用新对象来调用create()函数
                $result = $clone->create($data);
                if (!$result) {
                    $errorMessage = implode(',', $clone->getMessages());
                    return ['msg' => '活动生成签到/签退二维码失败', 'code' => 0, 'data' => $errorMessage];
                }
                //生成二维码图片
                $this->createQrcode($qrcode_link, $qrcode_address);
            }
        }
    }

    /**
     * description 对产生的一个二维码处理
     * @param mixed $data
     * @param string $activity_id
     * @param string $dir
     * @return data
     */
    public function addOneQrcode($data, $activity_id, $activity_title='', $dir){
        $galaxyWechatActivityUserModel = new GalaxyWechatActivityUser();
        if($data){
            foreach($data as $key => $value){
                # 2:签到二维码(一个);3:签退二维码(一个);4:主题二维码(一个)
                $qrcode_address = ltrim($dir, '.').'activity-'.$activity_id.'-type-'.$value.'.png';

                if($value == 2){
                    $qrcode_link = '/signIn/'.$activity_id;
                }else if($value == 3){
                    $qrcode_link = '/signOut/'.$activity_id;
                }else if($value == 4){
                    $qrcode_link = '/activeEnroll/'.$activity_id.'/0';
                    $qrcode_address = ltrim($dir, '.').'activity-'.$activity_id.'/后台.png';
                }

                $data = array(
                    'galaxy_wechat_activity_id' => $activity_id,
                    'galaxy_admin_id' => 0,
                    'qrcode_link' => $qrcode_link,
                    'qrcode_address'=> $qrcode_address,
                    'type' => (int)$value,
                );
                $clone = clone $galaxyWechatActivityUserModel; //克隆一个新对象，使用新对象来调用create()函数
                $result = $clone->create($data);
                if (!$result) {
                    $errorMessage = implode(',', $clone->getMessages());
                    return ['msg' => '活动生成签到/签退二维码失败', 'code' => 0, 'data' => $errorMessage];
                }
                //生成二维码图片
                $this->createQrcode($qrcode_link, $qrcode_address);
            }
        }
    }

    /**
 * description 对产生的多个二维码处理
 * @param mixed $data
 * @param string $activity_id
 * @param string $dir
 * @return data
 */
    public function getMoreQrcode($data, $activity_id, $dir, $employee_setting_array, $title){
        $galaxyWechatActivityUserModel = new GalaxyWechatActivityUser();
        if($data){
            foreach($data as $k1 => $v1){
                foreach($employee_setting_array as $k2 => $v2){
                    $qrcode_link = '/activity/'.$activity_id.'/'.$v2['id'];
                    $qrcode_address = ltrim($dir, '.').$v2['username'].'-'.$title.'.png';
                    $data = array(
                        'galaxy_wechat_activity_id' => $activity_id,
                        'galaxy_admin_id' => (int)$v2['id'],
                        'qrcode_link' => $qrcode_link,
                        'qrcode_address'=> $qrcode_address,
                        'type' => (int)$v1,
                    );
                    $clone = clone $galaxyWechatActivityUserModel; //克隆一个新对象，使用新对象来调用create()函数
                    $result = $clone->create($data);
                    if (!$result) {
                        $errorMessage = implode(',', $clone->getMessages());
                        return ['msg' => '活动生成顾问二维码失败', 'code' => 0, 'data' => $errorMessage];
                    }
                    //生成二维码图片
                    $this->createQrcode($qrcode_link, $qrcode_address);
                }
            }
        }
    }

    /**
     * description 对产生的多个二维码处理
     * @param mixed $data
     * @param string $activity_id
     * @param string $dir
     * @return data
     */
    public function addMoreQrcode($data, $activity_id, $dir, $employee_setting_array, $title){
        $galaxyWechatActivityUserModel = new GalaxyWechatActivityUser();
        if($data){
            foreach($data as $k1 => $v1){
                foreach($employee_setting_array as $k2 => $v2){
                    $qrcode_link = '/activeEnroll/'.$activity_id.'/'.$v2['id'];
//                    $qrcode_address = ltrim($dir, '.').$v2['username'].'-'.$title.'.png';
                    $qrcode_address = ltrim($dir, '.').$v2['company'].'-'.$v2['username'].'.png';
                    $data = array(
                        'galaxy_wechat_activity_id' => $activity_id,
                        'galaxy_admin_id' => (int)$v2['id'],
                        'qrcode_link' => $qrcode_link,
                        'qrcode_address'=> $qrcode_address,
                        'type' => (int)$v1,
                    );
                    $clone = clone $galaxyWechatActivityUserModel; //克隆一个新对象，使用新对象来调用create()函数
                    $result = $clone->create($data);
                    if (!$result) {
                        $errorMessage = implode(',', $clone->getMessages());
                        return ['msg' => '活动生成顾问二维码失败', 'code' => 0, 'data' => $errorMessage];
                    }
                    //生成二维码图片
                    $this->createQrcode($qrcode_link, $qrcode_address);
                }
            }
        }
    }

    public function listData(){
        $galaxyWechatActivity = self::find();
        $array = $galaxyWechatActivity->toArray();
        if($array){
            foreach($array as $key => $value){
                $array[$key]['id'] = (int)$value['id'];
                $array[$key]['is_deleted'] = (int)$value['is_deleted'];
                $array[$key]['sort'] = (int)$value['sort'];
                $array[$key]['status'] = (int)$value['status'];
            }
        }
        $result=[
            'msg' => '活动列表',
            'code' => 200,
            'data' => $array,
        ];

        return $result;
    }

    public function searchData($params){
        $conditions = "is_deleted = '0'";
        $bind = [];
        $time = time();
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['page_size']) ? (int)$params['page_size'] : 10;
        $offset = ($page - 1) * $limit;

        if(!empty($params['title']) && isset($params['title'])){
            $conditions .= " AND title like '".trim($params['title'])."%'";
            $bind['title'] = $params['title'];
        }

        //总数
        $count = self::count(array(
            'columns' => 'id',
            'conditions' => $conditions,
            'bind' => $bind,
        ));

        $ret = self::find(array(
            'columns' => 'id,title,start_time,end_time,status,has_open_status',
            'conditions' => $conditions,
            'bind' => $bind,
            'order' => 'id DESC',
            'limit' => $limit,
            'offset' => $offset,
        ));

        $array = [];
        if($ret){
            $array  = $ret->toArray();
            foreach ($array as $key => $value){
                $array[$key]['id'] = (int)$value['id'];
                $array[$key]['status'] = (int)$value['status'];
                $array[$key]['has_open_status'] = (int)$value['has_open_status'];
                #判断是否过期(0:未过期；1:已过期)
                $end_time = strtotime($value['end_time']);//结束时间戳
                $array[$key]['out_date_status'] = ($time <= (int)$end_time ? 0 : 1);
                $array[$key]['sign_in_qrcode'] =  di('config')->wechat_back_url.$this->getActivity($value['id'],2);
                $array[$key]['sign_back_qrcode'] = di('config')->wechat_back_url.$this->getActivity($value['id'],3);
            }
        }

        $result=[
            'msg' => '活动列表',
            'code' => 200,
            'count' => $count,
            'data' => $array,
        ];

        return $result;
    }

    public function getActivity($activity_id, $type){
        $ret = GalaxyWechatActivityUser::findFirst(array(
            'columns' => 'id,qrcode_address',
            'conditions' => "galaxy_wechat_activity_id = ".$activity_id." AND type = '".$type."'",
            'bind' => [],
        ));

        if($ret){
            $array  = $ret->toArray();
            return $array['qrcode_address'];
        }else{
            return '';
        }
    }

    public function getData($id){
        $galaxyWechatActivity = self::findFirstByid($id);
        if($galaxyWechatActivity){
            $galaxyWechatActivity->id = (int)$galaxyWechatActivity->id;
            $galaxyWechatActivity->is_deleted = (int)$galaxyWechatActivity->is_deleted;
            $galaxyWechatActivity->sort = (int)$galaxyWechatActivity->sort;
            $galaxyWechatActivity->status = (int)$galaxyWechatActivity->status;
            $galaxyWechatActivity->has_open_status = (int)$galaxyWechatActivity->has_open_status;
            $galaxyWechatActivity->thumb = di('config')->wechat_back_url.$galaxyWechatActivity->thumb;
            $galaxyWechatActivity->share_thumb = di('config')->wechat_back_url.$galaxyWechatActivity->share_thumb;
            $employee_setting = $galaxyWechatActivity->employee_setting;
            $data = $galaxyWechatActivity->toArray();

            if($employee_setting){
                $conditions = "id IN ({idlist:array}) AND roleid = 4 AND dimission = 'false'";
                $bind = ['idlist'=>explode(',',$employee_setting)];
                $adminData = GalaxyAdmin::find(array(
                    'columns' => 'id,username,nickname',
                    'conditions' => $conditions,
                    'bind' => $bind,
                ))->toArray();
                if($adminData){
                    foreach($adminData as $k=>$v){
                        $adminData[$k]['id'] = (int)$v['id'];
                    }
                }

                $data['employee_setting_data'] = $adminData;
                #thumb
                $thumbArray = explode("/", $data['thumb']);
                $thumbName = '';
                if($thumbArray && isset($thumbArray[6])){
                    $thumbName = $thumbArray[6];
                }
                $data['thumb'] = array(
                    'url'=>$data['thumb'],
                    'name' => $thumbName,
                );
                #share_thumb
                $shareThumbArray = explode("/", $data['share_thumb']);
                $shareThumbName = '';
                if($shareThumbArray && isset($shareThumbArray[6])){
                    $shareThumbName = $shareThumbArray[6];
                }
                $data['share_thumb'] = array(
                    'url'=>$data['share_thumb'],
                    'name' => $shareThumbName,
                );
            }

            $result=[
                'msg' => '活动详情',
                'code' => 200,
                'data' => $data,
            ];
        }else{
            $result=[
                'msg' => '活动id不存在',
                'code' => 0,
                'data' => false
            ];
        }

        return $result;
    }

    public function checkBase64Img($str){
        header("Content-type:text/html;charset=utf-8;");
        return $str == base64_encode(base64_decode($str)) ? true : false;
    }

    public function saveData($params){
        $result = ['msg' => '', 'code' => 0, 'data' => false];
        if($this->save($params)){
            return ['msg' => '活动编辑成功', 'code' => 200, 'data' => true];
        }
        return $result;
    }

    public function deleteData($id){
        $galaxyWechatActivity = self::findFirstByid($id);
        if($galaxyWechatActivity){
            $galaxyWechatActivity->is_deleted = 1;
            if($galaxyWechatActivity->update()){
                #删除与活动关联的二维码
                $phql = "update galaxy_wechat_activity_user set is_deleted='1' WHERE galaxy_wechat_activity_id = ".$id;
                $result = $this->di->get('db')->query($phql);
                if ($result) {
                    $result=[
                        'msg' => '活动删除成功',
                        'code' => 200,
                        'data' => true
                    ];
                }
            }else{
                $result=[
                    'msg' => '活动删除失败',
                    'code' => 0,
                    'data' => implode('<br>',$galaxyWechatActivity->getMessages()),
                ];
            }
        }else{
            $result=[
                'msg' => '活动id不存在',
                'code' => 0,
                'data' => false
            ];
        }

        return $result;
    }

    public function settingData($params){
        $conditions = "";
        $bind = [];
        $page = $params['page'] ? (int)$params['page'] : 1;
        $limit = $params['page_size'] ? (int)$params['page_size'] : 10;
        $offset = ($page - 1) * $limit;
        $conditions .= "roleid = 4 AND dimission = 'false' AND is_deleted = 0";

        if(!empty($params['username'])){
            $conditions .= " AND username like '".trim($params['username'])."%'";
        }

        //总数
        $count = GalaxyAdmin::count(array(
            'columns' => 'id',
            'conditions' => $conditions,
            'bind' => $bind,
        ));

        $ret = GalaxyAdmin::find(array(
            'columns' => 'id,username,company',
            'conditions' => $conditions,
            'bind' => $bind,
            'limit' => $limit,
            'offset' => $offset,
        ));

        $array  = $ret->toArray();
        #获取分公司项目信息
        $crm_region_info = di('redis')->setIndex(1)->get('initialize:region:key');
        $crm_region_info = json_decode($crm_region_info,true);

        if($array){
            foreach ($array as $key => $value){
                $array[$key]['id'] = (int)$value['id'];
                $array[$key]['company'] = $crm_region_info[$value['company']];
                #$array[$key]['company'] = $value['company'] == 'SZ' ? '深圳分公司':($value['company'] == 'BJ' ? '北京分公司':'not set');
            }
        }

        $result=[
            'msg' => '活动员工选择',
            'code' => 200,
            'count' => $count,
            'data' => $array
        ];
        return $result;
    }

    public function getUserData($ids){
        $employee_setting_array = explode(',',trim($ids));#客户ids
        $conditions = "id IN ({idlist:array})";
        $bind = ['idlist'=> $employee_setting_array];
        $data = GalaxyAdmin::find(array(
            'columns' => 'id,username,company',
            'conditions' => $conditions,
            'bind' => $bind,
        ))->toArray();

        return $data;
    }
}
