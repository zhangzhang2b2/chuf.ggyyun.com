<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Db;

//获取用户信息
 function user($tel,$password){
        $where['tel']=$tel;
        $where['password']=$password;
        $user=Db::name('user')->where($where)->find();
        return $user['id'];
 }

function  phone($encryptedData,$iv,$sessionKey,$appid){
    require_once __DIR__ . '/../extend/wxpay/wxBizDataCrypt.php';
    $pc = new WXBizDataCrypt($appid, $sessionKey);
    $errCode = $pc->decryptData($encryptedData, $iv, $data );

    return ['errCode'=>$errCode,'data'=>$data];
}

//诊所名称
function settled_name($id){
    $data=Db::name('settled')->find($id);
    echo $data['clinic'];
}


// 应用公共文件
function isMobile()
{
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    if (isset($_SERVER['HTTP_VIA'])) {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}
/**
 * 子元素计数器
 * @param array $array
 * @param int   $pid
 * @return array
 */
function array_children_count($array, $pid)
{
    $counter = [];
    foreach ($array as $item) {
        $count = isset($counter[$item[$pid]]) ? $counter[$item[$pid]] : 0;
        $count++;
        $counter[$item[$pid]] = $count;
    }
    return $counter;
}
/**
 * 数组层级缩进转换
 * @param array $array 源数组
 * @param int   $pid
 * @param int   $level
 * @return array
 */
function array2level($array, $pid = 0, $level = 1)
{
    static $list = [];
    foreach ($array as $v) {
        if ($v['pid'] == $pid) {
            $v['level'] = $level;
            $list[]     = $v;
            array2level($array, $v['id'], $level + 1);
        }
    }
    return $list;
}
/**
 * 构建层级（树状）数组
 * @param array  $array          要进行处理的一维数组，经过该函数处理后，该数组自动转为树状数组
 * @param string $pid_name       父级ID的字段名
 * @param string $child_key_name 子元素键名
 * @return array|bool
 */
function array2tree(&$array, $pid_name = 'pid', $child_key_name = 'children')
{
    $counter = array_children_count($array, $pid_name);
    if (!isset($counter[0]) || $counter[0] == 0) {
        return $array;
    }
    $tree = [];
    while (isset($counter[0]) && $counter[0] > 0) {
        $temp = array_shift($array);
        if (isset($counter[$temp['id']]) && $counter[$temp['id']] > 0) {
            array_push($array, $temp);
        } else {
            if ($temp[$pid_name] == 0) {
                $tree[] = $temp;
            } else {
                $array = array_child_append($array, $temp[$pid_name], $temp, $child_key_name);
            }
        }
        $counter = array_children_count($array, $pid_name);
    }
    return $tree;
}
/**
 * 把元素插入到对应的父元素$child_key_name字段
 * @param        $parent
 * @param        $pid
 * @param        $child
 * @param string $child_key_name 子元素键名
 * @return mixed
 */
function array_child_append($parent, $pid, $child, $child_key_name)
{
    foreach ($parent as &$item) {
        if ($item['id'] == $pid) {
            if (!isset($item[$child_key_name])) {
                $item[$child_key_name] = [];
            }

            $item[$child_key_name][] = $child;
        }
    }
    return $parent;
}
/**
 * 手机号格式检查
 * @param string $mobile
 * @return bool
 */
function check_mobile_number($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    $reg = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';

    return preg_match($reg, $mobile) ? true : false;
}

function check_env(){
    $items = array(
        'os'      => array('操作系统', '不限制', '类Unix', PHP_OS, 'check'),
        'php'     => array('PHP版本', '5.5', '5.5+', PHP_VERSION, 'check'),
        'upload'  => array('附件上传', '不限制', '2M+', '未知', 'check'),
        'gd'      => array('GD库', '2.0', '2.0+', '未知', 'check'),
        'disk'    => array('磁盘空间', '100M', '不限制', '未知', 'check'),
    );

    // PHP环境检测
    if($items['php'][3] < $items['php'][1]){
        $items['php'][4] = 'times text-warning';
        session('error', true);
    }

    // 附件上传检测
    if(@ini_get('file_uploads'))
        $items['upload'][3] = ini_get('upload_max_filesize');

    // GD库检测
    $tmp = function_exists('gd_info') ? gd_info() : array();
    if(empty($tmp['GD Version'])){
        $items['gd'][3] = '未安装';
        $items['gd'][4] = 'times text-warning';
        session('error', true);
    } else {
        $items['gd'][3] = $tmp['GD Version'];
    }
    unset($tmp);

    // 磁盘空间检测
    if(function_exists('disk_free_space')) {
        $disk_size = floor(disk_free_space(INSTALL_APP_PATH) / (1024*1024));
        $items['disk'][3] = $disk_size.'M';
        if ($disk_size < 100) {
            $items['disk'][4] = 'times text-warning';
            session('error', true);
        }
    }

    return $items;
}

/**
 * 目录，文件读写检测
 * @return array 检测数据
 */
function check_dirfile(){
    $items = array(
        array('dir',  '可写', 'check', '../application'),
        array('dir',  '可写', 'check', '../data'),
        array('dir',  '可写', 'check', '../export'),
        array('dir',  '可写', 'check', '../packet'),
        array('dir',  '可写', 'check', '../plugins'),
        array('dir',  '可写', 'check', './static'),
        array('dir',  '可写', 'check', './uploads'),
        array('dir',  '可写', 'check', '../runtime'),
        array('dir',  '可写', 'check', '../store'),
    );

    foreach ($items as &$val) {
        $item = INSTALL_APP_PATH . $val[3];
        if('dir' == $val[0]){
            if(!is_writable($item)) {
                if(is_dir($item)) {
                    $val[1] = '可读';
                    $val[2] = 'times text-warning';
                    session('error', true);
                } else {
                    $val[1] = '不存在';
                    $val[2] = 'times text-warning';
                    session('error', true);
                }
            }
        } else {
            if(file_exists($item)) {
                if(!is_writable($item)) {
                    $val[1] = '不可写';
                    $val[2] = 'times text-warning';
                    session('error', true);
                }
            } else {
                if(!is_writable(dirname($item))) {
                    $val[1] = '不存在';
                    $val[2] = 'times text-warning';
                    session('error', true);
                }
            }
        }
    }

    return $items;
}

/**
 * 函数检测
 * @return array 检测数据
 */
function check_func(){
    $items = array(
        array('pdo','支持','check','类'),
        array('pdo_mysql','支持','check','模块'),
        array('fileinfo','支持','check','模块'),
        array('curl','支持','check','模块'),
        array('file_get_contents', '支持', 'check','函数'),
        array('mb_strlen', '支持', 'check','函数'),
    );

    foreach ($items as &$val) {
        if(('类'==$val[3] && !class_exists($val[0]))
            || ('模块'==$val[3] && !extension_loaded($val[0]))
            || ('函数'==$val[3] && !function_exists($val[0]))
        ){
            $val[1] = '不支持';
            $val[2] = 'times text-warning';
            session('error', true);
        }
    }

    return $items;
}

/**
 * 写入配置文件
 * @param $config
 * @return array 配置信息
 */
function write_config($config){
    if(is_array($config)){
        //读取配置内容
        $conf = file_get_contents(APP_PATH . 'install/data/database.tpl');
        // 替换配置项
        foreach ($config as $name => $value) {
            $conf = str_replace("[{$name}]", $value, $conf);
        }

        //写入应用配置文件
        if(file_put_contents(APP_PATH . 'database.php', $conf)){
            show_msg('配置文件写入成功');
        } else {
            show_msg('配置文件写入失败！', 'error');
            session('error', true);
        }
        return '';
    }
}

/**
 * 创建数据表
 * @param $db 数据库连接资源
 * @param string $prefix 表前缀
 */
function create_tables($db, $prefix = ''){
    // 读取SQL文件
    $sql = file_get_contents(APP_PATH . 'install/data/dolphin.sql');

    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);

    // 替换表前缀
    $orginal = config('original_table_prefix');
    $sql = str_replace(" `{$orginal}", " `{$prefix}", $sql);

    // 开始安装
    show_progress('0%');
    $all_table = config('install_table_total');
    $i = 1;
    foreach ($sql as $value) {
        $value = trim($value);
        if(empty($value)) continue;
        $msg  = (int)($i/$all_table*100) . '%';
        if(false !== $db->execute($value)){
            show_progress($msg);
        } else {
            show_progress($msg, 'error');
            session('error', true);
        }
        $i++;
    }
}

/**
 * 更新数据表
 * @param $db 数据库连接资源
 * @param string $prefix 表前缀
 */
function update_tables($db, $prefix = ''){
    //读取SQL文件
    $sql = file_get_contents(APP_PATH . 'install/data/update.sql');
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);

    // 替换表前缀
    $sql = str_replace(" `dp_", " `{$prefix}", $sql);

    //开始安装
    show_progress('0%');
    $all_table = config('update_data_total');
    $i = 1;
    $msg = '';
    foreach ($sql as $value) {
        $value = trim($value);
        if(empty($value)) continue;
        if(substr($value, 0, 12) == 'CREATE TABLE') {
            $msg  = (int)($i/$all_table*100) . '%';
            if(($db->execute($value)) === false){
                session('error', true);
            }
        } else {
            if(substr($value, 0, 8) == 'UPDATE `') {
                $msg  = (int)($i/$all_table*100) . '%';
            } else if(substr($value, 0, 11) == 'ALTER TABLE'){
                $msg  = (int)($i/$all_table*100) . '%';
            } else if(substr($value, 0, 11) == 'INSERT INTO'){
                $msg  = (int)($i/$all_table*100) . '%';
            }
            if(($db->execute($value)) === false){
                session('error', true);
            }
        }

        if ($msg != '') {
            show_progress($msg);
            $i++;
        }
    }
}

/**
 * 及时显示提示信息
 * @param  string $msg 提示信息
 */
function show_msg($msg, $class = ''){
    echo "<script type=\"text/javascript\">showmsg(\"{$msg}\", \"{$class}\")</script>";
    flush();
    ob_flush();
}

/**
 * 显示进度
 * @param $msg
 * @param string $class
 * @author 蔡伟明 <314013107@qq.com>
 */
function show_progress($msg, $class = ''){
    echo "<script type=\"text/javascript\">show_progress(\"{$msg}\", \"{$class}\")</script>";
    flush();
    ob_flush();
}
/**
 * 获取 IP  地理位置
 * 淘宝IP接口
 * @Return: array
 */
function getCity($ip = '')
{
    if($ip == ''){
        $url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json";
        $ip=json_decode(file_get_contents($url),true);
        $data = $ip;
    }else{
        $url="http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
        $ip=json_decode(file_get_contents($url));   
        if((string)$ip->code=='1'){
           return false;
        }
        $data = (array)$ip->data;
    }
    
    return $data;   
}
//获取客户端真实IP
function getClientIP()
{
    global $ip;
    if (getenv("HTTP_CLIENT_IP")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else if (getenv("HTTP_X_FORWARDED_FOR")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } else if (getenv("REMOTE_ADDR")) {
        $ip = getenv("REMOTE_ADDR");
    } else {
        $ip = "Unknow";
    }

    return $ip;
}

/**
 * 发起http请求
 * @param string $url 访问路径
 * @param array $params 参数，该数组多于1个，表示为POST
 * @param int $expire 请求超时时间
 * @param array $extend 请求伪造包头参数
 * @param string $hostIp HOST的地址
 * @return array    返回的为一个请求状态，一个内容
 */
function makeRequest($url, $params = array(), $expire = 0, $extend = array(), $hostIp = '')
{
    if (empty($url)) {
        return array('code' => '100');
    }

    $_curl = curl_init();
    $_header = array(
        'Accept-Language: zh-CN',
        'Connection: Keep-Alive',
        'Cache-Control: no-cache'
    );
    // 方便直接访问要设置host的地址
    if (!empty($hostIp)) {
        $urlInfo = parse_url($url);
        if (empty($urlInfo['host'])) {
            $urlInfo['host'] = substr(DOMAIN, 7, -1);
            $url = "http://{$hostIp}{$url}";
        } else {
            $url = str_replace($urlInfo['host'], $hostIp, $url);
        }
        $_header[] = "Host: {$urlInfo['host']}";
    }

    // 只要第二个参数传了值之后，就是POST的
    if (!empty($params)) {
        curl_setopt($_curl, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($_curl, CURLOPT_POST, true);
    }

    if (substr($url, 0, 8) == 'https://') {
        curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    curl_setopt($_curl, CURLOPT_URL, $url);
    curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($_curl, CURLOPT_USERAGENT, 'API PHP CURL');
    curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);

    if ($expire > 0) {
        curl_setopt($_curl, CURLOPT_TIMEOUT, $expire); // 处理超时时间
        curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, $expire); // 建立连接超时时间
    }

    // 额外的配置
    if (!empty($extend)) {
        curl_setopt_array($_curl, $extend);
    }

    $result['result'] = curl_exec($_curl);
    $result['code'] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
    $result['info'] = curl_getinfo($_curl);
    if ($result['result'] === false) {
        $result['result'] = curl_error($_curl);
        $result['code'] = -curl_errno($_curl);
    }

    curl_close($_curl);
    return $result;
}

//返回json数据
function  json_code($code,$msg,$data=array()){
    $arr=array('code'=>$code,'msg'=>$msg,'data'=>$data);
    exit(json_encode($arr));
}

//图片显示
function file_show_pic($id){
    $pic = Db::name('file')->where('id','in',$id)->select();
    foreach ($pic as $k => $v) {
       echo '<img src="'.$v['url'].'" width="80" height="80" ondblclick=tClick("'.$v['url'].'")>';  //tClick方法在根目录/public/static/common/js/indexall.js
   }
}


/**
 * 导出excel
 * @param  string   $xlsTitle 导出文件名字
 * @param   array   $expCellName 表头
 * @param   array   $expTableData 导出数据
 * @return array    excel文件内容数组
 **/
function export_excel($expTitle,$expCellName,$expTableData){

    $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
    $fileName = $xlsTitle.'-'.date('YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
    $cellNum = count($expCellName);
    $dataNum = count($expTableData); 
    require_once __DIR__ . '/../extend/phpexcel/Classes/PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

    for($i=0;$i<$cellNum;$i++){
        //填充颜色
        // $objPHPExcel->getActiveSheet(0)->getStyle($cellName[$i].'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        // $objPHPExcel->getActiveSheet(0)->getStyle($cellName[$i].'1')->getFill()->getStartColor()->setARGB('FFccc');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->getFill()->getStartColor()->setARGB('FFFFB400');
        $k=$i+1;
        $objPHPExcel->getActiveSheet()->getRowDimension($k)->setRowHeight(25);
    }

    // Miscellaneous glyphs, UTF-8
    for($i=0;$i<$dataNum;$i++){
        for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);
            $objPHPExcel->getActiveSheet(0)->getColumnDimension($cellName[$j])->setAutoSize(true);
        }
    }
    // 输出Excel表格到浏览器下载
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');
    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0


    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $temp = 'Uploads/temp/'.$fileName.".xls";
    //$objWriter->save($temp);
    $objWriter->save('php://output');
    //return $temp;
}
