<?

include_once "defs/forum/User.php";
include_once "defs/forum/Topic.php";

include_once "defs/dbdata.php";

class TopicPerms extends DataPoint
{
    const CURRENT_TABLE = "TblTopicPerms";
    public $id;

    public $topic;
    public $user;
    public $perms;

    public function CanEditTopic()
    {
        return substr($this->perms, 0, 1) == "1";
    }

    public function CanRemoveTopic()
    {
        return substr($this->perms, 1, 1) == "1";
    }

    public function CanPinPost()
    {
        return substr($this->perms, 2, 1) == "1";
    }

    public function CanRemovePost()
    {
        return substr($this->perms, 3, 1) == "1";
    }
    
    public function Push(\PDO $con)
    {
        $sql = "INSERT INTO " . TopicPerms::CURRENT_TABLE . " (PUser, PTopic, PPerms) VALUES (?, ?, ?);";
        $con->prepare($sql)->execute([$this->user, $this->topic, $this->perms]);
        $this->id = $con->lastInsertId();
    }

    public function FetchFromUserTopic(\PDO $con)
    {
        $sql = "SELECT * FROM " . TopicPerms::CURRENT_TABLE . " WHERE PUser = ? AND PTopic = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->user, $this->topic]);
        $data = $stmt->fetch();
        if ($data == false)
            return false;
        $this->id = $data['ID'];
        $this->perms = $data['PPerms'];
        return true;
    }

    public function Fetch(\PDO $con)
    {
        $sql = "SELECT * FROM " . TopicPerms::CURRENT_TABLE . " WHERE ID = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->id]);
        $data = $stmt->fetch();
        if ($data === false)
            return false;
        $this->user = $data['PUser'];
        $this->topic = $data['PTopic'];
        $this->perms = $data['PPerms'];
        return true;
    }

    public function FetchAllWhere(\PDO $con, string $where, ...$args)
    {
        $sql = "SELECT * FROM " . TopicPerms::CURRENT_TABLE . " $where;";
        $stmt = $con->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public function Update(\PDO $con)
    {
        $sql = "UPDATE " . TopicPerms::CURRENT_TABLE . " SET PUser = ?, PTopic = ?, PPerms = ? WHERE ID = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->user, $this->topic, $this->perms, $this->id]);
        return $stmt->rowCount();
    }

    public static function Check(\PDO $con)
    {
        $tbl = TopicPerms::CURRENT_TABLE;
        $sql =
            <<<SQL
        CREATE TABLE IF NOT EXISTS $tbl (
            `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `PUser` INT UNSIGNED NOT NULL,
            `PTopic` INT UNSIGNED NOT NULL,
            `PPerms` VARCHAR(4) NOT NULL,
            PRIMARY KEY (`ID`),
            FOREIGN KEY (PUser) REFERENCES TblUsers(ID),
            FOREIGN KEY (PTopic) REFERENCES TblTopics(ID)
        ) ENGINE = InnoDB;
SQL;
        $con->prepare($sql)->execute();
    }

    public static function FetchAll(\PDO $con)
    {
        $sql = "SELECT * FROM " . TopicPerms::CURRENT_TABLE;
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
