<?php

namespace Legacy;

class Functions
{
    private $db;
    private $user;
    private $account_id;

    function __construct()
    {
        global $db, $user;

        $this->db = $db;
        $this->user = $user;
        $this->account_id = $GLOBALS['account_id'];
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

    public function func_c()
    {
        return $this->account_id;
    }
}
