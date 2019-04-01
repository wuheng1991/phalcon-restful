<?php

namespace Api\Modules\Backend\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;

class GalaxyAdmin extends \Phalcon\Mvc\Model
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
    public $username;

    /**
     *
     * @var string
     */
    public $password;

    /**
     *
     * @var string
     */
    public $nickname;

    /**
     *
     * @var string
     */
    public $ico;

    /**
     *
     * @var string
     */
    public $company;

    /**
     *
     * @var integer
     */
    public $department;

    /**
     *
     * @var integer
     */
    public $roleid;

    /**
     *
     * @var string
     */
    public $loginip;

    /**
     *
     * @var integer
     */
    public $logintime;

    /**
     *
     * @var string
     */
    public $furlough;

    /**
     *
     * @var string
     */
    public $dimission;

    /**
     *
     * @var string
     */
    public $checkinfo;

    /**
     *
     * @var string
     */
    public $create_time;

    /**
     *
     * @var string
     */
    public $email;

    /**
     *
     * @var string
     */
    public $role;

    /**
     *
     * @var integer
     */
    public $is_deleted;

    /**
     *
     * @var integer
     */
    public $fid;

    /**
     *
     * @var string
     */
    public $work_wechat_id;

    /**
     *
     * @var integer
     */
    public $leave;

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'email',
            new EmailValidator(
                [
                    'model'   => $this,
                    'message' => 'Please enter a correct email address',
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
//    public function initialize()
//    {
//        $this->setSchema("crm");
//        $this->setSource("galaxy_admin");
//    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'galaxy_admin';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxyAdmin[]|GalaxyAdmin|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return GalaxyAdmin|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
