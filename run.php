<?php
require_once 'vendor/autoload.php';
require_once 'config.php';
use WebSocket\Server;
$server = new Server($host,$port);