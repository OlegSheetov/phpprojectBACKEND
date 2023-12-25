<?php
namespace sql; 
use \PDO;
// GetUserPassword взята из класса baseclass

class CommentClass extends baseclass { 

    function GetAllComments(
        string $AnquetteID
    ){
        try{
            $state = self::$connection->prepare("
                    SELECT
                        `CommentID`,
                        (
                            SELECT
                                 name 
                            FROM 
                                users 
                            WHERE 
                                users.id = comments.AuthorName
                        )
                        AS 
                         AuthorName,
                        `commentBody`,
                        `timestamp`,
                        (
                            SELECT 
                                mbtitype
                            FROM
                                users
                            WHERE
                                users.id = comments.AuthorName
                        )
                        AS 
                            mbtitype
                    FROM
                        `comments`
                    WHERE
                        `AnquetteID` = :AnquetteID
               ");
            $state->bindValue(':AnquetteID', $AnquetteID, PDO::PARAM_STR);
            $state->execute();
            $result = $state->fetchAll(PDO::FETCH_ASSOC);
            $response = json_encode($result , JSON_UNESCAPED_UNICODE);
            echo  $response;
        }catch(PDOException $e) {
            echo $e->getMessage();
        }
    }

    function CreateComment(
        string $AnquetteID, 
        string $AuthorName, 
        string $CommentBody,
    ){
        try{
            $state = self::$connection->prepare("
                    INSERT INTO `comments`(
                        `AnquetteID`,
                        `AuthorName`,
                        `commentBody`
                    )
                    VALUES(
                        :AnquetteID,
                        (SELECT id from users where users.name = :AuthorName),
                        :CommentBody
    
                    )
               ");
            $state->bindValue(':AnquetteID', $AnquetteID, PDO::PARAM_STR);
            $state->bindValue(':AuthorName', $AuthorName, PDO::PARAM_STR);
            $state->bindValue(':CommentBody', $CommentBody , PDO::PARAM_STR);
            $state->execute();
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    function DeleteComment(
        string $AnquetteID, 
        string $CommentID, 
        string $AuthorLogin, 
        string $AuthorName,
        string $AuthorPassword
    ){
       $CurrentUser = self::GetUserPassword( $AuthorLogin, $AuthorName);
        try{
            if(password_verify($AuthorPassword, $CurrentUser['password'])){
                $state = self::$connection->prepare("
                        DELETE
                        FROM
                            `comments`
                        WHERE
                            AnquetteID=:AnquetteID AND CommentID=:CommentID AND 
                        (
                            SELECT 
                                id 
                            FROM 
                                users
                            WHERE 
                                users.login = :AuthorLogin 
                        ) 
                   ");
                $state->bindValue(':AnquetteID', $AnquetteID, PDO::PARAM_STR);
                $state->bindValue(':CommentID', $CommentID, PDO::PARAM_STR);
                $state->bindValue(':AuthorLogin', $AuthorLogin, PDO::PARAM_STR);
                $state->execute();
            }else{ 
                echo json_encode(["response"=>'Wrong Password'], JSON_UNESCAPED_UNICODE);
            }
        }catch (PDOException $e) { 
            echo $e->getMessage();
        }
    }


    function UpdateComment(
        string $AnquetteID, 
        string $CommentID, 
        string $AuthorLogin,
        string $AuthorName, 
        string $AuthorPassword,
        string $CommentBody
    ){ 
        try{
            $CurrentUser = self:: GetUserPassword($AuthorLogin, $AuthorName);
            if(password_verify($AuthorPassword, $CurrentUser['password'])){
                $state =  self::$connection->prepare("
                    UPDATE
                        `comments`
                    SET
                        `commentBody` = :CommentBody 
                    WHERE
                        CommentID = :CommentID AND AnquetteID = :AnquetteID
                ");
                $state->bindValue(':CommentBody', $CommentBody, PDO::PARAM_STR);
                $state->bindValue(':CommentID', $CommentID, PDO::PARAM_STR);
                $state->bindValue(':AnquetteID', $AnquetteID , PDO::PARAM_STR);
                $state->execute();
            }else{
                echo json_encode(['response'=>'Wrong Password']);
            }
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }
}
?>
