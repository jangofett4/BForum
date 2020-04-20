<?

abstract class DataPoint
{
    public function Push(\PDO $con)
    {
        throw new Exception("Push is not implemented for " . __CLASS__);
    }

    public function Fetch(\PDO $con)
    {
        throw new Exception("Fetch is not implemented for " . __CLASS__);
    }

    public function FetchAllWhere(\PDO $con, string $where, ...$args)
    {
        throw new Exception("Fetch all (where) is not implemented for " . __CLASS__);
    }

    public function Update(\PDO $con)
    {
        throw new Exception("Update is not implemented for " . __CLASS__);
    }

    public static function FetchAll(\PDO $con)
    {
        throw new Exception("Fetch all is not implemented for " . __CLASS__);
    }

    public static function Check(\PDO $con)
    {
        throw new Exception("Check is not implemented for " . __CLASS__);
    }
}
