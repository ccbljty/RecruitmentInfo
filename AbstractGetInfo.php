<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * Date: 17-1-6
 * Time: 上午9:35
 */
include_once './CurlUtil.php';
include_once './phpQuery/phpQuery.php';
abstract class AbstractGetInfo {
    // 开始时间
    protected $startTime;
    // 结果存放路径
    protected $filePath;
    // 要筛选的城市
    protected $city;
    function __construct($day = 7) {
        $this->startTime = time() - $day*24*3600;
        $this->filePath = '';
        $this->city = array(
            '天津',
            '北京',
            '山西',
            '廊坊',
        );
    }

    /**
     * 获取原始内容
     * @param $url
     * @return mixed|null
     */
    protected function getContentByUrl($url)
    {
        $res = null;
        do{
            $res = CurlUtil::http($url);
        }while(empty($res));
        return $res;
    }

    /**
     * 获取目标信息
     * @param $content
     * @return mixed
     */
    abstract protected function fetchInfoFromContent($content);

    /**
     * 格式化
     * @param $url
     * @return array
     */
    protected function formatContentByUrl($url)
    {
        $content = $this->getContentByUrl($url);
        $content = strip_tags($content, '<div><ul><li><a>');
        $arr = $this->fetchInfoFromContent($content);
        $tmpArr = array();
        if(empty($arr)) {
            return $tmpArr;
        }
        foreach ($arr as $item) {
            $tmpArr[] = implode("\t", $item);
        }
        return $tmpArr;
    }

    /**
     *　是否是需要的
     * @param $str
     * @return int
     */
    protected function isTarget($str) {
        $isTarget = 0;
        foreach ($this->city as $city) {
            if(mb_stripos($str, $city) !== false) {
                $isTarget = 1;
                break;
            }
        }
        return $isTarget;
    }

    /**
     * 输出到文件
     * @param $data
     */
    protected function putContent2File($data)
    {
        file_put_contents($this->filePath, implode("\n", $data));
    }

    /**
     * 入口函数
     * @return mixed
     */
    abstract protected function run();
}