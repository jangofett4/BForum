<?

include_once "xrouter/Router.php";

include_once "defs/forum/Topic.php";
include_once "defs/forum/Post.php";
include_once "defs/forum/PostFlags.php";
include_once "defs/forum/User.php";
include_once "defs/forum/Comment.php";

include_once "defs/default.php";
include_once "include/dbcon.php";
include_once "include/helper.php";
include_once "defs/ssn.php";

$con = DBConnection::open_or_get();

/** @var User $user */
$user = Session::getvnd("user");

$post = new Post();
$post->id = $_GET["post"];


if (!$post->Fetch($con)) {
    Router::fastswitch('/');
    exit;
}

$op = $post->GetOp($con);
$topic = $post->GetTopic($con);
$comments = $post->GetComments($con);
$flags = $post->GetFlags($con);
$usercomment = "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?= FORUM_BASE_TITLE ?> - <?= $post->title ?></title>

    <script src="https://cdn.jsdelivr.net/npm/showdown@1.9.1/dist/showdown.min.js" crossorigin="anonymous"></script>

    <? include_once "include/style.php"; ?>
    <? include_once "include/script-preload.php"; ?>
    <script>
        var c = -1;
        var title_text = "";
        var content_text = "";

        function editmode() {
            var titlespan = $('#post-title-span');
            var contentspan = $('#post-content-span');
            var title = $('#post-title');

            var edittitleinput = $('#post-edit-title');
            var editcontentinput = $('#post-edit-content');

            titlespan.toggleClass('d-none');
            contentspan.toggleClass('d-none');

            edittitleinput.toggleClass('d-none');
            editcontentinput.toggleClass('d-none');

            edittitleinput.val(title_text);
            editcontentinput.val(content_text);

            $('#post-complete-edit-button').toggleClass('d-none');
            $('#post-cancel-edit-button').toggleClass('d-none');
            $('#post-edit-button').toggleClass('d-none');
        }

        function editcomment(i) {
            var raw = $(`#comment-content-${ i }-raw`);
            var edit = $(`#comment-edit-${ i }`);
            var span = $(`#comment-content-${ i }-html`);

            var editcmtlink = $(`#edit-comment-link-${ i }`);
            var editp = $(`#edit-comment-${ i }-tools`)

            editcmtlink.toggleClass('d-none');
            editp.toggleClass('d-none');

            edit.html(raw.html());

            edit.toggleClass('d-none');
            span.toggleClass('d-none');
        }

        function completeeditcomment(i) {
            var raw = $(`#comment-content-${ i }-raw`);
            var edit = $(`#comment-edit-${ i }`);
            var span = $(`#comment-content-${ i }-html`);

            var editcmtlink = $(`#edit-comment-link-${ i }`);
            var editp = $(`#edit-comment-${ i }-tools`)
            $.post('/do/update-comment', {
                id: i,
                content: edit.val()
            }, (r) => {
                window.location = '/post/<?= $post->id ?>';
            })
        }

        function canceleditcomment(i) {
            var raw = $(`#comment-content-${ i }-raw`);
            var edit = $(`#comment-edit-${ i }`);
            var span = $(`#comment-content-${ i }-html`);

            var editcmtlink = $(`#edit-comment-link-${ i }`);
            var editp = $(`#edit-comment-${ i }-tools`)

            editcmtlink.toggleClass('d-none');
            editp.toggleClass('d-none');
            edit.toggleClass('d-none');
            span.toggleClass('d-none');
        }

        function completeedit() {
            var edittitleinput = $('#post-edit-title');
            var editcontentinput = $('#post-edit-content');

            $.post('/do/update-post', {
                id: <?= $post->id ?>,
                "post-title": edittitleinput.val(),
                "post-content": editcontentinput.val()
            }, (r) => {
                window.location = '/post/<?= $post->id ?>';
            });
        }

        function canceledit() {
            var titlespan = $('#post-title-span');
            var contentspan = $('#post-content-span');
            var title = $('#post-title');

            var edittitleinput = $('#post-edit-title');
            var editcontentinput = $('#post-edit-content');

            titlespan.toggleClass('d-none');
            contentspan.toggleClass('d-none');

            edittitleinput.toggleClass('d-none');
            editcontentinput.toggleClass('d-none');

            $('#post-complete-edit-button').toggleClass('d-none');
            $('#post-cancel-edit-button').toggleClass('d-none');
            $('#post-edit-button').toggleClass('d-none');
        }
    </script>
</head>

<body>
    <? include_once "include/header.php"; ?>
    <div class="container">
        <a href="/topic/<?= $topic->id ?>"><i class="fa fa-arrow-left"></i> Back to /t/<?= $topic->name ?></a>
        <? if (Session::get("error-comment")) { ?>
            <div class="alert alert-danger my-2" role="alert">
                Unable to post comment, try again a few seconds later (check your internet connection)
            </div>
        <? } else if (Session::get("success-comment")) { ?>
            <div class="alert alert-success my-2" role="alert">
                Comment successful!
            </div>
        <? } else if (Session::get("error-delete-comment-locked")) { ?>
            <div class="alert alert-danger my-2" role="alert">
                Cannot delete a comment in a locked post!
            </div>
        <? } ?>
        <? if (Session::get('success-lock-post')) { ?>
            <div class="alert alert-success my-2" role="alert">
                Post locked successfully!
            </div>
        <? } else if (Session::get('success-unlock-post')) { ?>
            <div class="alert alert-success my-2" role="alert">
                Post unlocked successfully!
            </div>
        <? } else if (Session::get('success-edit-post')) { ?>
            <div class="alert alert-success my-2" role="alert">
                Post edited successfully!
            </div>
        <? } else if (Session::get('success-edit-comment')) { ?>
            <div class="alert alert-success my-2" role="alert">
                Comment edited successfully!
            </div>
        <? } ?>
        <div class="card mt-sm-2">
            <h6 class="card-header">
                <div class="row">
                    <div class="col">
                        <input type="text" id="post-edit-title" class="form-control d-none">
                        <span id="post-title-span">
                            Post: <span id="post-title"><?= $post->title ?></span>
                        </span>
                    </div>
                    <div class="col">
                        <div class="dropdown dropleft show text-right">
                            <a href="javascript:;" role="button" id="postDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="btn fa fa-ellipsis-v"></i>
                            </a>

                            <div class="dropdown-menu">
                                <? if ($user != null && $op->id == $user->id) { ?>
                                    <a class="dropdown-item color-primary" href="#" onclick="editmode()" id="post-edit-button"><i class="fa fa-pencil"></i> Edit</a>
                                    <a class="dropdown-item d-none" href="#" onclick="completeedit()" id="post-complete-edit-button"><i class="fa fa-check"></i> Complete Edit</a>
                                    <a class="dropdown-item d-none" href="#" onclick="canceledit()" id="post-cancel-edit-button"><i class="fa fa-times"></i> Cancel Edit</a>
                                    <div class="dropdown-divider"></div>
                                    <? if ($flags != null && $flags->isLocked) { ?>
                                        <a class="dropdown-item" onclick="$.post('/do/unlock-post/', { id: <?= $post->id ?> }, (r) => { window.location = '/post/<?= $post->id ?>'; })"><i class="fa fa-unlock"></i> Unlock post</a>
                                    <? } else { ?>
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#lock-post-modal"><i class="fa fa-lock"></i> Lock post</a>
                                    <? } ?>
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#delete-post-modal"><i class="fa fa-trash"></i> Delete post</a>
                                <? } ?>
                                <a class="dropdown-item" href="#"><i class="fa fa-user"></i> User profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            </h6>
            <div class="card-body">
                <p class="card-text">
                    <textarea class="form-control d-none" id="post-edit-content" rows="10" required></textarea>
                    <p class="d-none" id="post-content-raw"><?= $post->content ?></p>
                    <p id="post-content-span">
                        <script>
                            (() => {
                                showdown.setFlavor('github');
                                var converter = new showdown.Converter(),
                                    text = $("#post-content-raw").html(),
                                    html = converter.makeHtml(text);
                                $("#post-content-span").html(html);
                                content_text = text;
                                title_text = $('#post-title').html();
                            })();
                        </script>
                    </p>
                </p>
            </div>
            <div class="m-sm-1 text-right">
                Posted at <?= $post->time->format('Y-m-d H:i') ?> by <a href="/user/<?= $op->id ?>"><?= $op->name . ' ' . $op->surname ?></a>
                <? if ($post->edits > 0) { ?>
                    (edited <?= $post->edits ?> time(s), last edit at <?= $post->updated->format("y-m-d H:i") ?>)
                <? } ?>
            </div>
        </div>
        <? if ($user !== null) { ?>
            <? if ($flags == false || !$flags->isLocked) { ?>
                <form class="mt-2" action="/do/comment" method="POST">
                    <div class="form-group">
                        <label>Post a Comment</label>
                        <input type="hidden" name="post" value="<?= $post->id ?>">
                        <textarea class="form-control" rows="4" name="content" placeholder="Your comment (markdown is enabled)" required></textarea>
                        <button type="submit" class="btn btn-primary mt-2 float-right" name="comment">Post Comment</button>
                    </div>
                </form>
            <? } else if ($flags != false) { ?>
                <h5>This post is locked, comments are disabled.</h5>
            <? } ?>
        <? } else { ?>
            You must log-in to comment
        <? }

        $countcomments = count($comments);
        echo "<h4 class='my-2'> $countcomments Comments </h4>";

        /** @var Comment $cmt */
        foreach ($comments as $cmt) {
            /* TODO: maybe optimize this, use a user cache? */
            $commentop = $cmt->GetOp($con);
        ?>
            <div class="card my-sm-2">
                <div class="card-body">
                    <pre class="d-none" id="comment-content-<?= $cmt->id ?>-raw"><?= htmlentities($cmt->content) ?></pre>
                    <textarea class="form-control d-none" id="comment-edit-<?= $cmt->id ?>" rows="10" required></textarea>
                    <p class="card-text" id="comment-content-<?= $cmt->id ?>-html">
                        <script>
                            (() => {
                                var converter = new showdown.Converter(),
                                    text = $("#comment-content-<?= $cmt->id ?>-raw").html(),
                                    html = converter.makeHtml(text);
                                $('#comment-content-<?= $cmt->id ?>-html').html(html);
                            })();
                        </script>
                    </p>
                </div>
                <div class="m-sm-1 text-right">
                    Comment at <?= $cmt->time ?> by <a href="/user/<?=$commentop->id ?>"><?= $commentop->name . ' ' . $commentop->surname ?></a>
                    <? if ($user != null && $user->id == $commentop->id) { ?>
                        (
                        <a href="javascript:;" data-toggle="modal" data-target="#delete-comment-modal" onclick="c = <?= $cmt->id ?>">delete</a>,
                        <a href="javascript:;" onclick="editcomment(<?= $cmt->id ?>)" id="edit-comment-link-<?= $cmt->id ?>">edit</a>
                        <span id="edit-comment-<?= $cmt->id ?>-tools" class="d-none">
                            <a href="javascript:;" onclick="completeeditcomment(<?= $cmt->id ?>)">save</a>, 
                            <a href="javascript:;" onclick="canceleditcomment(<?= $cmt->id ?>)">cancel</a>
                        </span>
                        )
                    <? } ?>
                </div>
            </div>
        <?
        }
        ?>
        <div class="modal" tabindex="-1" role="dialog" id="delete-comment-modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmation</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this comment?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="$.post('/do/delete-comment/', { id: c, post: <?= $post->id ?>}, ()=>{ location.href = '<?= Router::get_current_route() ?>'; })">Delete Comment</button>
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
                        <p>Are you sure you want to delete this post? (all the comments will be deleted too)</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="$.post('/do/delete-post/', { id: <?= $post->id ?> }, () => 
                        { 
                            window.location = '/topic/<?= $topic->id ?>'; 
                        })">Delete Post</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal" tabindex="-1" role="dialog" id="lock-post-modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmation</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to lock this post? Further comments will not be possible until unlocked.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="$.post('/do/lock-post/', { id: <?= $post->id ?> }, () => { window.location = '/post/<?= $post->id ?>'; })">Lock Post</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <? include_once "include/script.php"; ?>
</body>

</html>