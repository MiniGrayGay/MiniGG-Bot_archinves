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
        if (preg_match("/角色/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);
            $charapi = "https://info.minigg.cn/characters?query=" . urlencode($msgContent);
            if (preg_match("/\d{1,2}/", $msgContent, $levelMatch)) {
                $levelValue = $levelMatch[0];
                $msgContent = str_replace($levelValue, "", $msgContent);
                $charapi .= "&stats=" . $levelValue;
            }
            $res = json_decode($this->requestUrl($charapi, "", "", ""), true);
            if (isset ($res['errcode'])) {
                $ret = "查询的角色名字或角色类别不存在，可@机器人并发送/help获取完整帮助";
            } elseif (isset ($res['name'])) {
                $ret = $res['title'] . " - " . $res['fullname'] . "\n";
                $ret .= "【稀有度】：" . $res['rarity'] . "星\n";
                $ret .= "【武器】：" . $res['weapontype'] . "\n";
                $ret .= "【元素】：" . $res['element'] . "元素\n";
                $ret .= "【突破加成】：" . $res['substat'] . "\n";
                $ret .= "【生日】：" . $res['birthday'] . "\n";
                $ret .= "【命之座】：" . $res['constellation'] . "\n";
                $ret .= "【CV】：中：" . $res['cv']['chinese'] . "、日：" . $res['cv']['japanese'] . "\n";
                $ret .= "【介绍】：" . $res['description'];
            } else {
                foreach ($res as $resarray) {
                    $ret .= $resarray;
                    $ret .= "、";
                }
                $ret = rtrim($ret, "、");
            }
        } elseif (preg_match("/武器/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);
            $weaponapi = "https://info.minigg.cn/weapons?query=" . urlencode($msgContent);
            if (preg_match("/\d{1,2}/", $msgContent, $levelMatch)) {
                $levelValue = $levelMatch[0];
                $msgContent = str_replace($levelValue, "", $msgContent);
                $charapi .= "&stats=" . $levelValue;
            }
            $res = json_decode($this->requestUrl($weaponapi, "", "", ""), true);
            if (sizeof($res['costs']) == 6) {
                $weaponlevel = "https://info.minigg.cn/weapons?query=" . urlencode($msgContent) . "&stats=90";
                $weaponattack = json_decode($this->requestUrl($weaponlevel, "", "", ""), true);
            } else {
                $weaponlevel = "https://info.minigg.cn/weapons?query=" . urlencode($msgContent) . "&stats=70";
                $weaponattack = json_decode($this->requestUrl($weaponlevel, "", "", ""), true);
            }
            if (isset ($res['errcode'])) {
                $ret = "查询的武器或武器类别不存在，可@机器人并发送/help获取完整帮助";
            } elseif (isset ($res['name'])) {
                $ret .= "【名称】：" . $res['name'] . "\n";
                $ret .= "【类型】：" . $res['rarity'] . "星" . $res['weapontype'] . "\n";
                $ret .= "【基础攻击力】：" . $res['baseatk'] . "\n";
                $ret .= "【满级攻击力】：" . round($weaponattack['attack']) . "\n";
                $ret .= "【基础" . $res['substat'] . "】：" . $res['subvalue'] . "%\n";
                $ret .= "【满级" . $res['substat'] . "】：" . round($weaponattack['specialized'], 3) * 100 . "%\n";
                $ret .= "【介绍】：" . $res['description'] . "\n";
                $ret .= "【" . $res['effectname'] . "】：" . $res['effect'];
            } else {
                foreach ($res as $resarray) {
                    $ret .= $resarray;
                    $ret .= "、";
                }
                $ret = rtrim($ret, "、");
            }
        }
        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }
}