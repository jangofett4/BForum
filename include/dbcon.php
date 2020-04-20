<?

const DB_HOST = "localhost";
const DB_USER = "root";
const DB_PASS = "";
const DB_DATA = "BForum";
const DB_CHAR = "utf8mb4";

class DBConnection {
    private static $connection;
    private static $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_DATA . ";charset=" . DB_CHAR;
    private static $options = [
        PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES      => false
    ];

    public static function open_or_get() : PDO {
        if (!isset(DBConnection::$connection)) {
            try {
                DBConnection::$connection = new PDO(DBConnection::$dsn, DB_USER, DB_PASS, DBConnection::$options);
                return DBConnection::$connection;
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return DBConnection::$connection;
    }
}

?>