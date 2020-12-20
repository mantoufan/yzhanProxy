# mtfBetter
PHP网站及应用性能优化类，优化任意网站、应用和小程序性能。
## 介绍
>
Heal the world, make it a better place.  

静态文件（JS / CSS / 图像），通过：  
1. 合并请求
2. 压缩数据
3. 公共CDN  

三种方式，减少文件体积和请求数，利用缓存和边缘节点：
1. 加快网站速度
2. 减少流量消耗
3. 进而提升用户体验，提高搜索引擎评级

此外，本类还提供常用功能：  
1. 图片水印
2. 批量文件内容替换

## 协议
本类隶属于[mtfCOM](https://github.com/mantoufan/mtfCOM)通用组件库，与之采用相同协议开源

## 开箱即用
```php
include('mtfBetter.class.php');
$mtfBetter = new mtfBetter($conf); // 配置
$mtfBetter->handler($paths); // 要处理的文件路径列表
```
### 配置 · conf
```php
$conf = array(
    'cache_dir' => 'cache', // 缓存文件夹路径 
    'cache_time' => 3600, // 缓存时间 秒
    'task_num' => 3, // 最大并发数，超过不处理
    'available_pic' => true, // 开启图片转webp
    'watermark_path' => 'water.png', // 图片水印路径
    'watermark_pos' => '', // 图片水印位置，可选left-top / left-bottom / right-top / right-bottom
    'rules'=> array(// 批量文件内容替换，支持 * 通配符
        '/view/*/h.html' => array(
            '1' => '2',// 将view/a/h.html,view/b/h.html...中的 1 换成 2
        )
    )
);
```
### 文件列表 · paths
除了`rules`声明要替换内容的文件外，您可以在`$paths`中传入文件列表  
通常是 图片（jpg/jpeg/png）文件路径列表  
类就会按照配置`conf`中的规则，批量压缩，转格式和加水印  
处理后的文件会放到`cache_dir`中，缓存时间内，类将直接返回缓存  
```php
$path = array(
    'static/pic/202011112129349625.png',// 图片将被压缩 或 webp 同时加水印
    'static/pic/202011112129349626.png',// 对应{cache_dir}/static/pic/202011112129349626.png
    'static/pic/202011112129349627.png', 
)
```
**注意**类根据图片路径，在`cache_dir`中保留目录结构  
便于编写`伪静态规则`，先判断`cache_dir`相应文件是否存在  
在`cache_dir`不存在相应文件时，即插件尚未处理完成
时，加载原文件  
不同运行环境的伪静态规则的编写，您可以参考下方示例

## 第三方应用
- shopXO加速优化插件
### 介绍
![shopXO加速优化插件介绍](https://cdn.mantoufan.com/202011112129349627_c_w_600.png)
### 使用
1. 进入shopXO后台，应用管理，上传`加速优化`插件
2. 根据服务器运行环境，配置伪静态规则 
- Apache · Kangle
```
<IfModule mod_rewrite.c>
  RewriteEngine on
  RewriteBase /
  # ShopXO及ThinkPHP伪静态规则
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php?s=/$1 [QSA,PT,L]
  # 加速优化插件伪静态规则
  RewriteCond %{DOCUMENT_ROOT}/cache/optimizer/%{REQUEST_URI} -f
  RewriteRule ^(.*.(jpg|jpeg|png))$ cache/optimizer/$1 [L,NC]
</IfModule>
```
- Nginx
```
if (!-e $request_filename){
    rewrite  ^(.*)$  /index.php?s=$1  last;   break; # ShopXO及ThinkPHP伪静态规则
}
if (-f $document_root/cache/optimizer/$uri){
    set $rule_0 1$rule_0; # 加速优化插件伪静态规则
}
if (-f $document_root/cache/optimizer/$uri){
    rewrite ^/(.*.(jpg|jpeg|png))$ /cache/optimizer/$1 last; # 加速优化插件伪静态规则
}
```
- IIS
```
<?xml version="1.0" ?>
<rules>
<!-- ShopXO及ThinkPHP伪静态规则 -->
    <rule name="OrgPage_rewrite" stopProcessing="true">
       <match url="^(.*)$"/>
       <conditions logicalGrouping="MatchAll">
		<add input="{HTTP_HOST}" pattern="^(.*)$"/>
		<add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true"/>
		<add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true"/>
        </conditions>
        <action type="Rewrite" url="index.php/{R:1}"/>
    </rule>
<!-- 加速优化插件伪静态规则 -->
    <rule name="Optimizer_rewrite" stopProcessing="true">
       <match ignoreCase="true" url="^(.*.(jpg|jpeg|png))$"/>
       <conditions>
		<add input="{DOCUMENT_ROOT}/cache/optimizer/{REQUEST_URI}" matchType="IsFile"/>
       </conditions>
       <action type="Rewrite" url="cache/optimizer/{R:1}"/>
    </rule>
</rules>
```
3. 进入插件设置，开启静态文件、图片加速，保存即可生效