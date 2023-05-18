package netutil

import "net"

func IsResolvedLocalIP(domain string) bool {
	addrs, err := net.LookupIP(domain)
	if err != nil {
		return false
	}
	for _, addr := range addrs {
		ip4 := addr.To4()
		if ip4 != nil && ip4.IsPrivate() {
			return true
		}
		if !ip4.Equal(addr) && addr.IsGlobalUnicast() {
			return true
		}
		if ip6 := addr.To16(); ip6 != nil {
			if ip6.IsLinkLocalUnicast() || ip6.IsLoopback() || ip6.IsMulticast() {
				return true
			}
			if !ip6.IsGlobalUnicast() {
				return true
			}
		}
	}
	return false
}

func GetPortByScheme(port, scheme string) string {
	if port == "" {
		if scheme == "https" {
			return "443"
		} else {
			return "80"
		}
  }
	return port
}