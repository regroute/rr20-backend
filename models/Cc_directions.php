<?php

// namespace App;
use Illuminate\Database\Eloquent\Model as Eloquent;

// use Illuminate\Database\Capsule\Manager as Capsule;

class Cc_directions extends Eloquent
{
    
    protected $table = 'cc_directions';
    public $timestamps = false;
    
    public function scopeGetDirections($query) {
        
           return $query->where('active_directions', 1)
                  ->remember(cache_global)
                  ->orderBy('order_direction','asc')
                  ->get();

    }
}

//         $sth = $this->con->prepare("
// SELECT `id_blacklist`,`id_assignment`,`name_assignment`,`destination_assignment`,`type_assignment`,`note_assignment`,`note_blacklist` FROM `" . $table_black . "`
// INNER JOIN `" . $table_assignment . "` ON `" . $table_assignment . "`.`id_assignment` = `" . $table_black . "`.`assignment`
// WHERE `" . $table_black . "`.`nomer_blacklist`=:callerid
// AND  `" . $table_assignment . "`.`active_assignment` = '1'
// AND  `" . $table_black . "`.`active_blacklist` = '1'
// AND  (`" . $table_black . "`.`direction` = :type  OR `" . $table_black . "`.`direction`= 'ALL')
