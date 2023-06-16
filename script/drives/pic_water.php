<?php

use Javion\Image\Watermark;

return function ($keyword) {
    global $zbp;

    try {
        $files = glob(__DIR__ . '/../../data/pic_diy/*'); // 优先使用自定义

        if (empty($files)) {
            $files = glob(__DIR__ . '/../../data/pic_default/*');
        }

        $imagesFiles = array();

        foreach ($files as $file) {
            $result = getimagesize($file);
            $type = isset($result[2]) ? $result[2] : 0;
            if (in_array($type, array(2, 3))) { // 2=jpeg 3=png
                $imagesFiles[] = $file;
            }
        }

        if (empty($imagesFiles)) {
            return array();
        }

        $colors = array('#171010FF');

        shuffle($colors);

        $configs = [
            // 水印字体(默认字体不支持中文，请按需配置需要的字体)
            'font' => __DIR__ . '/../../resource/zh40.ttf',
            // 水印位置(1~9，9宫格位置，其他为随机)
            'pos' => 5,
            // 相对pos的x偏移量
            'posX' => 0,
            // 相对pos的y偏移量
            'posY' => 30,
            // 水印透明度-填写0~100间的数字,100为不透明
            'opacity' => 100,
            // 透明度参数 alpha，其值从 0 到 127。0 表示完全不透明，127 表示完全透明
            'alpha' => 0,
            // 默认水印文字
            'text' => $zbp->host,
            // 文字颜色 颜色使用16进制表示
            'textColor' => $colors[0],
            // 文字大小
            'textSize' => 20,
        ];

        $originFile = $files[mt_rand(0, count($files) - 1)];

        $date = date('Y-m-d');
        $dir = __DIR__ . "/../../../../../zb_users/upload/editor/water/{$date}/";

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $keyword = iddahe_com_editor_text_chunk($keyword, 20);
        $keyword = implode("\n", $keyword);

        $filename = strtolower(uniqid());

        # 加蒙板
        $image = new Watermark($originFile);
        $image->waterImg(__DIR__ . '/../../resource/mb.png', 5, 60)->save($dir, $filename);

        # 加水印
        $image = new Watermark($dir . $filename . '.jpeg', $configs);
        $image->waterText($keyword, 5)->save($dir, $filename);

        # 命令行模式下 host 获取出错 ???
        if (false === stripos($zbp->host, '//localhost')) {
            return array("{$zbp->host}zb_users/upload/editor/water/{$date}/{$filename}.jpeg");
        } else {
            $host = str_replace('|', '', $zbp->Config('system')->ZC_BLOG_HOST);
            return array("{$host}zb_users/upload/editor/water/{$date}/{$filename}.jpeg");
        }
    } catch (Exception $e) {
        // Just so so
    }
    return array();
};
