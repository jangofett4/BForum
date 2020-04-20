<?

include_once "defs/default.php";
include_once "include/dbcon.php";
include_once "include/helper.php";

include_once "defs/forum/Topic.php";
include_once "defs/forum/Post.php";
include_once "defs/forum/User.php";
include_once "defs/forum/TopicPerms.php";
include_once "defs/ssn.php";

$con = DBConnection::open_or_get();

/** @var User $user */
$user = Session::getvnd("user");

$topic = new Topic();
$topic->id = $_GET["topic"];

if (!$topic->Fetch($con)) {
    Router::fastswitch('/');
    exit;
}

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

    <title><?= FORUM_BASE_TITLE . " - " . $topic->name ?></title>

    <? include_once "include/style.php"; ?>
    <? include_once "include/script-preload.php"; ?>
    <script>
        var p = -1;
    </script>
</head>

<body>
    <? include_once "include/header.php"; ?>
    <div class="container">
        <? if (Session::get("fresh-login")) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                You are successfully logged in as <?= $user->name . " " . $user->surname ?>
            </div>
        <? } ?>
        <? if (Session::get("new-post")) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                You created a new post!
            </div>
        <? } ?>
        <? if (Session::get('success-delete-post')) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                Post deleted successfully!
            </div>
        <? } ?>
        <? if (Session::get('success-unpin-post')) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                Post unpinned!
            </div>
        <? } ?>
        <? if (Session::get('success-pin-post')) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                Post pinned!
            </div>
        <? } ?>
        <? if (Session::get('success-new-topic')) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                You created a new topic!
            </div>
        <? } ?>
        <? if ($user != null) { ?>
            <!-- user specific code -->
        <? } ?>
        <div class="row">
            <div class="col">
                <form method="post" action="/topic/<?= $topic->id ?>/search" class="mt-sm-2">
                    <input type="hidden" name="topic" value="<?= $topic->id ?>">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <input type="text" class="form-control" name="term" placeholder="Search posts" required>
                            </div>
                        </div>
                        <div class="col-sm-0 mr-sm-3">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </form>
                <?
                /** @var Topic[] $topics */
                $posts = (new Post)->FetchAllWhere($con, "WHERE PTopic = ? ORDER BY PTime DESC", $topic->id);

                /** @var Post[] $pinned */
                $pinned = array();
                $normal = array();

                foreach ($posts as $postarr) {
                    $post = new Post();
                    $post->id = $postarr['ID'];
                    $post->title = $postarr['PTitle'];
                    $post->op = $postarr['POp'];
                    $post->content = $postarr['PContent'];
                    $post->time = new DateTime($postarr['PTime']);

                    $trunc = truncate($post->content, 100);
                    $op = $post->GetOp($con);
                    $time = $post->time->format('d-m-Y H:i');
                    $flags = $post->GetFlags($con);

                    if ($flags !== false) {
                        /** @var PostFlags $flags */
                        if ($flags->isPinned) {
                            $pinned[$flags->order] = [$post, $trunc, $op, $time, $flags];
                            continue;
                        }
                    }

                    array_push($normal, [$post, $trunc, $op, $time, $flags]);
                }

                // Administrator pinned posts
                if (count($pinned) > 0) {
                    // Sort by order
                    ksort($pinned);
                    // Print 'Pinned posts' header
                    echo "<h6>Pinned posts on $topic->name topic</h6>";

                    foreach ($pinned as $order => $postarr) {
                        $post = $postarr[0];
                        $trunc = $postarr[1];
                        $op = $postarr[2];
                        $time = $postarr[3];
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
                    <?
                    }
                }

                // Normal posts
                if (count($normal) == 0) {
                    echo "<h6 class=\"mt-sm-2\">Looks like there is no posts in $topic->name, you can start <a href=\"/post/new/$topic->id\">creating</a>!</h6>";
                } else {
                    echo "<h6 class=\"mt-sm-2\">Latest posts on $topic->name topic</h6>";

                    foreach ($normal as $postarr) {
                        $post = $postarr[0];
                        $trunc = $postarr[1];
                        $op = $postarr[2];
                        $time = $postarr[3];
                        $replies = $post->GetCommentCount($con);
                    ?>
                        <a href="/post/<?= $post->id ?>" class="text-decoration-none">
                            <div class="card my-sm-1">
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
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#pin-post-modal" onclick="p = <?= $post->id ?>"><i class="fa fa-map-pin"></i> Pin post</a>
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
                <?
                    }
                }
                ?>
            </div>
            <div class="col-sm-3 py-2">
                <div class="sticky-top topic-sticky-top-margin py-sm-2">
                    <div class="card">
                        <div class="card-header">
                            About /t/<?= $topic->name ?>
                        </div>
                        <div class="p-2">
                            <?= $topic->desc ?>
                            <br>
                            <b>Owner: </b> <a href="/user/<?= $creator->id ?>"><?= $creator->name . ' ' . $creator->surname ?></a>
                        </div>
                    </div>
                    <? if ($user != null) { ?>
                        <div class="card mt-sm-2">
                            <div class="card-header">
                                Your permissions
                            </div>
                            <div class="p-2">
                                You <?= $perms->CanEditTopic() ? '<span class="text-success">can</span>' : '<span class="text-danger">cannot</span>' ?> edit topic properties <br>
                                You <?= $perms->CanRemoveTopic() ? '<span class="text-success">can</span>' : '<span class="text-danger">cannot</span>' ?> remove this topic <br>
                                You <?= $perms->CanPinPost() ? '<span class="text-success">can</span>' : '<span class="text-danger">cannot</span>' ?> pin a post <br>
                                You <?= $perms->CanRemovePost() ? '<span class="text-success">can</span>' : '<span class="text-danger">cannot</span>' ?> remove any post <br>
                            </div>
                        </div>
                        <a href="/post/new/<?= $topic->id ?>" class="text-decoration-none">
                            <button type="button" class="sticky-top mt-2 btn btn-outline-primary btn-block"><i class="fa fa-plus"></i> Create New Post</button>
                        </a>
                        <? if ($user->IsAdmin($con) || $user->id == $topic->creator || $perms->CanEditTopic() || $perms->CanRemoveTopic()) { ?>
                            <a href="/topic/<?= $topic->id ?>/settings/" class="text-decoration-none">
                                <button class="btn btn-outline-secondary btn-block mt-sm-2"><i class="fa fa-gear"></i> Topic Settings</button>
                            </a>
                        <? } ?>
                    <? } ?>
                </div>
            </div>
        </div>
        <div class="modal" tabindex="-1" role="dialog" id="pin-post-modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmation</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to pin this post? This post will be shown over other posts.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="$.post('/do/pin-post/', { id: p }, (r) => { if (r == 'INDEX') window.location = '/'; else window.location = '/topic/<?= $topic->id ?>'; })">Pin Post</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" tabindex="-1" role="dialog" id="unpin-post-modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmation</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to unpin this post?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="$.post('/do/unpin-post/', { id: p }, (r) => { if (r == 'INDEX') window.location = '/'; else window.location = '/topic/<?= $topic->id ?>'; })">Unpin Post</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" tabindex="-1" role="dialog" id="delete-post-modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmation</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this post? All comments will be deleted too.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="$.post('/do/delete-post/', { id: p }, () => { window.location = '/topic/<?= $topic->id ?>'; })">Delete Post</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <? include_once "include/script.php"; ?>
</body>

</html>