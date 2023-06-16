<?php

if (!$zbp->CheckRights('root')) {
    $zbp->ShowError(6);
    die();
}

$category = new Category();
$category->LoadInfoByID(GetVars('cid'));

if ('rand' == GetVars('cid')) {
    $categoryName = '随机分类';
} elseif ('未命名' == $category->Name) {
    $zbp->SetHint('bad', "分类 ID【" . GetVars('cid') . "】不存在");
    Redirect("{$zbp->host}zb_users/plugin/iddahe_com_editor/main.php");
} else {
    $categoryName = $category->Name;
}

if (GetVars('categoryId')) {
    if (function_exists('CheckIsRefererValid')) {
        CheckIsRefererValid();// 检查 csrfToken
    }
    $filename = iddahe_com_editor_build_file(GetVars('categoryId'));
    file_put_contents($filename, GetVars('keywords'));

    $zbp->SetHint('good', "分类【{$categoryName}】设置关键词成功");
    Redirect("{$zbp->host}zb_users/plugin/iddahe_com_editor/main.php?action=input");
}
?>

<form method="post">
    <?php
    if (function_exists('CheckIsRefererValid')) {
        echo '<input type="hidden" name="csrfToken" value="' . $zbp->GetCSRFToken() . '">';
    }
    ?>
  <input type="hidden" name="categoryId" value="<?php echo GetVars('cid'); ?>">
  <table style="width: 800px">
    <tr style="text-align: center;font-weight: bold">
      <td>++ 在下面填入关键词一行一个，当前分类【 <?php echo $categoryName; ?> 】 ++</td>
    </tr>
    <tr>
      <td>
          <?php $filename = iddahe_com_editor_build_file(GetVars('cid')); ?>
        <textarea
          style="width: 100%;padding: 5px"
          name="keywords"
          cols="30"
          rows="15"><?php echo is_file($filename) ? file_get_contents($filename) : ''; ?></textarea>
      </td>
    </tr>
    <tr style="text-align: center;">
      <td>
        <input type="submit" value="保存设置" style="width: 200px">
      </td>
    </tr>
  </table>
</form>
