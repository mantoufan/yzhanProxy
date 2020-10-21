<?php
return array(
    'js' => array(
        'amazeui.css' => 'https://cdn.jsdelivr.net/npm/amazeui@2.7.2/dist/css/amazeui.min.css', // 302跳转
        'jquery-2.1.0.js' => 'https://cdn.jsdelivr.net/combine/npm/jquery@2.1.0,npm/amazeui@2.7.2/dist/js/amazeui.min.js,npm/echarts@4.1.0/dist/echarts.min.js,npm/clipboard@2.0.4',
        'amazeui.min.js' => '', // 返回空
        'echarts.min.js' => '',
        'clipboard.min.js' => ''
    ),
    'arv' => array( // 可以通过传参，动态改变的参数
        'path' => '', // 文件路径
        'cache_dir' => dirname(__FILE__) . '/cache/',
        'cache_time' => 60 * 60 * 6, // 缓存时间，秒
        'task_num' => 8, // 最大任务数
        'watermark_path' => '', // 水印路径
        'watermark_pos' => '', // 水印位置
        'anti_stealing_link_pic' => false, // 防盗链图片
    )
);
?>