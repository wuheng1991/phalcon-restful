<?php

$router = $di->getRouter();

foreach ($application->getModules() as $key => $module) {
    $namespace = preg_replace('/Module$/', 'Controllers', $module["className"]);
    $router->add('/'.$key.'/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 'index',
        'action' => 'index',
        'params' => 1
    ])->setName($key);

    $router->add('/'.$key.'/:controller/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 'index',
        'params' => 2
    ]);
    $router->add('/'.$key.'/:controller/:action/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 2,
        'params' => 3
    ]);

    $router->addPost('/'.$key.'/:controller/:params', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action'     => 'create',
        'params' => 2,
    ]);

    $router->addPut('/'.$key.'/:controller/:int', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action'     => 'save',
        'id' => 2,
    ]);

    $router->addDelete('/'.$key.'/:controller/:int', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action'     => 'delete',
        'id' => 2,
    ]);

    $router->addGet('/'.$key.'/:controller/:int', [
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action'     => 'get',
        'id' => 2,
    ]);

//    $router->addGet('/'.$key.'/:controller/:params', [
//        'namespace' => $namespace,
//        'module' => $key,
//        'controller' => 1,
//        'action'     => 'search',
//        'params' => 2,
//    ]);

}
