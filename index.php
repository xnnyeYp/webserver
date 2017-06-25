<?php
/**
 * Created by PhpStorm.
 * User: yueping
 * Date: 2017/6/22
 * Time: 23:45
 * email:596169733@qq.com
 */
$config =  require_once( __DIR__."/config.php");
require_once __DIR__."/server.php";

new Server($config);


