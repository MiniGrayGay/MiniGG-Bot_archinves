<?php

//debug，会输出到 /app/cache/debug
const APP_DEBUG = false;

//获取时间戳
define('TIME_T', time());

//机器人配置
$botInfo = array(
    "XIAOAI" => array(
        "id" => "12345",
        "name" => "小爱同学",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => "12345"
    ),
    "MYPCQQ" => array(
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => ""
    ),
    "WSLY" => array(
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => ""
    ),
    "NOKNOK" => array(
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => "",
        "oper_id" => ""
    ),
    "QQChannel" => array(
        array(
            "id" => "",
            "name" => "",
            "accessToken" => "",
            "verifyToken" => "",
            "uin" => ""
        ),
        array(
            "id" => "",
            "name" => "",
            "accessToken" => "",
            "verifyToken" => "",
            "uin" => ""
        )
    )
);

//框架的回调地址
$originInfo[10000] = "http://127.0.0.1:8010";
$originInfo[20000] = "http://127.0.0.1:8073/send";
$originInfo[50000] = "https://openapi.noknok.cn";
$originInfo[60000] = "http://127.0.0.1:8020";
$originInfo[70000] = "https://api.sgroup.qq.com";

//参数信息
define('APP_BOT_TYPE', $_GET['botType'] ?? 1); //1 公域，0 私域
define('FRAME_ID', $_GET['frameId'] ?? 50000);
define('FRAME_IP', $_GET['frameIp'] ?? "127.0.0.1");
define('FRAME_GC', $_GET['frameGc'] ?? NULL);
define('FRAME_KEY', $_POST['key'] ?? NULL);

$appOrigin = $originInfo[FRAME_ID] ?? NULL;
$appOrigin = str_replace("127.0.0.1", FRAME_IP, $appOrigin);

define('APP_BOT_INFO', $botInfo);
define('APP_ORIGIN', $appOrigin);

/**
 *
 * debug 输出格式
 *
 */
function appDebug($type, $log)
{
    if (!APP_DEBUG) return;
    $dir = APP_DIR_CACHE . "debug";
    //不存在自动创建文件夹
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents("{$dir}/{$type}_" . FRAME_ID . "_" . TIME_T . ".txt", $log);
}