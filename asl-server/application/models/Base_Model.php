<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once APPPATH . "/libraries/Cipher_Sweet.php";
/**
 * Common database functionality. All models should extend this model.
 * 
 * @author Mark Wickline 2020-03-03
 */
class Base_Model extends CI_Model{

    public $cipher;
    protected $conf;
    protected $encryptedColumns;

    public function __construct($db = NULL){
        parent::__construct();
        if($db !== NULL){
            $this->db = $this->load->database($db);
        }
        $this->cipher = new Cipher_Sweet();
        $this->conf = $this->config->item("asl");
    }

    /**
     * Check if a row exists. Returns true of false.
     * 
     * @param string $table
     * @param array|object $where
     * @return bool
     */
    public function rowExists($table, $where){
        if($this->getRowWhere($table, $where))
            return true;
        return false;
    }

    /**
     * Updates a table and returns true or false.
     * 
     * @param string $table
     * @param array|object $where
     * @param array|object $data
     * @return bool
     */
    public function updWhere($table, $where, $data)
    {
        foreach($where as $key => $value){
            $this->db->where($key, $value);
        }
        $this->db->update($table, $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * inserts data into any table and returns the insert id
     * 
     * @param string $table
     * @param array|object $data
     */
    public function ins($table, $data)
    {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

     /**
     * Insert, on duplicate key update. Does not return the insert id like above.
     * 
     * @param string $table
     * @param string $keyCols
     * @param mixed $data
     */
    public function insUpd($table, $keyCol, $data){
        $table = "`" . $this->db->escape_str($table) . "`";
        $cols = [];
        $vals = [];
        $updates = [];
        foreach($data as $key => $val){
            $key =  "`" . $key . "`";
            $cols[] = $key;
            $val = $this->db->escape($val);
            $vals[] = $val;
            if($key != $keyCol){
                $updates[] = $key . " = VALUES(" . $key . ")";
            }
        }
        $sql = "INSERT INTO {$table}";
        $sql .= "\n(" . implode($cols, ",") .  ")";
        $sql .= "\nVALUES(" . implode($vals, ",") .  ")";
        $sql .= "\nON DUPLICATE KEY UPDATE\n";
        $sql .= implode($updates, ",");
        $this->db->query($sql);
        return $this->db->affected_rows();
    }


    /**
     * Get all records from the table
     * @param string $table
     */
    public function getAll($table){
        $qrs = $this->db->get($table);
        if($qrs && $qrs->num_rows()){
            return $qrs->result_array();
        }
        return false;
    }


    /**
     * A general method for fetching data with a simple query.
     * 
     * @param string $table
     * @param array|object $where Key value pairs of WHERE clause items or keycol value
     * @param string $select Comma separated list of selects.
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getWhere($table, $where = NULL, $select = NULL, $limit = NULL, $offset = NULL, $single = false){

        if($select)
            $this->db->select($select);

        //If we're not dealing with an array or object return empty.
        if(isset($where) && !in_array(gettype($where), ['array', 'object'], true))
            return [];

        $result = $this->db->get_where($table, $where, $limit, $offset);

        if($result && $result->num_rows())
            if($single)
                return $result->row_array();
            else
                return $result->result_array();
        return [];
    }

    /**
     * A general method for fetching A SINGLE ROW with a simple query.
     * 
     * @param string $table
     * @param array|object $where Key value pairs of WHERE clause items or keycol value
     * @param string $select Comma separated list of selects.
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getRowWhere($table, $where = NULL, $select = NULL, $limit = NULL, $offset = NULL){
        return $this->getWhere($table, $where, $select, $limit, $offset, true);
    }

    /**
     * A general method for fetching A single value from a single row
     * 
     * @param string $table
     * @param array|object $where Key value pairs of WHERE clause items or keycol value
     * @param string $select Comma separated list of selects.
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getSingleValue($table, $where = NULL, $select = NULL, $limit = NULL, $offset = NULL){
        $row = $this->getWhere($table, $where, $select, $limit, $offset, true);
        if(!$row){
            return false;
        }
        return $row[$select];
    }

    /**
     * A catch-all delete method.
     * 
     * @param string $table
     * @param array|object $where
     * @return bool
     */
    public function delWhere($table, $where){
        $this->db->delete($table, $where);
        if($this->db->affected_rows() > 0)
            return true;
        return false;
    }

    /**
     * Run any general query
     */
    public function query($qry, $return = true){
        $qrs = $this->db->query($qry);
        if(!$return){
            return;
        }
        if($qrs && $qrs->num_rows()){
            return $qrs->result_array();
        }
        return false;
    }

    /**
     * Run any general query
     */
    public function queryOne($qry){
        $qrs = $this->db->query($qry);
        if($qrs && $qrs->num_rows()){
            return $qrs->row_array();
        }
        return false;
    }


    /**
     * CIPHER METHODS
     */

      /**
     * If we are dealing with a standard key value array to update/insert a row
     * we can use this method to automatically compare rows to the values
     * in $this->encryptedColumns and transform the data;
     * 
     * @var string $table Table name
     * @var array $data see below for example
     * [
     *      'custFirst' => 'Mark'
     *      'custLast' => 'Wickline',
     *      'quoteType' => 'virtual'
     * ]
     * 
     * @return
     * [
     *      'custFirst' => 'nacl:ymyAr_EpO-0KCXh-wJktjx-TI9vSwn1dPpaw4HlPj8Tclj9d6tLsdFxLZCv7ephgoy5nold2KKL_TNhk',
     *      'custFirstBlind' => '768d5cd1', 
     *      'custLast' => 'nacl:ymyAr_EpO-0KCXh-wJktjx-TI9vSwn1dPpaw4HlPj8Tclj9d6tLsdFxLZCv7ephgoy5nold2KKL_TNhk',
     *      'custLastBlind' => '42c80093',
     *      'quoteType' => 'virtual'
     * ]
     */
    public function encryptRowAuto($table, $data){
        if(!isset($this->encryptedColumns[$table])){
            return $data;
        }

        $enc = $this->encryptedColumns[$table];

        // copy data to avoid itterating over added blind indexes and such.
        $dataCopy = $data;

        $encParams = [];
        foreach($dataCopy as $key => $val){
            if(isset($enc[$key]) && !empty($val)){
                $colBuild['val'] = $val;
                $colBuild['col'] = $key;
                if($enc[$key]['bi']){
                    $colBuild['bi'] = $key . "_bli";
                }
                $encParams[] = $colBuild;
            }
        }
        if(!empty($encParams)){
            $data = array_merge($data, $this->encryptRow($table, $encParams));
        }
        return $data;
    }

    /**
     * Encrypt a row before sending it to the database.
     * WARNING: this is not meant to encrypt multiple rows at a time. Column
     * names have to be unique.
     * 
     * @var string $table Table name
     * @var Array $data See below for parameter details
     *  [
     *      [
     *          'val' => 'example@crystal-d.com', //required
     *          'col' => 'column_name', //required
     *          'bi' => 'blind_index_column_name' //optional
     *      ]
     *  ]
     * 
     * @return
    [
        'column_name' => "nacl:ExG6iyOPYD2y9MYny1ODUbLfUsaEHMs6cihhPBmhkfSpxktCVdQk1P3523m2dB_TZ3x05bw",
        'blind_index_column_name' =>  "4f970d73"
    ]
     */
    public function encryptRow($table, $data){
        $this->cipher->clear();
        $this->cipher->setTable($table);
        foreach($data as $d){
            $bi = $d['bi'] ?? '';
            $this->cipher->addField($d['val'], $d['col'], $bi);
        }
        return $this->cipher->encryptFields();
    }


    /**
     * Decrypt a column => value pair of data
     * @var string $table Table name
     * @var array $data
    [
        'column_name' => 'nacl:9odZ8qGZC8Qiy_1Dxg_YOQbORwtwgRaaIKKab-PTgLvriQcxhx2t0BMHiUTFbiAU',
        'column_name_2' => 'nacl:9odZ8qGZC8Qiy_1Dxg_YOQbORwtwgRaaIKKab-PTgLvriQcxhx2t0BMHiUTFbiAU',
        'another_col_name' => 'Some Data'
    ];
     */
    public function decryptRowAuto($table, $data){
        if(!isset($this->encryptedColumns[$table])){
            return $data;
        }

        $enc = $this->encryptedColumns[$table];

        $encParams = [];
        foreach($data as $key => $val){
            if(isset($enc[$key]) && !empty($val)){
                $encParams[$key] = $val;
            }
        }

        if(!empty($encParams)){
            $data = array_merge($data, $this->decryptRow($table, $encParams));
        }


        return $data;
    }


    public function decryptRowByKeys($table, $data, $keys){
        $encParams = [];
        foreach($data as $key => $val){
            if(in_array($key, $keys) && !empty($val)){
                $encParams[$key] = $val;
            }
        }

        if(!empty($encParams)){
            $data = array_merge($data, $this->decryptRow($table, $encParams)[$table]);
        }


        return $data;
    }


    /**
     * Decrypt a column value pair of data
     * @var string $table Table name
     * @var array $data
    [
        'column_name' => 'nacl:9odZ8qGZC8Qiy_1Dxg_YOQbORwtwgRaaIKKab-PTgLvriQcxhx2t0BMHiUTFbiAU',
        'column_name_2' => 'nacl:9odZ8qGZC8Qiy_1Dxg_YOQbORwtwgRaaIKKab-PTgLvriQcxhx2t0BMHiUTFbiAU'
    ];
     */
    public function decryptRow($table, $data){
        $this->cipher->clear();
        $this->cipher->setTable($table);
        return $this->cipher->decryptRow($data);
    }


    /**
     * If you need to search the database for a field that's encrypted you'd use
     * the blind index instead of trying to decrypt each row and search for a match.
     * 
     * use example below
     * 
     $blindIndex = $base->getBlindIndex('quote', 'custName', "Mark Wickline");
     "SELECT * FROM 'quote' WHERE 'custNameBlind' = '{$blindIndex}'";

     * @param string $table The table name
     * @param string $column The name of the column where the big encryption lives.
     * @param string $searchString The non encrypted string you need to search for.
     */
    public function getBlindIndex($table, $column, $searchString){
        $this->cipher->clear();
        $this->cipher->setTable($table);
        $blindIndex = $this->cipher->getBlindIndex($field, $column, $column . "Blind");
        return $blindIndex;
    }

    /**
     * Return a key value pair of settings
     * getting settings where defined in area columns from the input array
     * Settings keys are comma delimited
     * 
     * @param array $areas
     * @return array
     */
    public function getSettings($areas){
        $count = 1;
        $where = [];
        foreach($areas as $ar){
            $where["area_{$count}"] = $ar;
            $count++;
        }

        $settings = [];
        foreach($this->getWhere('t_settings', $where) as $row){
            $key = [];
            for($x = 1; $x < 4; $x++){
                if($row["area_{$x}"] !== NULL){
                    $key[] = $row["area_{$x}"];
                }
            }
            $key = implode(",", $key);

            
            switch($row['type']){
                case 'int':
                    $settings[$key] = (int)$row['value'];
                    break;
                case 'date_time':
                    $settings[$key] = new DateTime($row['value']);
                    break;
                case 'string':
                    $settings[$key] = trim($row['value']);
                    break;
            }
        }

        // echo json_encode($settings); exit;
        return $settings;
    }

    /**
     * The way I chose to handle settings here is not the most optimized. Especially
     * when it comes to updated a global request count. I should create a new table for that
     * where the key is very easily found.
     */
    public function updateSettingValue($setting, $value){
        $setting = explode("_", $setting);
        $count = 1;
        $where = [];
        foreach($setting as $s){
            $where["area_{$count}"] = $s;
            $count++;
        }
        $this->updWhere('t_settings', $where, ['value' => $value]);
    }

    public function updateGuestIpTracker($ip, $count){
        $this->updWhere('t_guest_ip_tracker', 
            ['ip_address' => $ip], ['count' => $count, 'last_request' => sqlTimeStamp()]);
    }
}