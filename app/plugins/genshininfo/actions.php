<?php
/**
 * 这是一个示例插件
 *
 * 需要注意的几个默认规则:
 * 1.本插件类的文件名必须是action
 * 2.插件类的名称必须是{插件名_actions}
 */
class genshininfo_actions extends app {
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
		$msgContent = strtoupper($msgContent);
		if (preg_match("/角色/", $msgContent, $msgMatch)) {
			$matchValue = $msgMatch[0];
			$msgContent = str_replace($matchValue, "", $msgContent);
			$charapi = "https://info.minigg.cn/characters?query=" . urlencode($msgContent);
			if (preg_match("/\d{1,2}/", $msgContent, $levelMatch)) {
				$levelValue = $levelMatch[0];
				$msgContent = str_replace($levelValue, "", $msgContent);
				$charapi .= "&stats=" . $levelValue;
			}
			$res = json_decode(file_get_contents($charapi), true);
			$ret = $res['title'] . " - " . $res['fullname'];
			$ret .= "\n";
			$ret .= "【稀有度】：" . $res['rarity'] . "星";
			$ret .= "\n";
			$ret .= "【武器】：" . $res['weapontype'];
			$ret .= "\n";
			$ret .= "【元素】：" . $res['element'] ."元素";
			$ret .= "\n";
			$ret .= "【突破加成】：" . $res['substat'];
			$ret .= "\n";
			$ret .= "【生日】：" . $res['birthday'];
			$ret .= "\n";
			$ret .= "【命之座】：" . $res['constellation'];
			$ret .= "\n";
			$ret .= "【CV】：中：" . $res['cv']['chinese'] . "/日：" . $res['cv']['japanese'];
			$ret .= "\n";
			$ret .= "【介绍】：" . $res['description'];
		}
		$this->appSend($msgRobot, $msgType, $msgSource, $msgSender, $ret);
	}
}