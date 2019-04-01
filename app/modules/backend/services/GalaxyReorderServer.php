<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 11:06
 */
namespace Backend\Services;
use Api\Models\GalaxyReorder;
use Api\Models\GalaxyWechatClient;
use Api\Models\GalaxyCrmClient;

class GalaxyReorderServer extends BaseServer
{
    /**
     * 签约订单列表
     * @return mixed
     */
    public function searchDataService($id, $params){
        $galaxyReorderModel = new GalaxyReorder();
        $galaxyWechatClientModel = new GalaxyWechatClient();
        $galaxyCrmClientModel = new GalaxyCrmClient();

        $params['page'] = isset($params['page']) ? (int)$params['page'] : 1;
        $params['page_size'] = isset($params['page_size']) ? (int)$params['page_size'] : 10;
        $params['offset'] = ($params['page'] - 1) * $params['page_size'];

        $where['where'] = 'id = :id: AND is_deleted = :is_deleted:';
        $where['value']['id'] = $id;
        $where['value']['is_deleted'] = 0;

        $ret = $galaxyWechatClientModel->findone($where,$field="id,phone");
        if(!$ret){
            return ['msg'=>'该客户不存在或已删除', 'code'=>0, 'count'=> 0, 'data'=>[]];
        }

        #获取crm客户信息
        $crm_client_where['where'] = 'mobile = :mobile:';
        $crm_client_where['value']['mobile'] = $ret->phone;

        $galaxyCrmClientModel->getFindOne('id, mobile', $crm_client_where);
        $crm_client_ret = $galaxyCrmClientModel->getSucceedResult(1);

        if(!$crm_client_ret){
            return ['msg'=>'该客户在crm中未找到', 'code'=>0, 'count'=> 0, 'data'=>[]];
        }

        #获取crm客户ID
        $crm_client_id = $crm_client_ret[0]['id'];
        #pr($crm_client_id);

        #获取crm项目信息
        $crm_project_info = di('redis')->setIndex(1)->get('initialize:contract:key');
        $crm_project_info = json_decode($crm_project_info,true);
        #获取crm货币信息
        $crm_currency_info = di('redis')->setIndex(1)->get('initialize:currency:currency');
        $crm_currency_info= json_decode($crm_currency_info,true);

        #查询客户签约订单信息列表
        $reorderRet = $galaxyReorderModel->getOrderReveivedRecordData($params, $crm_client_id)->toArray();
        if($reorderRet){
            foreach($reorderRet as $k => $v){
                $reorderRet[$k]['fee_type_name'] = '第三方费用';
                $reorderRet[$k]['project_value'] = $crm_project_info[$v['projectid']];
                $reorderRet[$k]['currency_value'] = $crm_currency_info[$v['currency']];
                #通过收款方式，得到相应的收款值
                $reorderRet[$k]['payment_method_value'] = $this->getPaymentMethodValue($v['payment_method']);
                #通过审核状态，获取付款状态值
                $reorderRet[$k]['check_status_value'] = $this->getCheckStatusValue($v['check_status']);
            }
        }

        #查询客户签约订单总数
        $reorderNumRet = $galaxyReorderModel->getOrderReveivedRecordNumData($crm_client_id)->toArray();
        $reorderNum = (int)$reorderNumRet[0]['num'];
        
        return ['msg'=>'签约订单列表', 'code'=>200, 'count'=> $reorderNum, 'data'=>$reorderRet];
    }


    /**
     * 通过审核状态，获取付款状态值
     * @return mixed
     */
    public function getCheckStatusValue($check_status){
        switch ($check_status){
            case 0:
                return '待付款';
                break;
            case 1:
                return '已支付';
                break;
            case 2:
                return '已完成';
                break;
            case 3:
                return '支付失败';
                break;
            default:
                return '';
                break;
        }
    }

    /**
     * 通过收款方式，获取收款对应值
     * @return mixed
     */
    public function getPaymentMethodValue($payment_method){
        switch ($payment_method){
            case 0:
                return '待定';
                break;
            case 1:
                return '银行转账';
                break;
            case 2:
                return '网银转账';
                break;
            case 3:
                return 'POS机刷卡';
                break;
            case 4:
                return '支付宝';
                break;
            case 5:
                return '微信支付';
                break;
            case 6:
                return '现金';
                break;
            case 7:
                return '支票';
                break;
            case 8:
                return '电汇';
                break;
            case 9:
                return '邮件汇款';
                break;
            case 10:
                return '其他';
                break;
            default:
                return '';
                break;
        }
    }
}