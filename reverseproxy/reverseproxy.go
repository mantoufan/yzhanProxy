package reverseproxy
import (
  "log"
  "strings"
  "crypto/tls"
  "net/url"
  "net/http"
  "net/http/httputil"
  "github.com/mantoufan/yzhanproxy/stringutil"
  "github.com/mantoufan/yzhanproxy/crtgen"
  "github.com/mantoufan/yzhanproxy/crtget"
  "github.com/mantoufan/yzhanproxy/netutil"
)

func NewProxy(destination string) (*httputil.ReverseProxy, error) {
  destinationParse, err := url.Parse(destination)
  if err != nil {
    return nil, err
  }
  proxy := httputil.NewSingleHostReverseProxy(destinationParse)
  proxy.Director = func(request *http.Request) {
    destinationQuery := destinationParse.RawQuery
    request.URL.Scheme = destinationParse.Scheme
    request.URL.Host = destinationParse.Host
    request.Host = destinationParse.Host
    request.URL.Path, request.URL.RawPath = stringutil.JoinURLPath(destinationParse, request.URL)

    if destinationQuery == "" || request.URL.RawQuery == "" {
      request.URL.RawQuery = destinationQuery + request.URL.RawQuery
    } else {
      request.URL.RawQuery = destinationQuery + "&" + request.URL.RawQuery
    }
    if _, ok := request.Header["User-Agent"]; ok == false {
      request.Header.Set("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36")
    }
    log.Println("ReverseProxy:", request.URL)
  }
  return proxy, nil
}

func ProxyRequestHandler(proxy *httputil.ReverseProxy) func(http.ResponseWriter, *http.Request) {
  return func(w http.ResponseWriter, r *http.Request) {
    proxy.ServeHTTP(w, r)
  }
}

func RedirectRequestHandler(url string, statusCode int) func(http.ResponseWriter, *http.Request) {
  return func(w http.ResponseWriter, r *http.Request) {
	  http.Redirect(w, r, url, statusCode)
    log.Println("Redirect:", url)
  }
}

type optionsType struct {
  destination string
  option stringutil.OptionType
}

func Listen(sourceStr string, destinationStr string, optionStr string) {
  sources, destinations, options := strings.Split(sourceStr, ";"), strings.Split(destinationStr, ";"), strings.Split(optionStr, ";")
  sourcesLen, destinationsLen, optionsLen := len(sources), len(destinations), len(options)
  if sourcesLen != destinationsLen {
    log.Fatal("The total number of source addresses and destination addresses is not equal.")
  }
  if optionsLen < sourcesLen {
    for i := optionsLen - 1; i < sourcesLen; i++ {
      options = append(options, "")
    }
  }
  optionsMap := make(map[string]optionsType)
  muxMap := make(map[string]*http.ServeMux)
	for i, source := range sources {
		go func(source string, destination string, option string) {
      sourceParse, err := url.Parse(source)
      if err != nil {
        panic(err)
      }
      scheme, host, port, domain := sourceParse.Scheme, sourceParse.Host, sourceParse.Port(), sourceParse.Hostname()
      port = netutil.GetPortByScheme(port, scheme) 
      log.Println("Listening on:", port, "src:", source, "dst:", destination, stringutil.LogOption(option))
      optionsMap[scheme + host] = optionsType{destination, stringutil.ParseOption(option)}
      mux, muxExist := muxMap[port]
      if muxExist == true {
        return
      }
      mux = http.NewServeMux()
      muxMap[port] = mux
      mux.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        scheme := "http"
        if r.TLS != nil {
          scheme = "https"
        }
        host := r.Host
        options, optionsExist := optionsMap[scheme + host]
        if optionsExist == false {
          return
        }
        _, option := options.destination, options.option
        log.Println(option)
        // redirect, cacheTime := option.redirect, option.cacheTime
        // if redirect >= 300 && redirect < 400 {
        //   http.Redirect(w, r, destination, redirect)
        //   return
        // }
        // proxy, err := NewProxy(destination)
        // if err != nil {
        //   panic(err)
        // }
        // proxy.ServeHTTP(w, r)
      })
      if scheme == "http" {
			  log.Fatal(http.ListenAndServe(":" + port, mux))
      } else if scheme == "https" {
        if netutil.IsResolvedLocalIP(domain) {
          crtPath, keyPath := "./certs/self-signed.crt", "./certs/self-signed.key"
          crtgen.Gen(crtPath, keyPath)
          log.Fatal(http.ListenAndServeTLS(":" + port, crtPath, keyPath, mux))
        } else {
          manager := crtget.GetManager(domain, "./certs")
          sever := &http.Server{
            Addr: ":" + port,
            TLSConfig: &tls.Config{
              GetCertificate: manager.GetCertificate,
              NextProtos: []string{"acme-tls/1", "http/2.0", "http/1.1"},
            },
            Handler: mux,
          }
          log.Fatal(sever.ListenAndServeTLS("", ""))
        }
      } else {
        log.Fatal("Unsupported Scheme")
      }
		}(source, destinations[i], options[i])
	}
	select {}
}
