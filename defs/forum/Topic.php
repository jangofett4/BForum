<?

include_once "defs/dbdata.php";

class Topic extends DataPoint
{
    const CURRENT_TABLE = "TblTopics";
    public $id;

    public $name;
    public $desc;

    public $creator;

    public function GetCreator(\PDO $con)
    {
        $usr = new User();
        $usr->id = $this->creator;
        $usr->Fetch($con);
        return $usr;
    }

    public function Push(\PDO $con)
    {
        $sql = "INSERT INTO " . Topic::CURRENT_TABLE . " (TName, TDesc, TCreator) VALUES (?, ?, ?);";
        $con->prepare($sql)->execute([$this->name, $this->desc, $this->creator]);
        $this->id = $con->lastInsertId();
    }

    public function Fetch(\PDO $con)
    {
        $sql = "SELECT * FROM " . Topic::CURRENT_TABLE . " WHERE ID = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->id]);
        $data = $stmt->fetch();
        if ($data === false)
            return false;
        $this->name = $data['TName'];
        $this->desc = $data['TDesc'];
        $this->creator = $data['TCreator'];
        return true;
    }

    public function FetchAllWhere(\PDO $con, string $where, ...$args)
    {
        $sql = "SELECT * FROM " . Topic::CURRENT_TABLE . " $where;";
        $stmt = $con->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public function Update(\PDO $con)
    {
        $sql = "UPDATE " . Topic::CURRENT_TABLE . " SET TName = ?, TDesc = ?, TCreator = ? WHERE ID = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->name, $this->desc, $this->creator, $this->id]);
        return $stmt->rowCount();
    }

    public static function Check(\PDO $con)
    {
        $tbl = Topic::CURRENT_TABLE;
        $sql =
            <<<SQL
        CREATE TABLE IF NOT EXISTS $tbl (
            `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `TName` VARCHAR(32) NOT NULL,
            `TDesc` VARCHAR(128) NOT NULL,
            `TCreator` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`ID`),
            FOREIGN KEY (TCreator) REFERENCES TblUsers(ID)
        ) ENGINE = InnoDB;
SQL;
        $con->prepare($sql)->execute();
    }

    public static function FetchAll(\PDO $con)
    {
        $sql = "SELECT * FROM " . Topic::CURRENT_TABLE;
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
