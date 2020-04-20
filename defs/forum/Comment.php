<?

include_once "defs/forum/Post.php";
include_once "defs/dbdata.php";

class Comment extends DataPoint
{
    const CURRENT_TABLE = "TblComments";
    public $id;

    public $content;
    public $post;
    public $op;

    public $time;

    public function GetOp(\PDO $con): \User
    {
        $usr = new User();
        $usr->id = $this->op;
        $usr->Fetch($con);
        return $usr;
    }

    public function GetPost(\PDO $con): \Post
    {
        $post = new Post();
        $post->id = $this->post;
        $post->Fetch($con);
        return $post;
    }

    public function Push(\PDO $con)
    {
        $sql = "INSERT INTO " . Comment::CURRENT_TABLE . " (CContent, CPost, COp) VALUES (?, ?, ?);";
        $con->prepare($sql)->execute([$this->content, $this->post, $this->op]);
        $this->id = $con->lastInsertId();
    }

    public function Fetch(\PDO $con)
    {
        $sql = "SELECT * FROM " . Comment::CURRENT_TABLE . " WHERE ID = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->id]);
        $data = $stmt->fetch();
        $this->content = $data['CContent'];
        $this->op = $data['COp'];
        $this->post = $data['CPost'];
        $this->time = $data['CTime'];
        return true;
    }

    public function FetchAllWhere(\PDO $con, string $where, ...$args)
    {
        $sql = "SELECT * FROM " . Comment::CURRENT_TABLE . " $where;";
        $stmt = $con->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public function Update(\PDO $con)
    {
        $sql = "UPDATE " . Comment::CURRENT_TABLE . " SET CContent = ?, COp = ? WHERE ID = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->content, $this->op, $this->id]);
        return $stmt->rowCount();
    }

    public static function Check(\PDO $con)
    {
        $tbl = Comment::CURRENT_TABLE;
        $sql = 
<<<SQL
        CREATE TABLE IF NOT EXISTS $tbl (
            `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `CContent` VARCHAR(2048) NOT NULL,
            `COp` INT UNSIGNED NOT NULL,
            `CPost` INT UNSIGNED NOT NULL,
            `CTime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (`ID`),
            FOREIGN KEY (COp) REFERENCES TblUsers(ID),
            FOREIGN KEY (CPost) REFERENCES TblPosts(ID)
        ) ENGINE = InnoDB;
SQL;
        $con->prepare($sql)->execute();
    }

    public static function FetchAll(\PDO $con)
    {
        $sql = "SELECT * FROM " . Comment::CURRENT_TABLE;
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
