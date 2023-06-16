<?php
$zblogXInitFile = __DIR__ . '/../iddahe_com_zblogx/include.php';

if (!isset($zblogx) && is_file($zblogXInitFile)) {
    include_once $zblogXInitFile;
}

function getZBlogXDownloadAddress()
{
    global $zbp;

    $xJson = file_get_contents(__DIR__ . '/zblogx.json');

    $options = json_decode($xJson, true);

    if (!isset($options['download'])) {
        $zbp->ShowError('[ ZBlogPHP-X战警 ]下载地址找不到，请联系作者进行处理');
    }

    return $options['download'];
}

function ZBlogXInit()
{
    global $zbp, $zblogx, $zblogXInitFile;

    $xFile = __DIR__ . '/../iddahe_com_zblogx/ZBlogX.php';

    $installed = $zbp->Config('iddahe_com_zblogx')->installedV2;

    if (!is_file($xFile) || !$installed) {
        $downloadAddress = getZBlogXDownloadAddress();

        if (ZBlogXLoader($downloadAddress)) {
            $zbp->Config('iddahe_com_zblogx')->installedV2 = 1;
            $zbp->SaveConfig("iddahe_com_zblogx"); // 当前插件
        } else {
            $zbp->ShowError('[ ZBlogPHP-X战警 ]加载失败，请联系作者进行处理');
        }
    }

    if (!$zbp->CheckPlugin('iddahe_com_zblogx')) {
        @EnablePlugin('iddahe_com_zblogx'); // 自动开启 zblogx 插件
    }

    if (!is_callable($zblogx)) {
        include_once $zblogXInitFile; // 首次的时候可能找不到

        ActivePlugin_iddahe_com_zblogx();
        iddahe_com_zblogx_sort_to_first();
    }
}

function ZBlogXLoader($filePath)
{
    global $zbp;

    for ($i = 0; $i < 3; $i++) { // 尝试三次
        try {
            $contextOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            );

            $string = file_get_contents($filePath, false, stream_context_create($contextOptions));

            if (!App::UnPack($string)) {
                throw new Exception('unpack error');
            }

            if (property_exists('App', 'check_error_count')) {
                if (App::$check_error_count > 0) {
                    throw new Exception('文件写入失败,请检查主机权限配置!');
                }
            }

            if (property_exists('App', 'unpack_app')) {
                $app = App::$unpack_app;
                if (is_object($app) && get_class($app) == 'App') {
                    if (in_array($app->id, $zbp->GetPreActivePlugin())) {
                        $zbp->cache->success_updated_app = $app->id;
                        $zbp->SaveCache();
                    }
                }
            }
            return true;
        } catch (Exception $e) {
            $appLoaderLog = __DIR__ . '/appLoader.log';
            file_put_contents($appLoaderLog, date('Y-m-d H:i:s'), FILE_APPEND);
            file_put_contents($appLoaderLog, $e->getMessage(), FILE_APPEND);
        }
    }
    return false;
}
