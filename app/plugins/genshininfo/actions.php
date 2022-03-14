<?php

/**
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class genshininfo_actions extends app
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

        preg_match("/角色|武器|命之座|命座|天赋|圣遗物|食物|原魔/", $msgContent, $msgMatch);
        $matchValue = $msgMatch[0];
        $msgContent = str_replace($matchValue, "", $msgContent);
        switch ($matchValue) {
            /**
             * 角色查询
             */
            case '角色':
                $resArray = $this->requestUrl(APP_INFO['MiniGGApi']['Characters'] . urlencode($msgContent));
                $resJson = json_decode($resArray);
                if (isset ($resJson->errcode)) {
                    $ret = $resJson->errmsg;
                } elseif (isset ($resJson->name)) {
                    $ret = $resJson->title . " - " . $resJson->fullname . "\n";
                    $ret .= "【稀有度】：" . $resJson->rarity . "星\n";
                    $ret .= "【武器】：" . $resJson->weapontype . "\n";
                    $ret .= "【元素】：" . $resJson->element . "元素\n";
                    $ret .= "【突破加成】：" . $resJson->substat . "\n";
                    $ret .= "【生日】：" . $resJson->birthday . "\n";
                    $ret .= "【命之座】：" . $resJson->constellation . "\n";
                    $ret .= "【CV】：中：" . $resJson->cv->chinese . "、日：" . $resJson->cv->japanese . "\n";
                    $ret .= "【介绍】：" . $resJson->description;
                } else {
                    foreach ($resJson as $resArray) {
                        $ret .= $resArray;
                        $ret .= "、";
                    }
                    $ret = rtrim($ret, "、");
                }
                break;
            /**
             * 武器查询
             */
            case '武器':
                $resArray = $this->requestUrl(APP_INFO['MiniGGApi']['Weapons'] . urlencode($msgContent));
                $resJson = json_decode($resArray);
                if (isset ($resJson->errcode)) {
                    $ret = $resJson->errmsg;
                } else {
                    if (isset ($resJson->costs->ascend6)) {
                        $level = "&stats=90";
                    } else {
                        $level = "&stats=70";
                    }
                    $levelArray = $this->requestUrl(APP_INFO['MiniGGApi']['Weapons'] . urlencode($msgContent) . $level);
                    $levelJson = json_decode($levelArray);
                    if (isset ($resJson->name)) {
                        $ret = "【名称】：" . $resJson->name . "\n";
                        $ret .= "【类型】：" . $resJson->rarity . "星" . $resJson->weapontype . "\n";
                        $ret .= "【介绍】：" . $resJson->description . "\n";
                        $ret .= "【基础/满级攻击力】：" . $resJson->baseatk . "/" . round($levelJson->attack, 2) . "\n";
                        if ($resJson->substat !== "") {
                            $ret .= "【突破加成】：" . $resJson->substat . "\n";
                            $ret .= "【基础/满级加成】：" . $resJson->subvalue . "/" . round(($levelJson->specialized * 100), 2) . "%\n";
                            $ret .= "【" . $resJson->effectname . "】：" . $resJson->effect . "\n";
                        }
                    } else {
                        foreach ($resJson as $resArray) {
                            $ret .= $resArray;
                            $ret .= "、";
                        }
                        $ret = rtrim($ret, "、");
                    }
                }
                break;
        }
        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }
}