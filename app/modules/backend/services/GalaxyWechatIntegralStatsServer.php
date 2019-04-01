<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/27
 * Time: 19:17
 */

namespace Backend\Services;

use Api\Models\GalaxyWechatIntegralStats;

class GalaxyWechatIntegralStatsServer extends BaseServer
{
    /**
     * 积分收支/分统计
     * 1-今日；2-昨日; 3-最近7天
     * @return mixed
     */
    public function statsCountService($params){
        $galaxyWechatIntegralStatsModel = new GalaxyWechatIntegralStats();
        $time = time();
//        $data = array(
//            'register_count' => 0, #推荐注册总积分
//            'signed_count' => 0, #荐签约总积分
//            'conversion_count' => 0,#兑换总积分
//            'await_count' => 0, #待生效积分数
//        );

        #------------------------------------------------------------

        #今日统计条件
        $today_date = date("Y-m-d", $time);
        $today_where['where'] = 'stats_time = :stats_time:';
        $today_where['value']['stats_time'] = $today_date;

        $galaxyWechatIntegralStatsModel->getFindOne('', $today_where);
        $todayRet = $galaxyWechatIntegralStatsModel->getSucceedResult(1);

        $data[0]['name'] = '今日';
        $data[0]['register_count'] = isset($todayRet[0]['register_count']) ? (int)$todayRet[0]['register_count'] : 0;
        $data[0]['signed_count'] = isset($todayRet[0]['Signed_count']) ? (int)$todayRet[0]['Signed_count'] : 0;
        $data[0]['conversion_count'] = isset($todayRet[0]['conversion_count']) ? (int)$todayRet[0]['conversion_count'] : 0;
        $data[0]['await_count'] = isset($todayRet[0]['await_count']) ? (int)$todayRet[0]['await_count'] : 0;


        #昨日统计条件
        $yesterday_start_time = strtotime(date("Y-m-d",strtotime("-1 day"))); //昨天开始时间戳
        $yesterday_date = date("Y-m-d", $yesterday_start_time);

        $yesterday_where['where'] = 'stats_time = :stats_time:';
        $yesterday_where['value']['stats_time'] = $yesterday_date;

        $galaxyWechatIntegralStatsModel->getFindOne('', $yesterday_where);
        $yesterdayRet = $galaxyWechatIntegralStatsModel->getSucceedResult(1);

        $data[1]['name'] = '昨日';
        $data[1]['register_count'] = isset($yesterdayRet[0]['register_count']) ? (int)$yesterdayRet[0]['register_count'] : 0;
        $data[1]['signed_count'] = isset($yesterdayRet[0]['Signed_count']) ? (int)$yesterdayRet[0]['Signed_count'] : 0;
        $data[1]['conversion_count'] = isset($yesterdayRet[0]['conversion_count']) ? (int)$yesterdayRet[0]['conversion_count'] : 0;
        $data[1]['await_count'] = isset($yesterdayRet[0]['await_count']) ? (int)$yesterdayRet[0]['await_count'] : 0;


        ##最近7天统计(含今天)条件
        $recent7_start_time = strtotime(date("Y-m-d",strtotime("-6 day"))); //昨天开始时间戳
        $recent7_start_date = date("Y-m-d", $recent7_start_time);
        $recent7_end_date = date("Y-m-d", $time);

        $recent7_where['where'] = 'stats_time >= :start_stats_time: AND stats_time <= :end_stats_time:';
        $recent7_where['value']['start_stats_time'] = $recent7_start_date;
        $recent7_where['value']['end_stats_time'] = $recent7_end_date;

        $data[2]['name'] = '最近7天';
        $data[2]['register_count'] = (int)$galaxyWechatIntegralStatsModel->getSum($recent7_where,'register_count');
        $data[2]['signed_count'] = (int)$galaxyWechatIntegralStatsModel->getSum($recent7_where,'Signed_count');
        $data[2]['conversion_count'] = (int)$galaxyWechatIntegralStatsModel->getSum($recent7_where,'conversion_count');
        $data[2]['await_count'] = (int)$galaxyWechatIntegralStatsModel->getSum($recent7_where,'await_count');

        $this->code = 200;
        $this->msg = '积分收支/分-统计';
        $this->data = $data;
        return $this->returnData();
    }
}