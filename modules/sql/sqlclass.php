<?php 
namespace sql; 

require_once $base_path . 'modules/settings.php';

class sqlclass { 
    static $connection = null; 
    
     function __construct(){ 
        // Коннектится к БД при вызове класса.
        $connection_string = 'mysql:host=' . \Settings\DB_HOST . ';dbname=' . \Settings\DB_NAME . ';charset=utf8';
        self::$connection = new \PDO($connection_string , \Settings\DB_USERNAME , \Settings\DB_PASSWORD);
    }
    
     function __destruct(){
         //Разрывает соединение при отзыве класса.
        self::$connection = null;
    }

   // Почему мой код не работает ,а тот что прислал мне Иван сработал сразу ? 
   // function GetAllUsers(){ 
   //     try{
   //         $users = self::$connection->query("SELECT id , name ,  login FROM users", \PDO::FETCH_ASSOC);
   //         $usersJSON = json_encode($users ,  JSON_UNESCAPED_UNICODE);
   //         return $usersJSON;
   //     }
   //     catch(PDOException $e){ echo $e->getMessage();}
   // }




    function GetAllUsers() { 
        try {
            $statement = self::$connection->prepare('SELECT id, login , name FROM users');
            $statement->execute();
            $users = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $usersJSON = json_encode($users, JSON_UNESCAPED_UNICODE);
            return $usersJSON;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    } 

    /*
     * PapersPlease -  вспомогательная функция , которая берет из БД loginscreen/users данные пользователя(без пароля) и возвращает ввиде массива. 
     * Просто helper.
     * @param - string CurrentUserLogin
     * @param - string CurrentUserName
     */
    static function PapersPlease(string $CurrentUserLogin , string $CurrentUserName){
        $state = self::$connection->prepare('SELECT id , login , name FROM users WHERE login=:CurrentUserLogin && name=:CurrentUserName ');
        $state->bindValue(':CurrentUserLogin', $CurrentUserLogin , \PDO::PARAM_STR);
        $state->bindValue(':CurrentUserName', $CurrentUserName , \PDO::PARAM_STR);
        $state->execute();
        $result = $state->fetchAll(\PDO::FETCH_ASSOC); 
        return $result[0];
    }

    /*
     * GetUserPassword -  вспомогательная функция , которая берет из БД loginscreen/users данные пользователя и +пароль возвращает ввиде массива. 
     * Просто helper.
     * @param - string CurrentUserLogin
     * @param - string CurrentUserName
     */
    static function GetUserPassword(string $CurrentUserLogin , string $CurrentUserName){
        $state = self::$connection->prepare('SELECT id , login , name , password FROM users WHERE login=:CurrentUserLogin && name=:CurrentUserName');
        $state->bindValue(':CurrentUserLogin', $CurrentUserLogin , \PDO::PARAM_STR);
        $state->bindValue(':CurrentUserName', $CurrentUserName , \PDO::PARAM_STR);
        $state->execute();
        $result = $state->fetchAll(\PDO::FETCH_ASSOC); 
        return $result[0];
    }
    static function DecryptPassword(string $password) { 

    }

    function PickOneUser(string $CurrentUserLogin , string $CurrentUserName) { 
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

    function InsertNewUser(string $name , string $login , string $password){
        $Hashed_password = password_hash($password , PASSWORD_DEFAULT);
        try {
            $state = self::$connection->prepare("INSERT INTO `users`(`name`, `login`, `password`) VALUES (:name,:login,:password)");
            $state->bindValue(':name', $name , \PDO::PARAM_STR);
            $state->bindValue(':login', $login ,\PDO::PARAM_STR);
            $state->bindValue(':password', $Hashed_password ,\PDO::PARAM_STR);
            $state->execute();
        } catch (PDOException $e){ 
            echo $e->getMessage();
        }
    }

    function UpdateUser(string $CurrentUserLogin , string $CurrentUserName, string $CheckCurrentPassword, string $NewName , string $NewPassword){
             $user = self::GetUserPassword($CurrentUserLogin, $CurrentUserName);
             $Hashed_NewPassword = password_hash($NewPassword , PASSWORD_DEFAULT);
             if(isset($user)){
                if(password_verify($CheckCurrentPassword , $user['password'])){ 
                    try{
                        $state = self::$connection->prepare("UPDATE `users` SET name=:NewName, password=:NewPassword 
                            WHERE login=:CurrentUserLogin && name=:CurrentUserName");
                        $state->bindValue(':CurrentUserName', $CurrentUserName ,\PDO::PARAM_STR);
                        $state->bindValue(':CurrentUserLogin', $CurrentUserLogin ,\PDO::PARAM_STR);
                        $state->bindValue(':NewName', $NewName , \PDO::PARAM_STR);
                        $state->bindValue(':NewPassword', $Hashed_NewPassword ,\PDO::PARAM_STR);
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


    function DeleteUser( string $CurrentUserLogin , string $CurrentUserName  , $CheckCurrentPassword){
             $user = self::GetUserPassword($CurrentUserLogin, $CurrentUserName);
        if (password_verify($CheckCurrentPassword , $user['password'])){
            try{
                $state = self::$connection->prepare('DELETE from `users` WHERE  login=:CurrentUserLogin && name=:CurrentUserName');
                $state->bindValue(':CurrentUserLogin', $CurrentUserLogin , \PDO::PARAM_STR);
                $state->bindValue(':CurrentUserName', $CurrentUserName ,\PDO::PARAM_STR);
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
            $response = [
                'response'=> true,
                'password_hash'=> $user['password']
            ];
            echo json_encode($response , JSON_UNESCAPED_UNICODE ) ;
        }else {
            $response = [
                'response' => false
            ];
            echo json_encode($response , JSON_UNESCAPED_UNICODE ) ;
        }
    }
}
?>




