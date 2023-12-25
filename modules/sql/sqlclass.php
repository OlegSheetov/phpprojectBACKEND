<?php 
namespace sql; 
use PDO;

//require_once $base_path . 'modules/settings.php';

class sqlclass extends baseclass { 

    // __construct и __destruct ,  подключение к базе данных в baseclass.php
    // Этот класс с основными CRUD-ами для создания анкет и остальной работы с ними 
    // ХЗ почему я его назвал sqlclass , а не AnquetteClass но по крайней мере он работает.


    // Удалил логин , если что-то не работает верниииии
    function GetAllUsers() { 
        try {
            $statement = self::$connection->prepare('
                SELECT 
                    id, 
                    name, 
                    description, 
                    mbtitype
                FROM 
                    users
                ORDER BY RAND()
            ');
            $statement->execute();
            $users = $statement->fetchAll(PDO::FETCH_ASSOC);
            return json_encode($users, JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    } 

    // Наверное зря написал потому что проще сортировать то 
    // что уже запрошено через общий запрос
//    function GetAllUsersByType($type){ 
//        try {
//            $state = self::$connection->prepare('
//                SELECT 
//                    id, 
//                    name, 
//                    description, 
//                    mbtitype
//                FROM 
//                    users
//                WHERE
//                    mbtitype = :type
//                ORDER BY RAND()
//            ');
//            $state->bindValue(':type', $type , PDO::PARAM_STR);
//            $state->execute();
//            $users = $state->fetchAll(PDO::FETCH_ASSOC);
//            return json_encode($users, JSON_UNESCAPED_UNICODE);
//        } catch (PDOException $e) {
//            echo $e->getMessage();
//        }
//    }


    function PickOneUser(
        string $CurrentUserLogin,
        string $CurrentUserName
    ) { 
            $user = self::PapersPlease($CurrentUserLogin , $CurrentUserName);
            if(isset($user)){
                try{ 
                    $userJSON = json_encode($user, JSON_UNESCAPED_UNICODE);
                    return $userJSON;
                }catch (PDOException $e) {
                    echo $e->getMessage();
                }
            }else { 
                echo 'User not found';
                http_response_code(404);
            }

    }

    function InsertNewUser(
        string $name,
        string $login,
        string $password,
        string $description,
        string $mbtitype
    ){
        try {
            $Hashed_password = password_hash($password , PASSWORD_DEFAULT);
            $isuserexist = self::IsUserExist($login , $name);
            if($isuserexist === false ){ 
                //  Если пользователя нет , то регистрируем его, в обратном случае 
                //  отправляем сообщения "Дубликат", что просто означает что пользователь
                //  с похожим ником и логином уже существует.
                $state = self::$connection->prepare("
                    INSERT INTO `users`
                        (
                            `name`,
                            `login`,
                            `password`,
                            `description`,
                            `mbtitype`
                        ) 
                    VALUES 
                        (
                            :name,
                            :login,
                            :password,
                            :description,
                            :mbtitype
                        )
                ");
                $state->bindValue(':name', $name , PDO::PARAM_STR);
                $state->bindValue(':login', $login ,PDO::PARAM_STR);
                $state->bindValue(':password', $Hashed_password ,PDO::PARAM_STR);
                $state->bindValue(':description', $description ,PDO::PARAM_STR);
                $state->bindValue(':mbtitype', $mbtitype ,PDO::PARAM_STR);
                $state->execute();
                $UserDataResponse = self::PapersPlease($login , $name);
                return json_encode([ 'reseponse' =>'NEW','ID' => $UserDataResponse['id']] , JSON_UNESCAPED_UNICODE);
            }else{ 
                echo json_encode(['response'=> 'DUPLICATE'] , JSON_UNESCAPED_UNICODE);  
        }
        } catch (PDOException $e){ 
            echo $e->getMessage();
        }
    }

    function UpdateUser(
        string $CurrentUserLogin,
        string $CurrentUserName,
        string $CheckCurrentPassword,
        string $NewName,
        string $NewPassword,
        string $NewDescription,
        string $NewMBTITYPE,
    ){
             $user = self::GetUserPassword($CurrentUserLogin, $CurrentUserName);
             $Hashed_NewPassword = password_hash($NewPassword , PASSWORD_DEFAULT);
             if(isset($user)){
                if(password_verify($CheckCurrentPassword , $user['password'])){ 
                    try{
                        $state = self::$connection->prepare("
                            UPDATE 
                                `users` 
                            SET 
                                name=:NewName,
                                password=:NewPassword,
                                description=:NewDescription,
                                mbtitype=:NewMBTITYPE
                            WHERE 
                                login=:CurrentUserLogin && name=:CurrentUserName
                        ");
                        $state->bindValue(':CurrentUserName', $CurrentUserName ,PDO::PARAM_STR);
                        $state->bindValue(':CurrentUserLogin', $CurrentUserLogin ,PDO::PARAM_STR);
                        $state->bindValue(':NewName', $NewName , PDO::PARAM_STR);
                        $state->bindValue(':NewPassword', $Hashed_NewPassword ,PDO::PARAM_STR);
                        $state->bindValue(':NewDescription', $NewDescription ,PDO::PARAM_STR);
                        $state->bindValue(':NewMBTITYPE', $NewMBTITYPE ,PDO::PARAM_STR);
                        $state->execute();
                    } catch(PDOException $e) {
                        echo $e->getMessage(); 
                    }
                }
                else {
                    echo 'Wrong password';
                }
             }else { 
                 echo 'User not found';
                 http_response_code(404);
             }
    }


    function DeleteUser(
        string $CurrentUserLogin,
        string $CurrentUserName,
        string $CheckCurrentPassword
    ){
             $user = self::GetUserPassword($CurrentUserLogin, $CurrentUserName);
        if (password_verify($CheckCurrentPassword , $user['password'])){
            try{
                $state = self::$connection->prepare('DELETE from `users` WHERE  login=:CurrentUserLogin && name=:CurrentUserName');
                $state->bindValue(':CurrentUserLogin', $CurrentUserLogin , PDO::PARAM_STR);
                $state->bindValue(':CurrentUserName', $CurrentUserName ,PDO::PARAM_STR);
                $state->execute();
            }catch(PDOExceptino $e){
                echo $e->getMessage();
            }
        }
        else {
            echo 'Wrong password';
        }

    }

    function Login(string $Login , string $Name ,  string $password){
        $user = self::GetUserPassword($Login , $Name);
        if(isset($user) && password_verify($password , $user['password'])){
            $usersData = self::PapersPlease($Login , $Name);
            $response = [
                'user_exists'=> true,
                'UserData' => $usersData
            ];
            echo json_encode($response , JSON_UNESCAPED_UNICODE ) ;
        }else {
            $response = [
                'user_exists' => false
            ];
            echo json_encode($response , JSON_UNESCAPED_UNICODE ) ;
        }
    }
}
?>




