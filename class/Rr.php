<?php

// namespace App;
// use Illuminate\Database\Eloquent\Model as Eloquent;

define('debug', 4);
define('info', 3);
define('notice', 2);
define('warning', 1);
define('error', 0);

use Illuminate\Log\Writer;
use Monolog\Logger;

// class Rr extends AGI
class Rr
{
    public $agiconfig;
    public $uniqueid;
    public $extension;
    public $call;
    public $cust_limit_dial;
    public $trunk_limit_dial;
    public $unixdatatime;
    
    public function __construct(AGI $agi) {
        
        include (dirname(__FILE__) . "/../config/config.php");
        $this->config = $config;
        $GLOBALS['cache_global']=$config['cache_global'];
        $options = Cc_options::select('options', 'name_options')->where('group_options', 'agi')->remember((int)$GLOBALS['cache_global'])->get();
        foreach ($options as $row) {
            $r[$row->name_options] = $row->options;
        }
        $this->agiconfig = $r;
        $this->agi = $agi;
        $this->dialstatus_rev_list = $this->getDialStatus_Revert_List();
    }
    
    public static function getDialStatus_Revert_List() {
        $dialstatus_rev_list = array();
        $dialstatus_rev_list["ERROR"] = 10;
        $dialstatus_rev_list["ANSWER"] = 1;
        $dialstatus_rev_list["BUSY"] = 2;
        $dialstatus_rev_list["NOANSWER"] = 3;
        $dialstatus_rev_list["CANCEL"] = 4;
        $dialstatus_rev_list["CONGESTION"] = 5;
        $dialstatus_rev_list["CHANUNAVAIL"] = 6;
        $dialstatus_rev_list["DONTCALL"] = 7;
        $dialstatus_rev_list["TORTURE"] = 8;
        $dialstatus_rev_list["INVALIDARGS"] = 9;
        
        return $dialstatus_rev_list;
    }
    
    function loger($level, $file, $line, $string, $ar = NULL) {
        
        // если системный level ниже notice, то при включеном KINT_DUMP, ставим уровень notice
        if ($GLOBALS['KINT_DUMP'] && $this->agiconfig['verbosity_level'] < 2) {
            $this->agiconfig['verbosity_level'] = 2;
        }
        
        if ($this->agiconfig['verbosity_level'] < $level) {
            return;
        }
        
        if ($GLOBALS['KINT_DUMP']) {~d("$level | $file | $line");
            if (!is_null($string)) d($string);
            if (!is_null($ar)) d($ar);
            return;
        }
        
        if (!is_null($string)) $this->agi->verbose($string);
        if (!is_null($ar)) $this->agi->verbose($ar);
        
        if ((int)$this->agiconfig['logging_write_file'] === 1) {
            
            $logger = new Writer(new Logger('local'));
            $logger->useFiles($this->config['logs_patch']);
            
            if (!is_null($ar)) {
                
                $string.= "\n";
                $string.= var_export($string, true);
            }
            
            switch ($level) {
                case 'error':
                    $logger->error("[" . $this->uniqueid . "] [$file] [$line]: -- $string");
                    break;

                case 'warning':
                    $logger->warning("[" . $this->uniqueid . "] [$file] [$line]: -- $string");
                    break;

                case 'notice':
                    $logger->notice("[" . $this->uniqueid . "] [$file] [$line]: -- $string");
                    break;

                case 'info':
                    $logger->info("[" . $this->uniqueid . "] [$file] [$line]:  $string");
                    break;

                default:
                    $logger->debug("[" . $this->uniqueid . "] [$file] [$line]: $string");
                    break;
            }
        }
    }
    
    function stoperror($file, $line, $er, $rdump = false) {
        
        $this->loger(error, $file, $line, $er);
        if ($rdump) {
            $this->loger(error, $file, $line, $rdump);
        }
        
        $this->call['terminatecauseid'] = 10;
        $this->call['userfield'] = $er;
        
        if ($GLOBALS['KINT_DUMP']) {
            exit(0);
        }
        
        $this->agi->exec($this->config['stoperror_context'] . " " . $this->config['stoperror_extension']);
        $this->logercall();
        exit();
    }
    
    function actionEntryCall($destinations, $agi_extension) {
        
        if (!isset($destinations->type_assignment) OR !in_array($destinations->type_assignment, ['goto', 'dial', 'hangup', 'dial_replacing'])) {
            $mes = "Ошибка отбработки функции actionEntryCall; Контекст неверен";
            $this->call['userfield'] = $mes;
            $this->call['terminatecauseid'] = 10;
            $this->stoperror(__FILE__, __LINE__, $mes);
        }
        
        switch ($destinations->type_assignment) {
            case 'goto':
                $command_goto = "Goto " . $destinations->destination_assignment;
                $rx = $this->agi->exec($command_goto);
                $this->loger(debug, __FILE__, __LINE__, $command_goto);
                $this->loger(debug, __FILE__, __LINE__, "result goto: " . $rx['data']);
                break;

            case 'dial_replacing':
                if ($this->agiconfig['record_call_in'] && !empty($this->uniqueid)) {
                    $this->agi->exec("StopMixMonitor");
                    $fn = date("d-m-Y", time()) . "/" . $agi_extension . "/" . $this->uniqueid . '.' . $this->agiconfig['monitor_formatfile'];
                    $this->agi->set_variable('CDR(userfield)', "audio:" . $this->agiconfig['monitor_path_in'] . $fn);
                    $this->call['userfield'] = "audio:" . $this->agiconfig['monitor_path_in'] . $fn;
                    $command_mixmonitor = "MixMonitor " . $this->agiconfig['monitor_path_in'] . $fn . "," . $this->agiconfig['mixmon_post'];
                    $rx = $this->agi->exec($command_mixmonitor);
                    $this->loger(debug, __FILE__, __LINE__, $command_mixmonitor);
                    $this->loger(debug, __FILE__, __LINE__, "result MixMonitor: " . $rx['data']);
                }
                
                $time2call = $this->agiconfig['maxlong_call_in'];
                $dialparams = str_replace("%timeout%", $time2call, $this->agiconfig['dialcommand_param_in']);
                
                if (!is_null($agi_extension)) {
                    $dialstr = str_replace("%did%", $agi_extension, $destinations->destination_assignment . $dialparams);
                    $this->loger(debug, __FILE__, __LINE__, "Меняем в строке DIAL %did% на :" . $agi_extension);
                } 
                else {
                    $mes = " agi_extension = NULL;hangup;";
                    $this->loger(error, __FILE__, __LINE__, $mes);
                    $this->agi->verbose($mes);
                    break;
                }
                $this->dialstatus = $this->run_dial($dialstr);
                
                if ($this->dialstatus === "ANSWER") {
                    if (isset($_SERVER["argv"][3]) && $_SERVER["argv"][3]) {
                        $this->call['billsec'] = 10;
                        $this->loger(info, __FILE__, __LINE__, "ANSWEREDTIME(debug):" . $this->call['billsec']);
                    } 
                    else {
                        $this->call['billsec'] = $this->agi->get_variable('ANSWEREDTIME', true);
                        $this->loger(info, __FILE__, __LINE__, "ANSWEREDTIME(billsec):" . $this->call['billsec']);
                    }
                } 
                else {
                    
                    if ($this->agiconfig['record_call_in'] === 1) {
                        $this->agi->exec("StopMixMonitor");
                        $this->loger(info, __FILE__, __LINE__, "StopMixMonitor uniqueid:" . $this->uniqueid);
                        exec("rm " . $this->agiconfig['monitor_path_in'] . $fn);
                    }
                }
                break;

            case 'dial':
                if ($this->agiconfig['record_call_in'] && !empty($this->uniqueid)) {
                    $this->agi->exec("StopMixMonitor");
                    $fn = date("d-m-Y", time()) . "/" . $agi_extension . "/" . $this->uniqueid . '.' . $this->agiconfig['monitor_formatfile'];
                    $this->agi->set_variable('CDR(userfield)', "audio:" . $this->agiconfig['monitor_path_in'] . $fn);
                    $this->call['userfield'] = "audio:" . $this->agiconfig['monitor_path_in'] . $fn;
                    $command_mixmonitor = "MixMonitor " . $this->agiconfig['monitor_path_in'] . $fn . "," . $this->agiconfig['mixmon_post'];
                    $rx = $this->agi->exec($command_mixmonitor);
                    $this->loger(debug, __FILE__, __LINE__, $command_mixmonitor);
                    $this->loger(debug, __FILE__, __LINE__, "result MixMonitor: " . $rx['data']);
                }
                
                $time2call = $this->agiconfig['maxlong_call_in'];
                
                $dialparams = str_replace("%timeout%", $time2call, $this->agiconfig['dialcommand_param_in']);
                $dialstr = $destinations->destination_assignment . $dialparams;
                $this->dialstatus = $this->run_dial($dialstr);
                
                if ($this->dialstatus === "ANSWER") {
                    if (isset($_SERVER["argv"][3]) && $_SERVER["argv"][3]) {
                        $this->call['billsec'] = 10;
                        $this->loger(info, __FILE__, __LINE__, "ANSWEREDTIME(debug):" . $this->call['billsec']);
                    } 
                    else {
                        $this->call['billsec'] = $this->agi->get_variable('ANSWEREDTIME', true);
                        $this->loger(info, __FILE__, __LINE__, "ANSWEREDTIME(billsec):" . $this->call['billsec']);
                    }
                } 
                else {
                    
                    if ($this->agiconfig['record_call_in'] === 1) {
                        $this->agi->exec("StopMixMonitor");
                        $this->loger(info, __FILE__, __LINE__, "StopMixMonitor uniqueid:" . $this->uniqueid);
                        exec("rm " . $this->agiconfig['monitor_path_in'] . $fn);
                    }
                }
                break;

            case 'hangup':
                $this->call['billsec'] = 0;
                $this->dialstatus = 7;
                $this->loger(info, __FILE__, __LINE__, "hangup() uniqueid:" . $this->uniqueid);
                break;
        }
        
        $this->logedr();
        $this->agi->hangup();
        
        exit();
    }
    
    function get_channels_busy($asm) {
        
        $asm->connect();
        $res = $asm->Command('core show version');
        if (empty($res['data'])) {
            $this->loger(error, __FILE__, __LINE__, "AMI; Проблемы с подключеним к серверу");
            return NULL;
        } 
        else {
            $this->loger(debug, __FILE__, __LINE__, "AMI; Version Asterisk: " . $res['data']);
        }
        
        $res = $asm->Command("core show channels concise");
        $asm->disconnect();
        $responselines = @explode("\n", $res['data']);
        $this->loger(debug, __FILE__, __LINE__, "core show channels concise:");
        $this->loger(debug, __FILE__, __LINE__, $responselines);
        $lines = array();
        foreach ($responselines as $l) {
            if (preg_match("/^Response/", $l)) {
                continue;
            };
            if (preg_match("/^Privilege/", $l)) {
                continue;
            };
            if (preg_match("/^(SIP|IAX)/", $l)) {
                $lines[] = $l;
            }
        }
        $this->loger(debug, __FILE__, __LINE__, "Список активных каналов:");
        $this->loger(debug, __FILE__, __LINE__, $lines);
        
        $channels = array();
        $accounts = array();
        foreach ($lines as $l) {
            
            //sip аккаунты
            if (preg_match($this->config['channels_custom'], trim($l) , $matches)) {
                $channels[] = $matches[2];
            }
            
            //accounts
            if (preg_match($this->config['channels_accounts'], trim($l) , $matches)) {
                $accounts[] = $matches[3];
            }
        }
        
        $rr = array(
            'chan' => array_count_values($channels) ,
            'account' => array_count_values($accounts)
        );
        $this->loger(debug, __FILE__, __LINE__, "Результат работы get_channels_busy:");
        $this->loger(debug, __FILE__, __LINE__, $rr);
        
        return $rr;
    }
    
    public function calculate_timedial($limit, $whattdo) {
        
        $max_dial = $this->agiconfig['max_dial'];
        
        if ($limit <= $this->agiconfig['min_dial']) {
            
            // Проверка на действие whattdo
            if ($whattdo === "do_nothing") {
                $this->loger(info, __FILE__, __LINE__, "В данном транке нeту свободных минут, но по нем можно звонить ставим max_dial: $max_dial c");
                return $max_dial;
            } 
            elseif ($whattdo === "do_notify") {
                return $max_dial;
                $this->loger(info, __FILE__, __LINE__, "В данном транке нeту свободных минут, но по нем можно звонить, уведомляем, ставим max_dial $max_dial c");
            } 
            elseif ($whattdo === "do_deny") {
                $this->loger(info, __FILE__, __LINE__, "В данном транке нeту свободных минут выключаем");
                return NULL;
            }
        }
        
        // Минут многовато
        elseif ($limit > $max_dial) {
            $this->loger(debug, __FILE__, __LINE__, "В данном транке многовато минут ставим max_dial: $max_dial c");
            return $max_dial;
        }
        
        //Те посередине, отдаем сколько есть
        else {
            return $limit;
            $this->loger(debug, __FILE__, __LINE__, "Ставим max_dial: $max_dial c");
        }
        return NULL;
    }
    
    public function showtrunk($level, $title, $artrunks, $agi_accountcode = '') {
        
        if ($this->agiconfig['verbosity_level'] >= $level) {
            $mes = array();
            $i = 0;
            foreach ($artrunks as $value) {
                $mes[$i] = 'supertrunk: ' . $value['name_supertrunk'];
                $mes[$i].= ' | trunk: ' . $value['name_trunk'];
                $mes[$i].= ' | DIAL: ' . $value['tprefix'] . '/' . $value['ext_trunk'] . "/" . $value['dial_prefix'] . " {NOMER}";
                $mes[$i].= ' | tariff: ' . $value['name_tariff'] . "|  остаток: " . round($value['limit_'] / 60);
                $mes[$i].= " (".$value['limit_perse'].") ";
                $mes[$i].= ' | каналов: ' . $value['nchannels'];
                if (isset($value['limit_dial'])) $mes[$i].= ' | мак t звонка: ' . $value['limit_dial'];
                $mes[$i].= ' | skill: ' . $value['skill_'];
                $i++;
            }
            $this->loger($level, __FILE__, __LINE__, $title . "  accountcode:" . $agi_accountcode, $mes);
        }
    }
    public function run_dial($dialstr) {
        
        if (isset($_SERVER["argv"][3]) && $_SERVER["argv"][3]) {
            $this->loger(notice, __FILE__, __LINE__, "================: TEST DIAL  $dialstr ;  DIALSTATUS: " . $_SERVER["argv"][3]);
            return $_SERVER["argv"][3];
        }
        
        $this->call['startdialtime'] = date("Y-m-d H:i:s", time());
        $this->loger(debug, __FILE__, __LINE__, "$dialstr");
        
        $res_dial = $this->agi->exec("DIAL $dialstr");
        $this->dialstatus = $this->agi->get_variable("DIALSTATUS", true);
        $this->loger(info, __FILE__, __LINE__, "DIALSTATUS: " . $this->dialstatus);
        
        return $this->dialstatus;
    }
    
    public function dial_function($trunks, $agi_extension, $cust_limit_dial) {
        
        if ($this->agiconfig['record_call_out'] == 1 && !empty($this->uniqueid)) {
            $fn = date("d-m-Y", time()) . "/" . $agi_extension . "/" . $this->uniqueid . '.' . $this->agiconfig['monitor_formatfile'];
            $this->agi->set_variable('CDR(userfield)', "audio:" . $this->agiconfig['monitor_path'] . $fn);
            $this->call['userfield'] = "audio:" . $this->agiconfig['monitor_path_out'] . $fn;
            $command_mixmonitor = "MixMonitor " . $this->agiconfig['monitor_path_out'] . $fn . "," . $this->agiconfig['mixmon_post'];
            $rx = $this->agi->exec($command_mixmonitor);
            $this->loger(debug, __FILE__, __LINE__, $command_mixmonitor);
            $this->loger(debug, __FILE__, __LINE__, "result goto: " . $rx['data']);
        }
        $loop_ = 0;
        
        foreach ($trunks as $value) {
            
            $this->call['to_id_trunk_call'] = $value['id_trunk'];
            $this->call['loopdial'] = $loop_;
            $this->call['to_id_directions_call'] = $value['directions_id'];
            $this->call['to_id_supertrunk_call'] = $value['id_supertrunk'];
            $this->call['to_id_tariff_call'] = $value['id_tariff'];
            $this->call['nasipaddress'] = $value['ext_trunk'];
            
            $this->call['startdialtime'] = date("Y-m-d H:i:s", time());
            $this->unixdatatime['startdialtime'] = time();
            
            if ($loop_ >= $this->agiconfig['failover_recursive_limit']) {
                $mes = "Превысили failover_recursive_limit; Loop: $loop_";
                $this->loger(notice, __FILE__, __LINE__, $mes);
                $this->call['userfield'] = $mes;
                $this->logedr();
                exit();
            }
            $outtrunk = $value['ext_trunk'];
            $tech = $value['tprefix'];
            
            // по сути повторная проверка, можно заремить
            if ((int)$value['limit_dial'] < (int)$this->agiconfig['min_dial']) {
                $loop_++;
                $mes = "Мало доступных минут в данном транке: $outtrunk  continue;";
                $this->loger(debug, __FILE__, __LINE__, $mes);
                $this->call['userfield'] = $mes;
                $this->logedr();
                continue;
            }
            
            $loop_++;
            
            if (empty($tech) || !in_array($tech, ["sip", 'iax', 'IAX', 'SIP', 'PJSIP', 'pjsip']) || empty($outtrunk)) {
                $loop_++;
                $mes = "В транке неверно прописан tech. Trunk: $outtrunk  tech: $tech; continue";
                $this->loger(error, __FILE__, __LINE__, $mes);
                $this->call['userfield'] = $mes;
                $this->logedr();
                continue;
            }
            
            //Удаляем и добавляем префикс в транке
            $removeprefix = $value['minus_prefix'];
            if (!empty($removeprefix) && strncmp($agi_extension, $removeprefix, strlen($removeprefix)) == 0 && !empty($removeprefix)) {
                $agi_extension = substr($agi_extension, strlen($removeprefix));
                $this->loger(info, __FILE__, __LINE__, "Удаления префикса trunk: " . $removeprefix . " agi_extension= :" . $agi_extension);
            }
            $addprefix = $value['dial_prefix'];
            if (!empty($addprefix)) {
                $agi_dial = trim($addprefix) . $agi_extension;
                $this->loger(info, __FILE__, __LINE__, "Добавление префикса trunk: " . $addprefix . " agi_extension= :" . $agi_extension);
            } 
            else {
                
                $agi_dial = $agi_extension;
            }
            
            if (!empty($value['forced_clid'])) {
                $this->loger(info, __FILE__, __LINE__, "Установка CID: " . $value['forced_clid']);
                $this->agi->set_callerid($value['forced_clid']);
            }
            
            if ($value['limit_dial'] > $this->cust_limit_dial) {
                $value['limit_dial'] = $this->cust_limit_dial;
                $this->loger(info, __FILE__, __LINE__, "Установка limit_dial равному максимуму для потребителя(cust_limit_dial): " . $value['limit_dial'] . ' c');
            }
            
            $dialparams = str_replace("%timeout%", $value['limit_dial'], $this->agiconfig['dialcommand_param']);
            
            if ((int)$this->agiconfig['switchdialcommand'] == 1) {
                $dialstr = "$tech/$agi_extension@$outtrunk" . $dialparams;
            } 
            else {
                $dialstr = "$tech/$outtrunk/$agi_dial" . $dialparams;
            }
            
            $this->loger(info, __FILE__, __LINE__, "Попытка набора  " . $dialstr . "  limit_dial: " . $value['limit_dial'] . ".\n");
            
            $this->dialstatus = $this->run_dial($dialstr);
            $this->loger(info, __FILE__, __LINE__, "DIALSTATUS: (" . $this->dialstatus . ")");
            
            if ($this->agiconfig['record_call_out'] === 1) {
                $this->agi->exec("StopMixMonitor");
                $this->loger(info, __FILE__, __LINE__, "EXEC StopMixMonitor (" . $this->uniqueid . ")");
            }
            
            if (!isset($_SERVER["argv"][3])) {
                $this->call['cid'] = $this->agi->get_variable('CALLERID(num)', true);
                $this->call['calleridname'] = $this->agi->get_variable('CALLERID(name)', true);
            }
            
            $st = "ACTION_" . $this->dialstatus;
            $action = (isset($this->agiconfig[$st])) ? $this->agiconfig[$st] : 'return';
            
            // $this->call['userfield'] = $this->userfild;
            
            if ($this->dialstatus === "ANSWER") {
                if (isset($_SERVER["argv"][3]) && $_SERVER["argv"][3]) {
                    $this->call['billsec'] = 10;
                    $this->loger(info, __FILE__, __LINE__, "ANSWEREDTIME(debug):" . $this->call['billsec']);
                } 
                else {
                    
                    $this->call['billsec'] = $this->agi->get_variable('ANSWEREDTIME', true);
                    $this->loger(info, __FILE__, __LINE__, "ANSWEREDTIME(billsec):" . $this->call['billsec']);
                }
                $this->loger(info, __FILE__, __LINE__, "RETURN STATUS:" . $this->dialstatus . " ACTION:" . $action);
                $this->logedr();
                return;
            } 
            else {
                if ($this->agiconfig['record_call_out'] === 1) {
                    exec("rm " . $this->agiconfig['monitor_path_out'] . $fn);
                }
            }
            
            $this->loger(info, __FILE__, __LINE__, "RETURN STATUS:" . $this->dialstatus . " ACTION:" . $action);
            $this->logedr();
            
            if ($this->agiconfig[$st] === 'continue') {
                continue;
            }
            return;
        }
        
        $this->loger(info, __FILE__, __LINE__, "Закончились транки в переборе; Loop: $loop_");
        $this->call['userfield'] = "Закончились транки в переборе; Loop: $loop_";
        return;
    }
    
    public function logedr() {
        
        $this->call['terminatecauseid'] = isset($this->dialstatus_rev_list[$this->dialstatus]) ? $this->dialstatus_rev_list[$this->dialstatus] : 10;
        $this->call['stoptime'] = date("Y-m-d H:i:s", time());
        
        if (isset($_SERVER["argv"][3]) && $_SERVER["argv"][3]) {
            $this->call['dialedtime'] = 12;
            $this->loger(info, __FILE__, __LINE__, "dialedtime(debug):" . $this->call['dialedtime']);
        } 
        else {
            $this->call['dialedtime'] = $this->agi->get_variable('DIALEDTIME', true);
            $this->loger(info, __FILE__, __LINE__, "dialedtime:" . $this->call['dialedtime']);
        }
        
        //Время от начала сесии
        $this->call['real_sessiontime'] = time() - $this->unixdatatime['starttime'];
        
        //Время  от Последнего Dial
        $this->call['real_dialtime'] = time() - @$this->unixdatatime['startdialtime'];
        
        $this->call['status_tariff'] = 0;
        $this->call['status_custtariff'] = 0;
        $this->call['status_ftp'] = 0;
        
        $id = Cc_call::insertGetId($this->call);
        $this->loger(info, __FILE__, __LINE__, "INSERT CALL id:$id", $this->call);
        $terminatecauseid = (isset($this->call['terminatecauseid'])) ? (int)$this->call['terminatecauseid'] : 10;
        
        // локальный трафик не тарифицируется
        if ($terminatecauseid === 1 AND ($this->call['type'] === 'out' OR $this->call['type'] === 'local')) {
            
            $sec = $this->getbillarray($this->call['uniqueid'], $this->call['type']);
            if (is_numeric($sec)) {
                $this->loger(notice, __FILE__, __LINE__, "Билинг прошел удачно списано $sec сек");
            }
        }
    }
    
    public function getbillarray($agi_uniqueid, $type = 'out') {
        
        $pole_bill = (isset($this->config['pole_bill']) && empty($this->config['pole_bill'])) ? $this->config['pole_bill'] : 'billsec';
        
        $qq = ($type === 'out') ? Cc_call::GetBill($agi_uniqueid, $pole_bill) : Cc_call::GetBillLocal($agi_uniqueid, $pole_bill);

        if (!isset($qq->$pole_bill) OR $qq->$pole_bill < 1) {
            $this->loger(error, __FILE__, __LINE__, "Нет возможности провести подтсчет трафика для записи ID: $agi_uniqueid.");
            return;
        }
        
        $this->loger(debug, __FILE__, __LINE__, "Найдено поле для тарификации, ID:" . $qq->id . " значение для тарификации: " . $qq->$pole_bill);
        
        // посекундная тарификация
        $sec = (float)$qq->$pole_bill;
        $this->loger(info, __FILE__, __LINE__, "Тарификация посекундная, BILL: $sec");
        
        $limit = (int)Cc_custariff::where('id_custariff', $qq->to_id_custariff_call)->pluck('limit') - $sec;
        Cc_custariff::where('id_custariff', $qq->to_id_custariff_call)->update(['limit' => $limit]);
        $this->loger(info, __FILE__, __LINE__, "Произведена тарификация(Cc_custariff), id: $qq->to_id_custariff_call; остаток: " . round($limit / 60, 2) . " min");
        Cc_call::where('id', $qq->id)->update(['status_custtariff' => 1]);
        
        if ($type === 'out') {
            
            // поминутная
            if ($qq->typetarif === 'min') {
                $sec = (((float)$qq->$pole_bill / 60)) * 60;
                $this->loger(info, __FILE__, __LINE__, "Тарификация поминутная, BILL: $sec");
            }
            
            $limit = (int)Cc_tariff::where('id_tariff', $qq->to_id_tariff_call)->pluck('limit_') - $sec;
            Cc_tariff::where('id_tariff', $qq->to_id_tariff_call)->update(['limit_' => $limit]);
            $this->loger(info, __FILE__, __LINE__, "Произведена тарификация(Cc_tariff), id: $qq->to_id_tariff_call; остаток: " . round($limit / 60, 2) . " min");
            Cc_call::where('id', $qq->id)->update(['status_tariff' => 1]);
        }
        
        return $sec;
    }
}
