<?

include_once "defs/forum/Topic.php";
include_once "defs/forum/Post.php";

include_once "defs/dbdata.php";

class PostFlags extends DataPoint
{
    const CURRENT_TABLE = "TblPostFlags";
    public $id;
    
    public $post;
    public $isPinned;
    public $isLocked;
    public $order;

    public function GetPost(\PDO $con): \Post
    {
        $post = new Post();
        $post->id = $this->post;
        $post->Fetch($con);
        return $post;
    }

    public function Push(\PDO $con)
    {
        $sql = "INSERT INTO " . PostFlags::CURRENT_TABLE . " (FPost, FPinned, FOrder, FLocked) VALUES (?, ?, ?, ?);";
        $con->prepare($sql)->execute([$this->post, $this->isPinned, $this->order, $this->isLocked]);
        $this->id = $con->lastInsertId();
    }

    public function Fetch(\PDO $con)
    {
        $sql = "SELECT * FROM " . PostFlags::CURRENT_TABLE . " WHERE ID = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->id]);
        $data = $stmt->fetch();
        $this->post = $data['FPost'];
        $this->isPinned = $data['FPinned'];
        $this->order = $data['FOrder'];
        $this->isLocked = $data['FLocked'];
        return true;
    }

    public function FetchAllWhere(\PDO $con, string $where, ...$args)
    {
        $sql = "SELECT * FROM " . PostFlags::CURRENT_TABLE . " $where;";
        $stmt = $con->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public function Update(\PDO $con)
    {
        $sql = "UPDATE " . PostFlags::CURRENT_TABLE . " SET FPost = ?, FPinned = ?, FOrder = ?, FLocked = ? WHERE ID = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->post, $this->isPinned, $this->order, $this->isLocked, $this->id]);
        return $stmt->rowCount();
    }

    public static function Check(\PDO $con)
    {
        $tbl = PostFlags::CURRENT_TABLE;
        $sql = 
<<<SQL
        CREATE TABLE IF NOT EXISTS $tbl (
            `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `FPost` INT UNSIGNED NOT NULL,
            `FPinned` BOOLEAN NOT NULL,
            `FOrder` INT UNSIGNED NOT NULL,
            `FLocked` BOOLEAN NOT NULL,
            PRIMARY KEY (`ID`),
            FOREIGN KEY (FPost) REFERENCES TblPosts(ID)
        ) ENGINE = InnoDB;
SQL;
        $con->prepare($sql)->execute();
    }

    public static function FetchAll(\PDO $con)
    {
        $sql = "SELECT * FROM " . PostFlags::CURRENT_TABLE;
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
