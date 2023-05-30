package main
import (
  "github.com/jessevdk/go-flags"
  "github.com/mantoufan/yzhanproxy/reverseproxy"
)

type Option struct {
  Sources string `short:"s" long:"src" description:"Source URL List" required:"true"`
  Destinations string `short:"d" long:"dst" description:"Destination URL List" required:"true"`
  Options string `short:"o" long:"opt" description:"Option List"`
  Global string `short:"g" long:"global" description:"Global Config"`
}

func main() {
  var opt Option
  flags.Parse(&opt)
  reverseproxy.Listen(opt.Sources, opt.Destinations, opt.Options, opt.Global)
}
