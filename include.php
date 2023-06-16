<?php

require_once __DIR__ . '/component/activate.php';

RegisterPlugin("iddahe_com_editor", "ActivePlugin_iddahe_com_editor");

function ActivePlugin_iddahe_com_editor()
{
    iddahe_com_editor_unzip_vendor(); // 向下兼容

    Add_Filter_Plugin('Filter_Plugin_Member_Save', 'iddahe_com_editor_member_save');
}

function UninstallPlugin_iddahe_com_editor()
{
    // Just so so
}

function InstallPlugin_iddahe_com_editor()
{
    global $zbp;

    iddahe_com_editor_unzip_vendor();
}

function iddahe_com_editor_member_save($user)
{
    global $zbp;

    // $zbp->datainfo 是引用

    if (empty($user->Password)) {
        unset($zbp->datainfo['Member']['Password']);
        unset($zbp->datainfo['Member']['Guid']);
        unset($zbp->datainfo['Member']['mem_Password']);
    }
}

function iddahe_com_editor_unzip_vendor()
{
    try {
        if (is_dir(__DIR__ . '/vendor/javion')) {
            return; // javion 水印相关
        }

        $path = __DIR__ . '/resource/vendorV2.zip';

        $zip = new ZipArchive;

        if ($zip->open($path)) {
            $zip->extractTo(__DIR__);
            $zip->close();
        }

        // @unlink($path);
    } catch (Exception $e) {
        echo '扩展包解压失败，请联系作者进行解决';
        die();
    }
}

function iddahe_com_editor_build_file($categoryId)
{
    $dir = __DIR__ . '/data/keywords';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return "{$dir}/key{$categoryId}.txt";
}

function iddahe_com_editor_build_section_file($filename)
{
    $dir = __DIR__ . '/data/section';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return "{$dir}/$filename.txt";
}

function iddahe_com_editor_get_section_file($filename)
{
    $filename = iddahe_com_editor_build_section_file($filename);

    if (is_file($filename)) {
        return file_get_contents($filename);
    }

    return '';
}

function iddahe_com_editor_set_section_file($filename, $content)
{
    $filename = iddahe_com_editor_build_section_file($filename);

    file_put_contents($filename, $content);
}

function iddahe_com_editor_get_section_a_line($filename)
{
    $filename = iddahe_com_editor_build_section_file($filename);

    if (!is_file($filename)) {
        return '';
    }

    $values = array_values(
        array_filter(
            explode("\n", file_get_contents($filename))
        )
    );

    shuffle($values);

    return isset($values[0]) ? $values[0] : '';
}


function iddahe_com_editor_need_top()
{
    global $zbp;

    $limit = 20;

    $where = array(array('>', 'log_IsTop', 0));

    $articles = $zbp->GetArticleList('log_ID', $where, null, array($limit));

    return $limit > count($articles);
}

function iddahe_com_editor_curl(
    $url,
    $headers = array(),
    $isPost = false,
    $timeout = 30,
    $isProxy = true,
    $toUtf8 = true,
    $postData = array()
) {
    try {
        global $zblogx;

        $curl = curl_init();
        // 设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        // 设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 超时设置,以秒为单位
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        // 设置请求头
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        // 设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, $isPost);

        // Operation timed out after 30000 milliseconds with 0 bytes received !!!!
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);

        if (!empty($postData)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
        }

        if ($zblogx('iddahe_com_editor')->get_proxy_status() && $isProxy) {
            $ip = iddahe_com_editor_get_ip();
            if (false !== $ip) {
                curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                curl_setopt($curl, CURLOPT_PROXY, $ip);

                $proxyUsername = trim($zblogx('iddahe_com_editor')->get_proxy_username());
                $proxyPassword = trim($zblogx('iddahe_com_editor')->get_proxy_Password());

                if ($proxyUsername && $proxyPassword) {
                    curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                    curl_setopt($curl, CURLOPT_PROXYUSERPWD, "{$proxyUsername}:{$proxyPassword}");
                }
            }
        }

        if ($response = curl_exec($curl)) {
            curl_close($curl);
            if ($toUtf8) {
                return iddahe_com_editor_to_utf8($response);
            } else {
                return $response;
            }
        }

        if ($message = curl_error($curl)) {
            throw new Exception("get document error：$url => {$message}");
        }
        curl_close($curl);
    } catch (Exception $e) {
        // Just so so
    }

    if ($toUtf8) {
        return iddahe_com_editor_to_utf8($response);
    } else {
        return $response;
    }
}

function iddahe_com_editor_file_get($url)
{
    $contextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    );

    // 必须 file_get_contents 因为 CURL 不知道为什么就会变成 PC
    return file_get_contents($url, false, stream_context_create($contextOptions));
}

function iddahe_com_editor_get_ip()
{
    global $zblogx;

    $lastProxyIp = $zblogx('iddahe_com_editor')->get_proxy_ip();

    $proxyExpire = $zblogx("iddahe_com_editor")->get_proxy_expire();
    $proxyExpire = $proxyExpire ? $proxyExpire : 1;

    $proxyLastTime = $zblogx('iddahe_com_editor')->get_proxy_last_time();
    $proxyLastTime = $proxyLastTime ? $proxyLastTime : 0;

    if ($lastProxyIp && (time() - $proxyLastTime) < (60 * $proxyExpire)) {
        return $lastProxyIp; // 最大 5 分钟刷新一次 IP
    }

    $proxyApi = $zblogx('iddahe_com_editor')->get_proxy_api();

    if (empty($proxyApi)) {
        return false;
    }

    // 122.12.12.12:4040,120(秒)
    // $response = explode(',', trim($response));
    $response = file_get_contents($proxyApi);
    $response = explode(',', trim($response));

    $ip = isset($response[0]) ? $response[0] : '';
    $ip = str_replace(array("\n", "\r"), '', $ip);

    $logDir = __DIR__ . '/logs';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $date = date('Y-m-d');
    $time = date('Y-m-d H:i:s');
    $logFile = "{$logDir}/ip-{$date}.txt";
    file_put_contents($logFile, "$time 提取了IP => $ip\n", FILE_APPEND);

    $zblogx('iddahe_com_editor')->set_proxy_ip($ip);
    $zblogx('iddahe_com_editor')->set_proxy_last_time(time());

    return trim($ip);
}

function iddahe_com_editor_get_post_user_id()
{
    global $zbp, $zblogx;

    $member = $zblogx("iddahe_com_editor")->get_post_member();

    if ('rand' == $member) {
        $members = $zbp->GetMemberList('*', array(array('=', 'mem_Level', 1))); // 必须管理员
        $memberIds = array();
        foreach ($members as $member) {
            $memberIds[] = $member->ID;
        }
        return $memberIds[mt_rand(0, count($memberIds) - 1)];
    }

    return $member;
}

function iddahe_com_editor_to_utf8($content)
{
    $encode = mb_detect_encoding($content, ['ASCII', 'GB2312', 'GBK', 'UTF-8', 'EUC-CN', 'CP936']);

    if ('UTF-8' != $encode) {
        return @iconv($encode, 'UTF-8//IGNORE', $content);
    }

    return $content;
}

function iddahe_com_editor_is_utf8($content)
{
    $encode = mb_detect_encoding($content, ['ASCII', 'GB2312', 'GBK', 'UTF-8', 'EUC-CN', 'CP936']);

    return 'UTF-8' == $encode;
}

function iddahe_com_editor_xiala($keyword)
{
    $keywords = iddahe_com_editor_baiduxialaV2($keyword);

    if (empty($keywords)) {
        $keywords = iddahe_com_editor_baiduxiala($keyword);
    }

    return $keywords;
}

function iddahe_com_editor_baiduxiala($keyword)
{
    $encodeKeyword = urlencode($keyword);

    $data = iddahe_com_editor_curl(
        "https://sp0.baidu.com/5a1Fazu8AA54nxGko9WTAnF6hhy/su?wd={$encodeKeyword}",
        array(),
        false,
        15,
        false
    );
    $data = str_replace(');', '', $data);
    $data = str_replace('window.baidu.sug(', '', $data);
    $data = str_replace('q:', '"q":', $data);
    $data = str_replace('p:false', '"p":"false"', $data);
    $data = str_replace('p:true', '"p":"true"', $data);
    $data = str_replace('s:', '"s":', $data);

    if (!iddahe_com_editor_is_utf8($data)) {
        return array(); // 不是 utf8 不要
    }

    $data = json_decode($data, true);

    if (is_array($data) && isset($data['s'])) {
        $words = array();
        foreach ($data['s'] as $datum) {
            if (iddahe_com_editor_valid_keyword($datum)) {
                $words[] = trim($datum);
            }
        }
        return $words;
    }

    return array();
}

function iddahe_com_editor_baiduxialaV2($keyword)
{
    $encodeKeyword = urlencode($keyword);

    /*$json = iddahe_com_editor_curl(
        "https://www.baidu.com/sugrec?pre=1&p=3&ie=utf-8&json=1&prod=pc&from=pc_web&wd={$keyword}",
        $headers,
        false,
        15,
        false
    );*/

    $json = iddahe_com_editor_file_get(
        "https://www.baidu.com/sugrec?pre=1&p=3&ie=utf-8&json=1&prod=pc&from=pc_web&wd={$encodeKeyword}"
    );

    $data = json_decode($json, true);
    $data = is_array($data) ? $data : array();
    $data = isset($data['g']) ? $data['g'] : array();

    $words = array();
    foreach ($data as $datum) {
        if (isset($datum['q'])) {
            $words[] = $datum['q'];
        }
    }

    return $words;
}

function iddahe_com_editor_text_chunk($text, $length = 100)
{
    $chunks = array();

    $textLength = mb_strlen($text);

    while ($textLength) {
        $chunks[] = mb_substr($text, 0, $length, 'utf8');
        $text = mb_substr($text, $length, $textLength, 'utf8');
        $textLength = mb_strlen($text);
    }

    return $chunks;
}

function iddahe_com_editor_valid_keyword($keyword)
{
    foreach (iddahe_com_editor_get_mmps() as $mmp) {
        if (false !== mb_strpos($keyword, $mmp)) {
            return false;
        }
    }

    $rules = array(
        '/[\x{4e00}-\x{9fa5}]/u',
        '/[0-9]/',
        '/[a-z]/i',
    );

    $keyword = preg_replace($rules, '', trim($keyword));

    return !(mb_strlen($keyword) > 0); // 大于 0 就是包含了特殊字符，就不能要
}

function iddahe_com_editor_get_mmps()
{
    $filename = __DIR__ . '/resource/mmp.txt';

    if (!is_file($filename)) {
        return array();
    }
    $string = str_replace("\r", '', file_get_contents($filename));

    return array_values(array_unique(array_filter(explode("\n", $string))));
}

/**
 * 此处应感谢流年 ！！！！！
 *
 * @return string
 */
function iddahe_com_editor_get_script()
{
    if (substr(strtolower(PHP_OS), 0, 3) == 'win') {
        $ini = ini_get_all();
        $path = $ini['extension_dir']['local_value'];
        $php_path = str_replace('\\', '/', $path);
        $php_path = str_replace(array('/ext/', '/ext'), array('/', '/'), $php_path);
        if (strpos($php_path, ':') === false) {
            $path = $ini['upload_tmp_dir']['local_value'];
            $php_path = str_replace('\\', '/', $path);
            $php_path = str_replace(array('/temp/', '/temp'), array('/', '/'), $php_path);
        }
        if (strpos($php_path, ':') === false) {
            $path = $ini['session.save_path']['local_value'];
            $php_path = str_replace('\\', '/', $path);
            $php_path = str_replace(array('/temp/', '/temp'), array('/', '/'), $php_path);
        }
        $real_path = $php_path . 'php.exe';
    } else {
        $real_path = PHP_BINDIR . '/php';
    }
    if (strpos($real_path, 'ephp.exe') !== false) {
        $real_path = str_replace('ephp.exe', 'php.exe', $real_path);
    }

    $script = __DIR__ . '/script/editor.php';

    return 'su -c "' . $real_path . ' ' . $script . '" -s /bin/sh www';
}

// 确保所有操作都执行完了再进行提示
function iddahe_com_editor_hint($status, $message, $action = null)
{
    register_shutdown_function(function () use ($status, $message, $action) {
        global $zbp;
        $zbp->SetHint($status, $message);
        $action && Redirect($action);
    });
}

function iddahe_com_editor_ends_with($haystack, $needles)
{
    foreach ((array)$needles as $needle) {
        if (substr($haystack, -strlen($needle)) === (string)$needle) {
            return true;
        }
    }

    return false;
}

function iddahe_com_editor_curl_302($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 302 redirect

    $data = curl_exec($ch);
    $Headers = curl_getinfo($ch);

    curl_close($ch);

    if ($data != $Headers) {
        return $Headers["url"];
    }

    return $url;
}

function iddahe_com_editor_formatter($content)
{
    $string = '';

    $content = str_replace(array('<br>', '</br>', '<p>', '</p>'), "\n", $content);

    $values = explode("\n", $content);

    foreach ($values as $value) {
        $value = filter_urls2($value);

        if (!empty(trim($value))) {
            $string .= "<p>{$value}</p>";
        }
    }

    return $string;
}

function iddahe_com_editor_download_pic($originUrl)
{
    global $zbp;

    if (false !== strpos($originUrl, $zbp->host)) {
        return $originUrl; // 水印本地图片
    }

    $date = date('Ymd'); // url format
    $dir = __DIR__ . "/../../../zb_users/upload/editor/{$date}";

    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $filename = strtolower(uniqid()) . '.jpg';
    $fullFilename = "{$dir}/{$filename}";
    $url = @iddahe_com_editor_curl_302($originUrl);

    for ($i = 0; $i < 3; $i++) { // 有人说下载失败，那么我就尝试三次，还是失败那就认命
        try {
            $headers = array("User-Agent: " . get_rand_ua());
            @file_put_contents($fullFilename, @iddahe_com_editor_curl($url, $headers, false, 15, false, false));
            if (is_file($fullFilename) && filesize($fullFilename) > 10) {
                if (false === stripos($zbp->host, '//localhost')) {
                    return "{$zbp->host}zb_users/upload/editor/{$date}/{$filename}";
                } else {
                    $host = str_replace('|', '', $zbp->Config('system')->ZC_BLOG_HOST);
                    return "{$host}zb_users/upload/editor/{$date}/{$filename}";
                }
            }
        } catch (\Exception $e) {
            // Just so so
        }
    }

    return $originUrl;
}
