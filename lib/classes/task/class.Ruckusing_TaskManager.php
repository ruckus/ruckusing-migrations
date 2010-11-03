<?php

//requirements
require_once RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_NamingUtil.php';

define('RUCKUSING_TASK_DIR', RUCKUSING_BASE . '/lib/tasks');

class Ruckusing_TaskManager  {
	
	private $adapter;
	private $tasks = array();
	
	function __construct($adapter) {
		$this->set_adapter($adapter);
		$this->load_all_tasks(RUCKUSING_TASK_DIR);
	}//__construct
	
	public function set_adapter($adapter) { 
		$this->adapter = $adapter;
	}
	public function get_adapter() {
		return $this->adapter;
	}
	
	/* 
		Searches for the given task, and if found
		returns it. Otherwise null is returned.
	*/
	public function get_task($key) {
		if( array_key_exists($key, $this->tasks)) {
			return $this->tasks[$key];
		} else {
			return null;
		}		
	}

	public function has_task($key) {
		if( array_key_exists($key, $this->tasks)) {
			return true;
		} else {
			return false;
		}		
	}

	
	/*
		Register a new task name under the specified key.
		$obj is a class which implements the iTask interface
		and has an execute() method defined.
	*/
	public function register_task($key, $obj) {
		
		if( array_key_exists($key, $this->tasks)) {
			trigger_error(sprintf("Task key '%s' is already defined!", $key));
			return false;
		}
		
		//Reflect on the object and make sure it has an "execute()" method
		$refl = new ReflectionObject($obj);
		if( !$refl->hasMethod('execute')) {
			trigger_error(sprintf("Task '%s' does not have an 'execute' method defined", $key));
			return false;
		}
		$this->tasks[$key] = $obj;
		return true;
	}
	
	public function get_name() {
	}
	
	//---------------------
	// PRIVATE METHODS
	//---------------------
	private function load_all_tasks($task_dir) {
		if(!is_dir($task_dir)) {
			throw new Exception(sprintf("Task dir: %s does not exist", $task_dir));
			return false;
		}
		$files = scandir($task_dir);
		$regex = '/^class\.(\w+)\.php$/';
		foreach($files as $f) {			
			//skip over invalid files
			if($f == '.' || $f == ".." || !preg_match($regex,$f, $matches) ) { continue; }
			require_once $task_dir . '/' . $f;
			$task_name = Ruckusing_NamingUtil::task_from_class_name($matches[1]);
			$klass = Ruckusing_NamingUtil::class_from_file_name($f);
			$this->register_task($task_name, new $klass($this->get_adapter()));
		}
	}//require_tasks
	
	/*
		Execute the supplied Task object
	*/	
	private function execute_task($task_obj) {		
	}
	
	public function execute($task_name, $options) {
		if( !$this->has_task($task_name)) {
			throw new Exception("Task '$task_name' is not registered.");
		}
		$task = $this->get_task($task_name);
		if($task) {
			return $task->execute($options);
		}
		return "";		
	}
	
	
	
}

?>