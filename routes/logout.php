<?

include_once "defs/ssn.php";
include_once "xrouter/Router.php";

Session::unset('user');
if (Session::getnd('return'))
    Router::fastswitch(Session::getv('return'));
else
    Router::fastswitch('/');

?>