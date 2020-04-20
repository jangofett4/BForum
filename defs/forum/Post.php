<?

include_once "defs/forum/PostFlags.php";
include_once "defs/forum/Topic.php";
include_once "defs/forum/User.php";
include_once "defs/forum/Comment.php";
include_once "defs/dbdata.php";

class Post extends DataPoint
{
    const CURRENT_TABLE = "TblPosts";
    public $id;

    public $title;
    public $content;
    public $op;
    public $topic;
    /** @var DateTime $time */
    public $time;
    /** @var DateTime $updated */
    public $updated;
    public $edits;

    public function GetFlags(\PDO $con)
    {
        $flags = new PostFlags();
        $fetch = $flags->FetchAllWhere($con, "WHERE FPost = ?", $this->id);
        if (count($fetch) >= 1)
        {
            $f = $fetch[0];
            $flags->id = $f["ID"];
            $flags->post = $f["FPost"];
            $flags->isPinned = $f["FPinned"];
            $flags->isLocked = $f["FLocked"];
            $flags->order = $f["FOrder"];
            return $flags;
        }
        return false;
    }

    public function GetOp(\PDO $con): \User
    {
        $usr = new User();
        $usr->id = $this->op;
        $usr->Fetch($con);
        return $usr;
    }

    public function GetTopic(\PDO $con): \Topic
    {
        $topic = new Topic();
        $topic->id = $this->topic;
        $topic->Fetch($con);
        return $topic;
    }

    public function GetComments(\PDO $con)
    {
        $cmtlist = new Comment();
        $comments = $cmtlist->FetchAllWhere($con, "WHERE CPost = ?", $this->id);
        $ret = array();
        foreach ($comments as $comment)
        {
            $cmt = new Comment();
            $cmt->id = $comment['ID'];
            $cmt->content = $comment['CContent'];
            $cmt->op = $comment['COp'];
            $cmt->post = $comment['CPost'];
            $cmt->time = $comment['CTime'];
            array_push($ret, $cmt);
        }
        return $ret;
    }

    public function GetCommentCount(\PDO $con)
    {
        $tbl = Comment::CURRENT_TABLE;
        $sql = "SELECT COUNT(ID) FROM " . Comment::CURRENT_TABLE . " WHERE CPost=?";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->id]);
        $data = $stmt->fetch();
        return $data['COUNT(ID)'];
    }

    public function Push(\PDO $con)
    {
        $sql = "INSERT INTO " . Post::CURRENT_TABLE . " (PTitle, PContent, POp, PTopic, PEdits) VALUES (?, ?, ?, ?, ?);";
        $con->prepare($sql)->execute([$this->title, $this->content, $this->op, $this->topic, 0]);
        $this->id = $con->lastInsertId();
    }

    public function Fetch(\PDO $con)
    {
        $sql = "SELECT * FROM " . Post::CURRENT_TABLE . " WHERE ID = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->id]);
        $data = $stmt->fetch();
        $this->title = $data['PTitle'];
        $this->content = $data['PContent'];
        $this->topic = $data['PTopic'];
        $this->op = $data['POp'];
        $this->time = new DateTime($data['PTime']);
        $this->updated = new DateTime($data['PUpdated']);
        $this->edits = $data['PEdits'];
        return true;
    }

    public function FetchAllWhere(\PDO $con, string $where, ...$args)
    {
        $sql = "SELECT * FROM " . Post::CURRENT_TABLE . " $where;";
        $stmt = $con->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public function Update(\PDO $con)
    {
        $sql = "UPDATE " . Post::CURRENT_TABLE . " SET PTitle = ?, PContent = ?, POp = ?, PTopic = ?, PUpdated = ?, PEdits = ? WHERE ID = ?;";
        $stmt = $con->prepare($sql);
        $stmt->execute([$this->title, $this->content, $this->op, $this->topic, $this->updated->format("Y-m-d H:i:s"), $this->edits, $this->id]);
        return $stmt->rowCount();
    }

    public static function Check(\PDO $con)
    {
        $tbl = Post::CURRENT_TABLE;
        $sql =
<<<SQL
        CREATE TABLE IF NOT EXISTS $tbl (
            `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `PTitle` VARCHAR(64) NOT NULL,
            `PContent` VARCHAR(4096) NOT NULL,
            `POp` INT UNSIGNED NOT NULL,
            `PTopic` INT UNSIGNED NOT NULL,
            `PTime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            `PUpdated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            `PEdits` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`ID`),
            FOREIGN KEY (POp) REFERENCES TblUsers(ID),
            FOREIGN KEY (PTopic) REFERENCES TblTopics(ID)
        ) ENGINE = InnoDB;
SQL;
        $con->prepare($sql)->execute();
    }

    public static function FetchAll(\PDO $con)
    {
        $sql = "SELECT * FROM " . Post::CURRENT_TABLE;
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
