<?php

/*
  Generator for migrations.
  Usage: php generate.php <migration name>
  Call with no arguments to see usage info.
*/

if(!defined('RUCKUSING_BASE')) {
	define('RUCKUSING_BASE', realpath(__DIR__));
}

require RUCKUSING_BASE.'/bootstrap.php';
require_once RUCKUSING_BASE  . '/lib/classes/util/class.Ruckusing_NamingUtil.php';
require_once RUCKUSING_BASE  . '/lib/classes/util/class.Ruckusing_MigratorUtil.php';

$args = parse_args($argv);
main($args);


//-------------------

/*
  Parse command line arguments.
*/
function parse_args($argv) {
  $num_args = count($argv);
  if($num_args < 2) {
   print_help(true); 
  }
  $migration_name = $argv[1];
  
  if(isset($argv[2]))
  {
	  $template = $argv[2];
  }
  else
  {
	  $template = 'development';
	  echo sprintf("\nNo Db was delivered. Generating Migrationfile for standard Db '%s'. Give a additional parameter to use an other Db.\n", $template);
  }
  
  return array('name' => $migration_name,
			   'template' => $template);
}


/*
  Print a usage scenario for this script.
  Optionally take a boolean on whether to immediately die or not.
*/
function print_help($exit = false) {
  echo "\nusage: php generate.php <migration name>\n\n";
  echo "\tWhere <migration name> is a descriptive name of the migration, joined with underscores.\n";
  echo "\tExamples: add_index_to_users | create_users_table | remove_pending_users\n\n";
  if($exit) { exit; }
}

function main($args) {
  //input sanity check
  if(!is_array($args) || (is_array($args) && !array_key_exists('name', $args)) ) {
    print_help(true);
  }
  $migration_name = $args['name'];
  
  //clear any filesystem stats cache
  clearstatcache();
  
	$newMigrationDir = RUCKUSING_MIGRATION_DIR.'/'.$args['template'];
	
	if(!is_dir($newMigrationDir))
	{
		mkdir($newMigrationDir);
	}
  
  //check to make sure our migration directory exists
  if(!is_dir($newMigrationDir)) {
	die_with_error("ERROR: migration directory '" . $newMigrationDir . "' does not exist. Specify MIGRATION_DIR in config/config.inc.php and try again.");
  }
  
  //generate a complete migration file
  $next_version     = Ruckusing_MigratorUtil::generate_timestamp();
  $klass            = Ruckusing_NamingUtil::camelcase($migration_name);
  $file_name        = $next_version . '_' . $klass . '.php';
  $full_path        = realpath($newMigrationDir) . '/' . $file_name;
  $template_str     = get_template($klass);
    
  //check to make sure our destination directory is writable
  if(!is_writable($newMigrationDir . '/')) {
    die_with_error("ERROR: migration directory '" . $newMigrationDir . "' is not writable by the current user. Check permissions and try again.");
  }

  //write it out!
  $file_result = file_put_contents($full_path, $template_str);
	if($file_result === FALSE) {
		die_with_error("Error writing to migrations directory/file. Do you have sufficient privileges?");
	} else {
  	echo "\nCreated migration: {$file_name} for Db '{$args['template']}'.\n\n";
	}
}

function die_with_error($str) {
  die("\n{$str}\n");
}

function get_template($klass) {
if(defined('RUCKUSING_MIGRATION_TPL')
	&& is_file(RUCKUSING_MIGRATION_TPL)) {
    $template = file_get_contents(RUCKUSING_MIGRATION_TPL);
	$template = str_replace('CLASS_NAME', $klass, $template);
} else {
$template = <<<TPL
<?php\n
class $klass extends Ruckusing_BaseMigration {\n\n\tpublic function up() {\n\n\t}//up()
\n\tpublic function down() {\n\n\t}//down()
}
?>
TPL;
}
return $template;
}

?>