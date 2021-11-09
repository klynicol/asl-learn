<?php
/**
 * @author Mark Wickline 2021-11-01
 *
 * Include file at the begining of your processes.
 * stamp a start and end where needed, then print results.
 * 
 * Use Example:
$ScriptTime = new ScriptTime();
$ScriptTime->start('sql_test');
// Run a query
$ScriptTime->end('sql_test');
echo $ScriptTime;
 * 
 * Use Example Two:
$ScriptTime = new ScriptTime(); //begning of script
$ScriptTime->stop(); //end of script
echo json_encode([
    'whatever' => 'all good',
    'script_time_data' => $ScriptTime->times
]);
 */

class ScriptTime
{
    public $times = [];
    public $printType = 'json';

    public function __construct(){
        $this->times['script_time'] = [];
        $this->times['script_time']['start'] = $_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * Create a new entry and stamp it
     * 
     * @param mixed $key;
     */
    public function start($key){
        $this->times[$key] = [];
        $this->times[$key]['start'] = microtime(true);
    }

    /**
     * Stamp an entry as ended
     * 
     * @param mixed $key
     */
    public function end($key){
        if(!isset($this->times[$key])){
            return false;
        }
        $this->times[$key]['end'] = microtime(true);
        return true;
    }


    public function __toString(){
        $this->print();
    }

    /**
     * Stack the total script execution time from $_SERVER
     * to the array as 'script_time'
     */
    public function stop(){
        $this->times['script_time']['end'] = microtime(true);
        $this->calc();
    }

    /**
     * Calculated the elapsed times on $this->times();
     */
    public function calc(){
        foreach($this->times as $key => &$vals){
            if(!isset($vals['start']) || !isset($vals['end'])){
                continue;
            }
            $vals['elapsed'] = $vals['end'] - $vals['start'];
        }
        unset($vals);
    }

    /**
     * print $this->times to script output via your
     * favorite debug output.
     * This method also ends the 'script_time' entry.
     * 
     * @param string $typeOverride
     */
    public function print($typeOverride = null){
        $this->stop();

        $type = $this->printType;
        if($typeOverride && gettype($typeOverride) === 'string'){
            $type = $typeOverride;
        }
        echo "<pre>";
        switch ($type) {
            case 'json':
                echo json_encode($this->times, JSON_PRETTY_PRINT);
                break;
            case 'var_dump':
                var_dump($this->times);
                break;
            case 'print_r':
                print_r($this->times);
                break;
            default:
                echo "ScriptTime::printTimes() 1st parameter \"{$type}\" is not supported.";
                break;
        }
        echo "</pre>";
    }
}
