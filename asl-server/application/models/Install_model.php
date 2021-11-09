<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Modify the database quickly and efficiently.
 * 
 * THIS CLASS WAS NOT INTENTED TO PROVIDE FUNCTIONALITY FOR
 * RENAMING COLUMNS. ONLY CREATING AND DELETING.
 * 
 * If you need to modify a column do so in a database editor and replicate
 * the changes onto the property definitions
 * 
 * It's a good idea to create a backup before running this install.
 * 
 * @author Mark Wickline 2020-03-03
 */
class Install_Model extends CI_Model{
    /**
     * properties are names of tables with create code as values.
     */

    private $tables = [
        'users', 'guest_ip_tracker', 'settings', 'guest_mail_list','meetings','meeting_guest_regs','temp_token', 'email_templates'
    ];

    // Users table
    private $users = [
        'id' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
        'username' => 'VARCHAR(255) NOT NULL',
        'first_name' => 'VARCHAR(255)',
        'first_name_bli' => 'VARCHAR(110)',
        'last_name' => 'VARCHAR(255)',
        'last_name_bli' => 'VARCHAR(110)',
        'email' => 'VARCHAR(255)',
        'email_bli' => 'VARCHAR(110)',
        'create_date' => 'DATETIME DEFAULT NOW()',
        'last_login_date' => 'DATETIME DEFAULT NOW()',
        'last_activity_date' => 'DATETIME DEFAULT NOW()',
        'pass_hash' => 'VARCHAR(580) NOT NULL',
        'user_type' => 'TINYINT NOT NULL',
        'api_token' => 'VARCHAR(30)'
    ];

    private $guest_mail_list = [
        'email' => 'VARCHAR(320)',
        'date_time' => 'DATETIME DEFAULT NOW()'
    ];

    private $guest_ip_tracker = [
        'ip_address' => 'VARCHAR(55) KEY',
        'count' => 'SMALLINT',
        'last_request' => 'DATETIME DEFAULT NOW()'
    ];

    // Settings table
    private $settings = [
        'id' => 'SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
        'helper' => 'VARCHAR(255)',
        // Define areas that help know what the infromation is about.
        'area_1' => 'VARCHAR(30), INDEX `area_1` (`area_1`)',
        'area_2' => 'VARCHAR(30), INDEX `area_2` (`area_2`)',
        'area_3' => 'VARCHAR(30), INDEX `area_3` (`area_3`)',
        'type' => 'VARCHAR(30)',
        'value' => 'VARCHAR(255)'
    ];
    
    private $meetings = [
        'id' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT NOT NULL, INDEX `user_id` (`user_id`)',
        'zoom_pass' => 'VARCHAR(50)',
        'zoom_id' => 'VARCHAR(110)',
        'description' => 'TEXT',
        'date_time' => 'DATETIME',
        'title' => 'VARCHAR(255)',
        'duration' => 'SMALLINT',
        'create_date' => 'DATETIME',
        'modify_date' => 'DATETIME',
        'cost' => 'DECIMAL(32,2)'
    ];
    
    private $meeting_guest_regs = [
        'id' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
        'meeting_id' => 'INT UNSIGNED NOT NULL, INDEX `meeting_id` (`meeting_id`)',
        'first_name' => 'VARCHAR(255)',
        'first_name_bli' => 'VARCHAR(110)',
        'last_name' => 'VARCHAR(255)',
        'last_name_bli' => 'VARCHAR(110)',
        'email' => 'VARCHAR(255)',
        'email_bli' => 'VARCHAR(110)',
        'message' => 'TEXT',
        'is_paid' => 'TINYINT NOT NULL DEFAULT 0',
        'amount_paid' => 'DECIMAL(32,2)',
        'create_date' => 'DATETIME DEFAULT NOW()',
    ];
    
    // Temporary token table for whatever we need it for.
    private $temp_token = [
        'type' => 'VARCHAR(20), INDEX `type` (`type`)',
        'identifier' => 'VARCHAR(110), INDEX `identifier` (`identifier`)',
        'token' => 'VARCHAR(255)',
        'date_time' => 'DATETIME DEFAULT NOW()'
    ];
    
    private $email_templates = [
        'id' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
        'subject' => 'VARCHAR(500)',
        'body' => 'TEXT',
        'area_1' => 'VARCHAR(30), INDEX `area_1` (`area_1`)',
        'area_2' => 'VARCHAR(30), INDEX `area_2` (`area_2`)',
        'area_3' => 'VARCHAR(30), INDEX `area_3` (`area_3`)'
    ];


    /**
     * Depending on the type of application we're planning on having
     * we should define some intial settings here.
     */
    private $initialSettings = [
        'std' => [
            // Global rate limit settings
            [
                'areas' => ['RATE_LIMIT', 'GLOBAL'],
                'type' => 'int',
                'helper' => 'Number of requests allowed within INTERVAL',
                'value' => '100'
            ],
            [
                'areas' => ['RATE_LIMIT', 'GLOBAL', 'INTERVAL'],
                'type' => 'int',
                'helper' => 'Seconds',
                'value' => '1200'
            ],
            [
                'areas' => ['RATE_LIMIT', 'GLOBAL', 'COUNT'],
                'type' => 'int',
                'helper' => 'Current request count',
                'value' => '0'
            ],
            [
                'areas' => ['RATE_LIMIT', 'GLOBAL', 'DATETIME'],
                'type' => 'date_time',
                'helper' => 'Last request date time',
                'value' => '1990-01-01 00:00:00'
            ],
            // Global ip address rate limiting
            [
                'areas' => ['RATE_LIMIT', 'IP_ADDRESS'],
                'type' => 'int',
                'helper' => 'Number of requests allowed within INTERVAL',
                'value' => '100'
            ],
            [
                'areas' => ['RATE_LIMIT', 'IP_ADDRESS', 'INTERVAL'],
                'type' => 'int',
                'helper' => 'Seconds',
                'value' => '1200'
            ]
        ]
    ];

	public function __construct(){
		parent::__construct();
        $this->load->dbforge();
        include APPPATH . 'libraries/Cipher_Sweet.php';
	}

    /**
     * Install/update a file that holds the Cipher_Sweet encryption
     * key.
     */
    public function generateDatabaseEncryptionKey(){
        $this->load->helper('file');
        $keyLocation = $this->config->item('asl')['cipher_key_location'];
        $key = Cipher_Sweet::generateKey();
        echo "Inserting cipher key into {$keyLocation}\n";
        write_file($keyLocation, $key);
    }
    
    /**
     * Generate the token key files needed to make an encrypted
     * key for use when calling logged in user methods.
     * @see Base::encrypt
     * @see Base::decrypt
     */
    public function genarateTokenEncryptionKeys(){
        $this->load->helper('file');
        $config = $this->config->item('asl');
        echo "Inserting token key 1\n";
        write_file($config['token_key1_location'], generateRandomString(32));
        echo "Inserting token key 2\n";
        write_file($config['token_key2_location'], generateRandomString(32));
    }

    /**
     * Rename old tables if they exits
     * Create new tables
     * Migrate data if it exists
     */
	public function run(){
        $engine = array('ENGINE' => 'InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
        foreach($this->tables as $table){
            $array = $this->$table;

            $tableBase = $table;
            $table = "t_" . $table;

            $tableExits = false;

            if($this->db->table_exists($table)){
                if($this->db->table_exists($table . "_old")){
                    echo "Skipping table, {$table}_old already exists!\n";
                    continue;
                }
                echo "Renaming {$table} to {$table}_old\n";
                $this->dbforge->rename_table($table, $table . "_old");
                $tableExits = true;
            }

            if(is_array($array)){
                //Create the new table from array values.
                echo "Creating table {$table}\n";
                foreach($array as $fieldName => $definition){
                    $this->dbforge->add_field($fieldName . " " . $definition);
                }
                $this->dbforge->create_table($table, FALSE, $engine);
            } else {
                //We are working with a string, just run it in a query
                echo "Running query:\n";
                echo $array . "\n";
                $this->db->query($array);
            }


            if($tableExits){
                //We have to iterate over old fields and see if any were dropped.
                //Then run a query to grab the old data.
                $copyFields = [];
                foreach($this->db->list_fields($table . "_old") as $field){
                    if(array_key_exists($field, $this->$tableBase))
                        $copyFields[] = $field;
                }
                if(!empty($copyFields)){
                    echo "Migrating data to new table\n";
                    $copyFieldsString = implode(',', $copyFields);
                    $this->db->query("
                        INSERT INTO {$table} ({$copyFieldsString})
                        SELECT $copyFieldsString
                        FROM {$table}_old
                    ");
                }
            }
        }
	}

    /**
     * Settings described in $this->initialSettings are inserted
     * into the `settings` table.
     */
    public function insertInitialSettings($appType = 'std'){
        echo "Inserting intial settings to the t_settings table\n";
        $this->load->model('Base_Model', 'bm');
        foreach($this->initialSettings[$appType] as $setting){
            $areas = [];
            for($x = 1; $x < 4; $x ++){
                if(isset($setting['areas'][$x - 1])){
                    $areas["area_{$x}"] = $setting['areas'][$x - 1];
                }
            }
            unset($setting['areas']);
            $setting = array_merge($setting, $areas);
            if(!$this->bm->rowExists('t_settings', $areas)){
                $this->bm->ins('t_settings', $setting);
            }
        }
    }

    /**
     * Restore tables post fixed with _old
     * and delete the new tables.
     */
    public function revertTables(){
        $shouldProceed = readline('Do you wanna proceed?(y/n): ');
        if (strtolower(trim($shouldProceed)) == 'n') exit;

        echo "Restoring old tables and deleting new tables\n";

        foreach($this->tables as $table){
            $array = $this->$table;
            $table = "t_" . $table;

            if($this->db->table_exists($table . "_old")){
                if($this->db->table_exists($table)){
                    $this->dbforge->drop_table($table); 
                }
                $this->dbforge->rename_table($table . "_old", $table);
            }
        }
    }

    /**
     * Remove all tables post fixed with _old
     */
    public function removeOldTables(){
        $shouldProceed = readline('Do you wanna proceed?(y/n): ');
        if (strtolower(trim($shouldProceed)) == 'n') exit;

        echo "Removing all tables post fixed with _old\n";

        foreach($this->tables as $table){
            $array = $this->$table;
            $table = "t_" . $table;
            if($this->db->table_exists($table . "_old")){
                $this->dbforge->drop_table($table . "_old"); 
            }
        }
    }

    /**
     * On a fresh install we should install a user that can
     * access the admin panel and add more users as admin.
     */
    public function initSuperAdmin(){
        $cipher = new Cipher_Sweet();
    }
}