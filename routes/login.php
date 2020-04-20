<?
include_once "xrouter/Router.php";

include_once "defs/forum/User.php";
include_once "defs/default.php";
include_once "include/dbcon.php";

include_once "defs/post.php";
include_once "defs/ssn.php";

$con = DBConnection::open_or_get();

if (HTTPPost::isset(["submit", "email", "password"])) {
    $user = new User();

    $user->email = HTTPPost::get("email");
    $user->password = HTTPPost::get("password");
    $data = $user->FetchAllWhere($con, "WHERE UEmail = ? AND UPassword = ?", $user->email, $user->password);

    if (count($data) == 0)
        Session::set("user-not-found");
    else
    {
        $userdata = $data[0];
        $user->id = $userdata["ID"];
        $user->name = $userdata["UName"];
        $user->surname = $userdata["USurname"];

        Session::setv("user", $user);
        Session::set("fresh-login");
        if (Session::getnd('return'))
            Router::fastswitch(Session::getv('return'));
        else
            header("Location: /");
        exit(0);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?= FORUM_BASE_TITLE ?> - Login</title>
    
    <? include_once "include/style.php"; ?>
    <? include_once "include/script-preload.php"; ?>
</head>

<body>
    <? include_once "include/header.php"; ?>
    <div class="container">
        <? if (Session::get("user-not-found")) { ?>
            <div class="alert alert-danger my-2" role="alert">
                E-Mail or password is wrong, are you registered? <a href="/register" class="alert-link">Join now</a>!
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
                    <button type="submit" class="btn btn-primary btn-block" name="submit">Login</button>
                </form>
            </div>
            <div class="col-sm m-2">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Log-in to start posting!</h5>
                        <p class="card-text">
                            You need to login to post stuff, you can view posts without logging in, hop back to <a href="/">main page</a>!<br>
                            You are not registered? <a href="/register">Join now</a>!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <? include_once "include/script.php"; ?>
</body>
</html>