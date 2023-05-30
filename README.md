# yzhanProxy
Web reverse proxy with automatic SSL, LFU caching, command-line configuration  
支持自动配置 SSL 证书、LFU 缓存、用命令行配置的 Web 反向代理服务器  
## Usage
> Options:  
  -s, --src=    Source URL List Required  
  -d, --dst=    Destination URL List Required  
  -o, --opt=    Option List: redirect=<30x>&cache_ext=<jpg|png|...>&cache_max_age=<1|2|...>  
  -g, --global= Global Config: cert_dir=<./cert>&cache_dir=<./cache>&cache_max_size=<100>  
Help Options:  
  -h, --help  Show this help message
## Example  
```shell
# sudo yzhanproxy-{platform}-amd64 \
sudo go run main.go \
-s http://localhost,https://localhost,https://x.com:81 \
-d https://localhost,https://www.google.com,https://m.baidu.com \
-o "redirect=302,cache_ext=ico|jpg|png|webp&cache_max_age=60|3600" \
-g "cert_dir=./cert&cache_dir=./cache&cache_max_size=100"
```
### Meaning of Parameters
Redirect:  
http://localhost — **Redirect**: 302 —> https://localhost  
Reverse Proxy:  
https://localhost — **Localhost**: Self Signed Cert. **Cache**: ico - 60s, jpg|png|webp - 3600s —> https://www.google.com   
https://x.com:81  — **Domain**: Auto Get/Renew Cert. **No Cache** —> https://m.baidu.com  
Global Config with default value:  
cert_dir: `./cert`  
cache_dir: `./cache`  
cache_max_size: `100 MB`, **LFU** used to clean up least used files when exceeding max size

### Screenshot
#### Reverse proxy Google using your own domain
![reverseproxy.png](https://s2.loli.net/2023/05/31/qQfLncrslBIPz6D.png)
#### Console output
![console.png](https://s2.loli.net/2023/05/31/kI9pODTBsPMucFv.png)
## Development
### Build
```shell
# Linux
./scripts/build.sh [name | default: yzhanproxy]
# Windows
./scripts/build.bat [name | default: yzhanproxy]
```
### Unit Test
```shell
go test ./...
```
  
## Thanks
- ChatGPT: Assisting in coding and unit testing
- [go-flags](https://github.com/jessevdk/go-flags)

## Migration Notice
This repo used to be [mtfBetter](https://github.com/mantoufan/mtfCOM/tree/master/php/mtfBetter) (moved to [mtfCOM](https://github.com/mantoufan/mtfCOM)), now it has be refactored using *golang*.