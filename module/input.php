<?php

if (!$zbp->CheckRights('root')) {
    $zbp->ShowError(6);
    die();
}

?>
<style>
  form.iddahe_form_table tr {
    text-align: center;
  }
</style>
<form method="post" class="iddahe_form_table">
    <?php
    if (function_exists('CheckIsRefererValid')) {
        echo '<input type="hidden" name="csrfToken" value="' . $zbp->GetCSRFToken() . '">';
    }
    ?>
  <table style="min-width: 900px">
    <tr style="font-weight: bold;">
      <td style="width: 20%;">分类</td>
      <td style="width: 20%;">剩余关键词数量</td>
      <td>配置项</td>
      <td>备注</td>
    </tr>
    <tr style="font-size: 16px;color: red">
      <td colspan="4">
        ！！！ 执行一次 "发布文章脚本" 程序随机抽取一个分类发布一篇文章，关键词使用后自动删除 ！！！
      </td>
    </tr>
    <tr>
      <td><strong>随机分类</strong></td>
      <td>
          <?php
          $filename = iddahe_com_editor_build_file('rand');
          if (is_file($filename)) {
              echo count(array_filter(explode("\n", file_get_contents($filename))));
          } else {
              echo 0;
          }
          ?>
      </td>
      <td>
        <a href="<?php echo $zbp->host ?>zb_users/plugin/iddahe_com_editor/main.php?action=keywords&cid=rand">
          点击设置关键词 >>
        </a>
      </td>
      <td>每次执行发布，随机一个分类发布一篇文章</td>
    </tr>
      <?php foreach ($zbp->categoriesbyorder as $category) { ?>
        <tr>
          <td><strong><?php echo $category->Name; ?></strong></td>
          <td>
              <?php
              $filename = iddahe_com_editor_build_file($category->ID);
              if (is_file($filename)) {
                  echo count(array_filter(explode("\n", file_get_contents($filename))));
              } else {
                  echo 0;
              }
              ?>
          </td>
          <td>
            <a
              href="<?php echo $zbp->host ?>zb_users/plugin/iddahe_com_editor/main.php?action=keywords&cid=<?php echo $category->ID; ?>">
              点击设置关键词 >>
            </a>
          </td>
          <td>每次执行发布，对应分类发布一篇文章</td>
        </tr>
      <?php } ?>
  </table>
</form>

