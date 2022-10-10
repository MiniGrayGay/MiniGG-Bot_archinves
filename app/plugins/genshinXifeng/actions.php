<?php
/**
 * 感谢米游社猫冬大佬提供的原神攻略图
 * @dataProvider https://bbs.mihoyo.com/ys/collection/642956
 */
class genshinXifeng_actions extends app
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

        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
        $msgContent = str_replace(" ", "", $msgContent);    //去掉消息头的空格
        $msgContent = str_replace("#", "", $msgContent);    //去掉消息头的#

        if (preg_match("/攻略$/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);
        }

        if (preg_match("/^攻略/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);
        }


        /**
         * 角色图鉴-Yaml
         */
        $juese_tujian_array = $this->phpyaml->parseFile(APP_DIR_RESOURCES . "altnames/roldName.yaml");
        $juese_tujian = $this->array_search_mu($msgContent, $juese_tujian_array);
        $roleid_juese = json_decode(file_get_contents(APP_DIR_RESOURCES . "altnames/roleid_juese.json"), true);
        $juese_tujian = $roleid_juese[implode($juese_tujian)];

        if(FRAME_ID == 50000){
            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "image_msg";
            $ret = file_get_contents(APP_DIR_RESOURCES . "xifeng/{$juese_tujian}.json");
        }elseif (FRAME_ID == 70000){
            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "image_file";
            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgFile'] = APP_DIR_RESOURCES . "xifeng/{$juese_tujian}.jpg";
            $ret = "image_file";
        }elseif (FRAME_ID == 10000){
            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
            $MyPCQQImg = json_decode(file_get_contents(APP_DIR_RESOURCES . "xifeng/{$juese_tujian}.json"), true);
            $MyPCQQImg = $MyPCQQImg[0]['image_info_array'][0]['url'];
            $ret = "[{$MyPCQQImg}]";
        }

        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }

    function array_search_mu($search, $array, $i = 0, $found = array())
    {
        foreach ($array as $key => $value) {
            $needle = array_search($search, $value);
            if ($needle === 0) $found[] = $key;
            if ($needle) $found[] = $key;
        }
        return $found;
    }
}
