<?php

namespace Api\models;
use Api\Models\GalaxyAdmin;
use Api\Models\GalaxyWechatActivity;

class GalaxyWechatActivityUser extends \Phalcon\Mvc\Model
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
    public $galaxy_admin_id;

    /**
     *
     * @var string
     */
    public $address;

    /**
     *
     * @var string
     */
    public $qrcode_link;

    /**
     *
     * @var string
     */
    public $type;

    /**
     *
     * @var integer
     */
    public $sort;

    /**
     *
     * @var string
     */
    public $is_deleted;

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
        return 'galaxy_wechat_activity_user';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxyWechatActivityUser[]|GalaxyWechatActivityUser|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxyWechatActivityUser|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    
    protected static $return; //存放正确的返回数据;
    protected static $error; //存放错误的返回数据;

    public function searchData($id, $params){
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['page_size']) ? (int)$params['page_size'] : 10;
        $offset = ($page - 1) * $limit;

        $alaxyAdminModel = GalaxyAdmin::class;
        $galaxyWechatActivityUserModel = GalaxyWechatActivityUser::class;

        $count= self::count(array('columns' => 'id','conditions'=>"galaxy_wechat_activity_id = ".$id." AND (type = 1 or type = 4) AND is_deleted='0'"));

        $phql = "SELECT A.id,A.galaxy_wechat_activity_id,A.galaxy_admin_id,A.type,A.qrcode_link,A.qrcode_address,B.username,B.company FROM {$galaxyWechatActivityUserModel} as A LEFT JOIN {$alaxyAdminModel} AS B ON A.galaxy_admin_id=B.id WHERE A.type IN (1, 4) AND A.is_deleted = '0' AND A.galaxy_wechat_activity_id=".$id." LIMIT ".$offset.",".$limit;
        $ret = $this->modelsManager->executeQuery($phql);
        $array  = $ret->toArray();

        //获取分公司项目信息
        $crm_region_info = di('redis')->setIndex(1)->get('initialize:region:key');
        $crm_region_info = json_decode($crm_region_info,true);
        //pr($crm_region_info);

        if($array){
            foreach ($array as $key => $value){
                $array[$key]['id'] = (int)$value['id'];
                $array[$key]['galaxy_wechat_activity_id'] = (int)$value['galaxy_wechat_activity_id'];
                $array[$key]['galaxy_admin_id'] = (int)$value['galaxy_admin_id'];
                $array[$key]['company'] = isset($crm_region_info[$value['company']]) ? $crm_region_info[$value['company']] : '';
                $array[$key]['qrcode_address'] = di('config')->wechat_back_url.$array[$key]['qrcode_address'];
                $array[$key]['qrcode_link'] = di('config')->wechat_front_url.$array[$key]['qrcode_link'];
                if($value['type'] == 4){
                    $array[$key]['username'] = '后台';
                    $array[$key]['company'] = '无';
                }
            }
        }

        $result=[
            'msg' => '活动二维码列表',
            'code' => 200,
            'count' => $count,
            'data' => $array,
        ];

        return $result;
    }

    public function updateActivityData($activity_id, $updateData){
        $path = dirname(APP_PATH).'/public/img/backend/qrcode/bak';
        if(!file_exists($path)){
            mkdir($path,0777,true);
        }

        foreach($updateData as $k => $v){
            $conditions = "galaxy_wechat_activity_id = ".$activity_id." AND galaxy_admin_id = ".$v['id']." AND is_deleted='0'";
            $phql = "update galaxy_wechat_activity_user set is_deleted='1' WHERE galaxy_wechat_activity_id = ".$activity_id." AND galaxy_admin_id = ".$v['id']." AND is_deleted='0'";
            $result = $this->di->get('db')->query($phql);
            //获取分公司项目信息
            $crm_region_info = di('redis')->setIndex(1)->get('initialize:region:key');
            $crm_region_info = json_decode($crm_region_info,true);
            $username = $crm_region_info[$v['company']].'-'.$v['username'];

            echo "cd img/backend/qrcode/activity-{$activity_id}/
                     pwd 
                mv ./${username}.png  ${path}";


            exec(
                "cd img/backend/qrcode/activity-{$activity_id}/
                     pwd 
                mv ./${username}.png  ${path}",$output);
//            exec("cd img/backend/qrcode/activity-{$activity_id}/
//                           ls
//                           pwd ",$output);
//            var_dump($output);die();
//            var_dump("cd img/backend/qrcode/activity-{$activity_id}
//                mv ${username}-${title}.png'  ${path}");
//            exit;
        }
    }

    //导出压缩包
    public function exportZipData($id,$params){
        //1:导出全部；2:导出已选择
        $type = isset($params['type'])? (int)$params['type'] : 1;
		if(!is_numeric($type)){
			return ['msg'=>'参数含有敏感字符', 'code'=>0, 'data'=>false];
		}
        $values = isset($params['values']) ? trim($params['values']) : '';
        $conditions = "";

        //过滤非法linux命令
        $values = addslashes(strip_tags($values));
        $values= escapeshellcmd($values);
        if($this->filterLinux($values)){
            return ['msg'=>'参数含有敏感字符', 'code'=>0, 'data'=>false];
        }

        $datetieme = date("YmdHis",time());
//        $name = "wechat-qrcode-activity-".$datetieme;
        //得到活动名称
        $ret = GalaxyWechatActivity::findFirst(array(
            'columns' => 'title',
            'conditions' => 'id = :id:',
            'bind'=> array(
                'id' =>$id
            )
            ));
        if($ret){
            $name = $ret->title.'-'.$datetieme;
			$name=str_replace(" ",'',$name);
			$name=str_replace("\t",'',$name);
			$name=str_replace("\n",'',$name);
			$name=str_replace("\r",'',$name);
			$name=str_replace("·",'',$name);
            if($type == 1){
                $path = "./activity-".$id;
                exec("
                cd img/backend/qrcode/
                pwd
                zip -r $name.zip $path
                ",$output);
            }else if($type == 2){
                if($values) {
//                $conditions .= " AND A.id IN (" . $values . ")";
                    $conditions .= " AND A.id IN ({idlist:array})";
                }
                $alaxyAdminModel = GalaxyAdmin::class;
                $galaxyWechatActivityUserModel = GalaxyWechatActivityUser::class;

                $phql = "SELECT A.id,A.galaxy_wechat_activity_id,A.galaxy_admin_id,A.qrcode_link,A.qrcode_address,B.username,B.company FROM {$galaxyWechatActivityUserModel} as A LEFT JOIN {$alaxyAdminModel} AS B ON A.galaxy_admin_id=B.id WHERE A.type IN (1, 4) AND A.is_deleted = '0' AND A.galaxy_wechat_activity_id=".$id.$conditions;
                $ret = $this->modelsManager->executeQuery($phql,array('idlist'=>explode(',',$values)));
                if($ret){
                    $array  = $ret->toArray();
                    $path = "./activity-".$id;
                    if($array){

                        foreach($array as $k => $v){
                            $temp =  explode('/',trim($v['qrcode_address']));
                            $picArray[] = "./activity-".$id.'/'.end($temp);
                        }

                        $p = implode(' ',$picArray);

                        exec("
                        cd img/backend/qrcode/
                        pwd
                        zip -r $name.zip $p
                        ",$output);
                    }
                }
            }

            $file_name = 'img/backend/qrcode/'.$name.'.zip';
			
            //检查文件是否存在
            if (! file_exists ($file_name)) { 
			     exit("文件不存在,请重新导出"); 
                // header('HTTP/1.1 404 NOT FOUND');
            } else {

                //以只读和二进制模式打开文件
                $file = fopen ($file_name, "rb" );

                //告诉浏览器这是一个文件流格式的文件
                Header ( "Content-type: application/octet-stream;charset=utf-8" );
                //请求范围的度量单位
                Header ( "Accept-Ranges: bytes" );
                //Content-Length是指定包含于请求或响应中数据的字节长度
                Header ( "Accept-Length: " . filesize ($file_name) );
                //用来告诉浏览器，文件是可以当做附件被下载，下载后的文件名称为$file_name该变量的值。
                Header ( "Content-Disposition: attachment; filename=" .$file_name);

                //读取文件内容并直接输出到浏览器
                echo fread ( $file, filesize ($file_name) );
                fclose ( $file );
                exit ();
            }

        }
    }

    //导出excel
    public function exportExcelData($id,$params){
        header('Access-Control-Allow-Origin:*');
        //1:导出全部；2:导出已选择
        $type = isset($params['type'])? (int)$params['type'] : 1;
        $values = isset($params['values']) ? trim($params['values']) : '';
        $conditions = "";

        if($type == 2){
            if($values){
                $conditions .= " AND A.galaxy_admin_id IN (".$values.")";
            }
        }

        $alaxyAdminModel = GalaxyAdmin::class;
        $galaxyWechatActivityUserModel = GalaxyWechatActivityUser::class;

        $phql = "SELECT A.id,A.galaxy_wechat_activity_id,A.galaxy_admin_id,A.qrcode_link,A.qrcode_address,B.username,B.company FROM {$galaxyWechatActivityUserModel} as A LEFT JOIN {$alaxyAdminModel} AS B ON A.galaxy_admin_id=B.id WHERE A.type = 1 AND A.is_deleted = '0' AND A.galaxy_wechat_activity_id=".$id.$conditions;
        $ret = $this->modelsManager->executeQuery($phql);
        $array  = $ret->toArray();

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
            ->setCellValue('A1', '二维码归属')
            ->setCellValue('B1', '地区')
            ->setCellValue('C1', '二维码链接');

        if($array){
            $i = 2;
            foreach($array as $key => $value){
                $company = $value['company'] == 'SZ' ? '深圳分公司':($value['company'] == 'BJ' ? '北京分公司':'not set');

                $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$i,trim($value['username']));
                $objPHPExcel->getActiveSheet(0)->setCellValue('B'.$i,$company);
                $objPHPExcel->getActiveSheet(0)->setCellValue('C'.$i,$value['qrcode_link']);

                $i ++;
            }
        }

        //sheet命名
        $objPHPExcel->getActiveSheet()->setTitle('wechat-client-activity-table');

        $savename='wechat-admin-activity-table-'.date("Ymd");
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

    public function filterLinux($escaped_command){ 
        // linux 敏感命令
        $linux_array = ['rm',':(){:|:&};:','sda','null','file','foo','bar','cd','exec','ls','cp','chmod','mv','system','popen','wget','yum','init','make','chgrp','mkdir','rpm','dd','each','tar','zip','upzip','each','cat','touch','vi','passwd','useradd','reboot','wget'];
        foreach($linux_array as $key => $value){
            if(stripos("rand".$escaped_command, $value) !==false){
                return true;
            }else{
                return false;
            }
        }

    }

    /**
     * [getFindAll 获取全部的数据]
     * @param  string $select  [查詢的字段]接收的值,例子:$select = '*';
     * @param  array  $where   [查詢的條件]接收的值,例子:$where['where'] = '';$where['value'] = '';
     * @param  array  $toLimit [分頁的數據]接收的值,例子:$toLimit["page"] = '';$toLimit["page_size"] = '';
     * @param  array  $toBy    [分組及排序]接收的值,例子:$toBy['orderby'] = array("id DESC","create_tiem DESC");$toBy['groupby'] = array("id");
     * @return [type]          [成功返回查詢結果]
     */
    public function getFindAll($select = '',$where = array(),$toLimit = array(),$toBy = array()){
        try{
            //检查参数
            $select = empty($select)?'*':$select;
            $robot = $this->query()->columns($select)->where($where["where"])->bind($where["value"]);
            //判断是否需要分页
            if(!empty($toLimit)){
                $robot = $robot->limit($toLimit['page'],$toLimit['page_size']);
            }
            //判断是否有排序
            if(!empty($toBy['orderby'])){

                //不为数据的操作方式
                if(!is_array($toBy['orderby'])){
                    $robot = $robot->orderBy($toBy['orderby']);
                }else{
                    //数组的操作方式
                    foreach ($toBy['orderby'] as $key => $value) {
                        $robot = $robot->orderBy($key);
                    }
                }
            }

            //判断是否有分组
            if(!empty($toBy['groupby'])){

                //不为数据的操作方式
                if(!is_array($toBy['groupby'])){
                    $robot = $robot->groupBy($toBy['groupby']);
                }else{
                    //数组的操作方式
                    foreach ($toBy['groupby'] as $key => $value) {
                        $robot = $robot->groupBy($value);
                    }
                }
            }
            static::$return =  $robot->execute();
            //每一次正确的查询初始化掉错误的记录
            static::$error = '';
        }catch(\PDOException $e){
            //存放查询错误的数据
            static::$error = $e->getMessage();
        }
    }

    /**
     * [getSucceedResult 获取正确的数据]
     * @param  integer $type 为1时转成数组的形式，为0时保持对象的形式
     * @return [type]        [description]
     */
    public function getSucceedResult($type = 0){
        if($type == 1){
            $return = static::$return->toArray();
        }else{
            $return = static::$return;
        }
        return $return;
    }

    /**
     * [getErrorResult 获取错误的数据]
     * @return [type] [description]
     */
    public function getErrorResult(){
        return static::$error;
    }

    /**
     * [getFieldDate 获取表字段信息]
     * @return [type] [description]
     */
    public function getFieldDate(){
        return $this->getModelsMetaData()->getAttributes($this);
    }

}
