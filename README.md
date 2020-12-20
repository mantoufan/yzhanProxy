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

## 协议
本类隶属于[mtfCOM](https://github.com/mantoufan/mtfCOM)通用组件库，与之采用相同协议开源

## 配置
您可以在参数区，以`查询字符串`方式传入参数，实现个性化配置：  
- `path` - 静态文件相对于mtfBetter.php路径（开箱即用中，mtfBetter.php放到了根目录，默认就在根目录）  
- `watermark_path` - 水印图片相对于mtfBetter.php的路径
- `watermark_pos` - 水印图片的位置
- `anti_stealing_link` - 图片防盗链
- `cache_time` - 缓存时间
- `task_num` - 队列数

## 第三方应用
- shopXO加速优化插件
### 介绍
![shopXO加速优化插件介绍](https://cdn.mantoufan.com/202011112129349627_c_w_600.png)
### 使用
1. 进入shopXO后台，应用管理，上传`加速优化`插件
2. 根据服务器运行环境，配置伪静态  
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