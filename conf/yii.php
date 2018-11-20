<?php

$config = [
    'id' => 'Happy In',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'cache' => require(__DIR__ . '/cache.php'),
        'log' => require(__DIR__ . '/log.php'),
        'db' => require(__DIR__ . '/db.php'),
        'redis' => require(__DIR__ . '/redis.php'),
        'rabbitmq' => require(__DIR__ . '/rabbitmq.php'),
    ],
    'params' => require(__DIR__ . '/params.php'),
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;

?>
