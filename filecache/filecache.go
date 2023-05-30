package filecache

import (
	"container/heap"
	"io/ioutil"
	"os"
	"sync"
	"time"
)

type fileCache struct {
	mu       sync.RWMutex
	size     int64
	maxSize  int64
	items    map[string]*fileCacheItem
	priority *priorityQueue
	cacheDir string
}

type fileCacheItem struct {
	key      string
	value    []byte
	size     int64
	modTime  time.Time
	filePath string
	index    int
  ttl      time.Duration
	freq     int
}

type priorityQueue []*fileCacheItem

func (pq priorityQueue) Len() int { return len(pq) }

func (pq priorityQueue) Less(i, j int) bool {
	return pq[i].freq < pq[j].freq || (pq[i].freq == pq[j].freq && pq[i].modTime.Before(pq[j].modTime))
}

func (pq priorityQueue) Swap(i, j int) {
	pq[i], pq[j] = pq[j], pq[i]
	pq[i].index = i
	pq[j].index = j
}

func (pq *priorityQueue) Push(x interface{}) {
	n := len(*pq)
	item := x.(*fileCacheItem)
	item.index = n
	*pq = append(*pq, item)
}

func (pq *priorityQueue) Pop() interface{} {
	old := *pq
	n := len(old)
	item := old[n-1]
	item.index = -1 // for safety
	*pq = old[0 : n-1]
	return item
}

func NewFileCache(cacheDir string, maxSize int64) *fileCache {
	c := &fileCache{
		items:    make(map[string]*fileCacheItem),
		priority: new(priorityQueue),
		cacheDir: cacheDir,
		maxSize:  maxSize,
	}
	err := os.MkdirAll(cacheDir, os.ModePerm)
  if err != nil {
    panic(err)
  }
	heap.Init(c.priority)
	return c
}

func (c *fileCache) Get(key string) ([]byte, bool) {
	c.mu.RLock()
	defer c.mu.RUnlock()

	if item, ok := c.items[key]; ok {
    if item.modTime.Add(item.ttl).Before(time.Now()) {
			c.delete(key)
			return nil, false
		}
		item.freq++
		heap.Fix(c.priority, item.index)
		return item.value, true
	}

	return nil, false
}

func (c *fileCache) Set(key string, value []byte, ttl time.Duration) {
	c.mu.Lock()
	defer c.mu.Unlock()

	if item, ok := c.items[key]; ok {
		item.value = value
		item.modTime = time.Now()
		item.freq++
    item.ttl = ttl
		heap.Fix(c.priority, item.index)
		return
	}

	item := &fileCacheItem{
		key:      key,
		value:    value,
		size:     int64(len(value)),
		modTime:  time.Now(),
		filePath: c.cacheDir + "/" + key,
    ttl:      ttl,
		freq:     1,
	}

	c.items[key] = item
	heap.Push(c.priority, item)
	c.size += item.size

	for c.size > c.maxSize {
		c.removeOldest()
	}

	ioutil.WriteFile(item.filePath, item.value, 0644)
}

func (c *fileCache) delete(key string) bool {
  if item, ok := c.items[key]; ok {
		delete(c.items, key)
		heap.Remove(c.priority, item.index)
		c.size -= item.size
		os.Remove(item.filePath)
		return true
	}

	return false
}

func (c *fileCache) Remove(key string) bool {
	c.mu.Lock()
	defer c.mu.Unlock()

	return c.delete(key)
}

func (c *fileCache) removeOldest() {
	item := heap.Pop(c.priority).(*fileCacheItem)
	delete(c.items, item.key)
	c.size -= item.size
	os.Remove(item.filePath)
}

func (c *fileCache) Len() int {
	c.mu.RLock()
	defer c.mu.RUnlock()

	return len(c.items)
}

func (c *fileCache) Clear() {
	c.mu.Lock()
	defer c.mu.Unlock()

	for key, item := range c.items {
		os.Remove(item.filePath)
		delete(c.items, key)
	}

	c.size = 0
	heap.Init(c.priority)
}

func (c *fileCache) Keys() []string {
	c.mu.RLock()
	defer c.mu.RUnlock()

	keys := make([]string, 0, len(c.items))
	for key := range c.items {
		keys = append(keys, key)
	}

	return keys
}

func (c *fileCache) Values() [][]byte {
	c.mu.RLock()
	defer c.mu.RUnlock()

	values := make([][]byte, 0, len(c.items))
	for _, item := range c.items {
		values = append(values, item.value)
	}

	return values
}