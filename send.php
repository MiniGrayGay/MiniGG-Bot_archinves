<?php

require_once("main.php");

if (!FRAME_KEY || !in_array(FRAME_KEY, APP_KEY) || !PUSH_MSG_SOURCE || !PUSH_MSG_CONTENT) return;

$appManager = new app();

/**
 *
 * msgExt 示例
 *
 */
/*
$extMsgType = "at_msg";

$extMsgOrigMsg = array(
    "robot_wxid" => (string)$msgRobot, //机器人
    "from_wxid" => (string)$msgSource //群号
);

array(
    "msgOrigMsg" => $extMsgOrigMsg,
    "msgAtNokNok" => array(
        "at_type" => 2,
        "at_uid_list" => array()
    ),
    "msgAtQQChannel" => array(
        "at_type" => 2,
        "at_uid_list" => array()
    ),
    "msgType" => $extMsgType
)
*/
$appManager->appSend(PUSH_MSG_ROBOT, PUSH_MSG_TYPE, PUSH_MSG_SOURCE, PUSH_MSG_SOURCE, PUSH_MSG_CONTENT, PUSH_MSG_EXT);

echo json_encode(array(
    "data" => NULL,
    "status" => array(
        "code" => 200,
        "msg" => "ok"
    ),
    "usedtime" => 0
));
