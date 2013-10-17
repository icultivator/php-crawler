<?php

/*
 * 爬虫核心类
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'phpQuery' . DIRECTORY_SEPARATOR . 'phpQuery.php';

define('URLS', 1);
define('URL', 2);

class Crawler {

    //采集入口链接
    public $url;
    //列表链接规则
    public $list_rules;
    //标题获取规则
    public $title_rule;
    //内容获取规则
    public $content_rule;

    function __construct() {
        
    }

    public function crawl_article($type) {
        
        ob_end_clean(); //循环输出前关闭缓存区
        $res = $this->curl_func($this->url);
        $articles = $this->query_func($res, $type);
        $utils = new Utils();
        $utils->insert_db($articles);
    }

    public function curl_func($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    public function query_func($res, $type) {
        phpQuery::newDocument($res);
        $articles = array();
        switch ($type):
            case URLS:
                $urls_html = pq($this->list_rules)->html();
                $urls_arr = Utils::match_links($urls_html);
                foreach ($urls_arr['link'] as $url):
                    $html = $this->curl_func($url);
                    $article = $this->query_article($html);
                    $articles[] = $article;
                endforeach;
                break;
            case URL:
                $html = $this->curl_func($this->url);
                $article = $this->query_article($html);
                $articles[] = $article;
                break;
        endswitch;
        echo '采集所有文章成功,准备入库...<br>';
        flush();
        return $articles;        
    }

    public function query_article($res) {
        phpQuery::newDocumentHTML($res);
        $title = pq($this->title_rule)->html();
        $content = pq($this->content_rule)->html();
        //移除超链接标签（保留内容）	
	$content = preg_replace("/<a[^>]*>|<\/a>/i","",$content);
        //替换图片链接
        $content = $this->changeImageLinks($content);
        
        $article = array();
	$article['title'] = $title;
	$article['content'] = mysql_real_escape_string($content);
	
        echo '采集文章<'.$title.'>成功!<br>';
        flush();
        
        return $article;
    }

    function changeImageLinks($content) {
        //获取到所有的图片链接
        preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(png|jpg))\\2/i", $content, $imgurls);
        
        //下载图片保存地址
        $save_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'images';
        $webdir = '/crawl/images';

        foreach ($imgurls[3] as $imgurl):
            $imgarr = explode('/', $imgurl);
            $imgname = $imgarr[count($imgarr) - 1];
            $extarr = explode('.', $imgname);
            $ext = $extarr[1];
            $filename = md5($imgurl) . '.' . $ext;
            $errorno = Utils::download_image($imgurl, $save_dir, $filename);
            if (!$errorno):
                //echo '替换成功!<br>';
                $weburl = $webdir . '/' . $filename;
                $content = str_replace($imgurl, $weburl, $content);
            else:
                switch ($errorno) {
                    case 1:
                        echo 'url为空或错误!<br>';
                        break;
                    case 3:
                        echo 'filename为空或错误!<br>';
                        break;
                    case 5:
                        echo 'save_dir为空或错误!<br>';
                        break;
                    default:
                        echo '其他未知错误!<br>';
                        break;
                }
            endif;
        endforeach;
        
        return $content;
    }

    function autoLoadClass($classname) {
        $filename = $classname . ".class.php";
        $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path))
            include($path);
    }

    function __get($name) {
        return $this->$name;
    }

    function __set($name, $value) {
        $this->$name = $value;
    }
    
    public function test_curl(){
        echo $this->curl_func($this->url);
    }

}

?>
