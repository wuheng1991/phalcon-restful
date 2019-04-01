<?php
/*
 * Modified: preppend directory path of current file, because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */
defined('ROOT_PATH') || define('ROOT_PATH', realpath(__DIR__ . '/../..'));
defined('APP_PATH') || define('APP_PATH', ROOT_PATH . '/app');
define('APP_ENV_PATH', dirname(dirname(__DIR__)));
defined('GALAXY_WX_API_SECRET_KEY') || define('GALAXY_WX_API_SECRET_KEY', 'wx_api');
use Dotenv\Dotenv;
use Phalcon\Config;


if (file_exists(APP_ENV_PATH . '/.env')) {
    $dotenv = Dotenv::create(APP_ENV_PATH);
    $dotenv->load();
}else{
    echo 'this file is not exist';exit;
}

function env($key, $default = null)
{
    $value = getenv($key);
    return $value ? $value : $default;
}

defined('WECHAT_REDIRECT_URI') || define('WECHAT_REDIRECT_URI', env('WECHAT_REDIRECT_URI','http://wxapi.com'));
defined('APPSECRET') || define('APPSECRET', env('APPSECRET',''));
defined('APPID') || define('APPID', env('APPID',''));

/**
 * The System EVN.
 */
return new Config(
    [
        'wechat_front_url' => env('WECHAT_FRONT_URL','https://test.com'),
        'wechat_back_url' => env('WECHAT_BACK_URL','http://test.com'),
        'apiUrl' => env('apiUrl','http://wxapi.com'),

        'workwechat_appid' => env('workwechat_appid',''),
        'workwechat_agentid' => env('workwechat_agentid',''),
        /*
        |--------------------------------------------------------------------------
        | Version Environment
        |--------------------------------------------------------------------------
        |
        | This value is version for this project.
        |
        */
        'version' => env('APP_VERSION', '1.0.0'),

        /*
        |--------------------------------------------------------------------------
        | Domain Environment
        |--------------------------------------------------------------------------
        |
        | This value is the base url for app. When you need a wx redirecturl, but
        | you have many applications, you can set this value is "http://wx.xxx.com/phal/"
        | then set nginx proxy to this application.
        |
        */
        'domain' => env('APP_URL', 'localhost'),

        /*
        |--------------------------------------------------------------------------
        | Timezone Environment
        |--------------------------------------------------------------------------
        |
        | This value is the timezone for app.
        |
        */
        'timezone' => env('APP_TIMEZONE', 'Asia/Shanghai'),

        /*
        |--------------------------------------------------------------------------
        | Database Environment
        |--------------------------------------------------------------------------
        |
        | This value determines the "environment" your database.
        |
        */
        'database' => [
            'adapter' => env('DB_ADAPTER', 'Mysql'),
            'host' => env('DB_HOST', 'localhost'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', null),
            'dbname' => env('DB_DBNAME', 'phalcon'),
            'charset' => env('DB_CHARSET', 'utf8'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Redis Environment
        |--------------------------------------------------------------------------
        |
        | This value determines the "environment" your redis.
        |
        */
        'redis' => [
            'host' => env('REDIS_HOST', '192.168.0.111'),
            'port' => env('REDIS_PORT', '6380'),
            'auth' => env('REDIS_AUTH', '123456'),
            'persistent' => env('REDIS_PERSISTENT', false),
            'index' => env('REDIS_INDEX', 10),
            'prefix' => env('REDIS_PREFIX', ''),
            'lifetime' => env('REDIS_LIFETIME', 86400),
        ],

        /*
        |--------------------------------------------------------------------------
        | MongoDB Environment
        |--------------------------------------------------------------------------
        |
        | This value determines the "environment" your mongo.
        |
        */
        'mongo' => [
            'host' => env('MONGODB_HOST', '127.0.0.1'),
            'port' => env('MONGODB_PORT', '27017'),
            'connect' => env('MONGODB_CONNECT', true),
            'timeout' => env('MONGODB_TIMEOUT', null),
            'replicaSet' => env('MONGODB_REPLICA_SET', null),
            'username' => env('MONGODB_USERNAME', null),
            'password' => env('MONGODB_PASSWORD', null),
            'db' => env('MONGODB_DB', null),
            'collection' => env('MONGODB_COLLECTION', null),
            // 是否开启Mongo辅助类
            'isUtils' => env('MONGODB_IS_UTILS', false),
            // 是否开启Mongo Collection集合类
            'isCollection' => env('MONGODB_IS_COLLECTION', false),
        ],

        /*
        |--------------------------------------------------------------------------
        | Application Environment
        |--------------------------------------------------------------------------
        |
        | This value determines the "environment" your application is currently
        | running in. This may determine how you prefer to configure various
        | services your application utilizes.
        |
        */
        'application' => [
            'appDir' => APP_PATH,
            'configDir' => APP_PATH . '/config/',
            'controllersDir' => APP_PATH . '/controllers/',
            'modelsDir' => APP_PATH . '/common/models/',
            'backendServicesDir' => APP_PATH.'/modules/backend/services/',
            'apiServicesDir' => APP_PATH.'/modules/api/services/',
            'coreDir' => APP_PATH . '/core/',
            'jobsDir' => APP_PATH . '/jobs/',
            'libraryDir' => APP_PATH . '/library/',
            'logicsDir' => APP_PATH . '/logics/',
            'tasksDir' => APP_PATH . '/tasks/',
            'utilsDir' => APP_PATH . '/Utils/',
            'viewsDir' => APP_PATH . '/views/',
            'cacheDir' => ROOT_PATH . '/storage/cache/',
            'logDir' => ROOT_PATH . '/storage/log/',
            'metaDataDir' => ROOT_PATH . '/storage/meta/',
            'migrationsDir' => ROOT_PATH . '/storage/migrations/',
            'baseUri' => '/',
        ],

        'classes' => [
            'frontendModuleDir' => APP_PATH . '/modules/frontend/Module.php',
            'backendModuleDir' => APP_PATH . '/modules/backend/Module.php',
            'apiModuleDir' => APP_PATH . '/modules/api/Module.php',
            'cliModuleDir' => APP_PATH . '/modules/cli/Module.php',
        ],

        'files' => [
            'phpqrcodeDir' => APP_PATH ."/common/library/tool/phpqrcode.php",
            'wechatDir' => APP_PATH ."/common/library/wechat/wechat.php",
            'smsHelperDir' => APP_PATH ."/common/library/sms/SmsHelper.php",
            'redisDir' => APP_PATH ."/common/library/redis/redis.php",
            'expressDir' => APP_PATH ."/common/library/express/express.php",
        ],

        'dirs' => [
            'pluginsDir' => APP_PATH ."/common/library/plugins",
            'wechatDir' => APP_PATH ."/common/library/wechat",
            'smsDir' => APP_PATH ."/common/library/sms",
            'redisDir' => APP_PATH ."/common/library/redis",
        ],

        /*
        |--------------------------------------------------------------------------
        | printNewLine Environment
        |--------------------------------------------------------------------------
        |
        | If configs is set to true, then we print a new line at the end of each execution
        |
         */
        'printNewLine' => true,

        /*
        |--------------------------------------------------------------------------
        | Log Environment
        |--------------------------------------------------------------------------
        |
        | If db is set to true, then we write a log at the end of each sql.
        |
        */
        'log' => [
            'db' => env('LOG_DB', true),
            'error' => env('LOG_ERROR', true),
        ],

        /*
        |--------------------------------------------------------------------------
        | Model Meta Environment
        |--------------------------------------------------------------------------
        |
        | The modelMetaData support file and redis.
        |
        */
        'modelMeta' => [
            'driver' => env('MODELMETA_DRIVER', 'file'),
            'statsKey' => env('STATS_KEY','_PHCM_MM'),
            'lifetime' => env('LIFE_TIME', 172800),
            'index' => env('REDIS_INDEX', 0),
        ],

        /*
        |--------------------------------------------------------------------------
        | Cache Environment
        |--------------------------------------------------------------------------
        |
        | The default setting is file.
        |
         */
        'cache' => [
            'type' => env('CACHE_DRIVER', 'file'),
            'lifetime' => 172800,
        ],

        /*
        |--------------------------------------------------------------------------
        | SESSION Environment
        |--------------------------------------------------------------------------
        |
        | The default setting is file.
        |
        */
        'session' => [
            'type' => env('SESSION_DRIVER', 'file'),
        ],

        /*
        |--------------------------------------------------------------------------
        | COOKIES Environment
        |--------------------------------------------------------------------------
        |
        | isCrypt::是否加密 默认值false.
        |
        */
        'cookies' => [
            'isCrypt' => env('COOKIE_ISCRYPT', false),
        ],

        /*
        |--------------------------------------------------------------------------
        | CRYPT Environment
        |--------------------------------------------------------------------------
        |
        | key::The secret key.
        |
        */
        'crypt' => [
            'key' => env('CRYPT_KEY', 'phalcon-project-cookie->key'),
        ],

        /*
        |--------------------------------------------------------------------------
        | QUEUE Environment
        |--------------------------------------------------------------------------
        |
        | key: 消息队列的KEY键
        | delayKey: 延时消息队列的KEY键
        | errorKey: 失败的消息队列的KEY键
        |
        */
        'queue' => [
            'key' => env('QUEUE_KEY', 'phalcon:queue:default'),
            'delayKey' => env('QUEUE_DELAY_KEY', 'phalcon:queue:delay'),
            'errorKey' => env('QUEUE_ERROR_KEY', 'phalcon:queue:error'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Services
        |--------------------------------------------------------------------------
        |
        | 依赖注入服务
        |
        */
        'services' => [
            'common' => [
                'config' => App\Core\Services\ConfigService::class, // 系统配置
                'app' => App\Core\Services\App::class, // 自定义配置
                'db' => App\Core\Services\Db::class,
                'modelsMetadata' => App\Core\Services\ModelsMetadata::class,
                'filter' => App\Core\Services\Filter::class,
                'cache' => App\Core\Services\Cache::class,
                'error' => App\Core\Services\Error::class,
                'crypt' => App\Core\Services\Crypt::class,
                'redis' => App\Core\Services\Redis::class,
                'mongo' => App\Core\Services\Mongo::class,
                'cookies' => App\Core\Services\Cookies::class,
                'session' => App\Core\Services\Session::class,
                'modelsManager' => App\Core\Services\ModelsManager::class,
                'profiler' => App\Core\Services\Profiler::class,
                'logger' => App\Core\Services\Logger::class,
                'wechat' => App\Core\Services\Wechat::class,
                'SmsHelper' => App\Core\Services\SmsHelper::class,
                'wechatwork' => App\Core\Services\Wechatwork::class,
                'mail' => App\Core\Services\Mail::class,
            ],
            'cli' => [
                'dispatcher' => App\Core\Services\Cli\Dispatcher::class,
                'console' => App\Core\Services\Cli\Console::class,
                'xconsole' => App\Core\Services\Cli\XConsole::class,
            ],
            'mvc' => [
                'router' => App\Core\Services\Mvc\Router::class,
                'url' => App\Core\Services\Mvc\Url::class,
                'view' => App\Core\Services\Mvc\View::class,
                'dispatcher' => App\Core\Services\Mvc\Dispatcher::class,
            ],
        ],
    ]
);
