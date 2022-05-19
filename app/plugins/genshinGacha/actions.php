<?php

/**
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class genshinGacha_actions extends app
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
        $gachaUrl = "https://gacha.microgg.cn";

        preg_match("/获取卡池|设置卡池|定轨|单抽|十连/", $msgContent, $msgMatch);
        $matchValue = $msgMatch[0];
        $msgContent = str_replace($matchValue, "", $msgContent);

        if ($this->redisExists("MiniGG-Gacha-PondInfo")) {
            $resJson = $this->redisGet("MiniGG-Gacha-PondInfo");
        } else {
            $reqUrl = "{$gachaUrl}?Type=GetPondInfo";
            $resJson = json_decode($this->requestUrl($reqUrl, "", $authorzation), true);
            $this->redisSet("MiniGG-Gacha-PondInfo", $resJson, 14400);
        }
        $rolecount = sizeof($resJson['data']['role']);

        switch ($matchValue) {
            case '获取卡池':

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
                if (!$resJson['data']['role']['1']['pondInfo']['star5UpList']['0']['goodsName']) $resJson['data']['role']['1']['pondInfo']['star5UpList']['0']['goodsName'] = $resJson['data']['role']['0']['pondInfo']['star5UpList']['0']['goodsName'];

                if (!in_array($msgContent, array($resJson['data']['role']['0']['pondInfo']['star5UpList']['0']['goodsName'], $resJson['data']['role']['1']['pondInfo']['star5UpList']['0']['goodsName'], $resJson['data']['arm']['0']['pondInfo']['star5UpList']['0']['goodsName'], $resJson['data']['arm']['0']['pondInfo']['star5UpList']['1']['goodsName'], "角色池1", "角色池2", "武器池1", "武器池2"))){
                    $ret = "请输入卡池名字或卡池序号进行设置";
                }else {
                    $ret = ord($msgContent);
                }
                break;
            case '十连':
                break;
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