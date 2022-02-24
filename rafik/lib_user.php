<?php
    class User{
        private $pdo = null;
        private $stmt = null;
        public $error = "";
        /* [THE BASICS] */
    function __construct(){
        try {
            $this->pdo = new PDO(
                "mysql:host=".MYSQL_HOST.";dbname=".MYSQL_DB.";charset=".MYSQL_CHAR,MYSQL_USER, MYSQL_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (Exception $ex) { die($ex->getMessage()); }
    }
    function __destruct(){
        if ($this->stmt!==null) { $this->stmt = null; }
        if ($this->pdo!==null) { $this->pdo = null; }
    }
    function query($sql, $cond=[]){
        try {
            $this->stmt = $this->pdo->prepare($sql);
            $this->stmt->execute($cond);
        } catch (Exception $ex) { 
            $this->error = $ex->getMessage();
            return false;
        }
        $this->stmt = null;
        return true;
    }
    /* [GET USERS] */
    function getAll(){
        $this->stmt = $this->pdo->prepare("SELECT * FROM `users`");
        $this->stmt->execute();
        $users = $this->stmt->fetchAll();
        return count($users)==0 ? false : $users;
    }
    function getEmail($email){
        $this->stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `email`=?");
        $cond = [$email];
        $this->stmt->execute($cond);
        $user = $this->stmt->fetchAll();
        return count($user)==0 ? false : $user[0];
    }
    function getID($id){
        $this->stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `id`=?");
        $cond = [$id];
        $this->stmt->execute($cond);
        $user = $this->stmt->fetchAll();
        return count($user)==0 ? false : $user[0];
    }
    /* [SET & DELETE USERS] */
    function create($name, $email, $password){
        return $this->query(
            "INSERT INTO `users` (`name`, `email`, `password`) VALUES (?,?,?)",
            [$name, $email, openssl_encrypt($password, "AES-128-ECB", SECRET_KEY)]
        );
    }
    function update($name, $email, $password="", $id){
        $q = "UPDATE `users` SET `name`=?, `email`=?";
        $cond = [$name, $email];
        if ($password!="") {
            $q .= ", `password`=?";
            $cond[] = openssl_encrypt($password, "AES-128-ECB", SECRET_KEY);
        }
        $q .= " WHERE `id`=?";
        $cond[] = $id;
        return $this->query($q, $cond);
    }
    function delete($id){
        return $this->query(
        "DELETE FROM `users` WHERE `id`=?",
        [$id]
        );
    }
    /* [LOGIN] */
        function login($email, $password){
            $user = $this->getEmail($email);
            if ($user==false) { return false; }
            return openssl_decrypt($user['password'], 
            "AES-128-ECB", SECRET_KEY) == $password ? $user : false ;
        }
    }
?>
