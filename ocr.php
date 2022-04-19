<?php
header("Content-type:application/json;charset=utf-8");
$ocrApi = "https://aip.baidubce.com/rest/2.0/ocr/v1/general_basic";
$reqRet = '{"words_result":[{"words":"真珠之笼"},{"words":"空之杯"},{"words":"冰元素伤害加成·46.6%"},{"words":"★★★★★"},{"words":"+20"},{"words":"防御力+7.3%"},{"words":"暴击伤害+18.7%"},{"words":"·攻击力+31"},{"words":"·暴击率+9.3%"},{"words":"海染砗磲：()"},{"words":"⑦2件套：治疗加成提高15%。"},{"words":"⑦4件套：装备此圣遗物套装的"},{"words":"角色对队伍中的角色进行治"},{"words":"疗时，将产生持续3秒的海染"},{"words":"泡沫，记录治疗的生命值回"},{"words":"复量（包括溢出值）。持续时"},{"words":"间结束时，海染泡沫将会爆"},{"words":"炸，对周围的敌人造成90%"},{"words":"甘雨已装备"},{"words":"卸下"},{"words":"强化"}],"words_result_num":21,"log_id":1513871613596973311}';
//$reqRet = requestUrl($ocrApi, $_REQUEST);
$reqJson = json_decode($reqRet, true);
$resRet = $reqJson['words_result'];
$ret['name'] = $resRet[0]['words'];
$ret['pos'] = $resRet[1]['words'];
$ret['star'] = strlen($resRet[3]['words']) / 3;
preg_match_all('/\d+/', $resRet[4]['words'], $resLevel);
$ret['level'] = implode($resLevel[0]);
$resArtifact = wordsDecode($resRet[2]['words'])['name'];
$resValue = wordsDecode($resRet[2]['words'])['value'];
$main_item['type'] = nameToType($resArtifact);
$main_item['name'] = $resArtifact;
$main_item['value'] = $resValue;
$ret['main_item'] = $main_item;
$numArtifact = 5;
while (preg_match("/暴击率|暴击伤害|生命值|攻击力|防御力|元素充能效率|元素精通|火元素伤害造成|水元素伤害造成|风元素伤害造成|岩元素伤害造成|冰元素伤害加成|雷元素伤害造成/", $reqJson['words_result'][$numArtifact]['words'])) {
    $sub_item[] = wordsDecode($resRet[$numArtifact]['words']);
    $numArtifact++;
};
$ret['sub_item'] = $sub_item;
print_r($ret);

function requestUrl($url, $postData = "", $headers = "", $cookies = "")
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);

    if ($headers) {
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }

    if ($cookies) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

    $resData = curl_exec($ch);
    $resHeaders = curl_getinfo($ch);

    if (in_array($resHeaders['http_code'], array(301, 302))) {
        $resData = $resHeaders;
    }

    curl_close($ch);

    return $resData;
}

function wordsDecode($resWords)
{
    preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $resWords, $arrWords);
    $replaceWords = implode($arrWords[0]);
    $replaceValue = str_replace($replaceWords, "", $resWords);
    $replaceValue = str_replace("·", "", $replaceValue);
    $replaceValue = str_replace("+", "", $replaceValue);
    $wordsDec['name'] = $replaceWords;
    $wordsDec['value'] = $replaceValue;
    return $wordsDec;
}

function nameToType($name)
{
    switch ($name) {
        case '生命值':
            $type = "hp";
            break;
        case '攻击力':
            $type = "atk";
            break;
        case '防御力':
            $type = "df";
            break;
        case '元素充能效率':
            $type = "er";
            break;
        case '元素精通':
            $type = "em";
            break;
        case '物理伤害加成':
            $type = "phys";
            break;
        case '暴击率':
            $type = "cr";
            break;
        case '暴击伤害':
            $type = "cd";
            break;
        case '火元素伤害造成':
            $type = "pyro";
            break;
        case '水元素伤害造成':
            $type = "hydro";
            break;
        case '风元素伤害造成':
            $type = "anemo";
            break;
        case '岩元素伤害造成':
            $type = "geo";
            break;
        case '冰元素伤害加成':
            $type = "cryo";
            break;
        case '雷元素伤害造成':
            $type = "elec";
            break;
        case '治疗加成':
            $type = "heal";
            break;
    }
    return $type;
}