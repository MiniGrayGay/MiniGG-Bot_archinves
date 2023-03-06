<?php

use Onekb\ChatGpt\ChatGpt;
use Overtrue\Pinyin\Pinyin;

class api
{
    public $redis;

    /**
     *
     * XIAOAI:返回请求API的结果
     *
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段，详见 send.php 示例
     *
     * @link https://developers.xiaoai.mi.com
     */
    public function requestApiByXIAOAI($newMsg, $msgExtArr = array())
    {
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
        }
        $msgOrigMsg = $newData['msgOrigMsg'];
        $extMsgType = $newData['msgType'];

        $reqRet = json_encode(
            array(
                'version' => '1.0',
                'session_sttributes' => array(),
                'response' => array(
                    'open_mic' => true,
                    'to_speak' => array(
                        'type' => 0,
                        'text' => $newMsg
                    )
                ),
                'is_session_end' => false
            )
        );

        echo $reqRet;

        if (APP_DEBUG)
            appDebug("output", $newMsg . "\n\n" . $reqRet);
    }

    /**
     *
     * MPQ:返回请求API的结果
     *
     * @param string $newMsg 回复内容
     *
     * @link https://www.yuque.com/mpq/docs
     */
    public function requestApiByMPQ($newMsg)
    {
        $newMsg = str_replace("\n", "\\n", $newMsg);
        $newMsg = str_replace("\u", "\\u", $newMsg);

        $reqUrl = APP_ORIGIN . "/?API=" . urlencode($newMsg);

        /*
        $reqUrl = str_replace("%3C", "<", $reqUrl);
        $reqUrl = str_replace("%3E", ">", $reqUrl);
        $reqUrl = str_replace("%27", "'", $reqUrl);
        $reqUrl = str_replace("%27", "'", $reqUrl);
        $reqUrl = str_replace("%28", "(", $reqUrl);
        $reqUrl = str_replace("%29", ")", $reqUrl);
        $reqUrl = str_replace("%5b", "[", $reqUrl);
        $reqUrl = str_replace("%5d", "]", $reqUrl);
        $reqUrl = str_replace("%40", "@", $reqUrl);
        $reqUrl = str_replace("%3D", "=", $reqUrl);
        $reqUrl = str_replace("%3A", ":", $reqUrl);
        $reqUrl = str_replace("%2F", "/", $reqUrl);
        $reqUrl = str_replace("%2C", ",", $reqUrl);
        $reqUrl = str_replace("%3F", "?", $reqUrl);
        */
        $reqUrl = str_replace("+", "%20", $reqUrl);

        $reqRet = $this->requestUrl($reqUrl);
        $resJson = json_decode($reqRet);
        $resData = base64_decode($resJson->Data);

        if (APP_DEBUG)
            appDebug("output", $reqUrl . "\n\n" . $reqRet);

        return $resData;
    }

    /**
     *
     * 可爱猫:未死鲤鱼:返回请求API的结果
     *
     * 支持类型 at_msg、json_msg
     *
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段
     *
     * @link http://www.keaimao.com.cn/forum.php
     */
    public function requestApiByWSLY($newMsg, $msgExtArr = array())
    {
        $newMsg = str_replace("\n", "\\n", $newMsg);
        $newMsg = str_replace("\u", "\\u", $newMsg);

        /*
        $newMsg = str_replace("%5b", "[", $newMsg);
        $newMsg = str_replace("%5d", "]", $newMsg);
        $newMsg = str_replace("%40", "@", $newMsg);
        $newMsg = str_replace("%3D", "=", $newMsg);
        */

        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
        }
        $msgOrigMsg = $newData['msgOrigMsg'];
        $extMsgType = $newData['msgType'];

        $msgRobot = $msgOrigMsg['robot_wxid'] ?? NULL;
        $msgSource = $msgOrigMsg['from_wxid'] ?? NULL;

        if ($extMsgType == "json_msg") {
            $postArr = json_decode($newMsg, true);
        } else {
            if ($extMsgType == "at_msg") {
                $sendType = 102;

                $msgAtWxid = $msgOrigMsg['final_from_wxid'];
                $msgAtName = urldecode($msgOrigMsg['final_from_name']);
            } else {
                $sendType = 100;

                $msgAtWxid = NULL;
                $msgAtName = NULL;
            }

            $postArr = array();
            $postArr['type'] = $sendType;
            $postArr['robot_wxid'] = $msgRobot;
            $postArr['to_wxid'] = $msgSource;
            $postArr['at_name'] = $msgAtName;
            $postArr['at_wxid'] = $msgAtWxid;
            $postArr['msg'] = urlencode($newMsg);
        }

        $botInfo = APP_BOT_INFO['WSLY'];
        $postArr['key'] = $botInfo['accessToken'];
        $postData = json_encode(
            array(
                "data" => json_encode($postArr, JSON_UNESCAPED_UNICODE)
            )
        );

        $reqRet = $this->requestUrl(
            APP_ORIGIN,
            $postData,
            array(
                "Content-Type: application/json"
            )
        );

        if (APP_DEBUG)
            appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * NOKNOK:返回请求API的结果
     *
     * 支持类型 at_msg、image_msg、markdown_msg、reply_msg
     *
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段
     *
     * @link https://bot-docs.github.io/pages/events/1_callback.html
     */
    public function requestApiByNOKNOK($newMsg, $msgExtArr = array())
    {
        $reqUrl = APP_ORIGIN . "/api/v1/SendGroupMessage";
        //$reportUrl = APP_ORIGIN . "/api/v1/CommReport";

        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
            $msgOrigMsg = $newData['msgOrigMsg']['data'][0];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
            $msgOrigMsg = $newData['msgOrigMsg'];
        }
        $extMsgType = $newData['msgType'];

        $msgGuildId = $msgOrigMsg['gid'] ?? 0;
        $msgChannelId = $msgOrigMsg['target_id'] ?? 0;
        $msgSenderUid = $msgOrigMsg['sender_uid'] ?? 0;
        $msgId = $msgOrigMsg['msg_id'] ?? 0;
        $msgTs = $msgOrigMsg['ts'] ?? 0;
        $msgNonce = $msgOrigMsg['nonce'] ?? NULL;

        $postBody = array(
            "content" => $newMsg,
        );

        $botInfo = APP_BOT_INFO['NOKNOK'];

        if ($extMsgType) {
            if ($extMsgType == "markdown_msg") {
                $l2_type = 8;
                $l3_types = array();

                $newMsg = str_replace("\n", "\n\n", $newMsg);
            } elseif ($extMsgType == "image_msg") {
                $l2_type = 3;
                $l3_types = array();

                $postBody = array(
                    "pic_info" => json_decode($newMsg)
                );
            } elseif ($extMsgType == "reply_msg") {
                $l2_type = 1;
                $l3_types = array(1);

                $msgContent = $msgOrigMsg['body']['content'] ?? NULL;
                $oldMsg = substr($msgContent, strpos($msgContent, ")") + 1, strlen($msgContent));

                $newExtData = array(
                    "content" => "@" . $botInfo['name'] . " " . $oldMsg,
                    "uid_replied" => $msgSenderUid,
                    "msg_seq" => explode("_", $msgId)[2],
                    "msg_id" => "",
                );
            } elseif ($extMsgType == "at_msg") {
                $l2_type = 1;
                $l3_types = array(3);

                $msgAtNokNok = $newData['msgAtNokNok'] ?? NULL;
                $msgAtNokNok ? $newExtData = $msgAtNokNok : $newExtData = array("at_type" => 1, "at_uid_list" => array($msgSenderUid));
            }

            if ($newExtData)
                $postBody[$extMsgType] = $newExtData;
        } else {
            $l2_type = 1;
            $l3_types = array();
        }

        $postArr = array(
            "gid" => $msgGuildId,
            "target_id" => $msgChannelId,
            "ts" => $msgTs,
            "nonce" => $msgNonce
        );

        $postArr['l2_type'] = $l2_type;
        $postArr['l3_types'] = $l3_types;
        $postArr['body'] = $postBody;
        $postData = json_encode($postArr);

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            array(
                "Content-Type: application/json",
                "Authorization: " . $botInfo['accessToken']
            )
        );

        /*
        $reportArr = array(
        "ts" => $msgTs,
        "nonce" => $msgNonce,
        "data_list" => array(
        "oper_id" => $botInfo['oper_id'],
        "gid" => $msgGuildId,
        "target_id" => $msgChannelId,
        "to_uid" => $msgSenderUid,
        "scope" => "channel"
        )
        );
        $reportData = json_encode($reportArr);
        $reportRet = $this->requestUrl(
        $reportUrl,
        $reportData,
        array(
        "Content-Type: application/json",
        "Authorization: " . $botInfo['accessToken']
        )
        );
        */

        if (APP_DEBUG)
            appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * QQ频道:返回请求API的结果
     *
     * 支持类型 at_msg、image_msg、json_msg、reply_msg、xml_msg
     *
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段
     *
     * @link https://github.com/Mrs4s/go-cqhttp
     */
    public function requestApiByQQChannel_1($newMsg, $msgExtArr = array())
    {
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
        }
        $msgOrigMsg = $newData['msgOrigMsg'];
        $extMsgType = $newData['msgType'];

        $msgType = $msgOrigMsg['message_type'] ?? NULL;
        $msgId = $msgOrigMsg['message_id'] ?? 0;
        $msgSender = $msgOrigMsg['sender']['user_id'] ?? 0;

        if ($msgType == "guild") {
            $msgGuildId = $msgOrigMsg['guild_id'] ?? 0;
            $msgChannelId = $msgOrigMsg['channel_id'] ?? 0;

            $reqUrl = APP_ORIGIN . "/send_guild_channel_msg";
        } else {
            $msgGroupId = $msgOrigMsg['group_id'] ?? 0;

            $reqUrl = APP_ORIGIN . "/send_msg";
        }

        $postMsg = [];

        if (strpos($extMsgType, "reply_msg") > -1) {
            $postMsg[] = array(
                "type" => "reply",
                "data" => array(
                    "id" => $msgId
                )
            );
        }

        if (strpos($extMsgType, "at_msg") > -1) {
            $msgAtQQChannel = $newData['msgAtQQChannel'] ?? array();
            $msgAtType = $msgAtQQChannel['at_type'];

            $postMsg[] = array(
                "type" => "at",
                "data" => array(
                    "qq" => $msgAtType == 2 ? "all" : $msgSender
                )
            );

            //$postMsg = array_reverse($postMsg);
            //at 靠前
        }

        //可以叠加

        if ($extMsgType == "json_msg") {
            $postMsg[] = array(
                "type" => "json",
                "data" => array(
                    "data" => $newMsg
                )
            );
        }

        if ($extMsgType == "xml_msg") {
            $postMsg[] = array(
                "type" => "xml",
                "data" => array(
                    "data" => $newMsg
                )
            );
        }

        if ($newMsg) {
            $postMsg[] = array(
                "type" => "text",
                "data" => array(
                    "text" => $newMsg
                )
            );
        }

        if (strpos($extMsgType, "image_msg") > -1) {
            $extMsgImgUrl = $newData['msgImgUrl'] ?? NULL;

            $postMsg[] = array(
                "type" => "image",
                "data" => array(
                    "file" => $extMsgImgUrl
                )
            );
        }

        if ($msgType == "guild") {
            $postArr = array(
                "guild_id" => $msgGuildId,
                "channel_id" => $msgChannelId,
                "message" => $postMsg
            );
        } elseif ($msgType == "group") {
            $postArr = array(
                "message_type" => $msgType,
                "group_id" => $msgGroupId,
                "user_id" => $msgSender,
                "message" => $postMsg
            );
        } elseif ($msgType == "private") {
            $postArr = array(
                "message_type" => $msgType,
                "user_id" => $msgSender,
                "message" => $postMsg
            );
        }
        $postData = json_encode($postArr);

        $botInfo = APP_BOT_INFO['QQChannel'][0];

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            array(
                "Content-Type: application/json",
                "Authorization: " . $botInfo['accessToken']
            )
        );

        if (APP_DEBUG)
            appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * QQ频道:返回请求API的结果
     *
     * 支持类型 at_msg、image_file、image_msg、json_msg、markdown_msg、reply_msg
     *
     * @param string $newMsg 回复内容
     * @param array $msgExtArr 拓展字段
     *
     * @link https://q.qq.com
     */
    public function requestApiByQQChannel_2($newMsg, $msgExtArr = array())
    {
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode(json_encode($msgExtData), true) : $newData = $msgExtData;
        }
        $msgOrigMsg = $newData['msgOrigMsg'];
        $extMsgType = $newData['msgType'];

        $msgData = $msgOrigMsg['d'] ?? array();
        $msgDirect = $msgData['direct_message'] ?? false;
        $msgChannelId = $msgData['channel_id'] ?? 0;
        $msgGuildId = $msgData['guild_id'] ?? 0;
        $msgSender = $msgData['author']['id'] ?? 0;
        $msgId = $msgData['id'] ?? NULL;

        if ($msgDirect) {
            /**
             *
             * 私信
             *
             */
            $reqUrl = APP_ORIGIN . "/dms/{$msgGuildId}/messages";
        } else {
            /**
             *
             * 频道
             *
             */
            $reqUrl = APP_ORIGIN . "/channels/{$msgChannelId}/messages";
        }

        if ($extMsgType == "markdown_msg") {
            $postArr['markdown'] = json_decode($newMsg, true);
        } elseif ($extMsgType == "json_msg") {
            $postArr['ark'] = json_decode($newMsg, true);
        } elseif (strpos($extMsgType, "at_msg") > -1 || strpos($extMsgType, "reply_msg") > -1) {
            $postArr['content'] = "<@!{$msgSender}>{$newMsg}";
        } else {
            $postArr['content'] = $newMsg;
        }

        /**
         *
         * 移除这俩的 content ，用不到
         *
         */
        if (in_array($extMsgType, array("markdown_msg", "json_msg"))) {
            unset($postArr['content']);
        } elseif ($msgId) {
            $postArr['msg_id'] = $msgId;
        }

        if ($msgId) {
            if (strpos($extMsgType, "reply_msg") > -1) {
                $postArr['message_reference'] = array(
                    "message_id" => $msgId,
                    "ignore_get_message_error" => true
                );
            }
        }

        if (strpos($extMsgType, "image_msg") > -1) {
            $extMsgImgUrl = $newData['msgImgUrl'] ?? NULL;

            $postArr['image'] = $extMsgImgUrl;
        }

        if (strpos($extMsgType, "image_file") > -1) {
            $extMsgImgFile = $newData['msgImgFile'] ?? NULL;
            $extMsgImgFileType = getimagesize($extMsgImgFile);

            switch ($extMsgImgFileType['mime']) {
                case 'image/png':
                    $extMsgImgFileType = ".png";
                    break;

                case 'image/gif':
                    $extMsgImgFileType = ".gif";
                    break;

                case 'image/jpg':
                    $extMsgImgFileType = ".jpg";
                    break;

                case 'image/jpeg':
                    $extMsgImgFileType = ".jpeg";
                    break;

                case 'image/bmp':
                    $extMsgImgFileType = ".bmp";
                    break;

                case 'image/webp':
                    $extMsgImgFileType = ".webp";
                    break;
            }

            $postHeader[] = "Content-Type: multipart/form-data";

            $postArr['file_image'] = new CURLFile($extMsgImgFile, "multipart/form-data", time() . $extMsgImgFileType);

            $postData = $postArr;
        } else {
            $postHeader[] = "Content-Type: application/json";

            $postData = json_encode($postArr);
        }

        $botInfo = APP_BOT_INFO['QQChannel'][1];

        $postHeader[] = "Authorization: Bot " . $botInfo['id'] . "." . $botInfo['accessToken'];

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            $postHeader
        );

        if (APP_DEBUG)
            appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * 处理被添加好友/进群请求
     *
     * @param string $code 0:忽略 10:同意 20:拒绝 30:单项同意
     * @param string $msg 理由
     */
    public function appHandleByMPQ($code, $msg = "")
    {
        $ret = json_encode(
            array(
                "Ret" => (string) $code,
                "Msg" => !$msg ? "" : $msg
            ),
            JSON_UNESCAPED_UNICODE
        );

        echo $ret;
    }

    /**
     *
     * 设置:消息类型
     *
     * 支持类型 api_msg、at_msg、image_file、image_msg、json_msg (ark_msg)、markdown_msg、reply_msg
     *
     * @param string $msgType 支持类型
     *
     */
    public function appSetMsgType($msgType = NULL)
    {
        $msgGc = $GLOBALS['msgGc'];

        if ($msgGc) {
            $GLOBALS['msgExt'][$msgGc]['msgType'] = $msgType;
        }
    }

    /**
     *
     * 发送:信息
     *
     */
    public function appSend($msgRobot, $msgType, $msgSource, $msgSender, $msgContent, $msgExtArr = array())
    {
        if (!$msgContent)
            return;

        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode($msgExtData, true) : $newData = $msgExtData;
        }
        //$msgOrigMsg = $newData['msgOrigMsg'];
        $extMsgType = $newData['msgType'];

        if (strpos($extMsgType, "at_msg") > -1) {
            $msgContent = "\n{$msgContent}";
        }

        $msgContent = str_replace("[CONFIG_ADMIN]", implode(",", CONFIG_ADMIN), $msgContent);
        $msgContent = str_replace("[CONFIG_ROBOT]", implode(",", CONFIG_ROBOT), $msgContent);
        $msgContent = str_replace("[CONFIG_VERSION]", CONFIG_VERSION, $msgContent);
        $msgContent = str_replace("[TIME_T]", date("Y-m-d H:i:s a", TIME_T), $msgContent);
        $msgContent = str_replace("[PUSH_MSG_IMG]", "\n\n", $msgContent);
        //只回复图片的占位符

        if (FRAME_ID == 2500) {
            $this->requestApiByXIAOAI($msgContent, $msgExtArr);
        } elseif (FRAME_ID == 10000) {
            if ($extMsgType == "api_msg") {
                $newData = $msgContent;
            } elseif ($extMsgType == "json_msg") {
                $newData = "Api_SendAppMsg('{$msgRobot}',{$msgType},'{$msgSource}','{$msgSender}','{$msgContent}')";
            } elseif ($extMsgType == "xml_msg") {
                $newData = "Api_SendXml('{$msgRobot}',{$msgType},'{$msgSource}','{$msgSender}','{$msgContent}',0)";
            } else {
                if (strpos($extMsgType, "at_msg") > -1) {
                    $msgContent = "[@[QQ]]{$msgContent}";
                }

                $newData = "Api_SendMsg('{$msgRobot}',{$msgType},0,'{$msgSource}','{$msgSender}','{$msgContent}')";
            }

            $this->requestApiByMPQ($newData);
        } elseif (FRAME_ID == 20000) {
            $wechatTopic = APP_WECHAT_TOPIC;
            if ($wechatTopic) {
                $ret = $msgContent;
                $ret .= "\n----\n";
                $ret .= $wechatTopic;
            } else {
                $ret = $msgContent;
            }

            $this->requestApiByWSLY($ret, $msgExtArr);
        } elseif (FRAME_ID == 50000) {
            $this->requestApiByNOKNOK($msgContent, $msgExtArr);
        } elseif (FRAME_ID == 60000) {
            $this->requestApiByQQChannel_1($msgContent, $msgExtArr);
        } elseif (FRAME_ID == 70000) {
            $ret = $this->requestApiByQQChannel_2($msgContent, $msgExtArr);
            $resJson = json_decode($ret);
            $resCode = $resJson->code ?? 0;

            if ($resCode > 0) {
                /**
                 *
                 * 把报错都打印出来，方便处理
                 *
                 */
                appDebug("output", json_encode($newData) . "\n\n" . $ret);

                if ($resCode != 304023) {
                    $this->appSetMsgType("at_msg");

                    $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgUrl'] = NULL;
                    $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgFile'] = NULL;

                    $resMessage = $resJson->message;

                    $ret = "\n请求错误，请复制此消息反馈给开发者\n";
                    $ret .= "-----\n";
                    $ret .= "错误代码:{$resCode}\n";
                    $ret .= "错误信息:{$resMessage}";

                    sleep(1);

                    $this->requestApiByQQChannel_2($ret, $msgExtArr);

                    return;
                }
            }
        } else {
            return;
        }
    }

    ##### 以上为框架的出口，可以自行拓展

    /**
     *
     * 命令行错误示例
     *
     * @param string $keywords 关键词
     * @return string 返回错误时的示例
     */
    public function appCommandErrorMsg($keywords)
    {
        $this->appSetMsgType("at_msg");

        $keywordsInfo = $this->redisHget("plugins-keywordsInfo", $keywords) ?? "未知错误";

        return "参数有误，" . $keywordsInfo;
    }

    /**
     *
     * 随机字符串
     *
     * @param string $len 长度
     * @param string $chars 填充字符串
     * @return string 返回生成的随机字符串
     */
    public function appGetRandomString($len = 6, $chars = NULL)
    {
        if (is_null($chars)) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        mt_srand(10000000 * (float) microtime());
        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

    /**
     *
     * 图片、文本检测，输出的时候可能会包括用户输入内容的时候建议加入检测
     *
     * @param string $data 需要检测的内容
     * @param string $checkType MsgSecCheck 或 MediaCheckAsync
     * @param int $dataType $checkTyp 为 MediaCheckAsync 时，1:音频 2:图片
     * @return bool true:可能存在违规内容 false:正常
     *
     * @link https://q.qq.com/wiki/develop/miniprogram/server/open_port/port_safe.html
     */
    public function appMsgCheckData($data, $checkType = "MsgSecCheck", $dataType = 2)
    {
        if ($checkType === "MsgSecCheck") {
            $url = "https://api.q.qq.com/api/json/security/MsgSecCheck?access_token=" . QQ_ACCESS_TOKEN;
        }elseif ($checkType === "ImgSecCheck") {
            $url = "https://api.q.qq.com/api/json/security/ImgSecCheck?access_token=" . QQ_ACCESS_TOKEN;
        }elseif ($checkType === "MediaCheck" || $checkType === "MediaCheckAsync") {
            $url = "https://api.q.qq.com/api/json/security/MediaCheckAsync?access_token=" . QQ_ACCESS_TOKEN;
        }else{
            return "Type Error";
        }
        return false;
    }

    /**
     *
     * desription 判断是否gif动画
     *
     * @param string $imgPath 图片路径
     * @return bool true:是 false:否
     */
    public function appMsgCheckGif($imgPath)
    {
        $fp = fopen($imgPath, 'rb');
        $image_head = fread($fp, 1024);
        fclose($fp);

        return !preg_match("/" . chr(0x21) . chr(0xff) . chr(0x0b) . 'NETSCAPE2.0' . "/", $image_head);
    }

    /**
     *
     * desription 压缩图片
     *
     * @param string $imgPath 图片路径
     * @param string $imgDist 压缩后保存路径
     *
     * @link http://www.yuqingqi.com/phpjiaocheng/994.html
     */
    public function appMsgImgNewSize($imgPath, $imgDist)
    {
        list($width, $height, $type) = getimagesize($imgPath);
        $newWidth = $width;
        $newHeight = $height;

        switch ($type) {
            case 1:
                $giftype = $this->appMsgCheckGif($imgPath);

                if ($giftype) {
                    header('Content-Type:image/gif');
                    $image_wp = imagecreatetruecolor($newWidth, $newHeight);
                    $image = imagecreatefromgif($imgPath);
                    imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagejpeg($image_wp, $imgDist, 80);
                    imagedestroy($image_wp);
                }

                break;

            case 2:
                header('Content-Type:image/jpeg');
                $image_wp = imagecreatetruecolor($newWidth, $newHeight);
                $image = imagecreatefromjpeg($imgPath);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($image_wp, $imgDist, 80);
                imagedestroy($image_wp);

                break;

            case 3:
                header('Content-Type:image/png');
                $image_wp = imagecreatetruecolor($newWidth, $newHeight);
                $image = imagecreatefrompng($imgPath);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($image_wp, $imgDist, 80);
                imagedestroy($image_wp);

                break;
        }
    }

    /**
     *
     * 十六进制:编码
     *
     */
    public function appStrToHex($str)
    {
        $hex = "";
        for ($i = 0; $i < strlen($str); $i++)
            $hex .= dechex(ord($str[$i]));
        $hex = strtoupper($hex);

        return $hex;
    }

    /**
     *
     * 十六进制:解码
     *
     */
    public function appHexToStr($hex)
    {
        $str = "";
        for ($i = 0; $i < strlen($hex) - 1; $i += 2)
            $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));

        return $str;
    }

    /**
     *
     * 取中间
     *
     */
    public function appGetSubstr($str, $leftStr, $rightStr)
    {
        $left = strpos($str, $leftStr);
        //echo '左边:'.$left;
        $right = strpos($str, $rightStr, $left);
        //echo '<br>右边:'.$right;
        if ($left < 0 or $right < $left)
            return '';

        return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
    }

    /**
     *
     * 计算 Gtk
     *
     */
    public function appGetGtk($str)
    {
        //$str = $cookie['skey'];
        $hash = 5381;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $h = ($hash << 5) + $this->utf8Unicode($str[$i]);
            $hash += $h;
        }

        return $hash & 0x7fffffff;
    }

    /**
     *
     * 计算 Gtk:utf8Unicode
     *
     */
    public function utf8Unicode($c)
    {
        switch (strlen($c)) {
            case 1:
                return ord($c);
            case 2:
                $n = (ord($c[0]) & 0x3f) << 6;
                $n += ord($c[1]) & 0x3f;
                return $n;
            case 3:
                $n = (ord($c[0]) & 0x1f) << 12;
                $n += (ord($c[1]) & 0x3f) << 6;
                $n += ord($c[2]) & 0x3f;
                return $n;
            case 4:
                $n = (ord($c[0]) & 0x0f) << 18;
                $n += (ord($c[1]) & 0x3f) << 12;
                $n += (ord($c[2]) & 0x3f) << 6;
                $n += ord($c[3]) & 0x3f;
                return $n;
        }
    }

    /**
     *
     * redis 删除
     *
     */
    public function redisDel($redisKey, $isMd5 = false)
    {
        return $this->redis->DEL($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis 是否存在
     *
     */
    public function redisExists($redisKey, $isMd5 = false)
    {
        return $this->redis->EXISTS($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis 获取
     *
     */
    public function redisGet($redisKey, $isMd5 = false)
    {
        $redisValue = $this->redis->GET($isMd5 ? md5($redisKey) : $redisKey);
        $resJson = json_decode($redisValue, true);

        return is_array($resJson) ? $resJson : $redisValue;
    }

    /**
     *
     * redis 匹配到的
     *
     */
    public function redisKeys($redisKey, $isMd5 = false)
    {
        return $this->redis->KEYS($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis 添加/修改
     *
     */
    public function redisSet($redisKey, $redisValue, $expireTime = NULL, $isMd5 = false)
    {
        $isMd5 ? $newRedisKey = md5($redisKey) : $newRedisKey = $redisKey;

        $this->redis->SET($newRedisKey, is_array($redisValue) ? json_encode($redisValue) : $redisValue);

        if ($expireTime) {
            $this->redis->EXPIRE($newRedisKey, $expireTime);
        }
    }

    /**
     *
     * redis 剩余时间
     *
     */
    public function redisTtl($redisKey, $isMd5 = false)
    {
        return $this->redis->TTL($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis db 选择
     *
     */
    public function redisSelect($dbIndex)
    {
        $this->redis->SELECT($dbIndex);
    }

    /**
     *
     * redis:Hdel 删除
     *
     */
    public function redisHdel($redisKey, $redisField, $isMd5 = false)
    {
        return $this->redis->HDEL($isMd5 ? md5($redisKey) : $redisKey, $isMd5 ? md5($redisField) : $redisField);
    }

    /**
     *
     * redis:Hexists 是否存在
     *
     */
    public function redisHexists($redisKey, $redisField, $isMd5 = false)
    {
        return $this->redis->HEXISTS($isMd5 ? md5($redisKey) : $redisKey, $isMd5 ? md5($redisField) : $redisField);
    }

    /**
     *
     * redis:Hget 获取
     *
     */
    public function redisHget($redisKey, $redisField, $isMd5 = false)
    {
        $redisValue = $this->redis->HGET($isMd5 ? md5($redisKey) : $redisKey, $isMd5 ? md5($redisField) : $redisField);
        $resJson = json_decode($redisValue, true);

        return is_array($resJson) ? $resJson : $redisValue;
    }

    /**
     *
     * redis:Hkeys 获取 key
     *
     */
    public function redisHkeys($redisKey, $isMd5 = false)
    {
        return $this->redis->HKEYS($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis:Hgetall 获取 key、value
     *
     */
    public function redisHgetall($redisKey, $isMd5 = false)
    {
        return $this->redis->HGETALL($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis:Hset 设置
     *
     */
    public function redisHset($redisKey, $redisField, $redisValue, $expireTime = NULL, $isMd5 = false)
    {
        $isMd5 ? $newRedisKey = md5($redisKey) : $newRedisKey = $redisKey;
        $isMd5 ? $newRedisField = md5($redisField) : $newRedisField = $redisField;

        $this->redis->HSET($newRedisKey, $newRedisField, is_array($redisValue) ? json_encode($redisValue) : $redisValue);

        if ($expireTime) {
            $this->redis->EXPIRE($newRedisKey, $expireTime);
        }
    }

    /**
     *
     * 网页访问，301、302 返回 User-Agent
     *
     */
    public function requestUrl($url, $postData = "", $headers = array(DEFAULT_UA), $cookies = "", $proxy = "")
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($postData && !strpos($postData, "getHeaders")) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        if ($cookies) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookies);
        }

        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

        $resData = curl_exec($ch);
        $resHeaders = curl_getinfo($ch);

        if (strpos($postData, "getHeaders") > -1 && $resHeaders['http_code'] != 200) {
            $resData = $resHeaders;
        }

        curl_close($ch);

        return $resData;
    }
}