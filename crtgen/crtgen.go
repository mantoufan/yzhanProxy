package crtgen

import (
	"crypto/rand"
	"crypto/rsa"
	"crypto/x509"
	"crypto/x509/pkix"
	"encoding/pem"
	"math/big"
	"net"
	"time"
	"os"
)

func Gen(crtPath string, keyPath string) {
	// Generate a new private key
	privateKey, err := rsa.GenerateKey(rand.Reader, 2048)
	if err != nil {
			panic(err)
	}

	// Create a self-signed certificate
	template := x509.Certificate{
			SerialNumber: big.NewInt(1653),
			Subject: pkix.Name{
					Organization: []string{"My Company"},
			},
			NotBefore: time.Now(),
			NotAfter:  time.Now().AddDate(1, 0, 0), // valid for 1 year
			KeyUsage:  x509.KeyUsageKeyEncipherment | x509.KeyUsageDigitalSignature,
			ExtKeyUsage: []x509.ExtKeyUsage{
					x509.ExtKeyUsageServerAuth,
					x509.ExtKeyUsageClientAuth,
			},
			IPAddresses: []net.IP{net.ParseIP("127.0.0.1")},
	}

	derBytes, err := x509.CreateCertificate(rand.Reader, &template, &template, &privateKey.PublicKey, privateKey)
	if err != nil {
			panic(err)
	}

	certOut := pem.EncodeToMemory(&pem.Block{
			Type:  "CERTIFICATE",
			Bytes: derBytes,
	})

	keyOut := pem.EncodeToMemory(&pem.Block{
			Type:  "RSA PRIVATE KEY",
			Bytes: x509.MarshalPKCS1PrivateKey(privateKey),
	})

	// Write the certificate and private key to files
	err = WriteFile(crtPath, certOut)
	if err != nil {
			panic(err)
	}

	err = WriteFile(keyPath, keyOut)
	if err != nil {
			panic(err)
	}
	return 
}

func WriteFile(filename string, data []byte) error {
	f, err := os.Create(filename)
	if err != nil {
			return err
	}
	defer f.Close()

	_, err = f.Write(data)
	if err != nil {
			return err
	}

	return nil
}