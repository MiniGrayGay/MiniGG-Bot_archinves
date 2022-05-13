<?php

/**
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class genshinUid_actions extends app
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
                $res = $this->mihoyoapi($q, $url);
                if (FRAME_ID == 70000){
                    $igs = $this->igsold($res, $msgContent);
                }else{
                    $igs = $this->igs($res, $msgContent);
                }

                if (FRAME_ID == 10000) {
                    $ret .= "[{$igs}]";
                    $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
                } elseif (FRAME_ID == 50000) {
                    if($igs == "图片绘制失败"){
                        $ret = "图片绘制失败";
                    }else{
                        $ret = json_encode($igs);
                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "image_msg";
                    }
                } elseif (in_array(FRAME_ID, array(60000, 70000))) {
                    $ret = "UID：" . $msgContent;
                    $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgUrl'] = $igs;
                    $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg,image_msg";
                } else {
                    $ret = NULL;
                }

            } else {
                $ret = $this->appCommandErrorMsg($matchValue);
            }
        } elseif (preg_match("/角色材料|武器材料/", $msgContent)) {
            strpos($msgContent, "武器") > -1 ? $matchValue = "武器" : $matchValue = "角色";
            $ret = $this->getGenshinDaily($matchValue);
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
        $url = "https://kk.igs.minigg.cn/?style=egenshin&uid=" . $msgContent;
        $response = $this->requestUrl($url, $res, array('Content-Type: application/json; charset=utf-8', 'Content-Length: ' . strlen($res)), "");
        $img = json_decode($response, true);
        if ($img['retcode'] == 0) {
            $image_format = 1;
            $image_info_array = array(
                array(
                    "type" => 1,
                    "size" => $img['raw']['info']['size'],
                    "width" => $img['raw']['info']['width'],
                    "height" => $img['raw']['info']['height'],
                    "url" => $img['raw']['info']['url'],
                    "md5sum" => $img['raw']['info']['md5sum'],
                )
            );
            $pic_info = array(
                array(
                    "image_format" => $image_format,
                    "image_info_array" => $image_info_array
                )
            );
            $imgurl = $pic_info;
        } else {
            $imgurl = "图片绘制失败";
        }
        return $imgurl;
    }

    function igsold($res, $msgContent)
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

    /**
     *
     * 获取每日材料
     *
     * @link https://bbs.mihoyo.com/ys/obc
     *
     * 米游社·原神-观测枢
     */
    function getGenshinDaily($msgContent = "角色")
    {
        //周一四
        $dailyInfo['自由'] = array(
            "埃洛伊 达达利亚 可莉 安柏 芭芭拉 迪奥娜 砂糖",
            "风鹰剑 松籁响起之时 辰砂之纺锤 宗室秘法录 西风秘典 绝弦 西风剑 钟剑 宗室长剑 暗巷闪光 苍翠猎弓 雪葬的星银 幽夜华尔兹 冷刃 铁影阔剑 魔导绪论 鸦羽弓 银剑 口袋魔导书 无锋剑 学徒笔记"
        );
        //蒙德
        $dailyInfo['繁荣'] = array(
            "申鹤 魈 七七 刻晴 凝光",
            "和璞鸢 斫峰之刃 白影剑 弓藏 黑岩长剑 黑岩绯玉 黑岩战弓 流月针 千岩古剑 匣里龙吟 匣里日月 暗铁剑 白缨枪 弹弓 翡玉法球"
        );
        //璃月
        $dailyInfo['浮世'] = array(
            "珊瑚宫心海 宵宫 托马",
            "雾切之回光 不灭月华 白晨之环 恶王丸 天目影打刀 证誓之明瞳"
        );
        //稻妻

        //周二五
        $dailyInfo['抗争'] = array(
            "琴 莫娜 迪卢克 班尼特 诺艾尔 雷泽 优菈",
            "天空之卷 天空之刃 天空之傲 天空之翼 终末嗟叹之诗 流浪乐章 祭礼弓 笛剑 祭礼大剑 黑剑 决斗之枪 龙脊长枪 暗巷的酒与诗 嘟嘟可故事集 降临之剑 黎明神剑 沐浴龙血的剑 讨龙英杰谭 神射手之誓 佣兵重剑 历练的猎弓 训练大剑 猎弓"
        );
        //蒙德
        $dailyInfo['勤劳'] = array(
            "枫原万叶 胡桃 甘雨 云堇 香菱 重云",
            "息灾 磐岩结绿 无工之剑 黑岩刺枪 黑岩斩刀 试作澹月 试作金珀 试作斩岩 匣里灭辰 雨裁 昭心 宗室猎枪 吃虎鱼刀 甲级宝钰 信使 以理服人 钺矛"
        );
        //璃月
        $dailyInfo['风雅'] = array(
            "荒泷一斗 神里绫人 神里绫华 九条裟罗",
            "波乱月白经津 赤角石溃杵 飞雷之弦振 桂木斩长正 掠食者 矇云之月 破魔之弓"
        );
        //稻妻

        //周三六
        $dailyInfo['诗文'] = array(
            "阿贝多 温迪 罗莎莉亚 菲谢尔 丽莎 凯亚",
            "四风原典 狼的末路 阿莫斯之弓 天空之脊 苍古自由之誓 祭礼残章 宗室大剑 西风猎弓 宗室长弓 祭礼剑 西风大剑 西风长枪 腐殖之剑 忍冬之果 风花之颂 暗巷猎手 旅行剑 白铁大剑 异世界行记 反曲弓 铁尖枪 新手长枪"
        );
        //蒙德
        $dailyInfo['黄金'] = array(
            "钟离 行秋 北斗 辛焱 烟绯",
            "尘世之锁 贯虹之槊 护摩之杖 衔珠海皇 螭骨剑 钢轮弓 千岩长枪 试作古华 试作星镰 铁蜂刺 万国诸海图谱 飞天大御剑 飞天御剑 黑缨枪"
        );
        //璃月
        $dailyInfo['天光'] = array(
            "八重神子 雷电将军 五郎 早柚",
            "薙草之稻光 冬极白星 喜多院十文字 渔获 断浪长鳍"
        );
        //稻妻

        /**
         *
         * 日 一 二 三 四 五 六
         *
         */
        $weekArr = array("日", "一", "二", "三", "四", "五", "六");
        $nowWeek = date("w");

        $msgContent == "武器" ? $retType = 1 : $retType = 0;

        $ret = "今天是【周" . $weekArr[$nowWeek] . "】";

        if ($nowWeek == 0) {
            $ret .= "所有角色武器都可以升～";
        } else {
            $ret .= "今日素材可" . ($retType == 1 ? "突破的武器" : "升天赋角色") . " 如下:\n";
            $ret .= "-----\n";

            if ($nowWeek == 1 || $nowWeek == 4) {
                $ret .= "> 自由\n";
                $ret .= $dailyInfo['自由'][$retType] . "\n";
                $ret .= "> 繁荣\n";
                $ret .= $dailyInfo['繁荣'][$retType] . "\n";
                $ret .= "> 浮世\n";
                $ret .= $dailyInfo['浮世'][$retType];
            } elseif ($nowWeek == 2 || $nowWeek == 5) {
                $ret .= "> 抗争\n";
                $ret .= $dailyInfo['抗争'][$retType] . "\n";
                $ret .= "> 勤劳\n";
                $ret .= $dailyInfo['勤劳'][$retType] . "\n";
                $ret .= "> 风雅\n";
                $ret .= $dailyInfo['风雅'][$retType];
            } elseif ($nowWeek == 3 || $nowWeek == 6) {
                $ret .= "> 诗文\n";
                $ret .= $dailyInfo['诗文'][$retType] . "\n";
                $ret .= "> 黄金\n";
                $ret .= $dailyInfo['黄金'][$retType] . "\n";
                $ret .= "> 天光\n";
                $ret .= $dailyInfo['天光'][$retType];
            }
        }

        return $ret;
    }
}