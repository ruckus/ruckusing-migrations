<?php
/**
 * Bootstrap file for ruckusing-migrations
 * 
 * @package Ruckusing-Migrations
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */
require __DIR__.'/config/config.inc.php';
/**
 * Parses all option given as KEY=value to an array by splitting it by the equal char
 * 
 * @param array $argv
 * @return array An array with the options split 
 */
function parseAllArgs($argv)
{
	$options = array();
	
	foreach ($argv as $optStr)
	{
		$optStrArray = explode('=', $optStr);
		
		if(count($optStrArray) === 2)
		{
			$options[$optStrArray[0]] = $optStrArray[1];
		}
	}
	
	return $options;
}

/**
 * Returns the database configfile which should be required to get the db config array
 * 
 * @param array The argv variable storing the given parameters
 * @return string The full filepath to the config file
 */
function getConfigFile($argv)
{
	$args = parseAllArgs($argv);
	$configFile = realpath(RUCKUSING_BASE) . '/config/';
	
	if(isset($args['CONFIG']))
	{
		$configFile .= $args['CONFIG'];
		
		if(!is_file($configFile))
		{
			trigger_error(sprintf("\tConfigfile does not exist: %s\n", $configFile));
		}
	}
	else
	{
		$configFile .= RUCKUSING_DB_CONFIG;
		
		if(!is_file($configFile))
		{
			trigger_error(sprintf("\tConfigfile does not exist: %s\n", $configFile));
		}
	}
	
	return $configFile;
}
?>
