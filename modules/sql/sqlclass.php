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
     *
     * PapersPlease - функция , которая берет из БД loginscreen/users данные пользователя и возвращает ввиде массива. 
     * Просто helper.
     * @param - string CurrentUserID
     * @param - string CurrentUserLogin
     * @param - string CurrentUserName
     */
    static function PapersPlease(string $CurrentUserLogin , string $CurrentUserName){
        $state = self::$connection->prepare('SELECT id , login , name FROM users WHERE login=:CurrentUserLogin && name=:CurrentUserName ');
        $state->bindValue(':CurrentUserLogin', $CurrentUserLogin , \PDO::PARAM_STR);
        $state->bindValue(':CurrentUserName', $CurrentUserName , \PDO::PARAM_STR);
        $state->execute();
        return $state->fetchAll(\PDO::FETCH_ASSOC);
    }

    function PickOneUser(string $CurrentUserLogin , string $CurrentUserName) { 
        try{ 
           // $state = self::$connection->prepare('SELECT id , login , name FROM users WHERE id=:CurrentUserID ||
           //     login=:CurrentUserLogin || name=:CurrentUserName ');
           // $state->bindValue(':CurrentUserID', $CurrentUserID , \PDO::PARAM_STR);
           // $state->bindValue(':CurrentUserLogin', $CurrentUserLogin , \PDO::PARAM_STR);
           // $state->bindValue(':CurrentUserName', $CurrentUserName , \PDO::PARAM_STR);
           // $state->execute();
            $user = self::PapersPlease($CurrentUserLogin , $CurrentUserName);
            $userJSON = json_encode($user, JSON_UNESCAPED_UNICODE);
            return $userJSON;
        }catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    function InsertNewUser(string $name , string $login , string $password){
        try {
            $state = self::$connection->prepare("INSERT INTO `users`(`name`, `login`, `password`) VALUES (:name,:login,:password)");
            $state->bindValue(':name', $name , \PDO::PARAM_STR);
            $state->bindValue(':login', $login ,\PDO::PARAM_STR);
            $state->bindValue(':password', $password ,\PDO::PARAM_STR);
            $state->execute();
        } catch (PDOException $e) { 
            echo $e->getMessage();
        }
    }

    function UpdateUser( string $CurrentUserLogin , string $CurrentUserName , string $NewName , string $NewPassword){
        try{
            $state = self::$connection->prepare("UPDATE `users` SET name=:NewName, password=:NewPassword 
                WHERE login=:CurrentUserLogin && name=:CurrentUserName");
            $state->bindValue(':CurrentUserName', $CurrentUserName ,\PDO::PARAM_STR);
            $state->bindValue(':CurrentUserLogin', $CurrentUserLogin ,\PDO::PARAM_STR);
            $state->bindValue(':NewName', $NewName , \PDO::PARAM_STR);
            $state->bindValue(':NewPassword', $NewPassword ,\PDO::PARAM_STR);
            $state->execute();
        } catch(PDOException $e) {
            echo $e->getMessage(); 
        }
    }


    function DeleteUser( string $CurrentUserLogin , string $CurrentUserName ){
        try{
            $state = self::$connection->prepare('DELETE from `users` WHERE  login=:CurrentUserLogin && name=:CurrentUserName');
            $state->bindValue(':CurrentUserLogin', $CurrentUserLogin , \PDO::PARAM_STR);
            $state->bindValue(':CurrentUserName', $CurrentUserName ,\PDO::PARAM_STR);
            $state->execute();
        }catch(PDOExceptino $e){
            echo $e->getMessage();
        }

    }
}
?>




