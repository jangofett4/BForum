<?
include_once "xrouter/Router.php";
include_once "defs/ssn.php";

$admin = Session::getvnd('admin');
if ($admin != null)
    Router::fastswitch('/panel/dashboard');
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
    <div class="content">
        <div class="row" style="height: 100vh">
            <div class="col-sm-4 mx-auto my-auto">
                <? if (Session::get("admin-user-not-found")) { ?>
                    <div class="alert alert-danger my-sm-2" role="alert">
                        Username or password is incorrect!
                    </div>
                <? } ?>
                <div class="card text-center">
                    <div class="card-header">
                        Login to Admin Panel
                    </div>
                    <div class="card-body">
                        <form method="post" action="/do/login-admin">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Email address</label>
                                <input type="email" class="form-control" name="admin-email" placeholder="Enter email">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputPassword1">Password</label>
                                <input type="password" class="form-control" name="admin-password" placeholder="Password">
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>