<?php
// +----------------------------------------------------------------------
// | InputValidator.php [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Validation\Registry;

use App\Core\Validation\Validator;
use Phalcon\Validation\Validator\PresenceOf;

class InputValidator extends Validator
{
    public function initialize()
    {
        $this->add(
            [
                'service',
                'port',
                'ip',
                'nonce',
                'sign',
                'register'
            ],
            new PresenceOf(
                [
                    'message' => 'The :field is required',
                ]
            )
        );
    }

}