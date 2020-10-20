<?php
class mtfBetter
{
    public function handler($arv = array()) {        
        $CONF = include 'conf.php';
        forEach ($CONF['arv'] as $k => $v) {
            if (!empty($arv[$k])) {
                $CONF['arv'][$k] = $arv[$k];
            }
        }

        $_p = $CONF['arv']['path'];
        
        if (file_exists($_p)) {
            $_i = pathinfo($_p);
            switch ($_i['extension']) {
                case 'css':
                case 'js':
                case 'html':
                    if (isset($CONF['js'][$_i['basename']])) {
                        if (!empty($CONF['js'][$_i['basename']])) {
                            header('Location: ' . CONF['js'][$_i['basename']]);
                        }
                    } else {
                        $_p_cache = $CONF['arv']['cache_dir']. md5($_i['dirname'] . '/' . $_i['basename']) . '.' . $_i['extension'];
                        if (file_exists($_p_cache)) {
                            $_c = file_get_contents($_p_cache);
                            $this->cacheClear(10);
                        } else {
                            $this->$taskManager(1, $_p, $_i['extension']);
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
                            $this->$taskManager(0);
                        }
                        contentType($_i['extension']);
                        die($_c);
                    }
                break;
                case 'jpeg':
                case 'jpg':
                    $this->webp(imagecreatefromstring(file_get_contents($_p)), md5($_i['basename']), 'jpeg');
                    break;
                case 'png':
                    $image = imagecreatefromstring(file_get_contents($_p));
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                    $this->webp($image, md5($_i['basename']), 'png');
                    imagedestroy($image);
                    break;
                case 'gif':
                    $this->webp(imagecreatefromstring(file_get_contents($_p)), md5($_i['basename']), 'gif');
                    break;
                default:
                    die(file_get_contents($_p));
            }
        }
    }
    private function webp($image, $filename, $extension) {
        if (!is_dir($CONF['arv']['cache_dir'])) {
            mkdir($CONF['arv']['cache_dir']);
        }
        $this->cacheClear(5);
        if(strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false) {
            $this->$taskManager(1, $_p, $extension);
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
            $this->$taskManager(0);
            if ($_b) {
                contentType('webp');
                die(file_get_contents($_p));
            }
        }
        contentType($extension);
        if (!file_exists($_p)) {
            $quality = $extension === 'png' ? 7 : 75;
            $im = 'image' . $extension;
            $im($image, $_p, $quality);
        }
        die(file_get_contents($_p));
    }
    private function cacheClear($rand) {
        if (rand(0, 100) > $rand) return;
        if (is_dir($CONF['arv']['cache_dir'])) {
            forEach(glob($CONF['arv']['cache_dir'] . '*.*') as $file) {
                if (time() - filectime($file) > $CONF['arv']['cache_time']) {
                    unlink($file);
                }
            }
        }
    }
    private function data($datas = null) {
        $_p = $CONF['arv']['cache_dir'] . 'data.php';
        if (file_exists($_p)) {
            $data = include $_p;
        } else {
            $data = array();
        }
        if (isset($datas )) {
            $data = array_merge($data, $datas);
            file_put_contents($_p, '<?php return ' . var_export($data, true) . ';?>');
        }  
        return $data;
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
    private function taskManager($add, $_p, $_extension) {
        $_data = $this->data();
        $_task_num = $_data['task_num'];
        $_task_num_time = $_data['task_num_time'];
        if (time() - $_task_num_time > 3600) {// 超过1分钟，忽略最大任务数，重置当前任务数
            $_task_num = 0;
        }
        if ($_task_num > $CONF['arv']['task_num']) {
            contentType($_extension);
            die($file_get_contents($_p));
        } else {
            if ($_task_num) {
                $this->data(array(
                    'task_num' => $add ? ++$_task_num : --$_task_num,
                    'task_num_time' => time()
                ));
            } else {
                $this->data(array(
                    'task_num' => $add ? 1 : 0,
                    'task_num_time' => time()
                ));
            }
        }
    }
}
?>