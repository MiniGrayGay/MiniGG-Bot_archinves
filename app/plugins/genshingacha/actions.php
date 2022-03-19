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

        preg_match("/获取卡池|设置卡池/", $msgContent, $msgMatch);
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
                $ret = "角色祈愿UP池";
                $ret .= "\n卡池①";
                $ret .= "\n五星角色：" . $resJson['data']['role']['0']['pondInfo']['star5UpList']['0']['goodsName'];
                if ($rolecount == 2) {
                    $ret .= "\n卡池②";
                    $ret .= "\n五星角色：" . $resJson['data']['role']['1']['pondInfo']['star5UpList']['0']['goodsName'];
                }
                $ret .= "\n四星角色：" . $resJson['data']['role']['0']['pondInfo']['star4UpList']['0']['goodsName'] . "、" . $resJson['data']['role']['0']['pondInfo']['star4UpList']['1']['goodsName'] . "、" . $resJson['data']['role']['0']['pondInfo']['star4UpList']['2']['goodsName'] . "\n\n";
                $ret .= "武器祈愿UP池";
                $ret .= "\n五星武器①：" . $resJson['data']['arm']['0']['pondInfo']['star5UpList']['0']['goodsName'];
                $ret .= "\n五星武器②：" . $resJson['data']['arm']['0']['pondInfo']['star5UpList']['1']['goodsName'];
                $ret .= "\n四星武器：" . $resJson['data']['arm']['0']['pondInfo']['star4UpList']['0']['goodsName'] . "、" . $resJson['data']['arm']['0']['pondInfo']['star4UpList']['1']['goodsName'] . "、" . $resJson['data']['arm']['0']['pondInfo']['star4UpList']['2']['goodsName'] . "\n\n";
                break;
            case '设置卡池':
                switch ($msgContent) {
                    case '':
                        $ret = "null";
                        break;
                    case '角色':
                        $ret = "角色";
                        break;
                    case '武器':
                        $ret = "武器";
                        break;
                    case '常驻':
                    case '毒池':
                        $ret = "常驻";
                        break;
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