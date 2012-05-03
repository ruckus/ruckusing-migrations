<?php
/**
 * Holds the deploy task
 * 
 * @package DbMigrations
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */

require_once RUCKUSING_BASE . '/lib/classes/task/class.Ruckusing_iTask.php';
require_once RUCKUSING_BASE . '/config/config.inc.php';

/**
 * Executes the deploy task
 * 
 * Deploying means executing the schema to the database and afterwards
 * doing the setup and migrate task.
 * 
 * @package DbMigrations
 * @author Martin Jainta <maj@tradebyte.biz>
 * @copyright 2012 Tradebyte Software GmbH
 */
class Ruckusing_DB_Deploy implements Ruckusing_iTask
{
	/**
	 * @var Ruckusing_BaseAdapter|Ruckusing_MySQLAdapter
	 */
	private $adapter = null;
	
	function __construct($adapter) {
		$this->adapter = $adapter;
	}
	
	/**
	 * Deploys the db
	 * 
	 * Executes the sql queries from the schema and starts the setup and migrate
	 * tasks to have an up-to-date db ready. Does not create the database.
	 * 
	 * @param mixed $args 
	 */
	public function execute($args)
	{
		echo "Started: " . date('Y-m-d g:ia T') . "\n\n";		
		echo "[db:deploy]: \n";
		
		echo "\tStarted executing SQL for schema ".date('Y-m-d g:ia T')."\n\n";
		
		if($this->adapter->isMaster())
		{
			$filenameSuffix = 'master';
		}
		else
		{
			$filenameSuffix = 'client';
		}

		$filename = 'schema_'.$filenameSuffix.'_'.RUCKUSING_STANDARD_TEMPLATE.'.txt';
		$schemaSql = file_get_contents(RUCKUSING_DB_DIR.'/'.$filename);
		$this->adapter->executeSchema($schemaSql);
		
		echo "\tFinished executing SQL for schema ".date('Y-m-d g:ia T');
		
		$setup = new Ruckusing_DB_Setup($this->adapter);
		$setup->execute($args);
		
		if(isset($args['FLAVOUR']))
		{
			$flavour = $args['FLAVOUR'];
			unset($args['FLAVOUR']);
		}
		
		$migrate = new Ruckusing_DB_Migrate($this->adapter);
		$migrate->execute($args);
		
		if(isset($flavour))
		{
			$args['FLAVOUR'] = $flavour;
			$migrate = new Ruckusing_DB_Migrate($this->adapter);
			$migrate->execute($args);
		}
		
		
		echo "\n\nFinished deploy: " . date('Y-m-d g:ia T') . "\n\n";
	}
}
?>
