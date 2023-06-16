<?php

if (!$zbp->CheckRights('root')) {
    $zbp->ShowError(6);
    die();
}

if (GetVars('rules_save')) {
    if (function_exists('CheckIsRefererValid')) {
        CheckIsRefererValid();// 检查 csrfToken
    }

    $zblogx("iddahe_com_editor")->set_except_rules(GetVars('except_rules'));

    iddahe_com_editor_hint('good', '已保存', '?action=filter');
}
$config = $zblogx("iddahe_com_editor");
?>
<form method="post">
    <?php
    if (function_exists('CheckIsRefererValid')) {
        echo '<input type="hidden" name="csrfToken" value="' . $zbp->GetCSRFToken() . '">';
    }
    ?>
  <table>
    <tr style="text-align: center">
      <td><strong># 格式：过滤规则<=>替换值，一行一条，支持符号 '*' 进行通配 #</strong></td>
    </tr>
    <tr>
      <td>
        <textarea
          style="padding: 10px;"
          placeholder="一行一条"
          name="except_rules"
          cols="80"
          rows="10"><?php echo $config->get_except_rules(); ?></textarea>
      </td>
    </tr>
    <tr>
      <td style="text-align: center;">
        <input type="submit" name="rules_save" value="保存规则" style="width: 200px">
      </td>
    </tr>
  </table>
</form>
<div>
  <p style="font-weight: bold;padding: 5px 5px 5px 0;">
    # 案例: baidu.com<=>google.com 或 *.com<=>google.com 或 baidu.com<=>空白这里为空
  </p>
</div>
