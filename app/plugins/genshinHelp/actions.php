<?php

/**
 * 这是一个示例插件
 *
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class demo_actions extends app
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

        /**
         *
         * 这是返回时的消息类型，除了 MyPCQQ 机器，其他的都得特殊处理
         *
         * @link https://jmglsi.coding.net/public/bot.91m.top/backend/git/files#user-content-%E6%95%B0%E6%8D%AE
         */
        //$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = NULL;
        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
        $msgContent = str_replace(" ", "", $msgContent);

        $ret = NULL;

        //$this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }
}
