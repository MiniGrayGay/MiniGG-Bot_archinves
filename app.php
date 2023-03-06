<?php

require_once("main.php");

$reqRet = file_get_contents("php://input");


if (!$reqRet) {
    echo ('{"errmsg":"入口文件不正确"}');
    exit(1);
}

$appInfo = APP_INFO;

/**
 *
 * 这里是框架的入口，可以自行拓展，统一格式，保证 FRAME_ID 唯一即可
 * 需要在 api.class.php 增加入口的回复 API ，也需要在 config/app.config.php 修改机器人信息，否则无法替换
 *
 */
if (FRAME_ID == 2500) {
    $resJson = json_decode($reqRet, true);

    $resSession = $resJson['session'] ?? array();
    $XIAOAIMsgSource = $resSession['application']['app_id'] ?? 0;
    $XIAOAIMsgSender = $resSession['user']['user_id'] ?? 0;

    $resRequest = $resJson['request'] ?? array();
    $XIAOAIMsgType = $resRequest['type'] ?? 2;
    $XIAOAIMsgId = $resRequest['request_id'] ?? NULL;
    $XIAOAIMsgContent = $resRequest['intent']['query'] ?? NULL;
    $XIAOAIMsgNoResponse = $msgRequest['no_response'] ?? NULL;
    $XIAOAIMsgRobot = $resRequest['intent']['app_id'] ?? 0;

    $appMic = true;

    if ($XIAOAIMsgNoResponse) {
        $res = "主人，还在嘛？";
    } elseif ($XIAOAIMsgType == 0) {
        $res = "你好，主人。";
    } elseif ($XIAOAIMsgType == 2) {
        $res = "再见，主人！";

        $appMic = false;
    }

    if ($res) {
        echo json_encode(
            array(
                'version' => '1.0',
                'session_sttributes' => array(),
                'response' => array(
                    'open_mic' => $appMic,
                    'to_speak' => array(
                        'type' => 0,
                        'text' => $res
                    )
                ),
                'is_session_end' => false
            )
        );
        exit(0);
    }

    $msgContentOriginal = $XIAOAIMsgContent;

    $msg = array(
        "Ver" => 0,
        "Pid" => 0,
        "Port" => 0,
        "MsgID" => $XIAOAIMsgId,
        "OrigMsg" => $reqRet ? base64_encode($reqRet) : NULL,
        "Robot" => $XIAOAIMsgRobot,
        "MsgType" => $XIAOAIMsgType,
        "MsgSubType" => 0,
        //"MsgFileUrl" => $XIAOAIMsgFileUrl,
        "Content" => $XIAOAIMsgContent ? base64_encode(urldecode($XIAOAIMsgContent)) : NULL,
        "Source" => $XIAOAIMsgSource,
        "SubSource" => NULL,
        //"SourceName" => $XIAOAIMsgSourceName ? $XIAOAIMsgSourceName : NULL,
        "Sender" => $XIAOAIMsgSender,
        //"SenderName" => $XIAOAIMsgSenderName ? $XIAOAIMsgSenderName : NULL,
        "Receiver" => $XIAOAIMsgSender,
        //"ReceiverName" => $XIAOAIMsgSenderName ? $XIAOAIMsgSenderName : NULL,
    );

    //XIAOAI:统一格式
} elseif (FRAME_ID == 10000) {
    $resJson = json_decode($reqRet, true);

    if ($resJson['MsgType'] == -1 || $resJson['MsgType'] == 1002)
        exit(1);

    $msg = $resJson;
    $msg['SubSource'] = NULL;

    $msgContentOriginal = base64_decode($resJson['Content']);

    /**
     *
     * 机器人号码
     *
     */
    $mpqMsgRobot = $resJson['Robot'] ?? "";

    /**
     *
     * 艾特消息
     *
     */
    if (strpos($msgContentOriginal, "[@") > -1) {
        $msgContentOriginal = str_replace("[@{$mpqMsgRobot}] ", "", $msgContentOriginal);
        $msgContentOriginal = str_replace("[@{$mpqMsgRobot}]", "", $msgContentOriginal);

        $msg['Content'] = base64_encode(urldecode($msgContentOriginal));
        //移除艾特再转回去
    }
} elseif (FRAME_ID == 20000) {
    $resJson = $_POST;

    $kamMsgContent = $_POST['msg'] ?? NULL;

    /**
     *
     * 机器人号码
     *
     */
    $kamMsgRobot = $_POST['robot_wxid'] ?? "";

    /**
     *
     * 艾特消息
     *
     * [@at,nickname=xxxx,wxid=xxxx]
     */
    if (strpos($kamMsgContent, "[@at") > -1) {
        $kamMsgContent = str_replace("wxid={$kamMsgRobot}]  ", "wxid={$kamMsgRobot}]", $kamMsgContent);
        $kamMsgContent = substr($kamMsgContent, strpos($kamMsgContent, "]") + 1, strlen($kamMsgContent));
    } else {
        $kamMsgContent = str_replace("[@emoji=\u60C7]", "惇", $kamMsgContent);
        $kamMsgContent = str_replace("[@emoji=\u72BD]", "犽", $kamMsgContent);
        $kamMsgContent = str_replace("[@emoji=\u6683]", "暃", $kamMsgContent);
    }

    /**
     *
     * 图片、文件消息
     *
     */
    $kamMsgFileUrl = $_POST['file_url'] ?? NULL;

    $kamType = $_POST['type'] ?? 0;
    $kamMsgType = $_POST['msg_type'] ?? 0;
    $kamMsgId = $_POST['rid'] ?? 0;
    $kamMsgSource = $_POST['from_wxid'] ?? "";
    //$kamMsgSourceName = $_POST['from_name'] ?? NULL;
    $kamMsgSender = $_POST['final_from_wxid'] ?? "";
    //$kamMsgSenderName = $_POST['final_from_name'] ?? "";

    $msgContentOriginal = $kamMsgContent;

    $msg = array(
        "Ver" => 0,
        "Pid" => 0,
        "Port" => 0,
        "MsgID" => $kamMsgId,
        "OrigMsg" => $reqRet ? base64_encode($reqRet) : NULL,
        "Robot" => $kamMsgRobot,
        "MsgType" => $kamType,
        "MsgSubType" => $kamMsgType,
        "MsgFileUrl" => $kamMsgFileUrl,
        "Content" => $kamMsgContent ? base64_encode(urldecode($kamMsgContent)) : NULL,
        "Source" => $kamMsgSource,
        "SubSource" => NULL,
        //"SourceName" => $kamMsgSourceName ? urldecode($kamMsgSourceName) : NULL,
        "Sender" => $kamMsgSender,
        //"SenderName" => $kamMsgSenderName ? urldecode($kamMsgSenderName) : NULL,
        "Receiver" => $kamMsgSender,
        //"ReceiverName" => $kamMsgSenderName ? urldecode($kamMsgSenderName) : NULL,
    );

    //可爱猫:未死鲤鱼:统一格式
} elseif (FRAME_ID == 50000) {
    $resJson = json_decode($reqRet, true);

    $botInfo = APP_BOT_INFO['NOKNOK'];

    if ($resJson['verify_token'] != $botInfo['verifyToken'])
        exit(1);
    //验证token

    $nokNokSignal = $resJson['signal'];

    if ($nokNokSignal == 1) {
        /**
         *
         * 刷新缓存 - > 下面代码全部为异步执行，即代表先返回内容再输出，不然会重复请求
         *
         * @link https://github.com/1250422131/NokNok-Bot-PHP-SDK/blob/main/NokNok_PHP_SDK/index.php#L12
         */
        ob_end_clean();
        ob_start();

        echo json_encode(
            array(
                "ret" => 0,
                "msg" => "ok"
            )
        );

        $obLength = ob_get_length();
        header("Content-Length: " . $obLength);

        ob_end_flush();

        if ($obLength) {
            ob_flush();
        }
        //输出刷新页面

        flush();

        if (function_exists("fastcgi_finish_request")) {
            fastcgi_finish_request();
        }

        sleep(1);
        //暂停x秒让Bot来处理逻辑

        ignore_user_abort(true);
        //让页面继续跑
    } elseif ($nokNokSignal == 2) {
        $nokNokHeartbeat = $resJson['heartbeat'];

        echo json_encode(
            array(
                "ret" => 0,
                "msg" => "ok",
                "heartbeat" => $nokNokHeartbeat,
            )
        );

        exit(0);
    }

    $nokNokMsgData = $resJson['data'][0];
    $nokNokMsgBody = $nokNokMsgData['body'];

    $l2_type = $nokNokMsgData['l2_type'];
    $l3_types = $nokNokMsgData['l3_types'][0] ?? NULL;

    if (!in_array($l2_type, array(1, 3)) || ($l3_type != array() && !in_array($l3_type, array(3))))
        exit(1);
    //l2_type 1:文本消息 3:图片消息
    //l3_types 3:at消息

    $nokNokMsgContent = $nokNokMsgBody['content'];

    /**
     *
     * 机器人号码
     *
     */
    $nokNokBotId = $botInfo['id'];
    $nokNokAtMsg = $nokNokMsgBody['at_msg'] ?? NULL;

    /**
     *
     * 艾特消息
     *
     */
    if (strpos($nokNokMsgContent, "@(") > -1) {
        $nokNokMsgContent = substr($nokNokMsgContent, strpos($nokNokMsgContent, ")") + 1, strlen($nokNokMsgContent));
    }

    /**
     *
     * 移除首尾空格
     *
     */
    $nokNokMsgContent = rtrim($nokNokMsgContent);
    $nokNokMsgContentIndex = strpos(substr($nokNokMsgContent, 0, 6), " ");
    if ($nokNokMsgContentIndex > -1) {
        $nokNokMsgContent = substr($nokNokMsgContent, $nokNokMsgContentIndex + 1, strlen($nokNokMsgContent));
    }

    $nokNokMsgImg = $nokNokMsgBody['pic_info'][0]['image_info_array'][0]['url'] ?? NULL;

    /**
     *
     * 图片消息
     *
     */
    if ($nokNokMsgImg) {
        $nokNokMsgContent = "[NOKNOK:image,url={$nokNokMsgImg}]";
    } elseif (!in_array($nokNokBotId, $nokNokAtMsg['at_uid_list']) || $nokNokMsgBody['bot_data']['is_bot']) {
        //艾特的不是机器人 或 是别人的机器人
        exit(1);
    }

    $nokNokMsgId = $nokNokMsgData['msg_id'];
    $nokNokMsgType = $nokNokMsgData['scope'] == "private" ? 1 : 2;
    $nokNokMsgSubType = $l2_type;
    $nokNokMsgRobot = $nokNokBotId;
    //$nokNokMsgFileUrl = NULL;
    $nokNokMsgSource = $nokNokMsgData['gid'];
    $nokNokMsgSubSource = $nokNokMsgData['target_id'];
    //$nokNokMsgSourceName = NULL;
    $nokNokMsgSender = $nokNokMsgData['sender_uid'];
    //$nokNokMsgSenderName = NULL;

    $msgContentOriginal = $nokNokMsgContent;

    $msg = array(
        "Ver" => 0,
        "Pid" => 0,
        "Port" => 0,
        "MsgID" => $nokNokMsgId,
        "OrigMsg" => $reqRet ? base64_encode($reqRet) : NULL,
        "Robot" => $nokNokMsgRobot,
        "MsgType" => $nokNokMsgType,
        "MsgSubType" => $nokNokMsgSubType,
        //"MsgFileUrl" => $nokNokMsgFileUrl,
        "Content" => $nokNokMsgContent ? base64_encode(urldecode($nokNokMsgContent)) : NULL,
        "Source" => $nokNokMsgSource,
        "SubSource" => $nokNokMsgSubSource,
        //"SourceName" => $nokNokMsgSourceName ? $nokNokMsgSourceName : NULL,
        "Sender" => $nokNokMsgSender,
        //"SenderName" => $nokNokMsgSenderName ? $nokNokMsgSenderName : NULL,
        "Receiver" => $nokNokMsgSender,
        //"ReceiverName" => $nokNokMsgSenderName ? $nokNokMsgSenderName : NULL,
    );

    //NokNok:官方:统一格式
} elseif (FRAME_ID == 60000) {
    $resJson = json_decode($reqRet, true);

    $QQChannelMsgType = $resJson['message_type'] ?? NULL;
    $QQChannelMsgSubType = $resJson['sub_type'] ?? NULL;
    $QQChannelMsgPostType = $resJson['post_type'] ?? NULL;

    //if (!$QQChannelMsgType || $QQChannelMsgType != "guild" || $QQChannelMsgSubType != "channel") exit(1);
    //排除非频道信息

    $QQChannelMsgContent = $resJson['message'] ?? NULL;

    /**
     *
     * 机器人号码
     *
     */
    $QQChannelMsgRobot = $resJson['self_tiny_id'] ?? NULL;

    /**
     *
     * 艾特消息
     *
     */
    if (strpos($QQChannelMsgContent, "[CQ:at") > -1) {
        $QQChannelMsgContent = str_replace("[CQ:at,qq={$QQChannelMsgRobot}] ", "", $QQChannelMsgContent);
        $QQChannelMsgContent = str_replace("[CQ:at,qq={$QQChannelMsgRobot}]", "", $QQChannelMsgContent);
    }

    $QQChannelMsgId = $resJson['message_id'] ?? NULL;
    //$QQChannelMsgFileUrl = NULL;
    if ($QQChannelMsgType == "guild") {
        $QQChannelMsgSource = $resJson['guild_id'] ?? NULL;
        $QQChannelMsgSubSource = $resJson['channel_id'] ?? NULL;
    } elseif ($QQChannelMsgType == "group") {
        $QQChannelMsgSource = $resJson['group_id'] ?? NULL;
        $QQChannelMsgSubSource = NULL;
    } else {
        $QQChannelMsgSource = NULL;
        $QQChannelMsgSubSource = NULL;
    }
    //$QQChannelMsgSourceName = NULL;
    $QQChannelMsgSender = $resJson['sender']['user_id'] ?? NULL;
    //$QQChannelMsgSenderName = NULL;

    $msgContentOriginal = $QQChannelMsgContent;

    $msg = array(
        "Ver" => 0,
        "Pid" => 0,
        "Port" => 0,
        "MsgID" => $QQChannelMsgId,
        "OrigMsg" => $reqRet ? base64_encode($reqRet) : NULL,
        "Robot" => $QQChannelMsgRobot,
        "MsgType" => $QQChannelMsgType,
        "MsgSubType" => $QQChannelMsgSubType,
        //"MsgFileUrl" => $QQChannelMsgFileUrl,
        "Content" => $QQChannelMsgContent ? base64_encode(urldecode($QQChannelMsgContent)) : NULL,
        "Source" => $QQChannelMsgSource,
        "SubSource" => $QQChannelMsgSubSource,
        //"SourceName" => $QQChannelMsgSourceName ? $QQChannelMsgSourceName : NULL,
        "Sender" => $QQChannelMsgSender,
        //"SenderName" => $QQChannelMsgSenderName ? $QQChannelMsgSenderName : NULL,
        "Receiver" => $QQChannelMsgSender,
        //"ReceiverName" => $QQChannelMsgSenderName ? $QQChannelMsgSenderName : NULL,
    );

    //QQChannel:统一格式
} elseif (FRAME_ID == 70000) {
    $resJson = json_decode($reqRet, true);

    $QQChannelMsgType = $resJson['t'];
    $QQChannelMsgData = $resJson['d'];

    $QQChannelMsgContent = $QQChannelMsgData['content'] ?? NULL;

    /**
     *
     * 机器人号码
     *
     */
    $QQChannelMsgRobot = APP_BOT_INFO['QQChannel'][1]['uin'] ?? NULL;

    /**
     *
     * 艾特消息
     *
     * 不知道是什么奇怪的空格
     */
    if (strpos($QQChannelMsgContent, "<@!") > -1) {
        $QQChannelMsgContent = str_replace("<@!{$QQChannelMsgRobot}>" . chr(32), "", $QQChannelMsgContent);
        $QQChannelMsgContent = str_replace("<@!{$QQChannelMsgRobot}>" . chr(194) . chr(160), "", $QQChannelMsgContent);
        $QQChannelMsgContent = str_replace("<@!{$QQChannelMsgRobot}>" . chr(194) . chr(177), "", $QQChannelMsgContent);
        $QQChannelMsgContent = str_replace("<@!{$QQChannelMsgRobot}>", "", $QQChannelMsgContent);
    }

    /**
     *
     * 图片消息
     *
     */
    $QQChannelMsgImg = $QQChannelMsgData['attachments'][0]['url'] ?? NULL;

    if ($QQChannelMsgImg) {
        if (!strpos($QQChannelMsgImg, "http"))
            $QQChannelMsgImg = "https://" . $QQChannelMsgImg;

        $QQChannelMsgContent = "[QC:image,url={$QQChannelMsgImg}]";
    }

    $QQChannelMsgId = $QQChannelMsgData['id'] ?? NULL;
    $QQChannelMsgSource = $QQChannelMsgData['guild_id'] ?? NULL;
    $QQChannelMsgSubSource = $QQChannelMsgData['channel_id'] ?? NULL;
    $QQChannelMsgSender = $QQChannelMsgData['author']['id'] ?? NULL;

    $msgContentOriginal = $QQChannelMsgContent;

    $msg = array(
        "Ver" => 0,
        "Pid" => 0,
        "Port" => 0,
        "MsgID" => $QQChannelMsgId,
        "OrigMsg" => $reqRet ? base64_encode($reqRet) : NULL,
        "Robot" => $QQChannelMsgRobot,
        "MsgType" => $QQChannelMsgType,
        "MsgSubType" => 0,
        //"MsgFileUrl" => $QQChannelMsgFileUrl,
        "Content" => $QQChannelMsgContent ? base64_encode(urldecode($QQChannelMsgContent)) : NULL,
        "Source" => $QQChannelMsgSource,
        "SubSource" => $QQChannelMsgSubSource,
        //"SourceName" => $QQChannelMsgSourceName ? $QQChannelMsgSourceName : NULL,
        "Sender" => $QQChannelMsgSender,
        //"SenderName" => $QQChannelMsgSenderName ? $QQChannelMsgSenderName : NULL,
        "Receiver" => $QQChannelMsgSender,
        //"ReceiverName" => $QQChannelMsgSenderName ? $QQChannelMsgSenderName : NULL,
    );

    //QQChannel:官方:统一格式
} else {
    exit(1);
}

/**
 *
 * 防止自己触发自己的
 *
 */
if ($msg['Sender'] == $msg['Robot'])
    exit(1);

/**
 *
 * 像 NOKNOK、QQ频道 这样分子频道的需要
 *
 */
if (FRAME_GC) {
    $gcArr = explode(",", FRAME_GC);

    $Source = $msg['Source'] ?? NULL;
    $SubSource = $msg['SubSource'] ?? NULL;

    $SubSource ? $nowGc = $SubSource : $nowGc = $Source;

    if (!in_array($nowGc, $gcArr))
        exit(1);
}

/**
 *
 * debug
 *
 */
if (APP_DEBUG)
    appDebug("input", $reqRet);

/**
 *
 * 优雅的移除前缀
 *
 */
if (preg_match("/^(\#|\/|\!)/i", $msgContentOriginal, $msgMatch)) {
    $msgMatch = array_unique($msgMatch);
    $matchValue = $msgMatch[0];

    $msgContentOriginal = str_replace($matchValue . " ", "", $msgContentOriginal);
    $msgContentOriginal = str_replace($matchValue, "", $msgContentOriginal);

    if ($msgContentOriginal) {
        $msg['Content'] = base64_encode(urldecode($msgContentOriginal));
    }
}

/**
 *
 * 群组的唯一 id
 *
 */
$nowSource = $msg['Source'];
$nowSubSource = $msg['SubSource'];
$nowSubSource ? $nowGc = $nowSource . "," . $nowSubSource : $nowGc = $nowSource;
$GLOBALS['msgGc'] = $nowGc;
$GLOBALS['msgRobot'] = $msg['Robot'];
$GLOBALS['msgSender'] = $msg['Sender'];

/**
 *
 * 一些定义
 *
 * msgImgNewSize 压缩图片 msgAtNokNok 艾特信息
 */
$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = NULL;
$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgOrigMsg'] = $resJson;
$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgUrl'] = NULL;
$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgFile'] = NULL;
$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgNewSize'] = true;
$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgAtNokNok'] = array();

$appManager = new app();
$appManager->linkRedis();

$nowRobot = FRAME_ID . "," . $msg['Robot'];
$nowSender = FRAME_ID . "," . $msg['Sender'];

/**
 *
 * 填写的机器人才会触发，默认全部
 *
 */
if (count(CONFIG_ROBOT) > 0) {
    if (!in_array($msg['Robot'], CONFIG_ROBOT))
        exit(1);
}

/**
 *
 * 黑名单的对象不会触发，默认无黑名单
 *
 */
/*
if (count(CONFIG_USER_BLOCKLIST) > 0) {
    if (in_array($msg['Sender'], CONFIG_USER_BLOCKLIST))
        exit(1);
}
*/
if (FRAME_ID == 10000) {
    /**
     *
     * 黑名单的群不会触发，默认无黑名单
     *
     */
    if (CONFIG_GROUP_BLOCKLIST) {
        if ($msg['MsgType'] == 2 && in_array($msg['Source'], CONFIG_GROUP_BLOCKLIST))
            exit(1);
    }

    /**
     *
     * 被某人添加好友
     *
     */
    if (in_array($msg['MsgType'], array(1000, 1001))) {
        $config_event_robot = json_decode(CONFIG_EVENT_ROBOT, true);
        $config_event_robot['passive']['add']['switch'] ? $retMsg = 10 : $retMsg = 20;

        $appManager->appHandleByMPQ($retMsg, $config_event_robot['passive']['add']['text']);
    }

    /**
     *
     * 被某人邀请加群
     *
     */
    if ($msg['MsgType'] == 2003) {
        $config_event_robot = json_decode(CONFIG_EVENT_ROBOT, true);
        $config_event_robot['passive']['invite']['switch'] ? $retMsg = 10 : $retMsg = 20;

        $appManager->appHandleByMPQ($retMsg, $config_event_robot['passive']['invite']['text']);
    }

    /**
     *
     * 某人申请加群
     *
     */
    if ($msg['MsgType'] == 2001) {
        $config_event_group = json_decode(CONFIG_EVENT_GROUP, true);
        $config_event_group['admin']['add']['switch'] ? $retMsg = 10 : $retMsg = 20;

        $appManager->appHandleByMPQ($retMsg, $config_event_group['admin']['add']['text']);
    }

    /**
     *
     * 某人邀请某人加群
     *
     */
    if ($msg['MsgType'] == 2002) {
        $config_event_group = json_decode(CONFIG_EVENT_GROUP, true);
        $config_event_group['user']['invite']['switch'] ? $retMsg = 10 : $retMsg = 20;

        $appManager->appHandleByMPQ($retMsg, $config_event_group['user']['invite']['text']);
    }
} elseif (FRAME_ID == 20000) {
    /**
     *
     * 黑名单的群不会触发，默认无黑名单
     *
     */
    if (CONFIG_GROUP_BLOCKLIST) {
        if ($msg['MsgType'] == 200 && in_array($msg['Source'], CONFIG_GROUP_BLOCKLIST))
            exit(1);
    }

    /**
     *
     * 被某人添加好友
     *
     */
    if ($msg['MsgType'] == 500) {
        $config_event_robot = json_decode(CONFIG_EVENT_ROBOT, true);

        if ($config_event_robot['passive']['add']['switch'] == true) {
            $appManager->appSetMsgType("json_msg");

            $newData = array();
            $newData['type'] = 303;
            $newData['robot_wxid'] = $msg['Robot'];
            $newData['msg'] = $msgContentOriginal;

            $appManager->requestApiByWSLY(json_encode($newData));
        }
    }
}

$allPlugins = array();
$allKeywords = $appManager->redisHget("robot-config", "allKeywords-" . FRAME_ID) ?? NULL;

/**
 *
 * 群、成员 全局状态
 *
 */
$GLOBALS['sourceStatusInfo'] = (int) $appManager->redisHget("info-user-" . $GLOBALS['msgGc'] . "-" . FRAME_ID, "statusInfo");
$GLOBALS['senderStatusInfo'] = (int) $appManager->redisHget("info-user-" . $msg['Sender'] . "-" . FRAME_ID, "statusInfo");

if (!$allKeywords) {
    /**
     *
     * 触发指定插件，管理员需要向机器人发送【功能】注册钩子，只有命中关键词的才会运行相关插件
     *
     */
    $allPlugins[] = array("name" => "system", "path" => "app/plugins/system");
} elseif ($GLOBALS['senderStatusInfo'] != 0) {
    /**
     *
     * 存在关键词时触发插件
     *
     */
    //$allPlugins[] = array("name" => "chatGPT", "path" => "app/plugins/chatGPT");
} elseif (preg_match($allKeywords, $msgContentOriginal, $msgMatch_1)) {
    $msgMatch_1 = array_unique($msgMatch_1);
    $msgMatch_1 = array_values($msgMatch_1);

    preg_match(CONFIG_MSG_BLOCKLIST, $msgContentOriginal, $msgMatch_2);
    $msgMatch_2 = array_unique($msgMatch_2);
    $msgMatch_2 = array_values($msgMatch_2);

    if (count($msgMatch_2) > 0) {
        if (preg_match(CONFIG_MSG_WHITELIST, $msgContentOriginal)) {
            //存在白名单
        } elseif ($GLOBALS['senderStatusInfo'] == 0) {
            $ret = $appInfo['codeInfo'][1005];
            $appManager->appSend($msg['Robot'], $msg['MsgType'], $msg['Source'], $msg['Sender'], $ret);
            //存在黑名单
            exit(1);
        }
    }

    /**
     *
     * 触发指定关键词传递给插件
     *
     */
    $allTrigger = json_decode(json_encode($appManager->redisHget("robot-config", "allTrigger-" . FRAME_ID)), true);

    for ($allMsgMatch_i = 0; $allMsgMatch_i < count($msgMatch_1); $allMsgMatch_i++) {
        $forList = $msgMatch_1[$allMsgMatch_i] ?? NULL;

        if (!$forList)
            continue;

        /**
         *
         * 触发指定插件
         *
         */


            $matchValue = strtolower($forList);
            //coser github roll 等英文触发转小写

            /**
             *
             * 功能以及机器人统计
             *
             */
            $pluginsAnalysis = (int) $appManager->redisHget("plugins-analysis", $matchValue);
            $appManager->redisHset("plugins-analysis", $matchValue, $pluginsAnalysis + 1);

            /**
             *
             * 返回存在关键词的插件
             *
             */
            $nowPlugin = $allTrigger[$matchValue];

            if ($nowPlugin)
                $allPlugins[] = $nowPlugin;

    }
}

/**
 *
 * 全局触发指定插件
 *
 */
//$allPlugins[] = array("name" => "aila", "path" => "app/plugins/aila");

if (count($allPlugins) == 0) {
    /**
     *
     * 匹配不到关键词自动回复
     *
     */
    if (FRAME_ID == 70000 && APP_BOT_TYPE == 1) {
        $appManager->appSend($msg['Robot'], $msg['MsgType'], $msg['Source'], $msg['Sender'], APP_NO_KEYWORDS);
    }
    exit(1);
} else {
    /**
     *
     * 增删插件，管理员都需要向机器人发送【功能】重新注册钩子，只有命中关键词的才会运行相关插件
     *
     */
    $appManager->runPlugins($allPlugins);
    $appManager->trigger("plugin", $msg);
}