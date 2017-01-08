<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * 万行人才招聘
 * Date: 17-1-6
 * Time: 上午9:54
 */
require_once __DIR__ . '/AbstractGetInfo.php';
class WanxingrencaiRecruitment extends AbstractGetInfo {
    public function __construct($day)
    {
        parent::__construct($day);
        $this->filePath = dirname(__FILE__) . '/data/wanxingrencaizhaopin.txt';
    }

    public function fetchInfoFromContent($content)
    {
        // TODO: Implement fetchInfoFromContent() method.
        $arrRet = array();
        $patternBlank = '/\s*/';
        $patternSlipt = '/(\d{4})\/(\d{1,2})\/(\d{1,2})/';
        $doc = phpQuery::newDocumentHTML($content);
        phpQuery::selectDocument($doc);
        foreach (pq('div.mainbody li') as $key => $t) {
            if($key === 0) {
                continue;
            }
            $href = 'http://www.job910.com' . pq('a', $t)->attr('href');
            $str = $t -> nodeValue;
            $str = preg_replace($patternBlank, '', $str);
            $arrTmp = preg_split($patternSlipt, $str, -1, PREG_SPLIT_DELIM_CAPTURE);
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
            '天津' => '26',
            '北京' => '01',
            '山西' => '22',
            '廊坊' => '0906',
        );
        $arr = array();
        $url = 'http://www.job910.com/search.aspx?keyword_type=1&keyword=&updatetime=15&workmethord=%E4%B8%8D%E9%99%90&orderby=1&pagesize=20&jobclass=1111,1125,1138&area=';
        foreach ($areaArr as $areaId) {
            $res = $this->formatContentByUrl($url . $areaId);
            $arr = array_merge($arr, $res);
        }
        $this->putContent2File($arr);
    }

}
