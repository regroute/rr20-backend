<?php

// namespace App;
use Illuminate\Database\Eloquent\Model as Eloquent;
// use Illuminate\Database\Capsule\Manager as Capsule;

class Cc_blacklist extends Eloquent
{
    
    protected $table = 'cc_blacklist';
    public $timestamps = false;
    
    public function scopeGetExtenBlackList($query, $agi_extension, $type) {
        
       $query->select('id_blacklist','id_assignment','name_assignment','destination_assignment','type_assignment','note_assignment','note_blacklist');
       $query->join('cc_assignment', 'cc_assignment.id_assignment', '=', 'cc_blacklist.assignment');
       return  $query-> where('nomer_blacklist', $agi_extension)
              ->where('active_blacklist',1)
              ->where('active_assignment',1)
              ->where(function($query) use ($type) 
	            {
	                $query->where('direction', 'ALL')
	                      ->orWhere('direction', $type);
	            }) 
              ->first();
        // ddd(Capsule::getQueryLog());
    }



}


//         $sth = $this->con->prepare("
// SELECT `id_blacklist`,`id_assignment`,`name_assignment`,`destination_assignment`,`type_assignment`,`note_assignment`,`note_blacklist` FROM `" . $table_black . "`
// INNER JOIN `" . $table_assignment . "` ON `" . $table_assignment . "`.`id_assignment` = `" . $table_black . "`.`assignment`
// WHERE `" . $table_black . "`.`nomer_blacklist`=:callerid
// AND  `" . $table_assignment . "`.`active_assignment` = '1'
// AND  `" . $table_black . "`.`active_blacklist` = '1'
// AND  (`" . $table_black . "`.`direction` = :type  OR `" . $table_black . "`.`direction`= 'ALL')