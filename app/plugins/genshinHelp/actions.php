<?php

class genshinHelp_actions extends app
{
    function __construct(&$appManager)
    {
        $appManager->register('plugin', $this, 'EventFun');
        $this->linkRedis();
    }

    function EventFun($msg)
    {
        $msgPort = $msg['Port'];
        $msgPid = $msg['Pid'];
        $msgVer = $msg['Ver'];
        $msgId = $msg['MsgID'];
        $msgRobot = $msg['Robot'];
        $msgType = $msg['MsgType'];
        $msgSubType = $msg['MsgSubType'];
        $msgSource = $msg['Source'];
        $msgSender = $msg['Sender'];
        $msgReceiver = $msg['Receiver'];
        $msgContent = base64_decode($msg['Content']);
        $msgOrigMsg = base64_decode($msg['OrigMsg']);

        //$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = NULL;
        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
        $msgContent = str_replace(" ", "", $msgContent);

        $ret = NULL;

        //$this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }
}
