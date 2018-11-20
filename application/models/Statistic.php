<?php
use yii\db\Query;

class StatisticModel
{
    public static $tableName = HI\TableName\STATISTIC;

    private static function date($statDate)
    {
        return Accessor::either($statDate, date('Ymd', time()));
    }

    public static function insert($type, $payload, $statDate = null)
    {
        $command = Yii::$app->db->createCommand("REPLACE INTO " . self::$tableName . 
            " (stat_date, type, payload) VALUES(:statDate, :type, :payload)");
        $command->->bindValue(":statDate", self::date($statDate));
        $command->->bindValue(":type", Preconditions::checkNotEmpty($type));
        $command->->bindValue(":payload", Preconditions::checkNotEmpty($payload));
        return $command::execute();
    }

    public static function select($type, $statDate = null, $fields = "*") {
        $query = (new Query())->select($fields)->from(self::tableName)->where('type' => $type);
        if (Predicates::isNotNull($statDate)) {
            $query->andWhere('stat_date' => $statDate);
        }
        return $query->queryAll();
    }
}
