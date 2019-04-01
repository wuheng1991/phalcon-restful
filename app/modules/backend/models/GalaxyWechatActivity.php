<?php

namespace Api\Modules\Backend\Models;

use Api\Modules\Backend\Models\GalaxyWechatActivityUser;

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
     * Initialize method for model.
     */
//    public function initialize()
//    {
//        $this->setSchema("crm");
//        $this->setSource("galaxy_wechat_activity");
//
//        $this->hasMany(
//            "id",
//            "GalaxyWechatActivityUser",
//            "galaxy_wechat_activity_id"
//        );
//
//    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'galaxy_wechat_activity';
    }

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




}
