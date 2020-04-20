<?
include_once "xrouter/Router.php";

include_once "include/dbcon.php";

include_once "defs/default.php";
include_once "defs/forum/User.php";
include_once "defs/post.php";
include_once "defs/ssn.php";

$con = DBConnection::open_or_get();

if (HTTPPost::isset(["submit", "email", "password", "name", "surname"])) {
    $user = new User();

    $user->email = HTTPPost::get("email");
    $user->password = HTTPPost::get("password");
    $user->name = HTTPPost::get("name");
    $user->surname = HTTPPost::get("surname");

    try {
        $user->Push($con);
        Session::setv("user", $user);
        Session::set("fresh-register");
        Router::fastswitch('/');
    } catch (\PDOException $e) {
        if ($e->getCode() == 23000)
            Session::set("existing-user");
        else
            throw $e; // Unknown error, throw it
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?= FORUM_BASE_TITLE ?> - Register</title>

    <? include_once "include/style.php"; ?>
    <? include_once "include/script-preload.php"; ?>
</head>

<body>
    <? include_once "include/header.php"; ?>
    <div class="container">
        <? if (Session::get("existing-user")) { ?>
            <div class="alert alert-danger my-2" role="alert">
                A user with same e-mail already exists, maybe you wanted to <a href="login.php" class="alert-link">log in</a>?
            </div>
        <? } ?>
        <div class="row p-sm-2">
            <div class="col-sm">
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Email address</label>
                        <input type="email" class="form-control" aria-describedby="email" name="email" required>
                        <small id="email" class="form-text text-muted">We won't share your e-mail</small>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Surname</label>
                        <input type="text" class="form-control" name="surname" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" name="submit">Register</button>
                </form>
            </div>
            <div class="col-sm m-2">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Why you should join <?= FORUM_NAME ?>?</h5>
                        <p class="card-text">
                            <?= FORUM_NAME ?> is a forum created to ask questions and get answers fast.<br>
                            You can join absolutely free today to get answers!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <? include_once "include/script.php"; ?>
</body>

</html>