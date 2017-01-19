<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * 无忧招聘
 * Date: 17-1-6
 * Time: 上午9:54
 */
require_once __DIR__ . '/AbstractGetInfo.php';
class WuyouRecruitment extends AbstractGetInfo {
    public function __construct($day)
    {
        parent::__construct($day);
        $this->filePath = dirname(__FILE__) . '/data/wuyouzhaopin.txt';
    }

    public function fetchInfoFromContent($content)
    {
        $content = mb_convert_encoding($content, 'utf8', 'gb2312');
//        echo $content;
        // TODO: Implement fetchInfoFromContent() method.
        $arrRet = array();
        $patternBlank = '/\s*/';
        $patternSlipt = '/(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/';
        $doc = phpQuery::newDocumentHTML($content);
        phpQuery::selectDocument($doc);
        foreach (pq('div.news-list-left-content li') as $key => $t) {
            $class = pq($t)->attr('class');
            if(!empty($class)) {
                continue;
            }
            $href = 'http://www.51test.net' . pq('a', $t)->attr('href');
            $str = $t -> nodeValue;
            $str = preg_replace($patternBlank, '', $str);
            $arrTmp = preg_split($patternSlipt, $str, -1, PREG_SPLIT_DELIM_CAPTURE);
            if(count($arrTmp) < 5) {
                continue;
            }
            $curTime = mktime(0, 0, 0, $arrTmp[2], $arrTmp[3], $arrTmp[1]);
            if(($curTime - $this->startTime) < 0) {
                continue;
            }
            $arrRet[] = array(
                'time' => $arrTmp[1] . '-' . $arrTmp[2] . '-' . $arrTmp[3],
                'title' => "<a target=\"_blank\" href=\"$href\">"  . $arrTmp[0] . '</a>',
            );
        }
        return $arrRet;
    }

    public function run()
    {
        // TODO: Implement run() method.
        $areaArr = array(
            '天津' => 'tianjin/xinwen/',
            '北京' => 'beijing/xinwen/',
            '山西' => 'shanxi/xinwen/',
            '廊坊' => 'hebei/langfang/',
        );
        $arr = array();
        $url = 'http://www.51test.net/jszp/';
        foreach ($areaArr as $areaId) {
            $res = $this->formatContentByUrl($url . $areaId);
            $arr = array_merge($arr, $res);
        }
        $this->putContent2File($arr);
    }
}