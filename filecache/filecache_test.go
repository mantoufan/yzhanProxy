package filecache

import (
	"os"
	"testing"
	"time"
)

func TestFileCache(t *testing.T) {
	cacheDir := "./testcache"
	maxSize := int64(100)

	// Create a new cache
	cache := NewFileCache(cacheDir, maxSize)

	// Add data to the cache
	key := "testkey"
	value := []byte("testvalue")
	ttl := time.Second * 1
	cache.Set(key, value, ttl)

	// Read data from the cache
	result, ok := cache.Get(key)
	if !ok {
		t.Errorf("failed to get value from cache")
	}

	// Check if the read data is correct
	if string(result) != string(value) {
		t.Errorf("got %s, expected %s", string(result), string(value))
	}

	// Check if the key in the cache is correct
	keys := cache.Keys()
	if len(keys) != 1 || keys[0] != key {
		t.Errorf("got %v, expected %v", keys, []string{key})
	}

	// Check if the value in the cache is correct
	values := cache.Values()
	if len(values) != 1 || string(values[0]) != string(value) {
		t.Errorf("got%d, expected %s", len(values), string(value))
	}

	// Wait for the cache data to expire
	time.Sleep(time.Second * 2)

	// Check if expired data has been cleared
	_, ok = cache.Get(key)
	if ok {
		t.Errorf("failed to clear expired cache data")
	}

	// Clear the cache
	cache.Clear()

	// Check if the cache is empty
	if cache.Len() != 0 {
		t.Errorf("failed to clear cache")
	}

	// Remove the test cache directory
	os.RemoveAll(cacheDir)
}