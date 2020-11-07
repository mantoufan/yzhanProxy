<?php
return array(
    'cache_dir' => dirname(__FILE__) . '/cache/',
    'cache_time' => 60 * 60 * 6, // 缓存时间，秒
    'task_num' => 3, // 最大任务数
    'available_static' => 0, // 静态资源加速
    'available_pic' => 0, // 图片加速
    'watermark_path' => '', // 水印路径
    'watermark_pos' => '', // 水印位置
    'file_rules' => array(// 文件替换引擎
        'application/index/view/*/public/header.html' => array(
            '{{$public_host}}static/common/lib/assets/css/amazeui.css' => 'https://cdn.jsdelivr.net/npm/amazeui@2.7.2/dist/css/amazeui.min.css', // 302跳转
            '{{$public_host}}static/common/lib/amazeui-switch/amazeui.switch.css' => 'https://cdn.jsdelivr.net/combine/npm/amazeui-switch@3.3.3/amazeui.switch.min.css,npm/amazeui-chosen@1.3.0/amazeui.chosen.min.css,npm/cropper@0.9.2/dist/cropper.min.css,npm/amazeui-tagsinput@0.5.2/amazeui.tagsinput.min.css',
            '{{$public_host}}static/common/lib/amazeui-chosen/amazeui.chosen.css?v={{:MyC(\'home_static_cache_version\')}}"' => '',
            '{{$public_host}}static/common/lib/cropper/cropper.min.css?v={{:MyC(\'home_static_cache_version\')}}' => '',
            '{{$public_host}}static/common/lib/amazeui-tagsinput/amazeui.tagsinput.css?v={{:MyC(\'home_static_cache_version\')}}' => '',
        ),
        'application/index/view/*/public/footer.html' => array(
            '{{$public_host}}static/common/lib/jquery/jquery-2.1.0.js' => 'https://cdn.jsdelivr.net/combine/npm/jquery@2.1.0,npm/amazeui@2.7.2/dist/js/amazeui.min.js,npm/echarts@4.1.0/dist/echarts.min.js,npm/echarts@4.1.0/theme/macarons.min.js,npm/amazeui-switch@3.3.3,npm/amazeui-chosen@1.3.0,npm/amazeui-dialog@0.0.2/dist/amazeui.dialog.min.js,npm/amazeui-tagsinput@0.5.2,npm/cropper@0.9.2,npm/clipboard@2.0.4',
            '{{$public_host}}static/common/lib/assets/js/amazeui.min.js?v={{:MyC(\'home_static_cache_version\')}}' => '', // 返回空
            '{{$public_host}}static/common/lib/echarts/echarts.min.js?v={{:MyC(\'home_static_cache_version\')}}' => '',
            '{{$public_host}}static/common/lib/echarts/macarons.js?v={{:MyC(\'home_static_cache_version\')}}'=> '',
            '{{$public_host}}static/common/lib/amazeui-switch/amazeui.switch.min.js?v={{:MyC(\'home_static_cache_version\')}}'=> '',
            '{{$public_host}}static/common/lib/amazeui-chosen/amazeui.chosen.min.js?v={{:MyC(\'home_static_cache_version\')}}'=> '',
            '{{$public_host}}static/common/lib/amazeui-dialog/amazeui.dialog.js?v={{:MyC(\'home_static_cache_version\')}}'=> '',
            '{{$public_host}}static/common/lib/amazeui-tagsinput/amazeui.tagsinput.min.js?v={{:MyC(\'home_static_cache_version\')}}'=> '',
            '{{$public_host}}static/common/lib/cropper/cropper.min.js?v={{:MyC(\'home_static_cache_version\')}}'=> '',
            '{{$public_host}}static/common/lib/clipboard/clipboard.min.js?v={{:MyC(\'home_static_cache_version\')}}'=> '',
            '{{$public_host}}static/common/lib/My97DatePicker/WdatePicker.js'=> 'https://cdn.jsdelivr.net/npm/my97datepicker@4.8.0/WdatePicker.min.js',
        )
    )
);
?>