# mtfBetter
PHP网站及应用性能优化类，优化任意网站、应用和小程序性能。
## 介绍
>
Heal the world, make it a better place.  

用`.htaccess`拦截静态文件（JS / CSS / 图像），通过：  
1. 合并请求
2. 压缩数据
3. 公共CDN  

三种方式，减少文件体积和请求数，利用缓存和边缘节点：
1. 加快网站速度
2. 减少流量消耗
3. 进而提升用户体验，提高搜索引擎评级

此外，本类还提供常用功能：  
1. 图片水印
2. 图片防盗链

## 协议
本类隶属于[mtfCOM](https://github.com/mantoufan/mtfCOM)通用组件库，与之采用相同协议开源

## 开箱即用
1. [下载](https://github.com/mantoufan/mtfBetter/releases/)
2. 解压到网站/应用根目录
3. 在`.htaccess`中添加规则
```
RewriteRule ^(.*).(js|css|png|jpg|jpeg)$ mtfBetter.php?{参数区}&path=$1.$2 [L]
```

## 配置
您可以在参数区，以`查询字符串`方式传入参数，实现个性化配置：  
- `path` - 静态文件相对于mtfBetter.php路径（开箱即用中，mtfBetter.php放到了根目录，默认就在根目录）  
- `watermark_path` - 水印图片相对于mtfBetter.php的路径
- `watermark_pos` - 水印图片的位置
- `anti_stealing_link` - 图片防盗链
- `cache_time` - 缓存时间
- `task_num` - 队列数