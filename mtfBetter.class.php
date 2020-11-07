<?php
class mtfBetter
{
    public $CONF;
    public function __construct($arv = array())
    {
        if (!session_id()) session_start();
		$CONF = include dirname(__FILE__) . '/conf.php';
        forEach ($CONF['arv'] as $k => $v) {
            if (!empty($arv[$k])) {
                $CONF['arv'][$k] = $arv[$k];
            }
        }
        if (!is_dir($CONF['arv']['cache_dir'])) {
            mkdir($CONF['arv']['cache_dir']);
        }
        $this->CONF = $CONF;
    }
    
    public function handler() {        
        $CONF = $this->CONF;
        $_p = $CONF['arv']['path'];
        if (is_file($_p)) {
            $_i = pathinfo($_p);
            $_i['extension'] = strtolower($_i['extension']);
            switch ($_i['extension']) {
                case 'css':
                case 'js':
                case 'html':
                    if (!empty($CONF['arv']['available_static'])) {
                        if (isset($CONF['static'][$_i['basename']])) {
                            if (!empty($CONF['static'][$_i['basename']])) {
                                header('Location: ' . $CONF['static'][$_i['basename']]);
                            } else {
                                die();
                            }
                        } else {
                            $_p_cache = $CONF['arv']['cache_dir']. md5($_i['dirname'] . '/' . $_i['basename']) . '.' . $_i['extension'];
                            if (is_file($_p_cache)) {
                                $_p = $_p_cache;
                            } else {
                                $this->taskManager(1, $_p);
                                if ($_i['extension'] === 'js') {
                                    copy($_p, $_p_cache);
                                } else {
                                    file_put_contents($_p_cache, $this->compressHtml(file_get_contents($_p)));
                                }
                                $_p = $_p_cache;
                                $this->taskManager(0);
                            }
                        }
                    }
                    $this->contentType($_i['extension']);
                    $this->gzip();
                    $this->readTheFile($_p);
                    exit;
                break;
                case 'jpeg':
                case 'jpg':
                case 'png':
                case 'gif':
                    // 图片压缩 + 水印
                    $_webp = '';
                    if (!empty($CONF['arv']['available_pic'])) {
                        if(strpos($_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false) {
                            // 非PNG 或 PNG且PHP版本大于7.0.0 转Webp
                            if ($_i['extension'] !== 'png' || version_compare(PHP_VERSION, '7.0.0') === 1) {
                                $this->taskManager(1, $_p);
                                $_webp = $this->webp($_p);
                                $this->taskManager(0);
                                if ($_webp) {
                                    $this->contentType('webp');
                                    $_p = $_webp;
                                }
                            }        
                        }
                    } 
                    if (!$_webp) {
                        $this->taskManager(1, $_p);
                        $_p = $this->compressPic($_p);
                        $this->contentType($_i['extension']);
                        $this->taskManager(0);
                    }
                default:
                    $this->cacheClear(1);
                    $this->readTheFile($_p);
            }
        }
    }
    private function water($image) {
        // 图片水印
        $CONF = $this->CONF;
        if ($CONF['arv']['watermark_pos'] && $CONF['arv']['watermark_path']) {
            if (is_file($CONF['arv']['watermark_path'])) {
                $water = imagecreatefromstring(file_get_contents($CONF['arv']['watermark_path']));
                $image_w = imagesx($image);
                $image_h = imagesy($image);
                $water_w = imagesx($water);
                $water_h = imagesy($water);
                if ($image_w < $water_w * 3 || $image_h < $water_h * 3) return $image;
                $pad = 10;
                switch ($CONF['arv']['watermark_pos']) {
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
    private function compressPic($_p) {
        if (is_file($_p)) {
            $CONF = $this->CONF;
            $_i = pathinfo($_p);
            $_i['extension'] = strtolower($_i['extension']);
            $_p_new = $CONF['arv']['cache_dir']. md5($_i['dirname'] . '/' . $_i['filename']) . '.' . $_i['extension'];
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
    function gzip() {
        if(!headers_sent() && extension_loaded("zlib") && strstr($_SERVER["HTTP_ACCEPT_ENCODING"],"gzip")) {
            ini_set('zlib.output_compression', 'On');
            ini_set('zlib.output_compression_level', '2');
        }
    }
    function compressHtml($_s){
        return str_replace(': ', ':', str_replace(array("\r\n", "\r", "\n", "\t", '    ', '    '), '', preg_replace(array(
            '!/\*[^*]*\*+([^/][^*]*\*+)*/!',// CSS注释
        ), array(
            ''
        ), $_s)));
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
            $_p_new = $CONF['arv']['cache_dir']. md5($_i['dirname'] . '/' . $_i['filename']) .'.webp';
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
    private function outPut($_p) {
        $_i = pathinfo($_p);
        $_i['extension'] = strtolower($_i['extension']);
        $this->contentType($_i['extension']);
        $this->readTheFile($_p);
    }
    private function cacheClear($rand) {
        if (rand(0, 100) > $rand) return;
        $CONF = $this->CONF;
        if (is_dir($CONF['arv']['cache_dir'])) {
            forEach(glob($CONF['arv']['cache_dir'] . '*.*') as $file) {
                if (time() - filectime($file) > $CONF['arv']['cache_time']) {
                    unlink($file);
                }
            }
        }
    }
    private function contentType($ext) {
        switch ($ext) {
            case 'css':
                header('Content-type: text/css');
            break;
            case 'js':
                header('Content-type: text/javascript');
            break;
            case 'html':
                header('Content-type: text/html');
            break;
            case 'jpg':
            case 'jpeg':
                header('Content-type: image/jpeg');
            break;
            case 'png':
                header('Content-type: image/png');
            break;
            case 'gif':
                header('Content-type: image/gif');
            break;
            case 'webp':
                header('Content-type: image/webp'); 
            break;
        }
    }
    private function taskManager($add, $_p = '') {
        $CONF = $this->CONF;
        $_task_num = !empty($_SESSION['mtfBetter_task_num']) ? $_SESSION['mtfBetter_task_num'] : 0;
        if ($_task_num < 0 || isset($_SESSION['mtfBetter_task_time']) && time() - $_SESSION['mtfBetter_task_time'] > 10) {
            unset($_SESSION['mtfBetter_task_num'], $_SESSION['mtfBetter_task_time']);
        }
        if ($_task_num > $CONF['arv']['task_num']) {
            if ($_p) $this->outPut($_p);
        } else {
            $_SESSION['mtfBetter_task_num'] = $add ? ++$_task_num : --$_task_num;
            $_SESSION['mtfBetter_task_time'] = time();
        }
    }
    private function readTheFile($path) {
        $file = new SplFileObject($path);
        while (!$file->eof()) {
            echo $file->fgets();
        }
        $file = null;
        die();
    }
}
?>