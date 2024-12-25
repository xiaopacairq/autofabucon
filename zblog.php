<?php

date_default_timezone_set("PRC");
//在标注的文字中输入指定内容即可
//数据库配置开始
$mysql_prefix = ""; //（必填）输入表的前缀  默认dede_ *不可删除
$mysql_ip = "127.0.0.1"; //（必填）数据库链接Ip   *不可删除
$mysql_name = ""; //（必填）数据库用户名    *不可删除
$mysql_pwd = ""; //（必填）数据库密码       *不可删除
$mysql_base = ""; //（必填）数据库表名      *不可删除

$table_category = $mysql_prefix.'news';
$table_news = $mysql_prefix.'news';
$table_news_content = $mysql_prefix.'news_content';

//数据库配置结束
$pid =0;
$cid =3;

header("Content-type: text/html; charset=utf-8");

    
if (!isset($_POST["username"])) {
    $data["status"] = 1;
    $data["msg"] = "获取不到账户，校队错误";
    echo json_encode($data);
    exit;
}

$username = $_POST["username"];
if (mb_strlen($username) > 12) {
    $data["status"] = 1;
    $data["msg"] = "账户格式错误";
    echo json_encode($data);
    exit;
}
if ($username != "srq") {
    $data["status"] = 3;
    $data["msg"] = "账户错误";
    echo json_encode($data);
    exit;
}
$con = @mysqli_connect($mysql_ip, $mysql_name, $mysql_pwd, $mysql_name);
if (!$con) {
    $data['status'] = 1;
    $data['msg'] = '数据库连接失败';
    echo json_encode($data);
    exit;
}

$status = 0;
if (isset($_POST['status'])) {
    $status = intval($_POST['status']);
}

if($status == 0){
    $data["status"] = 0;
    $data["msg"] = "绑定成功";
    echo json_encode($data);
    exit;
}
if($status == 1){
    $keyword = $_POST["keyword"];
    $sql1 = "SELECT `name` FROM  $table_news WHERE name = '".$keyword ."'";
    $query = mysqli_query($con, $sql1);
    $res1 = mysqli_fetch_assoc($query);

    if($res1){
        $data['status'] = 2;
        $data['msg'] = '关键词已存在';
        echo json_encode($data);
        exit;
    }else{
        $data['status'] = 0;
        $data['msg'] = '关键词可用';
        echo json_encode($data);
        exit;
    }
}
if($status == 2){
    $title = "";
    if (!isset($_POST['name'])) {
        mysqli_close($con);
        $data['status'] = 4;
        $data['msg'] = '获取不到标题';
        echo json_encode($data);
        exit;
    }
    if (!isset($_POST['content'])) {
        mysqli_close($con);
        $data['status'] = 4;
        $data['msg'] = '获取不到内容';
        echo json_encode($data);
        exit;
    }
    $title = addslashes($_POST['name']);
    $brief = addslashes($_POST['brief']);
    $content = addslashes($_POST['content']);
    
    $seo_title = isset($_POST['title'])?addslashes($_POST['title']):'';
    $seo_keys = isset($_POST['keywords'])?addslashes($_POST['keywords']):'';
    $seo_desc = isset($_POST['description'])?addslashes($_POST['description']):'';
    
    mysqli_query($con, 'set names utf8');
    $time = date('Y-m-d H:i:s');
    $img_url = isset($_POST['img_url']) ? $_POST['img_url'] : '';

    $sql1 = "INSERT INTO $table_news (`name`,`image`,`created_at`,`cid`,`pid`)  "
            . "VALUES ('$title','$img_url',".time().",$cid,$pid)";
    $is_success = mysqli_query($con, $sql1);
    $id = mysqli_insert_id($con);
    if ($id) {
        $sql2 = "INSERT INTO $table_news_content (`id`,`content`,`brief`,`title`,`keywords`,`description`) VALUES ('" . $id . "','$content','$brief','$seo_title','$seo_keys','$seo_desc')";
        mysqli_query($con, $sql2);
        $classsql = "SELECT id FROM $table_news_content  WHERE  id = $id";
        $query = mysqli_query($con, $classsql);
        $class_name = mysqli_fetch_assoc($query);
        if ($class_name) {
            if (isset($class_name['id'])) {
                mysqli_query($con, "UPDATE $table_news SET is_display = 1 where id = $id");
                mysqli_close($con);
                $data['status'] = 0;
                $data['msg'] = '发送成功';
                $data['conid'] = $id;
                echo json_encode($data);
                exit;
            }else{
                mysqli_query($con, "DELETE FROM $table_news WHERE id = $id");
                mysqli_close($con);
                $data['status'] = 1;
                $data['msg'] = '失败';
                echo json_encode($data);
                exit;
            }
        }
        mysqli_close($con);
        $data['status'] = 1;
        $data['msg'] = '生成失败';
        echo json_encode($data);
        exit;
    } else {
        mysqli_close($con);
        $data['status'] = 1;
        $data['msg'] = '生成失败';
        echo json_encode($data);
        exit;
    }
}
if($status == 3){

    $uploadDir = 'uploads/keyimg/'.date('Ymd',time()).'/';  
    $uploadFile = $uploadDir . basename($_FILES['files']['name']);  
    if (!is_dir($uploadDir)) {  
        mkdir($uploadDir, 0777, true);  
    }  
    
    if (move_uploaded_file($_FILES['files']['tmp_name'], $uploadFile)) {  
        $data['status'] = 0;  
        $data['msg'] = '文件上传成功';  
        $data['file_name'] = basename($_FILES['files']['name']);  
        $data['file_type'] = $_FILES['files']['type'];  
        $data['file_size'] = $_FILES['files']['size'];  
    } else {  
        $data['status'] = 1;  
        $data['msg'] = '文件上传失败';  
        echo json_encode($data);
        exit;
    }  

    echo json_encode($data);
    exit;
}