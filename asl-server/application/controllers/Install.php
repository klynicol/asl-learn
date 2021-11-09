<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pizza Chronicle Install Controller.
 * 
 * To run these modules use the follow command as example
 * php index.php Install method_name
 * 
 * @author Mark Wickline 2020-03-16
 */

class Install extends CI_Controller{
	public function __construct(){
		parent::__construct();
		is_cli() or die("This controller is CLI only.");
		//TODO lock this down to a specific IP address.
		include FCPATH . 'vendor/autoload.php';
		$this->load->model('install_model');
	}

	/**
	 * Initial installation.
	 * Warning only run once!!!
	 */
	public function index(){
		$shouldProceed = readline('Do you wanna proceed?(y/n): ');
        if (strtolower(trim($shouldProceed)) == 'n') exit;

		$this->install_model->generateDatabaseEncryptionKey();
		$this->install_model->genarateTokenEncryptionKeys();
		$this->install_model->run();
		$this->install_model->insertInitialSettings();
	}
	
	/**
	 * If table definitions were chaned we should run the update
	 */
	public function update(){
		$this->install_model->run();
	}
	
	public function insertSettings(){
		$this->install_model->insertInitialSettings();
	}

	/**
	 * Restore tables post fixed with _old
	 */
	public function updateRevert(){
		$this->install_model->revertTables();
	}

	/**
	 * Clear tables post fixed with _old
	 */
	public function updateClear(){
		$this->install_model->removeOldTables();
	}

	/**
	 * Should not really be used!
	 */
	public function generateDatabaseEncryptionKey(){
		die("Not allowed right now");
		$this->install_model->generateDatabaseEncryptionKey();
	}

	/**
	 * Should not really be used!
	 */
	public function genarateTokenEncryptionKeys(){
		die("Not allowed right now");
		$this->install_model->genarateTokenEncryptionKeys();
	}
}