<?php

class iddahe_com_editor_formatter
{
    public function formatter($posts)
    {
        global $zblogx;

        shuffle($posts); // rand

        $content = $list = '';

        $list .= '<p style="margin-bottom: 3px;font-size: 18px;font-weight: bold">本文目录一览：</p><ul>';

        $isAddImg = false;
        foreach ($posts as $key => $post) {
            $number = $key + 1;
            $list .= "<li style='margin-bottom: 3px;list-style: none'>\n";
            $list .= "{$number}、<a href='#{$post['title']}' title='{$post['title']}'>{$post['title']}</a>\n";
            $list .= "</li>\n";

            $content .= "<h2 id='{$post['title']}'>{$post['title']}</h2>\n";
            $content .= $post['content'];

            if (!$isAddImg && mt_rand(1, 100) >= 50) {
                $content .= '[img]';
                $isAddImg = true;
            }
        }

        if (!$isAddImg) {
            $content .= '[img]';
        }

        // 这里判断 no 是兼容没有设置的情况
        if ('no' == $zblogx('iddahe_com_editor')->get_post_list_status()) {
            return '[start]' . $content . '[end]';
        }

        return '[start]' . $list . '</ul>' . $content . '[end]';
    }
}
