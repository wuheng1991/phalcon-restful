<?php

namespace Api\models;

class GalaxyRoleAuth extends \Phalcon\Mvc\Model
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
    public $roleid;

    /**
     *
     * @var string
     */
    public $authid;

    /**
     *
     * @var string
     */
    public $authname;

    /**
     *
     * @var string
     */
    public $platform;

    /**
     *
     * @var string
     */
    public $updatetime;

    /**
     *
     * @var string
     */
    public $createtime;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'galaxy_role_auth';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxyRoleAuth[]|GalaxyRoleAuth|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxyRoleAuth|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
