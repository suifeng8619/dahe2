<?php

if (!$zbp->CheckRights('root')) {
    $zbp->ShowError(6);
    die();
}

if (GetVars('editor_save')) {
    if (function_exists('CheckIsRefererValid')) {
        CheckIsRefererValid();// 检查 csrfToken
    }

    $zblogx("iddahe_com_editor")->set_post_start(GetVars('post_start'));
    $zblogx("iddahe_com_editor")->set_post_end(GetVars('post_end'));

    iddahe_com_editor_set_section_file('post_start_contents', GetVars('post_start_contents'));
    iddahe_com_editor_set_section_file('post_end_contents', GetVars('post_end_contents'));

    iddahe_com_editor_hint('good', '已保存', '?action=section');
}

if (is_null($zblogx("iddahe_com_editor")->get_post_start())) {
    $s = <<<S
本篇文章给大家谈谈{输入关键词}，以及{联想词}对应的知识点，希望对各位有所帮助，不要忘了收藏本站喔。
今天给各位分享{输入关键词}的知识，其中也会对{联想词}进行解释，如果能碰巧解决你现在面临的问题，别忘了关注本站，现在开始吧！
S;
    $e = <<<E
关于{输入关键词}和{联想词}的介绍到此就结束了，不知道你从中找到你需要的信息了吗 ？如果你还想了解更多这方面的信息，记得收藏关注本站。
{输入关键词}的介绍就聊到这里吧，感谢你花时间阅读本站内容，更多关于{联想词}、{输入关键词}的信息别忘了在本站进行查找喔。
E;
    iddahe_com_editor_set_section_file('post_start_contents', $s);
    iddahe_com_editor_set_section_file('post_end_contents', $e);

    $zblogx("iddahe_com_editor")->set_post_start(0);
    $zblogx("iddahe_com_editor")->set_post_end(0);

    Redirect('?action=section'); // 刷新一次防止 OB 原因后面读取不到
}

$config = $zblogx("iddahe_com_editor");

?>
<style>
  #iddahe_form_table tr:nth-child(n+1) td:first-child {
    text-align: right;
    font-weight: bold
  }

  #iddahe_form_table tr:nth-child(n+1) td:last-child {
    color: grey;
    font-size: 13px;
  }
</style>
<form method="post">
    <?php
    if (function_exists('CheckIsRefererValid')) {
        echo '<input type="hidden" name="csrfToken" value="' . $zbp->GetCSRFToken() . '">';
    }
    ?>
  <table id="iddahe_form_table">
    <tr>
      <td class="config_name" style="width: 150px">
        <strong>配置项</strong>
      </td>
      <td style="text-align: center;width: 900px">
        <strong>这里是备注，不明白的先看这里！！！</strong>
      </td>
    </tr>
    <tr>
      <td>插入位置</td>
      <td>
        <input
          type="text"
          name="post_start"
          class="checkbox"
          value="<?php echo $config->get_post_start(); ?>">
        <span class="remark">文章开头</span>
        &nbsp&nbsp&nbsp&nbsp&nbsp
        <input
          type="text"
          name="post_end"
          class="checkbox"
          value="<?php echo $config->get_post_end(); ?>">
        <span class="remark">文章结尾</span>
      </td>
    </tr>
    <tr>
      <td>文章开头段落</td>
      <td>
        <textarea
          style="width: 100%;padding: 5px"
          name="post_start_contents"
          rows="10"><?php echo iddahe_com_editor_get_section_file('post_start_contents'); ?></textarea>
        <span class="remark">支持使用 {输入关键词}、{联想词} 标签进行构建段落内容，一行一条，程序随机抽取一条进行使用</span>
      </td>
    </tr>
    <tr>
      <td>文章结尾段落</td>
      <td>
        <textarea
          style="width: 100%;padding: 5px"
          name="post_end_contents"
          rows="10"><?php echo iddahe_com_editor_get_section_file('post_end_contents'); ?></textarea>
        <span class="remark">支持使用 {输入关键词}、{联想词} 标签进行构建段落内容，一行一条，程序随机抽取一条进行使用</span>
      </td>
    </tr>
    <tr>
      <td class="config_name">
        <strong>开始体验</strong>
      </td>
      <td colspan="2">
        <input type="submit" name="editor_save" value="保存设置">
        <span style="color: grey;font-size: 13px">使用途中如有疑问，可加作者 QQ：2307903507 交流</span>
      </td>
    </tr>
  </table>
</form>
