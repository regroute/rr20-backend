<?php

// namespace App;
use Illuminate\Database\Eloquent\Model as Eloquent;
// use Illuminate\Database\Capsule\Manager as Capsule;

class Cc_trunk extends Eloquent
{
    
    protected $table = 'cc_trunk';
    public $timestamps = false;

    public function scopeGetSingleDestination($query, $agi_extension) {
        
       return $query->select('id_trunk','id_assignment','name_assignment','destination_assignment','type_assignment','note_assignment')
                ->join('cc_assignment', 'cc_assignment.id_assignment', '=', 'cc_trunk.assignment')
                ->where('did_trunk', $agi_extension)
                ->where('type','IN')
                ->where('active_assignment',1)
                ->first();
         // ddd(Capsule::getQueryLog());
    }
}

