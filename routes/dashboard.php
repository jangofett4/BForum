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
$admin = Session::getvnd("admin");
if ($admin == null)
    Router::fastswitch('/panel');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?= FORUM_BASE_TITLE ?> - Admin Panel</title>

    <? include_once "include/style.php"; ?>
    <? include_once "include/script-preload.php"; ?>
    <script>
        var pu = -1;

        function search() {
            var name = $("#perms-name").val();
            var surname = $("#perms-surname").val();
            $.post('/do/panel/perms-search-user', {
                "name": name,
                "surname": surname
            }, (r) => {
                if (r == "PANEL")
                    window.location = '/panel';
                else if (r == "EMPTY")
                    console.log("Erroneous input");
                else
                    $("#perms-search-result-list").html(r);
            })
        }

        function gettopics() {
            $.post('/do/panel/perms-fetch-new', {
                "id": puser
            }, (r) => {
                $("#perms-add-topic").html(r);
            });
        }

        function setCharAt(str, index, chr) {
            if (index > str.length - 1) return str;
            return str.substr(0, index) + chr + str.substr(index + 1);
        }

        function edit(i, chk, mode) {
            switch (mode) {
                case "cet":
                    pdata[i].PPerms = setCharAt(pdata[i].PPerms, 0, chk.checked ? "1" : "0");
                    break;
                case "crt":
                    pdata[i].PPerms = setCharAt(pdata[i].PPerms, 1, chk.checked ? "1" : "0");
                    break;
                case "cpp":
                    pdata[i].PPerms = setCharAt(pdata[i].PPerms, 2, chk.checked ? "1" : "0");
                    break;
                case "crp":
                    pdata[i].PPerms = setCharAt(pdata[i].PPerms, 3, chk.checked ? "1" : "0");
                    break;
                default:
                    break;
            }
        }

        var pdata;
        var puser;

        function editperms(id) {
            $('#perms-modal-accordion').html("");
            $.post('/do/panel/perms-fetch', {
                "id": id
            }, (data) => {
                pdata = data;
                puser = id;
                var i = 0;
                data.forEach(obj => {
                    var perms = obj.PPerms;

                    var canedit = perms.charAt(0) == '1';
                    var canremovetopic = perms.charAt(1) == '1';
                    var canpin = perms.charAt(2) == '1';
                    var canremovepost = perms.charAt(3) == '1';

                    $.get(`/api/json/topic/${ obj.PTopic }`, (topic) => {
                        $('#perms-modal-accordion').append(`
                        <div class="card mt-sm-2">
                            <div class="card-header" id="headingOne">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link" data-toggle="collapse" data-target="#perms-collapse-${ obj.ID }">
                                                Permissions for /t/${ topic.name }
                                            </button>
                                        </h5>
                                    </div>
                                    <div class="col">
                                    <button class="btn btn-danger float-right"><i class="fa fa-trash" onclick="deleteperms(${ i })"></i></button>
                                    <button class="btn btn-primary float-right mr-sm-2" onclick="saveperms(${ i }, ${ topic.id }, ${ obj.PUser })"><i class="fa fa-save"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div id="perms-collapse-${ obj.ID }" class="collapse" data-parent="#perms-modal-accordion">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="cet-${ obj.ID }" ${ canedit ? "checked" : "" } onclick="edit(${ i }, this, 'cet')">
                                        <label class="form-check-label" for="cet-${ obj.ID }">
                                            Can edit topic properties
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="crt-${ obj.ID }" ${ canremovetopic ? "checked" : "" }  onclick="edit(${ i }, this, 'crt')">
                                        <label class="form-check-label" for="crt-${ obj.ID }">
                                            Can remove topic
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="cpp-${ obj.ID }" ${ canpin ? "checked" : "" }  onclick="edit(${ i }, this, 'cpp')">
                                        <label class="form-check-label" for="cpp-${ obj.ID }">
                                            Can pin posts
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="crp-${ obj.ID }" ${ canremovepost ? "checked" : "" }  onclick="edit(${ i }, this, 'crp')">
                                        <label class="form-check-label" for="crp-${ obj.ID }">
                                            Can remove any post
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `);
                        i++;
                    }, "json");
                });
            }, "json");
        }

        function saveperms(id, topic, user) {
            var perms = pdata[id].PPerms;
            $.post('/do/panel/perms-add-update', {
                "topic": topic,
                "user": user,
                "perms": perms
            }, (r) => {
                toastr.success("Permissions edited!", "Success");
            });
        }

        function addperms() {
            var perms = "";
            perms += $("#perms-add-cet").prop("checked") ? "1" : "0";
            perms += $("#perms-add-crt").prop("checked") ? "1" : "0";
            perms += $("#perms-add-cpp").prop("checked") ? "1" : "0";
            perms += $("#perms-add-crp").prop("checked") ? "1" : "0";
            var topic = $("#perms-add-topic").prop("value");
            $.post('/do/panel/perms-add-update', { "topic": topic, "user": puser, "perms": perms }, () => {
                editperms(puser);
                toastr.success("New permissions added!", "Success");
            });
        }

        function deleteperms(id) {
            var pid = pdata[id].ID;
            $.post('/do/panel/perms-delete', {
                "id": pid
            }, () => {
                editperms(puser);
                toastr.success("Permissions deleted!", "Success");
            });
        }
    </script>
</head>

<body>
    <div class="container pt-sm-2">
        <ul class="nav nav-pills nav-fill" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#perms" role="tab">Permissions</a>
            </li>
            <!--
            <li class="nav-item">
                <a class="nav-link" id="contact-tab" data-toggle="tab" href="#topics" role="tab">Topics</a>
            </li>
            -->
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active text-center mt-sm-2" id="home" role="tabpanel">
                Hello <?= $admin->name . ' ' . $admin->surname ?>, see other tab for permissions
            </div>
            <div class="tab-pane fade" id="perms" role="tabpanel">
                <div class="row mt-sm-2">
                    <div class="col">
                        <label>Name</label>
                        <input type="text" class="form-control" id="perms-name" required>
                    </div>
                    <div class="col">
                        <label>Surname</label>
                        <input type="text" class="form-control" id="perms-surname" required>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-block my-sm-2" onclick="search()"><i class="fa fa-search"></i></button>
                <div id="perms-search-result-list">
                </div>
            </div>
            <!--
            <div class="tab-pane fade" id="topics" role="tabpanel">
                topics tab
            </div>
            -->
        </div>
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
                    <button class="btn btn-primary btn-block mt-sm-2" data-toggle="modal" data-target="#add-perms-modal" onclick="gettopics()"><i class="fa fa-plus"></i></button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="add-perms-modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add permissions</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="edit-perms-modal-body">
                    <label>Topic</label>
                    <select id="perms-add-topic" class="w-100">
                    </select>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="perms-add-cet">
                        <label class="form-check-label">
                            Can edit topic properties
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="perms-add-crt">
                        <label class="form-check-label">
                            Can remove topic
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="perms-add-cpp">
                        <label class="form-check-label">
                            Can pin posts
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="perms-add-crp">
                        <label class="form-check-label">
                            Can remove any post
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="addperms()">Add</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <? include_once "include/script.php"; ?>
</body>

</html>