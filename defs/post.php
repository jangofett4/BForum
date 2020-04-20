<?

class HTTPPost {
    public static function isset(array $values) {
        foreach ($values as $val)
            if (!isset($_POST[$val]))
                return false;
        return true;
    }

    public static function get(string $idx) {
        return $_POST[$idx];
    }

    public static function set(string $idx, $val) {
        $_POST[$idx] = $val;
    }
}