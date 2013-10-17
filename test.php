<?php

/*
 * 这里以我的个人博客icultivator.com为例进行测试
 */
function autoLoadClass($classname){
    $filename = dirname(__FILE__).DIRECTORY_SEPARATOR.$classname.'.class.php';
    include $filename;
}

spl_autoload_register('autoLoadClass');

$crawl = new Crawler();
$crawl->url = 'http://www.icultivator.com/program/wordpress';
//注意此处列表中不含链接标签a
$crawl->list_rules = 'div.caption h3';
$crawl->title_rule = 'div.page-header h1';
$crawl->content_rule = 'div.post_content';
//参数为1表示从列表批量采集，参数为2表示直接采集某一篇文章
$crawl->crawl_article(1);
?>
