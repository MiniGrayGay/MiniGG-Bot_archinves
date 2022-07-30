<?php

/**
 * 这是一个示例插件
 *
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class newUid_actions extends app
{
    function __construct(&$appManager)
    {
        //注册这个插件
        //第一个参数是钩子的名称
        //第二个参数是appManager的引用
        //第三个是插件所执行的方法
        $appManager->register('plugin', $this, 'EventFun');
        $this->IMS();
        $this->linkRedis();

        $this->mihoyo_headers[] = "Content-Type:" . "application/x-www-form-urlencoded; charset=UTF-8";
        $this->mihoyo_headers[] = 'x-rpc-app_version: 2.11.1';
        $this->mihoyo_headers[] = 'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) miHoYoBBS/2.11.1';
        $this->mihoyo_headers[] = 'x-rpc-client_type: 5';
        $this->mihoyo_headers[] = 'Referer: https://webstatic.mihoyo.com/';
        $this->mihoyo_url['o'] = "https://api-takumi.mihoyo.com";
        $this->mihoyo_url['n'] = "https://api-takumi-record.mihoyo.com";
        $this->get_ck = $this->get_ck();
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

        /**
         *
         * 这是返回时的消息类型，除了 MyPCQQ 机器，其他的都得特殊处理
         *
         * @link https://jmglsi.coding.net/public/bot.91m.top/backend/git/files#user-content-%E6%95%B0%E6%8D%AE
         */
        //$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = NULL;
        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
        $msgContent = str_replace(" ", "", $msgContent);

        if (preg_match("/uid/i", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);
            $uid = $msgContent;
            if (preg_match('/^([1-2]|5)\d{8}$/', $uid)) {
                $infoJson = $this->get_info($uid);
                $infoArray = json_decode($infoJson);
                foreach ($infoArray->data->avatars as $v) {
                    $character_ids[] = $v->id;
                }
                $characterJson = $this->get_character($uid, $character_ids);
                $characterArray = json_decode($characterJson);
                $ret = "success";
            }
        }

        //$avatar = json_decode(file_get_contents(__DIR__ . "/src/json/avatar.json"));

        //$img = $this->manager->canvas(2490, 2006);
        //$img = $img->insert('1109232.jpg');
        //$img->save('img.png');
        //$ret = "success";
        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }

    function get_character($uid, $character_ids, $server_id = "cn_gf01")
    {
        if (preg_match('/^(5)\d{8}$/', $uid)) $server_id = "cn_qd01";
        $url = $this->mihoyo_url['n'] . "/game_record/app/genshin/api/character";
        $postArray = array("character_ids" => $character_ids,"role_id" => $uid,"server" => $server_id);
        $headers = $this->mihoyo_headers;
        $headers[] = "DS:" . $this->get_ds_token(NULL, $postArray);
        $this->redisSet("DS", $headers);
        return $this->requestUrl($url, json_encode($postArray), $headers, $this->get_ck);
    }

    function get_info($uid, $server_id = "cn_gf01")
    {
        if (preg_match('/^(5)\d{8}$/', $uid)) $server_id = "cn_qd01";
        $params = "role_id=" . $uid . "&server=" . $server_id;
        $url = $this->mihoyo_url['n'] . "/game_record/app/genshin/api/index?" . $params;
        $headers = $this->mihoyo_headers;
        $headers[] = "DS:" . $this->get_ds_token($params);
        return $this->requestUrl($url, NULL, $headers, $this->get_ck);
    }

    function get_ds_token($q, $b = NULL)
    {
        if ($b) {
            $b = json_encode($b);
        }
        //加签DS，查询米哈游API
        $s = "xV8v4Qu54lUKrEYFZkJhB8cuOh9Asafs";
        $t = time();
        $r = rand(100001, 199999);
        $c = md5("salt=" . $s . "&t=" . $t . "&r=" . $r . "&b=" . $b . "&q=" . $q);
        $ds = $t . "," . $r . "," . $c;
        return $ds;
    }

    function get_ck()
    {
        // Cookies池
        $cks[] = "ltoken=hgMow1vIpXuENV3R1rgOxvDf3PE7NR8rLR3Oa6vr;ltuid=258293438;cookie_token=Sgb26NZlbIUek8lkEGzNVsYumU6ldMplkObZzeTT; account_id=258293438;";
        $cks[] = "ltoken=4VXkRPX5D1klkeLRYJBw4DeMvwpap4gSaSKMCOUg;ltuid=51929747;cookie_token=nvfHqJvX2bVhAPiOixMJSZIHMriTEmFjAz8XlLgb; account_id=51929747;";
        $ck = $cks[array_rand($cks)];
        return $ck;
    }
}