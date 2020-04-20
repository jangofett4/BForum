<?
include_once "defs/forum/Topic.php";
include_once "defs/forum/Post.php";
include_once "defs/forum/User.php";

include_once "defs/default.php";
include_once "include/dbcon.php";

include_once "defs/ssn.php";

$con = DBConnection::open_or_get();

/** @var User $user */
$user = Session::getvnd("user");
$term = $_POST['term'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?= FORUM_BASE_TITLE ?></title>

    <? include_once "include/style.php"; ?>
    <? include_once "include/script-preload.php"; ?>
</head>

<body>
    <? include_once "include/header.php"; ?>
    <div class="container">
        <a href="/"><i class="fa fa-arrow-left"></i> Back to main page</a>
        <h5>Search results</h5>
        <?
        /** @var Topic[] $topics */
        $tmp = new Topic();
        $topics = $tmp->FetchAllWhere($con, "WHERE TName LIKE ? OR TDesc LIKE ?", "%$term%", "%$term%");
        foreach ($topics as $topic) {
            $name = $topic['TName'];
            $desc = $topic['TDesc'];
            $id = $topic['ID'];
            $recent = (new Post())->FetchAllWhere($con, "ORDER BY PTime DESC LIMIT 3");
            echo 
<<<HTML
            <div class="card mt-2">
                <div class="card-header h4">
                    Topic - $name
                </div>
                <div class="card-body">
                    <p class="card-text">$desc</p>
                    <p class="card-text">Some recent posts from $name:</p>
                    <a href="/topic/$id" class="btn btn-primary">Start exploring "$name" topic</a>
                </div>
            </div>
HTML;
        }
        ?>
    </div>
    <? include_once "include/script.php"; ?>
</body>

</html>
