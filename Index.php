<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * Date: 17-1-6
 * Time: 下午9:44
 */

// 记录执行日志
file_put_contents(__DIR__ . '/run.log', date('Y/m/d H:i:s', time()) . "\n", FILE_APPEND);

function __autoload($classname) {
    $classpath = __DIR__ . '/' . $classname.'.php';
    if(file_exists($classpath)){
        require_once($classpath);
    }
}

// 抓数据
$day = empty($argv[1]) ? 7 : $argv[1];
$arrObj = array(
    'HuashiRecruitment',
    'ShifangshengRecruitment',
    'TeacherRecruitment',
    'WanxingrencaiRecruitment',
    'ZhaojiaoRecruitment',
    'BeishiRecruitment',
    'ShoushiRecruitment',
    'TianjinshifanRecruitment',
    'WuyouRecruitment',
    'ZhonggongRecruitment',
);

foreach ($arrObj as $class) {
    $obj = new $class($day);
    call_user_func(array($obj, 'run'));
    file_put_contents(__DIR__ . '/run.log', serialize($obj) . "\n", FILE_APPEND);
}

// 发邮件
require_once __DIR__ . '/lib/swift_required.php';
$username = 'changchunboeisr@163.com';
$password = 'ccb13467076496';
$to = array(
    'zhangjineisr@163.com' => 'zj',
    'changchunboeisr@163.com' => 'ccb',
);
$from = array(
    'changchunboeisr@163.com' => 'ccb',
);
// 要发送的数据
$dir = __DIR__ . '/data';
$arr = array();
if(file_exists($dir) && is_dir($dir)) {
    $handleDir = opendir($dir);
    while(false !== ($file = readdir($handleDir))) {
        $tmpPath = $dir . '/' . $file;
        if(is_file($tmpPath)) {
            $tmp = file($tmpPath);
            $arr = array_merge($arr, $tmp);
        }
    }
}
rsort($arr);
if(empty($arr)) {
    exit;
}

$content = implode("<br/>", $arr);
$content = '<!DOCTYPE HTML><html><head><style type="text/css">a{text-decoration: none;color: #333333;} a:hover,a:visited{color: red;}</style></head><body>' . $content . '</body></html>';
//　发送邮件对象
$transport =  Swift_SmtpTransport::newInstance('smtp.163.com', 25)
    ->setUsername($username)
    ->setPassword($password);
// 创建mailer对象
$mailer = Swift_Mailer::newInstance($transport);
// 创建message对象
$message = Swift_Message::newInstance();
$message->setSubject('教师招聘信息汇总');
$message->setBody($content, 'text/html');
$message->setTo($to);
$message->setFrom($from);
// 发送邮件
$result = $mailer->send($message);
