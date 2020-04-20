<?

include_once 'include/dbcon.php';

include_once 'defs/forum/Comment.php';
include_once 'defs/forum/Post.php';
include_once 'defs/forum/PostFlags.php';
include_once 'defs/forum/Topic.php';
include_once 'defs/forum/User.php';

$con = DBConnection::open_or_get();

User::Check($con);
Topic::Check($con);
Post::Check($con);
Comment::Check($con);
PostFlags::Check($con);