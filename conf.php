<?php
return array(
    'static' => array(//shopXO 静态文件重写规则
        'amazeui.css' => 'https://cdn.jsdelivr.net/npm/amazeui@2.7.2/dist/css/amazeui.min.css', // 302跳转
        'amazeui.switch.css' => 'https://cdn.jsdelivr.net/combine/npm/amazeui-switch@3.3.3/amazeui.switch.min.css,npm/amazeui-chosen@1.3.0/amazeui.chosen.min.css,npm/cropper@0.9.2/dist/cropper.min.css,npm/amazeui-tagsinput@0.5.2/amazeui.tagsinput.min.css',
        'amazeui.chosen.css' => '',
        'cropper.min.css' => '',
        'amazeui.tagsinput.css' => '',
        'jquery-2.1.0.js' => 'https://cdn.jsdelivr.net/combine/npm/jquery@2.1.0,npm/amazeui@2.7.2/dist/js/amazeui.min.js,npm/echarts@4.1.0/dist/echarts.min.js,npm/echarts@4.1.0/theme/macarons.min.js,npm/amazeui-switch@3.3.3,npm/amazeui-chosen@1.3.0,npm/amazeui-dialog@0.0.2/dist/amazeui.dialog.min.js,npm/amazeui-tagsinput@0.5.2,npm/cropper@0.9.2,npm/clipboard@2.0.4,npm/my-ueditor@0.1.2/ueditor.all.min.js,npm/my97datepicker@4.8.0',
        'amazeui.min.js' => '', // 返回空
        'echarts.min.js' => '',
        'macarons.js'=> '',
        'amazeui.switch.min.js',
        'amazeui.chosen.min.js',
        'amazeui.dialog.js',
        'amazeui.tagsinput.min.js',
        'cropper.min.js',
        'clipboard.min.js',
        'ueditor.all.js',
        'WdatePicker.js'
    ),
    'arv' => array( // 可以通过传参，动态改变的参数
        'path' => '', // 文件路径
        'cache_dir' => dirname(__FILE__) . '/cache/',
        'cache_time' => 60 * 60 * 6, // 缓存时间，秒
        'task_num' => 3, // 最大任务数
        'available_static' => 0, // 静态资源加速
        'available_pic' => 0, // 图片加速
        'watermark_path' => '', // 水印路径
        'watermark_pos' => '', // 水印位置
        'anti_stealing_link_pic' => 0, // 防盗链图片
    )
);
?>