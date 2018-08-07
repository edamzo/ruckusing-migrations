<?php
/**
 * Holds the deploy task
 * 
 * @package Ruckusing-Migrations
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
 * @package Ruckusing-Migrations
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

		$schemaTmpl = RUCKUSING_STANDARD_TEMPLATE;
		
		if(isset($args['TEMPLATE']))
		{
			$schemaTmpl = $args['TEMPLATE'];
		}

        $filenameTxt = 'schema_'.$schemaTmpl.'.txt';
        $filenameSql = 'schema_'.$schemaTmpl.'.sql';

        // We allow schemas in txt or sql (recommended) file suffix
        if (is_file(RUCKUSING_DB_DIR.'/'.$filenameTxt)) {
            $filepath = RUCKUSING_DB_DIR.'/'.$filenameTxt;
        } elseif (is_file(RUCKUSING_DB_DIR.'/'.$filenameSql)) {
            $filepath = RUCKUSING_DB_DIR.'/'.$filenameSql;
        } else {
            // Only doing the deploy if a SQL schema file exists.
            echo "\tNo SQL schema for Template '".$schemaTmpl."' ".date('Y-m-d g:ia T');
            trigger_error("\nAborting db:deploy for database '".$this->adapter->getDbName()."'\n\n");
        }

        $schemaSql = file_get_contents($filepath);
        $this->adapter->executeSchema($schemaSql);
        echo "\tFinished executing SQL for schema ".date('Y-m-d g:ia T');

        $setup = new Ruckusing_DB_Setup($this->adapter);
        $setup->execute($args);

        $migrate = new Ruckusing_DB_Migrate($this->adapter);
        $migrate->execute($args);

        echo "\n\nFinished deploy: " . date('Y-m-d g:ia T') . "\n\n";
	}
}
?>
