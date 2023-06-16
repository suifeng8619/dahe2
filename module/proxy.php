<?php

if (!$zbp->CheckRights('root')) {
    $zbp->ShowError(6);
    die();
}

if (GetVars('proxy_save')) {
    $zblogx("iddahe_com_editor")->set_proxy_status(GetVars('proxy_status'));
    $zblogx("iddahe_com_editor")->set_proxy_api(GetVars('proxy_api'));
    $zblogx("iddahe_com_editor")->set_proxy_username(GetVars('proxy_username'));
    $zblogx("iddahe_com_editor")->set_proxy_password(GetVars('proxy_password'));
    $zblogx("iddahe_com_editor")->set_proxy_expire(GetVars('proxy_expire'));

    iddahe_com_editor_hint('good', '已保存', '?action=proxy');
}

if (!$zblogx("iddahe_com_editor")->get_proxy_expire()) {
    $zblogx("iddahe_com_editor")->set_proxy_expire(1);
}

$config = $zblogx("iddahe_com_editor");

?>
<style>
  .iddahe_form_table tr:nth-child(n+1) td:first-child {
    text-align: right;
    font-weight: bold
  }

  form.iddahe_form_table span.remark {
    font-size: 13px;
    color: grey;
  }
</style>
<form method="post" class="iddahe_form_table">
    <?php
    if (function_exists('CheckIsRefererValid')) {
        echo '<input type="hidden" name="csrfToken" value="' . $zbp->GetCSRFToken() . '">';
    }
    ?>
  <table style="width: 800px">
    <tr>
      <td>开启代理</td>
      <td>
        <input type="text" class="checkbox" name="proxy_status" value="<?php echo $config->get_proxy_status() ?>">
        <span class="remark">
          开启代理（服务器不在大陆，需咨询对方客服是否支持海外机器调用；我本人使用的是
          <a href="https://www.kuaidaili.com/?ref=rg3jlsko0ymg" target="_blank">
            【 快代理 】
          </a>
          ）
        </span>
      </td>
    </tr>
    <tr>
      <td>代理 API</td>
      <td>
        <input type="text" name="proxy_api" value="<?php echo $config->get_proxy_api() ?>" style="width: 100%">
        <p>
            <?php $url = $zbp->host . "zb_users/plugin/iddahe_com_editor/main.php?action=logs"; ?>
          <span class="remark">
            API 需要设置每次提取一个 IP (使用 TXT 类型 \n 换行输出即可)
            <a href="https://www.iddahe.com/guide/46.html" target="_blank">【 代理配置教程 】</a>
            <a href="<?php echo $url; ?>" target="_blank">【 IP 提取日志 】</a>
          </span>
        </p>
      </td>
    </tr>
    <tr>
      <td>代理账户</td>
      <td>
        <input
          type="text"
          name="proxy_username"
          style="width: 200px"
          value="<?php echo $config->get_proxy_username(); ?>">
        <span class="remark">【可选】如果需要验证还请提供</span>
      </td>
    </tr>
    <tr>
      <td>代理密码</td>
      <td>
        <input
          type="text"
          name="proxy_password"
          style="width: 200px"
          value="<?php echo $config->get_proxy_password(); ?>">
        <span class="remark">【可选】如果需要验证还请提供</span>
      </td>
    </tr>
    <tr>
      <td>IP 时效</td>
      <td>
        <input
          type="number"
          min="1"
          max="5"
          name="proxy_expire"
          style="width: 200px"
          value="<?php echo $config->get_proxy_expire(); ?>">
        <span class="remark">&nbsp&nbspIP 有效时长（分钟）推荐动态 IP（1 ～ 5 分钟）</span>
      </td>
    </tr>
    <tr>
      <td>开始体验</td>
      <td>
        <input type="submit" name="proxy_save" value="保存配置">
        <span class="remark">&nbsp注：代理功能为可选，如果你服务器 IP 可正常提取内容，可以忽略此配置。</span>
      </td>
    </tr>
  </table>
</form>
