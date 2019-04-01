<?php

namespace Api\Modules\Backend\Models;

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
    public $qrcode_address;

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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("crm");
        $this->setSource("galaxy_wechat_activity_user");

        $this->belongsTo(
            'galaxy_wechat_activity_id',
            'GalaxyWechatActivity',
            'id'
        );

    }

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

}
