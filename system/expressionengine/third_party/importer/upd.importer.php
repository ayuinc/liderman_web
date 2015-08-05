<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Updater
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2014, Solspace, Inc.
 * @link		http://solspace.com/docs/importer
 * @license		http://www.solspace.com/license_agreement
 * @version		2.2.5
 * @filesource	importer/upd.importer.php
 */

require_once 'addon_builder/module_builder.php';

class Importer_upd extends Module_builder_importer
{
	public $module_actions		= array();
	public $hooks				= array();

	// --------------------------------------------------------------------

	/**
	 * Contructor
	 *
	 * @access	public
	 * @return	null
	 */

	public function __construct()
	{
		parent::__construct();

		// --------------------------------------------
		//  Module Actions
		// --------------------------------------------

		$this->module_actions = array(
			'ajax_connnection_test',
			'cron_import',
			'batch_import',
			'import_statistics'
		);

		$this->csrf_exempt_actions = array(
			'ajax_connnection_test',
			'cron_import',
			'batch_import',
			'import_statistics'
		);

		// --------------------------------------------
		//  Extension Hooks
		// --------------------------------------------

		$default = array(
			'class'        => $this->extension_name,
			'settings'     => '', 								// NEVER!
			'priority'     => 4,
			'version'      => IMPORTER_VERSION,
			'enabled'      => 'y'
		);

		$this->hooks = array(
			array_merge($default,
				array(
					'method'       => 'cp_js_end',
					'hook'         => 'cp_js_end'
				)
			),
		);
	}
	/* END*/

	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */

	public function install()
	{
		// Already installed, let's not install again.
		if ($this->database_version() !== FALSE)
		{
			return FALSE;
		}

		// --------------------------------------------
		//  Our Default Install
		// --------------------------------------------

		if ($this->default_module_install() == FALSE)
		{
			return FALSE;
		}

		// --------------------------------------------
		//  Module Install
		// --------------------------------------------

		$data = array(	'module_name'			=> $this->class_name,
						'module_version'		=> constant(strtoupper($this->lower_name).'_VERSION'),
						'has_publish_fields'	=> 'n',
						'has_cp_backend'		=> 'y');

		$sql[] = ee()->db->insert_string('exp_modules', $data);

		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}

		return TRUE;
	}
	/* END install() */

	// --------------------------------------------------------------------

	/**
	 * Install Module SQL - Override for Importer, May Put into AOB
	 *
	 * @access	public
	 * @return	null
	 */

	public function install_module_sql()
	{
		// --------------------------------------------
		//  Can We Go InnoDB?
		// --------------------------------------------

		$innodb = '';

		if (ee()->db->platform() == 'mysql')
		{
			$engines = ee()->db->query('SHOW ENGINES');

			foreach ($engines->result() as $engine)
			{
				if (strtolower($engine->Engine) == 'innodb')
				{
					$innodb = ' ENGINE=InnoDB ';
					break;
				}
			}
		}

		// --------------------------------------------
		//  Our Install Queries
		// --------------------------------------------

		$files = array($this->addon_path . $this->lower_name.'.sql',
					   $this->addon_path . 'db.'.$this->lower_name.'.sql');

		foreach($files as $file)
		{
			if (file_exists($file))
			{
				$sql = preg_split(
					"/;;\s*(\n+|$)/",
					file_get_contents($file),
					-1,
					PREG_SPLIT_NO_EMPTY
				);

				foreach($sql as $i => $query)
				{
					$sql[$i] = trim($query).$innodb;
				}

				break;
			}
		}

		// --------------------------------------------
		//  Module Install
		// --------------------------------------------

		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}
	}
	//END install_module_sql()

	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */

	public function uninstall()
	{
		// Cannot uninstall what does not exist, right?
		if ($this->database_version() === FALSE)
		{
			return FALSE;
		}

		// --------------------------------------------
		//  Default Module Uninstall
		// --------------------------------------------

		if ($this->default_module_uninstall() == FALSE)
		{
			return FALSE;
		}

		return TRUE;
	}
	/* END */


	// --------------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * For the sake of sanity, we only start upgrading from version 2.0 or above.  Cleans out
	 * all of the really old upgrade code, which was making Paul really really crazily confused.
	 *
	 * @access	public
	 * @return	bool
	 */

	public function update($current = '')
	{
		if ($current == $this->version)
		{
			return FALSE;
		}

		// --------------------------------------------
		//	Added Batch Table
		//	- Added: 2.0.0.d10
		// --------------------------------------------

		if ($this->version_compare($this->database_version(), '<', '2.0.0.d10'))
		{
			if (ee()->db->table_exists('exp_importer_batches') == FALSE)
			{
				ee()->db->query(
					"CREATE TABLE IF NOT EXISTS `exp_importer_batches` (
						`batch_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
						`profile_id` INT UNSIGNED NOT NULL DEFAULT 1,
						`batch_hash` VARCHAR(13) NOT NULL,
						`details` MEDIUMTEXT NOT NULL,
						`batch_date` int(10) unsigned DEFAULT 0,
						`finished` CHAR(1) NOT NULL DEFAULT 'n',
						PRIMARY KEY  (`batch_id`),
						KEY (`profile_id`),
						KEY (`batch_hash`)
					) CHARACTER SET utf8 COLLATE utf8_general_ci ;;"
				);

				ee()->db->query(
					"CREATE TABLE IF NOT EXISTS `exp_importer_batch_data` (
						`profile_id` INT UNSIGNED NOT NULL DEFAULT 1,
						`batch_hash` VARCHAR(13) NOT NULL,
						`batch_number` INT UNSIGNED NOT NULL DEFAULT 1,
						`batch_data` MEDIUMTEXT NOT NULL,
						`finished` CHAR(1) NOT NULL DEFAULT 'n',
						KEY (`profile_id`),
						KEY (`batch_hash`),
						KEY (`batch_number`)
					) CHARACTER SET utf8 COLLATE utf8_general_ci ;;"
				);
			}
		}

		// --------------------------------------------
		//	Added Batch Hash Field to Import Log
		//	- Added: 2.0.0.d14
		// --------------------------------------------

		if ($this->version_compare($this->database_version(), '<', '2.0.0.d14'))
		{
			ee()->db->query("ALTER TABLE `exp_importer_log` ADD `batch_hash` VARCHAR(13) NOT NULL DEFAULT ''");
		}

		// --------------------------------------------
		//	Added Batch Date to Batch Data TAble
		//	- Added: 2.0.2.d1
		//	- Updated: 2.0.3.d1 (seems the db.importer.sql file was missing the field)
		// --------------------------------------------

		if ($this->version_compare($this->database_version(), '<', '2.0.3.d1'))
		{
			if ( ! ee()->db->field_exists('batch_date', 'exp_importer_batch_data'))
			{
				ee()->db->query("ALTER TABLE `exp_importer_batch_data` ADD `batch_date` int(10) unsigned DEFAULT 0");
			}
		}

		// --------------------------------------------
		//	Added Content Type field to Import Profiles
		//	- Added: 2.1.1.d1
		// --------------------------------------------

		if ($this->version_compare($this->database_version(), '<', '2.1.1.d1'))
		{
			ee()->db->query("ALTER TABLE `exp_importer_profiles` ADD `content_type` VARCHAR(100) NOT NULL DEFAULT 'channel' AFTER `instructions`");
		}

		// --------------------------------------------
		//	Changed 'channel' content type to 'channel_entries'
		//	- Added: 2.2.0.d2
		// --------------------------------------------

		if ($this->version_compare($this->database_version(), '<', '2.2.0.d2'))
		{
			ee()->db->query("UPDATE `exp_importer_profiles` SET `content_type` = 'channel_entries' WHERE `content_type` = 'channel'");
			ee()->db->query("ALTER TABLE `exp_importer_profiles` CHANGE `content_type` `content_type` VARCHAR(100) NOT NULL DEFAULT 'channel_entries'");
		}

		// --------------------------------------------
		//	Changed 'value' size from TEXT to MEDIUMTEXT
		//	- Added: 2.2.5
		// --------------------------------------------

		if ($this->version_compare($this->database_version(), '<', '2.2.4.1'))
		{
			ee()->db->query(
				"ALTER TABLE `exp_importer_profile_settings`
				 CHANGE	`value` `value` mediumtext"
			);
		}


		// --------------------------------------------
		//  Default Module Update
		// --------------------------------------------

		$this->default_module_update();

		// --------------------------------------------
		//  Version Number Update - LAST!
		// --------------------------------------------

		$data = array(
			'module_version' 		=> constant(strtoupper($this->class_name).'_VERSION'),
			'has_publish_fields'	=> 'n'
		);

		ee()->db->query(
			ee()->db->update_string(
				'exp_modules',
				$data,
				array(
					'module_name'		=> $this->class_name
				)
			)
		);

		return TRUE;
	}
	// END update()



}
/* END Importer_updater_base CLASS */