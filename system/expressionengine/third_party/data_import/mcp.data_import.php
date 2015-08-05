<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Data Import Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		addonlabs
 * @link		http://addonlabs.com		
 */

class Data_import_mcp {
	
	public $return_data;
	
	private $_base_url;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->_base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=data_import';
		$this->_action_url = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=data_import';		
		$this->EE->cp->set_right_nav(array(
			'import_list'	 => $this->_base_url.AMP.'method=import_list',
			'database_settings' => $this->_base_url.AMP.'method=database_settings',
			'data_import_usage' => $this->_base_url.AMP.'method=usage',
			// Add more right nav items here.
		));
		$this->EE->load->helper(array('html'));
		$this->EE->load->library(array('db_lib', 'table', 'data_import_config'));
		$this->EE->load->model(array('data_import_model', 'data_import_list_model', 'data_import_remote_model'));
		// default global view variables
		$vars = array(
			'cp_data_import_table_template' => array(
				'table_open'		=> '<table class="mainTable data_import_table" border="0" cellspacing="0" cellpadding="0">',
				'row_start'			=> '<tr class="even">',
				'row_alt_start'		=> '<tr class="odd">'
		),
			'action_url' => $this->_action_url,
			'base_url' => $this->_base_url,
		);
		
		// load css 
		$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->EE->config->item('theme_folder_url').'third_party/data_import/css/cp.css" />');	
			
		$this->EE->cp->load_package_js('cp');	
		$this->EE->load->vars($vars);			
	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{
		return $this->import_list();	
	}
	
	public function import_list()
	{
		$js = "baseUrl='".htmlspecialchars_decode($this->_base_url)."'";

		$this->EE->javascript->output($js);		
		$options['order_by'] = 'title';
		$vars['import_list'] = $this->EE->data_import_list_model->get($options);
		return $this->EE->load->view('import_list', $vars, TRUE);
	}
	
	public function add_import_item()
	{
		if ( ! empty($_POST))
		{
			$data['title'] = $this->EE->input->post('title');
			$import_id = $this->EE->data_import_list_model->save($data);
			$this->EE->session->set_flashdata(
				'message_success',
				$this->EE->lang->line('import_item_saved')
			);

			$this->EE->functions->redirect($this->_base_url.AMP.'method=settings'.AMP.'import_id='.$import_id);
		}
	}
		
	public function remove_import_item()
	{
		if ($this->EE->input->get('import_id'))
		{
			$data['import_id'] 	= $this->EE->input->get('import_id');
			$this->EE->data_import_list_model->delete($data);
			$js = "$.ee_notice( '".$this->EE->lang->line('import_item_deleted')."' , {type: 'success', open:true});";
			$this->_make_js($js);
			exit;
		}
	}		
	public function update_import_item()
	{
		if ($this->EE->input->get('import_id'))
		{
			$data['title'] 		= $this->EE->input->get('title');
			$data['import_id'] 	= $this->EE->input->get('import_id');
			$this->EE->data_import_list_model->save($data);
			$js = "\$('#{$data['import_id']}').text('{$data['title']}').show();\$('#edit_import_{$data['import_id']}').remove();$.ee_notice( '".$this->EE->lang->line('import_item_saved')."' , {type: 'success', open:true});";
			$this->_make_js($js);
			exit;
		}
	}
	
	public function settings()
	{
		$this->EE->view->cp_page_title = lang('data_import_settings');
		// check if database connection data is correct
		$vars = $this->EE->data_import_config->items();
		$vars['import_id'] = $this->EE->input->get_post('import_id');
		$vars = array_merge($vars, $this->EE->data_import_list_model->get_settings($vars['import_id']));
		if( ! $this->EE->data_import_remote_model->connect($vars))
		{
			$this->EE->session->set_flashdata('message_failure', lang('invalid_database_settings'));
			$this->EE->functions->redirect($this->_base_url.AMP.'method=database_settings');
		}
		// check for submitted general form
		if ( ! empty($_POST['sbt']))
		{
			$upd['import_id'] = $this->EE->input->post('import_id');
			$upd['settings'] = serialize($_POST);
			$this->EE->data_import_list_model->save($upd);

			$this->EE->session->set_flashdata(
			'message_success',
			$this->EE->lang->line('preferences_updated')
			);

			$this->EE->functions->redirect($this->_base_url.AMP.'method=settings'.AMP.'import_id='.$upd['import_id']);			
		}

		$remote_tables = array(''=>$this->EE->lang->line('select_table'))+$this->EE->data_import_remote_model->get_tables();
		$channels = $this->EE->data_import_model->get_channels();
		$member_groups = $this->EE->data_import_model->get_member_groups();
		$remote_table_keys = array(''=>$this->EE->lang->line('select_key'));


		if($vars['remote_table'])
		$remote_table_keys = array_merge($remote_table_keys, $this->EE->data_import_remote_model->get_table_keys_ext($vars['remote_table']));

		$channel_fields = array();
		if($this->EE->input->get('channel'))
		{
			$channel_fields = $this->EE->data_import_model->get_channel_fields($this->EE->input->get('channel'));
			$vars['statuses'] = $this->_get_channel_status_groups($this->EE->input->get('channel'));
			$vars['channel_category_groups'] = $this->EE->data_import_model->get_channel_category_groups($this->EE->input->get('channel'));
		}
		elseif ($vars['channel'])
		{
			$vars['statuses'] = $this->_get_channel_status_groups($vars['channel']);
			$channel_fields = $this->EE->data_import_model->get_channel_fields($vars['channel']);
			$vars['channel_category_groups'] = $this->EE->data_import_model->get_channel_category_groups($vars['channel']);
		}
		
		$vars['channels'] 			= $channels;
		$vars['channel_fields'] 	= $channel_fields;
		$vars['all_fields'] 			= $this->EE->data_import_model->fields;
		$vars['remote_tables'] 		= $remote_tables;
		$vars['remote_table_keys']	= $remote_table_keys;
		$vars['member_groups']		= $member_groups;
		$vars['view_assign_fields'] = '';

		if($table = $this->EE->input->get('table'))
		{
			$table_keys = array_merge($table_keys, $this->EE->data_import_model->get_table_keys($table));
			$this->_make_table_keys($table_keys, 'key_field');
			
			exit;
		}
		
		if($this->EE->input->get('getcondition'))
		{
			extract($_GET);
			$tables = array($table1,$table2);
			$cond = $this->_join_condition($tables);
			$this->_make_js( "\$('#cond{$table1}').remove();");
			$js = "";
			foreach ($cond as $table => $c) 
			{

				$input = addslashes("<span style='width:30%' id='cond{$table1}'>".lang('ON').nbs().form_input("condition[{$table1}_{$table2}]", @$c['cond'], 'class="condition" style="width:60%"'))."</span>";

				$js .= '$(".'.$table1.' select").after("'.$input.'");';
				
					
				break;			
			}
			$this->_make_js($js);
		}
		if($this->EE->input->get('changefields') or $this->EE->input->get('channel'))
		{
			extract($_POST);
			$vars['remote_table_keys']	= $remote_table_keys = array_merge($remote_table_keys, $this->EE->data_import_remote_model->get_table_keys_ext($remote_table));
			$this->_make_table_keys($remote_table_keys, 'remote_key_field');

			if($channel_fields)
			{
				$js = '$("#assign_fields").html("'.addslashes($vars['view_assign_fields'] = $this->EE->load->view('assign_fields', $vars, TRUE)).'")';
				$js = str_replace("\r", '', $js);
				$js = str_replace("\n", '', $js);
				$this->_make_js($js);
			}
			exit;
		}


		if($vars['channel'])
		{
			$vars['view_assign_fields'] = $this->EE->load->view('assign_fields', $vars, TRUE);
		}

		$this->EE->javascript->compile();
		
		$view = $this->EE->load->view('assign_settings', $vars, TRUE);

		return $view;
	}	
	
	private function _get_channel_status_groups($channel_id)
	{
		$channel_data = $this->EE->data_import_model->get_row(array('channel_id'=>$channel_id), 'channels');
		$gr_id = $channel_data['status_group'] ? $channel_data['status_group'] : 1;
		$statuses = $this->EE->data_import_model->get(array('group_id'=>$gr_id), 'statuses');
		foreach ($statuses as $k => $v) 
		{
			$statuses_sel[$v['status']] = ucfirst($v['status']);
		}
		
		return $statuses_sel;
	}
	
	private function _make_table_keys($table_keys, $select_name)
	{
		$js = "<script>";
		$js .= '$("select[name='.$select_name.'] option").remove();';
		foreach ($table_keys as $key)
		{
			$js .= '$("select[name='.$select_name.']").append($("<option></option>").attr("value","'.$key.'").text("'.$key.'"));';
		}
		$js .= '$("#loading").remove();';
		$js .= "</script>";
		echo $js;
	}
	
	private function _make_js($cont)
	{
		$js = "<script>";
		$js .= $cont;
		$js .= "</script>\n";
		echo $js;
	}
		
	public function ftp_settings()
	{
		$this->EE->view->cp_page_title = lang('ftp_settings');

		// check for submitted general form
		if ( ! empty($_POST))
		{
			$this->_save_settings($this->_base_url.AMP.'method=ftp_settings');
		}

		$vars = $this->EE->data_import_config->items();
		return  $this->EE->load->view('ftp_settings', $vars, TRUE);
	}
		
	public function database_settings()
	{
		$this->EE->view->cp_page_title = lang('database_settings');

		// check for submitted general form
		if ( ! empty($_POST))
		{
			$this->_save_settings($this->_base_url.AMP.'method=database_settings');
		}

		$vars = $this->EE->data_import_config->items();
		return  $this->EE->load->view('database_settings', $vars, TRUE);
	}
		
	public function usage()
	{
		$this->EE->cp->set_variable('cp_page_title', lang('Help'));

		return  $this->EE->load->view('help', '', TRUE);
	}	
	
	private function _save_settings($redirect)
	{
//		myd($_POST,1);
		$this->EE->data_import_config->items($_POST);
		$this->EE->data_import_config->save();

		$this->EE->session->set_flashdata(
		'message_success',
		$this->EE->lang->line('preferences_updated')
		);

		$this->EE->functions->redirect($redirect);
	}
	
	private function _join_condition($tables)
	{
		foreach ($tables as $table)
		{
			$table_fields_full[$table] = $this->EE->data_import_remote_model->rdb->field_data($table);
			foreach ($table_fields_full[$table] as $k => $v) 
			{
				if ($v->primary_key)
				$primary[$table] = $v->name; 
			}
			$table_fields[$table] = $this->EE->data_import_remote_model->get_table_keys($table);
		}
		$passed_checks = array();
		foreach ($tables as $k => $table)
		{
			$tmp = $tables;
			unset($tmp[$k]);
			$cond[$table] = "";
			foreach ($tmp as $table1)
			{
				
				if(isset($table_fields[$table1][@$primary[$table]]) and ! isset($passed_checks[$table.$table1]))
				{
					$passed_checks[$table1.$table] = $table; // avoid reverse
					$passed_checks2[] = $table; // avoid reverse
					$cond[$table]['cond'] = "{$table}.{$primary[$table]}={$table1}.{$table_fields[$table1][$primary[$table]]}";
					$cond[$table]['table'] = $table1;
					$cond[$table]['type'] = 'left';
				} 
				else 
				{
					foreach ($table_fields[$table1] as $field)
					{
						if(isset($table_fields[$table][$field]))
						{
							$passed_checks[$table1.$table] = $table; // avoid reverse
							$passed_checks2[] = $table; // avoid reverse
							$cond[$table]['cond'] = "{$table}.{$field}={$table1}.{$field}";
							$cond[$table]['table'] = $table1;
						}
					}
				}
			}
			
		}
		
		foreach ($passed_checks as $k => $table)
		{
			$ok = false;
			foreach ($tables as $k1 => $table1)
			{
				if(strstr($k,$table1)) $ok = true;
			}
			if($ok and !$cond[$table1])
			unset($cond[$table1]);
		}
		return $cond;
	}
	
	public function do_import()
	{
		$this->EE->load->library(array('data_import_process'));
		$this->EE->data_import_process->start($this->EE->input->get('import'));
		$this->EE->session->set_flashdata(
		'message_success',
		$this->EE->lang->line('Done')
		);

		$this->EE->functions->redirect($this->_base_url);
	}
}

if( ! function_exists("myd"))
{
function myd($arr,$exit=false){
	if (isset($GLOBALS['debugifon']) and !isset($_REQUEST['debug'])) {
		return ;
	} 
	
	if ($exit === 2) 
	ob_start();
	if (is_array($arr)) {
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
	} elseif (is_string($arr)) {
		echo $arr."<br>";
	} elseif (is_object($arr)) {
		echo "<pre>";
		var_export($arr)."<br>";
		echo "</pre>";
	} else {
		echo ($arr)."<br>";
	}

	if ($exit === 2) {
		$cont = ob_get_contents();
		ob_end_clean();
		file_put_contents('myd2.debug.txt', str_replace("<br>", "\n", $cont), FILE_APPEND );
	}
	if ($exit === 1) exit;
}
}
/* End of file mcp.data_import.php */
/* Location: /system/expressionengine/third_party/data_import/mcp.data_import.php */