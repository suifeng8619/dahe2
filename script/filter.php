<?php

function filter_mmp($post)
{
    $search = array(
        "/([\r\n])[\s]+/",
        "/&(quot|#34);/i",
        "/&(amp|#38);/i",
        "/&(lt|#60);/i",
        "/&(gt|#62);/i",
        "/&(nbsp|#160);/i",
        "/&(iexcl|#161);/i",
        "/&(cent|#162);/i",
        "/&(pound|#163);/i",
        "/&(copy|#169);/i",
        "/&#(\d+);/"
    );

    $post = preg_replace('/[\x{200B}-\x{200D}]/u', '', $post);; // 0 宽空格

    return str_replace("\xef\xbb\xbf", '', preg_replace($search, '', $post));
}

// 目前仅标题和联想词过滤再使用
function filter_symbols($string)
{
    $symbols = file_get_contents(__DIR__ . '/../resource/symbols.txt');
    $symbols = str_replace("\r", '', $symbols);
    $symbols = array_filter(array_unique(explode("\n", $symbols)));

    return str_replace($symbols, '', $string);
}

function filter_urls2($article)
{
    $pattern = '/(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/im';

    return preg_replace($pattern, '', $article);
}

function filter_urls($content)
{
    preg_match_all('/\<.*?\>/is', $content, $labels);

    // #|||# 这个符号是为了避免 正则贪婪模式下有效内容被过滤掉
    $content = preg_replace('/\<.*?\>/is', '#|||#', $content);

    $content = preg_replace('/(http)(.)*([a-z0-9\-\.\_])+/i', '', $content);
    $content = preg_replace('/(www)(.)*([a-z0-9\-\.\_])+/i', '', $content);

    if (isset($labels[0])) {
        foreach ($labels[0] as $label) {
            $content = preg_replace('/#\|\|\|#/is', $label, $content, 1);
        }
    }

    return filter_urls2($content);
}

function filter_escape($string)
{
    $values = array(
        '&quot;',
        '&amp;',
        '&lt;',
        '&gt;',
        '&nbsp;',
        '&quot',
        '&amp',
        '&lt',
        '&gt',
        '&nbsp',
        ';;',
        '&?',
        '?&',
    );

    return str_replace($values, '', $string);
}
