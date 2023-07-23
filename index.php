<?php
$base_path = __DIR__ . '/';
header('Contend-Type: application/json; charset=UTF-8');
header('Cache-Control: no-chache, no-store, must-revalidate');
header('Access-Control-Allow-Origin: localhost');
header('Access-Control-Allow-Methods: GET,POST;');
header('Access-Control-Heders:X-Requested-with');

// А автолоадер вообще работает ? 
function autoloader(string $classname){ 
    global $base_path;
    $path =str_replace('\\', '/' , $base_path . 'modules/' . $classname . '.php');
    require_once $path; 
}
spl_autoload_register('autoloader');

// REST API 
$method= $_SERVER['REQUEST_METHOD'];
if($method === 'GET'){ 
            echo 'GetAllUsers';
            $sql = new \sql\sqlclass;
            echo $sql->GetAllUsers();
}
if($method === 'POST'){
    if(isset($_POST['__method'])){ 
            $method = $_POST['__method'];
            if($method === 'InsertNewUser'){
                $sql = new \sql\sqlclass;
                $sql->InsertNewUser( $_POST['name'], $_POST['login'] , $_POST['password']);
            }
            if($method === 'UpdateUser'){ 
                $sql = new \sql\sqlclass; 
                $sql->UpdateUser( $_POST['login'] , $_POST['name'] , $_POST['CheckPassword'], $_POST['new_name']  , $_POST['new_password'] );
            }
            if($method === 'DeleteUser'){
                $sql = new \sql\sqlclass; 
                $sql->DeleteUser($_POST['login'], $_POST['name'] , $_POST['CheckPassword']);
            }
            if($method === 'GetOneUser'){
                $sql = new \sql\sqlclass; 
                echo $sql->PickOneUser($_POST['login'], $_POST['name']);
            }
         }
     }
?>
