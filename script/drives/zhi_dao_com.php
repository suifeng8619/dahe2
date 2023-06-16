<?php

use QL\QueryList;

class zhi_dao_com
{
    public function handle($keyword, $page = 3)
    {
        $posts = array();

        $limit = mt_rand(3, 6); // 提取的条数

        for ($p = 0; $p < 3; $p++) {
            $pn = $p * 10;
            $listUrl = "https://zhidao.baidu.com/index/?fr=iknow_common_search&word={$keyword}&pn={$pn}";

            $listHtml = iddahe_com_editor_curl($listUrl, $this->headers(), true);

            if (empty($listHtml)) {
                $listHtml = $this->fileGet($listUrl);
            }

            preg_match_all('/data\-log\=\"co\" href\=\"\/question\/([^\"]*)\.html/i', $listHtml, $items);

            $questionIds = isset($items[1]) ? $items[1] : array();

            foreach ($questionIds as $questionId) {
                $postUrl = "https://wapiknow.baidu.com/question/{$questionId}.html";
                $postHtml = iddahe_com_editor_curl($postUrl);

                $title = QueryList::html($postHtml)->find('title:eq(0)')->text();

                $bestContent = QueryList::html($postHtml)
                    ->find('div.best-answer-container div.w-reply-text')
                    ->html();

                $bestAnswerId = QueryList::html($postHtml)
                    ->find('div.best-answer-container div.w-reply-text')
                    ->attr('data-rid');

                $content = QueryList::html($postHtml)->find('div.w-reply-text.text-only')->html();
                $content = $bestContent ? $bestContent : $content;

                $answerId = QueryList::html($postHtml)->find('div.w-reply-text.text-only')->attr('data-rid');
                $answerId = $bestContent ? $bestAnswerId : $answerId;

                // strip_tags($content) 必须及时这样去除
                if (iddahe_com_editor_ends_with(strip_tags(str_replace(array("\n", "\r"), '', $content)), '全文')) {
                    $answerUrl = "https://wapiknow.baidu.com/question/{$questionId}/answer/{$answerId}.html";
                    $content = $this->getFullContent($answerUrl);
                }

                $title = trim(strip_tags($title));
                $content = trim(strip_tags($content, '<br><p>'));
                $content = iddahe_com_editor_formatter($content);
                $content = join_keywords($content, $keyword);

                if (strlen($title) > 3 && strlen($content) > 90) {
                    $posts[md5($title)] = compact('title', 'content', 'postUrl');
                }

                if (count($posts) >= $limit) {
                    break 2;
                }
            }
        }

        return array_values($posts);
    }

    protected function fileGet($url)
    {
        return iddahe_com_editor_file_get($url);
    }

    protected function getFullContent($url)
    {
        // 必须 file_get_contents 因为 CURL 不知道为什么就会变成 PC
        $answerHtml = $this->fileGet($url);

        $content = QueryList::html($answerHtml)->find('div.full-content')->html();

        return $content;
    }

    protected function headers()
    {
        return array(
            "Host:zhidao.baidu.com",
            "Content-Type:application/x-www-form-urlencoded",
            "Connection: keep-alive",
            'Referer:http://www.baidu.com',
            'User-Agent: Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; BIDUBrowser 2.6)',
            'Cookie:Hm_lpvt_6859ce5aaf00fb00387e6434e4fcc925=1528897780; IKUT=787; Hm_lvt_6859ce5aaf00fb00387e6434e4fcc925=1528895737,1528896942; BAIDUID=AB6A640364215C33934B0B48C061256D:FG=1; BIDUPSID=7141BE6AEA656AC574B137E3B2B509A9; PSTM=1528553489; FP_UID=a790709ad43dffbda49c004c49eee0eb',
        );
    }
}
