<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * 教师招聘网
 * Date: 17-1-6
 * Time: 上午9:54
 */
require_once __DIR__ . '/AbstractGetInfo.php';
class TeacherRecruitment extends AbstractGetInfo {
    public function __construct($day)
    {
        parent::__construct($day);
        $this->filePath = dirname(__FILE__) . '/data/jiaoshizhaopinwang.txt';
    }

    public function fetchInfoFromContent($content)
    {
        // TODO: Implement fetchInfoFromContent() method.
        $content = mb_convert_encoding($content, 'UTF-8', 'GB2312');
        $arrRet = array();
        $patternBlank = '/\s*/';
        $patternSlipt = '/(\d{4})\-(\d{1,2})\-(\d{1,2})/';
        $doc = phpQuery::newDocumentHTML($content);
        phpQuery::selectDocument($doc);
        foreach (pq('li.article') as $t) {
            $href = pq('a', $t)->attr('href');
            $str = $t -> nodeValue;
            $str = preg_replace($patternBlank, '', $str);
            $arrTmp = preg_split($patternSlipt, $str, -1, PREG_SPLIT_DELIM_CAPTURE);
            $curTime = mktime(0, 0, 0, $arrTmp[2], $arrTmp[3], $arrTmp[1]);
            if(($curTime - $this->startTime) < 0) {
                continue;
            }
            $arrRet[] = array(
                'time' => $arrTmp[1] . '-' . $arrTmp[2] . '-' . $arrTmp[3],
                'title' => "<a target=\"_blank\" href=\"$href\">"  . substr($arrTmp[0], 0, strlen($arrTmp[0]) - 15) . '</a>',
            );
        }
        return $arrRet;
    }

    public function run()
    {
        // TODO: Implement run() method.
        $urls = array(
            '天津' => 'tianjin/?City=&page=',
            '山西' => 'shanxi1/?City=&page=',
            '北京' => 'beijing/?City=&page=',
            '河北' => 'hebei/?City=&page=',
        );
        $urlPrefix = 'http://www.jiaoshizhaopin.net/';
        $arr = array();
        foreach ($urls as $postFix) {
            $page = 1;
            do{
                $url = $urlPrefix . $postFix . $page;
                $res = $this->formatContentByUrl($url);
                $arr = array_merge($arr, $res);
                $page++;
            }while(!empty($res));
        }
        $this->putContent2File($arr);
    }
}