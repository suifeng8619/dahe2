<?php

function EditorPostArticle()
{
    global $zbp;
    if (!isset($_POST['ID'])) {
        return false;
    }

    if (isset($_COOKIE['timezone'])) {
        $tz = GetVars('timezone', 'COOKIE');
        if (is_numeric($tz)) {
            date_default_timezone_set('Etc/GMT' . sprintf('%+d', -$tz));
        }
        unset($tz);
    }

    if (isset($_POST['Tag'])) {
        $_POST['Tag'] = FormatString($_POST['Tag'], '[noscript]');
        $_POST['Tag'] = PostArticle_CheckTagAndConvertIDtoString($_POST['Tag']);
    }
    if (isset($_POST['Content'])) {
        $_POST['Content'] = str_replace('<hr class="more" />', '<!--more-->', $_POST['Content']);
        $_POST['Content'] = str_replace('<hr class="more"/>', '<!--more-->', $_POST['Content']);
        if (strpos($_POST['Content'], '<!--more-->') !== false) {
            if (isset($_POST['Intro'])) {
                $_POST['Intro'] = GetValueInArray(explode('<!--more-->', $_POST['Content']), 0);
            }
        } else {
            if (isset($_POST['Intro'])) {
                if ($_POST['Intro'] == '' || (stripos($_POST['Intro'], '<!--autointro-->') !== false)) {
                    //$_POST['Intro'] = SubStrUTF8_Html($_POST['Content'], (int) strpos($_POST['Content'], '>') + (int) $zbp->option['ZC_ARTICLE_EXCERPT_MAX']);
                    //改纯HTML摘要
                    $_POST['Intro'] = FormatString($_POST['Content'], "[nohtml]");
                    $_POST['Intro'] = SubStrUTF8_Html($_POST['Intro'], (int)$zbp->option['ZC_ARTICLE_EXCERPT_MAX']);
                    $_POST['Intro'] .= '<!--autointro-->';
                }
                $_POST['Intro'] = CloseTags($_POST['Intro']);
            }
        }
    }

    if (!isset($_POST['AuthorID'])) {
        $_POST['AuthorID'] = $zbp->user->ID;
    } else {
        if (($_POST['AuthorID'] != $zbp->user->ID) && (!$zbp->CheckRights('ArticleAll'))) {
            $_POST['AuthorID'] = $zbp->user->ID;
        }
        if (empty($_POST['AuthorID'])) {
            $_POST['AuthorID'] = $zbp->user->ID;
        }
    }

    if (isset($_POST['Alias'])) {
        $_POST['Alias'] = FormatString($_POST['Alias'], '[noscript]');
    }

    if (isset($_POST['PostTime'])) {
        $_POST['PostTime'] = strtotime($_POST['PostTime']);
    }

    if (!$zbp->CheckRights('ArticleAll')) {
        unset($_POST['IsTop']);
    }

    $article = new Post();
    $pre_author = null;
    $pre_tag = null;
    $pre_category = null;
    $pre_istop = null;
    $pre_status = null;
    $orig_id = 0;

    if (GetVars('ID', 'POST') == 0) {
        if (!$zbp->CheckRights('ArticlePub')) {
            $_POST['Status'] = ZC_POST_STATUS_AUDITING;
        }
    } else {
        $article->LoadInfoByID(GetVars('ID', 'POST'));
        if (($article->AuthorID != $zbp->user->ID) && (!$zbp->CheckRights('ArticleAll'))) {
            $zbp->ShowError(6, __FILE__, __LINE__);
        }
        if ((!$zbp->CheckRights('ArticlePub')) && ($article->Status == ZC_POST_STATUS_AUDITING)) {
            $_POST['Status'] = ZC_POST_STATUS_AUDITING;
        }
        $orig_id = $article->ID;
        $pre_author = $article->AuthorID;
        $pre_tag = $article->Tag;
        $pre_category = $article->CateID;
        $pre_istop = $article->IsTop;
        $pre_status = $article->Status;
    }

    foreach ($zbp->datainfo['Post'] as $key => $value) {
        if ($key == 'ID' || $key == 'Meta') {
            continue;
        }
        if (isset($_POST[$key])) {
            $article->$key = GetVars($key, 'POST');
        }
    }

    $article->Type = ZC_POST_TYPE_ARTICLE;

    FilterMeta($article);

    foreach ($GLOBALS['hooks']['Filter_Plugin_PostArticle_Core'] as $fpname => &$fpsignal) {
        $fpname($article);
    }

    FilterPost($article);

    $article->Save();

    //更新统计信息
    $pre_arrayTag = $zbp->LoadTagsByIDString($pre_tag);
    $now_arrayTag = $zbp->LoadTagsByIDString($article->Tag);
    $pre_array = $now_array = array();
    foreach ($pre_arrayTag as $tag) {
        $pre_array[] = $tag->ID;
    }
    foreach ($now_arrayTag as $tag) {
        $now_array[] = $tag->ID;
    }
    $del_array = array_diff($pre_array, $now_array);
    $add_array = array_diff($now_array, $pre_array);
    $del_string = $zbp->ConvertTagIDtoString($del_array);
    $add_string = $zbp->ConvertTagIDtoString($add_array);
    if ($del_string) {
        CountTagArrayString($del_string, -1, $article->ID);
    }
    if ($add_string) {
        CountTagArrayString($add_string, +1, $article->ID);
    }
    if ($pre_author != $article->AuthorID) {
        if ($pre_author > 0) {
            CountMemberArray(array($pre_author), array(-1, 0, 0, 0));
        }

        CountMemberArray(array($article->AuthorID), array(+1, 0, 0, 0));
    }
    if ($pre_category != $article->CateID) {
        if ($pre_category > 0) {
            OctopusCountCategoryArray(array($pre_category), -1);
        }

        OctopusCountCategoryArray(array($article->CateID), +1);
    }
    if ($zbp->option['ZC_LARGE_DATA'] == false) {
        CountPostArray(array($article->ID));
    }
    if ($orig_id == 0 && $article->IsTop == 0 && $article->Status == ZC_POST_STATUS_PUBLIC) {
        CountNormalArticleNums(+1);
    } elseif ($orig_id > 0) {
        if (($pre_istop == 0 && $pre_status == 0) && ($article->IsTop != 0 || $article->Status != 0)) {
            CountNormalArticleNums(-1);
        }
        if (($pre_istop != 0 || $pre_status != 0) && ($article->IsTop == 0 && $article->Status == 0)) {
            CountNormalArticleNums(+1);
        }
    }
    if ($article->IsTop == true && $article->Status == ZC_POST_STATUS_PUBLIC) {
        CountTopArticle($article->Type, $article->ID, null);
    } else {
        CountTopArticle($article->Type, null, $article->ID);
    }

    $zbp->AddBuildModule('previous');
    $zbp->AddBuildModule('calendar');
    $zbp->AddBuildModule('comments');
    $zbp->AddBuildModule('archives');
    $zbp->AddBuildModule('tags');
    $zbp->AddBuildModule('authors');

    foreach ($GLOBALS['hooks']['Filter_Plugin_PostArticle_Succeed'] as $fpname => &$fpsignal) {
        $fpname($article);
    }

    // return true;
    return $article->ID;
}

/**
 * 统计分类下文章数.
 *
 * @param Category &$category
 * @param int $plus 控制是否要进行全表扫描
 */
function OctopusCountCategory(&$category, $plus = null)
{
    global $zbp;

    if ($plus === null) {
        $id = $category->ID;

        $s = $zbp->db->sql->Count($zbp->table['Post'], array(array('COUNT', '*', 'num')), array(
            array('=', 'log_Type', 0),
            array('=', 'log_IsTop', 0),
            array('=', 'log_Status', 0),
            array('=', 'log_CateID', $id)
        ));
        $num = GetValueInArrayByCurrent($zbp->db->Query($s), 'num');

        $category->Count = $num;
    } else {
        $category->Count += $plus;
    }
}

/**
 * 批量统计指定分类下文章数并保存.
 *
 * @param array $array 记录分类ID的数组
 * @param int $plus 控制是否要进行全表扫描
 */
function OctopusCountCategoryArray($array, $plus = null)
{
    global $zbp;

    $cate = new Category();

    $array = array_unique($array);
    foreach ($array as $value) {
        if ($value == 0) {
            continue;
        }
        if (isset($zbp->categories[$value])) {
            OctopusCountCategory($zbp->categories[$value], $plus);
            $zbp->categories[$value]->Save();
        } elseif ($category = $cate->GetCategoryByID($value)) {
            OctopusCountCategory($category, $plus);
            $category->Save();
        }
    }
}
