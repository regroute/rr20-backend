<?php
namespace App;
include (dirname(__FILE__) . "/../config/config.php");

use Illuminate\Container\Container; // Only needed for DB
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Cache\CacheManager as CacheManager;


$dbc = new DB;

$dbc->addConnection([
        'driver'    => 'mysql',
            'host'      => $config['host'],
            'database'  => $config['database'],
            'username'  => $config['username'],
            'password'  => $config['password'],
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
    ]);

// $dbc->setFetchMode(PDO::FETCH_CLASS);

$container = $dbc->getContainer();
$container['config']['cache.driver'] = $config['cache_driver'];
$container['config']['cache.path'] = $config['cache_path'];
$container['config']['cache.prefix'] = "rr20";
$container['files'] = new Filesystem();
$container['config']['cache.memcached'] = ['host' => $config['cache_servers'], 'port' =>  $config['cache_port'], 'weight' => 100, ];

$container->offsetGet('config')->offsetSet('cache.driver', 'array');

$cacheManager = new CacheManager($container);
$dbc->setEventDispatcher(new Dispatcher(new Container));
$dbc->setCacheManager($cacheManager);
$dbc->setAsGlobal();
$dbc->bootEloquent();
global $dbc;

// $cache = new \Blablacar\Memcached\Client();
// $cache->addServer($config['cache_servers'], $config['cache_port']);



