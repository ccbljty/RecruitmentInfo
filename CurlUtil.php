<?php
/**
 * * * *************************************************************************
 *
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 *
 * ************************************************************************ */
/*
 * @file Curl.php
 * @author qianxuefeng(com@baidu.com)
 * @Date 14-8-27 下午3:17
 * @brief
 */

class CurlUtil {
    //debug 1 调试模式
    static $debug = 1;

    /**
     * [debugInfo debug调试]
     * @param  [type] $func      [description]
     * @param  [type] $ch        [description]
     * @param  [type] $ret       [description]
     * @param  [type] &$curlInfo [description]
     * @return [type]            [description]
     */
    public static function debugInfo($func, $ch, $ret, &$curlInfo) {
        if ((self::$debug == 1) || !$ret) {
            $curlInfo = curl_getinfo($ch);
            $error    = curl_error($ch);
            //Bd_Log::notice("$func [" . json_encode($curlInfo) . "] error [{$error}]");
        }
    }

    /**
     * [curlGet description]
     * @param  [type]  $url     [description]
     * @param  string  $header  [description]
     * @param  integer $timeout [description]
     * @return [type]           [description]
     */
    public static function curlGet($url, $header = '', $timeout = 1) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($header)); // 模拟的header头
        $result = curl_exec($ch);
        self::debugInfo(__METHOD__, $ch, $result, $curlInfo);
        curl_close($ch);
        if (!$result) {
            //Bd_Log::warning(__METHOD__ . "get curl error the url is " . $url);
        }
        return $result;
    }

    /**
     * [http description]
     * @param  [type]  $url          [description]
     * @param  string  $method       [description]
     * @param  [type]  $postfields   [description]
     * @param  boolean $multi        [description]
     * @param  array   $header_array [description]
     * @return [type]                [description]
     */
    public static function http($url, $method = 'GET', $postfields = null, $multi = false, $header_array = array()) {

        $ch = curl_init();

        /* Curl 设置 */
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        //print_r($postfields);

        $method = strtoupper($method);
        switch ($method) {
            case 'GET':
                $url = is_array($postfields) ? $url . '?' . http_build_query($postfields) : $url;
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    if (is_string($postfields)) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
                    } else {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
                    }
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }

        $header_array2 = array();

        if ($multi) {
            $header_array2 = array('Content-Type: multipart/form-data; boundary=' . self::$boundary, 'Expect: ');
        }

        if (is_array($header_array)) {
            foreach ($header_array as $k => $v) {
                array_push($header_array2, $k . ': ' . $v);
            }

        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array2);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        //$response = curl_exec($ch);

        //重试
        $times      = 0;
        $retryTimes = 3;
        do {
            $response = curl_exec($ch);
            if ($times > 0) {
                //Bd_Log::warning("curl failed " . $url . "\ttimes: " . $times);
            }

            if ($response) {
                break;
            }
            $times++;
        } while ($times <= $retryTimes);

        $curlInfo = array();
        self::debugInfo(__METHOD__, $ch, $response, $curlInfo);

        curl_close($ch);
        return $response;
    }

    /**
     * [curlPost description]
     * @param  [type]  $url       [description]
     * @param  array   $post_data [description]
     * @param  array   $header    [description]
     * @param  string  $cookie    [description]
     * @param  integer $timeout   [description]
     * @param  array   &$curlInfo [description]
     * @return [type]             [description]
     */
    public static function curlPost($url, $post_data = array(), $header = array(), $cookie = '', $timeout = 1, &$curlInfo = array()) {
        $post_string = is_array($post_data) ? http_build_query($post_data) : $post_data;

        $https = stripos($url, 'https://') === 0 ? true : false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); // 模拟的header头

        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        //设置连接结束后保存cookie信息的文件
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        $result = curl_exec($ch);

        self::debugInfo(__METHOD__, $ch, $result, $curlInfo);
        curl_close($ch);
        return $result;
    }

    /**
     * [get_content_from_url 定位服务使用，有多次超时重连]
     * @param  [type]  $url          [description]
     * @param  [type]  $host         [description]
     * @param  integer $timeout      [description]
     * @param  boolean $retry        [description]
     * @param  array   $extraHeaders [description]
     * @return [type]                [description]
     */
    public static function get_content_from_url($url, $host = null, $timeout = 1, $retry = false, $extraHeaders = array()) {
//        $timer = new Bd_Timer();
//        $timer->start(); //记录开始时间

        $ch = curl_init();
        if (isset($host)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $host);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        //curl_setopt($ch, CURLOPT_HTTPGET, 1);
        foreach ($extraHeaders as $key => $val) {
            curl_setopt($ch, $key, $val);
        }
        $res = curl_exec($ch);

//        $timer->stop(); //记录结束时间
        $error_code = 0;
        $status     = "succeed";
        if ($res === false) {
            $error_code = 1;
            $status     = "error";
        }
        $arr_args = array(
            "curl-url"    => $url,
//            "curl-cost"   => $timer->getTotalTime(),
            "curl-status" => $status,
        );
        //记录运行状态
        //Bd_Log::trace("", $error_code, $arr_args);

        $times = 1;
        //失败重试
        while (($res == false) && ($times <= 2) && $retry) {
            $times++;

            // retry after 200ms
            usleep(200000);
            $res = curl_exec($ch);
            if ($res != false) {
                break;
            }

        }

        if ($res) {
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $res        = substr($res, $headerSize);
        }

        curl_close($ch);
        return $res;
    }

}
