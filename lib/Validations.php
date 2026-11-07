<?php

namespace Lib;

use Core\Database\Database;

class Validations
{
    public static function notEmpty($attribute, $obj)
    {
        if ($obj->$attribute === null || $obj->$attribute === '') {
            $obj->addError($attribute, 'não pode ser vazio!');
            return false;
        }

        return true;
    }

    public static function passwordConfirmation($obj)
    {
        if ($obj->password !== $obj->password_confirmation) {
            $obj->addError('password', 'as senhas devem ser idênticas!');
            return false;
        }

        return true;
    }

    public static function uniqueness($fields, $object)
    {
        $dbFieldsValues = [];
        $objFieldValues = [];

        if (!is_array($fields)) {
            $fields = [$fields];
        }

        if (!$object->newRecord()) {
            $dbObject = $object::findById($object->id);

            foreach ($fields as $field) {
                $dbFieldsValues[] = $dbObject->$field;
                $objFieldValues[] = $object->$field;
            }

            if (
                !empty($dbFieldsValues) &&
                !empty($objFieldValues) &&
                $dbFieldsValues === $objFieldValues
            ) {
                return true;
            }
        }

        $table = $object::table();
        $conditions = implode(' AND ', array_map(fn($field) => "{$field} = :{$field}", $fields));

        $sql = <<<SQL
            SELECT id FROM {$table} WHERE {$conditions};
        SQL;

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);

        foreach ($fields as $field) {
            $stmt->bindValue($field, $object->$field);
        }

        $stmt->execute();

        if ($stmt->rowCount() !== 0) {
            foreach ($fields as $field) {
                $object->addError($field, 'já existe um registro com esse dado');
            }
            return false;
        }

        return true;
    }

    public static function isPositive(string $atributte, $obj)
    {
        if($obj->$atributte !== '' && $obj->$atributte < 0){
            $obj->addError($atributte, 'Não é permitido valores negativos');
            return false;
        }

        return true;
    }

    public static function isInteger($atributte, $obj)
    {
        if($obj->$atributte !== '' && filter_var($obj->$atributte, FILTER_VALIDATE_INT) === false){

            $obj->addError($atributte, 'Digite um número inteiro');
            return false;
        }

        return true;
    }

    public static function isNumber($attribute, $obj):bool
    {
        $value = $obj->$attribute;

        if ($obj->$attribute !== '' && !is_numeric($value)) {
            $obj->addError($attribute, 'Somente números são permitidos');
            return false;
        }

        return true;
    }

    public static function isMaxLarger($minAttribute,$maxAttribute, $obj){
        $max = (int) $obj->$maxAttribute;

        if($obj->$minAttribute > $max){
            $obj->addError($maxAttribute, "O valor deve ser maior que o mínimo");
            return false;
        }

        return true;
    }

}
