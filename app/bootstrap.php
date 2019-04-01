<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;
use Dotenv\Dotenv;
use Phalcon\Config;
use App\DI;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;

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
    // $di = new FactoryDefault();



    /**
     * Include general services
     */
     #require APP_PATH . '/config/services.php';

    $config = include APP_PATH . "/config/config_main.php";
    include APP_PATH . '/config/loader.php';
    require_once APP_PATH . '/DI.php';

    $di = (new DI($config))->getDI();
    
    function di($di_name){
        return $db = \Phalcon\Di::getDefault()->getShared($di_name);
    }

    // * Include web environment specific services
    // */

    #require APP_PATH . '/config/services_web.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */


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

    /**
     * Configure the Volt service for rendering .volt templates
     */
    $di->setShared('voltShared', function ($view) {
        $config = $this->getConfig();

        $volt = new VoltEngine($view, $this);
        $volt->setOptions([
            'compiledPath' => function($templatePath) use ($config) {
                $basePath = $config->application->appDir;
                if ($basePath && substr($basePath, 0, 2) == '..') {
                    $basePath = dirname(__DIR__);
                }

                $basePath = realpath($basePath);
                $templatePath = trim(substr($templatePath, strlen($basePath)), '\\/');

                $filename = basename(str_replace(['\\', '/'], '_', $templatePath), '.volt') . '.php';

                $cacheDir = $config->application->cacheDir;
                if ($cacheDir && substr($cacheDir, 0, 2) == '..') {
                    $cacheDir = __DIR__ . DIRECTORY_SEPARATOR . $cacheDir;
                }

                $cacheDir = realpath($cacheDir);

                if (!$cacheDir) {
                    $cacheDir = sys_get_temp_dir();
                }

                if (!is_dir($cacheDir . DIRECTORY_SEPARATOR . 'volt' )) {
                    @mkdir($cacheDir . DIRECTORY_SEPARATOR . 'volt' , 0755, true);
                }

                return $cacheDir . DIRECTORY_SEPARATOR . 'volt' . DIRECTORY_SEPARATOR . $filename;
            }
        ]);

        return $volt;
    });

    /**
     * Include routes
     */
    require APP_PATH . '/config/routes.php';

    echo str_replace(["\n","\r","\t"], '', $application->handle()->getContent());

} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}