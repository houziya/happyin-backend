<?php
use Yaf\Dispatcher;
use Yaf\Registry;
use yii\db\Query;
use yii\db\Expression;
class DataBase
{
    /*
     * 向某表插入数据
     *  */
    public static function doTableInsert($table, $parameter, $isReturnInsertId = 0) {
        $connection = Yii::$app->db;
        $resInsert = $connection->createCommand()->insert($table, $parameter)->execute();
        if($isReturnInsertId > 0) {
            return $connection->getLastInsertID();
        } else {
            return $resInsert;
        }
    }
    
    /*
     * 向某表查询数据(可传指定字段名)
     *  */
    public static function getTableFields($table, $whereFileds, $whereArray, $fields = null) {
        if(Predicates::isArray($fields)) {
            $fields = implode(",", $fields);
            return (new Query())
            ->select(Predicates::isNull($fields) ? "*" : $fields)
            ->from($table)
            ->where($whereFileds, $whereArray)
            ->one();
        }else {
            return (new Query())
            ->select(Predicates::isNull($fields) ? "*" : $fields)
            ->from($table)
            ->where($whereFileds, $whereArray)
            ->one()[$fields];
        }
    
    }
    
    /*
     * 向某表查询数据(rows)
     *  */
    public static function getTableDataRows($table, $whereFileds, $whereArray, $select = null) {
        return (new Query())
        ->select(Predicates::isNull($select) ? "*" : $select)
        ->from($table)
        ->where($whereFileds, $whereArray)
        ->all();
    }
    
    /*
     * 某表更改数据
     *   */
    public static function doTableUpdate($table, $updateFileds, $whereFileds, $whereArray = NULL)
    {
        if (empty($whereArray)) {
            return Yii::$app->db->createCommand()->update($table, $updateFileds, $whereFileds)->execute();
        }
        return Yii::$app->db->createCommand()->update($table, $updateFileds, $whereFileds, $whereArray)->execute();
    }
    

    /*
     * 获得指定表,指定条件的数据count
     * */
    public static function getTableWhereCount($table, $whereField, $whereArray) {
        return (new Query())->from($table)->where($whereField, $whereArray)->count();
    }
}
?>