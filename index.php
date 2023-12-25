<?php
$base_path = __DIR__ . '/';
header('Contend-Type: application/json; charset=UTF-8');
header('Cache-Control: no-chache, no-store, must-revalidate');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET,POST;');
header('Access-Control-Heders:X-Requested-with');

// А автолоадер вообще работает ? 
function autoloader($classname){
    global $base_path;
    $path =str_replace('\\', '/' , $base_path . 'modules/' . $classname . '.php');
    require_once $path; 
}
spl_autoload_register('autoloader');

// REST API 
$method= $_SERVER['REQUEST_METHOD'];



//Anquette`s CRUDs 
if($method === 'GET'){ 
            $sql = new \sql\sqlclass;
            echo $sql->GetAllUsers();
}
if($method === 'POST'){
    if(isset($_POST['__method'])){ 
            $method = $_POST['__method'];

            if($method === 'GetAllUsersByType'){
                $sql = new \sql\sqlclass;
            echo $sql->GetAllUsersByType(
                    $_POST['type']
                );
            } 

            // That same as REGISTRATION
            if($method === 'InsertNewUser'){
                $sql = new \sql\sqlclass;
            echo $sql->InsertNewUser(
                    $_POST['name'],
                    $_POST['login'] ,
                    $_POST['password'],
                    $_POST['description'],
                    $_POST['MBTITYPE']
                );
            }
            if($method === 'UpdateUser'){ 
                $sql = new \sql\sqlclass; 
                $sql->UpdateUser(
                    $_POST['login'],
                    $_POST['name'],
                    $_POST['CheckPassword'], 
                    $_POST['new_name'],
                    $_POST['new_password'],
                    $_POST['new_description'],
                    $_POST['new_MBTITYPE'] 
                );
            }
            if($method === 'DeleteUser'){
                $sql = new \sql\sqlclass; 
                $sql->DeleteUser(
                    $_POST['login'],
                    $_POST['name'],
                    $_POST['CheckPassword']
                );
            }
            if($method === 'GetOneUser'){
                $sql = new \sql\sqlclass; 
                echo $sql->PickOneUser(
                    $_POST['login'],
                    $_POST['name']
                );
            }
            if($method === 'Login'){
                $sql = new \sql\sqlclass; 
                $sql->Login(
                    $_POST['login'],
                    $_POST['name'],
                    $_POST['password']
                ); 
            }

// CRUDs for comments 
            if($method === 'GetAllComments'){
                $sql = new \sql\CommentClass; 
                $sql->GetAllComments(
                    $_POST['AnquetteID']
                ); 
            }

            if($method === 'CreateComment'){
                $sql = new \sql\CommentClass; 
               $sql->CreateComment(
                    $_POST['AnquetteID'],
                    $_POST['AuthorName'],
                    $_POST['CommentBody']
                ); 
            }

            if($method === 'DeleteComment'){
                $sql = new \sql\CommentClass; 
                $sql->DeleteComment(
                    $_POST['AnquetteID'],
                    $_POST['CommentID'],
                    $_POST['AuthorLogin'],
                    $_POST['AuthorName'],
                    $_POST['AuthorPassword']
                ); 
            }

            if($method === 'UpdateComment'){
                $sql = new \sql\CommentClass; 
                $sql->UpdateComment(
                    $_POST['AnquetteID'],
                    $_POST['CommentID'],
                    $_POST['AuthorLogin'],
                    $_POST['AuthorName'],
                    $_POST['AuthorPassword'],
                    $_POST['CommentBody']
               ); 
            }
         }
     }
?>
