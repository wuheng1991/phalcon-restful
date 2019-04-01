<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;
use Dotenv\Dotenv;
use Phalcon\Config;

# 指定编码
header("Content-Type:text/json;charset=utf-8;");
# 指定允许其他域名访问
//header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Headers:Origin,X-Requested-With,Content-Type,Accept,Token');
//# 响应类型
header('Access-Control-Allow-Methods:GET,POST,PUT,DELETE,OPTIONS');

error_reporting(E_ALL);
ini_set("display_errors", "On");

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

# 设置时区
date_default_timezone_set('Asia/Shanghai');
# dev:开发  test:测试   pro:线上
define('APP_ENV', 'test');

try {
    /**
     * The FactoryDefault Dependency Injector automatically registers the services that
     * provide a full stack framework. These default services can be overidden with custom ones.
     */
    $di = new FactoryDefault();

    /**
     * Include general services
     */
    require APP_PATH . '/config/services.php';

    /**
     * Include web environment specific services
     */
    require APP_PATH . '/config/services_web.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();
    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    /**
     * Handle the request
     */
    $application = new Application($di);

    /**
     * Register application modules
     */
    $application->registerModules([
        'frontend' => ['className' => 'Api\Modules\Frontend\Module',],
        'backend' => ['className' => 'Api\Modules\Backend\Module',],
        'api' => ['className' => 'Api\Modules\Api\Module',],
    ]);

    function di($di_name){
        global $di;
        return $db = $di->get($di_name);
    }

    /**
     * Include routes
     */
    require APP_PATH . '/config/routes.php';

    echo str_replace(["\n","\r","\t"], '', $application->handle()->getContent());

} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
