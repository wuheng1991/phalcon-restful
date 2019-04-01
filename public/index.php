<?php
use Phalcon\Mvc\Application;

require_once(dirname(dirname(__FILE__)).'/vendor/autoload.php');
define('IS_CLI', php_sapi_name()=='cli' ? true : false);
//require '../app/bootstrap_web.php';
require '../app/bootstrap.php';
date_default_timezone_set('Asia/Shanghai');

function pr( $var = '', $is_exit=true )
{
    echo '<pre>';
    if (empty($var)) {
        var_dump($var);
    } else {
        print_R($var);
    }
    echo '</pre>';
    if($is_exit)
        exit;
}