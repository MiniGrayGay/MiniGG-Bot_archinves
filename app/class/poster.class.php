<?php

class poster
{

    public static $errMsg = '';
    public static $config = array();
    private static $backGroundImage = '';
    private static $fontPath = '';
    private static $bgImageData = null;
    private static $textDefault = array();
    private static $imageDefault = array();

    public static function init()
    {
        self::$fontPath = dirname(dirname(__FILE__)) . '/resources/';
        self::$imageDefault = array(
            'name' => '', //图片名称，用于出错时定位
            'url' => '', //图片路径
            'stream' => 0, //图片数据流，与url二选一
            'left' => 0, //左边距
            'top' => 0, //上边距
            'right' => 0, //有边距
            'bottom' => 0, //下边距
            'width' => 0, //宽
            'height' => 0, //高
            'radius' => 0, //圆角度数，最大值为显示宽度的一半
            'opacity' => 100, //透明度
        );
        self::$textDefault = array(
            'text' => '', //显示文本
            'left' => 0, //左边距,数字或者center,水平居中
            'top' => 0, //上边距,数字或者center,垂直居中
            'width' => 0, //文本框宽度，设置后可实现文字换行
            'fontSize' => 32, //字号
            'fontPath' => 'msyh.ttf', //字体文件
            'fontColor' => '255,255,255', //字体颜色
            'angle' => 0, //倾斜角度
        );
    }

    /*
     * 海报配置信息
     * @param $config array 指定的配置信息
     * @return array 合并后的完整配置信息
     */

    public static function setConfig($config = array())
    {
        if (!self::$imageDefault || !self::$textDefault) {
            self::init();
        }
        self::$backGroundImage = isset($config['bg_url']) ? $config['bg_url'] : '';
        if (isset($config['image']) && $config['image']) {
            foreach ($config['image'] as $k => $v) {
                self::$config['image'][$k] = array_merge(self::$imageDefault, $v);
            }
        } else {
            self::$config['image'] = array();
        }
        if (isset($config['text']) && $config['text']) {
            foreach ($config['text'] as $k => $v) {
                self::$config['text'][$k] = array_merge(self::$textDefault, $v);
            }
        } else {
            self::$config['text'] = array();
        }
        return self::$config;
    }

    /*
     * 合并生成海报
     * @param $fileName string 指定生成的图片路径，不传则直接返回图片数据流
     * @return string or bool 图片数据流或者处理结果状态
     */

    public static function make($fileName = '')
    {
        self::$errMsg = null;
        if (!self::$backGroundImage || ((strpos(self::$backGroundImage, 'http') === false) && !is_file(self::$backGroundImage))) {
            self::$errMsg = '请先设置有效的海报背景图片';
            return false;
        }
        //背景方法
        if (!self::$bgImageData) {
            $backgroundInfo = getimagesize(self::$backGroundImage);
            $backgroundFun = 'imagecreatefrom' . image_type_to_extension($backgroundInfo[2], false);
            $bgData = $backgroundFun(self::$backGroundImage);
            $backgroundWidth = imagesx($bgData); //背景宽度
            $backgroundHeight = imagesy($bgData); //背景高度
            self::$bgImageData = imageCreatetruecolor($backgroundWidth, $backgroundHeight);
            //创建透明背景色，主要127参数，其他可以0-255，因为任何颜色的透明都是透明
            $transparent = imagecolorallocatealpha(self::$bgImageData, 0, 0, 0, 127);
            //指定颜色为透明
            imagecolortransparent(self::$bgImageData, $transparent);
            //保留透明颜色
            imagesavealpha(self::$bgImageData, true);
            //填充图片颜色
            imagefill(self::$bgImageData, 0, 0, $transparent);
            imagecopyresampled(self::$bgImageData, $bgData, 0, 0, 0, 0, $backgroundWidth, $backgroundHeight, $backgroundWidth, $backgroundHeight);
        }

        $bgImgData = self::$bgImageData;

        //处理图片
        if (self::$config['image']) {
            foreach (self::$config['image'] as $val) {
                if ($val['stream']) { //如果传的是字符串图像流
                    $info = getimagesizefromstring($val['stream']);
                    $res = imagecreatefromstring($val['stream']);
                } elseif ($val['url'] && (strpos($val['url'], 'http') !== FALSE)) {
                    $data = file_get_contents($val['url']);
                    if (!$data) {
                        self::$errMsg = '读取[' . $val['name'] . ']图片失败';
                        return false;
                    }
                    $info = getimagesizefromstring($data);
                    $res = imagecreatefromstring($data);
                } else {
                    if (!$val['url'] || ((strpos($val['url'], 'http') === FALSE) && !is_file($val['url']))) {
                        self::$errMsg = '[' . $val['name'] . ']图片不存在';
                        return false;
                    }
                    $info = getimagesize($val['url']);
                    $function = 'imagecreatefrom' . image_type_to_extension($info[2], false);
                    if (!function_exists($function)) {
                        self::$errMsg = '[' . $val['name'] . ']图片格式不支持';
                        return false;
                    }
                    $res = $function($val['url']);
                }
                imagesavealpha($res, true); //这里很重要;
                $resWidth = $info[0];
                $resHeight = $info[1];

                if ($val['radius']) {
                    if ($val['width'] > $resWidth) {
                        $val['width'] = $resWidth;
                    }
                    if ($val['height'] > $resHeight) {
                        $val['height'] = $resHeight;
                    }
                    if ($val['radius'] > round($val['width'] / 2)) {
                        self::$errMsg = '[' . $val['name'] . ']的圆角度数最大不能超过：' . (round($val['width'] / 2));
                        return false;
                    }
                    $canvas = self::setRadiusImage($res, $resWidth, $resHeight, $val['width'], $val['height'], $val['radius']);
                } else {
                    $canvas = imagecreatetruecolor($val['width'], $val['height']);
                    //创建透明背景色，主要127参数，其他可以0-255，因为任何颜色的透明都是透明
                    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
                    //指定颜色为透明
                    imagecolortransparent($canvas, $transparent);
                    //保留透明颜色
                    imagesavealpha($canvas, true);
                    //填充图片颜色
                    imagefill($canvas, 0, 0, $transparent);
                    //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
                    imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'], $resWidth, $resHeight);
                }
                $val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) - $val['width'] : $val['left'];
                $val['top'] = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) - $val['height'] : $val['top'];
                //放置图像
                imagecopymerge($bgImgData, $canvas, $val['left'], $val['top'], $val['right'], $val['bottom'], $val['width'], $val['height'], $val['opacity']); //左，上，右，下，宽度，高度，透明度
            }
        }

        //处理文字
        if (self::$config['text']) {
            mb_internal_encoding("UTF-8"); // 设置编码
            foreach (self::$config['text'] as $val) {

                $fontPath = self::$fontPath . $val['fontPath'];
                if ($val['width']) {
                    $val['text'] = self::stringAutoWrap($val['text'], $val['fontSize'], $val['angle'], $fontPath, $val['width'], false);
                }
                // $val['text'] = mb_convert_encoding($val['text'], "html-entities", "utf-8");
                list($R, $G, $B) = explode(',', $val['fontColor']);
                $fontColor = imagecolorallocate($bgImgData, $R, $G, $B);
                $val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) : $val['left'];
                $val['top'] = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) : $val['top'];
                if ($val['left'] == 'center' || $val['top'] == 'center') {
                    $fontBox = imagettfbbox($val['fontSize'], 0, $fontPath, $val['text']);
                    if ($val['left'] === 'center') {
                        $val['left'] = ceil(($backgroundWidth - $fontBox[2]) / 2);
                    }
                    if ($val['top'] === 'center') {
                        $val['top'] = ceil(($backgroundHeight - $fontBox[1] - $fontBox[7]) / 2);
                    }
                }
                imagettftext($bgImgData, $val['fontSize'], $val['angle'], $val['left'], $val['top'], $fontColor, $fontPath, $val['text']);
            }
        }
        if ($fileName) {
            $res = ImagePng($bgImgData, $fileName, 8); //保存到本地
            ImageDestroy($bgImgData);
            if (!$res) {
                self::$errMsg = '图片保存失败';
                return false;
            } else {
                return true;
            }
        } else {
            ob_start();
            ImagePng($bgImgData);
            $content = ob_get_contents();
            ob_end_clean();
            ImageDestroy($bgImgData);
            if (!$content) {
                self::$errMsg = '图片数据获取失败';
                return false;
            }
            return $content;
        }
    }

    /*
     * 清理海报背景缓存数据
     * @return bool 清理结果
     */

    public static function clear()
    {
        if (self::$bgImageData) {
            self::$bgImageData = null;
        }
        return true;
    }

    /*
     * 抛出异常信息
     * @return string 异常信息说明
     */

    public static function getErrMessage()
    {
        return self::$errMsg;
    }

    /*
     * 根据文字长度计算出行数
     * @param $string string 需要显示的文字
     * @param $info array 文字显示设置
     * @return int 返回文字行数
     */
    public static function getFontLines($string, $info)
    {
        if (!self::$imageDefault || !self::$textDefault) {
            self::init();
        }
        $arr = array('fontSize', 'angle', 'fontPath', 'width');
        $setting = array();
        foreach ($arr as $key) {
            $setting[$key] = isset($info[$key]) ? $info[$key] : self::$textDefault[$key];
        }
        return self::stringAutoWrap($string, $setting['fontSize'], $setting['angle'], self::$fontPath . $setting['fontPath'], $setting['width'], true);
    }

    //生成圆角图片
    private static function setRadiusImage(&$imgData, $resWidth, $resHeight, $w, $h, $radius = 10)
    {
        $img = imagecreatetruecolor($w, $h);
        //创建透明背景色，主要127参数，其他可以0-255，因为任何颜色的透明都是透明
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        //指定颜色为透明
        imagecolortransparent($img, $transparent);
        //保留透明颜色
        imagesavealpha($img, true);
        //填充图片颜色
        imagefill($img, 0, 0, $transparent);
        imagecopyresampled($imgData, $imgData, 0, 0, 0, 0, $w, $h, $resWidth, $resHeight); //将原图缩放尺寸重新获得数据流
        $r = $radius; //圆 角半径
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($imgData, $x, $y);
                if (($x >= $radius && $x <= ($w - $radius)) || ($y >= $radius && $y <= ($h - $radius))) {
                    //不在四角的范围内,直接画
                    imagesetpixel($img, $x, $y, $rgbColor);
                } else {
                    //在四角的范围内选择画
                    //上左
                    $yx1 = $r; //圆心X坐标
                    $yy1 = $r; //圆心Y坐标
                    if (((($x - $yx1) * ($x - $yx1) + ($y - $yy1) * ($y - $yy1)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //上右
                    $yx2 = $w - $r; //圆心X坐标
                    $yy2 = $r; //圆心Y坐标
                    if (((($x - $yx2) * ($x - $yx2) + ($y - $yy2) * ($y - $yy2)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //下左
                    $yx3 = $r; //圆心X坐标
                    $yy3 = $h - $r; //圆心Y坐标
                    if (((($x - $yx3) * ($x - $yx3) + ($y - $yy3) * ($y - $yy3)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //下右
                    $yx4 = $w - $r; //圆心X坐标
                    $yy4 = $h - $r; //圆心Y坐标
                    if (((($x - $yx4) * ($x - $yx4) + ($y - $yy4) * ($y - $yy4)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                }
            }
        }
        return $img;
    }

    //文字自动换行
    private static function stringAutoWrap($string, $fontsize, $angle, $fontface, $width, $returnLines = false)
    {
        $arr = array();
        $newStr = '';
        $counts = 1;
        $count = mb_strlen($string, 'UTF-8');
        for ($i = 0; $i < $count; $i++) {
            $str = mb_substr($string, $i, 1);
            $newStr .= $str;
            $box = imagettfbbox($fontsize, $angle, $fontface, $newStr);
            if (($box[2] > $width)) {
                $arr[] = PHP_EOL;
                $counts += 1;
                $newStr = '';
            }
            $arr[] = $str;
        }
        if ($returnLines) {
            return $counts;
        } else {
            return trim(implode('', $arr), PHP_EOL);
        }
    }
}
