<?php

/**
 * Ruckusing
 *
 * @category  Ruckusing
 * @package   Ruckusing_Util
 * @author    Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */

/**
 * Ruckusing_Util_Logger
 *
 * @category Ruckusing
 * @package  Ruckusing_Util
 * @author   Cody Caughlan <codycaughlan % gmail . com>
 * @link      https://github.com/ruckus/ruckusing-migrations
 */
class Ruckusing_Util_Logger
{
    /**
     * the log file
     *
     * @var string
     */
    private $file = '';

    /**
     * Creates an instance of Ruckusing_Util_Logger
     *
     * @param string $file the path to log to
     *
     * @return Ruckusing_Util_Logger
     */
    public function __construct($file)
    {
        $this->file = $file;
        $this->fp = fopen($file, "a+");
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
        $instance = new Ruckusing_Util_Logger($logfile);

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

}
