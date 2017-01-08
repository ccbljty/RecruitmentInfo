<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * 师范生网
 * Date: 17-1-6
 * Time: 上午9:54
 */
require_once __DIR__ . '/AbstractGetInfo.php';
class ShifangshengRecruitment extends AbstractGetInfo {
    public function __construct($day)
    {
        parent::__construct($day);
        $this->filePath = dirname(__FILE__) . '/data/shifangshengzhaopin.txt';
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
        foreach (pq('ul.d2 li') as $t) {
            $href = 'http://www.shifansheng.net' . pq('a', $t)->attr('href');
            $str = $t -> nodeValue;
            $str = preg_replace($patternBlank, '', $str);
            $arrTmp = preg_split($patternSlipt, $str, -1, PREG_SPLIT_DELIM_CAPTURE);
            $curTime = mktime(0, 0, 0, $arrTmp[2], $arrTmp[3], $arrTmp[1]);
            if(($curTime - $this->startTime) < 0) {
                continue;
            }
            $arrRet[] = array(
                'time' => $arrTmp[1] . '-' . $arrTmp[2] . '-' . $arrTmp[3],
                'title' => "<a target=\"_blank\" href=\"$href\">" . $arrTmp[0] . '</a>',
            );
        }
        return $arrRet;
    }

    public function run()
    {
        // TODO: Implement run() method.
        $url = 'http://www.zhaojiao.net/zhaojiao/list.php?catid=93&page=';
        $arr = array();
        $areaArr = array(
            '天津' => 'tianjinjiaoshizhaopin/',
            '北京' => 'beijingjiaoshizhaopin/',
            '山西' => 'shanxijiaoshizhaopin1/',
            '河北' => 'hebeijiaoshizhaopin/',
        );
        $url = 'http://www.shifansheng.net/';
        foreach ($areaArr as $pathInfo) {
            $res = $this->formatContentByUrl($url . $pathInfo);
            $arr = array_merge($arr, $res);
        }
        $this->putContent2File($arr);
    }

}
