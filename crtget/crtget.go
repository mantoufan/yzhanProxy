package crtget

import "golang.org/x/crypto/acme/autocert"

func GetManager(domains string, certDir string) autocert.Manager {
	return autocert.Manager{
		Cache:      autocert.DirCache(certDir),
		Prompt:     autocert.AcceptTOS,
		HostPolicy: autocert.HostWhitelist(domains),
	}
}