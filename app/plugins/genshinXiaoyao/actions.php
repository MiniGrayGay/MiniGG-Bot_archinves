<?php

/**
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class genshinXiaoyao_actions extends app
{
    function __construct(&$appManager)
    {
        //注册这个插件
        //第一个参数是钩子的名称
        //第二个参数是appManager的引用
        //第三个是插件所执行的方法
        $appManager->register('plugin', $this, 'EventFun');
        $this->linkRedis();
        $this->PHP_YAML();
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
        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
        $msgContent = str_replace(" ", "", $msgContent);    //去掉消息头的空格
        $msgContent = str_replace("#", "", $msgContent);    //去掉消息头的#

        /**
         * 图鉴目录
         */
        $Atlas_list_array = json_decode(file_get_contents(__DIR__ . "/resources/Atlas_list.json"), true);
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

            /**
             * 角色图鉴-Yaml
             */
            $juese_tujian_array = $this->phpyaml->parseFile(__DIR__ . '/resources/juese_tujian.yaml');
            $juese_tujian = $this->array_search_mu($msgContent, $juese_tujian_array);
            $roleid_juese = json_decode(file_get_contents(__DIR__ . "/resources/roleid_juese.json"), true);
            $juese_tujian = $roleid_juese[implode($juese_tujian)];
            $this->redisSet("juese", $juese_tujian);

            /**
             * 秘境图鉴
             */
            $mijin_tujian_array = json_decode(file_get_contents(__DIR__ . "/resources/mijin_tujian.json"), true);
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
            $shengyiwu_tujian_array = json_decode(file_get_contents(__DIR__ . "/resources/shengyiwu_tujian.json"), true);
            $shengyiwu_tujian = implode($this->array_search_mu($msgContent, $shengyiwu_tujian_array));
            if(!$shengyiwu_tujian){
                $shengyiwu_tujian_name = implode("|", array_keys($shengyiwu_tujian_array));
                if (preg_match("/^(" . $shengyiwu_tujian_name . ")$/", $msgContent)){
                    $mijin_tujian = $msgContent;
                }
            }

            /**
             * 食物图鉴
             */
            $shiwu_tujian_array = json_decode(file_get_contents(__DIR__ . "/resources/shiwu_tujian.json"), true);
            $shiwu_tujian = implode($this->array_search_mu($msgContent, $shiwu_tujian_array));
            if(!$mijin_tujian){
                $mijin_tujian_name = implode("|", array_keys($mijin_tujian_array));
                if (preg_match("/^(" . $mijin_tujian_name . ")$/", $msgContent)){
                    $mijin_tujian = $msgContent;
                }
            }

            /**
             * 武器图鉴
             */
            $wuqi_tujian_array = json_decode(file_get_contents(__DIR__ . "/resources/wuqi_tujian.json"), true);
            $wuqi_tujian = implode($this->array_search_mu($msgContent, $wuqi_tujian_array));
            if(!$wuqi_tujian){
                $wuqi_tujian_name = implode("|", array_keys($wuqi_tujian_array));
                if (preg_match("/^(" . $wuqi_tujian_name . ")$/", $msgContent)){
                    $mijin_tujian = $msgContent;
                }
            }

            /**
             * 原魔图鉴
             */
            $yuanmo_tujian_array = json_decode(file_get_contents(__DIR__ . "/resources/yuanmo_tujian.json"), true);
            $yuanmo_tujian = implode($this->array_search_mu($msgContent, $yuanmo_tujian_array));
            if(!$yuanmo_tujian){
                $yuanmo_tujian_name = implode("|", array_keys($yuanmo_tujian_array));
                if (preg_match("/^(" . $yuanmo_tujian_name . ")$/", $msgContent)){
                    $yuanmo_tujian = $msgContent;
                }
            }

            if(FRAME_ID == 50000){
                $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "image_msg";
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
                }elseif ($mijin_tujian){
                    $type_tujian = "mijin_tujian";
                    $name_tujian = $mijin_tujian;
                }elseif ($shiwu_tujian){
                    $type_tujian = "shiwu_tujian";
                    $name_tujian = $shiwu_tujian;
                }

                $ret = file_get_contents(__DIR__ . "/resources/{$type_tujian}/$name_tujian.json");
            }elseif (in_array(FRAME_ID, array(60000, 70000))){
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
                }elseif ($mijin_tujian){
                    $type_tujian = "mijin_tujian";
                    $name_tujian = $mijin_tujian;
                }elseif ($shiwu_tujian){
                    $type_tujian = "shiwu_tujian";
                    $name_tujian = $shiwu_tujian;
                }
                $ret = __DIR__ . "/resources/{$type_tujian}/$name_tujian.png";
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
