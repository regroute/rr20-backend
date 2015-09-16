<?php 

// namespace App;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;

class Cc_call extends Eloquent {

    protected $table = 'cc_call';
	  public $timestamps = false;



    public function scopeGetBill($query, $agi_uniqueid, $pole_bill) {
        
           return $query->select($pole_bill, 'to_id_tariff_call','to_id_custariff_call','id','starttarif','typetarif')
                   ->join('cc_tariff', 'cc_call.to_id_tariff_call', '=', 'cc_tariff.id_tariff')
                   ->where('uniqueid', $agi_uniqueid)
                   ->where('type', 'out')
                   ->where('terminatecauseid', '1')
                   ->where('cc_call.'.$pole_bill, '>', 'cc_tariff.starttarif')
                   ->whereNotNull('to_id_custariff_call')
                   ->whereNotNull('to_id_tariff_call')
                   ->first();
    }  

    public function scopeGetBillLocal($query, $agi_uniqueid, $pole_bill) {
        
           return $query->select($pole_bill,'to_id_custariff_call','id')
                   ->where('uniqueid', $agi_uniqueid)
                   ->where('type', 'local')
                   ->where('terminatecauseid', '1')
                   ->whereNotNull('to_id_custariff_call')
                   ->first();

           // ddd(Capsule::getQueryLog());       
    }

}
