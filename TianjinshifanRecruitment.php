<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * 北师
 * Date: 17-1-6
 * Time: 上午9:54
 */
require_once __DIR__ . '/AbstractGetInfo.php';
class TianjinshifanRecruitment extends AbstractGetInfo {
    public function __construct($day)
    {
        parent::__construct($day);
        $this->filePath = dirname(__FILE__) . '/data/tianshizhaopin.txt';
    }

    /**
     * 提取信息
     * @param $content
     * @return array
     */
    public function fetchInfoFromContent($content)
    {
        $arrRet = array();
        $patternBlank = '/[\[\]\s]*/';
        $patternSlipt = '/(\d{4})[\-\/](\d{1,2})[\-\/](\d{1,2})/';
        $doc = phpQuery::newDocumentHTML($content);
        phpQuery::selectDocument($doc);
        foreach (pq('table.winstyle49562 tr[id]') as $key => $t) {
            $href = 'http://jyzd.tjnu.edu.cn/' . substr(pq('a', $t)->attr('href'), 3);
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

    /**
     * 入口函数
     */
    public function run()
    {
        // TODO: Implement run() method.
        $url = 'http://jyzd.tjnu.edu.cn/index/zpxx.htm';
        $arr = $this->formatContentByUrl($url);

        foreach ($arr as $key => $str) {
            if(!$this->isTarget($str)) {
                unset($arr[$key]);
            }
        }
        $this->putContent2File($arr);
    }
}
