<?php
    $domain='tatarnews.ru';

    function kl_getlinks ($url, $host) {
        $fname=dirname(__FILE__)."/{$host}.links.db";
        if (!is_file($fname)) {
            return('<!--check code--><!--no file-->');
        }
        if (filesize($fname)==0) {
            return('<!--check code--><!--file size is 0-->');
        }

        $kl_links_src=file_get_contents($fname);

        @$kl_links=unserialize($kl_links_src);
        if (!is_array($kl_links)) {
            return '<!--check code--><!--wrong data-->';
        }

        if (isset($kl_links[$url])) {$links=$kl_links[$url];}
        else {$links='';}

        $links.=$kl_links['__checkcode__'];

        return $links;
    }

    /**
    * Получает данные с сервера и сохраняет в файл
    * 
    * @param string $host
    * @param string $type (curl, http10, http11)
    */
    function kl_getdata ($host, $type='curl') {
        $fname=dirname(__FILE__)."/{$host}.links.db";
        if (!is_file($fname)) {
            // Пытаемся создать файл.
            if (@touch($fname)) {
                @chmod($fname, 0777);    // Права доступа
            } else {
                echo "<!-- Нет файла {$fname}. Создать не удалось. Выставите права 777 на папку. -->";
                return;
            }
        }
        if (!is_writable($fname)) {
            echo "<!-- Нет доступа на запись к файлу: {$fname}! Выставите права 777 на папку или на файл. -->";
            return;
        }

        if ($type=='curl') {
            $ch=curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_URL, $url='http://l.yarus.net/l/lc.php?host='.$host);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data=curl_exec($ch);
            curl_close ($ch);
        }
        if ($type=='http10' || $type=='http11') {
            $fp=fsockopen('l.yarus.net', 80, $errno, $errstr, 15); 
            if($fp) {
                fwrite($fp, "GET /l/lc.php?host={$host} HTTP/1.".($type=='http10' ? '0' : '1')."\r\n");
                fwrite($fp, "Host: l.yarus.net\r\n");
                fwrite($fp, "Connection: Close\r\n\r\n");
                $result='';
                while(!feof($fp)) {$result.=fgets($fp);}
                fclose($fp);
                //removes headers
                $data=preg_replace("/^.*\r\n\r\n/Uis",'',$result);
                $data=preg_replace("/^[0-9a-f]+\r\n/Uis",'',$data);
                if (trim($data)=='') {
                    die('Can`t get body');
                }
                $f = @fopen($fname, 'w');
                if (!$f) {
                    die('Can`t fopen file');
                } else {
                    $bytes = fwrite($f, $data);
                    fclose($f);
                }          
            }
            else {
                die("Connection error: {$errstr} ({$errno})<br />\n");
            } 
        }

        $unserializedata=unserialize($data);
        if (!is_array($unserializedata)) {
            echo $data;
            die("wrong data!");
            return false;
        }
        file_put_contents($fname, $data);
        echo '<!--';
        foreach ($unserializedata as $k=>$v) {
            echo "Page [{$k}] links loaded<br>\n";
        }
        echo '-->';
    }
?>