<?php

$reqRet = file_get_contents(APP_DIR_CONFIG . "app.config.json");
$resJson = json_decode($reqRet);

define('DEFAULT_UA', "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36");
define('CONFIG_VERSION', $resJson->version);
define('CONFIG_ADMIN', $resJson->admin);
define('CONFIG_ROBOT', $resJson->robot);

$group = $resJson->group;
define('CONFIG_GROUP_BLOCKLIST', $group->blockList);

$userBlockList = file_get_contents(APP_DIR_CONFIG . "user.blockList.txt");
define('CONFIG_USER_BLOCKLIST', explode(",", $userBlockList));

$msgBlockList = file_get_contents(APP_DIR_CONFIG . "msg.blockList.txt");
define('CONFIG_MSG_BLOCKLIST', $msgBlockList);

$msgWhiteList = file_get_contents(APP_DIR_CONFIG . "msg.whiteList.txt");
define('CONFIG_MSG_WHITELIST', $msgWhiteList);

$event = $resJson->event;
define('CONFIG_EVENT_ROBOT', json_encode($event->robot));
define('CONFIG_EVENT_GROUP', json_encode($event->group));

//send.php 主动推送的参数
define('PUSH_MSG_ROBOT', $_POST['msgRobot'] ?? 0);
define('PUSH_MSG_TYPE', $_POST['msgType'] ?? 1);
define('PUSH_MSG_SOURCE', $_POST['msgSource'] ?? 0);
define('PUSH_MSG_CONTENT', $_POST['msgContent'] ?? NULL);
$msgExt = $_POST['msgExt'] ?? NULL;
define('PUSH_MSG_EXT', $msgExt ? json_decode($msgExt, true) : array());

//时间戳
define('TIME_T', time());