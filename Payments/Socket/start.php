<?php
use Workerman\Worker;
use Workerman\Autoloader;
use PHPSocketIO\SocketIO;

define('GLOBAL_START', true);
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/start_web.php';
require_once __DIR__ . '/start_io.php';

Worker::runAll();
