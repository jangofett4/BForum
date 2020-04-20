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

if ($user == null)
    Router::fastswitch('/');

$topic = new Topic();
$topic->id = $_GET["topic"];

if (!$topic->Fetch($con))
    Router::fastswitch('/');

$perms = new TopicPerms();
$perms->user = $user->id;
$perms->topic = $topic->id;
$isadmin = $user->IsAdmin($con);

if (!$perms->FetchFromUserTopic($con))
    $perms->perms = "0000";

if (!$isadmin)
    if (!$perms->CanEditTopic() && !$perms->CanRemoveTopic())
        Router::fastswitch('/');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?= FORUM_BASE_TITLE . " - " . $topic->name ?> Settings</title>

    <? include_once "include/style.php"; ?>
    <? include_once "include/script-preload.php"; ?>

    <script>
        function createperms(id) {
            let list = $("#perms-user-list");
            $.post('/do/topic/perms-create', {
                "id": id,
                "topic": <?= $topic->id ?>
            }, (r) => {
                $('#perms-modal-close-button').trigger('click');
                list.append(r);
                toastr.success("Added new permission!", "Success");
            });
        }

        function clearsearch() {
            $('#add-perm-search-user-list').html("");
        }

        function searchuser() {
            let name = $('#permName');
            let surname = $('#permSurname');
            name.val()
            surname.val()
            $.post('/do/topic/perms-search-user', {
                'topic': <?= $topic->id ?>,
                'name': name.val(),
                'surname': surname.val()
            }, (r) => {
                $('#add-perm-search-user-list').html(r);
            });
        }

        function deleteperms(id) {
            $.post('/do/topic/perms-delete', {
                'topic': <?= $topic->id ?>,
                'id': id
            }, (data) => {
                window.location = '/topic/<?= $topic->id ?>/settings'
            });
        }

        function setCharAt(str, index, chr) {
            if (index > str.length - 1) return str;
            return str.substr(0, index) + chr + str.substr(index + 1);
        }

        function edit(chk, mode) {
            switch (mode) {
                case "cet":
                    pdata.perms = setCharAt(pdata.perms, 0, chk.checked ? "1" : "0");
                    break;
                case "crt":
                    pdata.perms = setCharAt(pdata.perms, 1, chk.checked ? "1" : "0");
                    break;
                case "cpp":
                    pdata.perms = setCharAt(pdata.perms, 2, chk.checked ? "1" : "0");
                    break;
                case "crp":
                    pdata.perms = setCharAt(pdata.perms, 3, chk.checked ? "1" : "0");
                    break;
                default:
                    break;
            }
        }

        var pdata;

        function saveperms(id) {
            $.post('/do/topic/perms-update', {
                'topic': <?= $topic->id ?>,
                'user': id,
                'perms': pdata.perms
            }, (data) => {
                window.location = '/topic/<?= $topic->id ?>/settings'
            });
        }

        function editperms(id) {
            $('#perms-modal-accordion').html("");
            $.post('/do/topic/perms-fetch', {
                "topic": <?= $topic->id ?>,
                "user": id
            }, (data) => {
                if (data == 'NOPERM')
                    return;

                let i = 0;
                let perms = data.perms;
                pdata = data;

                let canedit = perms.charAt(0) == '1';
                let canremovetopic = perms.charAt(1) == '1';
                let canpin = perms.charAt(2) == '1';
                let canremovepost = perms.charAt(3) == '1';

                $.get(`/api/json/topic/${ data.topic }`, (topic) => {
                    $('#perms-modal-accordion').append(`
                        <div class="card mt-sm-2">
                            <div class="card-header" id="headingOne">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link" data-toggle="collapse" data-target="#perms-collapse-${ data.id }">
                                                Permissions for /t/${ topic.name }
                                            </button>
                                        </h5>
                                    </div>
                                    <div class="col">
                                        <button class="btn btn-primary float-right mr-sm-2" onclick="saveperms(${ data.user })"><i class="fa fa-save"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div id="perms-collapse-${ data.id }" class="collapse" data-parent="#perms-modal-accordion">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="cet-${ data.id }" ${ canedit ? "checked" : "" } onclick="edit(this, 'cet')">
                                        <label class="form-check-label" for="cet-${ data.id }">
                                            Can edit topic properties
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="crt-${ data.id }" ${ canremovetopic ? "checked" : "" }  onclick="edit(this, 'crt')">
                                        <label class="form-check-label" for="crt-${ data.id }">
                                            Can remove topic
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="cpp-${ data.id }" ${ canpin ? "checked" : "" }  onclick="edit(this, 'cpp')">
                                        <label class="form-check-label" for="cpp-${ data.id }">
                                            Can pin posts
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="crp-${ data.id }" ${ canremovepost ? "checked" : "" }  onclick="edit(this, 'crp')">
                                        <label class="form-check-label" for="crp-${ data.id }">
                                            Can remove any post
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `);
                    i++;
                }, "json");
            }, "json");
        }
    </script>
</head>

<body>
    <? include_once "include/header.php"; ?>
    <div class="container">
        <div class="text-center">
            <a href="/topic/<?= $topic->id ?>"><i class="fa fa-external-link"></i> Jump to /t/<?= $topic->name ?></a>
        </div>
        <? if (Session::get("fresh-login")) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                You are successfully logged in as <?= $user->name . " " . $user->surname ?>
            </div>
        <? } ?>
        <? if (Session::get("topic-success-delete-perms")) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                Successfully deleted permission!
            </div>
        <? } ?>
        <? if (Session::get("topic-success-update-perms")) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                Successfully updated permission!
            </div>
        <? } ?>
        <? if (Session::get("success-update-topic")) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                Successfully updated topic properties!
            </div>
        <? } ?>
        <div class="accordion mt-sm-2" id="props-accordion">
            <? if ($isadmin || $perms->CanEditTopic()) { ?>
                <div class="card">
                    <div class="card-header" id="headingOne">
                        <h2 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Basic topic properties
                            </button>
                        </h2>
                    </div>

                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#props-accordion">
                        <div class="card-body">
                            <form method="post" action="/do/topic-update">
                                <input type="hidden" name="id" value="<?= $topic->id ?>">
                                <div class="form-group">
                                    <label for="postTitle">Topic name</label>
                                    <input type="text" class="form-control" name="topic-name" required value="<?= $topic->name ?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Topic description</label>
                                    <textarea class="form-control" name="topic-desc" rows="4" required><?= $topic->desc ?></textarea>
                                    <p class="text-muted">Description and name are important, these are only ways to search your topic.</p>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i> Save</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingTwo">
                        <h2 class="mb-0">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Permissions
                            </button>
                        </h2>
                    </div>
                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#props-accordion">
                        <div class="card-body">
                            <div id="perms-user-list">
                                <?
                                $sql = "SELECT * FROM TblTopicPerms WHERE PTopic = ?";
                                $stmt = $con->prepare($sql);
                                $stmt->execute([$topic->id]);
                                $data = $stmt->fetchAll();
                                ?>
                                <? foreach ($data as $perm) { ?>
                                    <?
                                    if ($perm['PUser'] == $topic->creator || $perm['PUser'] == $user->id)
                                        continue;
                                    $user = new User();
                                    $user->id = $perm['PUser'];
                                    $user->Fetch($con);
                                    ?>
                                    <div class="row align-items-center mt-sm-2">
                                        <div class="col">
                                            <a href="javascript:;" class="list-group-item list-group-item-action" id="perms-user-<?= $user->id ?>" onclick="editperms(<?= $user->id ?>)" data-toggle="modal" data-target="#edit-perms-modal">
                                                User: <?= $user->name . ' ' . $user->surname ?> (<?= $user->email ?>)
                                            </a>
                                        </div>
                                        <div class="col-auto pl-0">
                                            <button class="btn btn-danger"><i class="fa fa-trash" onclick="deleteperms(<?= $user->id ?>)"></i></button>
                                        </div>
                                    </div>
                                <? } ?>
                            </div>
                            <button class="btn btn-success btn-block mt-sm-2" data-toggle="modal" data-target="#add-perm-user-modal" onclick="clearsearch()"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            <? } ?>
            <? if ($isadmin || $perms->CanRemoveTopic()) { ?>
                <div class="card">
                    <div class="card-header" id="headingThree">
                        <h2 class="mb-0">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Dangerous Operations
                            </button>
                        </h2>
                    </div>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#props-accordion">
                        <div class="card-body">
                            <button class="btn btn-danger btn-block" data-toggle="modal" data-target="#delete-topic-modal"><i class="fa fa-exclamation-triangle"></i> Delete Topic <i class="fa fa-exclamation-triangle"></i></button>
                        </div>
                    </div>
                </div>
            <? } ?>
        </div>
        <div class="modal" tabindex="-1" role="dialog" id="edit-perms-modal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit permissions</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="edit-perms-modal-body">
                        <div id="perms-modal-accordion">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal" tabindex="-1" role="dialog" id="add-perm-user-modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Permission</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="perms-modal-close-button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col">
                                <label>Name</label>
                                <input type="text" class="form-control" id="permName">
                            </div>
                            <div class="col">
                                <label>Surname</label>
                                <input type="text" class="form-control" id="permSurname">
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block mt-sm-2" onclick="searchuser()"><i class="fa fa-search"></i></button>
                        <div id="add-perm-search-user-list" class="mt-sm-2"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-block">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal" tabindex="-1" role="dialog" id="delete-topic-modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmation</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this topic? All posts will be deleted too.</p><br>
                        <b>Warning!</b> This is a highly destructive operation and there is no going back!
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-block" onclick="$.post('/do/delete-topic/', { id: <?= $topic->id ?> }, () => { window.location = '/'; })"><i class="fa fa-exclamation-triangle"></i> I am sure, delete the Topic <i class="fa fa-exclamation-triangle"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <? include_once "include/script.php"; ?>
</body>

</html>