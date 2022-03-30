<?php

/**
 * 这是一个示例插件
 *
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class music_actions extends app
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

        //$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = NULL;
        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
        $msgContent = str_replace(" ", "", $msgContent);

        if (preg_match("/点歌|我想听|来一首|换一首/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);

            $ret = $this->getMusicByQQ($msgContent);
        }

        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }

    /**
     *
     * 获取 QQ 音乐
     *
     */
    function getMusicByQQ($key)
    {
        $reqRet = $this->requestUrl("https://c.y.qq.com/splcloud/fcgi-bin/smartbox_new.fcg?_=1648503963910&cv=4747474&ct=24&format=json&inCharset=utf-8&outCharset=utf-8&notice=0&platform=yqq.json&needNewCode=1&uin=0&g_tk_new_20200303=99601036&g_tk=5381&hostUin=0&is_xml=0&key=" . urlencode($key));
        $resJson = json_decode($reqRet);
        $resArr = $resJson->data->song->itemlist ?? NULL;
        $resArrNum = count($resArr);

        if (!$resArr) {
            $ret = "好像没有诶，换一个关键词试一试叭~";

            return $ret;
        }

        $resArrNum > 3 ? $resNum = 3 : $resNum = $resArrNum;

        $ret = "搜索结果如下:\n";
        $ret .= "-----\n";
        for ($music_i = 0; $music_i < $resNum; $music_i++) {
            $forList = $resArr[$music_i];

            $name = $forList->name;
            $singer = $forList->singer;
            $mid = $forList->mid;

            $ret .= $name . " - {$singer}\n";
            $ret .= $this->appGetShortUrl("https://i.y.qq.com/v8/playsong.html?songmid={$mid}&ADTAG=myqq&from=myqq&channel=10007100") . "\n";
            $ret .= "-----\n";
        }
        $ret = substr($ret, 0, strlen($ret) - 7);

        return $ret;
    }
}
