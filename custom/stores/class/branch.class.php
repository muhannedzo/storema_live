<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2023 SuperAdmin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/branch.class.php
 * \ingroup     stores
 * \brief       This file is a CRUD class file for Branch (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for Branch
 */
class Branch extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'stores';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'branch';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'stores_branch';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for branch. Must be a 'fa-xxx' fontawesome code (or 'fa-xxx_fa_color_size') or 'branch@stores' if picto is file 'img/object_branch.png'.
	 */
	public $picto = 'fa-store';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CANCELED = 9;


	/**
	 *  'type' field format:
	 *  	'integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]',
	 *  	'select' (list of values are in 'options'),
	 *  	'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:Sortfield]]]]',
	 *  	'chkbxlst:...',
	 *  	'varchar(x)',
	 *  	'text', 'text:none', 'html',
	 *   	'double(24,8)', 'real', 'price',
	 *  	'date', 'datetime', 'timestamp', 'duration',
	 *  	'boolean', 'checkbox', 'radio', 'array',
	 *  	'mail', 'phone', 'url', 'password', 'ip'
	 *		Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM' or '!empty($conf->multicurrency->enabled)' ...)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 or 2 if field can be used for measure. Field type must be summable like integer or double(24,8). Use 1 in most cases, or 2 if you don't want to see the column total into list (for example for percentage)
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
	 *  'help' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arrayofkeyval' to set a list of values if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel"). Note that type can be 'integer' or 'varchar'
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *	'validate' is 1 if need to validate with $this->validateField()
	 *  'copytoclipboard' is 1 or 2 to allow to add a picto to copy value into clipboard (1=picto after label, 2=picto after value)
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		"rowid" => array("type"=>"integer", "label"=>"TechnicalID", "enabled"=>"1", 'position'=>1, 'notnull'=>1, "visible"=>"0", "noteditable"=>"1", "index"=>"1", "css"=>"left", "comment"=>"Id"),
		"fk_soc" => array("type"=>"integer:Societe:societe/class/societe.class.php", "label"=>"ThirdParty", "enabled"=>"1", 'position'=>14, 'notnull'=>-1, "visible"=>"1", "index"=>"1", "searchall"=>"1", "css"=>"maxwidth500 widthcentpercentminusxx", "help"=>"OrganizationEventLinkToThirdParty", "validate"=>"1",),
		"date_creation" => array("type"=>"datetime", "label"=>"DateCreation", "enabled"=>"1", 'position'=>500, 'notnull'=>1, "visible"=>"-2",),
		"tms" => array("type"=>"timestamp", "label"=>"DateModification", "enabled"=>"1", 'position'=>501, 'notnull'=>0, "visible"=>"-2",),
		"fk_user_creat" => array("type"=>"integer:User:user/class/user.class.php", "label"=>"UserAuthor", "enabled"=>"1", 'position'=>510, 'notnull'=>1, "visible"=>"-2",),
		"fk_user_modif" => array("type"=>"integer:User:user/class/user.class.php", "label"=>"UserModif", "picto"=>"user", "enabled"=>"1", 'position'=>511, 'notnull'=>-1, "visible"=>"-2",),
		"status" => array("type"=>"integer", "label"=>"Status", "enabled"=>"1", 'position'=>2000, 'notnull'=>1, "visible"=>"1", "index"=>"1", "arrayofkeyval"=>array("0" => "Entwurf", "1" => "Freigegeben", "9" => "Storniert"), "validate"=>"1",),
		"b_number" => array("type"=>"varchar(64)", "label"=>"BranchesNumber", "enabled"=>"1", 'position'=>3, 'notnull'=>1, "visible"=>"1", "index"=>"1", "searchall"=>"1",),
		"street" => array("type"=>"varchar(64)", "label"=>"Street", "enabled"=>"1", 'position'=>4, 'notnull'=>0, "visible"=>"-1",),
		"house_number" => array("type"=>"varchar(14)", "label"=>"HouseNumber", "enabled"=>"1", 'position'=>5, 'notnull'=>0, "visible"=>"-1",),
		"country" => array("type"=>"varchar(64)", "label"=>"Country", "enabled"=>"1", 'position'=>10, 'notnull'=>0, "visible"=>"-1",),
		"zip_code" => array("type"=>"varchar(14)", "label"=>"PostalCode", "enabled"=>"1", 'position'=>7, 'notnull'=>0, "visible"=>"-1",),
		"city" => array("type"=>"varchar(64)", "label"=>"City", "enabled"=>"1", 'position'=>8, 'notnull'=>0, "visible"=>"-1",),
		"state" => array("type"=>"varchar(128)", "label"=>"State", "enabled"=>"1", 'position'=>9, 'notnull'=>0, "visible"=>"-1",),
		"phone" => array("type"=>"varchar(20)", "label"=>"Phone", "enabled"=>"1", 'position'=>13, 'notnull'=>0, "visible"=>"-1",),
		"images" => array("type"=>"text", "label"=>"Images", "enabled"=>"1", 'position'=>11, 'notnull'=>0, "visible"=>"0",),
		"ref" => array("type"=>"varchar(128)", "label"=>"Ref", "enabled"=>"1", 'position'=>2, 'notnull'=>1, "visible"=>"1", "index"=>"1", "searchall"=>"1", "validate"=>"1", "comment"=>"Reference of object"),
		"state_id" => array("type"=>"integer", "label"=>"state_id", "enabled"=>"1", 'position'=>15, 'notnull'=>0, "visible"=>"0",),
		"country_id" => array("type"=>"integer", "label"=>"country_id", "enabled"=>"1", 'position'=>16, 'notnull'=>0, "visible"=>"0",),
		"store_manager" => array("type"=>"varchar(50)", "label"=>"Storemanager", "enabled"=>"1", 'position'=>17, 'notnull'=>0, "visible"=>"0",),
		"district_manager" => array("type"=>"varchar(50)", "label"=>"Districtmanager", "enabled"=>"1", 'position'=>18, 'notnull'=>0, "visible"=>"0",),
		"days" => array("type"=>"text", "label"=>"Days", "enabled"=>"1", 'position'=>19, 'notnull'=>0, "visible"=>"0",),
		"opening" => array("type"=>"date", "label"=>"Opened_in", "enabled"=>"1", 'position'=>20, 'notnull'=>0, "visible"=>"0",),
		"closing" => array("type"=>"date", "label"=>"Closed_in", "enabled"=>"1", 'position'=>21, 'notnull'=>0, "visible"=>"0",),
		"cashers_desks" => array("type"=>"integer", "label"=>"Cashers_desks", "enabled"=>"1", 'position'=>22, 'notnull'=>0, "visible"=>"0",),
		"store_size" => array("type"=>"integer", "label"=>"Store_size", "enabled"=>"1", 'position'=>23, 'notnull'=>0, "visible"=>"0",),
		"sales_area" => array("type"=>"integer", "label"=>"Sales_area", "enabled"=>"1", 'position'=>24, 'notnull'=>0, "visible"=>"0",),
		"warehouse_area" => array("type"=>"integer", "label"=>"Warehouse_area", "enabled"=>"1", 'position'=>25, 'notnull'=>0, "visible"=>"0",),
		"branch_height" => array("type"=>"integer", "label"=>"Branch_height", "enabled"=>"1", 'position'=>26, 'notnull'=>0, "visible"=>"0",),
		"goods" => array("type"=>"text", "label"=>"Goods", "enabled"=>"1", 'position'=>27, 'notnull'=>0, "visible"=>"0",),
		"import_key" => array("type"=>"varchar(14)", "label"=>"importId", "enabled"=>"1", 'position'=>1000, 'notnull'=>-1, "visible"=>"-2",),
		"fk_user_author" => array("type"=>"integer:User:user/class/user.class.php", "label"=>"UserAuthor1", "enabled"=>"1", 'position'=>512, 'notnull'=>0, "visible"=>"-2",),
		"excel_imported" => array("type"=>"integer", "label"=>"excel_imported", "enabled"=>"1", 'position'=>50, 'notnull'=>0, "visible"=>"1",),
		"customer_name" => array("type"=>"varchar(128)", "label"=>"customerName", "enabled"=>"1", 'position'=>50, 'notnull'=>0, "visible"=>"1",),
	);
	public $rowid;
	public $fk_soc;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $status;
	public $b_number;
	public $street;
	public $house_number;
	public $country;
	public $zip_code;
	public $city;
	public $state;
	public $phone;
	public $images;
	public $ref;
	public $state_id;
	public $country_id;
	public $store_manager;
	public $district_manager;
	public $days;
	public $opening;
	public $closing;
	public $cashers_desks;
	public $store_size;
	public $sales_area;
	public $warehouse_area;
	public $branch_height;
	public $goods;
	public $import_key;
	public $fk_user_author;
	public $excel_imported;
	public $customer_name;
	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	// public $table_element_line = 'stores_branchline';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	// public $fk_element = 'fk_branch';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	// public $class_element_line = 'Branchline';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array();

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('stores_branchdet');

	// /**
	//  * @var BranchLine[]     Array of subtable lines
	//  */
	// public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid']) && !empty($this->fields['ref'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->stores->branch->read) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		$resultcreate = $this->createCommon($user, $notrigger);

		//$resultvalidate = $this->validate($user, $notrigger);

		return $resultcreate;
	}

	/**
	 * Clone an object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $langs, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetchCommon($fromid);
		if ($result > 0 && !empty($object->table_element_line)) {
			$object->fetchLines();
		}

		// get lines so they will be clone
		//foreach($this->lines as $line)
		//	$line->fetch_optionals();

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);

		// Clear fields
		if (property_exists($object, 'ref')) {
			$object->ref = empty($this->fields['ref']['default']) ? "Copy_Of_".$object->ref : $this->fields['ref']['default'];
		}
		if (property_exists($object, 'label')) {
			$object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
		}
		if (property_exists($object, 'status')) {
			$object->status = self::STATUS_DRAFT;
		}
		if (property_exists($object, 'date_creation')) {
			$object->date_creation = dol_now();
		}
		if (property_exists($object, 'date_modification')) {
			$object->date_modification = null;
		}
		// ...
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0) {
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option) {
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey])) {
					//var_dump($key);
					//var_dump($clonedObj->array_options[$key]); exit;
					unset($object->array_options[$key]);
				}
			}
		}

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->createCommon($user);
		if ($result < 0) {
			$error++;
			$this->error = $object->error;
			$this->errors = $object->errors;
		}

		if (!$error) {
			// copy internal contacts
			if ($this->copy_linked_contact($object, 'internal') < 0) {
				$error++;
			}
		}

		if (!$error) {
			// copy external contacts if same company
			if (!empty($object->socid) && property_exists($this, 'fk_soc') && $this->fk_soc == $object->socid) {
				if ($this->copy_linked_contact($object, 'external') < 0) {
					$error++;
				}
			}
		}

		unset($object->context['createfromclone']);

		// End
		if (!$error) {
			$this->db->commit();
			return $object;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) {
			$this->fetchLines();
		}
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon();
		return $result;
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= " WHERE t.entity IN (".getEntity($this->element).")";
		} else {
			$sql .= " WHERE 1 = 1";
		}
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key." = ".((int) $value);
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key." = '".$this->db->idate($value)."'";
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key." IN (".$this->db->sanitize($this->db->escape($value)).")";
				} else {
					$sqlwhere[] = $key." LIKE '%".$this->db->escape($value)."%'";
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= " AND (".implode(" ".$filtermode." ", $sqlwhere).")";
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = false)
	{
		if ($this->status < 0) {
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}


	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf, $langs;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED) {
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->stores->branch->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->stores->branch->branch_advance->validate))))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) { // empty should not happened, but when it occurs, the test save life
			$num = $this->getNextNumRef();
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ref = '".$this->db->escape($num)."',";
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (!empty($this->fields['date_validation'])) {
				$sql .= ", date_validation = '".$this->db->idate($now)."'";
			}
			if (!empty($this->fields['fk_user_valid'])) {
				$sql .= ", fk_user_valid = ".((int) $user->id);
			}
			$sql .= " WHERE rowid = ".((int) $this->id);

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql) {
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger) {
				// Call trigger
				$result = $this->call_trigger('BRANCH_VALIDATE', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
		}

		if (!$error) {
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref)) {
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'branch/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'branch/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++; $this->error = $this->db->lasterror();
				}

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->stores->dir_output.'/branch/'.$oldref;
				$dirdest = $conf->stores->dir_output.'/branch/'.$newref;
				if (!$error && file_exists($dirsource)) {
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest)) {
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->stores->dir_output.'/branch/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry) {
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error) {
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error) {
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->stores->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->stores->stores_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'BRANCH_UNVALIDATE');
	}

	/**
	 *	Set cancel status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_VALIDATED) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->stores->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->stores->stores_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'BRANCH_CANCEL');
	}

	/**
	 *	Set back to validated status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function reopen($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_CANCELED) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->stores->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->stores->stores_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'BRANCH_REOPEN');
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';

		$label = '<span class="fa fa-store paddingright classfortooltip" style=" color: #6c6aa8;"></span>'.' <u>'.$langs->trans("Branch").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/stores/branch_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($url && $add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowBranch");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink' || empty($url)) {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink' || empty($url)) {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$result .= '<span class="fa fa-store paddingright classfortooltip" style=" color: #6c6aa8;"></span>';
			}
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) {
			$result .= $this->ref;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('branchdao'));
		$parameters = array('id'=>$this->id, 'getnomurl' => &$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLabelStatus($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			//$langs->load("stores@stores");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
		}

		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) {
			$statusType = 'status6';
		}

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = "SELECT rowid,";
		$sql .= " date_creation as datec, tms as datem,";
		$sql .= " fk_user_creat, fk_user_modif";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);

				$this->id = $obj->rowid;

				$this->user_creation_id = $obj->fk_user_creat;
				$this->user_modification_id = $obj->fk_user_modif;
				if (!empty($obj->fk_user_valid)) {
					$this->user_validation_id = $obj->fk_user_valid;
				}
				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = empty($obj->datem) ? '' : $this->db->jdate($obj->datem);
				if (!empty($obj->datev)) {
					$this->date_validation   = empty($obj->datev) ? '' : $this->db->jdate($obj->datev);
				}
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		// Set here init that are not commonf fields
		// $this->property1 = ...
		// $this->property2 = ...

		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new BranchLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_branch = '.((int) $this->id)));

		if (is_numeric($result)) {
			$this->error = $objectline->error;
			$this->errors = $objectline->errors;
			return $result;
		} else {
			$this->lines = $result;
			return $this->lines;
		}
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("stores@stores");

		if (empty($conf->global->STORES_BRANCH_ADDON)) {
			$conf->global->STORES_BRANCH_ADDON = 'mod_branch_standard';
		}

		if (!empty($conf->global->STORES_BRANCH_ADDON)) {
			$mybool = false;

			$file = $conf->global->STORES_BRANCH_ADDON.".php";
			$classname = $conf->global->STORES_BRANCH_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir) {
				$dir = dol_buildpath($reldir."core/modules/stores/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false) {
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1') {
					return $numref;
				} else {
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		} else {
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
			return "";
		}
	}

	/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf, $langs;

		$result = 0;
		$includedocgeneration = 0;

		$langs->load("stores@stores");

		if (!dol_strlen($modele)) {
			$modele = 'standard_branch';

			if (!empty($this->model_pdf)) {
				$modele = $this->model_pdf;
			} elseif (!empty($conf->global->BRANCH_ADDON_PDF)) {
				$modele = $conf->global->BRANCH_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/stores/doc/";

		if ($includedocgeneration && !empty($modele)) {
			$result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		}

		return $result;
	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 * Use public function doScheduledJob($param1, $param2, ...) to get parameters
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}

	public function select_store($thirdId = null, $selectedStore = null)
	{
		// var_dump($thirdId);
		$records = array();

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."stores_branch ";
		if($thirdId && $thirdId != -1){
			$sql .= "WHERE fk_soc = ".$thirdId;
		}

		$resql = $this->db->query($sql)->fetch_all();
		
		print '<span class="fas fa-store paddingright" style="color: #6c6aa8;"></span>
					<select class="select2-selection select2-selection--single flat minwidth200" name="options_fk_store" id="options_fk_store">
						<option value="" disabled selected></option>';
						
						foreach($resql as $elem) {
							if($selectedStore && $selectedStore == $elem[0]) {
								print '<option value="'.$elem[0].'" selected>'.$elem[7].'</option>';
							} else {
								print '<option value="'.$elem[0].'">'.$elem[7].'</option>';
							}
						}
					
					print '</select>
					
					<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
					<script>
						$(document).ready(function() {
							$("#options_fk_store").select2({
								width: "43%",
								placeholder: "",
							});
						});
					</script>
					';
	}

	// public function select_order_customer($choosed)
	// {	
	// 	print '	<span class="fas fa-receipt paddingright" style=" color: #6c6aa8;"></span>
	// 			<select class="flat minwidth100 maxwidthonsmartphone" name="options_ordervia" id="options_ordervia">
	// 				<option value="0">&nbsp;</option>
	// 				<option value="Telephone">Telephone</option>
	// 				<option value="E-Mail">E-Mail</option>
	// 				<option value="Online">Online</option>
	// 				<option value="Storema">Storema</option>
	// 			</select>';
	// 	print   "\n";
	// }
	public function select_order_customer($choosed)
	{   
		print '  <span class="fas fa-receipt paddingright" style="color: #6c6aa8;"></span>
				<select class="flat minwidth100 maxwidthonsmartphone" name="options_ordervia" id="options_ordervia">';
		
		// Array of options
		$options = array(
			array("value" => "0", "label" => "&nbsp;"),
			array("value" => "Telephone", "label" => "Telephone"),
			array("value" => "E-Mail", "label" => "E-Mail"),
			array("value" => "Online", "label" => "Online"),
			array("value" => "Storema", "label" => "Storema")
		);

		// Loop through options
		foreach ($options as $option) {
			print '<option value="' . $option["value"] . '"';
			
			// Check if the option value matches the $choosed value
			if ($option["value"] == $choosed) {
				print ' selected';
			}
			
			print '>' . $option["label"] . '</option>';
		}
		
		print '</select>';
		print "\n";
	}

	public function select_stores_distance($thirdId = null, $selectedStore = null)
	{
		// var_dump($thirdId);
		$records = array();

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."stores_branch ";
		if($thirdId && $thirdId != -1){
			$sql .= "WHERE fk_soc = ".$thirdId;
		}

		$resql = $this->db->query($sql)->fetch_all();
		// var_dump($resql);
		print '<span class="fas fa-store paddingright" style=" color: #6c6aa8;"></span>
					<select class="select2-selection select2-selection--single flat minwidth200" name="options_fk_store" id="options_fk_store">
						<option value="" disabled selected></option>';
							foreach($resql as $elem){
								if($selectedStore && $selectedStore == $elem[0]){
									print '<option value="'.$elem[11].' '.$elem[8].','.$elem[9].'|'.$elem[0].'" selected>'.$elem[7].'</option>';
								}else{
									print '<option value="'.$elem[11].' '.$elem[8].','.$elem[9].'|'.$elem[0].'">'.$elem[7].'</option>';
								}
							}
		print   '</select>'."\n";
	}

	public function select_internal_users()
	{
		$records = array();

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."user WHERE fk_soc is null;";

		$resql = $this->db->query($sql)->fetch_all();
		// var_dump($resql);
		print ' <select class="select2-selection select2-selection--single flat minwidth200" name="options_fk_internal" id="options_fk_internal">
						<option value="" disabled selected></option>';
							foreach($resql as $elem){
								print '<option value="'.$elem[22].' '.$elem[21].'|'.$elem[0].'">'.$elem[20].' '.$elem[19].'</option>';
							}
		print   '</select>'."\n";
	}

	public function select_stores($thirdId = null, $selectedStore = null)
	{
		
		$records = array();

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."stores_branch ";
		if($thirdId && $thirdId != -1){
			$sql .= "WHERE fk_soc = ".$thirdId;
		}

		$resql = $this->db->query($sql)->fetch_all();
		
		return $resql;
	}

	function print_fleche_navigationId($id, $page, $file, $options = '', $nextpage = 0, $betweenarrows = '', $afterarrows = '', $limit = -1, $totalnboflines = 0, $hideselectlimit = 0, $beforearrows = '')
{
	global $conf, $langs;

	print '<div class="pagination"><ul>';
	if ($beforearrows) {
		print '<li class="paginationbeforearrows">';
		print $beforearrows;
		print '</li>';
	}
	if ((int) $limit > 0 && empty($hideselectlimit)) {
		$pagesizechoices = '10:10,15:15,20:20,30:30,40:40,50:50,100:100,250:250,500:500,1000:1000';
		$pagesizechoices .= ',5000:5000,10000:10000,20000:20000';
		//$pagesizechoices.=',0:'.$langs->trans("All");     // Not yet supported
		//$pagesizechoices.=',2:2';
		if (!empty($conf->global->MAIN_PAGESIZE_CHOICES)) {
			$pagesizechoices = $conf->global->MAIN_PAGESIZE_CHOICES;
		}

		print '<li class="pagination">';
		print '<select class="flat selectlimit" name="limit" title="'.dol_escape_htmltag($langs->trans("MaxNbOfRecordPerPage")).'">';
		$tmpchoice = explode(',', $pagesizechoices);
		$tmpkey = $limit.':'.$limit;
		if (!in_array($tmpkey, $tmpchoice)) {
			$tmpchoice[] = $tmpkey;
		}
		$tmpkey = $conf->liste_limit.':'.$conf->liste_limit;
		if (!in_array($tmpkey, $tmpchoice)) {
			$tmpchoice[] = $tmpkey;
		}
		asort($tmpchoice, SORT_NUMERIC);
		foreach ($tmpchoice as $val) {
			$selected = '';
			$tmp = explode(':', $val);
			$key = $tmp[0];
			$val = $tmp[1];
			if ($key != '' && $val != '') {
				if ((int) $key == (int) $limit) {
					$selected = ' selected="selected"';
				}
				print '<option name="'.$key.'"'.$selected.'>'.dol_escape_htmltag($val).'</option>'."\n";
			}
		}
		print '</select>';
		if ($conf->use_javascript_ajax) {
			print '<!-- JS CODE TO ENABLE select limit to launch submit of page -->
            		<script>
                	jQuery(document).ready(function () {
            	  		jQuery(".selectlimit").change(function() {
                            console.log("Change limit. Send submit");
                            $(this).parents(\'form:first\').submit();
            	  		});
                	});
            		</script>
                ';
		}
		print '</li>';
	}
	if ($page > 0) {
		print '<li class="pagination paginationpage paginationpageleft"><a class="paginationprevious" href="'.$file.'?id='.$id.'&page='.($page - 1).$options.'"><i class="fa fa-chevron-left" title="'.dol_escape_htmltag($langs->trans("Previous")).'"></i></a></li>';
	}
	if ($betweenarrows) {
		print '<!--<div class="betweenarrows nowraponall inline-block">-->';
		print $betweenarrows;
		print '<!--</div>-->';
	}
	if ($nextpage > 0) {
		print '<li class="pagination paginationpage paginationpageright"><a class="paginationnext" href="'.$file.'?id='.$id.'&page='.($page + 1).$options.'"><i class="fa fa-chevron-right" title="'.dol_escape_htmltag($langs->trans("Next")).'"></i></a></li>';
	}
	if ($afterarrows) {
		print '<li class="paginationafterarrows">';
		print $afterarrows;
		print '</li>';
	}
	print '</ul></div>'."\n";
}

	function print_barre_listeId($id, $titre, $page, $file, $options = '', $sortfield = '', $sortorder = '', $morehtmlcenter = '', $num = -1, $totalnboflines = '', $picto = 'generic', $pictoisfullpath = 0, $morehtmlright = '', $morecss = '', $limit = -1, $hideselectlimit = 0, $hidenavigation = 0, $pagenavastextinput = 0, $morehtmlrightbeforearrow = '')
{
	global $conf, $langs;

	$savlimit = $limit;
	$savtotalnboflines = $totalnboflines;
	$totalnboflines = abs((int) $totalnboflines);

	if ($picto == 'setup') {
		$picto = 'title_setup.png';
	}
	if (($conf->browser->name == 'ie') && $picto == 'generic') {
		$picto = 'title.gif';
	}
	if ($limit < 0) {
		$limit = $conf->liste_limit;
	}
	if ($savlimit != 0 && (($num > $limit) || ($num == -1) || ($limit == 0))) {
		$nextpage = 1;
	} else {
		$nextpage = 0;
	}
	//print 'totalnboflines='.$totalnboflines.'-savlimit='.$savlimit.'-limit='.$limit.'-num='.$num.'-nextpage='.$nextpage;

	print "\n";
	print "<!-- Begin title -->\n";
	print '<table class="centpercent notopnoleftnoright table-fiche-title'.($morecss ? ' '.$morecss : '').'"><tr>'; // maring bottom must be same than into load_fiche_tire

	// Left

	if ($picto && $titre) {
		print '<td class="nobordernopadding widthpictotitle valignmiddle col-picto">'.img_picto('', $picto, 'class="valignmiddle pictotitle widthpictotitle"', $pictoisfullpath).'</td>';
	}
	print '<td class="nobordernopadding valignmiddle col-title">';
	print '<div class="titre inline-block">'.$titre;
	if (!empty($titre) && $savtotalnboflines >= 0 && (string) $savtotalnboflines != '') {
		print '<span class="opacitymedium colorblack paddingleft">('.$totalnboflines.')</span>';
	}
	print '</div></td>';

	// Center
	if ($morehtmlcenter) {
		print '<td class="nobordernopadding center valignmiddle col-center">'.$morehtmlcenter.'</td>';
	}

	// Right
	print '<td class="nobordernopadding valignmiddle right col-right">';
	print '<input type="hidden" name="pageplusoneold" value="'.((int) $page + 1).'">';
	if ($sortfield) {
		$options .= "&sortfield=".urlencode($sortfield);
	}
	if ($sortorder) {
		$options .= "&sortorder=".urlencode($sortorder);
	}
	// Show navigation bar
	$pagelist = '';
	if ($savlimit != 0 && ($page > 0 || $num > $limit)) {
		if ($totalnboflines) {	// If we know total nb of lines
			// Define nb of extra page links before and after selected page + ... + first or last
			$maxnbofpage = (empty($conf->dol_optimize_smallscreen) ? 4 : 0);

			if ($limit > 0) {
				$nbpages = ceil($totalnboflines / $limit);
			} else {
				$nbpages = 1;
			}
			$cpt = ($page - $maxnbofpage);
			if ($cpt < 0) {
				$cpt = 0;
			}

			if ($cpt >= 1) {
				if (empty($pagenavastextinput)) {
					$pagelist .= '<li class="pagination"><a href="'.$file.'?page=0'.$options.'">1</a></li>';
					if ($cpt > 2) {
						$pagelist .= '<li class="pagination"><span class="inactive">...</span></li>';
					} elseif ($cpt == 2) {
						$pagelist .= '<li class="pagination"><a href="'.$file.'?id='.$id.'&page=1'.$options.'">2</a></li>';
					}
				}
			}

			do {
				if ($pagenavastextinput) {
					if ($cpt == $page) {
						$pagelist .= '<li class="pagination"><input type="text" class="width25 center pageplusone" name="pageplusone" value="'.($page + 1).'"></li>';
						$pagelist .= '/';
					}
				} else {
					if ($cpt == $page) {
						$pagelist .= '<li class="pagination"><span class="active">'.($page + 1).'</span></li>';
					} else {
						$pagelist .= '<li class="pagination"><a href="'.$file.'?id='.$id.'&page='.$cpt.$options.'">'.($cpt + 1).'</a></li>';
					}
				}
				$cpt++;
			} while ($cpt < $nbpages && $cpt <= ($page + $maxnbofpage));

			if (empty($pagenavastextinput)) {
				if ($cpt < $nbpages) {
					if ($cpt < $nbpages - 2) {
						$pagelist .= '<li class="pagination"><span class="inactive">...</span></li>';
					} elseif ($cpt == $nbpages - 2) {
						$pagelist .= '<li class="pagination"><a href="'.$file.'?id='.$id.'&page='.($nbpages - 2).$options.'">'.($nbpages - 1).'</a></li>';
					}
					$pagelist .= '<li class="pagination"><a href="'.$file.'?id='.$id.'&page='.($nbpages - 1).$options.'">'.$nbpages.'</a></li>';
				}
			} else {
				//var_dump($page.' '.$cpt.' '.$nbpages);
				$pagelist .= '<li class="pagination paginationlastpage"><a href="'.$file.'?id='.$id.'&page='.($nbpages - 1).$options.'">'.$nbpages.'</a></li>';
			}
		} else {
			$pagelist .= '<li class="pagination"><span class="active">'.($page + 1)."</li>";
		}
	}

	if ($savlimit || $morehtmlright || $morehtmlrightbeforearrow) {
		$this->print_fleche_navigationId($id, $page, $file, $options, $nextpage, $pagelist, $morehtmlright, $savlimit, $totalnboflines, $hideselectlimit, $morehtmlrightbeforearrow); // output the div and ul for previous/last completed with page numbers into $pagelist
	}

	// js to autoselect page field on focus
	if ($pagenavastextinput) {
		print ajax_autoselect('.pageplusone');
	}

	print '</td>';

	print '</tr></table>'."\n";
	print "<!-- End title -->\n\n";
}

function getTitleFieldOfListId($id, $name, $thead = 0, $file = "", $field = "", $begin = "", $moreparam = "", $moreattrib = "", $sortfield = "", $sortorder = "", $prefix = "", $disablesortlink = 0, $tooltip = '', $forcenowrapcolumntitle = 0)
{
	global $conf, $langs, $form;
	//print "$name, $file, $field, $begin, $options, $moreattrib, $sortfield, $sortorder<br>\n";

	if ($moreattrib == 'class="right"') {
		$prefix .= 'right '; // For backward compatibility
	}

	$sortorder = strtoupper($sortorder);
	$out = '';
	$sortimg = '';

	$tag = 'th';
	if ($thead == 2) {
		$tag = 'div';
	}

	$tmpsortfield = explode(',', $sortfield);
	$sortfield1 = trim($tmpsortfield[0]); // If $sortfield is 'd.datep,d.id', it becomes 'd.datep'
	$tmpfield = explode(',', $field);
	$field1 = trim($tmpfield[0]); // If $field is 'd.datep,d.id', it becomes 'd.datep'

	if (empty($conf->global->MAIN_DISABLE_WRAPPING_ON_COLUMN_TITLE) && empty($forcenowrapcolumntitle)) {
		$prefix = 'wrapcolumntitle '.$prefix;
	}

	//var_dump('field='.$field.' field1='.$field1.' sortfield='.$sortfield.' sortfield1='.$sortfield1);
	// If field is used as sort criteria we use a specific css class liste_titre_sel
	// Example if (sortfield,field)=("nom","xxx.nom") or (sortfield,field)=("nom","nom")
	$liste_titre = 'liste_titre';
	if ($field1 && ($sortfield1 == $field1 || $sortfield1 == preg_replace("/^[^\.]+\./", "", $field1))) {
		$liste_titre = 'liste_titre_sel';
	}

	$tagstart = '<'.$tag.' class="'.$prefix.$liste_titre.'" '.$moreattrib;
	//$out .= (($field && empty($conf->global->MAIN_DISABLE_WRAPPING_ON_COLUMN_TITLE) && preg_match('/^[a-zA-Z_0-9\s\.\-:&;]*$/', $name)) ? ' title="'.dol_escape_htmltag($langs->trans($name)).'"' : '');
	$tagstart .= ($name && empty($conf->global->MAIN_DISABLE_WRAPPING_ON_COLUMN_TITLE) && empty($forcenowrapcolumntitle) && !dol_textishtml($name)) ? ' title="'.dol_escape_htmltag($langs->trans($name)).'"' : '';
	$tagstart .= '>';

	if (empty($thead) && $field && empty($disablesortlink)) {    // If this is a sort field
		$options = preg_replace('/sortfield=([a-zA-Z0-9,\s\.]+)/i', '', (is_scalar($moreparam) ? $moreparam : ''));
		$options = preg_replace('/sortorder=([a-zA-Z0-9,\s\.]+)/i', '', $options);
		$options = preg_replace('/&+/i', '&', $options);
		if (!preg_match('/^&/', $options)) {
			$options = '&'.$options;
		}

		$sortordertouseinlink = '';
		if ($field1 != $sortfield1) { // We are on another field than current sorted field
			if (preg_match('/^DESC/i', $sortorder)) {
				$sortordertouseinlink .= str_repeat('desc,', count(explode(',', $field)));
			} else { // We reverse the var $sortordertouseinlink
				$sortordertouseinlink .= str_repeat('asc,', count(explode(',', $field)));
			}
		} else { // We are on field that is the first current sorting criteria
			if (preg_match('/^ASC/i', $sortorder)) {	// We reverse the var $sortordertouseinlink
				$sortordertouseinlink .= str_repeat('desc,', count(explode(',', $field)));
			} else {
				$sortordertouseinlink .= str_repeat('asc,', count(explode(',', $field)));
			}
		}
		$sortordertouseinlink = preg_replace('/,$/', '', $sortordertouseinlink);
		$out .= '<a class="reposition" href="'.$file.'?id='.$id.'&sortfield='.$field.'&sortorder='.$sortordertouseinlink.'&begin='.$begin.$options.'"';
		//$out .= (empty($conf->global->MAIN_DISABLE_WRAPPING_ON_COLUMN_TITLE) ? ' title="'.dol_escape_htmltag($langs->trans($name)).'"' : '');
		$out .= '>';
	}
	if ($tooltip) {
		// You can also use 'TranslationString:keyfortooltiponclick' for a tooltip on click.
		if (preg_match('/:\w+$/', $tooltip)) {
			$tmptooltip = explode(':', $tooltip);
		} else {
			$tmptooltip = array($tooltip);
		}
		$out .= $form->textwithpicto($langs->trans($name), $langs->trans($tmptooltip[0]), 1, 'help', '', 0, 3, (empty($tmptooltip[1]) ? '' : 'extra_'.str_replace('.', '_', $field).'_'.$tmptooltip[1]));
	} else {
		$out .= $langs->trans($name);
	}

	if (empty($thead) && $field && empty($disablesortlink)) {    // If this is a sort field
		$out .= '</a>';
	}

	if (empty($thead) && $field) {    // If this is a sort field
		$options = preg_replace('/sortfield=([a-zA-Z0-9,\s\.]+)/i', '', (is_scalar($moreparam) ? $moreparam : ''));
		$options = preg_replace('/sortorder=([a-zA-Z0-9,\s\.]+)/i', '', $options);
		$options = preg_replace('/&+/i', '&', $options);
		if (!preg_match('/^&/', $options)) {
			$options = '&'.$options;
		}

		if (!$sortorder || $field1 != $sortfield1) {
			//$out.= '<a href="'.$file.'?sortfield='.$field.'&sortorder=asc&begin='.$begin.$options.'">'.img_down("A-Z",0).'</a>';
			//$out.= '<a href="'.$file.'?sortfield='.$field.'&sortorder=desc&begin='.$begin.$options.'">'.img_up("Z-A",0).'</a>';
		} else {
			if (preg_match('/^DESC/', $sortorder)) {
				//$out.= '<a href="'.$file.'?sortfield='.$field.'&sortorder=asc&begin='.$begin.$options.'">'.img_down("A-Z",0).'</a>';
				//$out.= '<a href="'.$file.'?sortfield='.$field.'&sortorder=desc&begin='.$begin.$options.'">'.img_up("Z-A",1).'</a>';
				$sortimg .= '<span class="nowrap">'.img_up("Z-A", 0, 'paddingright').'</span>';
			}
			if (preg_match('/^ASC/', $sortorder)) {
				//$out.= '<a href="'.$file.'?sortfield='.$field.'&sortorder=asc&begin='.$begin.$options.'">'.img_down("A-Z",1).'</a>';
				//$out.= '<a href="'.$file.'?sortfield='.$field.'&sortorder=desc&begin='.$begin.$options.'">'.img_up("Z-A",0).'</a>';
				$sortimg .= '<span class="nowrap">'.img_down("A-Z", 0, 'paddingright').'</span>';
			}
		}
	}

	$tagend = '</'.$tag.'>';

	$out = $tagstart.$sortimg.$out.$tagend;

	return $out;
}

}


require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';

/**
 * Class BranchLine. You can also remove this and generate a CRUD class for lines objects.
 */
class BranchLine extends CommonObjectLine
{
	// To complete with content of an object BranchLine
	// We should have a field rowid, fk_branch and position

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}
}
