<?php

namespace Controllers;

class UserController
{
    private $db_conn;
    
    public function __construct($db_conn)
    {
        $this->db_conn = $db_conn;
    }

    public function create()
    {
        echo "create user";

    }
}