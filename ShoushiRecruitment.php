<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * 首都师范
 * Date: 17-1-6
 * Time: 上午9:54
 */
require_once __DIR__ . '/AbstractGetInfo.php';
class ShoushiRecruitment extends AbstractGetInfo {
    public function __construct($day)
    {
        parent::__construct($day);
        $this->filePath = dirname(__FILE__) . '/data/shoushizhaopin.txt';
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
        $patternSlipt = '/(\d{4})\-(\d{1,2})\-(\d{1,2})/';
        $doc = phpQuery::newDocumentHTML($content);
        phpQuery::selectDocument($doc);
        foreach (pq('div.list1_main_rt_2 li') as $t) {
            $href = 'http://jy.cnu.edu.cn/zpxx/' . pq('a', $t)->attr('href');
            $str = $t -> nodeValue;
            $str = preg_replace($patternBlank, '', $str);
            $arrTmp = preg_split($patternSlipt, $str, -1, PREG_SPLIT_DELIM_CAPTURE);
            $curTime = mktime(0, 0, 0, $arrTmp[2], $arrTmp[3], $arrTmp[1]);
            if(($curTime - $this->startTime) < 0) {
                continue;
            }
            $arrRet[] = array(
                'time' => $arrTmp[1] . '-' . $arrTmp[2] . '-' . $arrTmp[3],
                'title' => "<a target=\"_blank\" href=\"$href\">" . $arrTmp[4] . '</a>',
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
        $page = 0;
        $url = 'http://jy.cnu.edu.cn/zpxx/';
        $arr = array();
        do{
            if($page === 0) {
                $indexPage = 'index.htm';
            }else {
                $indexPage = "index$page.htm";
            }
            $res = $this->formatContentByUrl($url . $indexPage);
            $arr = array_merge($arr, $res);
            $page++;
        }while(!empty($res));

        foreach ($arr as $key => $str) {
            if(!$this->isTarget($str)) {
                unset($arr[$key]);
            }
        }
        $this->putContent2File($arr);
    }
}