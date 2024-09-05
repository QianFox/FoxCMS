<?php

define('INSTALL_PATH', str_replace('\\', '/', dirname(__FILE__)));//安装文件路径
define('ROOT_PATH', str_replace('\\', '/', dirname(INSTALL_PATH)));//项目路径
require_once(INSTALL_PATH."/install.common.php");

$check = $_POST['check'];
$dbhost = $_POST['dbhost'];
$dbport = $_POST['dbport'];
$dbuser = $_POST['dbuser'];
$dbpwd = $_POST['dbpwd'];
if($check == "conn"){//数据库连接
    $con = connDb($dbhost, $dbuser, $dbpwd, $dbport);
    if($con){
        echo 'true';
    }else{
        echo 'false';
    }
    exit();
}elseif ($check == "db"){//数据库名称
    $conn = connDb($dbhost, $dbuser, $dbpwd, $dbport);
    if($conn == null){
        echo 'conn';
    }else{
        try {
            $dbname = $_POST['dbname'];
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->exec("use `".$dbname."`");
            echo 'false';
        } catch (Exception $e) {
            echo "true";
        }
    }
    exit();
}elseif ($check == "cu"){//检查用户权限

    $conn = connDb($dbhost, $dbuser, $dbpwd, $dbport);
    if($conn == null){
        echo 'conn';
    }else{
        try {
            $stmt = $conn->query("SHOW GRANTS");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $cu = "false";
            foreach ($rows as $val){
                if($cu == "false"){
                    foreach ($val as $vall){
                        $value = get_between($vall,"GRANT", "ON");
                        $value = trim($value);
                        $allDb = trim(get_between($vall,"ON", "TO"));
                        if($value == "ALL PRIVILEGES"){
                            if($allDb == "*.*"){
                                $cu = "true";
                                break;
                            }
                        }else{
                            $valArr = explode(",", $value);
                            if(in_array("CREATE", $valArr) && in_array("DROP", $valArr) && in_array("PROCESS", $valArr)
                                && in_array("SELECT", $valArr) && in_array("INSERT", $valArr) && in_array("UPDATE", $valArr)
                                && in_array("DELETE", $valArr) && in_array("TABLES", $valArr) && in_array("LOCK", $valArr)
                                && in_array("ALTER", $valArr) && in_array("INDEX", $valArr) && in_array("TABLES", $valArr)
                            ){
                                if($allDb == "*.*"){
                                    $cu = "true";
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            echo $cu;
        } catch (Exception $e) {
            echo "false";
        }
    }
    exit();

}elseif ($check == "install_struct"){//安装结构
    $dbname = $_POST['dbname'];
    $my_website = $_POST['my_website'];
    $email = $_POST['email'];
    $domain = $_POST['domain'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $url_prefix = $_POST['url_prefix'];

    $pattern  = '/^(?![a-zA-Z]+$)(?![A-Z0-9]+$)(?![A-Z\W_]+$)(?![a-z0-9]+$)(?![a-z\W_]+$)(?![0-9\W_]+$)[a-zA-Z0-9\W_]{8,}$/';
    if (!preg_match($pattern,$password)){
        echo 'pwd';
        exit();
    }
    $password = xn_encrypt($password);
    $pdo = connDb($dbhost, $dbuser, $dbpwd, $dbport);
    if($pdo == null){
        echo 'conn';
        exit();
    }
    // 查询数据库
    $res = $pdo->query('show Databases');
    // 遍历所有数据库，存入数组
    $dbnameArr = [];
    foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $dbnameArr[] = $row['Database'];
    }
    // 检查数据库是否存在，没有则创建数据库
    if (!in_array(trim($dbname), $dbnameArr)) {
        $pdo->query("SET NAMES utf8"); // 设置数据库编码
        $pdo->query("SET FOREIGN_KEY_CHECKS = 0");
        if (!$pdo->exec("CREATE DATABASE `$dbname`")) {
            echo "create";
            exit();
        }
    }
    try{
        // 数据库创建完成，开始连接
        $pdo->query("USE `$dbname`");
        //创建表结构
        $sql_struct = file_get_contents(INSTALL_PATH."/data/install_struct.php");
        $f = $pdo->exec(trim($sql_struct));
        ob_flush();
        flush();
        // 结束缓存区
        ob_end_flush();
        $pdo = null;
        if($f==0){
            exit("true");
        }else{
            exit("false");
        }
    }catch (Exception $e){
        exit("false");
    }

}elseif ($check == "install_data"){//安装数据
    $dbname = $_POST['dbname'];
    $my_website = $_POST['my_website'];
    $email = $_POST['email'];
    $domain = $_POST['domain'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password = xn_encrypt($password);
    $url_prefix = $_POST['url_prefix'];
    $data_method = $_POST['data_method'];

    $pdo = connDb($dbhost, $dbuser, $dbpwd, $dbport);
    if($pdo == null){
        echo 'conn';
        exit();
    }
    try{
        // 数据库创建完成，开始连接
        $pdo->query("USE `$dbname`");
        //保存数据库信息
        $phpPath = ROOT_PATH."/data/";
        $filename = "dbconfig.php";
        $saveData = require_once($phpPath.$filename);
        $saveData['hostname'] = $dbhost;
        $saveData['database'] = $dbname;
        $saveData['username'] = $dbuser;
        $saveData['password'] = $dbpwd;
        $saveData['hostport'] = $dbport;
        set_php_arr($phpPath, $filename, $saveData);
        ob_flush();
        flush();
        //导入默认数据
        if($data_method == 2){
            $sql_data = file_get_contents(INSTALL_PATH."/data/pure_install_data.php");
        }else{
            $sql_data = file_get_contents(INSTALL_PATH."/data/install_data.php");
        }
        //将SQL大件内容被照分隔待进行分割
        $sqls = explode( ");\n",$sql_data);
        // 遍历并执行每条8L语句
        foreach ($sqls as $sql){
            //执行sql语句，并捕获发生的异常
            if(empty($sql)){
                continue;
            }
            try{
                if(end_with($sql, ";")){
                    $eSql = trim($sql);
               }else{
                    $eSql = trim($sql).")";
               }
                $pdo->exec($eSql);
            }catch (PDOException $e){
                exit("false".$e->getMessage());
            }
        }
//        $fd = $pdo->exec(trim($sql_data));
        ob_flush();
        flush();
        //修改基本信息
        $stmt = $pdo->query("select id from {$saveData['prefix']}basic limit 1");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($rows) > 0){
            $row = $rows[0];
            $upSql = "UPDATE `{$saveData['prefix']}basic` SET `url_prefix` = '{$url_prefix}', `url` = '{$domain}', `name` = '{$my_website}' WHERE `id` = {$row['id']}";
            $pdo->exec($upSql);
        }
        //修改用户信息
        $upSql = "UPDATE `{$saveData['prefix']}admin` SET `username` = '{$username}', `password` = '{$password}', `email` = '{$email}' WHERE `id` = 1";
        $pdo->exec($upSql);
        // 结束缓存区
        ob_end_flush();
        $pdo = null;
        exit("true");
    }catch (PDOException $e){
        exit("false".$e->getMessage());
    }
}
exit("false");
