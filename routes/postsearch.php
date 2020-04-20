<?
include_once 'xrouter/Router.php';

include_once "defs/forum/Topic.php";
include_once "defs/forum/Post.php";
include_once "defs/forum/User.php";

include_once "defs/default.php";

include_once "include/dbcon.php";
include_once "include/helper.php";

include_once "defs/ssn.php";

$con = DBConnection::open_or_get();

/** @var User $user */
$user = Session::getvnd("user");

$topic = new Topic();
$topic->id = $_POST['topic'];
if (!$topic->Fetch($con))
    Router::fastswitch('/');

$term = $_POST['term'];

$perms = new TopicPerms();
$perms->perms = "0000";
if ($user != null) {
    $perms->topic = $topic->id;
    $perms->user = $user->id;
    if (!$perms->FetchFromUserTopic($con))
        $perms->perms = "0000";
}

$creator = $topic->GetCreator($con);

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
        <a href="/topic/<?= $topic->id ?>"><i class="fa fa-arrow-left"></i> Back to /t/<?= $topic->name ?></a>
        <h5>Search results</h5>
        <?
        $tmp = new Post();
        $posts = $tmp->FetchAllWhere($con, "WHERE PTopic = ? AND (PTitle LIKE ? OR PContent LIKE ?)", $topic->id, "%$term%", "%$term%");
        foreach ($posts as $tmp) { 
            $post = new Post();
            $post->title = $tmp['PTitle'];
            $post->content = $tmp['PContent'];
            $post->op = $tmp['POp'];
            $trunc = truncate($post->content, 100);
            $op = $post->GetOp($con);
            
            ?>
            <a href="/post/<?= $post->id ?>" class="text-decoration-none">
                <div class="card mt-sm-2">
                    <div class="card-body">
                        <h6 class="card-title">Post: <?= $post->title ?></h6>
                        <p class="card-text"><?= $trunc ?></p>
                        <p class="text-muted">Posted by <?= $op->name . ' ' . $op->surname ?></p>
                        <div class="dropdown dropleft show text-right">
                            <a href="javascript:;" role="button" id="postDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="btn fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu">
                                <? if ($perms->CanPinPost()) { ?>
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#unpin-post-modal" onclick="p = <?= $post->id ?>"><i class="fa fa-map-pin"></i> Unpin post</a>
                                <? } ?>
                                <? if ($perms->CanRemovePost()) { ?>
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#delete-post-modal" onclick="p = <?= $post->id ?>"><i class="fa fa-trash"></i> Delete post</a>
                                <? } ?>
                                <a class="dropdown-item" href="/user/<?= $op->id ?>"><i class="fa fa-user"></i> User profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        <? } ?>
    </div>
    <? include_once "include/script.php"; ?>
</body>

</html>