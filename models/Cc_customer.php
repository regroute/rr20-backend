<?php

// namespace App;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Cc_customer extends Eloquent
{
    
    protected $table = 'cc_customer';
    protected $primaryKey = 'id_customer';
    public $timestamps = false;
}
