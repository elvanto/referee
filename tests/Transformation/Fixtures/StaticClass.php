<?php

namespace Legacy;

class Functions
{
    public static function func_a($sql, $user)
    {
        global $db;

        $db->query($sql);
        $user->actions();
    }

    public static function func_b()
    {
        global $db, $user;

        $db->delete('items', array('user_id' => $user->id));
    }

    public static function func_c()
    {
        return $GLOBALS['account_id'];
    }
}
