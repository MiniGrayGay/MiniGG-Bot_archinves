<?php

/**
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class genshinuid_actions extends app
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
        if (in_array($msgSource, APP_SPECIAL_GROUP)) {
            return;
        }
        //特殊群
        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
        $msgContent = str_replace(" ", "", $msgContent);
        if (preg_match("/查询/i", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);
            if (strlen($msgContent) == 9 && (substr($msgContent, 0, 1) == "1" || substr($msgContent, 0, 1) == "2" || substr($msgContent, 0, 1) == "5")) {
                if (substr($msgContent, 0, 1) == "5") {
                    $area = "cn_qd01";
                } else {
                    $area = "cn_gf01";
                }
                $q = "role_id=" . $msgContent . "&server=" . $area;
                $url = "https://api-takumi-record.mihoyo.com/game_record/app/genshin/api/index?" . $q;
                //游戏uid
                $res = $this->mihoyoapi($q, $url);
                $imgurl = $this->igs($res, $msgContent);
                //我也不知道为什么QQ频道机器人 at_msg、文本、image_msg 一起发会丢失一个 \n
                $ret = "UID：" . $msgContent;
                $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgUrl'] = $imgurl;
                $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg,image_msg";
            } else {
                $ret = $this->appCommandErrorMsg($matchValue);
            }
        } elseif (preg_match("/材料/", $msgContent)) {
            $ret = $this->getGenshinDaily();
        }
        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }

    /**
     *
     * 获取信息
     *
     */
    function ckget()
    {
        // Cookies池
        $cks[] = "";
        $cks[] = "";
        $ck = $cks[array_rand($cks)];
        return $ck;
    }

    function mihoyoapi($q, $url)
    {
        //加签DS，查询米哈游API
        $s = "xV8v4Qu54lUKrEYFZkJhB8cuOh9Asafs";
        $t = time();
        $r = rand(100001, 199999);
        $c = md5("salt=" . $s . "&t=" . $t . "&r=" . $r . "&b=&q=" . $q);
        $ds = $t . "," . $r . "," . $c;
        $headers[] = 'Content-Type:' . 'application/x-www-form-urlencoded; charset=UTF-8';
        $headers[] = 'DS:' . $ds;
        $headers[] = 'x-rpc-app_version: 2.11.1';
        $headers[] = 'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) miHoYoBBS/2.11.1';
        $headers[] = 'x-rpc-client_type: 5';
        $headers[] = 'Referer: https://webstatic.mihoyo.com/';
        return $this->requestUrl($url, "", $headers, $this->ckget());
    }

    function igs($res, $msgContent)
    {
        $url = "https://yuanshen.minigg.cn/generator/user_info?style=egenshin&uid=" . $msgContent . "&nickname=%E6%B4%BE%E8%92%99%E7%9A%84%E7%99%BE%E5%AE%9D%E7%AE%B1";
        $response = $this->requestUrl($url, $res, array('Content-Type: application/json; charset=utf-8', 'Content-Length: ' . strlen($res)), "");
        $img = json_decode($response, true);
        if ($img['retcode'] == 0) {
            $imgurl = "https://yuanshen.minigg.cn" . $img['url'];
        } else {
            $imgurl = "图片绘制失败";
        }
        return $imgurl;
    }

    function getGenshinDaily()
    {
        $dailyInfo['自由'] = "达达利亚、可莉、埃洛伊、安柏、砂糖、芭芭拉、迪奥娜";
        //蒙德
        $dailyInfo['繁荣'] = "七七、刻晴、魈、凝光";
        //璃月
        $dailyInfo['浮世'] = "宵宫、珊瑚宫心海、托马";
        //稻妻
        $dailyInfo['抗争'] = "琴、莫娜、迪卢克、优菈、班尼特、诺艾尔、雷泽";
        //蒙德
        $dailyInfo['勤劳'] = "甘雨、胡桃、枫原万叶、重云、香菱";
        //璃月
        $dailyInfo['风雅'] = "神里凌华、九条娑罗";
        //稻妻
        $dailyInfo['诗文'] = "温迪、阿贝多、丽莎、凯亚、菲谢尔、罗莎莉亚";
        //蒙德
        $dailyInfo['黄金'] = "钟离、辛焱、北斗、行秋、烟绯";
        //璃月
        $dailyInfo['天光'] = "雷电将军、早柚";
        //稻妻
        /**
         *
         * 日 一 二 三 四 五 六
         *
         */
        $weekArr = array("日", "一", "二", "三", "四", "五", "六");
        $nowWeek = date("w");
        $ret = "今天是【周" . $weekArr[$nowWeek] . "】";
        if ($nowWeek == 0) {
            $ret .= "所有角色都可以升～";
        } else {
            $ret .= "今日素材可升天赋角色 如下:\n";
            $ret .= "----------------\n";
            if ($nowWeek == 1 || $nowWeek == 4) {
                $ret .= "> 自由\n";
                $ret .= $dailyInfo['自由'] . "\n\n";
                $ret .= "> 繁荣\n";
                $ret .= $dailyInfo['繁荣'] . "\n\n";
                $ret .= "> 浮世\n";
                $ret .= $dailyInfo['浮世'] . "\n\n";
            } elseif ($nowWeek == 2 || $nowWeek == 5) {
                $ret .= "> 抗争\n";
                $ret .= $dailyInfo['抗争'] . "\n\n";
                $ret .= "> 勤劳\n";
                $ret .= $dailyInfo['勤劳'] . "\n\n";
                $ret .= "> 风雅\n";
                $ret .= $dailyInfo['风雅'] . "\n\n";
            } elseif ($nowWeek == 3 || $nowWeek == 6) {
                $ret .= "> 诗文\n";
                $ret .= $dailyInfo['诗文'] . "\n\n";
                $ret .= "> 黄金\n";
                $ret .= $dailyInfo['黄金'] . "\n\n";
                $ret .= "> 天光\n";
                $ret .= $dailyInfo['天光'] . "\n\n";
            }
        }
        return $ret;
    }
}