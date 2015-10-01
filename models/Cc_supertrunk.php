<?php

// namespace App;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;
// ddd(Capsule::getQueryLog());
 

class Cc_supertrunk extends Eloquent
{
    
    protected $table = 'cc_supertrunk';
    public $timestamps = false;
    
    public function scopeGetTrunks($query, $id_directions) {
        
             $QQ= $query->where('directions_id', $id_directions)
                  ->join('cc_directions_trunk', 'cc_supertrunk.id_supertrunk', '=', 'cc_directions_trunk.trunk_id')
                  ->join('cc_trunk', 'cc_supertrunk.id_supertrunk', '=', 'cc_trunk.supertrunk')
                  ->join('cc_tariff', 'cc_supertrunk.id_supertrunk', '=', 'cc_tariff.to_id_supertrunk')
                  ->where('type', 'out')
                  ->where('active_supertrunk', 1)
                  ->where('active_trunk', 1)
                  ->orderBy('order_supertank','ASC')
                  ->orderBy('skill_','DESC')
                  ->remember($GLOBALS['cache_global'])
                  ->get();

           // ddd(json_decode(json_encode($QQ)));
            return json_decode(json_encode($QQ) , true);      

    }
}

/*
SELECT * FROM `$s_table`
INNER JOIN `$dire_table` ON `$s_table`.`id_supertrunk` = `$dire_table`.`trunk_id`
INNER JOIN `$trunk_table` ON `$s_table`.`id_supertrunk` = `$trunk_table`.`supertrunk`
INNER JOIN `$tarif_table` ON `$s_table`.`id_supertrunk` = `$tarif_table`.`to_id_supertrunk`
WHERE `$dire_table`.`directions_id`=:directions
AND  `$s_table`.`active_supertrunk` = 1
AND  `$trunk_table`.`type` = 'out'
AND  `$trunk_table`.`active_trunk` = 1
ORDER BY `order_supertank` ASC, `skill_` DESC ");
*/