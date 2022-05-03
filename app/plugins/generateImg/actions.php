<?php

/**
 * 这是一个示例插件
 *
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class generateImg_actions extends app
{
    function __construct(&$appManager)
    {
        //注册这个插件
        //第一个参数是钩子的名称
        //第二个参数是appManager的引用
        //第三个是插件所执行的方法
        $appManager->register('plugin', $this, 'EventFun');

        $this->linkRedis();
    }
    //解析函数的参数是appManager的引用

    function EventFun($msg)
    {
        $msgPort = $msg['Port'];
        //监听的服务端口，范围为 8010-8020
        $msgPid = $msg['Pid'];
        //进程ID
        $msgVer = $msg['Ver'];
        //机器人版本
        $msgId = $msg['MsgID'];
        ///信息序号
        $msgRobot = $msg['Robot'];
        //参_机器人
        $msgType = $msg['MsgType'];
        //参_信息类型
        $msgSubType = $msg['MsgSubType'];
        //参_信息子类型
        $msgSource = $msg['Source'];
        //参_信息来源
        $msgSender = $msg['Sender'];
        //参_触发对象_主动
        $msgReceiver = $msg['Receiver'];
        //参_触发对象_被动
        $msgContent = base64_decode($msg['Content']);
        //参_信息内容
        $msgOrigMsg = base64_decode($msg['OrigMsg']);
        //参_原始信息

        if (in_array($msgSource, APP_SPECIAL_GROUP)) return;
        //特殊群

        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
        //$msgContent = str_replace(" ", "", $msgContent);
        $img = NULL;

        if (preg_match("/我有个(.*?)说/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $sayType = $this->appGetSubstr($msgContent, "我有个", "说") ?? "朋友";

            if ($sayType) {
                $msgContent = str_replace($matchValue, "", $msgContent);

                if ($msgContent) {
                    if (FRAME_ID == 10000) {
                        if (strpos($msgContent, "[@")) {
                            $sayUin = $this->appGetSubstr($msgContent, "[@", "]");
                        } else {
                            $sayUin = $msgSender;
                        }

                        $avatarUrl = "https://q1.qlogo.cn/g?b=qq&nk={$sayUin}&s=100";

                        $msgContent = str_replace("[@{$sayUin}] ", "", $msgContent);
                        $msgContent = str_replace("[@{$sayUin}]", "", $msgContent);
                    } elseif (FRAME_ID == 70000) {
                        $data = json_decode($msgOrigMsg)->d;
                        $msgAt = $data->mentions ?? NULL;
                        $msgAtNum = count($msgAt);

                        if ($msgAtNum == 2) {
                            /**
                             *
                             * 公域，艾特了机器人和朋友
                             * 艾特的顺序疑似 id 升序
                             *
                             */
                            if ($msgAt[0]->bot == true) {
                                $friend = $msgAt[1];
                            } else {
                                $friend = $msgAt[0];
                            }

                            $sayUin = $friend->id;
                            $avatarUrl = $friend->avatar;
                        } else {
                            /**
                             *
                             * 私域，没艾特人的时候
                             * 公域，只艾特了机器人
                             * 艾特人数 != 2 的全部取发言人
                             *
                             */
                            $msgAuthor = $data->author;

                            $sayUin = $msgAuthor->id;
                            $avatarUrl = $msgAuthor->avatar;
                        }

                        $msgContent = str_replace("<@!{$sayUin}> ", "", $msgContent);
                        $msgContent = str_replace("<@!{$sayUin}>", "", $msgContent);
                    }

                    $img = $this->getFriendSay($msgSender, $avatarUrl, $sayType, $msgContent);

                    if ($img == -1) {
                        return;
                    } elseif ($img) {
                        //$ret = "[PUSH_MSG_IMG]";
                        $ret = "仅供娱乐，不能给他人造成麻烦哦~\n";

                        if (FRAME_ID == 10000) {
                            $ret .= "[{$img}]";

                            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
                        } elseif (in_array(FRAME_ID, array(60000, 70000))) {
                            //if (FRAME_ID == 70000) {
                            $ret = "\n" . $ret;
                            //}
                            //我也不知道为什么 QQ机器人 at_msg、文本、image_msg 一起发会丢失一个 \n

                            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgUrl'] = $img;
                            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg,image_msg";
                        } else {
                            $ret = APP_INFO['codeInfo'][1001];
                        }
                    } else {
                        $ret = APP_INFO['codeInfo'][1002];
                    }
                } else {
                    $ret = $this->appCommandErrorMsg("我有个(.*?)说");
                }
            } else {
                $ret = "该类型不允许哦，只能是\n";
                $ret .= implode("、", $s_1) . "\n";
                $ret .= implode("、", $s_2) . "\n";
                $ret .= implode("、", $s_3);
            }
        } elseif (preg_match("/鲁迅说/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);
            if ($msgContent) {
                $img = $this->getLuXunSay($msgSender, $msgContent);
                if ($img == -1) {
                    return;
                } elseif ($img) {
                    //$ret = "[PUSH_MSG_IMG]";
                    $ret = "仅供娱乐，不能给他人造成麻烦哦~\n";

                    if (FRAME_ID == 10000) {
                        $ret .= "[{$img}]";

                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
                    } elseif (in_array(FRAME_ID, array(60000, 70000))) {
                        //if (FRAME_ID == 70000) {
                        $ret = "\n" . $ret;
                        //}
                        //我也不知道为什么 QQ机器人 at_msg、文本、image_msg 一起发会丢失一个 \n

                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgUrl'] = $img;
                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg,image_msg";
                    } else {
                        $ret = APP_INFO['codeInfo'][1001];
                    }
                } else {
                    $ret = APP_INFO['codeInfo'][1002];
                }
            } else {
                $ret = $this->appCommandErrorMsg($matchValue);
            }
        }

        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }

    /**
     *
     * 朋友说了什么
     *
     */
    function getFriendSay($msgSender, $img, $sayType = "朋友", $value = "再来点")
    {
        if ($this->appMsgCheckAsync($value, "MsgSecCheck")) return -1;
        require_once(APP_DIR_CLASS . "poster.class.php");

        $nowPath = __DIR__;

        /**
         *
         * 不用压缩图片，默认压缩
         *
         */
        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgNewSize'] = false;

        $reqRet = $this->appDownloadImg($msgSender, "onefriend", "avatar", "onefriend", $img);

        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgNewSize'] = true;

        $imgName = $reqRet['name'];
        $newPath = APP_DIR_CACHE . "onefriend";
        $newImg = $newPath . "/" . $imgName;

        $t_H = date("H", TIME_T);
        $t_i = date("i", TIME_T);

        if ($t_H >= 19 || $t_H < 6) {
            $bgColor = "black";
            $friendColor = "255,255,255";
        } else {
            $bgColor = "white";
            $friendColor = "0,0,0";
        }

        $valueLen = strlen($value); //19 个字
        if ($valueLen >= 57) {
            if ($valueLen % 3 == 0) {
                $nowValue = substr($value, 0, 57) . "...";
            } else {
                $nowValue = substr($value, 0, 56) . "...";
            }
        } else {
            $nowValue = $value;
        }
        $nowValue = str_replace("\n", " ", $nowValue);

        $config = array(
            "bg_url" => $nowPath . "/资源/bg_{$bgColor}.jpg", //宽 575 高 100
            "text" => array(
                array(
                    "text" => $sayType,
                    "left" => 117,
                    "top" => 43,
                    "fontSize" => 17,
                    "fontColor" => $friendColor,
                    "angle" => 0,
                ),
                array(
                    "text" => $t_H . ":" . $t_i,
                    "left" => 520,
                    "top" => 40,
                    "fontSize" => 10,
                    "fontColor" => "125,125,125",
                    "angle" => 0,
                ),
                array(
                    "text" => $nowValue,
                    "left" => 117,
                    "top" => 77,
                    "width" => 500,
                    "fontSize" => 14,
                    "fontColor" => "125,125,125",
                    "angle" => 0,
                )
            ),
            "image" => array(
                array(
                    "name" => "friend",
                    "url" => APP_DIR_CACHE . "avatar/" . $imgName,
                    "stream" => 0,
                    "left" => 20,
                    "top" => 13,
                    "right" => 0,
                    "bottom" => 0,
                    "width" => 75,
                    "height" => 75,
                    "radius" => 38,
                    "opacity" => 100
                )
            )
        );

        if ($t_H >= 19 || $t_H < 6) {
            array_push($config['image'], array(
                "name" => "newMsg",
                "url" => $nowPath . "/资源/newMsg.png",
                "stream" => 0,
                "left" => 533,
                "top" => 57,
                "right" => 0,
                "bottom" => 0,
                "width" => 24,
                "height" => 24,
                "radius" => 12,
                "opacity" => 100
            ));
        }

        poster::setConfig($config);
        $res = poster::make($newImg);

        if (!$res) {
            $ret = -1;
        } else {
            $ret = $this->appGetCacheImg($newImg);
        }

        return $ret;
    }

    /**
     *
     * 鲁迅说了什么
     *
     */
    function getLuXunSay($msgSender, $value = "再来点")
    {
        if ($this->appMsgCheckAsync($value, "MsgSecCheck")) return -1;

        require_once(APP_DIR_CLASS . "poster.class.php");

        $nowPath = __DIR__;

        $newPath = APP_DIR_CACHE . "luxun";
        $imgName = md5($msgSender . "鲁迅说" . TIME_T) . ".jpg";

        if (!is_dir($newPath)) {
            mkdir($newPath, 0777);
        }

        $newImg = $newPath . "/" . $imgName;

        $config = array(
            "bg_url" => $nowPath . "/资源/luxun.jpg",
            "text" => array(
                array(
                    "text" => $value,
                    "left" => 100,
                    "top" => 400,
                    "fontSize" => 17,
                    "fontColor" => "255,255,255",
                    "angle" => 0,
                ),
            )
        );

        poster::setConfig($config);
        $res = poster::make($newImg);

        if (!$res) {
            $ret = -1;
        } else {
            $ret = $this->appGetCacheImg($newImg);
        }

        return $ret;
    }
}
