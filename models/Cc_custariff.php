<?php

// namespace App;
use Illuminate\Database\Eloquent\Model as Eloquent;

// use Illuminate\Database\Capsule\Manager as Capsule;

class Cc_custariff extends Eloquent
{
    
    protected $table = 'cc_custariff';
    public $timestamps = false;
    
    public function scopeGetCusTariff($query, $id_customer) {
        
            $qq = $query->where('to_id_customer', $id_customer)
                  ->where('active_custariff', 1)
                  ->remember($GLOBALS['cache_global'])
                  ->orderBy('id_custariff','asc')
                  ->get();

            if (is_null($qq)) {
                return NULL;
            }
            
            foreach ($qq as $row) {

                $rr[] = array(
                    'id_custariff' => $row->id_custariff,
                    'regular_custariff' => $row->regular_custariff,
                    'name_custariff' => $row->name_custariff,
                    'limit' => $row->limit,
                    'whattdo' => $row->whattdo,
                    'post_prefix_plass' => $row->post_prefix_plass,
                    'limit_perse' => ($row->limit_start) ? round(($row->limit / $row->limit_start) * 100) : 0
                );
            }
            return $rr;
    }
}

