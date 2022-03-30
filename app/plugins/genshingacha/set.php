<?php
//error_reporting(E_ALL^E_WARNING);
$userid = $_REQUEST['userid'];
$pray = $_REQUEST['pray'];
$index = $_REQUEST['index'];
$token = $_REQUEST['token'];
//if(!$userid || !$pray || !$index || !$token){exit ("0");}
$t = time();
for ($i = 0; $i < 15; ++$i) {
    $s = $t - $i . "y8h8tqUAEDzyR6xZQKX6Ak";
    $c[] = md5($s);
}
if (!in_array($token, $c)) {
    echo '<script type="text/javascript">window.alert("链接已失效，请重新发送命令“设置卡池”");</script>';
    exit ("链接已失效，请重新发送命令\"设置卡池\"");
}
$redis = new Redis();
if (!$redis->connect('127.0.0.1', 6379)) {
    exit ("Database connect error");
}
$set = '{"pray":"' . $pray . '","index": "' . $index . '"}';
$redis->set("MiniGG-Gacha-Set-" . $userid, $set);
echo '<script type="text/javascript">window.alert("卡池设置成功");</script>';
exit("卡池设置成功");