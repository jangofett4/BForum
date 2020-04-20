<?
include_once "xrouter/Router.php";

include_once "defs/forum/Topic.php";
include_once "defs/forum/Post.php";
include_once "defs/forum/User.php";

include_once "defs/default.php";
include_once "include/dbcon.php";

include_once "defs/ssn.php";

$con = DBConnection::open_or_get();

/** @var User $user */
$user = Session::getvnd("user");

if ($user == null) {
    Session::setv('return', Router::get_current_route());
    Router::fastswitch("/login");
}

$topic = new Topic();
$topic->id = $_GET["topic"];

if (!$topic->Fetch($con)) {
    Router::fastswitch('/');
    exit;
}
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
        <? if (Session::get("fresh-login")) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                You are successfully logged in as <?= $user->name . " " . $user->surname ?>
            </div>
        <? } else if (Session::get("fresh-register")) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                You are successfully registered to <?= FORUM_NAME ?> as <?= $user->name . " " . $user->surname ?>
            </div>
        <? } ?>
        <h5 class="mt-sm-2">You are creating a new post in <?= $topic->name ?></h5>
        <div class="row">
            <div class="col py-sm-2">
                <form method="post" action="/do/post-new">
                    <input type="hidden" name="post-op" value="<?= $user->id ?>">
                    <input type="hidden" name="post-topic" value="<?= $topic->id ?>">
                    <div class="form-group">
                        <label for="postTitle">Post title</label>
                        <input type="text" class="form-control" name="post-title" required>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Post content</label>
                        <textarea class="form-control" name="post-content" rows="10" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Create</button>
                </form>
            </div>
            <div class="col-sm-3 py-sm-2">
                <div class="card sticky-top mt-2 topic-sticky-top-margin">
                    <div class="card-header">
                        You are creating a new post
                    </div>
                    <div class="p-2">
                        Markdown is enabled (see this <a href="https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet">link</a> for information)
                    </div>
                </div>
            </div>
        </div>
    </div>
    <? include_once "include/script.php"; ?>
</body>

</html>