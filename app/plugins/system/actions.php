<?php

/**
 * 这是一个示例插件
 *
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class system_actions extends app
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

        $this->appSetMsgType();
        $msgContent = str_replace(" ", "", $msgContent);

        if (preg_match("/加群/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);

            if (FRAME_ID == 10000 && $msgType == 1 && in_array($msgSender, CONFIG_ADMIN)) {
                if (!$msgContent) return;

                $this->appInviteInGroup($msgRobot, $msgContent, $msgSender);
            } elseif (FRAME_ID == 20000 && $msgType == 100) {
                $botInfo = APP_INFO['botInfo']['WSLY'];

                $this->appInviteInGroup($msgRobot, $botInfo['inviteInGroup'], $msgSender);
            } else {
                $ret = $this->appCommandErrorMsg($matchValue);
            }
        } elseif (preg_match("/订阅/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];
            $msgContent = str_replace($matchValue, "", $msgContent);

            $appInfo = APP_INFO;

            if (!$msgContent || $msgContent == "列表") {
                $ret = $this->getRssInfo($msgContent);
            } else {
                if (FRAME_ID == 70000) {
                    $nowMsg = json_decode($msgOrigMsg);
                    $nowData = $nowMsg->d;
                    $roles = $nowData->member->roles;
                } else {
                    $roles = array();
                }

                /**
                 *
                 * QQ频道 2:管理员 4:频道主 5:子频道管理员
                 *
                 */
                if (in_array($msgSender, CONFIG_ADMIN) || in_array(2, $roles) || in_array(4, $roles) || in_array(5, $roles)) {
                    $ret = $this->updateRssInfo($msgContent);
                } else {
                    $ret = $appInfo['codeInfo'][1000];
                }
            }
        } elseif (preg_match("/^(主菜单)$/", $msgContent, $msgMatch)) {
            $ret .= "> 原神功能\n";
            $ret .= "> 王者功能\n";
            $ret .= "-----\n";
            $ret .= "输入【上方分组】，查看详细列表\n";
        } elseif (preg_match("/^(((原神|王者(荣耀)?|系统)(功能|相关)?)|帮助|功能|菜单)$/", $msgContent, $msgMatch)) {
            $matchValue = $msgMatch[0];

            $menuArr['原神'] = "原神";
            $menuArr['王者荣耀'] = "王者荣耀";
            $menuArr['系统功能'] = "系统";

            $ret = $this->getPluginsInfo($menuArr[$matchValue]);
        } elseif ($msgContent == "群组") {
            if (in_array(FRAME_ID, array(10000, 20000))) {
                $ret = "机器人:{$msgRobot} 当前群号:{$msgSource} 您的账号:" . $msgSender;
            } elseif (FRAME_ID == 50000) {
                $nowMsg = json_decode($msgOrigMsg);
                $nowData = $nowMsg->data[0];

                $ret = "机器人:{$msgRobot} 当前频道:{$msgSource} 子频道:" . $nowData->target_id  . " 您的账号:{$msgSender} ts:" . $nowData->ts . " nonce:" . $nowData->nonce;
            } elseif (FRAME_ID == 60000) {
                $nowMsg = json_decode($msgOrigMsg);

                $ret = "机器人:{$msgRobot} 当前频道:{$msgSource} 子频道:" . $nowMsg->channel_id . " 您的账号:" . $msgSender;
            } elseif (FRAME_ID == 70000) {
                $nowMsg = json_decode($msgOrigMsg);

                $ret = "机器人:{$msgRobot} 当前频道:{$msgSource} 子频道:" . $nowMsg->d->channel_id . " 您的账号:" . $msgSender;
            } elseif (FRAME_ID == 80000) {
                $nowMsg = json_decode($msgOrigMsg);

                $ret = "机器人:{$msgRobot} 当前频道:{$msgSource} 子频道:" . $nowMsg->chatRoomId . " 您的账号:" . $msgSender;
            } else {
                return;
            }

            //获取群组信息
        } elseif ($msgContent == "系统状态") {
            $ret = "咕咕咕~";
        } elseif ($msgContent == "频道数据") {
            $ret = "咕咕咕~";
        } elseif (in_array($msgSender, CONFIG_ADMIN)) {
            if (preg_match("/复述/", $msgContent, $msgMatch)) {
                $matchValue = $msgMatch[0];
                $msgContent = str_replace($matchValue, "", $msgContent);

                if ($msgContent) {
                    if (strpos($msgContent, '{"') > -1) {
                        $nowMsgType = "json_msg";
                    } elseif (strpos($msgContent, 'xml') > -1) {
                        $nowMsgType = "xml_msg";
                    } else {
                        $nowMsgType = NULL;
                    }

                    $this->appSetMsgType($nowMsgType);

                    $ret = $msgContent;
                } else {
                    $ret = $this->appCommandErrorMsg($matchValue);
                }
            } elseif (preg_match("/拉黑|加黑/", $msgContent, $msgMatch)) {
                $matchValue = $msgMatch[0];
                $msgContent = str_replace($matchValue, "", $msgContent);

                if ($msgContent) {
                    $ret = $this->addBlockList($msgContent);
                } else {
                    $ret = $this->appCommandErrorMsg($matchValue);
                }

                //添加黑名单
            } elseif (preg_match("/删黑/", $msgContent, $msgMatch)) {
                $matchValue = $msgMatch[0];
                $msgContent = str_replace($matchValue, "", $msgContent);

                if ($msgContent) {
                    $ret = $this->deleteBlockList($msgContent);
                } else {
                    $ret = $this->appCommandErrorMsg($matchValue);
                }

                //删除黑名单
            } elseif ($msgContent == "黑名单") {
                $ret = $this->getBlockList();

                //获取黑名单
            } elseif ($msgContent == "清除系统缓存") {
                $this->cleanRedisCache("plugins-slip-*");

                $ret = $this->cleanAppCache(APP_DIR_CACHE);

                //清除系统缓存
            } elseif ($msgContent == "清除网站缓存") {
                $ret = $this->cleanWebCache();

                //清除系统缓存
            }
        } elseif (FRAME_ID == 10000 && $msgContent == "登录") {
            $ret = $this->getMpqLoginQrcode($msgSender);
        } elseif (FRAME_ID == 20000 && preg_match("/http/", $msgContent, $msgMatch)) {
            $msgFileUrl = $msg['MsgFileUrl'] ?? NULL;
            if ($msgType == 100 && $msgFileUrl) {
                $ret = $this->appGetShortUrl($msgContent);
            }
        }

        $this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);

        //$this->appGcInterconnected($msgRobot, $msgType, $msgSource, $msgSender, $ret);
    }

    /**
     *
     * 获取订阅列表
     *
     */
    function getRssInfo($msgContent)
    {
        $reqRet = $this->requestUrl(
            APP_API_ROBOT . "?type=rss&frameId=" . FRAME_ID . "&aid=0",
            "robotUin=" . $GLOBALS['msgRobot'] . "&msgSource=" . $GLOBALS['msgGc'],
            array(
                "Referer: https://bot.91m.top",
                DEFAULT_UA
            )
        );
        $resJson = json_decode($reqRet);
        $resData = $resJson->data;
        $resStatus = $resJson->status;
        $resResult = $resData->result;
        $resRssInfo = $resData->rssInfo;
        $resArr = $resResult->rows ?? NULL;
        $resArrNum = count($resArr);

        if ($resStatus->code != 200) {
            $ret = $resStatus->msg;

            return $ret;
        }

        $rssKey = array_column($resRssInfo, "name");

        $ret = "";

        if (!$msgContent || $resArrNum == 0) {
            $ret .= implode(",", $rssKey) . "\n";
            $ret .= "-----\n";
            $ret .= "快来订阅吧，订阅<项目名>，示例:订阅王者公告";

            return $ret;
        }

        for ($rss_i = 0; $rss_i < $resArrNum; $rss_i++) {
            $forList = $resArr[$rss_i];

            $rssId = $forList->rssId;
            $switch = $forList->switch;

            $switch == 1 ? $switchText = "已订阅" : $switchText = "x";

            $ret .= "{$rssId} {$switchText}\n";
        }
        $ret .= "-----\n";
        $ret .= "更多订阅即将上线 ;D";

        return $ret;
    }

    /**
     *
     * 更新订阅
     *
     */
    function updateRssInfo($msgContent)
    {
        $reqRet = $this->requestUrl(
            APP_API_ROBOT . "?type=rss&frameId=" . FRAME_ID . "&aid=1",
            "robotUin=" . $GLOBALS['msgRobot'] . "&msgSource=" . $GLOBALS['msgGc'] . "&rssId=" . $msgContent,
            array(
                "Referer: https://bot.91m.top",
                DEFAULT_UA
            )
        );
        $resJson = json_decode($reqRet);
        $resData = $resJson->data;
        $resStatus = $resJson->status;

        if ($resStatus->code != 200) {
            $ret = $resStatus->msg;
        } else {
            $ret = $resData;
        }

        return $ret;
    }

    /**
     *
     * 清除系统缓存
     *
     * @link https://www.cnblogs.com/itbsl/p/10430718.html
     */
    function cleanAppCache($aDir)
    {
        if (is_dir($aDir)) {
            $dirs = scandir($aDir);

            $dirList = array();
            foreach ($dirs as $dir) {
                if ($dir != '.' && $dir != '..') {
                    $dirList[] = $dir;

                    $sonDir = $aDir . '/' . $dir;

                    if (is_dir($sonDir)) {
                        $this->cleanAppCache($sonDir);

                        rmdir($sonDir);
                    } else {
                        unlink($sonDir);
                    }
                }
            }

            //rmdir($aDir);
        }

        $ret = "清除系统缓存:\n";
        $ret .= implode(",", $dirList);

        return $ret;
    }

    /**
     *
     * 清除网站缓存
     *
     */
    function cleanWebCache()
    {
        $keyList = array("*-getHero*", "*-getAppInfo-*", "*-getAppHome-*", "*-getRanking-*", "*-gcIsOverdue-*", "plugins-generateImgInfo-*");
        for ($k_i = 0; $k_i < count($keyList); $k_i++) {
            $forList = $keyList[$k_i];

            $this->cleanRedisCache($forList);
        }

        $ret = "清除网站缓存:\n";
        $ret .= implode(",", $keyList);

        return $ret;
    }

    /**
     *
     * 清除 Redis 缓存
     *
     */
    function cleanRedisCache($key)
    {
        $keyList = $this->redisKeys($key);

        foreach ($keyList as $value) {
            $this->redisDel($value);
        }
    }

    /**
     *
     * 菜单
     *
     */
    function getPluginsInfo($menuType = NULL)
    {
        $plugins = $this->getPlugins();
        $pluginsNum = count($plugins);

        $commandIndex = 0;
        $allTimes = 0;
        $allCommand = "";
        $allTrigger = array();
        $allKeywords = "";
        $adminCommand = "";
        $commonCommand = "";
        foreach ($plugins as $plugin) {
            $pName = $plugin['name'];
            $pPath = $plugin['path'];

            $configPath = $pPath . "/config.json";
            $reqRet = file_get_contents($configPath);
            $resJson = json_decode($reqRet);

            $pluginSwitch = $resJson->switch;

            if (!$pluginSwitch) continue;

            $pluginType = $resJson->type;
            $pluginName = $resJson->name;
            $pluginDesc = $resJson->desc;
            $pluginFrame = $resJson->trigger->frame;
            //$resJson->switch == true ? $pluginSwitch = "✔︎" : $pluginSwitch = "✗";

            if ($pluginFrame != [] && !in_array(FRAME_ID, $pluginFrame)) continue;

            $pluginCommand =  $resJson->trigger->command;
            foreach ($pluginCommand as $commandList) {
                //$n = $commandIndex + 1;

                $keywords = $commandList->keywords;
                $keywordsArr = explode("|", $keywords);
                $desc = $commandList->desc;
                $demo = $commandList->demo ?? NULL;
                $show = $commandList->show ?? true;

                $keywordsArr_1 = $keywordsArr[0];

                if ($keywordsArr_1 == "(.*?)") continue;
                //跳过全部触发

                $allKeywords .= $keywords . "|";

                for ($keywordsArr_i = 0; $keywordsArr_i < count($keywordsArr); $keywordsArr_i++) {
                    $forList = $keywordsArr[$keywordsArr_i];

                    $times = (int) $this->redisGet("plugins-analysis-" . $forList);
                    $allTimes = $allTimes + $times;

                    if ($demo) {
                        $this->redisSet("plugins-keywordsInfo-" . $forList, $demo);
                    }

                    $allTrigger[$forList] = $plugin;
                }

                if (!$show) continue;

                $command = $keywordsArr_1 . " - {$desc}\n";

                if ($menuType && !in_array($menuType, $pluginType)) continue;
                //跳过不是一个类型的

                if (strpos($desc, "[管]") > -1) {
                    //$adminCommand .= $command;
                } else {
                    $commonCommand .= $command;
                }

                $commandIndex++;
            }
            //$allCommand .= $commonCommand . $adminCommand;
            //$ret .= "[{$pluginSwitch}]{$pluginName} {$pluginDesc}\n";
        }
        $allKeywords = substr($allKeywords, 0, strlen($allKeywords) - 1);

        $adminCommand = str_replace("[管]", "", $adminCommand);

        $triggerNum = count($allTrigger);
        $nowAllTimes = floor(($allTimes / 10000) * 100) / 100;
        $allCommand .= $adminCommand;
        //$allCommand .= "以下为所有人命令:\n";
        $allCommand .= $commonCommand;
        $allCommand .= "-----\n";
        $allCommand .= "上善若水小灰灰\n";
        $allCommand .= "-----\n";
        $allCommand .= "插件/钩子/调用:{$pluginsNum}/{$triggerNum}/{$nowAllTimes}w";

        $this->redisSet("plugins-allTrigger-" . FRAME_ID, $allTrigger);
        $this->redisSet("plugins-allKeywords-" . FRAME_ID, "/^(\#|\/|\!)?({$allKeywords})/i");

        return $allCommand;
    }

    /**
     *
     * 获取登录二维码
     *
     */
    function getMpqLoginQrcode($msgSender)
    {
        $this->appSetMsgType("api_msg");

        $loginQrCode = $this->requestApiByMPQ("Api_GetLoginQRCode()");
        $reqRet = $this->appDownloadImg($msgSender, "mpqLoginQrcode", "mpqLoginQrcode", "mpqLoginQrcode", NULL, base64_decode($loginQrCode));

        $img = $reqRet['url'];

        $ret = "请打开链接，使用摄像头扫码，有效期很短\n";
        $ret .= $img;

        $this->appSetMsgType();

        return $ret;
    }

    /**
     *
     * 添加黑名单
     *
     */
    function addBlockList($msgSender)
    {
        $sender = NULL;

        if (FRAME_ID == 10000) {
            $sender = $this->appGetSubstr($msgSender, "[@", "]");
        } elseif (FRAME_ID == 20000) {
            $sender = $this->appGetSubstr($msgSender, "wxid=", "]");
        } elseif (FRAME_ID == 60000) {
            $sender = $this->appGetSubstr($msgSender, "qq=", "]");
        } elseif (FRAME_ID == 70000) {
            $sender = $this->appGetSubstr($msgSender, "<@!", ">");
        } else {
            return;
        }

        if (strlen($sender) > 25 || in_array($sender, CONFIG_ADMIN) || in_array($sender, CONFIG_ROBOT)) {
            return;
        } elseif ($sender) {
            $file_path = APP_DIR_CONFIG . "user.blockList.txt";

            $allBlockList = file_get_contents($file_path);
            $allBlockList ? $blockSender = $allBlockList . "," . $sender : $blockSender = $sender;

            if (strpos($allBlockList, $sender) > -1) {
                $ret = "已存在";
            } else {
                file_put_contents($file_path, $blockSender);

                $ret = "添加成功";
            }
        } else {
            $ret = "添加黑名单异常";
        }

        return $ret;
    }

    /**
     *
     * 删除黑名单
     *
     */
    function deleteBlockList($msgSender)
    {
        $file_path = APP_DIR_CONFIG . "user.blockList.txt";

        $allBlockList = file_get_contents($file_path);
        $newBlockList = str_replace("," . $msgSender, "", $allBlockList);
        $newBlockList = str_replace($msgSender, "", $newBlockList);

        file_put_contents($file_path, $newBlockList);

        $ret = "删除成功";

        return $ret;
    }

    /**
     *
     * 获取黑名单
     *
     */
    function getBlockList()
    {
        $file_path = APP_DIR_CONFIG . "user.blockList.txt";

        $allBlockList = file_get_contents($file_path);
        $allBlockList ? $blockNum = count(explode(",", $allBlockList)) : $blockNum = 0;

        $ret = "列表如下:\n";
        $ret .= $allBlockList . "\n";
        $ret .= "共计【{$blockNum}】个";

        return $ret;
    }

    /**
     *
     * 加群
     *
     */
    function appInviteInGroup($msgRobot, $msgSource, $msgSender)
    {
        if (FRAME_ID == 10000) {
            $this->appSetMsgType("api_msg");

            $this->requestApiByMPQ("Api_JoinGroup('{$msgRobot}','{$msgSource}','')");

            $this->appSetMsgType();
        } elseif (FRAME_ID == 20000) {
            $this->appSetMsgType("json_msg");

            $newData = array();
            $newData['type'] = 311;
            $newData['robot_wxid'] = $msgRobot;
            $newData['group_wxid'] = $msgSource;
            $newData['friend_wxid'] = $msgSender;

            $this->requestApiByWSLY(json_encode($newData));
        } else {
            return;
        }
    }

    /**
     *
     * 群互联
     *
     */
    function appGcInterconnected($msgRobot, $msgType, $msgSource, $msgSender, $msgContent)
    {
        $interconnected_1 = array();
        $interconnected_2 = array();

        $interconnectedSearch_1 = array_search($msgSource, $interconnected_1);
        $interconnectedSearch_2 = array_search($msgSource, $interconnected_2);

        $group = NULL;
        if ($interconnectedSearch_1 > -1) {
            $group = $interconnected_2[$interconnectedSearch_1];
        }

        if ($interconnectedSearch_2 > -1) {
            $group = $interconnected_1[$interconnectedSearch_2];
        }

        if ($group && !strpos($msgContent, '{"') && !strpos($msgContent, 'xml')) {
            $ret = "群({$group})成员({$msgSender}):\n" . $msgContent;

            $this->appSend($msgRobot, $msgType, $group, $msgSender, $ret);
        }
    }
}
