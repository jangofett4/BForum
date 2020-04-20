<?
include_once "xrouter/Router.php";

include_once "defs/forum/User.php";
include_once "defs/ssn.php";

/** @var User $user */
$user = Session::getvnd("user");

?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="/">BForum</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarContent">
    <ul class="navbar-nav mr-auto">
      <? if ($user == null) { ?>
        <li class="nav-item">
          <a href="/login" class="nav-link">Login</a>
        </li>
        <li class="nav-item">
          <a href="/register" class="nav-link">Register</a>
        </li>
      <? } else { ?>
        <? Session::setv('return', Router::get_current_route()); ?>
        <li class="nav-item">
          <a href="/user/<? echo $user->id; ?>" class="nav-link">My Profile</a>
        </li>
        <li class="nav-item">
          <a href="/logout" class="nav-link">Logout</a>
        </li>
      <? } ?>
    </ul>
    <form class="form-inline my-2 my-lg-0" action="/search" method="post">
      <input class="form-control mr-sm-2" type="search" placeholder="Search for topics" aria-label="Search" name="term">
      <button class="btn btn-outline-success my-2 my-sm-0" type="submit"><i class="fa fa-search"></i></button>
    </form>
  </div>
</nav>