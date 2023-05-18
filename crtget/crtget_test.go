package crtget

import (
	"testing"
	"golang.org/x/crypto/acme/autocert"
)

func TestGetManager(t *testing.T) {
	domains, certDir := "example.com", "./certs"
	m := GetManager(domains, certDir)

	// check if the cache directory is correct
	if m.Cache != autocert.DirCache(certDir) {
		t.Errorf("GetManager(%s) returned incorrect cache directory %s", domains, m.Cache)
	}
}