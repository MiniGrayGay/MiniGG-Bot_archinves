<?php

/**
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class genshingacha_actions extends app
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
        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = NULL;
        $msgContent = str_replace(" ", "", $msgContent);
        $msgContent = strtoupper($msgContent);
        $gachaMember = "memberCode=" . $msgReceiver;
        /**
         * 抽卡接口地址
         */
        $gachaUrl = "https://gacha.minigg.cn/api/";
        $gachaKey = "SmG5TNYyHCFGjnE4";
        $ArmPrayOne = "ArmPray/PrayOne?";
        //单抽武器祈愿池
        $ArmPrayTen = "ArmPray/PrayTen?";
        //十连武器祈愿池
        $PermPrayOne = "PermPray/PrayOne?";
        //单抽常驻祈愿池
        $PermPrayTen = "PermPray/PrayTen?";
        //十连常驻祈愿池
        $RolePrayOne = "RolePray/PrayOne?";
        //单抽角色祈愿池
        $RolePrayTen = "RolePray/PrayTen?";
        //十连角色祈愿池
        $SetMemberAssign = "PrayInfo/SetMemberAssign?";
        //设置武器定轨
        $GetMemberAssign = "PrayInfo/GetMemberAssign?";
        //获取武器定轨
        $GetPondInfo = "PrayInfo/GetPondInfo?";
        //获取卡池信息
        $GetMemberPrayDetail = "PrayInfo/GetMemberPrayDetail?";
        //获取成员抽卡分析
        $GetLuckRanking = "PrayInfo/GetLuckRanking?";
        //获取授权码内欧气排行
        $authorzation = array("authorzation:" . $gachaKey);

        preg_match("/获取卡池|设置卡池|定轨|单抽|十连/", $msgContent, $msgMatch);
        $matchValue = $msgMatch[0];
        $msgContent = str_replace($matchValue, "", $msgContent);
        $this->redisSet("msgContent", $msgContent);
        switch ($matchValue) {
            /**
             * 信息图片
             */
            case '获取卡池':
                if ($this->redisExists("MiniGG-Gacha-PondInfo")) {
                    $resJson = $this->redisGet("MiniGG-Gacha-PondInfo");
                } else {
                    $reqUrl = $gachaUrl . $GetPondInfo;
                    $resJson = json_decode($this->requestUrl($reqUrl, "", $authorzation), true);
                    $this->redisSet("MiniGG-Gacha-PondInfo", $resJson, 14400);
                }
                $rolecount = sizeof($resJson['data']['role']);
                $ret = "【角色祈愿UP池】\n";
                $ret .= "\n五星角色①：" . $resJson['data']['role']['0']['pondInfo']['star5UpList']['0']['goodsName'];
                if ($rolecount == 2) {
                    $ret .= "\n五星角色②：" . $resJson['data']['role']['1']['pondInfo']['star5UpList']['0']['goodsName'];
                }
                $ret .= "\n\n四星角色：" . $resJson['data']['role']['0']['pondInfo']['star4UpList']['0']['goodsName'] . "、" . $resJson['data']['role']['0']['pondInfo']['star4UpList']['1']['goodsName'] . "、" . $resJson['data']['role']['0']['pondInfo']['star4UpList']['2']['goodsName'];
                $ret .= "\n\n----------------\n";
                $ret .= "\n【武器祈愿UP池】\n";
                $ret .= "\n五星武器①：" . $resJson['data']['arm']['0']['pondInfo']['star5UpList']['0']['goodsName'];
                $ret .= "\n五星武器②：" . $resJson['data']['arm']['0']['pondInfo']['star5UpList']['1']['goodsName'];
                $ret .= "\n\n四星武器：" . $resJson['data']['arm']['0']['pondInfo']['star4UpList']['0']['goodsName'] . "、" . $resJson['data']['arm']['0']['pondInfo']['star4UpList']['1']['goodsName'] . "、" . $resJson['data']['arm']['0']['pondInfo']['star4UpList']['2']['goodsName'];
                break;
            case '设置卡池':
                if ($this->redisExists("MiniGG-Gacha-PondInfo")) {
                    $resJson = $this->redisGet("MiniGG-Gacha-PondInfo");
                } else {
                    $reqUrl = $gachaUrl . $GetPondInfo;
                    $resJson = json_decode($this->requestUrl($reqUrl, "", $authorzation), true);
                    $this->redisSet("MiniGG-Gacha-PondInfo", $resJson, 14400);
                }
                $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "json_msg";
                $t = time();
                $s = $t . "y8h8tqUAEDzyR6xZQKX6Ak";
                $c = md5($s);
                $ret = array(
                    "template_id" => 23,
                    "kv" => array(
                        array(
                            "key" => "#DESC#",
                            "value" => "原神模拟抽卡-设置"
                        ),
                        array(
                            "key" => "#PROMPT#",
                            "value" => "原神模拟抽卡-设置"
                        ),
                        array(
                            "key" => "#LIST#",
                            "obj" => array(
                                array(
                                    "obj_kv" => array(
                                        array(
                                            "key" => "desc",
                                            "value" => "原神模拟抽卡-设置"
                                        )
                                    )
                                ), array(
                                    "obj_kv" => array(
                                        array(
                                            "key" => "desc",
                                            "value" => "请在30秒内点击对应的卡池名字进行设置"
                                        )
                                    )
                                ), array(
                                    "obj_kv" => array(
                                        array(
                                            "key" => "desc",
                                            "value" => "角色-" . $resJson['data']['role']['0']['pondInfo']['star5UpList']['0']['goodsName']
                                        ), array(
                                            "key" => "link",
                                            "value" => "https://bot.q.minigg.cn/app/plugins/genshingacha/set.php?userid=" . $msgReceiver . "&token=" . $c . "&pray=role&index=0"
                                        )
                                    )
                                ), array(
                                    "obj_kv" => array(
                                        array(
                                            "key" => "desc",
                                            "value" => "角色-" . $resJson['data']['role']['1']['pondInfo']['star5UpList']['0']['goodsName']
                                        ), array(
                                            "key" => "link",
                                            "value" => "https://bot.q.minigg.cn/app/plugins/genshingacha/set.php?userid=" . $msgReceiver . "&token=" . $c . "&pray=role&index=1"
                                        )
                                    )
                                ), array(
                                    "obj_kv" => array(
                                        array(
                                            "key" => "desc",
                                            "value" => "武器-" . $resJson['data']['arm']['0']['pondInfo']['star5UpList']['0']['goodsName']
                                        ), array(
                                            "key" => "link",
                                            "value" => "https://bot.q.minigg.cn/app/plugins/genshingacha/set.php?userid=" . $msgReceiver . "&token=" . $c . "&pray=arm&index=0"
                                        )
                                    )
                                ), array(
                                    "obj_kv" => array(
                                        array(
                                            "key" => "desc",
                                            "value" => "武器-" . $resJson['data']['arm']['0']['pondInfo']['star5UpList']['1']['goodsName']
                                        ), array(
                                            "key" => "link",
                                            "value" => "https://bot.q.minigg.cn/app/plugins/genshingacha/set.php?userid=" . $msgReceiver . "&token=" . $c . "&pray=arm&index=1"
                                        )
                                    )
                                ), array(
                                    "obj_kv" => array(
                                        array(
                                            "key" => "desc",
                                            "value" => "常驻"
                                        ), array(
                                            "key" => "link",
                                            "value" => "https://bot.q.minigg.cn/app/plugins/genshingacha/set.php?userid=" . $msgReceiver . "&token=" . $c . "&pray=perm&index=0"
                                        )
                                    )
                                ),
                            )
                        )
                    )
                );
                $ret = json_encode($ret);
                break;
            case '十连':
                if ($this->redisExists("MiniGG-Gacha-Set-" . $msgReceiver)) {
                    $setJson = $this->redisGet("MiniGG-Gacha-Set-" . $msgReceiver);
                    switch ($setJson['pray']) {
                        case 'role':
                            switch ($setJson['index']) {
                                case '0':
                                    $ret = "角色池1";
                                    break;
                                case '1':
                                    $ret = "角色池2";
                                    break;
                            }
                            break;
                        case 'arm':
                            switch ($setJson['index']) {
                                case '0':
                                    $ret = "武器池1";
                                    break;
                                case '1':
                                    $ret = "武器池2";
                                    break;
                            }
                            break;
                        case 'perm':
                            $ret = "常驻池";
                            break;
                    }
                } else {
                    $reqUrl = $gachaUrl . $GetPondInfo;
                    $resJson = json_decode($this->requestUrl($reqUrl, "", $authorzation), true);
                    $this->redisSet("MiniGG-Gacha-PondInfo", $resJson, 14400);
                }
        }
        /**
         *
         *
         *读数据 $this->redisGet("name");
         *判断是否存在 $this->redisExists("name");
         *写数据 $this->redisSet("name", "text or json");
         *
         */
        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }
}