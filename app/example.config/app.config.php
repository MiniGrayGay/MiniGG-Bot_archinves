<?php

/**
 * 
 * 参数信息
 * 
 */
define('BOT_TYPE', $_GET['botType'] ?? 1); //官方频道Bot接口需要设置，1 公域，0 私域
define('FRAME_ID', $_GET['frameId'] ?? 70000);
define('FRAME_IP', $_GET['frameIp'] ?? "127.0.0.1"); //如果Bot无响应可替换127.0.0.1为你的域名
define('FRAME_GC', $_GET['frameGc'] ?? NULL);
define('FRAME_KEY', $_POST['key'] ?? NULL);
//挂机器人的服务器，请求回去的时候需要
define('APP_API_HOST', "https://bot.w.minigg.cn"); //如果Bot无响应可替换为你的域名

/**
 * 
 * 卡片信息
 * 
 */
define('APP_DESC', "卡片信息");
define('APP_MSG_ID', 1105200115);
define('APP_MSG_NAME', "com.tencent.structmsg");
define('APP_MSG_TAG', "膨胀的小灰灰");
define('APP_MSG_TYPE', 1);
define('APP_VIEW', "news");

$appInfo['debug'] = false;
$appInfo['noKeywords'] = "姿势不对哦~\n发送【功能】可以查看派蒙的全部烹饪方法!";

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
  "MYPCQQ" => array(
    "id" => "",
    "name" => "",
    "accessToken" => "",
    "verifyToken" => "",
    "uin" => ""
  ),
  "WSLY" => array( //微信Bot-可爱猫
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
    "uin" => ""
  ),
  "QQChannel" => array(
    array( //GO-CQ频道Bot接口填这里
      "id" => "",
      "name" => "",
      "accessToken" => "",
      "uin" => ""
    ),
    array( //官方频道Bot接口填这个
      "id" => "", //填Botid
      "name" => "", //Bot的名字
      "accessToken" => "", //Token
      "verifyToken" => "", //Secret-虽然暂时用不上但还是写上吧
      "uin" => "" //Bot的Userid，启动 node app/ws/qq_ws.js 时提示的第一个userid
    )
  )
);

if (FRAME_ID == 10000) {
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

$originInfo[10000] = "http://127.0.0.1:8010";
$originInfo[20000] = "http://127.0.0.1:8073/send"; //如果可爱猫客户端与网站不在同一机器按需修改成对应域名
$originInfo[50000] = "https://openapi.noknok.cn";
$originInfo[60000] = "http://127.0.0.1:5700"; //GO-CQhttp默认Http地址
$originInfo[70000] = "https://api.sgroup.qq.com"; //QQ官方频道正式接口
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
