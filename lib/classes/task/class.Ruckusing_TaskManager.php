<?php

require_once RUCKUSING_BASE . '/lib/classes/util/class.Ruckusing_NamingUtil.php';

define('RUCKUSING_TASK_DIR', RUCKUSING_BASE . '/lib/tasks');

/**
 * Implementation of Ruckusing_TaskManager
 *
 * @category Ruckusing_Tasks
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_TaskManager
{
    private $adapter;
    private $tasks = array();

    /**
     * Creates an instance of Ruckusing_TaskManager
     *
     * @param object $adapter The current adapter being used
     */
    public function __construct($adapter)
    {
        $this->set_adapter($adapter);
        $this->load_all_tasks(RUCKUSING_TASK_DIR);
    }//__construct

    /**
     * Creates an instance of Ruckusing_TaskManager
     *
     * @param object $adapter The current adapter being used
     */
    public function set_adapter($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Get the current adapter
     *
     * @return object $adapter The current adapter being used
     */
    public function get_adapter()
    {
        return $this->adapter;
    }

    /**
     * Searches for the given task, and if found
     * returns it. Otherwise null is returned.
     *
     * @param string $key The task name
     *
     * @return object | null
     */
    public function get_task($key)
    {
        if ( array_key_exists($key, $this->tasks)) {
            return $this->tasks[$key];
        } else {
            return null;
        }
    }

    /**
     * Check if a task exists
     *
     * @param string $key The task name
     *
     * @return boolean
     */
    public function has_task($key)
    {
        if ( array_key_exists($key, $this->tasks)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Register a new task name under the specified key.
     * $obj is a class which implements the iTask interface
     * and has an execute() method defined.
     *
     * @param string $key the task name
     * @param object $obj the task object
     *
     * @return boolean
     */
    public function register_task($key, $obj)
    {
        if ( array_key_exists($key, $this->tasks)) {
            trigger_error(sprintf("Task key '%s' is already defined!", $key));

            return false;
        }

        //Reflect on the object and make sure it has an "execute()" method
        $refl = new ReflectionObject($obj);
        if ( !$refl->hasMethod('execute')) {
            trigger_error(sprintf("Task '%s' does not have an 'execute' method defined", $key));

            return false;
        }
        $this->tasks[$key] = $obj;

        return true;
    }

    /**
     * Get name
     */
    public function get_name()
    {
    }

    //---------------------
    // PRIVATE METHODS
    //---------------------
    /**
    * Load all taks
    *
    * @param string $task_dir the task dir path
    */
    private function load_all_tasks($task_dir)
    {
        if (!is_dir($task_dir)) {
            throw new Exception(sprintf("Task dir: %s does not exist", $task_dir));

            return false;
        }
        $files = scandir($task_dir);
        $regex = '/^class\.(\w+)\.php$/';
        foreach ($files as $f) {
            //skip over invalid files
            if ($f == '.' || $f == ".." || !preg_match($regex,$f, $matches) ) {
                continue;
            }
            require_once $task_dir . '/' . $f;
            $task_name = Ruckusing_NamingUtil::task_from_class_name($matches[1]);
            $klass = Ruckusing_NamingUtil::class_from_file_name($f);
            $this->register_task($task_name, new $klass($this->get_adapter()));
        }
    }//require_tasks

    /**
     * Execute the supplied Task object
     *
     * @param object $task_obj The task object
     */
    private function execute_task($task_obj)
    {
    }

    /**
     * Execute a task
     *
     * @param object $framework The current framework
     * @param string $task_name the task to execute
     * @param array  $options
     *
     * @return boolean
     */
    public function execute($framework, $task_name, $options)
    {
        if ( !$this->has_task($task_name)) {
            throw new Exception("Task '$task_name' is not registered.");
        }
        $task = $this->get_task($task_name);
        if ($task) {
            $task->set_framework($framework);

            return $task->execute($options);
        }

        return "";
    }

    /**
     * Get display help of task
     *
     * @param string $task_name The task name
     *
     * @return string
     */
    public function help($task_name)
    {
        $task = $this->get_task($task_name);
        return $task->help();
    }

}
