package stringutil
import (
	"strings"
	"net/url"
	"time"
	"strconv"
)

func singleJoiningSlash(a, b string) string {
	aslash := strings.HasSuffix(a, "/")
	bslash := strings.HasPrefix(b, "/")
	switch {
	case aslash && bslash:
		return a + b[1:]
	case !aslash && !bslash:
		return a + "/" + b
	}
	return a + b
}

func JoinURLPath(a, b *url.URL) (path, rawpath string) {
	if a.RawPath == "" && b.RawPath == "" {
		return singleJoiningSlash(a.Path, b.Path), ""
	}
	apath := a.EscapedPath()
	bpath := b.EscapedPath()

	aslash := strings.HasSuffix(apath, "/")
	bslash := strings.HasPrefix(bpath, "/")

	switch {
	case aslash && bslash:
		return a.Path + b.Path[1:], apath + bpath[1:]
	case !aslash && !bslash:
		return a.Path + "/" + b.Path, apath + "/" + bpath
	}
	return a.Path + b.Path, apath + bpath
}

func LogOption(option string) string {
  if option != "" {
    return "opt:" + option
  }
  return ""
}

type OptionType struct {
	Redirect int
	CacheTime map[string]time.Duration
}

func ParseOption(option string) OptionType {
	Redirect, CacheTime := 0, map[string]time.Duration{}
	optMap, err := url.ParseQuery(option)
	if err != nil {
		panic(err)
	}
	redirectStr := optMap.Get("redirect")
	if redirectStr != "" {
		Redirect, err = strconv.Atoi(redirectStr)
		if err != nil {
			panic(err)
		}
	}
	if cacheExtStr := optMap.Get("cache_ext"); cacheExtStr != "" {
		if cacheMaxAgeStr := optMap.Get("cache_max_age"); cacheMaxAgeStr != "" {
			cacheExt, cacheMaxAge := strings.Split(cacheExtStr, "|"), strings.Split(cacheMaxAgeStr, "|")
			cacheExtLen, cacheMaxAgeLen := len(cacheExt), len(cacheMaxAge)
			if cacheMaxAgeLen < cacheExtLen {
				for i := cacheMaxAgeLen - 1; i < cacheExtLen; i++ {
					cacheMaxAge = append(cacheMaxAge, cacheMaxAge[cacheMaxAgeLen - 1])
				}
			}
			
			for i, ext := range cacheExt {
				cacheMaxAgeInt, err := strconv.Atoi(cacheMaxAge[i])
				if err != nil {
					panic(err)
				}
				CacheTime[ext] = time.Duration(cacheMaxAgeInt) * time.Second
			}
		}
	}
	return OptionType{Redirect, CacheTime}
}


type GlobalType struct {
	CertDir string
	CacheDir string
	CacheMaxSize int64
}

func ParseGlobal(globalStr string) GlobalType {
	globalMap, err := url.ParseQuery(globalStr)
	if err != nil {
		panic(err)
	}
	certDir, cacheDir, cacheMaxSizeStr := globalMap.Get("cert_dir"), globalMap.Get("cache_dir"), globalMap.Get("cache_max_size")
	if certDir == "" {
		certDir = "./cert"
	}
	if cacheDir == "" {
		cacheDir = "./cache"
	}
	var cacheMaxSize int64 = 100
	if cacheMaxSizeStr != "" {
		cacheMaxSize, err = strconv.ParseInt(cacheMaxSizeStr, 10, 64)
		if err != nil {
			panic(err)
		}
	} 
	cacheMaxSize *= 1024 * 1024
	return GlobalType{certDir, cacheDir, cacheMaxSize}
}