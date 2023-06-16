<?php
require_once __DIR__ . '/../../../zb_system/function/c_system_base.php';

require_once __DIR__ . '/../../../zb_system/function/c_system_admin.php';

require_once __DIR__ . '/appLoader.php';

$zbp->Load();

$action = 'root';

if (!$zbp->CheckRights($action)) {
    $zbp->ShowError(6);
    die();
}

if (!$zbp->CheckPlugin('iddahe_com_editor')) {
    $zbp->ShowError(48);
    die();
}

if (count($_POST) > 0) {
    CheckIsRefererValid();
}

ZBlogXInit();

$blogtitle = '问答组合文章（By iddahe.com）';

$action = isset($_GET['action']) ? $_GET['action'] : null;
$settingClass = ($action == 'setting' || empty($action)) ? 'm-now' : '';
$inputClass = $action == 'input' ? 'm-now' : '';
$keywordsClass = $action == 'keywords' ? 'm-now' : '';
$guideClass = $action == 'guide' ? 'm-now' : '';
$filterClass = $action == 'filter' ? 'm-now' : '';
$sectionClass = $action == 'section' ? 'm-now' : '';

require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';

?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle; ?></div>
  <div class="SubMenu">
    <ul>
      <li>
        <a href="?action=setting">
          <span class="m-left <?php echo $settingClass; ?>">基础设置</span>
        </a>
      </li>
        <?php if (iddahe_com_editor_component_has('TitleExtension')) {
            include __DIR__ . '/component/TitleExtension/menu.php';
        } ?>
        <?php if (iddahe_com_editor_component_has('ZCYContent')) {
            include __DIR__ . '/component/ZCYContent/menu.php';
        } ?>
        <?php if (iddahe_com_editor_component_has('WXCPicture')) {
            include __DIR__ . '/component/WXCPicture/menu.php';
        } ?>
      <li>
        <a href="?action=section">
          <span class="m-left <?php echo $sectionClass; ?>">首尾段落</span>
        </a>
      </li>
      <li>
        <a href="?action=input">
          <span class="m-left <?php echo $inputClass; ?>">关键词录入</span>
        </a>
      </li>
      <li>
        <a href="?action=filter">
          <span class="m-left <?php echo $filterClass; ?>">自定义过滤</span>
        </a>
      </li>
      <li>
        <a href="?action=guide">
          <span class="m-left <?php echo $guideClass; ?>">必看指南</span>
        </a>
      </li>
      <li>
        <a href="https://1024.iddahe.com/app/logs" target="_blank">
          <span class="m-left">更新日志</span>
        </a>
      </li>
      <li>
        <a href="https://app.zblogcn.com/?auth=af4999d4-6725-481f-a1e8-b644db3671eb"
           target="_blank">
          <span class="m-left">其他作品 >></span>
        </a>
      </li>
    </ul>
  </div>
  <div id="divMain2">
      <?php
      if ('keywords' == $action) {
          include_once __DIR__ . '/module/keywords.php';
      } elseif ('proxy' == $action) {
          include_once __DIR__ . '/module/proxy.php';
      } elseif ('logs' == $action) {
          $files = glob(__DIR__ . '/logs/*.txt');
          foreach ($files as $file) {
              $basename = basename($file);
              $url = "{$zbp->host}zb_users/plugin/iddahe_com_editor/logs/{$basename}";
              echo "<p><a href=\"$url\" target=\"_blank\">{$url}</a></p>";
          }
      } elseif ('input' == $action) {
          include_once __DIR__ . '/module/input.php';
      } elseif ('guide' == $action) {
          include_once __DIR__ . '/module/guide.php';
      } elseif ('filter' == $action) {
          include_once __DIR__ . '/module/filter.php';
      } elseif ('section' == $action) {
          include_once __DIR__ . '/module/section.php';
      } elseif ('title-ext' == $action) {
          if (iddahe_com_editor_component_has('TitleExtension')) {
              include __DIR__ . '/component/TitleExtension/admin.php';
          }
      } elseif ('zcy-content' == $action) {
          if (iddahe_com_editor_component_has('ZCYContent')) {
              include __DIR__ . '/component/ZCYContent/admin.php';
          }
      } elseif ('wxc-picture' == $action) {
          if (iddahe_com_editor_component_has('WXCPicture')) {
              include __DIR__ . '/component/WXCPicture/admin.php';
          }
      } else {
          include_once __DIR__ . '/module/setting.php';
      }
      ?>
  </div>
</div>
<script type="text/javascript">
  AddHeaderIcon("<?php echo $bloghost . 'zb_users/plugin/iddahe_com_editor/logo.png';?>");
</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';

RunTime();

?>
