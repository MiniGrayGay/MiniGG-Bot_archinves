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

        preg_match("/信息|攻略|角色|武器|命之座|命座|天赋|圣遗物|食物|原魔|料理|怪物|副本/", $msgContent, $msgMatch);
        $matchValue = $msgMatch[0];
        $msgContent = str_replace($matchValue, "", $msgContent);
        if ($msgContent) {
            switch ($matchValue) {
                /**
                 * 信息图片
                 */
                case '信息':
                    $imgUrl = "https://img.genshin.minigg.cn/info/" . urlencode($msgContent) . ".jpg";
                    if (FRAME_ID == 10000) {
                        $ret .= "[{$imgUrl}]";

                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
                    } elseif (FRAME_ID == 50000) {
                        $ret = file_get_contents(__DIR__ . "/信息/" . $msgContent . ".json");
                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "image_msg";
                    } elseif (in_array(FRAME_ID, array(60000, 70000))) {
                        $ret = "";
                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgUrl'] = $imgUrl;
                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg,image_msg";
                    } else {
                        $ret = NULL;
                    }
                    break;
                /**
                 * 攻略图片
                 */
                case '攻略':
                    $imgUrl = "https://img.genshin.minigg.cn/guide/" . urlencode($msgContent) . ".jpg";
                    if (FRAME_ID == 10000) {
                        $ret .= "[{$imgUrl}]";

                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg";
                    } elseif (FRAME_ID == 50000) {
                        $ret = file_get_contents(__DIR__ . "/攻略/" . $msgContent . ".json");
                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "image_msg";
                    } elseif (in_array(FRAME_ID, array(60000, 70000))) {
                        $ret = "";
                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgImgUrl'] = $imgUrl;
                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "at_msg,image_msg";
                    } else {
                        $ret = NULL;
                    }
                    break;
                /**
                 * 角色查询
                 */
                case '角色':
                    $resArray = $this->requestUrl(APP_INFO['MiniGGApi']['Characters'] . urlencode($msgContent));
                    $resJson = json_decode($resArray);
                    if (isset ($resJson->errcode)) {
                        $ret = $resJson->errmsg;
                    } elseif (isset ($resJson->name)) {
                        $ret = $resJson->title . " - " . $resJson->fullname . "\n";
                        $ret .= "\n================\n";
                        $ret .= "【稀有度】：" . $resJson->rarity . "星\n";
                        $ret .= "【武器】：" . $resJson->weapontype . "\n";
                        $ret .= "【元素】：" . $resJson->element . "元素\n";
                        $ret .= "【突破加成】：" . $resJson->substat . "\n";
                        $ret .= "【生日】：" . $resJson->birthday . "\n";
                        $ret .= "【命之座】：" . $resJson->constellation . "\n";
                        $ret .= "【CV】：中：" . $resJson->cv->chinese . "、日：" . $resJson->cv->japanese . "\n";
                        $ret .= "【介绍】：" . $resJson->description;
                    } else {
                        $ret = $this->retrtrim($resJson);
                    }

                    if (FRAME_ID == 50000) {
                        $ret = str_replace("\n", "\n\n", $ret);
                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "markdown_msg";
                    }
                    break;
                /**
                 * 武器查询
                 */
                case '武器':
                    $resArray = $this->requestUrl(APP_INFO['MiniGGApi']['Weapons'] . urlencode($msgContent));
                    $resJson = json_decode($resArray);
                    if (isset ($resJson->errcode)) {
                        $ret = $resJson->errmsg;
                    } else {
                        if (isset ($resJson->costs->ascend6)) {
                            $level = "&stats=90";
                        } else {
                            $level = "&stats=70";
                        }
                        $levelArray = $this->requestUrl(APP_INFO['MiniGGApi']['Weapons'] . urlencode($msgContent) . $level);
                        $levelJson = json_decode($levelArray);
                        if (isset ($resJson->name)) {
                            $ret = "【名字】：" . $resJson->name . "\n";
                            $ret .= "================\n";
                            $ret .= "【类型】：" . $resJson->rarity . "星" . $resJson->weapontype . "\n";
                            $ret .= "【介绍】：" . $resJson->description . "\n";
                            $ret .= "【基础/满级攻击力】：" . $resJson->baseatk . "/" . round($levelJson->attack, 2) . "\n";
                            if ($resJson->substat !== "") {
                                $ret .= "【突破加成】：" . $resJson->substat . "\n";
                                $ret .= "【基础/满级加成】：" . $resJson->subvalue . "/" . round(($levelJson->specialized * 100), 2) . "%\n";
                                $ret .= "【" . $resJson->effectname . "】：" . $resJson->effect . "\n";
                            }
                        } else {
                            $ret = $this->retrtrim($resJson);
                        }
                    }
                    if (FRAME_ID == 50000) {
                        $ret = str_replace("\n", "\n\n", $ret);
                        $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "markdown_msg";
                    }
                    break;
                /**
                 * 天赋查询
                 */
                case '天赋':
                    $resArray = $this->requestUrl(APP_INFO['MiniGGApi']['Talents'] . urlencode($msgContent));
                    $resJson = json_decode($resArray);
                    if (isset ($resJson->errcode)) {
                        $ret = $resJson->errmsg;
                    } elseif (isset ($resJson->name)) {
                        $ret = "**【名字】：" . $resJson->name . "**\n";
                        $ret .= "================\n";
                        $ret .= "【" . $resJson->combat1->name . "】：\n\n" . $resJson->combat1->info . "\n";
                        $ret .= "【" . $resJson->combat2->name . "】：" . $resJson->combat2->info . "\n";
                        $ret .= "【" . $resJson->combat3->name . "】：" . $resJson->combat3->info . "\n";
                        if (isset ($resJson->combatsp)) {
                            $ret .= "\n【" . $resJson->combatsp->name . "】：" . $resJson->combatsp->info . "\n";
                        }
                        $ret .= "【" . $resJson->passive1->name . "】：" . $resJson->passive1->info . "\n";
                        $ret .= "【" . $resJson->passive2->name . "】：" . $resJson->passive2->info . "\n";
                        $ret .= "【" . $resJson->passive3->name . "】：" . $resJson->passive3->info . "\n";

                        if (FRAME_ID == 50000) {
                            $ret = str_replace("\n", "\n\n", $ret);
                            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "markdown_msg";
                        }
                    } else {
                        $ret = $this->retrtrim($resJson);
                    }
                    break;
                /**
                 * 命座查询
                 */
                case '命座':
                case '命之座':
                    $resArray = $this->requestUrl(APP_INFO['MiniGGApi']['Constellations'] . urlencode($msgContent));
                    $resJson = json_decode($resArray);
                    if (isset ($resJson->errcode)) {
                        $ret = $resJson->errmsg;
                    } elseif (isset ($resJson->name)) {
                        $ret = "【名字】：" . $resJson->name . "\n";
                        $ret .= $resJson->c1->effect . "\n";
                        $ret .= $resJson->c2->effect . "\n";
                        $ret .= $resJson->c3->effect . "\n";
                        $ret .= $resJson->c4->effect . "\n";
                        $ret .= $resJson->c5->effect . "\n";
                        $ret .= $resJson->c6->effect . "\n";
                        if (FRAME_ID == 50000) {
                            $ret = str_replace("\n", "\n\n", $ret);
                            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "markdown_msg";
                        }
                    }
                    break;
                /**
                 * 食物查询
                 */
                case '料理':
                case '食物':
                    $resArray = $this->requestUrl(APP_INFO['MiniGGApi']['Foods'] . urlencode($msgContent));
                    $resJson = json_decode($resArray);
                    if (isset ($resJson->errcode)) {
                        $ret = $resJson->errmsg;
                    } elseif (isset ($resJson->name)) {
                        $ret = "【名字】：" . $resJson->name . "\n";
                        $ret .= "【类型】：" . $resJson->rarity . "星" . $resJson->foodfilter . "\n";
                        $ret .= "【效果】：" . $resJson->effect . "\n";
                        $ret .= "【描述】：" . $resJson->description . "\n";
                        if (FRAME_ID == 50000) {
                            $ret = str_replace("\n", "\n\n", $ret);
                            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "markdown_msg";
                        }
                    } else {
                        $ret = $this->retrtrim($resJson);
                    }
                    break;
                /**
                 * 原魔查询
                 */
                case '原魔':
                case '怪物':
                    $resArray = $this->requestUrl(APP_INFO['MiniGGApi']['Enemies'] . urlencode($msgContent));
                    $resJson = json_decode($resArray);
                    if (isset ($resJson->errcode)) {
                        $ret = $resJson->errmsg;
                    } elseif (isset ($resJson->name)) {
                        $ret = "【名字】：" . $resJson->name . "\n";
                        if ($resJson->specialname) "【别名】：" . $resJson->specialname . "\n";
                        $ret .= "【阵营】：" . $resJson->category . "\n";
                        $ret .= "【背景故事】：" . $resJson->description . "\n";
                        if (FRAME_ID == 50000) {
                            $ret = str_replace("\n", "\n\n", $ret);
                            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "markdown_msg";
                        }
                    } else {
                        $ret = $this->retrtrim($resJson);
                    }
                    break;
                /**
                 * 副本查询
                 */
                case '副本':
                    $resArray = $this->requestUrl(APP_INFO['MiniGGApi']['Domains'] . urlencode($msgContent));
                    $resJson = json_decode($resArray);
                    if (isset ($resJson->errcode)) {
                        $ret = $resJson->errmsg;
                    } elseif (isset ($resJson->name)) {
                        $ret = "【名字】：" . $resJson->name . "\n";
                        $ret .= "(可在副本名字后面加1-4查看不同等级的副本)";
                        $ret .= "【区域】：" . $resJson->region . "-" . $resJson->domainentrance . "\n";
                        $ret .= "【类型】：" . $resJson->domaintype . "\n";
                        $ret .= "【开放时间】：" . $this->retrtrim($resJson->daysofweek) . "\n";
                        $ret .= "【推荐等级】：" . $resJson->recommendedlevel . "\n";
                        $ret .= "【推荐元素】：" . $this->retrtrim($resJson->recommendedelements) . "\n";
                        $ret .= "【可能的掉落物】：";
                        $tmpNum = 0;
                        foreach ($resJson->rewardpreview as $resArray) {
                            if ($tmpNum >= 3) {
                                $rewardpreview .= $resArray->name;
                                $rewardpreview .= "、";
                            }
                            $tmpNum++;
                        }
                        $ret .= rtrim($rewardpreview, "、");
                        $ret .= "\n";
                        $ret .= "【地脉异常】：" . $this->retrtrim($resJson->disorder) . "\n";
                        $ret .= "【登场的原魔】：" . $this->retrtrim($resJson->monsterlist);
                        $ret .= "【背景故事】：" . $resJson->description . "\n";
                        if (FRAME_ID == 50000) {
                            $ret = str_replace("\n", "\n\n", $ret);
                            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "markdown_msg";
                        }
                    } else {
                        $ret = $this->retrtrim($resJson);
                    }
                    break;
                /**
                 * 圣遗物查询
                 */
                case '圣遗物':
                    $resArray = $this->requestUrl(APP_INFO['MiniGGApi']['Artifacts'] . urlencode($msgContent));
                    $resJson = json_decode($resArray);
                    if (isset ($resJson->errcode)) {
                        $ret = $resJson->errmsg;
                    } elseif (isset ($resJson->name)) {
                        $ret = "【名字】：" . $resJson->name . "\n";
                        $ret .= "【稀有度】：" . $this->retrtrim($resJson->rarity) . "\n";
                        $epc = "2pc";
                        $spc = "4pc";
                        $ret .= "【2件套效果】：" . $resJson->$epc . "\n";
                        $ret .= "【4件套效果】：" . $resJson->$spc . "\n";
                        if (FRAME_ID == 50000) {
                            $ret = str_replace("\n", "\n\n", $ret);
                            $GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "markdown_msg";
                        }
                    } else {
                        $ret = $this->retrtrim($resJson);
                    }
                    break;
            }
        } else {
            $ret = $this->appCommandErrorMsg($matchValue);
        }
        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }
}