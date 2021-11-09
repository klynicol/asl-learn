<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once APPPATH . 'models/Base_Model.php';
/**
 * Models that extend from this model are synonymous with their
 * database conterpart. This way they can be easily loaded and saved.
 * 
 * It's essential allowing to load $this and save $this from models.
 * 
 * I created this model as an alterantive to codeigniters custom_row_object
 * and custom_results_object which didn't seem to allow loading $this.
 * 
 */
abstract class Data_Model extends Base_Model{

    /**
     * To be set from classes that extend. The table the data lives on.
     */
    protected $table;

    /**
     * A list of fields that should be converted to integers when pulling data
     * from the database. This unfotunately isn't being done automatically.
     */
    protected $intFields = ['id'];

    /**
     * Fields that should be json_encoded when storing to database
     */
    protected $jsonFields = [];

    /**
     * Special set of definitions to regulate text fields.
     * key = length, lengthy will be trimmed.
     */
    protected $txtFields = [];

    /**
     * DateTime fields need to be converted from DateTime to 
     * a sql formated string.
     */
    protected $dateTimeFields = [];

    /**
     * A list of class properties that should be ignored
     * when saving to the database or outputing json.
     * 
     * These names will be off limits to regular property names.
     */
    
    protected $ignoreFields = [
        'id',
        'table',
        'ignoreFields',
        'intFields',
        'cipher',
        'encryptedFields',
        'encryptedColumns',
        'jsonFields',
        'txtFields',
        'conf',
        'dateTimeFields'
    ];

    /**
     * These fields require to be encrypted before being stored
     * data will be array as such
     * 
     * 'example_field' => [ 'blind_index' => 'blind_index_name' ]
     * 
     * If there's no blind index one will not be added.
     */
    protected $encryptedFields = [];


    public function __construct(){
        parent::__construct();
    }

    /**
     * Every table in the database has a primary key of 'id'.
     * This is a simple method to load a single row based on id.
     */
    public function loadFromId($id){
        $row = $this->getRowWhere($this->table, ['id' => $id]);
        if(empty($row)) return false;
        $this->loadThis($row);
        return true;
    }

    /**
     * A simple way to populate the properties of $this
     * from and object or an associative array.
     */
    public function setProperties($data){
        foreach($data as $key => $value){
            $this->_setProperty($key, $value);
        }
    }

    /**
     * Set a single property on the calling class.
     * 
     * @param string $key
     * @param mixed $value
     * @param boolean $handleJson Should we json_decode this field?
     */
    private function _setProperty($key, $value, $handleJson = false){
        if(!property_exists($this, $key))
            return;
        if(in_array($key, $this->intFields)){
            $value = intval($value);
        }
        if($handleJson && in_array($key, $this->jsonFields)){
            $value = json_decode($value, true);
        }
        if(in_array($key, $this->dateTimeFields)){
            $value = new DateTime($value);
        }
        $this->$key = $value;
    }

    /**
     * Prepares the data in this for storage.
     * Paying attention to field definitions
     * Creates a new data array from this and returns it.
     * 
     * @param boolean $ignoreCrypto Ignoring crypto will speed things up though changes to senstive data won't be saved.
     * @return array Data array to be stored in the database.
     */
    private function _prepareStorageData($ignoreCrypto = false){
        $data = [];
        $this->cipher->clear();
        $ignoreFields = $ignoreCrypto ? array_merge($this->ignoreFields, array_keys($this->encryptedFields)) : $this->ignoreFields;
        $hasEncryptedData = false;
        foreach($this as $key => $value){
            if(in_array($key, $ignoreFields)){
                continue;
            }
            $set = false;
            if(isset($this->txtFields[$key])){
                $data[$key] = trim((string)$value);
                if(isset($this->txtFields[$key]['length'])  && strlen($value) > $this->txtFields[$key]['length']){
                    $data[$key] = substr($data[$key], 0, $this->txtFields[$key]['length']);
                } else {
                    $data[$key] = $data[$key];
                }
                $set = true;
            }
            if(array_key_exists($key, $this->encryptedFields)){
                $set = true;
                $hasEncryptedData = true;
                $blindIndex = $this->encryptedFields[$key]['blind_index'] ?? '';
                $this->cipher->addField($value, $key, $blindIndex);
            }
            if(in_array($key, $this->jsonFields)){
                $set = true;
                $data[$key] = json_encode(trim((string)$value));
            }
            if(in_array($key, $this->dateTimeFields)){
                $set = true;
                $data[$key] = $this->$key->format('Y-m-d H:i:s');
            }
            if(!$set){
                // Try adding raw data, this may be a vunerability later on?
                // Consider removing this and handle everything from a definition. TODO
                $data[$key] = $value;
            }
        }
        if($hasEncryptedData){
            $this->cipher->setTable($this->table);
            if($cryptoData = $this->cipher->encryptFields()){
                $data = array_merge($data, $cryptoData);
            }
        }
        return $data;
    }

    /**
     * Prepare the properties of $this to be exported in a json format
     */
    public function toJsonReady(){
        $data = [];
        foreach($this as $key => $value){
            if($key === 'api_token'){
                continue;
            }
            if(in_array($key, $this->ignoreFields)){
                continue;
            }
            $set = false;
            if(in_array($key, $this->dateTimeFields)){
                $set = true;
                $data[$key] = $this->$key->format('Y-m-d H:i:s');
            }
            if(!$set){
                // Try adding raw data, this may be a vunerability later on?
                // Consider removing this and handle everything from a definition. TODO
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * Will take in a result array and load $this from it.
     * 
     * @param arrayy $resultArray
     */
    public function loadThis($resultArray){
        $cryptoFields = [];
        foreach($resultArray as $key => $value){
            if(!isset($value)){
                continue;
            } elseif(array_key_exists($key, $this->encryptedFields)){
                //If we're dealing with encrypted fields, set to deal with them later all at once.
                $cryptoFields[$key] = $value;
            } else {
                $this->_setProperty($key, $value, true, true);   
            }
        }
        if(!empty($cryptoFields)){
            foreach($this->decryptRow($this->table, $cryptoFields)[$this->table] as $decKey => $decVal){
                $this->_setProperty($decKey, $decVal, true, true);
            }
        }
    }

    /**
     * Iterate through the calling object and replace the database data.
     * 
     * @param boolean $ignoreCrypto Ignoring crypto will speed things up though changes to senstive data won't be saved.
     */
    public function saveThis($ignoreCrypto = false){
        $data = $this->_prepareStorageData($ignoreCrypto);
        // echo json_encode($data); exit;
        return $this->updWhere($this->table, ['id' => $this->id], $data);
    }

    /**
     * Iterate through the calling object and insert the database data.
     * Saves the new id to the calling object.
     */
    public function insertThis(){
        $data = $this->_prepareStorageData(false);
        $this->id = $this->ins($this->table, $data);
    }

    /**
     * Overloader for private properties. Don't need this?
     */
    public function __set($name, $value){
        $this->$name = $value;
    }
}

