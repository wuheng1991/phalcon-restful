<?php

use Phalcon\Loader;
$loader = new Loader();

/**
 * Register Namespaces
 */
$loader->registerNamespaces([
    'App' => $config->application->appDir,
    'Api\Models' => $config->application->modelsDir,
    'Backend\Services' => $config->application->backendServicesDir,
    'Api\Services' => $config->application->apiServicesDir,
    'App\Utils' => $config->application->utilsDir,
    'App\Core' => $config->application->coreDir,
])->registerFiles(
        [
            'function' => $config->application->coreDir . 'helper.php',
        ]);
/**
 * Register module classes
 */
$loader->registerClasses([
    'Api\Modules\Frontend\Module' => $config->classes->frontendModuleDir,
    'Api\Modules\Backend\Module' => $config->classes->backendModuleDir,
    'Api\Modules\Api\Module' => $config->classes->apiModuleDir,
    'Api\Modules\Cli\Module' => $config->classes->cliModuleDir,
]);
$loader->registerFiles(
    [
        $config->files->phpqrcodeDir,
        $config->files->wechatDir,
        $config->files->smsHelperDir,
        $config->files->redisDir,
        $config->files->expressDir,
    ]
);
$loader->registerDirs(
    [
        $config->dirs->pluginsDir,
        $config->dirs->wechatDir,
        $config->dirs->smsDir,
        $config->dirs->redisDir,
    ]
);
$loader->register();
