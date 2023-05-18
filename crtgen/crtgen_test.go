package crtgen
import (
	"io/ioutil"
	"os"
	"testing"
)

func TestGen(t *testing.T) {
	crtPath := "test.crt"
	keyPath := "test.key"

	// Remove the test files if they exist
	os.Remove(crtPath)
	os.Remove(keyPath)

	// Generate the certificate and key
	Gen(crtPath, keyPath)

	// Read the generated certificate and key from files
	crtData, err := ioutil.ReadFile(crtPath)
	if err != nil {
		t.Fatalf("Failed to read certificate file: %v", err)
	}

	keyData, err := ioutil.ReadFile(keyPath)
	if err != nil {
		t.Fatalf("Failed to read key file: %v", err)
	}

	// Check that the certificate and key data are not empty
	if len(crtData) == 0 {
		t.Error("Certificate data is empty")
	}

	if len(keyData) == 0 {
		t.Error("Key data is empty")
	}

	// Remove the test files
	os.Remove(crtPath)
	os.Remove(keyPath)
}