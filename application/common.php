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
use GatewayWorker\Lib\Gateway;
// 应用公共文件

/* kind_file_manager排序 */
function kind_cmp_func($a, $b) {
    global $order;
    if ($a['is_dir'] && !$b['is_dir']) {
        return -1;
    } else if (!$a['is_dir'] && $b['is_dir']) {
        return 1;
    } else {
        if ($order == 'size') {
            if ($a['filesize'] > $b['filesize']) {
                return 1;
            } else if ($a['filesize'] < $b['filesize']) {
                return -1;
            } else {
                return 0;
            }
        } else if ($order == 'type') {
            return strcmp($a['filetype'], $b['filetype']);
        } else {
            return strcmp($a['filename'], $b['filename']);
        }
    }
}

/* 判断字符串是否为json格式 */
function is_not_json($str){
    return is_null(json_decode($str));
}

/* 地图经纬度两点距离 */
function getDistance($lat1=0,$lng1=0,$lat2=0,$lng2=0)
{
    $earthRadius = 6371000; //地球半径,百度版 国际平均半径为6378137
    $lat1 = ($lat1 * pi() ) / 180;
    $lng1 = ($lng1 * pi() ) / 180;
    $lat2 = ($lat2 * pi() ) / 180;
    $lng2 = ($lng2 * pi() ) / 180;
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;
    return round($calculatedDistance);
}

/*
 * 将百度地图区域定点的字符串解析成索引数组
 * $str = lat1,lng1;lat2,lng2;...
 * 返回[['lat'=>纬度,'lng'=>经度],['lat'=>纬度,'lng'=>经度],...]
 */
function parseMapPoint($str){
    if(empty($str)) return false;
    $arrs = explode(';',$str);
    $result = [];
    if(count($arrs)<3) return false;
    foreach($arrs as $arr){
        $point = explode(',',$arr);
        if(count($point)!=2) continue;
        $result[] = ['lat'=>$point[0],'lat'=>$point[1]];
    }
    return $result;
}

/*
 *
 * 地图点的经纬度和多边形定点经纬度计算点是否在多边形内
 * $point 点的经纬度 ['lat'=>纬度,'lng'=>经度]
 * $pylon 多边形依次连接的顶点索引数组 [['lat'=>纬度,'lng'=>经度],['lat'=>纬度,'lng'=>经度],['lat'=>纬度,'lng'=>经度]]
 *
 * */
function pointInPolygon($point,$pylon){
    if(count($pylon)<3) return false;
    $lng = $point['lng'];
    $lat = $point['lat'];
    $throwCount = 0;
    for($i=0;$i<count($pylon);$i++){
        if($point==$pylon[$i]) return true;
        $j = ($i==count($pylon)-1) ? 0 : $i+1;
        if(betweenPoint($lat,$pylon[$i]['lat'],$pylon[$j]['lat']) &&
            $lng<=max($pylon[$i]['lng'],$pylon[$j]['lng'])){
            if(leftPoint($point,$pylon[$i],$pylon[$j])){
                $throwCount++;
                continue;
            }
        }
    }
    return $throwCount%2==1 ? true : false;
}

/* 判断某点经度或纬度的坐标轴在另外两点之间 */
function betweenPoint($point,$pa,$pb){
    return (($point>=$pa && $point<=$pb) || ($point<=$pa && $point>=$pb)) ? true : false;
}

/* 判断经纬度的某点在线段的一侧或在线段上 */
function leftPoint($point,$pointa,$pointb){
    $pointmax = $pointa;
    $pointmin = $pointb;
    if($pointa['lng'] < $pointb['lng']){
        $pointmax = $pointb;
        $pointmin = $pointa;
    }
    if($pointa['lat']==$pointb['lat']){
        if($point['lat']==$pointa['lat'] && betweenPoint($point['lng'],$pointa['lng'],$pointb['lng'])){
            return true;
        }
        return false;
    }
    return (abs(($point['lng']-$pointmax['lng'])/($point['lat']-$pointmax['lat'])) >=
        abs(($pointmax['lng']-$pointmin['lng'])/($pointmax['lat']-$pointmin['lat']))) ? true : false;
}

/* 获取图片文件的16*16缩略图的hash值的255位 */
function getImageHashValue($filepath) {
    $w = 16;
    $h = 16;
    $img = imagecreatetruecolor($w, $h);
    list($src_w, $src_h) = getimagesize($filepath);

    $extname = pathinfo($filepath, PATHINFO_EXTENSION);
    if(!in_array($extname, ['jpg','jpeg','png','gif'])) exit(false);
    $src = call_user_func('imagecreatefrom'. ( $extname == 'jpg' ? 'jpeg' : $extname ) , $filepath);

    imagecopyresampled($img, $src, 0, 0, 0, 0, $w, $h, $src_w, $src_h);
    imagedestroy($src);
    $total = 0;
    $array = array();
    for( $y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $gray = (imagecolorat($img, $x, $y) >> 8) & 0xFF;
            if(!isset($array[$y])) $array[$y] = array();
            $array[$y][$x] = $gray;
            $total += $gray;
        }
    }
    imagedestroy($img);
    $average = intval($total / ($w * $h * 2));
    $hash = '';
    for($y = 0; $y < $h; $y++) {
        for($x = 0; $x < $w; $x++) {
            $hash .= ($array[$y][$x] >= $average) ? '1' : '0';
        }
    }
    return substr($hash,0,-1);
}

/* kindeditor文件管理器获取文件列表方法 */
function kind_file_manager(){
    $upath = config('kindeditor.upload_path');
    $upath = isset($upath) && !empty($upath) ? $upath : 'public/upload/';
    $php_path = $_SERVER['DOCUMENT_ROOT'] . '/';
    $php_url = '/'.$upath;

    //根目录路径，可以指定绝对路径，比如 /var/www/attached/
    $root_path = str_replace('\\','/',$php_path . $upath);
    //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
    $root_url = $php_url;

    /*图片扩展名*/
    $ext_arr = config('kindeditor.file_manager_ext');
    $ext_arr = isset($ext_arr) && !empty($ext_arr) ? $ext_arr : ['gif', 'jpg', 'jpeg', 'png', 'bmp'];

    /*目录名*/
    $dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
    if (!in_array($dir_name, array('', 'image', 'flash', 'media', 'file'))) {
        echo "Invalid Directory name.";
        exit;
    }
    if ($dir_name !== '') {
        $root_path .= $dir_name . "/";
        $root_url .= $dir_name . "/";
        if (!file_exists($root_path)) {
            mkdir($root_path);
        }
    }

    /*根据path参数，设置各路径和URL*/
    if (empty($_GET['path'])) {
        $current_path = realpath($root_path) . '/';
        $current_url = $root_url;
        $current_dir_path = '';
        $moveup_dir_path = '';
    } else {
        $current_path = realpath($root_path) . '/' . $_GET['path'];
        $current_url = $root_url . $_GET['path'];
        $current_dir_path = $_GET['path'];
        $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
    }

    /*echo realpath($root_path);
    排序形式，name or size or type*/

    $order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);

    /*不允许使用..移动到上一级目录*/
    if (preg_match('/\.\./', $current_path)) {
        echo 'Access is not allowed.';
        exit;
    }
    /*最后一个字符不是*/
    if (!preg_match('/\/$/', $current_path)) {
        echo 'Parameter is not valid.';
        exit;
    }
    /*目录不存在或不是目录*/
    if (!file_exists($current_path) || !is_dir($current_path)) {
        echo 'Directory does not exist.';
        exit;
    }

    /*遍历目录取得文件信息*/
    $file_list = array();
    if ($handle = opendir($current_path)) {
        $i = 0;
        while (false !== ($filename = readdir($handle))) {
            if ($filename{0} == '.') continue;
            $file = $current_path . $filename;
            if (is_dir($file)) {
                $file_list[$i]['is_dir'] = true; //是否文件夹
                $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                $file_list[$i]['filesize'] = 0; //文件大小
                $file_list[$i]['is_photo'] = false; //是否图片
                $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
            } else {
                $file_list[$i]['is_dir'] = false;
                $file_list[$i]['has_file'] = false;
                $file_list[$i]['filesize'] = filesize($file);
                $file_list[$i]['dir_path'] = '';
                $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                $file_list[$i]['filetype'] = $file_ext;
            }
            $file_list[$i]['filename'] = $filename; //文件名，包含扩展名
            $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
            $i++;
        }
        closedir($handle);
    }

    /* 调用common公共方法里的kind排序方法 */
    usort($file_list, 'kind_cmp_func');

    $result = array();
    /* 相对于根目录的上一级目录 */
    $result['moveup_dir_path'] = $moveup_dir_path;
    /* 相对于根目录的当前目录 */
    $result['current_dir_path'] = $current_dir_path;
    /* 当前目录的URL */
    $result['current_url'] = $current_url;
    /* 文件数 */
    $result['total_count'] = count($file_list);
    /* 文件列表数组 */
    $result['file_list'] = $file_list;

    return kind_json($result);
}

/* kindeditor上传图片文件方法 */
function kind_upload(){

    $upath = config('kindeditor.upload_path');
    $upath = isset($upath) && !empty($upath) ? $upath : 'public/upload/';
    $php_path = $_SERVER['DOCUMENT_ROOT'] . '/';
    $php_url = '/'.$upath;

    //根目录路径，可以指定绝对路径，比如 /var/www/attached/
    $save_path = str_replace('\\','/',$php_path . $upath);
    //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
    $save_url = $php_url;

    /* 定义允许上传的文件扩展名*/
    $ext_arr = config('kindeditor.upload_file_type');
    $ext_arr = isset($ext_arr) && !empty($ext_arr) ? $ext_arr : [
        'image' => ['gif', 'jpg', 'jpeg', 'png', 'bmp'],
        'flash' => ['swf', 'flv'],
        'media' => ['swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'],
        'file' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'],
    ];
    /* 最大文件大小*/
    $max_size = 1000000;

    if(!file_exists($save_path)){
        mkdir($save_path,0777,true);
    }

    /* PHP上传失败*/
    if (!empty($_FILES['imgFile']['error'])) {
        switch($_FILES['imgFile']['error']){
            case '1':
                $error = '超过php.ini允许的大小。';
                break;
            case '2':
                $error = '超过表单允许的大小。';
                break;
            case '3':
                $error = '图片只有部分被上传。';
                break;
            case '4':
                $error = '请选择图片。';
                break;
            case '6':
                $error = '找不到临时目录。';
                break;
            case '7':
                $error = '写文件到硬盘出错。';
                break;
            case '8':
                $error = 'File upload stopped by extension。';
                break;
            case '999':
            default:
                $error = '未知错误。';
        }
        kind_json(['error'=>1,'message'=>$error]);
    }

    /* 有上传文件时*/
    if (empty($_FILES) === false) {
        /* 原文件名*/
        $file_name = $_FILES['imgFile']['name'];
        /* 服务器上临时文件名*/
        $tmp_name = $_FILES['imgFile']['tmp_name'];
        /* 文件大小*/
        $file_size = $_FILES['imgFile']['size'];
        /* 检查文件名*/
        if (!$file_name) {
            return kind_json(['error'=>1,'message'=>"请选择文件。"]);
        }
        /* 检查目录*/
        if (@is_dir($save_path) === false) {
            return kind_json(['error'=>1,'message'=>"上传目录不存在。"]);
        }
        /* 检查目录写权限*/
        if (@is_writable($save_path) === false) {
            return kind_json(['error'=>1,'message'=>"上传目录没有写权限。"]);
        }
        /* 检查是否已上传*/
        if (@is_uploaded_file($tmp_name) === false) {
            return kind_json(['error'=>1,'message'=>"上传失败。"]);
        }
        /* 检查文件大小*/
        if ($file_size > $max_size) {
            return kind_json(['error'=>1,'message'=>"上传文件大小超过限制。"]);
        }
        /* 检查目录名*/
        $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
        if (empty($ext_arr[$dir_name])) {
            return kind_json(['error'=>1,'message'=>"目录名不正确。"]);
        }
        /* 获得文件扩展名*/
        $temp_arr = explode(".", $file_name);
        $file_ext = array_pop($temp_arr);
        $file_ext = trim($file_ext);
        $file_ext = strtolower($file_ext);
        /* 检查扩展名*/
        if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
            return kind_json(['error'=>1,'message'=>"上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。"]);
        }
        /* 创建文件夹*/
        if ($dir_name !== '') {
            $save_path .= $dir_name . "/";
            $save_url .= $dir_name . "/";
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }
        }
        $ymd = date("Ymd");
        $save_path .= $ymd . "/";
        $save_url .= $ymd . "/";
        if (!file_exists($save_path)) {
            mkdir($save_path);
        }
        /* 新文件名*/
        $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
        /* 移动文件*/
        $file_path = $save_path . $new_file_name;
        if (move_uploaded_file($tmp_name, $file_path) === false) {
            return kind_json(['error'=>1,'message'=>"上传文件失败。"]);
        }
        @chmod($file_path, 0644);
        $file_url = $save_url . $new_file_name;

        return kind_json(['error'=>0,'url'=>$file_url]);
    }
}

/* kind_editor上传文件方法的返回json数据方法,无需使用,由前面方法调用 */
function kind_json($data=['error'=>1,'message'=>'unknow error']) {
    if(!is_array($data)) $data = ['error'=>1,'message'=>'parameter expect array, '.gettype($data).' given'];
    header('Content-type: text/html; charset=UTF-8');
    $json = new kindeditor\Services_JSON();
    return $json->encode($data);
}

/*
*    文件上传通用方法
*    $filename = 上传文件的FILES参数名,
*   $dir上传文件所在分类目录,
*   $validate上传文件的验证条件
 */
function upload_file($filename,$dir='file',$validate=['size'=>2097152,'ext'=>'jpg,jpeg,png,gif,bmp,xls,xlsx']){
    $file = request()->file($filename);
    if($file){
        $path_url = config('upload_path');
        $path_url = isset($path_url) && !empty($path_url) ? $path_url : 'public' . DS . 'upload';
        $path = ROOT_PATH . $path_url . DS . $dir;
        if(!file_exists($path)) mkdir($path,0777,true);
        $info = $file->validate($validate)->move($path);
        if($info){
            $filepath = $path . DS . $info->getSaveName();
            $urlpath = DS . $path_url . DS . $info->getSaveName();
            return ['status'=>0,'msg'=>'上传成功','data'=>['file_path'=>$filepath,'url_path'=>$urlpath]];
        }else{
            return ['status'=>1,'msg'=>$file->getError()];
        }
    }else{
        return ['status'=>1,'msg'=>'没有文件上传'];
    }
}

/* CURL请求封装 */
function curl($url='',$method='POST',$params=[],$header=[],$ssl=true){
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method);
    if(strtoupper($method)=='POST'){
        curl_setopt($ch,CURLOPT_POSTFIELDS,$params);
    }
    if(!empty($header)){
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
    }
    if($ssl){
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    }
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


/*
 * gateway-worker即时通信数据封装方法
 * @return string
 * @param $controller string 返回给前端处理的前端js控制器名
 * @param $action string 返回给前端处理的前端js控制器里的方法名
 * @param $data array 返回给前端的数据内容
 * @param $msg string 返回给前端的提示信息
 * @param $status int 返回给前端的状态码
 * */
function wmjson($controller='index',$action='error',$data=[],$msg='ok',$status=0){
    if(is_array($controller)) return json_encode($controller,true);
    return json_encode(['status'=>$status,'message'=>$msg,'controller'=>$controller,'action'=>$action,'result'=>$data],true);
}

/*
 * gateway-worker即时通信session封装方法
 * $client_id 客户端ID
 * $key session key, 当为true时则返回所有session,当为数组时按键值对循环设置session
 * $value 当不是false时,且$key不是true和数组时,设置单一session键值
 * */
function wmsession($client_id,$key=true,$value=false){
    $session = Gateway::getSession($client_id);
    if(empty($session)) $session = [];
    if($key===true) return $session;
    if(is_array($key)){
        foreach($key as $k=>$v){
            $session[$k] = $v;
        }
        Gateway::setSession($client_id,$session);return true;
    }
    if($value===false){
        return isset($session[$key]) ? $session[$key] : null;
    }
    $session[$key] = $value;
    Gateway::setSession($client_id,$session);
    return true;
}

/*
 * gateway-worker即时通信批量加入组群封装方法
 * $client_id 客户端ID
 * $groups 用户组的索引数组
 * */
function joinAllGroup($client_id,$groups=[]){
    if(!empty($groups) && is_array($groups)){
        foreach($groups as $gp){
            Gateway::joinGroup($client_id,$gp);
        }
    }
}

/**
 * 取随机字符串
 * @param int $length 随机串长度
 * @param string $p [可选]a或nlus
 * @return string 随机字符串
 */
function rand_str($length, $p = 'a')
{
    $n = '0123456789';
    $l = 'abcdefghijklmnopqrstuvwxyz';
    $u = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $s = '~!@#$%^&*()_+`-=[]{};:\\\'|,./<>?"';
    if ($p == 'a') {
        $src = $n . $l . $u . $s;
    } else {
        $src = '';
        if (stripos($p, 'n') !== false)
            $src .= $n;
        if (stripos($p, 'l') !== false)
            $src .= $l;
        if (stripos($p, 'u') !== false)
            $src .= $u;
        if (stripos($p, 's') !== false)
            $src .= $s;
    }
    return $src ? substr(str_shuffle($src), 0, $length) : $src;
}
