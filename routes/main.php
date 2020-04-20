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
        <? if (Session::get('success-delete-topic')) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                Successfully deleted topic!
            </div>
        <? } ?>
        <? if (Session::get("fresh-login")) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                You are successfully logged in as <?= $user->name . " " . $user->surname ?>
            </div>
        <? } else if (Session::get("fresh-register")) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                You are successfully registered to <?= FORUM_NAME ?> as <?= $user->name . " " . $user->surname ?>
            </div>
        <? } else if (!Session::getnd("user")) { ?>
            <div class="jumbotron mt-2">
                <h1 class="display-4">Welcome to <?= FORUM_NAME ?>!</h1>
                <p class="lead">
                    Welcome, you can start exploring topics, or you can <a href="/login">log in</a> to create new posts.<br>
                </p>
                <hr class="my-4">
                <p>Not registered yet? Join to ask questions quick, get answers quick!</p>
                <a class="btn btn-primary btn-lg" href="/register" role="button">Join</a>
            </div>
        <? } ?>
        <?
        /** @var Topic[] $topics */
        $topics = Topic::FetchAll($con);
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
        <a href="/topic/new"><button class="btn btn-success btn-block my-sm-2"><i class="fa fa-plus"></i></button></a>
    </div>
    <? include_once "include/script.php"; ?>
</body>

</html>