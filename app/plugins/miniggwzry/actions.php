<?php

/**
 * 这是一个示例插件
 *
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class miniggwzry_actions extends app
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

        if (preg_match("/查牌子|查金牌/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);

            preg_match("/安卓QQ|苹果QQ|安卓微信|苹果微信/i", $msgContent, $areaTypeMatch);

            switch ($areaTypeMatch[0]) {
                case '安卓QQ':
                    $areaType = "qq";
                    break;
                case '安卓微信':
                    $areaType = "wx";
                    break;
                case '苹果QQ':
                    $areaType = "ios_qq";
                    break;
                case '苹果微信':
                    $areaType = "ios_wx";
                    break;
                default:
                    $areaType = "wx";
                    break;
            }

            $msgContent = str_replace($areaTypeMatch, "", $msgContent);
            $kofapi = "https://www.somekey.cn/mini/hero/getHeroInfo.php?hero=" . $msgContent . "&type=" . $areaType;
            $res = json_decode(file_get_contents($kofapi), true);
            $ret = "战力查询结果";
            $ret .= "\n";
            $ret .= "英雄：【" . $res["data"]["name"] . "】";
            $ret .= "\n";
            $ret .= "平台：【" . $res["data"]["platform"] . "】";
            $ret .= "\n";
            $ret .= "县标：" . $res["data"]["areaPower"] . " " . $res["data"]["area"];
            $ret .= "\n";
            $ret .= "市标：" . $res["data"]["cityPower"] . " " . $res["data"]["city"];
            $ret .= "\n";
            $ret .= "省标：" . $res["data"]["provincePower"] . " " . $res["data"]["province"];
            $ret .= "\n";
            $ret .= "查询结果仅供参考，数据非实时更新";
        }

        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }

    /**
     *
     * 获取金牌排行
     *
     */
    function getRankingByHeroJinPai($heroName, $areaType, $msgRobot, $msgSource, $msgSender, $len = 100)
    {
        $heroName_en = urlencode($heroName);

        $reqRet_1 = $this->requestUrl(
            APP_API_APP . "?type=getHeroInfo&heroName={$heroName_en}&frameId=" . FRAME_ID,
            "robotUin={$msgRobot}&msgSource={$msgSource}&msgUin=" . $msgSender
        );
        $resJson_1 = json_decode($reqRet_1);
        $heroInfo = $resJson_1->data->heroInfo;
        $heroId = $heroInfo->id;

        if (!$heroId) return;

        $heroName = $heroInfo->name;

        $reqRet_2 = $this->requestUrl(
            APP_API_APP . "?type=getRanking&aid=8&heroId={$heroId}&areaType=" . urlencode($areaType),
            "robotUin={$msgRobot}&msgSource={$msgSource}&msgUin=" . $msgSender
        );

        $resJson_2 = json_decode($reqRet_2);
        $resData = $resJson_2->data;
        $resStatus = $resJson_2->status;

        if ($resStatus->code != 200) {
            return $resStatus->msg ?? NULL;
        } else {
            $resArr = $resData->result->rows ?? NULL;
            $resArrNum = count($resArr);

            if ($resArrNum == 0) return $heroName . " 暂未更新";
        }

        $rankingIndex = 0;
        $ret = "[{$heroName}] {$areaType} 最低金牌\n";
        $ret .= "-----\n";
        //$ret .= "       战力       省份       更新时间\n";
        $ret .= "       战力       省份\n";
        $ret .= "-----\n";
        for ($ranking_i = 0; $ranking_i < $len; $ranking_i++) {
            $forList = $resArr[$ranking_i];

            $provinceName = APP_INFO['provinceType'][$forList->province];
            $zhanli = $forList->zhanli;
            //$time = date("Y-m-d", $forList->time);
            //$img = $forList->img_url;
            //$img = str_replace("http://img.laoliubei.com/zhanli/", "https://pvp.91m.top/img/zhanli/", $img);

            $rankingIndex++;

            if ($rankingIndex > 3) break;

            //$ret .= "[{$rankingIndex}].{$zhanli} {$provinceName} {$time}\n";
            $ret .= "[{$rankingIndex}].{$zhanli} {$provinceName}\n";
        }
        $ret = substr($ret, 0, strlen($ret) - 1);

        return $ret;
    }

    /**
     *
     * 获取战力排行
     *
     */
    function getRankingByHeroZhanLi($heroName, $areaType, $msgRobot, $msgSource, $msgSender, $len = 100)
    {
        $heroName_en = urlencode($heroName);

        $reqRet_1 = $this->requestUrl(
            APP_API_APP . "?type=getHeroInfo&heroName={$heroName_en}&frameId=" . FRAME_ID,
            "robotUin={$msgRobot}&msgSource={$msgSource}&msgUin=" . $msgSender
        );
        $resJson_1 = json_decode($reqRet_1);
        $heroInfo = $resJson_1->data->heroInfo;
        $heroId = $heroInfo->id;

        if (!$heroId) return;

        $heroName = $heroInfo->name;
        $nowAreaType = str_replace("WX", "微信", $areaType);

        $reqRet_2 = $this->requestUrl(
            APP_API_APP . "?type=getRanking&aid=9&heroId={$heroId}",
            "robotUin={$msgRobot}&msgSource={$msgSource}&msgUin=" . $msgSender
        );
        $resJson_2 = json_decode($reqRet_2);
        $resData = $resJson_2->data;
        $resStatus = $resJson_2->status;

        if ($resStatus->code != 200) {
            return $resStatus->msg ?? NULL;
        } else {
            $resArr = $resData->result->rows ?? NULL;
        }

        $nowAreaIndex = array_search($areaType, APP_INFO['areaType']) ?? 1;

        $rankingIndex = 0;
        $ret = "[{$heroName}] {$areaType} 国服战力\n";
        $ret .= "-----\n";
        $ret .= "       战力       玩家\n";
        $ret .= "-----\n";
        for ($ranking_i = 0; $ranking_i < $len; $ranking_i++) {
            $forList = $resArr[$ranking_i];

            $gamePlayerName = $forList->yxname;
            //$gamePlayerNameLen = strlen($gamePlayerName);
            //$newGamePlayerName = substr($gamePlayerName, 0, 3 * 3) . "*****";
            $heroFightPower = $forList->heroFightPower;
            $selareaid = $forList->selareaid;

            if ($selareaid != $nowAreaType) continue;

            $rankingIndex++;

            if ($rankingIndex > 15) break;

            $ret .= "[{$rankingIndex}].{$heroFightPower} {$gamePlayerName}\n";
        }
        $ret .= "-----\n";
        //$ret .= "> 全国百强 https://pvp.91m.top/ranking?type=2&bid=" . $nowAreaIndex;

        return $ret;
    }
}
