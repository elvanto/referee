<?php

namespace Legacy;

class Functions
{
    private $db;
    private $user;

    function __construct()
    {
        global $db, $user;

        $this->db = $db;
        $this->user = $user;
    }

    public function func_a($sql, $user)
    {
        $this->db->query($sql);
        $user->actions();
    }

    public function func_b()
    {
        $this->db->delete('items', array('user_id' => $this->user->id));
    }

    public static function func_c()
    {
        return 404;
    }
}
