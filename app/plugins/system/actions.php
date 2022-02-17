<?php
/**
 * 这是一个示例插件
 *
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class system_actions extends app {
	function __construct(&$appManager) {
		//注册这个插件
		//第一个参数是钩子的名称
		//第二个参数是appManager的引用
		//第三个是插件所执行的方法
		$appManager->register('plugin', $this, 'EventFun');
		$this->linkRedis();
	}
	//解析函数的参数是appManager的引用
	function EventFun($msg) {
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
		} elseif (preg_match("/^(功能|菜单|帮助)\$/", $msgContent)) {
			$ret = "> 派蒙的百宝箱\n";
			$ret .= "\n";
			$ret .= "> 娱乐功能\n";
			$ret .= "> 原神功能\n";
			$ret .= "> 系统功能";
		} elseif (preg_match("/^(娱乐功能|游戏相关|原神功能|系统功能)\$/", $msgContent, $msgMatch)) {
			$matchValue = $msgMatch[0];
			$menuArr['娱乐功能'] = "工具";
			$menuArr['原神功能'] = "原神";
			$menuArr['系统功能'] = "系统";
			$ret = $this->getPluginsInfo($menuArr[$matchValue]);
		} elseif (FRAME_ID == 10000 && $msgContent == "登录") {
			$ret = $this->getMpqLoginQrcode($msgSender);
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
			} else {
				return;
			}
			//获取群组信息
		} elseif ($msgContent == "系统状态") {
			$ret = $this->getSystemInfo();
			//获取系统状态
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
					$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = $nowMsgType;
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
			} elseif ($msgContent == "清除缓存") {
				$ret = $this->cleanSystemCache(APP_DIR_CACHE);
				//清除系统缓存
			}
		}
		//管理员
		$this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
		//$this->appGcInterconnected($msgRobot, $msgType, $msgSource, $msgSender, $ret);
	}
	/**
   * 
   * 清除系统缓存
   * 
   * @link https://www.cnblogs.com/itbsl/p/10430718.html
   */
	function cleanSystemCache($aDir) {
		if (is_dir($aDir)) {
			$dirs = scandir($aDir);
			$dirList = array();
			foreach ($dirs as $dir) {
				if ($dir != '.' && $dir != '..') {
					$dirList[] = $dir;
					$sonDir = $aDir . '/' . $dir;
					if (is_dir($sonDir)) {
						$this->cleanSystemCache($sonDir);
						rmdir($sonDir);
					} else {
						unlink($sonDir);
					}
				}
			}
			rmdir($aDir);
		}
		$ret = "已清除缓存:\n";
		$ret .= json_encode($dirList);
		return $ret;
	}
	/**
   * 
   * appNode 控制面板自带开发文档，获取系统信息，配合 F12 使用
   * 
   * @link https://www.kancloud.cn/appnode/apidoc/504312
   * @link http://apidoc.cn/explore
   */
	function getSystemInfo() {
		$key = APP_INFO['authInfo'][1002][0];
		$host = "http://127.0.0.1:8899";
		/**
     * 
     * 获取系统信息
     * 
     */
		$url_1 = "api_action=Status.Overview&api_agent_app=sysinfo&api_nodeid=1&api_nonce=" . $this->getRandomString(16) . "&api_timestamp=" . TIME_T;
		$sign_1 = hash_hmac("md5", $url_1, $key);
		$newUrl_1 = $host . "/?{$url_1}&api_sign=" . $sign_1;
		$reqRet_1 = $this->requestUrl($newUrl_1);
		$resJson_1 = json_decode($reqRet_1);
		$resData_1 = $resJson_1->DATA;
		$CPUUseRate = $resData_1->CPUUseRate;
		$UpTime = $resData_1->UpTime;
		$LoadAvg = $resData_1->LoadAvg;
		$MemInfo = $resData_1->MemInfo;
		$Disks = $resData_1->Disks[0];
		/**
     * 
     * 获取网络信息
     * 
     */
		$url_2 = "api_action=Network.Info&api_agent_app=sysinfo&api_nodeid=1&api_nonce=" . $this->getRandomString(16) . "&api_timestamp=" . TIME_T;
		$sign_2 = hash_hmac("md5", $url_2, $key);
		$newUrl_2 = $host . "/?{$url_2}&api_sign=" . $sign_2;
		$reqRet_2 = $this->requestUrl($newUrl_2);
		$reqRet_2 = str_replace("K/", " K/", $reqRet_2);
		$reqRet_2 = str_replace("B/", " B/", $reqRet_2);
		$reqRet_2 = str_replace("M/", " M/", $reqRet_2);
		$reqRet_2 = str_replace("G", " G", $reqRet_2);
		$resJson_2 = json_decode($reqRet_2);
		$resData_2 = $resJson_2->DATA;
		$NetworkCards = $resData_2->NetworkCards[0];
		$ret = "SDK/PHP:" . CONFIG_VERSION . " / " . PHP_VERSION . "\n";
		$ret .= "运行时间:" . (floor($UpTime->Total * 100 / 86400) / 100) . " 天\n";
		$ret .= "内存使用:" . str_replace("G", "", $MemInfo->MemUsed) . " / " . str_replace("G", "", $MemInfo->MemTotal) . " G\n";
		$ret .= "存储空间:" . str_replace("G", "", $Disks->Used) . " / " . str_replace("G", "", $Disks->Total) . " G\n";
		$ret .= "当前负载:" . ($CPUUseRate * 100) .  " % CPU使用率:" . $LoadAvg->Last1MinRate . " / " . $LoadAvg->Last5MinRate . " / " . $LoadAvg->Last15MinRate . " %\n";
		$ret .= "网络接口:↑ " . $NetworkCards->TXSpeed . " | " . $NetworkCards->TX . " / ↓ " . $NetworkCards->RXSpeed . " | " . $NetworkCards->RX . "\n";
		$ret .= "操作系统:" . php_uname();
		return $ret;
	}
	/**
   * 
   * 菜单
   * 
   */
	function getPluginsInfo($menuType = NULL) {
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
		//$allCommand .= "以下为所有人命令:\n";
		$allCommand .= $commonCommand;
		$allCommand .= "-----\n";
		//$allCommand .= "以下为管理员命令:\n";
		//$allCommand .= $adminCommand;
		//$allCommand .= "-----\n";
		$allCommand .= "发送【功能】返回【主菜单】\n";
		$allCommand .= "-----\n";
		$allCommand .= "插件/钩子/调用:{$pluginsNum}/{$triggerNum}/{$nowAllTimes}w";
		$this->redisSet("plugins-allTrigger-" . FRAME_ID, $allTrigger);
		$this->redisSet("plugins-allKeywords-" . FRAME_ID, "/^({$allKeywords})/i");
		return $allCommand;
	}
	/**
   * 
   * 获取登录二维码
   * 
   */
	function getMpqLoginQrcode($msgSender) {
		$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "api_msg";
		;
		$loginQrCode = $this->requestApiByMPQ("Api_GetLoginQRCode()");
		$reqRet = $this->appDownloadImg($msgSender, "mpqLoginQrcode", "mpqLoginQrcode", "mpqLoginQrcode", NULL, base64_decode($loginQrCode));
		$img = $reqRet['url'];
		$ret = "请打开链接，使用摄像头扫码，有效期很短\n";
		$ret .= $img;
		$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = NULL;
		return $ret;
	}
	/**
   * 
   * 添加黑名单
   * 
   */
	function addBlockList($msgSender) {
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
	function deleteBlockList($msgSender) {
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
	function getBlockList() {
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
	function appInviteInGroup($msgRobot, $msgSource, $msgSender) {
		if (FRAME_ID == 10000) {
			$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = "api_msg";
			$this->requestApiByMPQ("Api_JoinGroup('{$msgRobot}','{$msgSource}','')");
			$GLOBALS['msgExt'][$GLOBALS['msgGc']]['msgType'] = NULL;
		} elseif (FRAME_ID == 20000) {
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
	function appGcInterconnected($msgRobot, $msgType, $msgSource, $msgSender, $msgContent) {
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