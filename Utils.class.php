<?php

/*
 * 工具类
 * 提供一些常用的公共函数
 */
 
class Utils {
	
    //匹配列表链接
    public static function match_links($document) {

        $match = array();

        preg_match_all("'<\s*a\s.*?href\s*=\s*([\"\'])?(?(1)(.*?)\\1|([^\s\>]+))[^>]*>?(.*?)</a>'isx", $document, $links);

        while (list($key, $val) = each($links[2])) {

            if (!empty($val))
                $match['link'][] = $val;
        }

        while (list($key, $val) = each($links[3])) {

            if (!empty($val))
                $match['link'][] = $val;
        }

        while (list($key, $val) = each($links[4])) {

            if (!empty($val))
                $match['content'][] = $val;
        }

        while (list($key, $val) = each($links[0])) {

            if (!empty($val))
                $match['all'][] = $val;
        }

        return $match;
    }
	
    //下载远程图片	 
    public static function download_image($url, $save_dir = '', $filename = '', $type = 0) {
        if (trim($url) == '') {
            return 1;
        }
        if (trim($save_dir) == '') {
            $save_dir = './';
        }
        if (trim($filename) == '') {//保存文件名
            $ext = strrchr($url, '.');
            if ($ext != '.gif' && $ext != '.jpg' && $ext != '.png') {
                return 3;
            }
            $filename = time() . $ext;
        }
        if (0 !== strrpos($save_dir, '/')) {
            $save_dir.='/';
        }
        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
            return 5;
        }
        //获取远程文件所采用的方法 
        if ($type) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            $img = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $img = ob_get_contents();
            ob_end_clean();
        }
        //$size=strlen($img);
        //文件大小 
        $fp2 = @fopen($save_dir . $filename, 'a');
        fwrite($fp2, $img);
        fclose($fp2);
        unset($img, $url);

        return 0;
    }
    
    //插入数据库
    public function insert_db($arrs) {
        $pdo = new PDO('mysql:host=localhost;dbname=test;charset=utf8', 'root', 'root');
        foreach ($arrs as $arr) {
            $count = $pdo->exec("insert into article(title,content) values(
			'" . $arr['title'] . "','" . $arr['content'] . "')");
            if ($count) {
                echo '文章<' . $arr['title'] . '>入库成功!<br>';
            } else {
                echo '文章<' . $arr['title'] . '>入库失败!<br>';
            }
            flush();//直接推送内容到浏览器
        }
        $pdo = null;
    }
 
    //查询数据库
    function query_db() {
        $pdo = new PDO('mysql:host=localhost;dbname=test;charset=utf8', 'root', 'root');
        $rs = $pdo->query('SELECT * from article');
        $pdo = null;
        return $rs;
    }

}

?>
