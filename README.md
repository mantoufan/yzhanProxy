# yzhanProxy
Proxy cache and auto renew SSL certificate using a yaml  
支持自动获取 SSL 证书、可配置缓存和响应头的 Web 反向代理服务器
## Usage
> Application Options:  
  -s, --src=  Source URL List Required  
  -d, --dst=  Destination URL List Required  
  -o, --opt=  Option List: redirect=<30x> cache_ext=<jpg|png|...> cache_max_age=<1|2|...>
Help Options:  
  -h, --help  Show this help message
## Example  
```shell
yzhanproxy
-s http://localhost;https://localhost;https://xxx.com:81
-d https://localhost;https://www.baidu.com;https://m.baidu.com
-o redirect=302;cache_ext=jpg,png,webp&cache_max_age=60/3600
```
### Results
Redirect:  
http://localhost — **Redirect**: 302 —> https://localhost  
Reverse Proxy:  
https://localhost — **Localhost**: Self Signed Cert. **Cache**: jpg - 60s, png|webp - 3600s —> https://www.baidu.com   
https://x.com:81  — **Domain**: Auto Get/Renew Cert. **No Cache** —> https://m.baidu.com
## Development
### Build
```shell
go build
```
### Unit Test
```shell
go test ./...
```
## Migration Notice
This repo used to be [mtfBetter](https://github.com/mantoufan/mtfCOM/tree/master/php/mtfBetter) (moved to [mtfCOM](https://github.com/mantoufan/mtfCOM)), now it will be refactored using *golang*.  
## Todo
1. Cache static files in proxy nodes.  
2. Apply and auto renew SSL certificate.  
3. Could be configured using a yaml.  