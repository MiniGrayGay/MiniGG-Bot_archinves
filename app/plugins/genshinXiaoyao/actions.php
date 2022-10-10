<?php

/**
 * 感谢逍遥CVS大佬提供的原神图鉴
 * @dataProvider https://github.com/ctrlcvs/
 */
class genshinXiaoyao_actions extends app
{
    function __construct(&$appManager)
    {
        $appManager->register('plugin', $this, 'EventFun');
        $this->linkRedis();
        $this->PHP_YAML();
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

        /**
         * 图鉴目录
         */
        $Atlas_list_array = json_decode(file_get_contents(APP_DIR_RESOURCES . "xiaoyao_plus/Atlas_list.json"), true);
        $Atlas_list_keys = array_keys($Atlas_list_array);
        if (preg_match("/^(" . implode("|", $Atlas_list_keys) . ")$/", $msgContent)) {
            foreach ($Atlas_list_keys as $value) {
                if (preg_match("/^(" . $value . ")$/", $msgContent)) {
                    $Atlas_list_match = $value;
                }
            }
            $ret = implode("\n", $Atlas_list_array[$Atlas_list_match]);
        } else {
            if (preg_match("/图鉴$/", $msgContent, $msgMatch)) {
                $matchValue = $msgMatch[0];
                $msgContent = str_replace($matchValue, "", $msgContent);
            }
            if (preg_match("/^图鉴/", $msgContent, $msgMatch)) {
                $matchValue = $msgMatch[0];
                $msgContent = str_replace($matchValue, "", $msgContent);
            }

            /**
             * 角色图鉴-Yaml
             * @link https://raw.githubusercontent.com/Le-niao/Yunzai-Bot/main/plugins/genshin/defSet/role/name.yaml
             */
            $juese_tujian_array = $this->phpyaml->parseFile(APP_DIR_RESOURCES . "altnames/roldName.yaml");
            $juese_tujian = $this->array_search_mu($msgContent, $juese_tujian_array);
            $roleid_juese = json_decode(file_get_contents(APP_DIR_RESOURCES . "altnames/roleid_juese.json"), true);
            $juese_tujian = $roleid_juese[implode($juese_tujian)];

            /**
             * 秘境图鉴
             */
            $mijin_tujian_array = json_decode(file_get_contents(APP_DIR_RESOURCES . "altnames/mijin_tujian.json"), true);
            $mijin_tujian = implode($this->array_search_mu($msgContent, $mijin_tujian_array));
            if(!$mijin_tujian){
                $mijin_tujian_name = implode("|", array_keys($mijin_tujian_array));
                if (preg_match("/^(" . $mijin_tujian_name . ")$/", $msgContent)){
                    $mijin_tujian = $msgContent;
                }
            }

            /**
             * 圣遗物图鉴
             */
            $shengyiwu_tujian_array = json_decode(file_get_contents(APP_DIR_RESOURCES . "altnames/shengyiwu_tujian.json"), true);
            $shengyiwu_tujian = implode($this->array_search_mu($msgContent, $shengyiwu_tujian_array));
            if(!$shengyiwu_tujian){
                $shengyiwu_tujian_name = implode("|", array_keys($shengyiwu_tujian_array));
                if (preg_match("/^(" . $shengyiwu_tujian_name . ")$/", $msgContent)){
                    $shengyiwu_tujian = $msgContent;
                }
            }

            /**
             * 食物图鉴
             */
            $shiwu_tujian_array = json_decode(file_get_contents(APP_DIR_RESOURCES . "altnames/shiwu_tujian.json"), true);
            $shiwu_tujian = implode($this->array_search_mu($msgContent, $shiwu_tujian_array));
            if(!$mijin_tujian){
                $mijin_tujian_name = implode("|", array_keys($mijin_tujian_array));
                if (preg_match("/^(" . $mijin_tujian_name . ")$/", $msgContent)){
                    $shiwu_tujian = $msgContent;
                }
            }

            /**
             * 武器图鉴
             */
            $wuqi_tujian_array = json_decode(file_get_contents(APP_DIR_RESOURCES . "altnames/wuqi_tujian.json"), true);
            $wuqi_tujian = implode($this->array_search_mu($msgContent, $wuqi_tujian_array));
            if(!$wuqi_tujian){
                $wuqi_tujian_name = implode("|", array_keys($wuqi_tujian_array));
                if (preg_match("/^(" . $wuqi_tujian_name . ")$/", $msgContent)){
                    $wuqi_tujian = $msgContent;
                }
            }

            /**
             * 道具图鉴
             */
            $daoju_tujian_array = json_decode(file_get_contents(APP_DIR_RESOURCES . "altnames/daoju_tujian.json"), true);
            $daoju_tujian = implode($this->array_search_mu($msgContent, $daoju_tujian_array));
            if(!$daoju_tujian){
                $daoju_tujian_name = implode("|", array_keys($daoju_tujian_array));
                if (preg_match("/^(" . $daoju_tujian_name . ")$/", $msgContent)){
                    $daoju_tujian = $msgContent;
                }
            }

            /**
             * 原魔图鉴
             */
            $yuanmo_tujian_array = json_decode(file_get_contents(APP_DIR_RESOURCES . "altnames/yuanmo_tujian.json"), true);
            $yuanmo_tujian = implode($this->array_search_mu($msgContent, $yuanmo_tujian_array));
            if(!$yuanmo_tujian){
                $yuanmo_tujian_name = implode("|", array_keys($yuanmo_tujian_array));
                if (preg_match("/^(" . $yuanmo_tujian_name . ")$/", $msgContent)){
                    $yuanmo_tujian = $msgContent;
                }
            }

            if($juese_tujian){
                $type_tujian = "juese_tujian";
                $name_tujian = $juese_tujian;
            }elseif ($wuqi_tujian){
                $type_tujian = "wuqi_tujian";
                $name_tujian = $wuqi_tujian;
            }elseif ($shengyiwu_tujian){
                $type_tujian = "shengyiwu_tujian";
                $name_tujian = $shengyiwu_tujian;
            }elseif ($yuanmo_tujian){
                $type_tujian = "yuanmo_tujian";
                $name_tujian = $yuanmo_tujian;
            }elseif ($daoju_tujian){
                $type_tujian = "daoju_tujian";
                $name_tujian = $daoju_tujian;
            }elseif ($mijin_tujian){
                $type_tujian = "mijin_tujian";
                $name_tujian = $mijin_tujian;
            }elseif ($shiwu_tujian){
                $type_tujian = "shiwu_tujian";
                $name_tujian = $shiwu_tujian;
            }

            if(FRAME_ID == 50000){
                $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "image_msg";
                $ret = file_get_contents(APP_DIR_RESOURCES . "xiaoyao_plus/{$type_tujian}/{$name_tujian}.json");
            }elseif (FRAME_ID == 70000){
                $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "image_file";
                $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgFile'] = APP_DIR_RESOURCES . "xiaoyao_plus/{$type_tujian}/{$name_tujian}.png";
                $ret = "image_file";
            }elseif (FRAME_ID == 10000){
                $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
                $MyPCQQImg = json_decode(file_get_contents(APP_DIR_RESOURCES . "xiaoyao_plus/{$type_tujian}/{$name_tujian}.json"), true);
                $MyPCQQImg = $MyPCQQImg[0]['image_info_array'][0]['url'];
                $ret = "[{$MyPCQQImg}]";
            }

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
