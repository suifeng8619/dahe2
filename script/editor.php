<?php

require_once __DIR__ . '/../../../../zb_system/function/c_system_base.php';
require_once __DIR__ . '/../../../../zb_system/function/c_system_admin.php';

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../post.php';

require_once __DIR__ . '/filter.php';
require_once __DIR__ . '/formatter.php';
require_once __DIR__ . '/drives/zhi_dao_com.php';

$zbp->Load();

if ('cli' != PHP_SAPI) {
    if ($zblogx("iddahe_com_editor")->get_token() != trim($_GET['token'])) {
        die('会话错误，检查 token 参数是否正确!');
    }
}

$zblogx('iddahe_com_editor')->authApp();

function get_rand_ua()
{
    $file = __DIR__ . '/../resource/ua.txt';
    $values = array_values(array_filter(explode("\n", file_get_contents($file))));
    $ua = $values[mt_rand(0, count($values) - 1)];
    // return random_ua();
    return str_replace("\r", '', $ua);
}

function title_formatter($keyword, $relatedLists)
{
    global $zblogx;

    $relatedLists = array_values($relatedLists);

    if ($relatedLists) {
        $related = $relatedLists[mt_rand(0, count($relatedLists) - 1)];
        $related = filter_symbols($related);

        if ($formats = $zblogx('iddahe_com_editor')->get_title_format()) {
            $formats = array_filter(explode('|', $formats));
            $format = $formats[mt_rand(0, count($formats) - 1)];
            $title = str_replace(
                array('{输入关键词}', '{联想词}'),
                array($keyword, $related),
                $format
            );
        } else {
            $title = "{$keyword}（{$related}）";
        }
        return array($title, $related);
    }

    $defaultTitle = $zblogx("iddahe_com_editor")->get_default_title();
    $defaultTitle = $defaultTitle ? $defaultTitle : 'yes'; # 没有初始化情况

    if ('yes' == $defaultTitle) {
        if (mt_rand(1, 100) <= 50) {
            return array("{$keyword}的简单介绍", '');
        } elseif (mt_rand(1, 100) <= 50) {
            return array("关于{$keyword}的信息", '');
        }

        return array("包含{$keyword}的词条", '');
    }

    return array($keyword, '');
}

function get_category_ids()
{
    global $zbp;

    $categoryIds = array();

    $categories = $zbp->GetCategoryList(null, null, array('cate_Order' => 'ASC'), null, null);
    foreach ($categories as $category) {
        $categoryIds[] = $category->ID;
    }

    if (empty($categoryIds)) {
        return $categoryIds;
    }

    return array_values($categoryIds);
}

function rand_category_id()
{
    $categoryIds = get_category_ids();

    return $categoryIds[mt_rand(0, count($categoryIds) - 1)];
}

function diy_filter($string)
{
    global $zblogx;

    $rules = $zblogx("iddahe_com_editor")->get_except_rules();

    if (empty($rules)) {
        return $string;
    }

    preg_match_all('/<.*?>/i', $string, $matches);
    $string = preg_replace('/<.*?>/i', '<!_!_!>', $string);
    $string = filter_escape($string);

    $rules = array_filter(explode("\n", $rules));
    $rules = is_array($rules) ? $rules : array();

    foreach ($rules as $item) {
        if ($values = array_filter(explode('<=>', $item))) {
            $rule = str_replace('*', '#@#', $values[0]);
            $rule = str_replace('#@#', '.*?', preg_quote($rule, '/'));
            $value = isset($values[1]) ? $values[1] : '';
            $string = preg_replace("/{$rule}/", $value, $string);
        }
    }

    $tags = is_array($matches[0]) ? $matches[0] : array();

    foreach ($tags as $tag) {
        $string = preg_replace('/\<\!\_\!\_\!\>/i', $tag, $string, 1);
    }

    return str_replace('<tag>', '', $string);
}

function join_keywords($content, $keyword)
{
    global $zblogx;

    if ('yes' != $zblogx("iddahe_com_editor")->get_keyword_join()) {
        return $content;
    }

    if (mt_rand(1, 100) > 30) {
        return $content; // 30% 插入率
    }

    preg_match_all('/<.*?>/', $content, $matches);
    $content = preg_replace('/<.*?>/', '{html}', $content);

    $limit = mt_rand(2, 5);
    $symbols = array('：', '，', '？', '；', '！', '，', '你', '我', '他', '了', '的');

    $replaces = array();

    for ($i = 0; $i < $limit; $i++) {
        shuffle($symbols);
        foreach ($symbols as $symbol) {
            if (false !== mb_strpos($content, $symbol)) {
                $index = uniqid();
                $replaces[$index] = "[strong]{$keyword}[/strong]{$symbol}";
                $content = preg_replace("/{$symbol}/i", '{' . $index . '}', $content, 1);
                break;
            }
        }
    }

    foreach ($replaces as $index => $value) {
        $content = str_replace('{' . $index . '}', $value, $content);
    }

    $htmlTags = isset($matches[0]) ? $matches[0] : array();
    foreach ($htmlTags as $htmlTag) {
        $content = preg_replace('/\{html\}/i', $htmlTag, $content, 1);
    }

    return $content;
}

function join_images($content, $title, $keyword)
{
    global $zblogx;

    // no 是兼容没有设置的情况 ！！！
    if ('no' == $zblogx("iddahe_com_editor")->get_generate_img()) {
        return $content;
    }

    if (iddahe_com_editor_component_has('WXCPicture')) {
        $handler = include __DIR__ . '/../component/WXCPicture/handle.php';
        list($imageUrl) = $handler($keyword);
    }

    if (!isset($imageUrl) || empty($imageUrl)) {
        $handler = include __DIR__ . '/drives/pic_water.php';
        list($imageUrl) = $handler($title);
    }

    $img = '<p style="text-align: center">';
    $img .= '<img style="max-width: 600px" src="' . $imageUrl . '" title="' . $title . '"><p>';

    return str_replace('[img]', $img, $content);
}

function handle()
{
    global $zbp, $zblogx;

    $files = glob(__DIR__ . '/../data/keywords/*.txt');
    shuffle($files); // 随机处理关键词分类组

    $categoryIdsAll = get_category_ids();
    shuffle($categoryIdsAll);

    $counter = 0;
    foreach ($files as $file) {
        try {
            $categoryIdFormFile = str_replace(array('key', '.txt', ''), '', basename($file));
            if ('rand' == $categoryIdFormFile) {
                $categoryIds = array(rand_category_id()); // 随机一个分类处理
            } else {
                $categoryIds = array($categoryIdFormFile); // 处理一个单独的分类 ID
            }
            foreach ($categoryIds as $categoryId) {
                $keywords = array_filter(explode("\n", file_get_contents($file)));

                if (empty($keywords)) {
                    continue;
                }

                $index = mt_rand(0, count($keywords) - 1);
                $keyword = str_replace(array("\r", ' '), '', $keywords[$index]);

                $category = new Category();
                $category->LoadInfoByID($categoryId);

                // 分类错误时，删除关键词并进行忽略
                if (empty($keyword) || '未命名' == $category->Name) {
                    unset($keywords[$index]);
                    file_put_contents($file, implode("\n", $keywords));
                    continue;
                }

                // 标题
                $relatedLists = iddahe_com_editor_xiala($keyword);
                list($title, $related) = title_formatter($keyword, $relatedLists);
                $title = diy_filter($title);

                if (iddahe_com_editor_component_has('TitleExtension')) {
                    $handler = include __DIR__ . '/../component/TitleExtension/handle.php';
                    $title = $handler($title);
                }

                // post start
                $startContent = iddahe_com_editor_get_section_a_line('post_start_contents');
                $startContent = str_replace(array('{输入关键词}', '{联想词}'), array($keyword, $related), $startContent);
                $startContent = "<p>$startContent</p>";

                // post end
                $endContent = iddahe_com_editor_get_section_a_line('post_end_contents');
                $endContent = str_replace(array('{输入关键词}', '{联想词}'), array($keyword, $related), $endContent);
                $endContent = "<p>$endContent</p>";

                $posts = array();

                if (iddahe_com_editor_component_has('ZCYContent')) {
                    $handler = include __DIR__ . '/../component/ZCYContent/handle.php';
                    $posts = $handler($keyword);
                }

                if (empty($posts)) {
                    $posts = (new zhi_dao_com())->handle($keyword); // 默认内容
                }

                $content = (new iddahe_com_editor_formatter())->formatter($posts);
                $content = filter_mmp($content);
                $content = diy_filter($content);

                ## 还原文章里面插入的关键词
                $content = str_replace(array('[strong]', '[/strong]'), array('<strong>', '</strong>'), $content);
                $content = @join_images($content, $title, $keyword);

                ## 插入首尾段落
                if ($zblogx("iddahe_com_editor")->get_post_start()) {
                    $content = str_replace('[start]', $startContent, $content);
                } else {
                    $content = str_replace('[start]', '', $content);
                }
                if ($zblogx("iddahe_com_editor")->get_post_end()) {
                    $content = str_replace('[end]', $endContent, $content);
                } else {
                    $content = str_replace('[end]', '', $content);
                }

                // 内容未匹配上时，删除关键词并进行忽略
                if (false === strpos(strip_tags($content, '<h2>'), '<h2')) {
                    unset($keywords[$index]);
                    file_put_contents($file, implode("\n", $keywords));
                    continue;
                }

                // 文章状态
                $postStatus = $zblogx('iddahe_com_editor')->get_post_status();
                $postStatus = !is_null($postStatus) ? $postStatus : 0;

                // 发布时间处理
                $postTime = null;
                $postTimeDiy = $zblogx("iddahe_com_editor")->get_post_time_diy();
                $postStartAt = $zblogx("iddahe_com_editor")->get_post_start_at();
                $postEndAt = $zblogx("iddahe_com_editor")->get_post_end_at();
                if ('yes' == $postTimeDiy) {
                    $postStartAt = $postStartAt ? $postStartAt : date('Y-m-d 00:00:00');
                    $postEndAt = $postEndAt ? $postEndAt : date('Y-m-d 23:59:59'); // 你不知道他会干出些什么蠢事
                    $time = mt_rand(strtotime($postStartAt), strtotime($postEndAt));
                    $postTime = date('Y-m-d H:i:s', $time);
                }

                // 文章作者
                $AuthorID = iddahe_com_editor_get_post_user_id();

                // 插入关键词
                // $content = iddahe_com_editor_join_keywords($content, $keyword);

                $_POST = array(
                    'Intro' => '',            // 自动摘要
                    'ID' => 0,                // 新文章
                    'Tag' => '',              // 标签
                    'AuthorID' => $AuthorID,  // 作者
                    'Title' => $title,        // 标题
                    'Content' => $content,    // 分类
                    'Status' => $postStatus,  // 发布状态
                    'CateID' => $categoryId,  // 默认分类
                    'PostTime' => $postTime,  // 文章时间
                    'meta_editor_keyword' => $keyword, // 文章来源关键词
                    'meta_editor_related' => $related, // 文章联想词
                );

                // 对应【文章管理】里面的置顶功能，开启后发布文章时以 10% 概率设置置顶文章
                if ('yes' == $zblogx("iddahe_com_editor")->get_rand_top() && mt_rand(1, 100) <= 10) {
                    if (iddahe_com_editor_need_top()) {
                        $_POST['IsTop'] = 1; // 仅置顶 20 篇文章
                    }
                }

                // 文章标签.用关键词
                $keywordToTag = $zblogx("iddahe_com_editor")->get_keyword_to_tag();
                $keywordToTag = $keywordToTag ? $keywordToTag : 'yes';
                if ('yes' == $keywordToTag) {
                    $_POST['Tag'] = $keyword;
                }

                // 按标题去重
                $articles = $zbp->GetArticleList('*', array(array('=', 'log_Title', $_POST['Title'])));
                if (empty($articles)) {
                    $user = new Member(); // 需要每次加载用户
                    $user->LoadInfoByID($AuthorID);
                    $zbp->user = $user;

                    if ($GLOBALS['blogversion'] > 170000) {
                        $post = PostArticle();
                        $postId = $post ? $post->ID : false;
                    } else {
                        $postId = EditorPostArticle();
                    }

                    $counter++; // 合计
                }
                unset($keywords[$index]);
                file_put_contents($file, implode("\n", $keywords));

                if ($counter > 0) {
                    break 2; // 这插件每次就干一篇文章
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            echo "\n";
            echo $e->getTraceAsString();
            echo "\n";
        }
    }

    @$zbp->BuildModule(); // 重建模块，更新缓存，不然侧栏不更新
    @$zbp->SaveCache(); // zx 说要放到最后，出问题找 zx

    echo date('Y-m-d H:i:s') . "【问答组合文章】执行了写作任务，生成了 {$counter} 篇文章，请到文章管理查看\n";
}

// ==============================================================================
handle();
// ==============================================================================

