<?
include_once "xrouter/Router.php";

include_once "defs/forum/Topic.php";
include_once "defs/forum/Post.php";
include_once "defs/forum/User.php";

include_once "defs/default.php";
include_once "include/dbcon.php";

include_once "defs/ssn.php";

if (!isset($_GET['id']))
    Router::fastswitch('/');

$con = DBConnection::open_or_get();

/** @var User $user */
$user = Session::getvnd("user");

if ($user == null) {
    Session::setv('return', Router::get_current_route());
    Router::fastswitch("/login");
}

$profile = new User();
$profile->id = $_GET['id'];
$profile->Fetch($con);

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
    <script>
        function editmode() {
            var edit_name = $('#user-name-edit');
            var edit_surname = $('#user-surname-edit');
            var edit_email = $('#user-email-edit');

            var name = $('#user-name');
            var surname = $('#user-surname');
            var email = $('#user-email');

            $('#btn-edit').toggleClass('d-none');
            $('#div-edit-tools').toggleClass('d-none');

            name.toggleClass('d-none');
            surname.toggleClass('d-none');
            email.toggleClass('d-none');

            edit_name.toggleClass('d-none');
            edit_surname.toggleClass('d-none');
            edit_email.toggleClass('d-none');

            edit_name.val(name.html());
            edit_surname.val(surname.html());
            edit_email.val(email.html());
        }

        function completeedit() {
            var edit_name = $('#user-name-edit');
            var edit_surname = $('#user-surname-edit');
            var edit_email = $('#user-email-edit');

            $.post('/do/update-user', { id: <?= $user->id ?>, name: edit_name.val(), surname: edit_surname.val(), email: edit_email.val() }, (r) => {
                window.location = '/user/<?= $user->id ?>';
            });
        }

        function canceledit() {
            var edit_name = $('#user-name-edit');
            var edit_surname = $('#user-surname-edit');
            var edit_email = $('#user-email-edit');

            var name = $('#user-name');
            var surname = $('#user-surname');
            var email = $('#user-email');

            name.toggleClass('d-none');
            surname.toggleClass('d-none');
            email.toggleClass('d-none');

            edit_name.toggleClass('d-none');
            edit_surname.toggleClass('d-none');
            edit_email.toggleClass('d-none');

            $('#btn-edit').toggleClass('d-none');
            $('#div-edit-tools').toggleClass('d-none');
        }
    </script>
</head>

<body>
    <? include_once "include/header.php"; ?>
    <div class="container">
        <? if (Session::get('success-update-profile')) { ?>
            <div class="alert alert-success my-sm-2" role="alert">
                Profile updated!
            </div>
        <? } ?>
        <div class="card mt-sm-2">
            <div class="card-body">
                <? if ($user != null && $user->id == $profile->id) { ?>
                    <h5 class="card-title">Your profile</h5>
                <? } else { ?>
                    <h5 class="card-title">User Profile</h5>
                <? } ?>
                <p class="card-text">
                    <b>Name:</b>
                    <input type="text" id="user-name-edit" class="form-control d-none">
                    <span id="user-name"><?= $profile->name ?></span>
                </p>
                <p class="card-text">
                    <b>Surname:</b>
                    <input type="text" id="user-surname-edit" class="form-control d-none">
                    <span id="user-surname"><?= $profile->surname ?></span>
                </p>
                <p class="card-text">
                    <b>E-Mail:</b>
                    <input type="text" id="user-email-edit" class="form-control d-none">
                    <span id="user-email"><?= $profile->email ?></span>
                </p>
                <? if ($user != null && $user->id == $profile->id) { ?>
                    <button class="btn btn-primary btn-block" onclick="editmode()" id="btn-edit"><i class="fa fa-pencil"></i> Edit</button>
                    <div class="row d-none" id="div-edit-tools">
                        <div class="col">
                            <button class="btn btn-primary btn-block" onclick="completeedit()"><i class="fa fa-save"></i> Save</button>
                        </div>
                        <div class="col">
                            <button class="btn btn-danger btn-block" onclick="canceledit()"><i class="fa fa-trash"></i> Cancel</button>
                        </div>
                    </div>
                <? } ?>
            </div>
        </div>
    </div>
    <? include_once "include/script.php"; ?>
</body>

</html>