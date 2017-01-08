<?php
/**
 * Created by PhpStorm.
 * 华师
 * User: bobo
 * Date: 17-1-6
 * Time: 上午9:54
 */
require_once __DIR__ . '/AbstractGetInfo.php';
class HuashiRecruitment extends AbstractGetInfo {
    public function __construct($day)
    {
        parent::__construct($day);
        $this->filePath = dirname(__FILE__) . '/data/huashizhaopin.txt';
    }


    public function fetchInfoFromContent($content)
    {
        // TODO: Implement fetchInfoFromContent() method.
        $arrRet = array();
        $patternBlank = '/\s*/';
        $patternSlipt = '/(\d{4})\-(\d{1,2})\-(\d{1,2})/';
        $doc = phpQuery::newDocumentHTML($content);
        phpQuery::selectDocument($doc);
        foreach (pq('ul.nwcont li') as $t) {
            $href = 'http://career.ccnu.edu.cn' . pq('a', $t)->attr('href');
            $str = $t -> nodeValue;
            $str = preg_replace($patternBlank, '', $str);
            $isTarget = 0;
            foreach ($this->city as $city) {
                if(mb_stripos($str, $city) !== false) {
                    $isTarget = 1;
                    break;
                }
            }
            if(!$isTarget) {
                continue;
            }
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
        $requests = array(
            'http://career.ccnu.edu.cn/Schedule/ScheduleCategory?type=%E6%A0%A1%E5%86%85%E4%B8%93%E5%9C%BA',
            'http://career.ccnu.edu.cn/Schedule/ScheduleCategory?type=%E6%A0%A1%E5%A4%96%E4%B8%93%E5%9C%BA',
        );
        $arr = array();
        foreach ($requests as $request) {
            $res = $this->formatContentByUrl($request);
            $arr = array_merge($arr, $res);
        }
        foreach ($arr as $key => $str) {
            if(!$this->isTarget($str)) {
                unset($arr[$key]);
            }
        }
        $this->putContent2File($arr);
    }
}

