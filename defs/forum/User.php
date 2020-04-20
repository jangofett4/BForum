<?php

include_once "defs/dbdata.php";

class User extends DataPoint
{
    const CURRENT_TABLE = "TblUsers";
    public $id;

    public $email;
    public $password;
    public $name;
    public $surname;
    
    public function IsAdmin(\PDO $con)
    {
        $sql = "SELECT ID FROM TblAdmins WHERE AUserID = ?";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->id]);
        if (count($stmt->fetchAll()) > 0)
            return true;
        return false;
    }
    
    public function Push(\PDO $con)
    {
        $sql = "INSERT INTO " . User::CURRENT_TABLE . " (UPassword, UName, USurname, UEmail) VALUES (?, ?, ?, ?);";
        $con->prepare($sql)->execute([$this->password, $this->name, $this->surname, $this->email]);
        $this->id = $con->lastInsertId();
    }

    public function Fetch(\PDO $con)
    {
        $sql = "SELECT * FROM " . User::CURRENT_TABLE . " WHERE ID = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->id]);
        $data = $stmt->fetch();
        $this->password = $data['UPassword'];
        $this->name = $data['UName'];
        $this->surname = $data['USurname'];
        $this->email = $data['UEmail'];
        return true;
    }

    public function FetchAllWhere(\PDO $con, string $where, ...$args)
    {
        $sql = "SELECT * FROM " . User::CURRENT_TABLE . " $where;";
        $stmt = $con->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public function Update(\PDO $con)
    {
        $sql = "UPDATE " . User::CURRENT_TABLE . " SET UPassword = ?, UName = ?, USurname = ?, UEmail = ? WHERE ID = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->password, $this->name, $this->surname, $this->email, $this->id]);
        return $stmt->rowCount();
    }

    public static function Check(\PDO $con)
    {
        $tbl = User::CURRENT_TABLE;
        $sql = 
<<<SQL
        CREATE TABLE IF NOT EXISTS $tbl (
            `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `UPassword` VARCHAR(32) NOT NULL,
            `UName` VARCHAR(32) NOT NULL,
            `USurname` VARCHAR(32) NOT NULL,
            `UEmail` VARCHAR(48) NOT NULL,
            PRIMARY KEY (`ID`),
            UNIQUE `Email`(`UEmail`)
        ) ENGINE = InnoDB;
SQL;
        $con->prepare($sql)->execute();
    }

    public static function FetchAll(\PDO $con)
    {
        $sql = "SELECT * FROM " . User::CURRENT_TABLE;
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

?>