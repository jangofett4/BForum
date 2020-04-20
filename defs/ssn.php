<?

if (session_status() == PHP_SESSION_NONE)
    session_start();

class Session
{
    public static function unset($key)
    {
        if (isset($_SESSION[$key]))
            unset($_SESSION[$key]);
    }
    
    public static function get($key) : bool
    {
        if (isset($_SESSION[$key]))
        {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    public static function getnd($key) : bool
    {
        return isset($_SESSION[$key]);
    }

    public static function getv($key)
    {
        if (isset($_SESSION[$key]))
        {
            $val = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $val;
        }
        return null;
    }

    public static function getvnd($key)
    {
        if (isset($_SESSION[$key]))
            return $_SESSION[$key];
        return null;
    }

    public static function set($key)
    {
        $_SESSION[$key] = true;
    }

    public static function setv($key, $value)
    {
        $_SESSION[$key] = $value;
    }
}

?>