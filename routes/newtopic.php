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
        <h5 class="mt-sm-2">You are creating a new topic</h5>
        <h6 class="text-muted">You will be able to fully moderate your own topic.</h6>
        <form method="post" action="/do/topic-new">
            <input type="hidden" name="topic-op" value="<?= $user->id ?>">
            <div class="form-group">
                <label for="postTitle">Topic name</label>
                <input type="text" class="form-control" name="topic-name" required>
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Topic description</label>
                <textarea class="form-control" name="topic-desc" rows="4" required></textarea>
                <p class="text-muted">Description and name are important, these are only ways to search your topic.</p>
            </div>
            <button type="submit" class="btn btn-primary">Create Topic</button>
        </form>
    </div>
    <? include_once "include/script.php"; ?>
</body>

</html>