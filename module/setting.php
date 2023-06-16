<?php

if (!$zbp->CheckRights('root')) {
    $zbp->ShowError(6);
    die();
}

if (GetVars('editor_save')) {
    if (function_exists('CheckIsRefererValid')) {
        CheckIsRefererValid();// 检查 csrfToken
    }

    $zblogx("iddahe_com_editor")->set_post_status(GetVars('post_status'));
    $zblogx("iddahe_com_editor")->set_post_member(GetVars('post_member'));
    $zblogx("iddahe_com_editor")->set_post_time_diy(GetVars('post_time_diy'));
    $zblogx("iddahe_com_editor")->set_post_start_at(GetVars('post_start_at'));
    $zblogx("iddahe_com_editor")->set_post_end_at(GetVars('post_end_at'));
    $zblogx("iddahe_com_editor")->set_title_format(GetVars('title_format'));
    $zblogx("iddahe_com_editor")->set_rand_top(GetVars('rand_top'));
    $zblogx("iddahe_com_editor")->set_default_title(GetVars('default_title'));
    $zblogx("iddahe_com_editor")->set_keyword_to_tag(GetVars('keyword_to_tag'));

    $zblogx("iddahe_com_editor")->set_keyword_join(GetVars('keyword_join'));
    $zblogx("iddahe_com_editor")->set_post_list_status(GetVars('post_list_status'));
    $zblogx("iddahe_com_editor")->set_generate_img(GetVars('generate_img'));

    iddahe_com_editor_hint('good', '已保存', '?action=setting');
}

//if (!$zblogx("iddahe_com_editor")->get_token()) {
    $zblogx("iddahe_com_editor")->set_token(uniqid());
    $zblogx("iddahe_com_editor")->set_post_status(0);
    $zblogx("iddahe_com_editor")->set_post_member('rand');
    $zblogx("iddahe_com_editor")->set_post_time_diy('no');
    $zblogx("iddahe_com_editor")->set_post_start_at(date('Y-m-d 00:00:00'));
    $zblogx("iddahe_com_editor")->set_post_end_at(date('Y-m-d 23:59:59'));
    $zblogx("iddahe_com_editor")->set_title_format('{输入关键词}（{联想词}）');
    $zblogx("iddahe_com_editor")->set_rand_top('no');
    $zblogx("iddahe_com_editor")->set_keyword_to_tag('yes');

    $zblogx("iddahe_com_editor")->set_keyword_join('no');
    $zblogx("iddahe_com_editor")->set_post_list_status('yes');
    $zblogx("iddahe_com_editor")->set_generate_img('yes');
//}

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
      <td style="text-align: center">
        <strong>配置值</strong>
      </td>
      <td style="text-align: center;width: 800px">
        <strong>这里是备注，不明白的先看这里！！！</strong>
      </td>
    </tr>
    <tr>
      <td>标题格式</td>
      <td style="font-size: 14px;" colspan="2">
        <input
          type="text"
          name="title_format"
          value="<?php echo $config->get_title_format(); ?>"
          style="width: 100%">
        <p style="font-size: 13px;">
          使用 {输入关键词} 和 {联想词} 构建标题格式，比如: {输入关键词}_{联想词}，可用 "|" 分隔多个
        </p>
      </td>
    </tr>
    <tr>
      <td>内置标题扩展</td>
      <td>
        <select name="default_title" style="min-width: 125px;">
            <?php $defaultTitle = $config->get_default_title(); ?>
          <option value="yes" <?php echo $defaultTitle == 'yes' ? 'selected' : '' ?>>yes</option>
          <option value="no" <?php echo $defaultTitle == 'no' ? 'selected' : '' ?>>no</option>
        </select>
      </td>
      <td>开启后，若联想词加载失败，系统自动使用内置标题扩展，如："{关键词}的简单介绍"、"关于{关键词}的信息" ... ...</td>
    </tr>
    <tr>
      <td class="config_name">
        <strong>生成图片</strong>
      </td>
      <td>
        <select name="generate_img" style="min-width: 125px;">
            <?php $generateImg = $config->get_generate_img(); ?>
          <option value="yes" <?php echo 'yes' == $generateImg ? 'selected' : '' ?>>yes</option>
          <option value="no" <?php echo 'no' == $generateImg ? 'selected' : '' ?>>no</option>
        </select>
      </td>
      <td>开启后文章自动插入水印图片（自定义背景查看 <a href="?action=guide">必看指南</a>），若关闭，发布文章将无图</td>
    </tr>
    <tr>
      <td class="config_name">
        <strong>发布文章状态</strong>
      </td>
      <td>
        <select name="post_status" style="min-width: 125px;">
            <?php $postStatus = $config->get_post_status(); ?>
          <option value="0" <?php echo 0 == $postStatus ? 'selected' : '' ?>>公开</option>
          <option value="1" <?php echo 1 == $postStatus ? 'selected' : '' ?>>草稿</option>
          <option value="2" <?php echo 2 == $postStatus ? 'selected' : '' ?>>审核</option>
        </select>
      </td>
      <td>对应【文章管理】里面的文章状态，如果你想用其他工具定时发布，此处不要设置为【公开】</td>
    </tr>
    <tr>
      <td class="config_name">
        <strong>文章随机推荐</strong>
      </td>
      <td>
        <select name="rand_top" style="min-width: 125px;">
            <?php $randTop = $config->get_rand_top(); ?>
          <option value="yes" <?php echo 'yes' == $randTop ? 'selected' : '' ?>>yes</option>
          <option value="no" <?php echo 'no' == $randTop ? 'selected' : '' ?>>no</option>
        </select>
      </td>
      <td>对应【文章管理】里面的置顶功能，开启后发布文章时将以 10% 的概率将文章设置为置顶（最多置顶 20 篇）</td>
    </tr>
    <tr>
      <td class="config_name">
        <strong>文章使用作者</strong>
      </td>
      <td>
        <select name="post_member" style="min-width: 125px;">
            <?php $postMember = $config->get_post_member(); ?>
          <option value="rand" <?php echo 'rand' == $postMember ? 'selected' : ''; ?>>随机</option>
            <?php $members = $zbp->GetMemberList('*', array(array('=', 'mem_Level', 1))); ?>
            <?php foreach ($members as $member) { ?>
              <option
                value="<?php echo $member->ID; ?>"
                  <?php echo $postMember == $member->ID ? 'selected' : ''; ?>>
                  <?php echo $member->Name; ?>
              </option>
            <?php } ?>
        </select>
      </td>
      <td>对应【用户管理】里面的账户，权限必须是【管理员】注意：ZBlog会默认使用当前登录用户</td>
    </tr>
    <tr>
      <td>关键词作标签</td>
      <td>
        <select name="keyword_to_tag" style="min-width: 125px;">
            <?php $keywordToTag = $config->get_keyword_to_tag(); ?>
          <option value="yes" <?php echo $keywordToTag == 'yes' ? 'selected' : '' ?>>yes</option>
          <option value="no" <?php echo $keywordToTag == 'no' ? 'selected' : '' ?>>no</option>
        </select>
      </td>
      <td>开启后【生成文章的关键词】将自动作为文章标签进行绑定</td>
    </tr>
    <tr>
      <td>关键词插入</td>
      <td>
        <select name="keyword_join" style="min-width: 125px;">
            <?php $keywordJoin = $config->get_keyword_join(); ?>
          <option value="yes" <?php echo $keywordJoin == 'yes' ? 'selected' : '' ?>>yes</option>
          <option value="no" <?php echo $keywordJoin == 'no' ? 'selected' : '' ?>>no</option>
        </select>
      </td>
      <td>开启后【生成文章的关键词】将强制随机插入到文章内容中（30% 的概率）</td>
    </tr>
    <tr>
      <td>文章开头目录</td>
      <td>
        <select name="post_list_status" style="min-width: 125px;">
            <?php $postListStatus = $config->get_post_list_status(); ?>
          <option value="yes" <?php echo $postListStatus == 'yes' ? 'selected' : '' ?>>yes</option>
          <option value="no" <?php echo $postListStatus == 'no' ? 'selected' : '' ?>>no</option>
        </select>
      </td>
      <td>是否开启文章开头的目录列表（yes｜no）</td>
    </tr>
    <tr>
      <td class="config_name">
        <strong>自定义文章时间</strong>
      </td>
      <td colspan="2" style="font-size: 14px">
        <select name="post_time_diy" style="width: 80px;">
            <?php $postTimeDiy = $config->get_post_time_diy(); ?>
          <option value="no" <?php echo $postTimeDiy == 'no' ? 'selected' : '' ?>>no</option>
          <option value="yes" <?php echo $postTimeDiy == 'yes' ? 'selected' : '' ?>>yes</option>
        </select>
        -
        <input
          type="text"
          name="post_start_at"
          id="post_start_at"
          value="<?php echo $config->get_post_start_at(); ?>"
          style="margin: 5px 0 0 0;">
        -
        <input
          type="text"
          name="post_end_at"
          id="post_end_at"
          value="<?php echo $config->get_post_end_at(); ?>"
          style="margin: 5px 0 0 0;">
        <span style="font-size: 13px">【yes 状态】在该时间范围随机一个时间点作为文章最终的显示时间</span>
      </td>
    </tr>
    <tr>
      <td>发布文章脚本</td>
      <td colspan="3" style="text-align:left;;font-size: 14px">
        <p>
            <?php $token = trim($zblogx("iddahe_com_editor")->get_token()); ?>
            <?php $api = "{$zbp->host}zb_users/plugin/iddahe_com_editor/script/editor.php?token={$token}"; ?>
          <textarea
            style="width: 100%;padding: 5px"
            rows="2"><?php echo iddahe_com_editor_get_script(); ?></textarea>
          <br>
          <span style="font-size: 14px;color: black">
           定时发布，复制此内容到【宝塔计划任务】中，添加 Shell 脚本任务（建议最小间隔 2 ～ 5 分钟）
            /
           <a href="<?php echo $api; ?>" target="_blank">
             <strong>【 手动发布 】</strong>
           </a>
           <a href="<?php echo "{$zbp->host}zb_users/plugin/iddahe_com_editor/main.php?action=proxy"; ?>">
             <strong>【 代理配置 】</strong>
           </a>
          </span>
        </p>
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
<script type="text/javascript" src="/zb_system/script/jquery-ui-timepicker-addon.js"></script>
<script>
  $.datepicker.regional['zh-CN'] = {
    closeText: '完成',
    prevText: '上个月',
    nextText: '下个月',
    currentText: '现在',
    monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
    monthNamesShort: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
    dayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
    dayNamesShort: ['周日', '周一', '周二', '周三', '周四', '周五', '周六'],
    dayNamesMin: ['日', '一', '二', '三', '四', '五', '六'],
    weekHeader: '周',
    dateFormat: 'yy-mm-dd',
    firstDay: 1,
    isRTL: false,
    showMonthAfterYear: true,
    yearSuffix: ' 年  '
  };

  $.datepicker.setDefaults($.datepicker.regional['zh-CN']);

  $.timepicker.regional['zh-CN'] = {
    timeOnlyTitle: '时间',
    timeText: '时间',
    hourText: '小时',
    minuteText: '分钟',
    secondText: '秒钟',
    millisecText: '毫秒',
    currentText: '现在',
    closeText: '完成',
    timeFormat: 'HH:mm:ss',
    ampm: false
  };

  $.timepicker.setDefaults($.timepicker.regional['zh-CN']);

  $('#post_start_at').datetimepicker({
    showSecond: true
  });
  $('#post_end_at').datetimepicker({
    showSecond: true
  });
</script>
