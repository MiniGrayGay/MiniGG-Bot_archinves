<?php

/**
 *
 * 这是一个示例插件
 *
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 *
 */
class peep_actions extends app
{
    function __construct(&$appManager)
    {
        //注册这个插件
        //第一个参数是钩子的名称
        //第二个参数是appManager的引用
        //第三个是插件所执行的方法
        $appManager->register('plugin', $this, 'EventFun');
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

        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = NULL;
        $msgContent = str_replace(" ", "", $msgContent);

        if (in_array($msgSender, CONFIG_ADMIN)) {
            if ($msgContent == "谁在窥屏") {
                $ret = $this->addPeepInfo($msgSource, $msgRobot);
            } elseif ($msgContent == "查窥屏") {
                $ret = $this->getPeepInfo($msgSource) ?? APP_INFO['codeInfo'][1002];
            }
        }
        //管理员

        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }

    /**
     *
     * 谁在窥屏,记录
     *
     */
    function addPeepInfo($msgSource, $msgRobot)
    {
        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "json_msg";

        $title = "谁在窥屏";
        $desc = "请在5秒后发送【查窥屏】，查看本群潜水怪~";
        $openId = md5($msgSource);
        //$img = "https://static01.imgkr.com/temp/523345e5ae974d2c8140154e22276102.jpg";
        $img = "https://ae02.alicdn.com/kf/Hb1faa1812ed946c29083fda329a54008k.png";
        $preview = $this->appGetShortUrl(APP_API_ROBOT . "?type=peep&openId={$openId}&img=" . urlencode($img) . "&aid=0&t=" . TIME_T);
        $url = $this->appGetShortUrl("https://www.baidu.com");

        if (in_array(FRAME_ID, array(10000, 60000))) {
            $appMsg = array(
                "app" => APP_MSG_NAME,
                "config" => array(
                    "autosize" => true,
                    //"ctime" => 1637560598,
                    "forward" => true,
                    //"token" => "1a911f1d0ac44e9b53cc4fd627172d43",
                    "type" => "normal"
                ),
                "desc" => APP_DESC,
                "extra" => array(
                    "app_type" => APP_MSG_TYPE,
                    "appid" => APP_MSG_ID
                ),
                "meta" => array(
                    APP_VIEW => array(
                        "action" => "",
                        "android_pkg_name" => "",
                        "app_type" => APP_MSG_TYPE,
                        "appid" => APP_MSG_ID,
                        "desc" => $desc,
                        "jumpUrl" => $url,
                        "preview" => $preview,
                        "source_icon" => "",
                        "source_url" => "",
                        "tag" => APP_MSG_TAG,
                        "title" => $title
                    )
                ),
                "prompt" => "[分享] " . $title,
                "ver" => "0.0.0.1",
                "view" => APP_VIEW
            );
        } elseif (FRAME_ID == 20000) {
            $appMsg = array(
                "type" => 107,
                "msg" => array(
                    "title" => $title,
                    "text" => $desc,
                    "url" => $url,
                    "pic" => $preview
                ),
                "to_wxid" => $msgSource,
                "robot_wxid" => $msgRobot
            );
        } elseif (in_array(FRAME_ID, array(60000, 70000))) {
            $appMsg = array(
                "template_id" => 24,
                "kv" => array(
                    array(
                        "key" => "#TITLE#",
                        "value" => $title
                    ),
                    array(
                        "key" => "#METADESC#",
                        "value" => $desc
                    ),
                    array(
                        "key" => "#DESC#",
                        "value" => $title
                    ),
                    array(
                        "key" => "#PROMPT#",
                        "value" => $title
                    ),
                    array(
                        "key" => "#IMG#",
                        "value" => $preview
                    ),
                    array(
                        "key" => "#LINK#",
                        "value" => $url
                    ),
                    array(
                        "key" => "#SUBTITLE#",
                        "value" => APP_MSG_TAG
                    )
                ),
            );
        } else {
            return;
        }

        return $appMsg ? json_encode($appMsg) : NULL;
    }

    /**
     *
     * 谁在窥屏,获取
     *
     */
    function getPeepInfo($gc, $len = 20)
    {
        $openId = md5($gc);

        $reqRet = $this->requestUrl(APP_API_ROBOT . "?type=peep&openId={$openId}&aid=1");
        $resJson = json_decode($reqRet);
        $resResult = $resJson->data->result;
        $resArr = $resResult->rows ?? NULL;
        $resArrNum = count($resArr);

        if (!$resArr) return;

        $resInterval = $resResult->interval;
        $resArrNum < 20 ? $resNum = $resArrNum : $resNum = $len;

        $ret = "窥屏次数   IP      归属地\n";
        $ret .= "-----\n";
        for ($peep_i = 0; $peep_i < $resNum; $peep_i++) {
            $forList = $resArr[$peep_i];

            $ip = $forList->ip;
            $addr = $forList->addr;
            $times = $forList->times;

            $ret .= "{$times}次 {$ip} {$addr}\n";
        }
        $ret .= "-----\n";
        $ret .= "{$resInterval}分钟内，大约有【{$resArrNum}】人在窥屏。更新潜水列表请发送【谁在窥屏】~";

        return $ret;
    }
}
