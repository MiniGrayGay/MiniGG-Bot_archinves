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
define('APP_API_MINIGG', "https://info.minigg.cn");
define('APP_CD', 5);

$appInfo['debug'] = false;
$appInfo['noKeywords'] = "进不去！怎么想都进不去吧！！！~\n发送【功能】可以解锁小派蒙的所有姿势！";

/**
 *
 * 主动推送密钥
 *
 */
$appKey = array(
    "65e4f038e9857ceb12d481fb58e1e23d",
);

define('APP_KEY', $appKey);

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
        //小爱开放平台内测
        "id" => "12345",
        "name" => "小爱同学",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => "12345"
    ),
    "MYPCQQ" => array(
        //MYPCQQ机器人-无需额外配置
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => ""
    ),
    "WSLY" => array(
        //微信机器人可爱猫
        "id" => "", //微信原始id或留空
        "name" => "", //微信名或留空
        "accessToken" => "", //设置了访问API KEY时填入，否则留空
        "verifyToken" => "", //留空
        "inviteInGroup" => $inviteInGroup[array_rand($inviteInGroup)],
        "uin" => ""
    ),
    "NOKNOK" => array(
        //联系NokNok管理员获取
        "id" => "", //uid
        "name" => "",   //昵称
        "accessToken" => "",    //token
        "verifyToken" => "",    //verifytoken
        "uin" => "", //uid
        "oper_id" => ""
    ),
    "QQChannel" => array(
        array(
            //第一个array为GO-CQHttp，第二个array为官方API的配置文件
            "id" => "", //QQ号
            "name" => "",   //QQ昵称
            "accessToken" => "",    //如果设置了secret填入这里
            "verifyToken" => "",
            "uin" => "" //QQ号
        ),
        array(
            //QQ官方频道API https://bot.q.qq.com/#/developer/developer-setting
            "id" => "", //开发设置内的 BotAppID
            "name" => "",   //需要和设置内的机器人名称一致
            "accessToken" => "",    //开发设置内的 Bot Token
            "verifyToken" => "",    //开发设置内的 Bot Secret
            "uin" => "" //填入 yarn start:qq 启动WS后，尝试鉴权后的消息user字段内的id
        )
    ),
    "XXQ" => array(
        //X星球内测
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

$miniGGInfo['Api'] = APP_API_MINIGG;
$miniGGInfo['GachaSet'] = "https://bot.q.minigg.cn/src/plugins/genshingacha/set.php";
$miniGGInfo['Characters'] = APP_API_MINIGG . "characters?query=";
$miniGGInfo['Weapons'] = APP_API_MINIGG . "weapons?query=";
$miniGGInfo['Talents'] = APP_API_MINIGG . "talents?query=";
$miniGGInfo['Constellations'] = APP_API_MINIGG . "constellations?query=";
$miniGGInfo['Foods'] = APP_API_MINIGG . "foods?query=";
$miniGGInfo['Enemies'] = APP_API_MINIGG . "enemies?query=";
$miniGGInfo['Domains'] = APP_API_MINIGG . "domains?query=";
$miniGGInfo['Artifacts'] = APP_API_MINIGG . "artifacts?query=";
//-
$appInfo['miniGG'] = $miniGGInfo;

$authInfo[1000] = array(
    ""
);
//-
$appInfo['authInfo'] = $authInfo;

$iconInfo[10000] = array(
    '\uF09F94A5',
    '\uF09F9885'
);
$iconInfo[20000] = array(
    '[@emoji=\uD83D\uDD25]',
    '[@emoji=\uD83D\uDE05]'
);
$iconInfo[50000] = array(
    '🔥',
    '😅'
);
//-
$appInfo['iconInfo'] = $iconInfo;

FRAME_ID != 20000 ? $nowAreaType = "安卓QQ" : $nowAreaType = "安卓WX";
$appInfo['nowAreaType'] = $nowAreaType;
//默认大区

define('APP_INFO', $appInfo);

$whiteListGroup = array();
define('APP_WHITELIST_GROUP', $whiteListGroup);
//白名单群

$specialGroup = array();
define('APP_SPECIAL_GROUP', $specialGroup);
//特殊群

$appOrigin = $appInfo['originInfo'][FRAME_ID] ?? NULL;
$appOrigin = str_replace("127.0.0.1", FRAME_IP, $appOrigin);
define('APP_ORIGIN', $appOrigin);

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
