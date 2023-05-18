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
	redirect int
	cacheTime map[string]time.Duration
}

func ParseOption(option string) OptionType {
	redirect, cacheTime := 0, map[string]time.Duration{}
	optMap, err := url.ParseQuery(option)
	if err != nil {
		panic(err)
	}
	redirectStr := optMap.Get("redirect")
	if redirectStr != "" {
		redirect, err = strconv.Atoi(redirectStr)
		if err != nil {
			panic(err)
		}
	}
	if cacheExtStr := optMap.Get("cache_ext"); cacheExtStr != "" {
		if cacheMaxAgeStr := optMap.Get("cache_max_age"); cacheMaxAgeStr != "" {
			cacheExt, cacheMaxAge := strings.Split(cacheExtStr, ","), strings.Split(cacheMaxAgeStr, ",")
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
				cacheTime[ext] = time.Duration(cacheMaxAgeInt) * time.Second
			}
		}
	}
	return OptionType{redirect, cacheTime}
}