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
//挂机器人的服务器，请求回去的时候需要
define('GenshinWikiUrl', "https://info.minigg.cn/");


/**
 *
 * 卡片信息
 *
 */
define('APP_DESC', "奶香的一刀");
define('APP_MSG_ID', 1919810);
define('APP_MSG_NAME', "com.tencent.structmsg");
define('APP_MSG_TAG', "奶香的一刀");
define('APP_MSG_TYPE', 1);
define('APP_VIEW', "news");

$appInfo['debug'] = false;
$appInfo['noKeywords'] = "进不去！怎么想都进不去吧！！！~\n发送【功能】可以查看咱的所有技能!";

/**
 *
 * 主动推送密钥
 *
 */
$appKey = array(
    "65e4f038e9857ceb12d481fb58e1e23d", //我
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
        //测试中-小爱开放平台-仅支持单行文字的功能
        "id" => "12345",
        "name" => "小爱同学",
        "accessToken" => NULL,
        "verifyToken" => NULL,
        "uin" => "12345"
    ),
    "MYPCQQ" => array(
        //QQ机器人-留空即可
        "id" => "",
        "name" => "",
        "accessToken" => "",
        "verifyToken" => "",
        "uin" => ""
    ),
    "WSLY" => array(
        //可爱猫
        "id" => "", //留空
        "name" => "", //留空
        "accessToken" => "", //设置了访问API KEY时填入，否则留空
        "verifyToken" => "", //留空
        "inviteInGroup" => $inviteInGroup[array_rand($inviteInGroup)],
        "uin" => ""
    ),
    "NOKNOK" => array(//联系NokNok管理员获取
        "id" => "", //uid
        "name" => "",   //昵称
        "accessToken" => "",    //token
        "verifyToken" => "",    //verifytoken
        "uin" => ""
    ),
    "QQChannel" => array(
        //第一个array为GO-CQHttp，第二个array为官方API的配置文件
        array(
            "id" => "",
            "name" => "",
            "accessToken" => "",
            "uin" => ""
        ),
        array(
            //QQ官方频道API https://bot.q.qq.com/#/developer/developer-setting
            "id" => "", //开发设置内的 BotAppID
            "name" => "", //需要和设置内的机器人名称一致
            "accessToken" => "", //开发设置内的 Bot Token
            "verifyToken" => "", //开发设置内的 Bot Secret
            "uin" => "" //填入 yarn start:qq 启动WS后，尝试鉴权后的消息user字段内的id
        )
    )
);

if (FRAME_ID == 5000) {
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

$originInfo[10000] = "http://127.0.0.1:8010"; //默认转发回本机8010端口，如果MyPCQQ与网站不在同一机器按需修改成对应域名
$originInfo[20000] = "http://127.0.0.1:8073/send"; //默认转发回本机8073端口，如果可爱猫与网站不在同一机器按需修改成对应域名
$originInfo[50000] = "https://openapi.noknok.cn";
$originInfo[60000] = "http://127.0.0.1:8020"; //GO-CQhttp默认Http端口为5700，按需修改
$originInfo[70000] = "https://api.sgroup.qq.com"; //使用沙箱模式时替换URL为 https://sandbox.api.sgroup.qq.com ，沙箱环境只会收到测试频道的事件，且调用openapi仅能操作测试频道
//-
$appInfo['originInfo'] = $originInfo;

$codeInfo[1000] = "您不是管理员";
$codeInfo[1001] = "该群 或 框架暂不支持该功能";
$codeInfo[1002] = "内容为空，请稍后再来看看吧";
$codeInfo[1003] = "还未更新，请稍后再来看看吧";
$codeInfo[1004] = "玩家不存在 或 未公开";
$codeInfo[1005] = "可能存在违规内容，请修改后再试试吧~";
//-
$appInfo['codeInfo'] = $codeInfo;

$authInfo[1000] = array(
    ""
);

$authInfo[1003] = array(
    "" //舔狗日记、网易云热评、互删句子接口密钥
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

$appInfo['MiniGGApi']['Characters'] = GenshinWikiUrl . "characters?query=";
$appInfo['MiniGGApi']['Weapons'] = GenshinWikiUrl . "weapons?query=";
$appInfo['MiniGGApi']['Talents'] = GenshinWikiUrl . "talents?query=";

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
        mkdir($debugDir, 0777);
    }

    file_put_contents($debugDir . "/{$type}_" . FRAME_ID . "_" . TIME_T . ".txt", $log);
}
