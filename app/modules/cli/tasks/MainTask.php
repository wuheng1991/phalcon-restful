<?php
namespace Api\Modules\Cli\Tasks;
use Api\Models\GalaxyWechatIntegralGoods;
use Api\Models\GalaxyWechatIntegralOrder;
use Api\Models\GalaxyWechatInventoryLog;
use Api\Models\GalaxyWechatIntegralLog;
use Api\Models\GalaxyWechatIntegralStats;
use Api\Models\GalaxyWechatActivity;
use Api\Models\GalaxyWechatActivityClient;
use Api\Models\GalaxySmsStatusLog;
use Api\Models\GalaxySmsGroupLog;
use Api\Models\GalaxyWechatClient;
use Api\Models\GalaxyWechatConfig;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\manager;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Logger\Formatter\Line as LineFormatter;

class MainTask extends \Phalcon\Cli\Task
{

    public function initialize(){
        date_default_timezone_set('Asia/Shanghai');
        $task_log = APP_PATH.'/logs/task.log';
        if(!file_exists($task_log)){
            fopen($task_log, "a+");
            //mkdir($task_log,0777, true);
        }
        $this->logger = new FileLogger($task_log);
        //$formatter = new LineFormatter("[%date% - %message% - %type%]");
        //$this->logger->setFormatter($formatter);

        //判断是不是在cli模式下执行的命令
        if(php_sapi_name() != 'cli'){
            $this->logger->log(\Phalcon\Logger::DEBUG, '异常请求，不予理睬');
            exit;
        }
    }

    # 对已开启的过期活动的处理（status = 0）
    public function syscActivityAction(){
        // $result = ['msg' => '', 'code' => 0, 'data' => false];
        // $conditions = "is_deleted = '0' AND status = '1'";
        // $bind = [];
        // $datetime = strtotime(date("Y-m-d H:i:s", time()));
        // $ret = GalaxyWechatActivity::find(
        //     array(
        //         'columns' => 'id, end_time, status',
        //         'conditions' => $conditions,
        //         'bind' => $bind,
        //     )
        // );

        // if(!$ret){
        //     $this->logger->log(\Phalcon\Logger::INFO, '活动列表为空');
        //     exit;
        // }

        // $temp = [];
        // $array = $ret->toArray();
        // if($array) {
        //     foreach ($array as $k => $v) {
        //         $endtime = strtotime($v['end_time']);
        //         if ($endtime < $datetime) {
        //             $temp[] = $v['id'];
        //         }
        //     }
        // }

        // if(!$temp) {
        //     $this->logger->log(\Phalcon\Logger::INFO, '活动开启，但没有过期的ID');
        //     exit;
        // }

        // $conditions = "id IN ({idlist:array})";
        // $bind = ['idlist' => $temp];
        // $data = GalaxyWechatActivity::find(array(
        //     'conditions' => $conditions,
        //     'bind' => $bind,
        // ));

        // if(!$data){
        //     $this->logger->log(\Phalcon\Logger::ERROR, '活动开启，但数据库没有找到相关过期ID的信息。id=['.implode(',',$temp).']');
        //     exit;
        // }

        // $updateData['status'] = 0;
        // // Start a transaction
        // $this->db->begin();
        // if($data->update($updateData)){
        //     $this->logger->log(\Phalcon\Logger::INFO, '开启并过期的活动修改成功!');
        //     // Commit the transaction
        //     $this->db->commit();
        //     exit;
        // }else{
        //     $this->logger->log(\Phalcon\Logger::ERROR, '开启并过期的活动修改失败!');
        //     // The model failed to save, so rollback the transaction
        //     $this->db->rollback();
        //     exit;
        // }
    }

    /**
     * 客户活动通知-短信消息
     *
     */
    public function syscGroupMessageAction($testType){

        //设置测试的号码
        $testPhone = "18825124242,18688795987,18292966039,13480177643";

        date_default_timezone_set('Asia/Shanghai');
        $galaxyWechatActivityModelModel = new GalaxyWechatActivity();
        $activityClientModel = new GalaxyWechatActivityClient();
        $galaxyWechatConfigModel = new GalaxyWechatConfig();
        $galaxySmsGroupLogModel = new GalaxySmsGroupLog();

        $group_send_log = APP_PATH.'/logs/group_send_message.log';
        if(!file_exists($group_send_log)){
            fopen($group_send_log, "a+");
            //mkdir($task_log,0777, true);
        }
        $this->logger = new FileLogger($group_send_log);

        $time = time();
        $mobile = '';

        //判断该公众号是否支持群发
        $config_where['where'] = 'flag = :flag:';
        $config_where['value']['flag'] = '1';
        $configRet = $galaxyWechatConfigModel->findone($config_where, $field="id, sms_group_status")->toArray();
        if(empty($configRet) || (!empty($configRet) && ($configRet['sms_group_status'] == '0'))){
            $this->logger->log(\Phalcon\Logger::INFO, '该公众号未开启短信群发功能');
            exit;
        }

        //获取有效的活动信息
        $where['where'] = "is_deleted = :is_deleted: AND status = :status: AND sign_in_time = DATE_FORMAT(CURDATE() + 1,'%Y-%m-%d 00:00:00')";
        $where['value']['is_deleted'] = '0';
        $where['value']['status'] = '1';

        $field = "id, title, start_time, end_time, address, sign_in_time";
        $order = "id DESC";
        $activityData = $galaxyWechatActivityModelModel->findall($where,$field,$order)->toArray();
        //判断活动是否存在
        if($activityData){
            //循环活动做处理
            foreach($activityData as $key => $value){
                $client_where['where'] = "galaxy_wechat_activity_id = :galaxy_wechat_activity_id: AND sms_status = '0'";
                $client_where['value']['galaxy_wechat_activity_id'] = $value['id'];

                $field = "id, phone";
                $order = "id DESC";
                $clientData = $activityClientModel->findall($client_where,$field,$order)->toArray();
                //联表获取参与客户的手机号信息
                //$phql = "SELECT A.id, B.phone FROM {$galaxyWechatActivityClientModel} AS A LEFT JOIN {$galaxyWechatClientModel} AS B ON A.galaxy_wechat_client_id = B.id  WHERE A.sms_status = '0' AND A.galaxy_wechat_activity_id = ".$v['id'];
                //$ret = $this->modelsManager->executeQuery($phql)->toArray();
                $tempId = []; //存放有效id
                $tempPhone = []; //存放有效手机号码

                //循环存放有效的手机号
                if($clientData){
                    foreach($clientData as $k1 => $v1){
                        // 有效手机号验证
                        if(!empty($v1['phone']) && preg_match("/^0?(13|14|15|16|17|18|19)[0-9]{9}$/", $v1['phone'])){
                            $tempPhone[] = trim($v1['phone']);
                            $tempId[] = $v1['id'];
                        }
                    }
                }

                //判断是否有有效的手机号
                if(empty($tempPhone)){
                    $this->logger->log(\Phalcon\Logger::INFO, '活动名称【'.$value['title'].'】短信群发失败.原因:没有未发送的有效手机号');
                    continue;
                }
                //如果不为测试模式则取正确的手机号码
                if(empty($testType)){
                    // 手机号码，多个用逗号隔开
                    $mobile = implode(',', $tempPhone);

                    // 短信群发模板ID
                    
                    // 发送模板内容
                    #$content = $value['title']."##".$value['start_time']."##".$value['address'];
                }else{
                    $mobile = $testPhone;
                }

                $group_template_id = '';
                $signInTime = date('Y-m-d',strtotime($value['sign_in_time']));
                $content = "尊敬的客户您好，您报名参加的“".$value['title']."”将于".$signInTime."举行，地址：".$value['address']."，期待您的光临！";
                $sms_res = $this->SmsHelper->groupSentMessage($mobile, $group_template_id, $content);
                if($sms_res['code'] == 0){
                    //成功, 标记该活动相关客户已发送完短信通知
                    $update_where['where'] = 'id IN ('.implode(',', $tempId).')';
                    $update_data['sms_status'] = '1';
                    $updateRet = $activityClientModel->updates($update_where, $update_data);

                    //记录群发的结果
                    $group_aprams = [
                        'code' => (int)$sms_res['code'],
                        'msg' => $sms_res['msg'],
                        'batch_id' => $sms_res['batchId'],
                        'remarks' => $this->getSmsCodeData($sms_res['code']),
                        'galaxy_wechat_activity_id' => $value['id'],
                    ];
                    $smsGroupLogclone = clone $galaxySmsGroupLogModel; //克隆一个新对象，使用新对象来调用create()函数
                    $smsGroupRet = $smsGroupLogclone->createData($group_aprams);
                    if($smsGroupRet){
                        $this->logger->log(\Phalcon\Logger::INFO, '活动名称【'.$value['title'].'】群发日志添加成功');
                    }else{
                        $this->logger->log(\Phalcon\Logger::ERROR, '活动名称【'.$value['title'].'】群发日志添加失败');
                    }
                    $this->logger->log(\Phalcon\Logger::INFO, '活动名称【'.$value['title'].'】'.';请求状态码 '.$sms_res['code'].'; 状态说明:'.$sms_res['msg'].'; 批次id='.$sms_res['batchId'].'; 有效手机号为 ：'.$mobile);

                }else{
                    //失败
                    $this->logger->log(\Phalcon\Logger::ERROR, '活动名称【'.$value['title'].'】'.';请求状态码 '.$sms_res['code'].'; 状态说明:'.$sms_res['msg'].'; 批次id='.$sms_res['batchId']);
                }

                // 是暂停多少秒
            }
        }else{
            $this->logger->log(\Phalcon\Logger::INFO, '暂无有效的活动，需要发送短信消息');
        }
    }

    /**
     * 每分钟-获取短信状态
     */
    public function syscSmsLogAction(){
        $galaxySmsLogModel = new GalaxySmsStatusLog();

        $sms_status_log = APP_PATH.'/logs/sms_status_message.log';
        if(!file_exists($sms_status_log)){
            fopen($sms_status_log, "a+");
            //mkdir($task_log,0777, true);
        }
        $this->logger = new FileLogger($sms_status_log);

        $res = $this->SmsHelper->statusMessage();
        $data = $res['data'];
        if($data){
            $this->db->begin();
            foreach($data as $k => $v){
                $addData = [
                    'smuuid' => $v['smUuid'],
                    'deliver_time' => $v['deliverTime'],
                    'mobile' => $v['mobile'],
                    'deliver_result' => $v['deliverResult'],
                    'batch_id' => $v['batchId']
                ];
                $smslogclone = clone $galaxySmsLogModel; //克隆一个新对象，使用新对象来调用create()函数

                $res = $smslogclone->createData($addData);
                if($res){
                    $this->logger->log(\Phalcon\Logger::INFO, '获取短信状态成功！');
                }else{
                    $this->logger->log(\Phalcon\Logger::ERROR, '获取短信状态失败!');
                    $this->db->rollback();
                }
            }

            $this->db->commit();
        }else{
            $this->logger->log(\Phalcon\Logger::INFO, '暂无获取短信状态消息');
        }
    }

    /**
     * 每分钟-积分收支/分统计
     */
    public function syscStatsAction(){
        $stats_log = APP_PATH.'/logs/stats.log';
        if(!file_exists($stats_log)){
            fopen($stats_log, "a+");
            //mkdir($task_log,0777, true);
        }
        $this->logger = new FileLogger($stats_log);
        $time = time();
        #积分类型 integral_type,0为默认,1为推荐注册,2为推荐签约,3为积分兑换,4位签到赠送,100为系统调整
        //当天开始时间
        $start_time=strtotime(date("Y-m-d", $time));
        //当天结束之间
        $end_time = $start_time+60*60*24-1;
        $start_date = date("Y-m-d H:i:s", $start_time);
        $start_date = "'".$start_date."'";
        $end_date = date("Y-m-d H:i:s", $end_time);
        $end_date = "'".$end_date."'";

        #推荐注册总积分 register_count
        $register_count = 0;
        $conditions = "integral_type = 1 AND create_time >= $start_date AND create_time <= $end_date";
        $bind = [];
        $registerRet = GalaxyWechatIntegralLog::find(array(
            'columns' => 'id, integral',
            'conditions' => $conditions,
            'bind' => $bind,
        ))->toArray();
        if($registerRet){
            foreach($registerRet as $k1 => $v1){
                $register_count += (int)$v1['integral'];
            }
        }

        #推荐签约总积分 Signed_count
        $signed_count = 0;
        $conditions = "integral_type = 2 AND create_time >= $start_date AND create_time <= $end_date";
        $bind = [];
        $signedRet = GalaxyWechatIntegralLog::find(array(
            'columns' => 'id, integral',
            'conditions' => $conditions,
            'bind' => $bind,
        ))->toArray();
        if($signedRet){
            foreach($signedRet as $k2 => $v2){
                $signed_count += (int)$v2['integral'];
            }
        }

        #兑换总积分 conversion_count
        $conversion_count = 0;
        $conditions = "integral_type = 3 AND create_time >= $start_date AND create_time <= $end_date";
        $bind = [];
        $conversionRet = GalaxyWechatIntegralLog::find(array(
            'columns' => 'id, integral',
            'conditions' => $conditions,
            'bind' => $bind,
        ))->toArray();
        if($conversionRet){
            foreach($conversionRet as $k3 => $v3){
                $conversion_count += (int)$v3['integral'];
            }
        }

        #待生效积分数 await_count
        $await_count = 0;
        $conditions = "integral_state = 0 AND create_time >= $start_date AND create_time <= $end_date";
        $bind = [];
        $awaitRet = GalaxyWechatIntegralLog::find(array(
            'columns' => 'id, integral',
            'conditions' => $conditions,
            'bind' => $bind,
        ))->toArray();
        if($awaitRet){
            foreach($awaitRet as $k4 => $v4){
                $await_count += (int)$v4['integral'];
            }
        }
        #echo $register_count."_".$signed_count."_".$conversion_count."_".$await_count;
        $stats_date = date("Y-m-d", $time);
        $stats_date = "'".$stats_date."'";
        $stats_conditions = "stats_time = $stats_date";

        $data = GalaxyWechatIntegralStats::find(array(
            'conditions' => $stats_conditions,
            'bind' => [],
        ));

        $ret = $data->toArray();
        $this->db->begin();
        if($ret){
            # 跟新操作
            $updateData['register_count'] = $register_count;
            $updateData['Signed_count'] = $signed_count;
            $updateData['conversion_count'] = $conversion_count;
            $updateData['await_count'] = $await_count;
            if(!$data->update($updateData)){
                $this->logger->log(\Phalcon\Logger::ERROR, '积分收支统计更新失败!');
                // The model failed to save, so rollback the transaction
                $this->db->rollback();
            }else{
                $this->logger->log(\Phalcon\Logger::INFO, '积分收支统计更新成功!');
            }

        }else{
            # 插入操作
            $model = new GalaxyWechatIntegralStats();
            $insertData = array(
                'register_count' => $register_count,
                'Signed_count' => $signed_count,
                'conversion_count' => $conversion_count,
                'await_count' => $await_count,
                'stats_time' => date("Y-m-d", $time),
                'create_time' => date("Y-m-d H:i:s", $time)
            );

            if($model->save($insertData) > 0) {
                $this->logger->log(\Phalcon\Logger::INFO, '积分收支统计插入成功!');
            }else{
                $this->logger->log(\Phalcon\Logger::ERROR, '积分收支统计插入失败!');
                // The model failed to save, so rollback the transaction
                $this->db->rollback();
            }
        }

        $this->db->commit();
        exit;
    }

    /**
     * 三十分钟未兑换 取消订单
     */
    public function cancelAction(){
        date_default_timezone_set('Asia/Shanghai');
        $conditions = "order_type = 1";
        $bind = [];
        $order_res = GalaxyWechatIntegralOrder::find(array(
            'columns' => 'id,order_type,create_time,amount,galaxy_wechat_integral_goods_id',
            'conditions' => $conditions,
            'bind' => $bind,
        ))->toArray();
        if(!empty($order_res)){
            foreach($order_res as $k=>$v){
                $tmp_time = strtotime($v['create_time']);
                if(($tmp_time+1800) >time()){
                   continue;
                }
                $this->db->begin();
                $order =GalaxyWechatIntegralOrder::findFirstById($v["id"]);
                $order->order_type = 100;
                $order->cancel_time = date("Y-m-d H:i:s",time());
                if(!$order->update()){
                    $this->db->rollback();
                    continue;
                }
                $update_num = $this->redis->hIncrBy("goods:num",$v["galaxy_wechat_integral_goods_id"],$v['amount']);
                if(!$update_num){
                    $this->db->rollback();
                    continue;
                }
                $arr["front_inventory"] = $update_num-$v['amount'];
                $arr["behind_inventory"] = $update_num;
                $arr["inventory"] = $v['amount'];
                $arr["galaxy_wechat_integral_goods_id"] = $v['galaxy_wechat_integral_goods_id'];
                $inventory_log = new GalaxyWechatInventoryLog();
                $inventory_log->createData($arr);
                if($inventory_log->getErrorResult()){
                    $this->redis->hIncrBy("goods:num",$v["galaxy_wechat_integral_goods_id"],-$v['amount']);
                    $this->db->rollback();
                    continue;
                }
                $this->db->commit();
                $dir = dirname(dirname(dirname(dirname(__FILE__))))."/logs/".DEFAULT_MODULE.'/'.date("Ymd",time());
                $db_log = $dir."/cancelorder.log";
                $this->logger = new FileLogger($db_log);
                $this->logger->log($db_log,"订单id：".$v["id"]."(30分钟未兑换，已取消订单)");
                echo "订单id：".$v["id"]."(30分钟未兑换，已取消订单)".PHP_EOL;
            }
        }else{
            echo "无订单需要处理";
        }
    }

    /**
     * 定时同步redis库存到数据库
     */
    public function updateGoodsInventoryAction(){
        $sql = "SELECT
                    *
                FROM
                    (
                        SELECT
                            *
                        FROM
                            galaxy_wechat_inventory_log
                        ORDER BY
                            create_time DESC
                    ) AS a
                GROUP BY
                    galaxy_wechat_integral_goods_id
                ORDER BY
                    create_time DESC";
        $success = $this->di->get('db')->fetchAll($sql);
        if(!empty($success)){
            foreach($success as $k=>$v){
                $goods = GalaxyWechatIntegralGoods::findFirstById($v['galaxy_wechat_integral_goods_id']);
                if(!empty($goods) && $goods->inventory != $v["behind_inventory"]){
                    $goods->inventory = $v["behind_inventory"];
                    $inventory_log = "inventory_log";
                    if(!$goods->update()){
                        $this->di->get("logger")->log($inventory_log,"更改".$v['galaxy_wechat_integral_goods_id']."库存为".$v["behind_inventory"]."失败",array('error'=>true));
                        continue;
                    }
                    $this->di->get("logger")->log($inventory_log,"更改".$v['galaxy_wechat_integral_goods_id']."库存为".$v["behind_inventory"]."成功");
                }
            }
        }
    }

    /**
     * 获取某个时间戳的周几，以及未来几天以后的周几
     */
    public function getTimeWeek($time, $i = 0) {
        $weekarray = array("一", "二", "三", "四", "五", "六", "日");
        $oneD = 24 * 60 * 60;
        return "周" . $weekarray[date("w", $time + $oneD * $i)];
    }

    public function getSmsCodeData($code){
        switch ($code){
            case 0:
                return '请求成功';
                break;
            case 9001:
                return '签名格式不正确';
                break;
            case 9002:
                return '参数未赋值';
                break;
            case 9003:
                return '手机号码格式不正确';
                break;
            case 9006:
                return '用户accessKey不正确';
                break;
            case 9007:
                return 'IP白名单限制';
                break;
            case 9009:
                return '短信内容参数不正确';
                break;
            case 9010:
                return '用户短信余额不足';
                break;
            case 9011:
                return '用户帐户异常';
                break;
            case 9012:
                return '日期时间格式不正确';
                break;
            case 9013:
                return '不合法的语音验证码，4~8位的数字';
                break;
            case 9014:
                return '超出了最大手机号数量';
                break;
            case 9015:
                return '不支持的国家短信';
                break;
            case 9016:
                return '无效的签名或者签名ID';
                break;
            case 9017:
                return '无效的模板ID';
                break;
            case 9018:
                return '单个变量限制为1-20个字';
                break;
            case 9019:
                return '内容不可以为空';
                break;
            case 9021:
                return '主叫和被叫号码不能相同';
                break;
            case 9022:
                return '手机号码不能为空';
                break;
            case 9023:
                return '手机号码黑名单';
                break;
            case 9024:
                return '手机号码超频';
                break;
            case 10001:
                return '内容包含敏感词';
                break;
            case 10002:
                return '内容包含屏蔽词';
                break;
            case 10003:
                return '错误的定时时间';
                break;
            case 10004:
                return '自定义扩展只能是数字且长度不能超过4位';
                break;
            case 10005:
                return '模版类型不存在';
                break;
            case 10006:
                return '模版和内容不匹配';
                break;
        }
    }

}
