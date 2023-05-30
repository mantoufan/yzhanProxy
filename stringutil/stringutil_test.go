package stringutil
import (
	"testing"
	"strings"
	"net/url"
)

func TestSingleJoiningSlash(t *testing.T) {
	testCases := []struct {
		name     string
		a        string
		b        string
		expected string
	}{
		{"empty strings", "", "", "/"},
		{"first empty string", "", "foo", "/foo"},
		{"second empty string", "foo", "", "foo/"},
		{"both strings without slashes", "foo", "bar", "foo/bar"},
		{"both strings with slashes", "foo/", "/bar", "foo/bar"},
		{"first string with slash", "foo/", "bar", "foo/bar"},
		{"second string with slash", "foo", "/bar", "foo/bar"},
	}

	for _, tc := range testCases {
		t.Run(tc.name, func(t *testing.T) {
			got := singleJoiningSlash(tc.a, tc.b)
			if got != tc.expected {
				t.Errorf("singleJoiningSlash(%q, %q) = %q; want %q", tc.a, tc.b, got, tc.expected)
			}
		})
	}
}

func TestJoinURLPath(t *testing.T) {
	testCases := []struct {
		name     string
		a        string
		b        string
		expected string
	}{
		{
			name:     "Both paths without slashes",
			a:        "https://example.com/path1",
			b:        "path2",
			expected: "https://example.com/path1/path2",
		},
		{
			name:     "First path with trailing slash",
			a:        "https://example.com/path1/",
			b:        "path2",
			expected: "https://example.com/path1/path2",
		},
		{
			name:     "Second path with leading slash",
			a:        "https://example.com/path1",
			b:        "/path2",
			expected: "https://example.com/path1/path2",
		},
		{
			name:     "Both paths with slashes",
			a:        "https://example.com/path1/",
			b:        "/path2",
			expected: "https://example.com/path1/path2",
		},
	}

	for _, tc := range testCases {
		t.Run(tc.name, func(t *testing.T) {
			a, _ := url.Parse(tc.a)
			b, _ := url.Parse(tc.b)

			result, _ := JoinURLPath(a, b)
			fullResult := strings.TrimSuffix(a.String(), a.Path) + result

			if fullResult != tc.expected {
				t.Errorf("Expected '%s', but got '%s'", tc.expected, fullResult)
			}
		})
	}
}

func TestLogOption(t *testing.T) {
	result := LogOption("")
	if result != "" {
			t.Errorf("LogOption(\"\") = %v; want \"\"", result)
	}

	result = LogOption("debug")
	if result != "opt:debug" {
			t.Errorf("LogOption(\"debug\") = %v; want \"opt:debug\"", result)
	}
}

func TestParseGlobal(t *testing.T) {
	testCases := []struct {
		name       string
		globalStr  string
		expected   GlobalType
	}{
		{
			name:       "Default values",
			globalStr:  "",
			expected:   GlobalType{CertDir: "./cert", CacheDir: "./cache", CacheMaxSize: 100 * 1024 * 1024},
		},
		{
			name:       "Custom values",
			globalStr:  "cert_dir=/custom/cert&cache_dir=/custom/cache&cache_max_size=200",
			expected:   GlobalType{CertDir: "/custom/cert", CacheDir: "/custom/cache", CacheMaxSize: 200 * 1024 * 1024},
		},
	}

	for _, tc := range testCases {
		t.Run(tc.name, func(t *testing.T) {
			result := ParseGlobal(tc.globalStr)
			if result != tc.expected {
				t.Errorf("Expected %v, got %v", tc.expected, result)
			}
		})
	}
}