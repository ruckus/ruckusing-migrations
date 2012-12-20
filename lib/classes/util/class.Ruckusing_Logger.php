<?php

/**
 * Implementation of Ruckusing_Logger
 *
 * @category Ruckusing_Utils
 * @package  Ruckusing_Migrations
 * @author   (c) Cody Caughlan <codycaughlan % gmail . com>
 */
class Ruckusing_Logger
{
    private $file = '';

    /**
     * Creates an instance of Ruckusing_Logger
     *
     * @param string $file the path to log to
     */
    public function __construct($file)
    {
        $this->file = $file;
        $this->fp = fopen($file, "a+");
        //register_shutdown_function(array("Logger", "close_log"));
    }

    /**
     * Singleton for the instance
     *
     * @param string $logfile the path to log to
     *
     * @return object
     */
    public static function instance($logfile)
    {
        static $instance;
        if ($instance !== NULL) {
            return $instance;
        }
        $instance = new Ruckusing_Logger($logfile);

        return $instance;
    }

    /**
     * Log a message
     *
     * @param string $msg message to log
     */
    public function log($msg)
    {
        if ($this->fp) {
            $ts = date('M d H:i:s', time());
            $line = sprintf("%s [info] %s\n", $ts, $msg);
            fwrite($this->fp, $line);
        } else {
            throw new Exception(sprintf("Error: logfile '%s' not open for writing!", $this->file));
        }

    }

    /**
     * Close the log file handler
     */
    public function close()
    {
        if ($this->fp) {
            fclose($this->fp);
        }
    }

}//class()
