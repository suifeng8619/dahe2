<?php

if (!$zbp->CheckRights('root')) {
    $zbp->ShowError(6);
    die();
}

?>
<style>
  div.fire-guide p {
    padding: 7px;
    font-size: 15px;
    color: #3e3c3c;
  }

  div.fire-guide {
    border: 1px solid #d4cdcd;
    padding: 10px;
    width: 1100px;
  }
</style>
<div class="fire-guide">
  <h2 style="padding: 5px">插件必要配置</h2>
  <p>
    <strong>
      *** 由于发布文章相对耗时，请务必参考此教程配置好 PHP 参数：
      <a href="https://www.iddahe.com/jishu/17.html" target="_blank">
        https://www.iddahe.com/jishu/17.html
      </a>
    </strong>
  </p>
  <p>
    防止 PHP 内存占有过多，从而影响网站速度，建议每次录入 5000 个关键词左右，处理完之后再继续录入
  </p>
  <h2 style="padding: 5px">插件操作方式</h2>
  <p>三个简单步骤即可开始自动发布文章：1、录入关键词 &nbsp&nbsp 2、访问基础设置里面的【 手动发布 】进行测试&nbsp&nbsp3、服务器定时执行发布文章脚本</p>
  <p>
    每次执行【发布文章脚本】随机抽取一个分类发布一篇文章，自动发布：将【发布文章脚本】定时到服务器即可。
  </p>
  <h2 style="padding: 5px">文字水印图片</h2>
  <p>
    发布文章时，插件会自动根据关键词【联想图片】并插入到文章中，因为图片来自网络匹配，有一定的失败概率，此为不可控因素
  </p>
  <p>所以为保证文章一定有图，我做了一个【文字水印图片】功能，细节如下：</p>
  <p>当【联想图片】失败时，程序将自动把关键词作为【水印标记】添加到事先准备好的背景图片里面，生成一张原创图片，并插入到文章中</p>
  <p>【默认的背景图片】存放目录：zb_users/plugin/iddahe_com_editor/data/pic_default</p>
  <p>【自定义背景图片】存放目录：zb_users/plugin/iddahe_com_editor/data/pic_diy</p>
  <p>若你要自定义水印背景图片，还请将自定义图片上传到上述的【自定义背景图片存放目录】程序会优先使用自定义背景图</p>
  <p>注意，背景图片【必须是】 jpeg 或者 png 格式（宽600 * 高400），否则无效！！！</p>
  <p>（完）</p>
</div>
