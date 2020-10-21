<?php
class mtfBetter
{
    public $CONF;
    public function __construct($arv = array())
    {
        session_start();
		$CONF = include dirname(__FILE__) . '/conf.php';
        forEach ($CONF['arv'] as $k => $v) {
            if (!empty($arv[$k])) {
                $CONF['arv'][$k] = $arv[$k];
            }
        }
        $this->CONF = $CONF;
    }
    
    public function handler() {        
        $CONF = $this->CONF;
        $_p = $CONF['arv']['path'];
        if (file_exists($_p)) {
            $_i = pathinfo($_p);
            switch ($_i['extension']) {
                case 'css':
                case 'js':
                case 'html':
                    if (isset($CONF['js'][$_i['basename']])) {
                        if (!empty($CONF['js'][$_i['basename']])) {
                            header('Location: ' . $CONF['js'][$_i['basename']]);
                        }
                    } else {
                        $_p_cache = $CONF['arv']['cache_dir']. md5($_i['dirname'] . '/' . $_i['basename']) . '.' . $_i['extension'];
                        if (file_exists($_p_cache)) {
                            $_c = file_get_contents($_p_cache);
                            $this->cacheClear(10);
                        } else {
                            $this->taskManager(1, $_p);
                            include_once(dirname(__FILE__) . '/vendor/autoload.php');
                            $minifier = new \marcocesarato\minifier\Minifier();
                            $_c = file_get_contents($_p);
                            if ($_i['extension'] === 'css') {
                                $_c = $minifier->minifyCSS($_c);
                            } else if ($_i['extension'] === 'js') {
                                $_c = $minifier->minifyJS($_c);
                            } else {
                                $_c = $minifier->minifyHTML($_c);
                            }
                            file_put_contents($_p_cache, $_c);
                            $this->taskManager(0);
                        }
                        $this->contentType($_i['extension']);
                        die($_c);
                    }
                break;
                case 'jpeg':
                case 'jpg':
                case 'png':
                case 'gif':
                    $this->taskManager(1, $_p);
                    $_p = $this->webp($_p, md5($_i['basename']));
                    $this->taskManager(0);
                    if ($_p) {
                        $this->contentType('webp');
                    } else {
                        $this->outPut($_p);
                    }
                default:
                    die(file_get_contents($_p));
            }
        }
    }
    public function webp($_p, $filename) {
        if (file_exists($_p)) {
            $CONF = $this->CONF;
            $image = imagecreatefromstring(file_get_contents($_p));
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
    
            if (!is_dir($CONF['arv']['cache_dir'])) {
                mkdir($CONF['arv']['cache_dir']);
            }
            $this->cacheClear(5);
            if(strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false) {
                $_b = true;
                $_p = $CONF['arv']['cache_dir']. $filename .'.webp';
                if (!file_exists($_p)) {
                    try {
                        imagepalettetotruecolor($image);
                        $_b  = imagewebp($image, $_p, 75);
                    } catch (\Exception $e) {
                        $_b  = false;
                    }
                }
                imagedestroy($image);
                if ($_b) {
                    return $_p;
                }
            }
        }
        return false;
    }
    private function outPut($_p) {
        $_i = pathinfo($_p);
        $this->contentType($_i['extension']);
        die(file_get_contents($_p));
    }
    private function cacheClear($rand) {
        $CONF = $this->CONF;
        if (rand(0, 100) > $rand) return;
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
        if ($_task_num > $CONF['arv']['task_num']) {
            if ($_p) $this->outPut($_p);
        } else {
            $_SESSION['mtfBetter_task_num'] = $add ? ++$_task_num : --$_task_num;
        }
        if ($_task_num < 0) {
            unset($_SESSION['mtfBetter_task_num']);
        }
    }
}
?>