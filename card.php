<?php

class Card
{
    public $password = "0000";
    public $money = 10000;

    public function __construct($password, $money)
    {
        $this->password = $password;
        $this->money = $money;
    }

}