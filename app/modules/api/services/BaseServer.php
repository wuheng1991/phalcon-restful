<?php
namespace Api\Services;
use Phalcon\Mvc\Controller;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/29
 * Time: 14:38
 */
class BaseServer extends Controller{

	//设置返回参数
	public $msg = '系统有误';
	public $code = 0;
	public $data = array();

	//返回值
	public function returnData(){
	  	return $response = [
            "code" => $this->code,
            "data" => $this->data,
            "msg" => $this->msg
        ];
	}	
}