<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * 北师
 * Date: 17-1-6
 * Time: 上午9:54
 */
require_once __DIR__ . '/AbstractGetInfo.php';
class BeishiRecruitment extends AbstractGetInfo {
    public function __construct($day)
    {
        parent::__construct($day);
        $this->filePath = dirname(__FILE__) . '/data/beishizhaopin.txt';
    }

    /**
     * 提取信息
     * @param $content
     * @return array
     */
    public function fetchInfoFromContent($content)
    {
        $content = json_decode($content, true);
        $arrRet = array();
        if(empty($content['data'])) {
            return $arrRet;
        }
        foreach ($content['data'] as $item) {
            $curTime = $item['createTime']/1000;
            if(($curTime - $this->startTime) < 0) {
                continue;
            }
            $href = 'http://career.bnu.edu.cn/front/zpxx.jspa?tid=' . $item['tid'];
            $date = date('Y-m-d', $curTime);
            $arrRet[] = array(
                'time' => $date,
                'title' => "<a target=\"_blank\" href=\"$href\">" . $item['dwmc'] .'---'. $item['dwszddm'] . '</a>',
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
        $page = 1;
        $url = 'http://career.bnu.edu.cn/front/zp_query/zpxxQuery.jspa?paramMap.xxlx=1&paramMap.gzcs=&paramMap.dwhydm=42&paramMap.dwxzdm=&paramMap.dwmc=&page.curPage=';
        $arr = array();
        do{
            $res = $this->formatContentByUrl($url . $page);
            $arr = array_merge($arr, $res);
            $page++;
        }while(!empty($res));


        foreach ($arr as $key => $str) {
            if(!$this->isTarget($str)) {
                unset($arr[$key]);
            }
        }
        print_r($arr);
        $this->putContent2File($arr);
    }
}