<?php

include_once "defs/forum/User.php";
include_once "defs/forum/Topic.php";
include_once "defs/forum/TopicPerms.php";
include_once "defs/forum/Comment.php";
include_once "defs/forum/Post.php";
include_once "defs/forum/PostFlags.php";

include_once "include/dbcon.php";
include_once "defs/ssn.php";

$con = DBConnection::open_or_get();

$user = Session::getvnd('user');
$admin = Session::getvnd('admin');

include_once "xrouter/Router.php";

const PREG_NUMBER = "/[0-9]+/";

Router::get('/', function () {
    require("routes/main.php");
});

Router::any('/login', function () {
    require("routes/login.php");
});

Router::any('/register', function () {
    require("routes/register.php");
});

Router::get('/logout', function () {
    Session::unset('user');
    if (Session::getnd('return'))
        Router::fastswitch(Session::getv('return'));
    else
        Router::fastswitch('/');
});

Router::get('/check', function () {
    require("routes/reset.php");
});

Router::any('/search', function () {
    require('routes/search.php');
});

/* ADMIN */
Router::any('/panel', function () {
    require('routes/panel.php');
});

Router::any('/panel/dashboard', function () {
    require('routes/dashboard.php');
});

/* POST */
Router::any('/post/:post', function ($post) {
    $_GET['post'] = intval($post);
    require("routes/post.php");
}, ROUTE_PUBLIC, PREG_NUMBER);

Router::any('/post/new/:topic', function ($topic) {
    $_GET['topic'] = intval($topic);
    require('routes/newpost.php');
}, ROUTE_PUBLIC, PREG_NUMBER);

/* TOPIC */
Router::getany(['/topic/:topic', '/t/:topic'], function ($topic) {
    $_GET['topic'] = intval($topic);
    require('routes/topic.php');
}, ROUTE_PUBLIC, PREG_NUMBER);

Router::post('/topic/:topic/search', function ($topic) {
    $_POST['topic'] = intval($topic);
    require('routes/postsearch.php');
}, ROUTE_PUBLIC, PREG_NUMBER);

Router::getany(['/topic/:topic/settings', '/t/:topic/settings'], function ($topic) {
    $_GET['topic'] = intval($topic);
    require('routes/topicsettings.php');
});

Router::get('/topic/new', function () {
    require('routes/newtopic.php');
}, ROUTE_PUBLIC, PREG_NUMBER);

Router::get('/topic/:topic/list/:start/:end', function ($topic, $start, $end) {
    $topic = intval($topic);
    $start = intval($start);
    $end = intval($end);
    // Invalid url
    if ($start > $end)
        return Router::fastswitch("/topic/$topic");
    return "range";
}, ROUTE_PUBLIC, PREG_NUMBER, PREG_NUMBER, PREG_NUMBER);

/* USER */
Router::get('/user/:id', function ($id) {
    $id = intval($id);
    $_GET['id'] = $id;
    require('routes/profile.php');
}, ROUTE_PUBLIC, PREG_NUMBER);

Router::get('/cls', function () {
    session_start();
    session_destroy();
    echo "Sessions cleared, going back to /";
    header("Location: /");
});

/* API */
Router::get('/api/json/topic/:id', function ($id) use ($con) {
    $t = new Topic();
    $t->id = $id;
    $t->Fetch($con);
    echo json_encode($t);
}, ROUTE_PUBLIC, PREG_NUMBER);

Router::get('/api/json/post/:id', function ($id) use ($con) {
    $p = new Post();
    $p->id = $id;
    $p->Fetch($con);
    echo json_encode($p);
}, ROUTE_PUBLIC, PREG_NUMBER);

Router::get('/api/json/comment/:id', function ($id) use ($con) {
    $c = new Comment();
    $c->id = $id;
    $c->Fetch($con);
    echo json_encode($c);
}, ROUTE_PUBLIC, PREG_NUMBER);

Router::get('/api/json/user/:id', function ($id) use ($con) {
    $c = new User();
    $c->id = $id;
    $c->Fetch($con);
    $d = array();
    $d['name'] = $c->name;
    $d['surname'] = $c->surname;
    echo json_encode($d);
}, ROUTE_PUBLIC, PREG_NUMBER);

/* SPECIAL FUNCTIONS */
Router::post('/do/login-admin', function () use ($con) {
    if (!isset($_POST['admin-email'], $_POST['admin-password']))
        Router::fastswitch('/');

    $user = new User();
    $user->email = $_POST['admin-email'];
    $user->password = $_POST['admin-password'];
    $data = $user->FetchAllWhere($con, "WHERE UEmail = ? AND UPassword = ?", $user->email, $user->password);
    if (count($data) == 0) {
        Session::set('admin-user-not-found');
        Router::fastbackswitch();
        exit;
    } else {
        $userdata = $data[0];
        $user->id = $userdata["ID"];
        $user->name = $userdata["UName"];
        $user->surname = $userdata["USurname"];
        $sql = "SELECT * FROM TblAdmins WHERE AUserID = ?";
        $stmt = $con->prepare($sql);
        $stmt->execute([$user->id]);
        $data = $stmt->fetchAll();
        if (count($data) == 0) {
            Session::set('admin-user-not-found');
            Router::fastbackswitch();
            exit;
        }
        Session::setv('admin', $user);
        Session::set('fresh-admin-login');
        Router::fastswitch('/panel/dashboard');
    }
});

Router::post('/do/topic-new', function () use ($con, $user) {
    if (!isset($_POST['topic-name'], $_POST['topic-desc'], $_POST['topic-op']))
        Router::fastswitch('/');
    if ($user == null)
        Router::fastswitch('/');

    $topic = new Topic();
    $topic->name = $_POST['topic-name'];
    $topic->desc = $_POST['topic-desc'];
    $topic->creator = $user->id;
    $topic->Push($con);

    $perms = new TopicPerms();
    $perms->topic = $topic->id;
    $perms->user = $user->id;
    $perms->perms = "1111";
    $perms->Push($con);

    Session::set('success-new-topic');
    Router::fastswitch('/topic/' . $topic->id);
});

Router::post('/do/topic-update', function () use ($con, $user) {
    if (!isset($_POST['topic-name'], $_POST['topic-desc'], $_POST['id']))
        Router::fastswitch('/');
    if ($user == null)
        Router::fastswitch('/');
    $topic = new Topic();
    $topic->id = $_POST['id'];
    $topic->Fetch($con);
    $topic->name = $_POST['topic-name'];
    $topic->desc = $_POST['topic-desc'];
    $topic->Update($con);

    Session::set('success-update-topic');
    Router::fastswitch('/topic/' . $topic->id . '/settings');
});

Router::post('/do/post-new', function () use ($con) {
    if (!isset($_POST['post-content'], $_POST['post-title'], $_POST['post-op'], $_POST['post-topic']))
        Router::fastswitch("/");
    $post = new Post();
    $post->title = $_POST['post-title'];
    $post->content = $_POST['post-content'];
    $post->op = $_POST['post-op'];
    $post->topic = $_POST['post-topic'];
    $post->Push($con);
    Session::set('new-post');
    Router::fastswitch('/topic/' . $_POST['post-topic']);
});

Router::post('/do/pin-post', function () use ($con, $user) {
    if (!isset($_POST['id']))
        die('INDEX');
    if ($user == null)
        die('INDEX');
    $post = new Post();
    $post->id = $_POST['id'];
    $post->Fetch($con);

    $perms = new TopicPerms();
    $perms->user = $user->id;
    $perms->topic = $post->topic;
    if (!$perms->FetchFromUserTopic($con))
        die('INDEX');
    if (!$perms->CanPinPost())
        die('INDEX');
    $flags = $post->GetFlags($con);
    $flags->isPinned = true;
    $flags->Update($con);
    Session::set('success-pin-post');
});

Router::post('/do/unpin-post', function () use ($con, $user) {
    if (!isset($_POST['id']))
        die('INDEX');
    if ($user == null)
        die('INDEX');
    $post = new Post();
    $post->id = $_POST['id'];
    $post->Fetch($con);

    $perms = new TopicPerms();
    $perms->user = $user->id;
    $perms->topic = $post->topic;
    if (!$perms->FetchFromUserTopic($con))
        die('INDEX');
    if (!$perms->CanPinPost())
        die('INDEX');
    $flags = $post->GetFlags($con);
    $flags->isPinned = false;
    $flags->Update($con);
    Session::set('success-unpin-post');
});

Router::post('/do/delete-topic', function () use ($con, $user) {
    if (!isset($_POST['id']))
        Router::fastswitch('/');
    if ($user == null)
        Router::fastswitch('/');

    $topic = new Topic();
    $topic->id = $_POST['id'];
    $topic->Fetch($con);

    $perms = new TopicPerms();
    $perms->user = $user->id;
    $perms->topic = $topic->id;

    if ($user->id != $topic->creator) {
        if ($perms->FetchFromUserTopic($con)) {
            if (!$perms->CanRemoveTopic())
                Router::fastswitch('/');
            else {
                $sql = "DELETE FROM " . Topic::CURRENT_TABLE . " WHERE ID = ?";
                $stmt = $con->prepare($sql);
                $stmt->execute([$topic->id]);
                Session::set('success-delete-topic');
            }
        } else
            Router::fastswitch('/');
    }

    $sql = "DELETE FROM " . Topic::CURRENT_TABLE . " WHERE ID = ?";
    $stmt = $con->prepare($sql);
    $stmt->execute([$topic->id]);
    Session::set('success-delete-topic');
});

Router::post('/do/delete-post', function () use ($con, $user) {
    if (!isset($_POST['id']))
        Router::fastswitch('/');
    if ($user == null)
        Router::fastswitch('/');
    $post = new Post();
    $post->id = $_POST['id'];
    $post->Fetch($con);

    $perms = new TopicPerms();
    $perms->user = $user->id;
    $perms->topic = $post->topic;

    if ($post->op != $user->id) {
        if ($perms->FetchFromUserTopic($con)) {
            if (!$perms->CanRemovePost())
                Router::fastswitch('/');
            else {
                $sql = "DELETE FROM " . Post::CURRENT_TABLE . " WHERE ID = ?";
                $stmt = $con->prepare($sql);
                $stmt->execute([$post->id]);
                Session::set('success-delete-post');
            }
        } else
            Router::fastswitch('/');
    }

    $sql = "DELETE FROM " . Post::CURRENT_TABLE . " WHERE ID = ?";
    $stmt = $con->prepare($sql);
    $stmt->execute([$post->id]);
    Session::set('success-delete-post');
});

Router::post('/do/update-comment', function () use ($con, $user) {
    if (!isset($_POST['id'], $_POST['content']))
        exit;
    if ($user == null)
        exit;
    $comment = new Comment();
    $comment->id = $_POST['id'];
    $comment->Fetch($con);
    if ($comment->op != $user->id)
        exit;
    $comment->content = $_POST['content'];
    $comment->Update($con);
    Session::set('success-edit-comment');
});

Router::post('/do/update-user', function () use ($con, $user) {
    if (!isset($_POST['id']))
        die('EMPTY');
    if ($user == null)
        die('NOUSER');
    $profile = new User();
    $profile->id = $_POST['id'];
    $profile->Fetch($con);
    if ($profile->id != $user->id)
        die('RESTRICT');
    $profile->name = $_POST['name'];
    $profile->surname = $_POST['surname'];
    $profile->email = $_POST['email'];
    $profile->Update($con);
    Session::set('success-update-profile');
});

Router::post('/do/update-post', function () use ($con, $user) {
    if (!isset($_POST['id'], $_POST['post-title'], $_POST['post-content']))
        exit;
    if ($user == null)
        exit;
    $post = new Post();
    $post->id = $_POST['id'];
    $post->Fetch($con);
    if ($post->op != $user->id)
        exit;
    $post->title = $_POST['post-title'];
    $post->content = $_POST['post-content'];
    $post->edits = $post->edits + 1;
    $post->updated = new DateTime();
    $post->Update($con);
    Session::set('success-edit-post');
});

Router::post('/do/lock-post', function () use ($con, $user) {
    if (!isset($_POST['id']))
        Router::fastswitch('/');
    if ($user == null)
        Router::fastswitch('/');
    $post = new Post();
    $post->id = $_POST['id'];
    $post->Fetch($con);
    if ($post->op != $user->id)
        Router::fastswitch('/');
    $flags = $post->GetFlags($con);
    if ($flags == false) {
        $flags = new PostFlags();
        $flags->post = $post->id;
        $flags->isPinned = false;
        $flags->isLocked = true;
        $flags->order = 0;
        $flags->Push($con);
    } else {
        $flags->isLocked = true;
        $flags->Update($con);
    }
    Session::set('success-lock-post');
});

Router::post('/do/unlock-post', function () use ($con, $user) {
    if (!isset($_POST['id']))
        Router::fastswitch('/');
    if ($user == null)
        Router::fastswitch('/');
    $post = new Post();
    $post->id = $_POST['id'];
    $post->Fetch($con);
    if ($post->op != $user->id)
        Router::fastswitch('/');
    $flags = $post->GetFlags($con);
    if ($flags == false) {
        Router::fastswitch('/');
    } else {
        $flags->isLocked = false;
        $flags->Update($con);
    }
    Session::set('success-unlock-post');
});

Router::post('/do/delete-comment', function () use ($con, $user) {
    if (!isset($_POST['id']))
        exit;
    $id = $_POST['id'];
    if ($user == null)
        exit;
    $comment = new Comment();
    $comment->id = $id;
    $comment->Fetch($con);
    $post = $comment->GetPost($con);
    $flags = $post->GetFlags($con);
    if ($flags != false && $flags->isLocked) {
        Session::set('error-delete-comment-locked');
        exit;
    }
    // TODO: fix permissions
    if ($user->id != $comment->op)
        Router::fastswitch('/');
    $sql = "DELETE FROM " . Comment::CURRENT_TABLE . " WHERE ID = ?";
    $stmt = $con->prepare($sql);
    $stmt->execute([$comment->id]);
    Session::set('success-delete-comment');
});

Router::post('/do/comment', function () use ($con, $user) {
    if (!isset($_POST['content'], $_POST['post']))
        Router::fastswitch('/');

    if ($user == null) {
        Router::fastswitch('/');
        exit;
    }

    $cmt = new Comment();
    $cmt->content = $_POST['content'];
    $cmt->op = $user->id;
    $cmt->post = $_POST['post'];

    try {
        !$cmt->Push($con);
        Session::set('success-comment');
    } catch (\PDOException $th) {
        Session::set('error-comment');
    }
    Router::fastswitch('/post/' . $cmt->post);
});

Router::post('/do/panel/perms-fetch', function () use ($con, $admin) {
    if (!isset($_POST['id']))
        die("PANEL");
    if ($admin == null)
        exit;
    try {
        $sql = "SELECT * FROM TblTopicPerms WHERE PUser = ?";
        $stmt = $con->prepare($sql);
        $stmt->execute([$_POST['id']]);
        $data = $stmt->fetchAll();
        die(json_encode($data));
    } catch (\Exception $e) {
    }
});

Router::post('/do/panel/perms-fetch-new', function () use ($con, $admin) {
    if (!isset($_POST['id']))
        die('PANEL');
    if ($admin == null)
        exit;
    try {
        $sql = "SELECT * FROM TblTopics WHERE ID NOT IN (SELECT PTopic FROM TblTopicPerms WHERE PUser = ?)";
        $stmt = $con->prepare($sql);
        $stmt->execute([$_POST['id']]);
        $data = $stmt->fetchAll();
        foreach ($data as $topic) {
            $id = $topic['ID'];
            $name = $topic['TName'];
            echo $name;
            echo "<option value=\"$id\">/t/$name</option>";
        }
    } catch (\Exception $e) {
        die($e);
    }
});

Router::post('/do/panel/perms-add-update', function () use ($con, $admin) {
    if (!isset($_POST['topic'], $_POST['user'], $_POST['perms']))
        die('EMPTY');
    if ($admin == null)
        die('PANEL');
    $user = $_POST['user'];
    $topic = $_POST['topic'];
    $perms = $_POST['perms'];

    try {
        $sql = "SELECT * FROM TblTopicPerms WHERE PUser = ? AND PTopic = ?";
        $stmt = $con->prepare($sql);
        $stmt->execute([$user, $topic]);
        $data = $stmt->fetchAll();
        if (count($data) == 0) {
            $sql = "INSERT INTO TblTopicPerms (PUser, PTopic, PPerms) VALUES (?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->execute([$user, $topic, $perms]);
        } else {
            $sql = "UPDATE TblTopicPerms SET PPerms = ? WHERE ID = ?";
            $stmt = $con->prepare($sql);
            $stmt->execute([$perms, $data[0]['ID']]);
        }
        die('SUCCESS');
    } catch (\Exception $e) {
        die('EXCEPTION');
    }
});

Router::post('/do/panel/perms-delete', function () use ($con, $admin) {
    if (!isset($_POST['id']))
        die('EMPTY');
    if ($admin == null)
        die('EMPTY');
    $sql = "DELETE FROM TblTopicPerms WHERE ID = ?";
    $stmt = $con->prepare($sql);
    $stmt->execute([$_POST['id']]);
});

Router::post('/do/topic/perms-fetch', function () use ($con, $admin, $user) {
    if (!isset($_POST['topic'], $_POST['user']))
        die('EMPTY');
    if ($user == null)
        exit;

    $perms = new TopicPerms();
    $perms->topic = $_POST['topic'];
    $perms->user = $user->id;
    $perms->FetchFromUserTopic($con);

    $isadmin = $user->IsAdmin($con);
    if (!$perms->CanEditTopic() && !$isadmin)
        die('PERMS');

    $perms->user = $_POST['user'];
    if (!$perms->FetchFromUserTopic($con))
        die('NOPERM');
    die(json_encode($perms));
});

Router::post('/do/topic/perms-search-user', function () use ($con, $admin, $user) {
    if (!isset($_POST['topic'], $_POST['name'], $_POST['surname']))
        die('EMPTY');
    if ($user == null)
        exit;

    $topic = new Topic();
    $topic->id = $_POST['topic'];
    $topic->Fetch($con);

    $perms = new TopicPerms();
    $perms->user = $user->id;
    $perms->topic = $topic->id;
    $perms->FetchFromUserTopic($con);

    $isadmin = $user->IsAdmin($con);
    if (!$perms->CanEditTopic() && !$isadmin)
        die('PERMS');

    $termname = $_POST['name'];
    $termsurname = $_POST['surname'];
    $users = (new User())->FetchAllWhere($con, "
    WHERE UName LIKE ? AND
    USurname LIKE ? AND
    ID NOT IN (
        SELECT PUser as ID FROM TblTopicPerms WHERE PTopic = ?
        UNION
        SELECT AUserID as ID FROM TblAdmins
    )", "%$termname%", "%$termsurname%", $topic->id);
    foreach ($users as $user) {
        if ($user['ID'] == $topic->creator)
            continue;
        $userget = new User();
        $userget->id = $user['ID'];
?>
        <a href="#" class="list-group-item list-group-item-action" onclick="createperms(<?= $user['ID'] ?>)">User: <?= $user['UName'] . ' ' . $user['USurname'] ?> (<?= $user['UEmail'] ?>)</a>
    <?
    }
});

Router::post('/do/topic/perms-delete', function () use ($con, $admin, $user) {
    if (!isset($_POST['topic'], $_POST['id']))
        die('EMPTY');
    if ($user == null)
        die('NOUSER');

    $topic = new Topic();
    $topic->id = $_POST['topic'];
    $topic->Fetch($con);

    $perms = new TopicPerms();
    $perms->user = $user->id;
    $perms->topic = $topic->id;
    $perms->FetchFromUserTopic($con);

    $isadmin = $user->IsAdmin($con);
    if (!$perms->CanRemoveTopic() && !$isadmin)
        die('PERMS');

    $sql = "DELETE FROM TblTopicPerms WHERE PUser = ? AND PTopic = ?";
    $stmt = $con->prepare($sql);
    $stmt->execute([$_POST['id'], $topic->id]);
    Session::set('topic-success-delete-perms');
});

Router::post('/do/topic/perms-update', function () use ($con, $admin, $user) {
    if (!isset($_POST['topic'], $_POST['user'], $_POST['perms']))
        die('EMPTY');
    if ($user == null)
        die('NOUSER');

    $topic = new Topic();
    $topic->id = $_POST['topic'];
    $topic->Fetch($con);

    $perms = new TopicPerms();
    $perms->user = $user->id;
    $perms->topic = $topic->id;
    $perms->FetchFromUserTopic($con);

    $isadmin = $user->IsAdmin($con);

    if (!$perms->CanEditTopic() && !$isadmin)
        die('PERMS');

    $perms = new TopicPerms();
    $perms->user = $_POST['user'];
    $perms->topic = $_POST['topic'];
    $perms->FetchFromUserTopic($con);

    $perms->perms = $_POST['perms'];
    $perms->Update($con);
    Session::set('topic-success-update-perms');
});

Router::post('/do/topic/perms-create', function () use ($con, $admin, $user) {
    if (!isset($_POST['topic'], $_POST['id']))
        die('EMPTY');
    if ($user == null)
        exit;

    $topic = new Topic();
    $topic->id = $_POST['topic'];
    $topic->Fetch($con);

    $perms = new TopicPerms();
    $perms->user = $user->id;
    $perms->topic = $topic->id;
    $perms->FetchFromUserTopic($con);

    $isadmin = $user->IsAdmin($con);
    if (!$perms->CanEditTopic() && !$isadmin)
        die('PERMS');

    $userid = $_POST['id'];
    $userget = new User();
    $userget->id = $userid;
    $userget->Fetch($con);

    try {
        $sql = "INSERT INTO TblTopicPerms (PUser, PTopic, PPerms) VALUES (?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->execute([$userid, $topic->id, "0000"]);
    ?>
        <div class="row align-items-center mt-sm-2">
            <div class="col">
                <a href="javascript:;" class="list-group-item list-group-item-action" id="perms-user-<?= $userget->id ?>" onclick="editperms(<?= $userget->id ?>)" data-toggle="modal" data-target="#edit-perms-modal">
                    User: <?= $userget->name . ' ' . $userget->surname ?> (<?= $userget->email ?>)
                </a>
            </div>
            <div class="col-auto pl-0">
                <button class="btn btn-danger"><i class="fa fa-trash" onclick="deleteperms(<?= $userget->id ?>)"></i></button>
            </div>
        </div>
<?
    } catch (\Throwable $th) {
        die('EXCEPTION');
    }
});

/* PANEL FUNCTIONS */
Router::post('/do/panel/perms-search-user', function () use ($con, $admin) {
    if (!isset($_POST['name'], $_POST['surname']))
        die("PANEL");
    if ($admin == null)
        exit;

    $name = $_POST['name'];
    $surname = $_POST['surname'];

    try {
        $sql = "SELECT * FROM TblUsers WHERE UName LIKE ? AND USurname LIKE ?";
        $stmt = $con->prepare($sql);
        $stmt->execute(['%' . $name . '%', '%' . $surname . '%']);
        $data = $stmt->fetchAll();

        echo "<div class=\"list-group\">";
        foreach ($data as $d) {
            $id = $d['ID'];
            $name = $d['UName'];
            $surname = $d['USurname'];
            $email = $d['UEmail'];
            echo "<a href=\"#\" class=\"list-group-item list-group-item-action\" id=\"perms-user-$id\" onclick=\"editperms($id)\" data-toggle=\"modal\" data-target=\"#edit-perms-modal\">User: $name $surname ($email)</a>";
        }
        echo "</div>";
    } catch (\Exception $e) {
        die("EMPTY");
    }
});
