<?php

/**
 *
 * 参数信息
 *
 */
define('BOT_TYPE', $_GET['botType'] ?? 1); //1 公域，0 私域
define('FRAME_ID', $_GET['frameId'] ?? 50000);
define('FRAME_IP', $_GET['frameIp'] ?? "127.0.0.1");
define('FRAME_GC', $_GET['frameGc'] ?? NULL);
define('FRAME_KEY', $_POST['key'] ?? NULL);

$appInfo['debug'] = false;
$appInfo['noKeywords'] = "指令不对哦，是要找咱玩嘛~\n发送【功能】可以查看咱的所有技能!";

$inviteInGroup = array(
    "12345@chatroom"
);

/**
 *
 * 机器人信息
 *
 */
$appInfo['botInfo'] = array(
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
        "inviteInGroup" => $inviteInGroup[array_rand($inviteInGroup)],
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
    ),
    "XXQ" => array(
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => ""
    )
);

define('PUSH_MSG_ROBOT', $_POST['msgRobot'] ?? 0);
define('PUSH_MSG_TYPE', $_POST['msgType'] ?? 1);
define('PUSH_MSG_SOURCE', $_POST['msgSource'] ?? 0);
define('PUSH_MSG_CONTENT', $_POST['msgContent'] ?? NULL);
$msgExt = $_POST['msgExt'] ?? NULL;
define('PUSH_MSG_EXT', $msgExt ? json_decode($msgExt, true) : array());

$t = time();
define('TIME_T', $t);
//当前

$originInfo[10000] = "http://127.0.0.1:8010";
$originInfo[20000] = "http://127.0.0.1:8073/send";
$originInfo[50000] = "https://openapi.noknok.cn";
$originInfo[60000] = "http://127.0.0.1:8020";
$originInfo[70000] = "https://api.sgroup.qq.com";
$originInfo[80000] = "https://api.91m.top";
//-
$appInfo['originInfo'] = $originInfo;

$codeInfo[1000] = "您暂无权限";
$codeInfo[1001] = "该群 或 框架暂不支持该功能";
$codeInfo[1002] = "内容为空，请稍后再来看看吧";
$codeInfo[1003] = "还未更新，请稍后再来看看吧";
$codeInfo[1004] = "玩家不存在 或 未公开";
$codeInfo[1005] = "可能存在违规内容，请修改后再试试吧~";
//-
$appInfo['codeInfo'] = $codeInfo;

/**
 *
 * 白名单
 *
 */
$whiteListInfo['coser'] = array();
$whiteListInfo['winRate'] = array();
//-
$appInfo['whiteListInfo'] = $whiteListInfo;

define('APP_INFO', $appInfo);

$specialGroup = array();
define('APP_SPECIAL_GROUP', $specialGroup);
//特殊群

$appOrigin = $appInfo['originInfo'][FRAME_ID] ?? NULL;
$appOrigin = str_replace("127.0.0.1", FRAME_IP, $appOrigin);
define('APP_ORIGIN', $appOrigin);

//Mysql配置
$dbConfig = array(array("localhost", "root", "test", "dbname", 3306));
//Redis配置
$redisConfig = array("");
define('APP_DB_CONFIG', $dbConfig);
define('APP_REDIS_CONFIG', $redisConfig);
/**
 *
 * debug 输出格式
 *
 */
function appDebug($type, $log)
{
    if (APP_INFO['debug'] == false) return;

    $debugDir = APP_DIR_CACHE . "debug";

    /**
     *
     * 不存在自动创建文件夹
     *
     */
    if (!is_dir($debugDir)) {
        mkdir($debugDir, 0755);
    }

    file_put_contents($debugDir . "/{$type}_" . FRAME_ID . "_" . TIME_T . ".txt", $log);
}
