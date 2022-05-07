<?php
/**
 * Debug调试
 */
$appInfo['debug'] = false;

/**
 * 如果Bot不正常可改为当前的IP或者域名，推荐IP
 */
define('FRAME_IP', $_GET['frameIp'] ?? "127.0.0.1");

/**
 * QQ官方频道Bot接口参数
 * 1 公域，0 私域
 */
define('BOT_TYPE', $_GET['botType'] ?? 1);

/**
 * 命令不存在时默认回复
 */
$appInfo['noKeywords'] = "进不去！怎么想都进不去吧！！！~\n发送【功能】可以查看咱的所有技能!";

/**
 * 机器人信息-发
 */
$originInfo[10000] = "http://127.0.0.1:8010";   //MyPCQQ，默认转发回本机8010端口，如果MyPCQQ与网站不在同一服务器按需修改成对应域名
$originInfo[20000] = "http://127.0.0.1:8073/send";  //微信可爱猫，默认转发回本机8073端口，如果可爱猫与网站不在同一服务器按需修改成对应域名
$originInfo[50000] = "https://openapi.noknok.cn";   //NokNok，默认无需修改
$originInfo[60000] = "http://127.0.0.1:5700";   //GO-CQhttp默认Http通信端口为5700，按需修改
$originInfo[70000] = "https://api.sgroup.qq.com";   //使用沙箱模式时替换URL为 https://sandbox.api.sgroup.qq.com ，沙箱环境只会收到测试频道的事件，且调用openapi仅能操作测试频道
$originInfo[80000] = "https://api.91m.top"; //X星球，默认无需修改
$appInfo['originInfo'] = $originInfo;

/**
 * 框架默认参数-一般情况下无需修改
 */
define('FRAME_ID', $_GET['frameId'] ?? 50000);
define('FRAME_GC', $_GET['frameGc'] ?? NULL);
define('FRAME_KEY', $_POST['key'] ?? NULL);
define('APP_API_MINIGG', "https://info.minigg.cn/");
$inviteInGroup = array("114514@chatroom");

/**
 * 机器人设置-收
 */

$appInfo['botInfo'] = array(
    "XIAOAI" => array(
        //小爱开放平台内测
        "id" => "12345",
        "name" => "小爱同学",
        "accessToken" => NULL,
        "verifyToken" => NULL,
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
        //可爱猫
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
        "uin" => "" //uid
    ),
    "QQChannel" => array(
        //第一个array为GO-CQHttp，第二个array为官方API的配置文件
        array(
            "id" => "", //QQ号
            "name" => "",   //QQ昵称
            "accessToken" => "", //如果设置了secret填入这里
            "uin" => "" //QQ号
        ),
        array(
            //QQ官方频道API https://bot.q.qq.com/#/developer/developer-setting
            "id" => "", //开发设置内的 BotAppID
            "name" => "", //需要和设置内的机器人名称一致
            "accessToken" => "", //开发设置内的 Bot Token
            "verifyToken" => "", //开发设置内的 Bot Secret
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

if (FRAME_ID == 2500) {
    $nowRobot = $appInfo['botInfo']['XIAOAI']['uin'];
} elseif (FRAME_ID == 10000) {
    $nowRobot = $appInfo['botInfo']['MYPCQQ']['uin'];
} elseif (FRAME_ID == 20000) {
    $nowRobot = $appInfo['botInfo']['WSLY']['uin'];
} elseif (FRAME_ID == 50000) {
    $nowRobot = $appInfo['botInfo']['NOKNOK']['uin'];
} elseif (FRAME_ID == 60000) {
    $nowRobot = $appInfo['botInfo']['QQChannel'][0]['uin'];
} elseif (FRAME_ID == 70000) {
    $nowRobot = $appInfo['botInfo']['QQChannel'][1]['uin'];
} elseif (FRAME_ID == 80000) {
    $nowRobot = $appInfo['botInfo']['XXQ']['uin'];
} else {
    exit(1);
}

define('PUSH_MSG_ROBOT', $_POST['msgRobot'] ?? $nowRobot);
define('PUSH_MSG_TYPE', $_POST['msgType'] ?? 1);
define('PUSH_MSG_SOURCE', $_POST['msgSource'] ?? 0);
define('PUSH_MSG_CONTENT', $_POST['msgContent'] ?? NULL);
$msgExt = $_POST['msgExt'] ?? NULL;
define('PUSH_MSG_EXT', $msgExt ? json_decode($msgExt, true) : array());

$t = time();
define('TIME_T', $t);
//当前

$codeInfo[1000] = "您不是管理员";
$codeInfo[1001] = "该群 或 框架暂不支持该功能";
$codeInfo[1002] = "内容为空，请稍后再来看看吧";
$codeInfo[1003] = "还未更新，请稍后再来看看吧";
$codeInfo[1004] = "玩家不存在 或 未公开";
$codeInfo[1005] = "可能存在违规内容，请修改后再试试吧~";
//-
$appInfo['codeInfo'] = $codeInfo;

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

$miniGGInfo['Api'] = APP_API_MINIGG;
$miniGGInfo['Characters'] = APP_API_MINIGG . "characters?query=";
$miniGGInfo['Weapons'] = APP_API_MINIGG . "weapons?query=";
$miniGGInfo['Talents'] = APP_API_MINIGG . "talents?query=";
$miniGGInfo['Constellations'] = APP_API_MINIGG . "constellations?query=";
$miniGGInfo['Foods'] = APP_API_MINIGG . "foods?query=";
$miniGGInfo['Enemies'] = APP_API_MINIGG . "enemies?query=";
$miniGGInfo['Domains'] = APP_API_MINIGG . "domains?query=";
$miniGGInfo['Artifacts'] = APP_API_MINIGG . "artifacts?query=";

$appInfo['miniGG'] = $miniGGInfo;

define('APP_INFO', $appInfo);

$whiteListGroup = array();
define('APP_WHITELIST_GROUP', $whiteListGroup);
//白名单群

$specialGroup = array();
define('APP_SPECIAL_GROUP', $specialGroup);
//特殊群

$appOrigin = APP_INFO['originInfo'][FRAME_ID] ?? NULL;
$appOrigin = str_replace("127.0.0.1", FRAME_IP, $appOrigin);
define('APP_ORIGIN', $appOrigin);

/**
 * debug 输出格式
 */
function appDebug($type, $log)
{
    if (APP_INFO['debug'] == false) return;
    $debugDir = APP_DIR_CACHE . "debug";

    /**
     * 不存在自动创建文件夹
     */

    if (!is_dir($debugDir)) {
        mkdir($debugDir, 0755);
    }

    file_put_contents($debugDir . "/{$type}_" . FRAME_ID . "_" . TIME_T . ".txt", $log);
}
