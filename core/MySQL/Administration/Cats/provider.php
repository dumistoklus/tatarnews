<?php
class CatsManager
{
    public static function create($name) {
        $name = filter_string($name);

        return query('INSERT INTO '.PREFIX.'cats (name) VALUES ("'.$name.'")');
    }

    public static function delete($id) {
        return affectedRowsQuery('DELETE FROM '.PREFIX.'cats WHERE id = '.(int)$id);
    }
}