<?php
class mtfBetter
{
    public $CONF;
    public function __construct($arv = array())
    {
        if (!session_id()) session_start();
		$CONF = include dirname(__FILE__) . '/conf.php';
        forEach ($CONF as $k => $v) {
            if (!empty($arv[$k])) {
                $CONF[$k] = $arv[$k];
            }
        }
        if (!is_dir($CONF['cache_dir'])) {
            $this->cacheDir($CONF['cache_dir']);
        }
        $this->CONF = $CONF;
    }
    
    public function handler($paths = array()) {       
        $CONF = $this->CONF;
        foreach($paths as $_p) {
            if (is_file($_p)) {
                $_i = pathinfo($_p);
                $_i['extension'] = strtolower($_i['extension']);
                switch ($_i['extension']) {
                    case 'jpeg':
                    case 'jpg':
                    case 'png':
                        // 创建文件缓存目录
                        $this->cacheDir($_i['dirname']);
                        // 图片压缩 + 水印
                        $_webp = '';
                        if (!empty($CONF['available_pic'])) {
                            if(strpos($_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false) {
                                // 非PNG 或 PNG且PHP版本大于7.0.0 转Webp
                                if ($_i['extension'] !== 'png' || version_compare(PHP_VERSION, '7.0.0') === 1) {
                                    $this->taskManager(1);
                                    $_webp = $this->webp($_p);
                                    $this->taskManager(0);
                                }        
                            }
                        } 
                        if (!$_webp) {
                            $this->taskManager(1);
                            $this->compressPic($_p);
                            $this->taskManager(0);
                        }
                    default:
                        $this->cacheClear(1);
                }
            }
        }
    }
    private function water($image) {
        // 图片水印
        $CONF = $this->CONF;
        if ($CONF['watermark_pos'] && $CONF['watermark_path']) {
            if (is_file($CONF['watermark_path'])) {
                $water = imagecreatefromstring(file_get_contents($CONF['watermark_path']));
                $image_w = imagesx($image);
                $image_h = imagesy($image);
                $water_w = imagesx($water);
                $water_h = imagesy($water);
                if ($image_w < $water_w * 3 || $image_h < $water_h * 3) return $image;
                $pad = 10;
                switch ($CONF['watermark_pos']) {
                    case 'left-top':
                        $x = $pad;
                        $y = $pad;
                    break;
                    case 'left-bottom':
                        $x = $pad;
                        $y = $image_h - $water_h - $pad;
                    break;
                    case 'right-top':
                        $x = $image_w - $water_w - $pad;
                        $y = $pad;
                    break;
                    case 'right-bottom':
                        $x = $image_w - $water_w - $pad;
                        $y = $image_h - $water_h - $pad;
                    break;
                }
                $water = $this->savealpha($water);
                imagecopy($image, $water, $x, $y, 0, 0, $water_w, $water_h);
                imagedestroy($water);
            }
        }
        return $image;
    }
    private function savealpha($image) {
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        imagepalettetotruecolor($image);
        return $image;
    }
    public function webp($_p) {
        if (is_file($_p)) {
            $CONF = $this->CONF;
            $_i = pathinfo($_p);
            $_p_new = $CONF['cache_dir']. md5($_i['dirname'] . '/' . $_i['filename']) .'.webp';
            if (!is_file($_p_new)) {
                $image = imagecreatefromstring(file_get_contents($_p));
                $image = $this->savealpha($image);
                $image = $this->water($image);
                imagewebp($image, $_p_new, 70);
                imagedestroy($image);
            }
            return $_p_new;
        }
        return false;
    }
    public function compressPic($_p) {
        if (is_file($_p)) {
            $CONF = $this->CONF;
            $_i = pathinfo($_p);
            $_i['extension'] = strtolower($_i['extension']);
            $_p_new = $CONF['cache_dir']. md5($_i['dirname'] . '/' . $_i['filename']) . '.' . $_i['extension'];
            if (!is_file($_p_new)) {
                $image = imagecreatefromstring(file_get_contents($_p));
                $image = $this->savealpha($image);
                $image = $this->water($image);
                $quality = $_i['extension'] === 'png' ? 7 : 75;
                $im = 'image' . ($_i['extension'] === 'jpg' ? 'jpeg' : $_i['extension']);
                $im($image, $_p_new, $quality);
                imagedestroy($image);
            }
            return $_p_new;
        }
        return false;
    }
    public function cacheClear($rand) {
        if (rand(0, 100) > $rand) return;
        $CONF = $this->CONF;
        if (is_dir($CONF['cache_dir'])) {
            forEach(glob($CONF['cache_dir'] . '*.*') as $file) {
                if (time() - filectime($file) > $CONF['cache_time']) {
                    unlink($file); 
                }
            }
        }
    }
    public function cacheDir($_dirname) {
        $CONF = $this->CONF;
        $dirs = explode('/', $_dirname);
        $p = array();
        foreach($dirs as $dir) {
            $_p = $CONF['cache_dir'] . implode('/', $p);
            echo $_p;
            if (!is_dir($_p)) {
                mkdir($_p);
            }
            array_push($p, $dir);
        }
    }
    public function taskManager($add) {
        $CONF = $this->CONF;
        $_task_num = !empty($_SESSION['mtfBetter_task_num']) ? $_SESSION['mtfBetter_task_num'] : 0;
        if ($_task_num < 0 || isset($_SESSION['mtfBetter_task_time']) && time() - $_SESSION['mtfBetter_task_time'] > 10) {
            unset($_SESSION['mtfBetter_task_num'], $_SESSION['mtfBetter_task_time']);
        }
        if ($_task_num <= $CONF['task_num']) {
            $_SESSION['mtfBetter_task_num'] = $add ? ++$_task_num : --$_task_num;
            $_SESSION['mtfBetter_task_time'] = time();
        }
    }
    public function replace($rules) {
        $rules = $this->wildcardPath($rules);
        foreach($rules as $path => $rule) {
            $path_bak = $path . '.bak';
            $_c = file_get_contents($path);
            if (!is_file($path_bak)) {
                file_put_contents($path_bak, $_c);
            }
            file_put_contents($path, strtr($_c, $rule));
        }
    }
    public function restore($rules) {
        $rules = $this->wildcardPath($rules);
        foreach($rules as $path => $rule) {
            $path_bak = $path . '.bak';
            if (is_file($path_bak)) {
                if (copy($path_bak, $path)) {
                    unlink($path_bak);
                }
            }
        }
    }
    private function wildcardPath($rules) {
        foreach($rules as $path => $rule) {
            if (stripos($path, '*') !== FALSE) {
                $a = explode('*');
                $cur = glob($a[0].'/*');
                if($cur){
                    foreach($cur as $f){
                        if(is_dir($f)){
                            $rules[$f + '/' + $a[1]] = $rule;
                        }
                    }
                }
                unset($rules[$path]);
            }
        }
        return $rules;
    }
}
?>