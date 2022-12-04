<?php

class genshinENKA_actions extends app
{
    function __construct(&$appManager)
    {
        $appManager->register('plugin', $this, 'EventFun');
        $this->linkRedis();
        $this->IMS();
    }

    //解析函数的参数是appManager的引用

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

        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
        $msgContent = str_replace(" ", "", $msgContent);

        $img = $this->manager->canvas(1920, 1080, '#FFFFFF');



        $img->save('test.jpg', 100);

        $ret = "Image Test";

        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }

    function createImg()
    {
        return 0;
    }
}
