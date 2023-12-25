<?php
namespace sql;

require_once $base_path . 'modules/settings.php';

class baseclass {
    static $connection = null; 
    
     function __construct(){ 
        // Коннектится к БД при вызове класса.
         $connection_string = 
            'mysql:host=' . \Settings\DB_HOST . ';dbname=' . \Settings\DB_NAME . ';charset=utf8';
         self::$connection = 
            new \PDO($connection_string , \Settings\DB_USERNAME , \Settings\DB_PASSWORD);
    }
    
     function __destruct(){
         //Разрывает соединение при отзыве класса.
        self::$connection = null;
    }


    /*
     * PapersPlease -  вспомогательная функция , которая берет из БД Anquetee/users данные пользователя(без пароля) и возвращает ввиде массива. 
     * Просто helper.
     * @param - string CurrentUserLogin
     * @param - string CurrentUserName
     */
    static function PapersPlease(
        string $CurrentUserLogin,
        string $CurrentUserName
    ){
            $state = self::$connection->prepare('
                SELECT 
                    id, login,
                    name,
                    description,
                    mbtitype
                FROM 
                    users
                WHERE 
                    login=:CurrentUserLogin && name=:CurrentUserName
             ');
            $state->bindValue(':CurrentUserLogin', $CurrentUserLogin , \PDO::PARAM_STR);
            $state->bindValue(':CurrentUserName', $CurrentUserName , \PDO::PARAM_STR);
            $state->execute();
            $result = $state->fetchAll(\PDO::FETCH_ASSOC); 
            return $result[0];
        }

   /*
     * GetUserPassword -  вспомогательная функция , которая берет из БД Anquette/users данные пользователя и +пароль возвращает ввиде массива. 
     * Просто helper.
     * @param - string CurrentUserLogin
     * @param - string CurrentUserName
     */
    static function GetUserPassword(
        string $CurrentUserLogin,
        string $CurrentUserName
    ){
            $state = self::$connection->prepare('SELECT id , login , name , password FROM users WHERE login=:CurrentUserLogin && name=:CurrentUserName');
            $state->bindValue(':CurrentUserLogin', $CurrentUserLogin , \PDO::PARAM_STR);
            $state->bindValue(':CurrentUserName', $CurrentUserName , \PDO::PARAM_STR);
            $state->execute();
            $result = $state->fetchAll(\PDO::FETCH_ASSOC); 
            return $result[0];
        }

    static function IsUserExist(
        string $Login,
        string $Name
    ){ 
        $state = self::$connection->prepare('
                SELECT
                      login , name
                FROM 
                     users
                WHERE 
                    login=:Login && name=:Name
            ');
            $state->bindValue(':Login', $Login , \PDO::PARAM_STR);
            $state->bindValue(':Name', $Name , \PDO::PARAM_STR);
            $state->execute();
            $result = $state->fetchAll(\PDO::FETCH_ASSOC);
            return isset($result[0]);
    }



}

?>
