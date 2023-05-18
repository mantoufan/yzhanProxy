package netutil

import "testing"

func TestIsResolvedLocalIP(t *testing.T) {
	tests := []struct {
		domain string
		want   bool
	}{
		{"baidu.com", false},
		{"domain-not-exists.com", false},
		{"localhost", true},
		{"127.0.0.1", true},
		{"0.0.0.0", true},
		{"10.0.0.0", true},
		{"::1", true},
		{"fe80::1", true},
		{"ff02::1", true},
		{"2001:db8::1", true},
	}

	for _, tt := range tests {
		got := IsResolvedLocalIP(tt.domain)
		if got != tt.want {
			t.Errorf("isResolvedLocalIP(%q) = %v, want %v", tt.domain, got, tt.want)
		}
	}
}

func TestGetPortByScheme(t *testing.T) {
	result := GetPortByScheme("", "")
	if result != "80" {
			t.Errorf("GetPortByScheme(\"\") = %v; want 80", result)
	}

	result = GetPortByScheme("", "https")
	if result != "443" {
			t.Errorf("GetPortByScheme(\"\", \"https\") = %v; want 443", result)
	}

	result = GetPortByScheme("8080", "http")
	if result != "8080" {
			t.Errorf("GetPortByScheme(\"8080\", \"http\") = %v; want 8080", result)
	}
}