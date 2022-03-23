<?php

class api
{
    /**
     *
     * XIAOAI:返回请求API的结果
     *
     */
    public function requestApiByXIAOAI($newMsg, $msgExtArr = array())
    {
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode($msgExtData, true) : $newData = $msgExtData;
        }
        $msgOrigMsg = $newData['msgOrigMsg'];
        $extMsgType = $newData['msgType'];

        $reqRet = json_encode(array(
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
        ));

        echo $reqRet;

        if (APP_INFO['debug']) appDebug("output", $newMsg . "\n\n" . $reqRet);
    }

    /**
     *
     * MPQ:返回请求API的结果
     *
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

        if (APP_INFO['debug']) appDebug("output", $reqUrl . "\n\n" . $reqRet);

        return $resData;
    }

    /**
     *
     * 可爱猫:未死鲤鱼:返回请求API的结果
     *
     *
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
            !is_array($msgExtData) ? $newData = json_decode($msgExtData, true) : $newData = $msgExtData;
        }
        $msgOrigMsg = $newData['msgOrigMsg'];
        $extMsgType = $newData['msgType'];

        $msgRobot = $msgOrigMsg['robot_wxid'] ?? NULL;
        $msgSource = $msgOrigMsg['from_wxid'] ?? NULL;

        if (strpos($newMsg, '{"') > -1) {
            $postArr = json_decode($newMsg, true);
            //好友申请之类的
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

        $botInfo = APP_INFO['botInfo']['WSLY'];
        $postArr['key'] = $botInfo['accessToken'];

        $postData = json_encode(array(
            "data" => json_encode($postArr, JSON_UNESCAPED_UNICODE)
        ));

        $reqRet = $this->requestUrl(
            APP_ORIGIN,
            $postData,
            array(
                "Content-Type: application/json"
            )
        );

        if (APP_INFO['debug']) appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * NOKNOK:返回请求API的结果
     *
     *
     */
    public function requestApiByNOKNOK($newMsg, $msgExtArr = array())
    {
        $reqUrl = APP_ORIGIN . "/api/v1/SendGroupMessage";
        $nokNokBot = APP_INFO['nokNokBot'];

        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
            $msgOrigMsg = $newData['msgOrigMsg']['data'][0];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode($msgExtData, true) : $newData = $msgExtData;
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

        if ($extMsgType) {
            if ($extMsgType == "markdown_msg") {
                $l2_type = 8;
                $l3_types = array();
            } elseif ($extMsgType == "reply_msg") {
                $l2_type = 1;
                $l3_types = array(1);

                $msgContent = $msgOrigMsg['body']['content'] ?? NULL;
                $oldMsg = substr($msgContent, strpos($msgContent, ")") + 1, strlen($msgContent));

                $newExtData = array(
                    "content" => "@" . $nokNokBot['name'] . " " . $oldMsg,
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

            if ($newExtData) $postBody[$extMsgType] = $newExtData;
            //消息类型 回复 markwodn 艾特
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

        $botInfo = APP_INFO['botInfo']['NOKNOK'];

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            array(
                "Content-Type: application/json",
                "Authorization: " . $botInfo['accessToken']
            )
        );

        if (APP_INFO['debug']) appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * QQ频道:返回请求API的结果
     *
     *
     */
    public function requestApiByQQChannel_1($newMsg, $msgExtArr = array())
    {
        $reqUrl = APP_ORIGIN . "/send_guild_channel_msg";

        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode($msgExtData, true) : $newData = $msgExtData;
        }
        $msgOrigMsg = $newData['msgOrigMsg'];
        $extMsgType = $newData['msgType'];

        $msgGuildId = $msgOrigMsg['guild_id'] ?? 0;
        $msgChannelId = $msgOrigMsg['channel_id'] ?? 0;
        $msgId = $msgOrigMsg['message_id'] ?? 0;
        $msgSender = $msgOrigMsg['sender']['user_id'] ?? 0;

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

        if (strpos($extMsgType, "image_msg") > -1) {
            $extMsgImgUrl = $newData['msgImgUrl'] ?? NULL;

            $postMsg[] = array(
                "type" => "image",
                "data" => array(
                    "file" => $extMsgImgUrl
                )
            );
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

        $postArr = array(
            "guild_id" => $msgGuildId,
            "channel_id" => $msgChannelId,
            "message" => $postMsg
        );
        $postData = json_encode($postArr);

        $botInfo = APP_INFO['botInfo']['QQChannel'][0];

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            array(
                "Content-Type: application/json",
                "Authorization: " . $botInfo['accessToken']
            )
        );

        if (APP_INFO['debug']) appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * QQ频道:返回请求API的结果
     *
     *
     */
    public function requestApiByQQChannel_2($newMsg, $msgExtArr = array())
    {
        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode($msgExtData, true) : $newData = $msgExtData;
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
            $reqUrl = APP_ORIGIN . "/dms/{$msgGuildId}/messages";
        } else {
            $reqUrl = APP_ORIGIN . "/channels/{$msgChannelId}/messages";
        }

        if ($extMsgType == "json_msg") {
            $postArr['ark'] = json_decode($newMsg, true);
        } elseif (strpos($extMsgType, "at_msg") > -1) {
            $msgAt = "<@!{$msgSender}>";
        } else {
            $msgAt = NUll;
        }

        if ($extMsgType == "json_msg") {
            $postArr['content'] = NULL;
        } else {
            $postArr['content'] = $msgAt . $newMsg;
        }

        if ($msgId) $postArr['msg_id'] = $msgId;

        if (strpos($extMsgType, "image_msg") > -1) {
            $extMsgImgUrl = $newData['msgImgUrl'] ?? NULL;

            $postArr['image'] = $extMsgImgUrl;
        }

        $postData = json_encode($postArr);

        $botInfo = APP_INFO['botInfo']['QQChannel'][1];

        $reqRet = $this->requestUrl(
            $reqUrl,
            $postData,
            array(
                "Content-Type: application/json",
                "Authorization: Bot " . $botInfo['id'] . "." . $botInfo['accessToken']
            )
        );

        if (APP_INFO['debug']) appDebug("output", $postData . "\n\n" . $reqRet);

        return $reqRet;
    }

    /**
     *
     * 处理被添加好友/进群请求
     * 0  忽略
     * 10 同意
     * 20 拒绝
     * 30 单项同意
     *
     */
    public function appHandle($ret, $msg = "")
    {
        $ret = json_encode(array(
            "Ret" => (string)$ret,
            "Msg" => !$msg ? "" : $msg
        ), JSON_UNESCAPED_UNICODE);

        echo $ret;
    }

    /**
     *
     * 错误命令行demo
     *
     */
    public function appCommandErrorMsg($keywords)
    {
        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";

        $keywordsInfo = $this->redisGet("plugins-keywordsInfo-" . urldecode($keywords)) ?? "未知错误";

        return "参数有误，" . $keywordsInfo;
    }

    /**
     *
     * 随机字符串
     *
     */
    public function getRandomString($len, $chars = null)
    {
        if (is_null($chars)) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        mt_srand(10000000 * (float)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

    /**
     *
     * 缩短链接
     *
     */
    public function appGetShortUrl($longUrl)
    {
        $reqRet = $this->requestUrl(
            APP_API_APP . "?type=getShortUrl",
            "url=" . urlencode($longUrl)
        );
        $resJson = json_decode($reqRet);
        $resData = $resJson->data;
        $ret = $resData->url ?? "缩短失败";

        return $ret;
    }

    /**
     *
     * 将图片压缩缓存到服务器本地 app/cache/:inputPath
     *
     * @param keywords 文件夹
     * @param inputPath 输入路径文件夹名
     * @param outputPath 输出路径文件夹名，和输入不同时将会替换
     * @param imgUrl 需要下载的图片链接
     * @param imgData base64的图片数据
     */
    public function appDownloadImg($msgSender, $keywords, $inputPath, $outputPath, $imgUrl = NULL, $imgData = NULL)
    {
        $imgDir = APP_DIR_CACHE . $inputPath;

        /**
         *
         * 不存在自动创建文件夹
         *
         */
        if (!is_dir($imgDir)) {
            mkdir($imgDir, 0777);

            if ($inputPath != $outputPath) {
                mkdir(APP_DIR_CACHE . $outputPath, 0777);
            }
        }

        $imgName = md5($msgSender . $keywords . TIME_T) . "_temp.jpg";
        $imgPath = $imgDir . "/" . $imgName;

        $newImgName = str_replace("_temp", "", $imgName);
        $newImgPath = str_replace("_temp", "", $imgPath);

        file_put_contents($imgPath, $imgData ? $imgData : $this->requestUrl($imgUrl));

        /**
         *
         * 不压缩的只需重命名统一格式即可
         *
         */
        if ($GLOBALS['msgExt'][FRAME_ID]['imgNewSize'] == false) {
            rename($imgPath, $newImgPath);
        } else {
            $this->imgNewSize($imgPath, $newImgPath);
            //压缩图片

            unlink($imgPath);
            //删除原件
        }

        $newImgPath = str_replace($inputPath . "/", $outputPath . "/", $newImgPath);
        //输入替换成输出

        if (strpos(APP_ORIGIN, ":") > -1 && !in_array(FRAME_ID, array(50000, 70000))) {
            $http = "http";
        } else {
            $http = "https";
        }

        $ret = $http . "://" . $_SERVER['SERVER_NAME'] . "/" . $newImgPath . "?t=" . TIME_T;

        return array(
            "name" => $newImgName,
            "url" => $ret
        );
    }

    /**
     *
     * desription 压缩图片
     * @param string $imgSrc 图片路径
     * @param string $imgDist 压缩后保存路径
     *
     * @link http://www.yuqingqi.com/phpjiaocheng/994.html
     */
    public function imgNewSize($imgSrc, $imgDist)
    {
        list($width, $height, $type) = getimagesize($imgSrc);
        $newWidth = $width;
        $newHeight = $height;

        switch ($type) {
            case 1:
                $giftype = $this->imgCheckGif($imgSrc);

                if ($giftype) {
                    header('Content-Type:image/gif');
                    $image_wp = imagecreatetruecolor($newWidth, $newHeight);
                    $image = imagecreatefromgif($imgSrc);
                    imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagejpeg($image_wp, $imgDist, 75);
                    imagedestroy($image_wp);
                }

                break;

            case 2:
                header('Content-Type:image/jpeg');
                $image_wp = imagecreatetruecolor($newWidth, $newHeight);
                $image = imagecreatefromjpeg($imgSrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($image_wp, $imgDist, 75);
                imagedestroy($image_wp);

                break;

            case 3:
                header('Content-Type:image/png');
                $image_wp = imagecreatetruecolor($newWidth, $newHeight);
                $image = imagecreatefrompng($imgSrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($image_wp, $imgDist, 75);
                imagedestroy($image_wp);

                break;
        }
    }

    /**
     *
     * desription 判断是否gif动画
     * @param string $image_file 图片路径
     * @return boolean t 是 f 否
     *
     */
    public function imgCheckGif($image_file)
    {
        $fp = fopen($image_file, 'rb');
        $image_head = fread($fp, 1024);
        fclose($fp);
        return preg_match("/" . chr(0x21) . chr(0xff) . chr(0x0b) . 'NETSCAPE2.0' . "/", $image_head) ? false : true;
    }

    /**
     *
     * 发送:信息
     *
     */
    public function appSend($msgRobot, $msgType, $msgSource, $msgSender, $msgContent, $msgExtArr = array())
    {
        if (!$msgContent) return;

        if ($msgExtArr == array()) {
            $newData = $GLOBALS['msgExt'][$GLOBALS['msgGc']];
        } else {
            $msgExtData = $msgExtArr;
            !is_array($msgExtData) ? $newData = json_decode($msgExtData, true) : $newData = $msgExtData;
        }
        //$msgOrigMsg = $newData['msgOrigMsg'];
        $extMsgType = $newData['msgType'];

        if (in_array($msgType, array(1, 100, "DIRECT_MESSAGE_CREATE")) && strpos($extMsgType, "at_msg") > -1) {
            if ($extMsgType == "at_msg,image_msg") {
                $sendMsgType = "image_msg";
            } elseif ($extMsgType == "at_msg") {
                $sendMsgType = NULL;
            }

            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = $sendMsgType;

            //移除私聊的艾特 1:MPQ，2:KAM，DIRECT_MESSAGE_CREATE:QQ 频道
        } elseif ($extMsgType == "at_msg") {
            $msgContent = "[@[QQ]]\n" . $msgContent;
            //模拟MYPCQQ的格式艾特

            if (FRAME_ID != 10000) {
                $msgContent = str_replace("[@[QQ]]", "", $msgContent);
            }
            //非MYPCQQ 和 QQ频道的移除艾特，这俩都是相同格式的收发
        }

        $msgContent = str_replace("[CONFIG_ADMIN]", implode(",", CONFIG_ADMIN), $msgContent);
        $msgContent = str_replace("[CONFIG_ROBOT]", implode(",", CONFIG_ROBOT), $msgContent);
        $msgContent = str_replace("[CONFIG_VERSION]", CONFIG_VERSION, $msgContent);
        $msgContent = str_replace("[TIME_T]", date("Y-m-d H:i:s a", TIME_T), $msgContent);
        $msgContent = str_replace("[PUSH_MSG_IMG]", "", $msgContent);
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
                $newData = "Api_SendMsg('{$msgRobot}',{$msgType},0,'{$msgSource}','{$msgSender}','{$msgContent}')";
            }

            $this->requestApiByMPQ($newData);

            //最为特殊
        } elseif (FRAME_ID == 20000) {
            $this->requestApiByWSLY($msgContent, $msgExtArr);
        } elseif (FRAME_ID == 50000) {
            $this->requestApiByNOKNOK($msgContent, $msgExtArr);
        } elseif (FRAME_ID == 60000) {
            $this->requestApiByQQChannel_1($msgContent, $msgExtArr);
        } elseif (FRAME_ID == 70000) {
            //{"code":304003,"message":"url not allowed"}
            $ret = $this->requestApiByQQChannel_2($msgContent, $msgExtArr);
            $resJson = json_decode($ret);
            $resCode = $resJson->code ?? 0;

            if ($resCode > 0 && $resCode != 304023) {
                $resMessage = $resJson->message;

                $ret = "请求错误，请复制此消息反馈给开发者\n";
                $ret .= "-----\n";
                $ret .= "错误代码:{$resCode}\n";
                $ret .= "错误信息:" . $resMessage;

                $this->requestApiByQQChannel_2($ret, $msgExtArr);

                exit(1);
            }
        } else {
            exit(1);
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
        if ($left < 0 or $right < $left) return '';

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
     * 网页访问，301、302 返回 User-Agent
     *
     */
    public function requestUrl($url, $postData = "", $headers = array(DEFAULT_UA), $cookies = "")
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        if ($cookies) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookies);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

        $resData = curl_exec($ch);
        $resHeaders = curl_getinfo($ch);

        if (in_array($resHeaders['http_code'], array(301, 302))) {
            $resData = $resHeaders;
        }

        curl_close($ch);

        return $resData;
    }

    /**
     *
     * redis 添加/修改
     *
     */
    public function redisSet($redisKey, $redisValue, $expireTime = NULL, $isMd5 = false)
    {
        $isMd5 ? $newRedisKey = md5($redisKey) : $newRedisKey = $redisKey;

        $this->redis->set($newRedisKey, is_array($redisValue) ? json_encode($redisValue) : $redisValue);

        if ($expireTime) {
            $this->redis->expire($newRedisKey, $expireTime);
        }
    }

    /**
     *
     * redis 获取
     *
     */
    public function redisGet($redisKey, $isMd5 = false)
    {
        $redisValue = $this->redis->get($isMd5 ? md5($redisKey) : $redisKey);
        $resJson = json_decode($redisValue, true);

        return is_array($resJson) ? $resJson : $redisValue;
    }

    /**
     *
     * redis 删除
     *
     */
    public function redisDel($redisKey, $isMd5 = false)
    {
        return $this->redis->del($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis 是否存在
     *
     */
    public function redisExists($redisKey, $isMd5 = false)
    {
        return $this->redis->exists($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis 剩余时间
     *
     */
    public function redisTTL($redisKey, $isMd5 = false)
    {
        return $this->redis->ttl($isMd5 ? md5($redisKey) : $redisKey);
    }

    /**
     *
     * redis 匹配到的
     *
     */
    public function redisKeys($redisKey, $isMd5 = false)
    {
        return $this->redis->keys($isMd5 ? md5($redisKey) : $redisKey);
    }
}
