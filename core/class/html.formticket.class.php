<?php
/* Copyright (C) 2013-2015 Jean-François FERRY     <hello@librethic.io>
 * Copyright (C) 2016      Christophe Battarel     <christophe@altairis.fr>
 * Copyright (C) 2019-2022 Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2021      Juanjo Menent           <jmenent@2byte.es>
 * Copyright (C) 2021      Alexandre Spangaro      <aspangaro@open-dsi.fr>
 * Copyright (C) 2023      Charlene Benke	       <charlene.r@patas-monkey.com>
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
 *    \file       htdocs/core/class/html.formticket.class.php
 *    \ingroup    ticket
 *    \brief      File of class to generate the form for creating a new ticket.
 */

require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/stores/class/branch.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';

if (!class_exists('FormCompany')) {
	include DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
}

/**
 * Class to generate the form for creating a new ticket.
 * Usage: 	$formticket = new FormTicket($db)
 * 			$formticket->proprietes = 1 or string or array of values
 * 			$formticket->show_form()  shows the form
 *
 * @package Ticket
 */
class FormTicket
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string		A hash value of the ticket. Duplicate of ref but for public purposes.
	 */
	public $track_id;

	/**
	 * @var string 		Email $trackid. Used also for the $keytoavoidconflict to name session vars to upload files.
	 */
	public $trackid;

	/**
	 * @var int ID
	 */
	public $fk_user_create;

	public $message;
	public $topic_title;

	public $action;

	public $withtopic;
	public $withemail;

	/**
	 * @var int $withsubstit Show substitution array
	 */
	public $withsubstit;

	public $withfile;
	public $withfilereadonly;

	public $backtopage;

	public $ispublic;  // to show information or not into public form

	public $withtitletopic;
	public $withtopicreadonly;
	public $withreadid;

	public $withcompany;  // to show company drop-down list
	public $withfromsocid;
	public $withfromcontactid;
	public $withnotifytiersatcreate;
	public $withusercreate;  // to show name of creating user in form
	public $withcreatereadonly;

	/**
	 * @var int withextrafields
	 */
	public $withextrafields;

	public $withref;  // to show ref field
	public $withcancel;

	public $type_code;
	public $category_code;
	public $severity_code;


	/**
	 *
	 * @var array $substit Substitutions
	 */
	public $substit = array();
	public $param = array();

	/**
	 * @var string Error code (or message)
	 */
	public $error;
	public $errors = array();


	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $conf;

		$this->db = $db;

		$this->action = 'add';

		$this->withcompany = isModEnabled("societe");
		$this->withfromsocid = 0;
		$this->withfromcontactid = 0;
		$this->withreadid=0;
		//$this->withtitletopic='';
		$this->withnotifytiersatcreate = 0;
		$this->withusercreate = 1;
		$this->withcreatereadonly = 1;
		$this->withemail = 0;
		$this->withref = 0;
		$this->withextrafields = 0;  // to show extrafields or not
		//$this->withtopicreadonly=0;
	}

	// /**
	//  * Show the form to input ticket
	//  *
	//  * @param  	int	 			$withdolfichehead		With dol_get_fiche_head() and dol_get_fiche_end()
	//  * @param	string			$mode					Mode ('create' or 'edit')
	//  * @param	int				$public					1=If we show the form for the public interface
	//  * @param	Contact|null	$with_contact			[=NULL] Contact to link to this ticket if it exists
	//  * @param	string			$action					[=''] Action in card
	//  * @return 	void
	//  */
	// public function showForm($withdolfichehead = 0, $mode = 'edit', $public = 0, Contact $with_contact = null, $action = '')
	// {
	// 	global $conf, $langs, $user, $hookmanager;

	// 	// Load translation files required by the page
	// 	$langs->loadLangs(array('other', 'mails', 'ticket'));

	// 	$form = new Form($this->db);
	// 	$formcompany = new FormCompany($this->db);
	// 	$ticketstatic = new Ticket($this->db);

	// 	$soc = new Societe($this->db);
	// 	if (!empty($this->withfromsocid) && $this->withfromsocid > 0) {
	// 		$soc->fetch($this->withfromsocid);
	// 	}

	// 	$ticketstat = new Ticket($this->db);

	// 	$extrafields = new ExtraFields($this->db);
	// 	$extrafields->fetch_name_optionals_label($ticketstat->table_element);

	// 	print "\n<!-- Begin form TICKET -->\n";

	// 	if ($withdolfichehead) {
	// 		print dol_get_fiche_head(null, 'card', '', 0, '');
	// 	}

	// 	print '<form method="POST" '.($withdolfichehead ? '' : 'style="margin-bottom: 30px;" ').'name="ticket" id="form_create_ticket" enctype="multipart/form-data" action="'.(!empty($this->param["returnurl"]) ? $this->param["returnurl"] : $_SERVER['PHP_SELF']).'">';
	// 	print '<input type="hidden" name="token" value="'.newToken().'">';
	// 	print '<input type="hidden" name="action" value="'.$this->action.'">';
	// 	print '<input type="hidden" name="trackid" value="'.$this->trackid.'">';
	// 	foreach ($this->param as $key => $value) {
	// 		print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
	// 	}
	// 	print '<input type="hidden" name="fk_user_create" value="'.$this->fk_user_create.'">';

	// 	print '<table class="border centpercent">';

	// 	if ($this->withref) {
	// 		// Ref
	// 		$defaultref = $ticketstat->getDefaultRef();
	// 		print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans("Ref").'</span></td><td>';
	// 		print '<input type="text" name="ref" value="'.dol_escape_htmltag(GETPOST("ref", 'alpha') ? GETPOST("ref", 'alpha') : $defaultref).'">';
	// 		print '</td></tr>';
	// 	}

	// 	// TITLE
	// 	$email = GETPOSTISSET('email') ? GETPOST('email', 'alphanohtml') : '';
	// 	if ($this->withemail) {
	// 		print '<tr><td class="titlefield"><label for="email"><span class="fieldrequired">'.$langs->trans("Email").'</span></label></td><td>';
	// 		print '<input class="text minwidth200" id="email" name="email" value="'.$email.'" autofocus>';
	// 		print '</td></tr>';

	// 		if ($with_contact) {
	// 			// contact search and result
	// 			$html_contact_search  = '';
	// 			$html_contact_search .= '<tr id="contact_search_line">';
	// 			$html_contact_search .= '<td class="titlefield">';
	// 			$html_contact_search .= '<label for="contact"><span class="fieldrequired">' . $langs->trans('Contact') . '</span></label>';
	// 			$html_contact_search .= '<input type="hidden" id="contact_id" name="contact_id" value="" />';
	// 			$html_contact_search .= '</td>';
	// 			$html_contact_search .= '<td id="contact_search_result"></td>';
	// 			$html_contact_search .= '</tr>';
	// 			print $html_contact_search;
	// 			// contact lastname
	// 			$html_contact_lastname = '';
	// 			$html_contact_lastname .= '<tr id="contact_lastname_line" class="contact_field"><td class="titlefield"><label for="contact_lastname"><span class="fieldrequired">' . $langs->trans('Lastname') . '</span></label></td><td>';
	// 			$html_contact_lastname .= '<input type="text" id="contact_lastname" name="contact_lastname" value="' . dol_escape_htmltag(GETPOSTISSET('contact_lastname') ? GETPOST('contact_lastname', 'alphanohtml') : '') . '" />';
	// 			$html_contact_lastname .= '</td></tr>';
	// 			print $html_contact_lastname;
	// 			// contact firstname
	// 			$html_contact_firstname  = '';
	// 			$html_contact_firstname .= '<tr id="contact_firstname_line" class="contact_field"><td class="titlefield"><label for="contact_firstname"><span class="fieldrequired">' . $langs->trans('Firstname') . '</span></label></td><td>';
	// 			$html_contact_firstname .= '<input type="text" id="contact_firstname" name="contact_firstname" value="' . dol_escape_htmltag(GETPOSTISSET('contact_firstname') ? GETPOST('contact_firstname', 'alphanohtml') : '') . '" />';
	// 			$html_contact_firstname .= '</td></tr>';
	// 			print $html_contact_firstname;
	// 			// company name
	// 			$html_company_name  = '';
	// 			$html_company_name .= '<tr id="contact_company_name_line" class="contact_field"><td><label for="company_name"><span>' . $langs->trans('Company') . '</span></label></td><td>';
	// 			$html_company_name .= '<input type="text" id="company_name" name="company_name" value="' . dol_escape_htmltag(GETPOSTISSET('company_name') ? GETPOST('company_name', 'alphanohtml') : '') . '" />';
	// 			$html_company_name .= '</td></tr>';
	// 			print $html_company_name;
	// 			// contact phone
	// 			$html_contact_phone  = '';
	// 			$html_contact_phone .= '<tr id="contact_phone_line" class="contact_field"><td><label for="contact_phone"><span>' . $langs->trans('Phone') . '</span></label></td><td>';
	// 			$html_contact_phone .= '<input type="text" id="contact_phone" name="contact_phone" value="' . dol_escape_htmltag(GETPOSTISSET('contact_phone') ? GETPOST('contact_phone', 'alphanohtml') : '') . '" />';
	// 			$html_contact_phone .= '</td></tr>';
	// 			print $html_contact_phone;

	// 			// search contact form email
	// 			$langs->load('errors');
	// 			print '<script nonce="'.getNonce().'" type="text/javascript">
    //                 jQuery(document).ready(function() {
    //                     var contact = jQuery.parseJSON("'.dol_escape_js(json_encode($with_contact), 2).'");
    //                     jQuery("#contact_search_line").hide();
    //                     if (contact) {
    //                     	if (contact.id > 0) {
    //                     		jQuery("#contact_search_line").show();
    //                     		jQuery("#contact_id").val(contact.id);
	// 							jQuery("#contact_search_result").html(contact.firstname+" "+contact.lastname);
	// 							jQuery(".contact_field").hide();
    //                     	} else {
    //                     		jQuery(".contact_field").show();
    //                     	}
    //                     }

    //                 	jQuery("#email").change(function() {
    //                         jQuery("#contact_search_line").show();
    //                         jQuery("#contact_search_result").html("'.dol_escape_js($langs->trans('Select2SearchInProgress')).'");
    //                         jQuery("#contact_id").val("");
    //                         jQuery("#contact_lastname").val("");
    //                         jQuery("#contact_firstname").val("");
    //                         jQuery("#company_name").val("");
    //                         jQuery("#contact_phone").val("");

    //                         jQuery.getJSON(
    //                             "'.dol_escape_js(dol_buildpath('/public/ticket/ajax/ajax.php', 1)).'",
	// 							{
	// 								action: "getContacts",
	// 								email: jQuery("#email").val()
	// 							},
	// 							function(response) {
	// 								if (response.error) {
    //                                     jQuery("#contact_search_result").html("<span class=\"error\">"+response.error+"</span>");
	// 								} else {
    //                                     var contact_list = response.contacts;
	// 									if (contact_list.length == 1) {
    //                                         var contact = contact_list[0];
	// 										jQuery("#contact_id").val(contact.id);
	// 										jQuery("#contact_search_result").html(contact.firstname+" "+contact.lastname);
    //                                         jQuery(".contact_field").hide();
	// 									} else if (contact_list.length <= 0) {
    //                                         jQuery("#contact_search_line").hide();
    //                                         jQuery(".contact_field").show();
	// 									}
	// 								}
	// 							}
    //                         ).fail(function(jqxhr, textStatus, error) {
    // 							var error_msg = "'.dol_escape_js($langs->trans('ErrorAjaxRequestFailed')).'"+" ["+textStatus+"] : "+error;
    //                             jQuery("#contact_search_result").html("<span class=\"error\">"+error_msg+"</span>");
    //                         });
    //                     });
    //                 });
    //                 </script>';
	// 		}
	// 	}

	// 	// If ticket created from another object
	// 	$subelement = '';
	// 	if (isset($this->param['origin']) && $this->param['originid'] > 0) {
	// 		// Parse element/subelement (ex: project_task)
	// 		$element = $subelement = $this->param['origin'];
	// 		$regs = array();
	// 		if (preg_match('/^([^_]+)_([^_]+)/i', $this->param['origin'], $regs)) {
	// 			$element = $regs[1];
	// 			$subelement = $regs[2];
	// 		}

	// 		dol_include_once('/'.$element.'/class/'.$subelement.'.class.php');
	// 		$classname = ucfirst($subelement);
	// 		$objectsrc = new $classname($this->db);
	// 		$objectsrc->fetch(GETPOST('originid', 'int'));

	// 		if (empty($objectsrc->lines) && method_exists($objectsrc, 'fetch_lines')) {
	// 			$objectsrc->fetch_lines();
	// 		}

	// 		$objectsrc->fetch_thirdparty();
	// 		$newclassname = $classname;
	// 		print '<tr><td>'.$langs->trans($newclassname).'</td><td colspan="2"><input name="'.$subelement.'id" value="'.GETPOST('originid').'" type="hidden" />'.$objectsrc->getNomUrl(1).'</td></tr>';
	// 	}

	// 	// Type of Ticket
	// 	print '<tr><td class="titlefield"><span class="fieldrequired"><label for="selecttype_code">'.$langs->trans("TicketTypeRequest").'</span></label></td><td>';
	// 	$this->selectTypesTickets((GETPOST('type_code', 'alpha') ? GETPOST('type_code', 'alpha') : $this->type_code), 'type_code', '', 2, 1, 0, 0, 'minwidth200');
	// 	print '</td></tr>';

	// 	// Group => Category
	// 	print '<tr><td><span class="fieldrequired"><label for="selectcategory_code">'.$langs->trans("TicketCategory").'</span></label></td><td>';
	// 	$filter = '';
	// 	if ($public) {
	// 		$filter = 'public=1';
	// 	}
	// 	$selected = (GETPOST('category_code') ? GETPOST('category_code') : $this->category_code);
	// 	$this->selectGroupTickets($selected, 'category_code', $filter, 2, 1, 0, 0, 'minwidth200');
	// 	print '</td></tr>';

	// 	// Severity => Priority
	// 	print '<tr><td><span class="fieldrequired"><label for="selectseverity_code">'.$langs->trans("TicketSeverity").'</span></label></td><td>';
	// 	$this->selectSeveritiesTickets((GETPOST('severity_code') ? GETPOST('severity_code') : $this->severity_code), 'severity_code', '', 2, 1);
	// 	print '</td></tr>';

	// 	if (!empty($conf->knowledgemanagement->enabled)) {
	// 		// KM Articles
	// 		print '<tr id="KWwithajax" class="hidden"><td></td></tr>';
	// 		print '<!-- Script to manage change of ticket group -->
	// 		<script nonce="'.getNonce().'">
	// 		jQuery(document).ready(function() {
	// 			function groupticketchange() {
	// 				console.log("We called groupticketchange, so we try to load list KM linked to event");
	// 				$("#KWwithajax").html("");
	// 				idgroupticket = $("#selectcategory_code").val();

	// 				console.log("We have selected id="+idgroupticket);

	// 				if (idgroupticket != "") {
	// 					$.ajax({ url: \''.DOL_URL_ROOT.'/core/ajax/fetchKnowledgeRecord.php\',
	// 						 data: { action: \'getKnowledgeRecord\', idticketgroup: idgroupticket, token: \''.newToken().'\', lang:\''.$langs->defaultlang.'\', public:'.($public).' },
	// 						 type: \'GET\',
	// 						 success: function(response) {
	// 							var urllist = \'\';
	// 							console.log("We received response "+response);
	// 							if (typeof response == "object") {
	// 								console.log("response is already type object, no need to parse it");
	// 							} else {
	// 								console.log("response is type "+(typeof response));
	// 								response = JSON.parse(response);
	// 							}
	// 							for (key in response) {
	// 								answer = response[key].answer;
	// 								urllist += \'<li><a href="#" title="\'+response[key].title+\'" class="button_KMpopup" data-html="\'+answer+\'">\' +response[key].title+\'</a></li>\';
	// 							}
	// 							if (urllist != "") {
	// 								$("#KWwithajax").html(\'<td>'.$langs->trans("KMFoundForTicketGroup").'</td><td><ul>\'+urllist+\'</ul></td>\');
	// 								$("#KWwithajax").show();
	// 								$(".button_KMpopup").on("click",function(){
	// 									console.log("Open popup with jQuery(...).dialog() with KM article")
	// 									var $dialog = $("<div></div>").html($(this).attr("data-html"))
	// 										.dialog({
	// 											autoOpen: false,
	// 											modal: true,
	// 											height: (window.innerHeight - 150),
	// 											width: "80%",
	// 											title: $(this).attr("title"),
	// 										});
	// 									$dialog.dialog("open");
	// 									console.log($dialog);
	// 								})
	// 							}
	// 						 },
	// 						 error : function(output) {
	// 							console.error("Error on Fetch of KM articles");
	// 						 },
	// 					});
	// 				}
	// 			};
	// 			$("#selectcategory_code").on("change",function() { groupticketchange(); });
	// 			if ($("#selectcategory_code").val() != "") {
	// 				groupticketchange();
	// 			}
	// 		});
	// 		</script>'."\n";
	// 	}

	// 	// Subject
	// 	if ($this->withtitletopic) {
	// 		print '<tr><td><label for="subject"><span class="fieldrequired">'.$langs->trans("Subject").'</span></label></td><td>';
	// 		// Answer to a ticket : display of the thread title in readonly
	// 		if ($this->withtopicreadonly) {
	// 			print $langs->trans('SubjectAnswerToTicket').' '.$this->topic_title;
	// 		} else {
	// 			if (isset($this->withreadid) && $this->withreadid > 0) {
	// 				$subject = $langs->trans('SubjectAnswerToTicket').' '.$this->withreadid.' : '.$this->topic_title;
	// 			} else {
	// 				$subject = GETPOST('subject', 'alpha');
	// 			}
	// 			print '<input class="text minwidth500" id="subject" name="subject" value="'.$subject.'"'.(empty($this->withemail) ? ' autofocus' : '').' />';
	// 		}
	// 		print '</td></tr>';
	// 	}

	// 	// MESSAGE
	// 	$msg = GETPOSTISSET('message') ? GETPOST('message', 'restricthtml') : '';
	// 	print '<tr><td><label for="message"><span class="fieldrequired">'.$langs->trans("Message").'</span></label></td><td>';

	// 	// If public form, display more information
	// 	$toolbarname = 'dolibarr_notes';
	// 	if ($this->ispublic) {
	// 		$toolbarname = 'dolibarr_details';
	// 		print '<div class="warning hideonsmartphone">'.(getDolGlobalString("TICKET_PUBLIC_TEXT_HELP_MESSAGE", $langs->trans('TicketPublicPleaseBeAccuratelyDescribe'))).'</div>';
	// 	}
	// 	include_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
	// 	$uselocalbrowser = true;
	// 	$doleditor = new DolEditor('message', $msg, '100%', 230, $toolbarname, 'In', true, $uselocalbrowser, getDolGlobalInt('FCKEDITOR_ENABLE_TICKET'), ROWS_8, '90%');
	// 	$doleditor->Create();
	// 	print '</td></tr>';

	// 	if ($public && getDolGlobalString('MAIN_SECURITY_ENABLECAPTCHA_TICKET')) {
	// 		require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
	// 		print '<tr><td class="titlefield"><label for="email"><span class="fieldrequired">'.$langs->trans("SecurityCode").'</span></label></td><td>';
	// 		print '<span class="span-icon-security inline-block">';
	// 		print '<input id="securitycode" placeholder="'.$langs->trans("SecurityCode").'" class="flat input-icon-security width125" type="text" maxlength="5" name="code" tabindex="3" />';
	// 		print '</span>';
	// 		print '<span class="nowrap inline-block">';
	// 		print '<img class="inline-block valignmiddle" src="'.DOL_URL_ROOT.'/core/antispamimage.php" border="0" width="80" height="32" id="img_securitycode" />';
	// 		print '<a class="inline-block valignmiddle" href="" tabindex="4" data-role="button">'.img_picto($langs->trans("Refresh"), 'refresh', 'id="captcha_refresh_img"').'</a>';
	// 		print '</span>';
	// 		print '</td></tr>';
	// 	}

	// 	// Categories
	// 	if (isModEnabled('categorie')) {
	// 		include_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
	// 		$cate_arbo = $form->select_all_categories(Categorie::TYPE_TICKET, '', 'parent', 64, 0, 1);

	// 		if (count($cate_arbo)) {
	// 			// Categories
	// 			print '<tr><td class="wordbreak">'.$langs->trans("Categories").'</td><td>';
	// 			print img_picto('', 'category', 'class="pictofixedwidth"').$form->multiselectarray('categories', $cate_arbo, GETPOST('categories', 'array'), '', 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);
	// 			print "</td></tr>";
	// 		}
	// 	}

	// 	// Attached files
	// 	if (!empty($this->withfile)) {
	// 		// Define list of attached files
	// 		$listofpaths = array();
	// 		$listofnames = array();
	// 		$listofmimes = array();
	// 		if (!empty($_SESSION["listofpaths"])) {
	// 			$listofpaths = explode(';', $_SESSION["listofpaths"]);
	// 		}

	// 		if (!empty($_SESSION["listofnames"])) {
	// 			$listofnames = explode(';', $_SESSION["listofnames"]);
	// 		}

	// 		if (!empty($_SESSION["listofmimes"])) {
	// 			$listofmimes = explode(';', $_SESSION["listofmimes"]);
	// 		}

	// 		$out = '<tr>';
	// 		$out .= '<td>'.$langs->trans("MailFile").'</td>';
	// 		$out .= '<td>';
	// 		// TODO Trick to have param removedfile containing nb of image to delete. But this does not works without javascript
	// 		$out .= '<input type="hidden" class="removedfilehidden" name="removedfile" value="">'."\n";
	// 		$out .= '<script nonce="'.getNonce().'" type="text/javascript">';
	// 		$out .= 'jQuery(document).ready(function () {';
	// 		$out .= '    jQuery(".removedfile").click(function() {';
	// 		$out .= '        jQuery(".removedfilehidden").val(jQuery(this).val());';
	// 		$out .= '    });';
	// 		$out .= '})';
	// 		$out .= '</script>'."\n";
	// 		if (count($listofpaths)) {
	// 			foreach ($listofpaths as $key => $val) {
	// 				$out .= '<div id="attachfile_'.$key.'">';
	// 				$out .= img_mime($listofnames[$key]).' '.$listofnames[$key];
	// 				if (!$this->withfilereadonly) {
	// 					$out .= ' <input type="image" style="border: 0px;" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/delete.png" value="'.($key + 1).'" class="removedfile" id="removedfile_'.$key.'" name="removedfile_'.$key.'" />';
	// 				}
	// 				$out .= '<br></div>';
	// 			}
	// 		} else {
	// 			$out .= $langs->trans("NoAttachedFiles").'<br>';
	// 		}
	// 		if ($this->withfile == 2) { // Can add other files
	// 			$maxfilesizearray = getMaxFileSizeArray();
	// 			$maxmin = $maxfilesizearray['maxmin'];
	// 			if ($maxmin > 0) {
	// 				$out .= '<input type="hidden" name="MAX_FILE_SIZE" value="'.($maxmin * 1024).'">';	// MAX_FILE_SIZE must precede the field type=file
	// 			}
	// 			$out .= '<input type="file" class="flat" id="addedfile" name="addedfile" value="'.$langs->trans("Upload").'" />';
	// 			$out .= ' ';
	// 			$out .= '<input type="submit" class="button smallpaddingimp reposition" id="addfile" name="addfile" value="'.$langs->trans("MailingAddFile").'" />';
	// 		}
	// 		$out .= "</td></tr>\n";

	// 		print $out;
	// 	}

	// 	// User of creation
	// 	if ($this->withusercreate > 0 && $this->fk_user_create) {
	// 		print '<tr><td class="titlefield">'.$langs->trans("CreatedBy").'</td><td>';
	// 		$langs->load("users");
	// 		$fuser = new User($this->db);

	// 		if ($this->withcreatereadonly) {
	// 			if ($res = $fuser->fetch($this->fk_user_create)) {
	// 				print $fuser->getNomUrl(1);
	// 			}
	// 		}
	// 		print ' &nbsp; ';
	// 		print "</td></tr>\n";
	// 	}

	// 	// Customer or supplier
	// 	if ($this->withcompany) {
	// 		// altairis: force company and contact id for external user
	// 		if (empty($user->socid)) {
	// 			// Company
	// 			print '<tr><td class="titlefield">'.$langs->trans("ThirdParty").'</td><td>';
	// 			$events = array();
	// 			$events[] = array('method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php', 1), 'htmlname' => 'contactid', 'params' => array('add-customer-contact' => 'disabled'));
	// 			print img_picto('', 'company', 'class="paddingright"');
	// 			print $form->select_company($this->withfromsocid, 'socid', '', 1, 1, '', $events, 0, 'minwidth200');
	// 			print '</td></tr>';
	// 			if (!empty($conf->use_javascript_ajax) && getDolGlobalString('COMPANY_USE_SEARCH_TO_SELECT')) {
	// 				$htmlname = 'socid';
	// 				print '<script nonce="'.getNonce().'" type="text/javascript">
    //                 $(document).ready(function () {
    //                     jQuery("#'.$htmlname.'").change(function () {
    //                         var obj = '.json_encode($events).';
    //                         $.each(obj, function(key,values) {
    //                             if (values.method.length) {
    //                                 runJsCodeForEvent'.$htmlname.'(values);
    //                             }
    //                         });
    //                     });

    //                     function runJsCodeForEvent'.$htmlname.'(obj) {
    //                         console.log("Run runJsCodeForEvent'.$htmlname.'");
    //                         var id = $("#'.$htmlname.'").val();
    //                         var method = obj.method;
    //                         var url = obj.url;
    //                         var htmlname = obj.htmlname;
    //                         var showempty = obj.showempty;
    //                         $.getJSON(url,
    //                                 {
    //                                     action: method,
    //                                     id: id,
    //                                     htmlname: htmlname,
    //                                     showempty: showempty
    //                                 },
    //                                 function(response) {
    //                                     $.each(obj.params, function(key,action) {
    //                                         if (key.length) {
    //                                             var num = response.num;
    //                                             if (num > 0) {
    //                                                 $("#" + key).removeAttr(action);
    //                                             } else {
    //                                                 $("#" + key).attr(action, action);
    //                                             }
    //                                         }
    //                                     });
    //                                     $("select#" + htmlname).html(response.value);
    //                                     if (response.num) {
    //                                         var selecthtml_str = response.value;
    //                                         var selecthtml_dom=$.parseHTML(selecthtml_str);
	// 										if (typeof(selecthtml_dom[0][0]) !== \'undefined\') {
    //                                         	$("#inputautocomplete"+htmlname).val(selecthtml_dom[0][0].innerHTML);
	// 										}
    //                                     } else {
    //                                         $("#inputautocomplete"+htmlname).val("");
    //                                     }
    //                                     $("select#" + htmlname).change();	/* Trigger event change */
    //                                 }
    //                         );
    //                     }
    //                 });
    //                 </script>';
	// 			}

	// 			// Contact and type
	// 			print '<tr><td>'.$langs->trans("Contact").'</td><td>';
	// 			// If no socid, set to -1 to avoid full contacts list
	// 			$selectedCompany = ($this->withfromsocid > 0) ? $this->withfromsocid : -1;
	// 			print img_picto('', 'contact', 'class="paddingright"');
	// 			print $form->selectcontacts($selectedCompany, $this->withfromcontactid, 'contactid', 3, '', '', 0, 'minwidth200');
	// 			print ' ';
	// 			$formcompany->selectTypeContact($ticketstatic, '', 'type', 'external', '', 0, 'maginleftonly');
	// 			print '</td></tr>';
	// 		} else {
	// 			print '<tr><td class="titlefield"><input type="hidden" name="socid" value="'.$user->socid.'"/></td>';
	// 			print '<td><input type="hidden" name="contactid" value="'.$user->contact_id.'"/></td>';
	// 			print '<td><input type="hidden" name="type" value="Z"/></td></tr>';
	// 		}

	// 		// Notify thirdparty at creation
	// 		if (empty($this->ispublic)) {
	// 			print '<tr><td><label for="notify_tiers_at_create">'.$langs->trans("TicketNotifyTiersAtCreation").'</label></td><td>';
	// 			print '<input type="checkbox" id="notify_tiers_at_create" name="notify_tiers_at_create"'.($this->withnotifytiersatcreate ? ' checked="checked"' : '').'>';
	// 			print '</td></tr>';
	// 		}

	// 		// User assigned
	// 		print '<tr><td>';
	// 		print $langs->trans("AssignedTo");
	// 		print '</td><td>';
	// 		print img_picto('', 'user', 'class="pictofixedwidth"');
	// 		print $form->select_dolusers(GETPOST('fk_user_assign', 'int'), 'fk_user_assign', 1);
	// 		print '</td>';
	// 		print '</tr>';
	// 	}

	// 	if ($subelement != 'project') {
	// 		if (isModEnabled('project') && !$this->ispublic) {
	// 			$formproject = new FormProjets($this->db);
	// 			print '<tr><td><label for="project"><span class="">'.$langs->trans("Project").'</span></label></td><td>';
	// 			print img_picto('', 'project').$formproject->select_projects(-1, GETPOST('projectid', 'int'), 'projectid', 0, 0, 1, 1, 0, 0, 0, '', 1, 0, 'maxwidth500');
	// 			print '</td></tr>';
	// 		}
	// 	}

	// 	if ($subelement != 'contract') {
	// 		if (isModEnabled('contract') && !$this->ispublic) {
	// 			$langs->load('contracts');
	// 			$formcontract = new FormContract($this->db);
	// 			print '<tr><td><label for="contract"><span class="">'.$langs->trans("Contract").'</span></label></td><td>';
	// 			print img_picto('', 'contract');
	// 			print $formcontract->select_contract(-1, GETPOST('contactid', 'int'), 'contractid', 0, 1, 1, 1);
	// 			print '</td></tr>';
	// 		}
	// 	}

	// 	// Other attributes
	// 	$parameters = array();
	// 	$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $ticketstat, $action); // Note that $action and $object may have been modified by hook
	// 	if (empty($reshook)) {
	// 		print $ticketstat->showOptionals($extrafields, 'create');
	// 	}

	// 	print '</table>';

	// 	if ($withdolfichehead) {
	// 		print dol_get_fiche_end();
	// 	}

	// 	print '<br><br>';

	// 	print $form->buttonsSaveCancel(((isset($this->withreadid) && $this->withreadid > 0) ? "SendResponse" : "CreateTicket"), ($this->withcancel ? "Cancel" : ""));

	// 	/*
	// 	print '<div class="center">';
	// 	print '<input type="submit" class="button" name="add" value="'.$langs->trans(($this->withreadid > 0 ? "SendResponse" : "CreateTicket")).'" />';
	// 	if ($this->withcancel) {
	// 		print " &nbsp; &nbsp; &nbsp;";
	// 		print '<input class="button button-cancel" type="submit" name="cancel" value="'.$langs->trans("Cancel").'">';
	// 	}
	// 	print '</div>';
	// 	*/

	// 	print '<input type="hidden" name="page_y">'."\n";

	// 	print "</form>\n";
	// 	print "<!-- End form TICKET -->\n";
	// }

	/**
	 * Show the form to input ticket
	 *
	 * @param  	int	 			$withdolfichehead		With dol_get_fiche_head() and dol_get_fiche_end()
	 * @param	string			$mode					Mode ('create' or 'edit')
	 * @param	int				$public					1=If we show the form for the public interface
	 * @param	Contact|null	$with_contact			[=NULL] Contact to link to this ticket if it exists
	 * @param	string			$action					[=''] Action in card
	 * @return 	void
	 */
	public function showForm($withdolfichehead = 0, $mode = 'edit', $public = 0, Contact $with_contact = null, $action = '')
	{
		global $conf, $langs, $user, $hookmanager;
		$mainid = GETPOST("mainid");
		$projectid = GETPOST("projectid");
		$socid = GETPOST("socid");
		$customerid = GETPOST("customerid");
		$storeid = GETPOST("storeid");
		$parentid = GETPOST("parentid");
		if($parentid){
			$mainid = $parentid;
		}
		// Load translation files required by the page
		$langs->loadLangs(array('other', 'mails', 'ticket'));

		$form = new Form($this->db);
		$formcompany = new FormCompany($this->db);
		$ticketstatic = new Ticket($this->db);
		$store = new Branch($this->db);
		$proj = new Project($this->db);
		if($mainid){
			$mainticket = new Ticket($this->db);
			$mainticket->fetch($mainid);
			$mainticketref = $mainticket->ref;
			$sql = 'SELECT * FROM llx_ticket_extrafields WHERE parentticket = '.$mainid;
			$total = $this->db->query($sql)->num_rows;
			$parts = explode("-", $mainticketref);
			$parts[0] = "27";
			$parts[] = $total + 1;
			$newRef = implode("-", $parts);
		}
		
		

		$soc = new Societe($this->db);
		if (!empty($this->withfromsocid) && $this->withfromsocid > 0) {
			$soc->fetch($this->withfromsocid);
		}
		$ticketstat = new Ticket($this->db);

		$extrafields = new ExtraFields($this->db);
		$extrafields->fetch_name_optionals_label($ticketstat->table_element);

		print "\n<!-- Begin form TICKET -->\n";

		if ($withdolfichehead) {
			print dol_get_fiche_head(null, 'card', '', 0, '');
		}

		print '<form method="POST" '.($withdolfichehead ? '' : 'style="margin-bottom: 30px;" ').'name="ticket" id="form_create_ticket" enctype="multipart/form-data" action="'.(!empty($this->param["returnurl"]) ? $this->param["returnurl"] : $_SERVER['PHP_SELF']).'?action=create">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="'.$this->action.'">';
		print '<input type="hidden" name="trackid" value="'.$this->trackid.'">';
		foreach ($this->param as $key => $value) {
			print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
		}
		print '<input type="hidden" name="fk_user_create" value="'.$this->fk_user_create.'">';

		print '<table class="border centpercent">';
		print '<script type="text/javascript">
					$(document).ready(function () {

						$("form").submit(function() {
							var formData = $(this).serializeArray();
							formData.forEach(function(field) {
								localStorage.setItem(field.name, field.value);
							});
						});
						var currentURL = window.location.href;
						var parts = currentURL.split("?");
						if (parts[1] == null) {
							$("form input, form select, form textarea, form span").each(function() {
								var storedValue = localStorage.getItem($(this).attr("name"));
								if (storedValue) {
									$(this).val(storedValue);
								}
							});
						}else{
							localStorage.clear();
						}

						const cookies = document.cookie.split(";");

						for (let i = 0; i < cookies.length; i++) {
							const cookie = cookies[i];
							if (cookie.startsWith("project=")) {
								var cookieValue = cookie.substring("project".length + 1);
								
								if (cookieValue !== "") {
									console.log(1);
									$(".button-save").removeAttr("disabled");
								}
							}
							const eqPos = cookie.indexOf("=");
							const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
							document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
						}

						var submitButton = document.getElementById("addfile");

						submitButton.addEventListener("click", function(event) {
							document.cookie = "customer=" + $("#options_customer").val();
							document.cookie = "ordervia=" + $("#options_ordervia").val();
							document.cookie = "store=" + $("#options_fk_store").val();
							document.cookie = "project=" + $("#projectid").val();
							document.cookie = "dateofuse=" + $("#options_dateofuse").val();
							document.cookie = "timehour=" + $("#options_timehour").val();
							document.cookie = "timeminute=" + $("#options_timeminute").val();
							document.cookie = "datemin=" + $("#options_datemin").val();
							document.cookie = "datehour=" + $("#options_datehour").val();
							document.cookie = "externalref=" + $("#options_externalticketnumber").val();
							document.cookie = "parentticket=" + $("#options_parentticket").val();
						});
					});';
		print '</script>';		

		if ($this->withref) {
			// Ref
			$defaultref = $ticketstat->getDefaultRef();
			if($mainid){
				$defaultref = $newRef;
			}
			print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans("Ref").'</span></td><td>';
			print '<input type="text" name="ref" value="'.dol_escape_htmltag(GETPOST("ref", 'alpha') ? GETPOST("ref", 'alpha') : $defaultref).'">';
			print '</td></tr>';
		}
		
		// Parent Ticket
		if($mainid){
			$parentticket = $mainid;
			if(isset($_COOKIE["parentticket"])){
				$parentticket = $_COOKIE["parentticket"];
			}
			print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans("Parent Ticket").'</span></td><td>';
			print $form->selectTickets($parentticket,'options_parentticket');
			print '</td></tr>';
		}

		$externalref = "";
		if(isset($_COOKIE["externalref"])){
			$externalref = $_COOKIE["externalref"];
		}
		//External Ref
		print '<tr class="fieldrequired field_options_externalticketnumber ticket_extras_externalticketnumber trextrafields_collapse" data-element="extrafield" data-targetelement="ticket" data-targetid="">
					<td class="titlefieldcreate wordbreak">'.$langs->trans("External Ticket Number").'</td>
					<td class="valuefieldcreate ticket_extras_externalticketnumber">
						<input type="text" class="flat minwidth400 maxwidthonsmartphone" name="options_externalticketnumber" id="options_externalticketnumber" maxlength="255" value="'.$externalref.'">
					</td>
				</tr>';

		// Business Partner
		if ($this->withcompany) {
			$pro = GETPOST('originid', 'int');
			$selectedThird = $this->withfromsocid;
			if($pro){
				$proj->fetch($pro);
				$selectedThird = $proj->socid;
			}
			// altairis: force company and contact id for external user
			if (empty($user->socid)) {
				// Company
				print '<tr><td class="titlefield">'.$langs->trans("ThirdParty").'</td><td>';
				$events = array();
				$events[] = array('method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php', 1), 'htmlname' => 'contactid', 'params' => array('add-customer-contact' => 'disabled'));
				$st = array('method' => 'getContacts',
								'url' => dol_buildpath('/custom/stores/ajax/stores.php', 1),
								'htmlname' => 'contactid_customer',
								'params' => array('add-customer-contact' => 'disabled')
							);
				$pr = array('method' => 'getContacts',
								'url' => dol_buildpath('/ticket/ajax/tickets.php', 1),
								'htmlname' => 'contactid_customer',
								'params' => array('add-customer-contact' => 'disabled')
							);
				print img_picto('', 'company', 'class="paddingright"');

				print $form->select_company($selectedThird, 'socid', '', 1, 1, '', $events, 0, 'minwidth200');
				print '</td></tr>';

				
				// Order Via
				print '<tr><td>'.$langs->trans("Order via").'</td><td>';
				// If no socid, set to -1 to avoid full contacts list
				$selectedCompany = ($this->withfromsocid > 0) ? $this->withfromsocid : -1;
				$selectedOrderVia = 0;
				if($mainid){
					$selectedOrderVia = "Storema";
				}
				if(isset($_COOKIE["ordervia"])){
					$selectedOrderVia = $_COOKIE["ordervia"];
				}
				$store->select_order_customer($selectedOrderVia);
				
				// Contact and type
				print '<tr><td>'.$langs->trans("Contact").'</td><td>';
				print img_picto('', 'contact', 'class="paddingright"');
		
				print $form->selectcontacts($selectedThird, $this->withfromcontactid, 'contactid', 3, '', '', 0, 'minwidth200');

				print ' ';
				$formcompany->selectTypeContact($ticketstatic, '', 'type_customer', 'external', '', 0, 'maginleftonly');
				print '</td></tr>';
			} else {
				print '<tr><td class="titlefield"><input type="hidden" name="socid" value="'.$user->socid.'"/></td>';
				// if (isModEnabled('stores')){
				print '<tr><td>'.$langs->trans("Order via").'</td><td>';
					// If no socid, set to -1 to avoid full contacts list
				$selectedOrderVia = 0;
				if(isset($_COOKIE["ordervia"])){
					$selectedOrderVia = $_COOKIE["ordervia"];
				}
				$store->select_order_customer($selectedOrderVia);
				
				// }
				print '<td><input type="hidden" name="contactid" value="'.$user->contact_id.'"/></td>';
				print '<td><input type="hidden" name="type_customer" value="Z"/></td></tr>';
			}


		}
		 
		// Customer
		if ($this->withcompany) {
			$pro = GETPOST('originid', 'int');
			if($pro){
				$proj->fetch($pro);
			}
			// altairis: force company and contact id for external user
			if (empty($user->socid)) {
				// Company
				print '<tr><td class="titlefield">'.$langs->trans("Customer").'</td><td>';
				$events = array();
				$events[] = array('method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php', 1), 'htmlname' => 'contactid_customer', 'params' => array('add-customer-contact' => 'disabled'));
				$st = array('method' => 'getContacts',
								'url' => dol_buildpath('/custom/stores/ajax/stores.php', 1),
								'htmlname' => 'contactid',
								'params' => array('add-customer-contact' => 'disabled')
							);
				$pr = array('method' => 'getContacts',
								'url' => dol_buildpath('/ticket/ajax/tickets.php', 1),
								'htmlname' => 'contactid',
								'params' => array('add-customer-contact' => 'disabled')
							);
				print img_picto('', 'company', 'class="paddingright"');
				
				$selectedCustomer = $customerid;
				if(isset($_COOKIE["customer"])){
					$selectedCustomer = $_COOKIE["customer"];
				}
				
				print $form->select_company($selectedCustomer, 'options_customer', '', 1, 1, '', $events, 0, 'minwidth200');
				
				print '</td></tr>';
				// if (!empty($conf->use_javascript_ajax) && !empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT)) {
					$htmlname = 'socid';
					$customer='options_customer';
					print '<script type="text/javascript">
                    $(document).ready(function () {

                        jQuery("#'.$customer.'").change(function () {
							runJs();
                            var obj = '.json_encode($events).';
                            $.each(obj, function(key,values) {
                                if (values.method.length) {
                                    runJsCodeForEvent'.$customer.'(values);
                                }
                            });
                        });
                        jQuery("#'.$htmlname.'").change(function () {
							runJsProject();
                        });

						function runJs(){
							var id = $("#'.$customer.'").val();
                            var obj = '.json_encode($st).';
                            $.getJSON(obj["url"],
                                    {
                                        id: id,
                                    },
                                    function(response) {
										console.log(response["data"]);
										$("#options_fk_store").empty();
										var stores = document.getElementById("options_fk_store");
										for(var i = 0; i < response["data"].length; i++){
											var option = document.createElement("option");
											
											// Set the text and value of the <option> element
											option.text = response["data"][i][7];
											option.value = response["data"][i][0];
											
											// Append the <option> element to the <select> element
											stores.appendChild(option);
										}
                                    }
                            );
						}

						function runJsProject(){
							var id = $("#'.$htmlname.'").val();
                            var obj = '.json_encode($pr).';
                            $.getJSON(obj["url"],
                                    {
                                        id: id,
                                    },
									function(response) {
								  		console.log(response);
										// Get the select2-results__options element
										var optionsElement = $("#projectid");
										optionsElement.empty();
										var option1 = document.createElement("option");
										option1.text = "";
										option1.disabled = true;
										option1.selected = true;
										$("#projectid").append(option1);
										for(var i = 0; i < response["data"].length; i++){
											var option = document.createElement("option");
									
											// Set the text and value of the <option> element
											option.text = response["data"][i][1];
											option.value = response["data"][i][0];
											
											// Append the <option> element to the <select> element
											$("#projectid").append(option);
										}
									}
                            );
						}
                        function runJsCodeForEvent'.$customer.'(obj) {
                            console.log("Run runJsCodeForEvent'.$customer.'");
                            var id = $("#'.$customer.'").val();
                            var method = obj.method;
                            var url = obj.url;
                            var htmlname = obj.htmlname;
                            var showempty = obj.showempty;
                            $.getJSON(url,
                                    {
                                        action: method,
                                        id: id,
                                        htmlname: htmlname,
                                        showempty: showempty
                                    },
                                    function(response) {
                                        $.each(obj.params, function(key,action) {
                                            if (key.length) {
                                                var num = response.num;
                                                if (num > 0) {
                                                    $("#" + key).removeAttr(action);
                                                } else {
                                                    $("#" + key).attr(action, action);
                                                }
                                            }
                                        });
                                        $("select#" + htmlname).html(response.value);
                                        if (response.num) {
                                            var selecthtml_str = response.value;
                                            var selecthtml_dom=$.parseHTML(selecthtml_str);
											if (typeof(selecthtml_dom[0][0]) !== \'undefined\') {
                                            	$("#inputautocomplete"+htmlname).val(selecthtml_dom[0][0].innerHTML);
											}
                                        } else {
                                            $("#inputautocomplete"+htmlname).val("");
                                        }
                                        $("select#" + htmlname).change();	/* Trigger event change */
                                    }
                            );
                        }
                    });
                    </script>';
				// }

				// Store
				if (isModEnabled('stores')){
					$selectedStore = $storeid;
					if(isset($_COOKIE["store"])){
						$selectedStore = $_COOKIE["store"];
					}
					print '<tr><td>'.$langs->trans("Store").'</td><td>';
					$selectedCompany = ($this->withfromsocid > 0) ? $this->withfromsocid : -1;
					$store->select_store($customerid, $storeid);
				}


				// Contact and type
				print '<tr><td>'.$langs->trans("Contact").'</td><td>';
				print img_picto('', 'contact', 'class="paddingright"');
				// if($proj->id){
				// 	if(isset($_COOKIE["customer"])){
				// 		$selectedCompany = $_COOKIE["customer"];
				// 	}
				// }
				print $form->selectcontacts("", $this->withfromcontactid, 'contactid_customer', 3, '', '', 0, 'minwidth200');

				print ' ';
				$formcompany->selectTypeContact($ticketstatic, '', 'type', 'external', '', 0, 'maginleftonly');
				print '</td></tr>';
			} else {
				print '<tr><td class="titlefield"><input type="hidden" name="options_customer" value="'.$user->socid.'"/></td>';
				$selectedStore = null;
				if(isset($_COOKIE["store"])){
					$selectedStore = $_COOKIE["store"];
				}
				if (isModEnabled('stores')){
					print '<tr><td>'.$langs->trans("Store").'</td><td>';
					// If no socid, set to -1 to avoid full contacts list
					$store->select_store("", "");
				}
				print '<td><input type="hidden" name="contactid" value="'.$user->contact_id.'"/></td>';
				print '<td><input type="hidden" name="type" value="Z"/></td></tr>';
			}


		}

		
		// TITLE
		$email = GETPOSTISSET('email') ? GETPOST('email', 'alphanohtml') : '';
		if ($this->withemail) {
			print '<tr><td class="titlefield"><label for="email"><span class="fieldrequired">'.$langs->trans("Email").'</span></label></td><td>';
			print '<input class="text minwidth200" id="email" name="email" value="'.$email.'" autofocus>';
			print '</td></tr>';

			if ($with_contact) {
				// contact search and result
				$html_contact_search  = '';
				$html_contact_search .= '<tr id="contact_search_line">';
				$html_contact_search .= '<td class="titlefield">';
				$html_contact_search .= '<label for="contact"><span class="fieldrequired">' . $langs->trans('Contact') . '</span></label>';
				$html_contact_search .= '<input type="hidden" id="contact_id" name="contact_id" value="" />';
				$html_contact_search .= '</td>';
				$html_contact_search .= '<td id="contact_search_result"></td>';
				$html_contact_search .= '</tr>';
				print $html_contact_search;
				// contact lastname
				$html_contact_lastname = '';
				$html_contact_lastname .= '<tr id="contact_lastname_line" class="contact_field"><td class="titlefield"><label for="contact_lastname"><span class="fieldrequired">' . $langs->trans('Lastname') . '</span></label></td><td>';
				$html_contact_lastname .= '<input type="text" id="contact_lastname" name="contact_lastname" value="' . dol_escape_htmltag(GETPOSTISSET('contact_lastname') ? GETPOST('contact_lastname', 'alphanohtml') : '') . '" />';
				$html_contact_lastname .= '</td></tr>';
				print $html_contact_lastname;
				// contact firstname
				$html_contact_firstname  = '';
				$html_contact_firstname .= '<tr id="contact_firstname_line" class="contact_field"><td class="titlefield"><label for="contact_firstname"><span class="fieldrequired">' . $langs->trans('Firstname') . '</span></label></td><td>';
				$html_contact_firstname .= '<input type="text" id="contact_firstname" name="contact_firstname" value="' . dol_escape_htmltag(GETPOSTISSET('contact_firstname') ? GETPOST('contact_firstname', 'alphanohtml') : '') . '" />';
				$html_contact_firstname .= '</td></tr>';
				print $html_contact_firstname;
				// company name
				$html_company_name  = '';
				$html_company_name .= '<tr id="contact_company_name_line" class="contact_field"><td><label for="company_name"><span>' . $langs->trans('Company') . '</span></label></td><td>';
				$html_company_name .= '<input type="text" id="company_name" name="company_name" value="' . dol_escape_htmltag(GETPOSTISSET('company_name') ? GETPOST('company_name', 'alphanohtml') : '') . '" />';
				$html_company_name .= '</td></tr>';
				print $html_company_name;
				// contact phone
				$html_contact_phone  = '';
				$html_contact_phone .= '<tr id="contact_phone_line" class="contact_field"><td><label for="contact_phone"><span>' . $langs->trans('Phone') . '</span></label></td><td>';
				$html_contact_phone .= '<input type="text" id="contact_phone" name="contact_phone" value="' . dol_escape_htmltag(GETPOSTISSET('contact_phone') ? GETPOST('contact_phone', 'alphanohtml') : '') . '" />';
				$html_contact_phone .= '</td></tr>';
				print $html_contact_phone;

				// search contact form email
				$langs->load('errors');
				print '<script type="text/javascript">
                    jQuery(document).ready(function() {
                        var contact = jQuery.parseJSON("'.dol_escape_js(json_encode($with_contact), 2).'");
                        jQuery("#contact_search_line").hide();
                        if (contact) {
                        	if (contact.id > 0) {
                        		jQuery("#contact_search_line").show();
                        		jQuery("#contact_id").val(contact.id);
								jQuery("#contact_search_result").html(contact.firstname+" "+contact.lastname);
								jQuery(".contact_field").hide();
                        	} else {
                        		jQuery(".contact_field").show();
                        	}
                        }

                    	jQuery("#email").change(function() {
                            jQuery("#contact_search_line").show();
                            jQuery("#contact_search_result").html("'.dol_escape_js($langs->trans('Select2SearchInProgress')).'");
                            jQuery("#contact_id").val("");
                            jQuery("#contact_lastname").val("");
                            jQuery("#contact_firstname").val("");
                            jQuery("#company_name").val("");
                            jQuery("#contact_phone").val("");

                            jQuery.getJSON(
                                "'.dol_escape_js(dol_buildpath('/public/ticket/ajax/ajax.php', 1)).'",
								{
									action: "getContacts",
									email: jQuery("#email").val()
								},
								function(response) {
									if (response.error) {
                                        jQuery("#contact_search_result").html("<span class=\"error\">"+response.error+"</span>");
									} else {
                                        var contact_list = response.contacts;
										if (contact_list.length == 1) {
                                            var contact = contact_list[0];
											jQuery("#contact_id").val(contact.id);
											jQuery("#contact_search_result").html(contact.firstname+" "+contact.lastname);
                                            jQuery(".contact_field").hide();
										} else if (contact_list.length <= 0) {
                                            jQuery("#contact_search_line").hide();
                                            jQuery(".contact_field").show();
										}
									}
								}
                            ).fail(function(jqxhr, textStatus, error) {
    							var error_msg = "'.dol_escape_js($langs->trans('ErrorAjaxRequestFailed')).'"+" ["+textStatus+"] : "+error;
                                jQuery("#contact_search_result").html("<span class=\"error\">"+error_msg+"</span>");
                            });
                        });
                    });
                    </script>';
			}
		}

		// If ticket created from another object
		$subelement = '';
		if (isset($this->param['origin']) && $this->param['originid'] > 0) {
			// Parse element/subelement (ex: project_task)
			$element = $subelement = $this->param['origin'];
			$regs = array();
			if (preg_match('/^([^_]+)_([^_]+)/i', $this->param['origin'], $regs)) {
				$element = $regs[1];
				$subelement = $regs[2];
			}

			dol_include_once('/'.$element.'/class/'.$subelement.'.class.php');
			$classname = ucfirst($subelement);
			$objectsrc = new $classname($this->db);
			$objectsrc->fetch(GETPOST('originid', 'int'));

			if (empty($objectsrc->lines) && method_exists($objectsrc, 'fetch_lines')) {
				$objectsrc->fetch_lines();
			}

			$objectsrc->fetch_thirdparty();
			$newclassname = $classname;
			// if (isModEnabled('project')) {
			// 	print '<tr>
			// 				<td>'.$langs->trans($newclassname).'</td>
			// 				<td><input name="'.$subelement.'id" value="'.GETPOST('originid').'" type="hidden" />'.$objectsrc->getNomUrl(1).'</td>
			// 		   </tr>';
			// }
			if (isModEnabled('project') && !$this->ispublic) {
				$formproject = new FormProjets($this->db);
				print '<tr><td><label for="project"><span class="">'.$langs->trans("Project").'</span></label></td><td>';
									
					global $langs, $conf, $form, $db;
			
					$sql = "SELECT p.rowid, CONCAT(p.ref, ', ', p.title, ' - ', s.nom, ' (', s.name_alias, ')',
									CASE WHEN p.fk_statut = 0 THEN ' - Draft' ELSE '' END)
							FROM ".MAIN_DB_PREFIX."projet AS p
							LEFT JOIN ".MAIN_DB_PREFIX."societe AS s ON p.fk_soc = s.rowid";
						$sql .= " WHERE p.fk_soc = ".$proj->socid;
					$sql .=" AND p.fk_statut = 1";
			
					$projects = $db->query($sql)->fetch_all();
					print img_picto('', 'project').'
						<select id="projectid" name="projectid" required>';
							print "<option selected disabled></option>";
							for($i = 0; $i < count($projects); $i++){
								if($projects[$i][0] == $proj->id) {
									print "<option value=".$projects[$i][0]." selected>".$projects[$i][1]."</option>";
								} else {
									print "<option value=".$projects[$i][0].">".$projects[$i][1]."</option>";
								}
							}
						print '</select>
					';
				print '</td></tr>';
			}
		}

		// Type of Ticket
		$typeCode = (GETPOST('type_code', 'alpha') ? GETPOST('type_code', 'alpha') : $this->type_code);
		if($mainid){
			$typeCode = $mainticket->type_code;
		}
		print '<tr><td class="titlefield"><span class="fieldrequired"><label for="selecttype_code">'.$langs->trans("TicketTypeRequest").'</span></label></td><td>';
		$this->selectTypesTickets($typeCode, 'type_code', '', 2, 1, 0, 0, 'minwidth200');
		print '</td></tr>';

		// Group => Category
		print '<tr><td><span class="fieldrequired"><label for="selectcategory_code">'.$langs->trans("TicketCategory").'</span></label></td><td>';
		$filter = '';
		if ($public) {
			$filter = 'public=1';
		}
		$categoryCode = (GETPOST('category_code') ? GETPOST('category_code') : $this->category_code);
		if($mainid){
			$categoryCode = $mainticket->category_code;
		}
		$this->selectGroupTickets($categoryCode, 'category_code', $filter, 2, 1, 0, 0, 'minwidth200');
		print '</td></tr>';

		// Severity => Priority
		$severityCode = (GETPOST('severity_code') ? GETPOST('severity_code') : $this->severity_code);
		if($mainid){
			$severityCode = $mainticket->severity_code;
		}
		print '<tr><td><span class="fieldrequired"><label for="selectseverity_code">'.$langs->trans("TicketSeverity").'</span></label></td><td>';
		$this->selectSeveritiesTickets($severityCode, 'severity_code', '', 2, 1);
		print '</td></tr>';

		if (!empty($conf->knowledgemanagement->enabled)) {
			// KM Articles
			print '<tr id="KWwithajax" class="hidden"><td></td></tr>';
			print '<!-- Script to manage change of ticket group -->
			<script>
			jQuery(document).ready(function() {
				function groupticketchange(){
					console.log("We called groupticketchange, so we try to load list KM linked to event");
					$("#KWwithajax").html("");
					idgroupticket = $("#selectcategory_code").val();

					console.log("We have selected id="+idgroupticket);

					if (idgroupticket != "") {
						$.ajax({ url: \''.DOL_URL_ROOT.'/core/ajax/fetchKnowledgeRecord.php\',
							 data: { action: \'getKnowledgeRecord\', idticketgroup: idgroupticket, token: \''.newToken().'\', lang:\''.$langs->defaultlang.'\', public:'.($public).' },
							 type: \'GET\',
							 success: function(response) {
								var urllist = \'\';
								console.log("We received response "+response);
								if (typeof response == "object") {
									console.log("response is already type object, no need to parse it");
								} else {
									console.log("response is type "+(typeof response));
									response = JSON.parse(response);
								}
								for (key in response) {
									answer = response[key].answer;
									urllist += \'<li><a href="#" title="\'+response[key].title+\'" class="button_KMpopup" data-html="\'+answer+\'">\' +response[key].title+\'</a></li>\';
								}
								if (urllist != "") {
									$("#KWwithajax").html(\'<td>'.$langs->trans("KMFoundForTicketGroup").'</td><td><ul>\'+urllist+\'</ul></td>\');
									$("#KWwithajax").show();
									$(".button_KMpopup").on("click",function(){
										console.log("Open popup with jQuery(...).dialog() with KM article")
										var $dialog = $("<div></div>").html($(this).attr("data-html"))
											.dialog({
												autoOpen: false,
												modal: true,
												height: (window.innerHeight - 150),
												width: "80%",
												title: $(this).attr("title"),
											});
										$dialog.dialog("open");
										console.log($dialog);
									})
								}
							 },
							 error : function(output) {
								console.error("Error on Fetch of KM articles");
							 },
						});
					}
				};
				$("#selectcategory_code").on("change",function() { groupticketchange(); });
				if ($("#selectcategory_code").val() != "") {
					groupticketchange();
				}
			});
			</script>'."\n";
		}

		// Subject
		if ($this->withtitletopic) {
			print '<tr><td><label for="subject"><span class="fieldrequired">'.$langs->trans("Subject").'</span></label></td><td>';
			// Answer to a ticket : display of the thread title in readonly
			if ($this->withtopicreadonly) {
				print $langs->trans('SubjectAnswerToTicket').' '.$this->topic_title;
			} else {
				if (isset($this->withreadid) && $this->withreadid > 0) {
					$subject = $langs->trans('SubjectAnswerToTicket').' '.$this->withreadid.' : '.$this->topic_title;
				} else {
					$subject = GETPOST('subject', 'alpha');
				}
				if($mainid){
					$subject = $mainticket->subject;
				}
				print '<input class="text minwidth500" id="subject" name="subject" value="'.$subject.'"'.(empty($this->withemail)?' autofocus':'').' />';
			}
			print '</td></tr>';
		}
		print '<tr><td><label for="subject"><span class="fieldrequired">'.$langs->trans("Date of use / Estimated duration").'</span></label></td><td>';
				
		$dateOfUse = null;
		if(isset($_COOKIE["dateofuse"])){
			$dateOfUse = $_COOKIE["dateofuse"];
		}
		
		$hours = array(
			array("value" => "00", "label" => "00"),
			array("value" => "01", "label" => "01"),
			array("value" => "02", "label" => "02"),
			array("value" => "03", "label" => "03"),
			array("value" => "04", "label" => "04"),
			array("value" => "05", "label" => "05"),
			array("value" => "06", "label" => "06"),
			array("value" => "07", "label" => "07"),
			array("value" => "08", "label" => "08"),
			array("value" => "09", "label" => "09"),
			array("value" => "10", "label" => "10"),
			array("value" => "11", "label" => "11"),
			array("value" => "12", "label" => "12"),
			array("value" => "13", "label" => "13"),
			array("value" => "14", "label" => "14"),
			array("value" => "15", "label" => "15"),
			array("value" => "16", "label" => "16"),
			array("value" => "17", "label" => "17"),
			array("value" => "18", "label" => "18"),
			array("value" => "19", "label" => "19"),
			array("value" => "20", "label" => "20"),
			array("value" => "21", "label" => "21"),
			array("value" => "22", "label" => "22"),
			array("value" => "23", "label" => "23")
		);

		$minutes = array(
			array("value" => "00", "label" => "00"),
			array("value" => "01", "label" => "01"),
			array("value" => "02", "label" => "02"),
			array("value" => "03", "label" => "03"),
			array("value" => "04", "label" => "04"),
			array("value" => "05", "label" => "05"),
			array("value" => "06", "label" => "06"),
			array("value" => "07", "label" => "07"),
			array("value" => "08", "label" => "08"),
			array("value" => "09", "label" => "09"),
			array("value" => "10", "label" => "10"),
			array("value" => "11", "label" => "11"),
			array("value" => "12", "label" => "12"),
			array("value" => "13", "label" => "13"),
			array("value" => "14", "label" => "14"),
			array("value" => "15", "label" => "15"),
			array("value" => "16", "label" => "16"),
			array("value" => "17", "label" => "17"),
			array("value" => "18", "label" => "18"),
			array("value" => "19", "label" => "19"),
			array("value" => "20", "label" => "20"),
			array("value" => "21", "label" => "21"),
			array("value" => "22", "label" => "22"),
			array("value" => "23", "label" => "23"),
			array("value" => "24", "label" => "24"),
			array("value" => "25", "label" => "25"),
			array("value" => "26", "label" => "26"),
			array("value" => "27", "label" => "27"),
			array("value" => "28", "label" => "28"),
			array("value" => "29", "label" => "29"),
			array("value" => "30", "label" => "30"),
			array("value" => "31", "label" => "31"),
			array("value" => "32", "label" => "32"),
			array("value" => "33", "label" => "33"),
			array("value" => "34", "label" => "34"),
			array("value" => "35", "label" => "35"),
			array("value" => "36", "label" => "36"),
			array("value" => "37", "label" => "37"),
			array("value" => "38", "label" => "38"),
			array("value" => "39", "label" => "39"),
			array("value" => "40", "label" => "40"),
			array("value" => "41", "label" => "41"),
			array("value" => "42", "label" => "42"),
			array("value" => "43", "label" => "43"),
			array("value" => "44", "label" => "44"),
			array("value" => "45", "label" => "45"),
			array("value" => "46", "label" => "46"),
			array("value" => "47", "label" => "47"),
			array("value" => "48", "label" => "48"),
			array("value" => "49", "label" => "49"),
			array("value" => "50", "label" => "50"),
			array("value" => "51", "label" => "51"),
			array("value" => "52", "label" => "52"),
			array("value" => "53", "label" => "53"),
			array("value" => "54", "label" => "54"),
			array("value" => "55", "label" => "55"),
			array("value" => "56", "label" => "56"),
			array("value" => "57", "label" => "57"),
			array("value" => "58", "label" => "58"),
			array("value" => "59", "label" => "59")
		);
				
		$datehour = 00;
		if(isset($_COOKIE["datehour"])){
			$datehour = $_COOKIE["datehour"];
		}	

		$datemin = 00;
		if(isset($_COOKIE["datemin"])){
			$datemin = $_COOKIE["datemin"];
		}

		$dateString = $dateOfUse;

		$timestamp = strtotime($dateString);

		print $form->selectDate($timestamp, 'options_dateofuse', 0, 0, 0, "perso", 1, 0);
		print '<span class="nowraponall">
					<select class="flat valignmiddle maxwidth50 " id="options_datehour" name="options_datehour">
						';
						foreach ($hours as $hour) {
							print '<option value="' . $hour["value"] . '"';
							
							if ($hour["value"] == $datehour) {
								print ' selected';
							}
							
							print '>' . $hour["label"] . '</option>';
						} print '
					</select>
					:
					<select class="flat valignmiddle maxwidth50 " id="options_datemin" name="options_datemin">
						';
						foreach ($minutes as $minute) {
							print '<option value="' . $minute["value"] . '"';
							
							if ($minute["value"] == $datemin) {
								print ' selected';
							}
							
							print '>' . $minute["label"] . '</option>';
						} print '
					</select>
				</span>
				&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';		
		$timehour = 0;
		if(isset($_COOKIE["timehour"])){
			$timehour = $_COOKIE["timehour"];
		}		
		$timeminute = 0;
		if(isset($_COOKIE["timeminute"])){
			$timeminute = $_COOKIE["timeminute"];
		}
		print '<span class="nowraponall">
					<input type="number" name="options_timehour" id="options_timehour" min="0" step="1" value="'.$timehour.'" style="width:35px"> H
					<input type="number" name="options_timeminute" id="options_timeminute" min="0" step="1" value="'.$timeminute.'" style="width:35px"> mn
				</span>';
		print '</td></tr>';
		// MESSAGE
		$msg = GETPOSTISSET('message') ? GETPOST('message', 'restricthtml') : '';
		if($mainid){
			$msg = $mainticket->subject;
		}
		print '<tr><td><label for="message"><span class="fieldrequired">'.$langs->trans("Message").'</span></label></td><td>';

		// If public form, display more information
		$toolbarname = 'dolibarr_notes';
		if ($this->ispublic) {
			$toolbarname = 'dolibarr_details';
			print '<div class="warning hideonsmartphone">'.(getDolGlobalString("TICKET_PUBLIC_TEXT_HELP_MESSAGE", $langs->trans('TicketPublicPleaseBeAccuratelyDescribe'))).'</div>';
		}
		include_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
		$uselocalbrowser = true;
		$doleditor = new DolEditor('message', $msg, '100%', 230, $toolbarname, 'In', true, $uselocalbrowser, getDolGlobalInt('FCKEDITOR_ENABLE_TICKET'), ROWS_8, '90%');
		$doleditor->Create();
		print '</td></tr>';

		if ($public && !empty($conf->global->MAIN_SECURITY_ENABLECAPTCHA_TICKET)) {
			require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
			print '<tr><td class="titlefield"><label for="email"><span class="fieldrequired">'.$langs->trans("SecurityCode").'</span></label></td><td>';
			print '<span class="span-icon-security inline-block">';
			print '<input id="securitycode" placeholder="'.$langs->trans("SecurityCode").'" class="flat input-icon-security width125" type="text" maxlength="5" name="code" tabindex="3" />';
			print '</span>';
			print '<span class="nowrap inline-block">';
			print '<img class="inline-block valignmiddle" src="'.DOL_URL_ROOT.'/core/antispamimage.php" border="0" width="80" height="32" id="img_securitycode" />';
			print '<a class="inline-block valignmiddle" href="" tabindex="4" data-role="button">'.img_picto($langs->trans("Refresh"), 'refresh', 'id="captcha_refresh_img"').'</a>';
			print '</span>';
			print '</td></tr>';
		}

		// Categories
		if (isModEnabled('categorie')) {
			include_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
			$cate_arbo = $form->select_all_categories(Categorie::TYPE_TICKET, '', 'parent', 64, 0, 1);

			if (count($cate_arbo)) {
				// Categories
				print '<tr><td class="wordbreak">'.$langs->trans("Categories").'</td><td>';
				print img_picto('', 'category', 'class="pictofixedwidth"').$form->multiselectarray('categories', $cate_arbo, GETPOST('categories', 'array'), '', 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);
				print "</td></tr>";
			}
		}

		// Attached files
		if (!empty($this->withfile)) {
			// Define list of attached files
			$listofpaths = array();
			$listofnames = array();
			$listofmimes = array();
			if (!empty($_SESSION["listofpaths"])) {
				$listofpaths = explode(';', $_SESSION["listofpaths"]);
			}

			if (!empty($_SESSION["listofnames"])) {
				$listofnames = explode(';', $_SESSION["listofnames"]);
			}

			if (!empty($_SESSION["listofmimes"])) {
				$listofmimes = explode(';', $_SESSION["listofmimes"]);
			}

			$out = '<tr>';
			$out .= '<td>'.$langs->trans("MailFile").'</td>';
			$out .= '<td>';
			// TODO Trick to have param removedfile containing nb of image to delete. But this does not works without javascript
			$out .= '<input type="hidden" class="removedfilehidden" name="removedfile" value="">'."\n";
			$out .= '<script type="text/javascript">';
			$out .= 'jQuery(document).ready(function () {';
			$out .= '    jQuery(".removedfile").click(function() {';
			$out .= '        jQuery(".removedfilehidden").val(jQuery(this).val());';
			$out .= '    });';
			$out .= '})';
			$out .= '</script>'."\n";
			if (count($listofpaths)) {
				foreach ($listofpaths as $key => $val) {
					$out .= '<div id="attachfile_'.$key.'">';
					$out .= img_mime($listofnames[$key]).' '.$listofnames[$key];
					if (!$this->withfilereadonly) {
						$out .= ' <input type="image" style="border: 0px;" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/delete.png" value="'.($key + 1).'" class="removedfile" id="removedfile_'.$key.'" name="removedfile_'.$key.'" />';
					}
					$out .= '<br></div>';
				}
			} else {
				$out .= $langs->trans("NoAttachedFiles").'<br>';
			}
			if ($this->withfile == 2) { // Can add other files
				$maxfilesizearray = getMaxFileSizeArray();
				$maxmin = $maxfilesizearray['maxmin'];
				if ($maxmin > 0) {
					$out .= '<input type="hidden" name="MAX_FILE_SIZE" value="'.($maxmin * 1024).'">';	// MAX_FILE_SIZE must precede the field type=file
				}
				$out .= '<input type="file" class="flat" id="addedfile" name="addedfile" value="'.$langs->trans("Upload").'" />';
				$out .= ' ';
				$out .= '<input type="submit" class="button smallpaddingimp reposition" id="addfile" name="addfile" value="'.$langs->trans("MailingAddFile").'" />';
			}
			$out .= "</td></tr>\n";

			print $out;
		}

		// User of creation
		if ($this->withusercreate > 0 && $this->fk_user_create) {
			print '<tr><td class="titlefield">'.$langs->trans("CreatedBy").'</td><td>';
			$langs->load("users");
			$fuser = new User($this->db);

			if ($this->withcreatereadonly) {
				if ($res = $fuser->fetch($this->fk_user_create)) {
					print $fuser->getNomUrl(1);
				}
			}
			print ' &nbsp; ';
			print "</td></tr>\n";
		}

			// Notify thirdparty at creation
			if (empty($this->ispublic)) {
				print '<tr><td><label for="notify_tiers_at_create">'.$langs->trans("TicketNotifyTiersAtCreation").'</label></td><td>';
				print '<input type="checkbox" id="notify_tiers_at_create" name="notify_tiers_at_create"'.($this->withnotifytiersatcreate ? ' checked="checked"' : '').'>';
				print '</td></tr>';
			}

			// User assigned
			print '<tr><td>';
			print $langs->trans("AssignedTo");
			print '</td><td>';
			print img_picto('', 'user', 'class="pictofixedwidth"');
			print $form->select_dolusers(GETPOST('fk_user_assign', 'int'), 'fk_user_assign', 1);
			print '</td>';
			print '</tr>';

		if ($subelement != 'project') {
			$selectedProject = $projectid;
			if(isset($_COOKIE["project"])){
				$selectedProject = $_COOKIE["project"];
			}
			if (isModEnabled('project') && !$this->ispublic) {
				$formproject = new FormProjets($this->db);
				print '<tr><td><label for="project"><span class="">'.$langs->trans("Project").'</span></label></td><td>';
				
				// $selectedCompany = null;
				// if(isset($_COOKIE["customer"])){
				// 	$selectedCompany = $_COOKIE["customer"];
				// }

				if((isset($_COOKIE["customer"]) && isset($_COOKIE["project"]))){

					print '<script>';
					print '
					$(document).on("change","#projectid",function(){
						$(".button-save").removeAttr("disabled");
					});';
					print '</script>';
				}

				if((isset($customerid) && isset($projectid))) {

					print '<script>';
						print '
							$( document ).ready(function() {
								$(".button-save").removeAttr("disabled");
							});
						';
					print '</script>';
				}

				$selectedCompany = $socid;
				// var_dump($selectedCompany);
				if($selectedCompany != -1 && $selectedCompany != null){
					
									
					global $langs, $conf, $form, $db;
			
					$sql = "SELECT p.rowid, CONCAT(p.ref, ', ', p.title, ' - ', s.nom, ' (', s.name_alias, ')',
									CASE WHEN p.fk_statut = 0 THEN ' - Draft' ELSE '' END)
							FROM ".MAIN_DB_PREFIX."projet AS p
							LEFT JOIN ".MAIN_DB_PREFIX."societe AS s ON p.fk_soc = s.rowid";
				
					$sql .= " WHERE p.fk_soc = ".$selectedCompany;
					
					$sql .=" AND p.fk_statut = 1";
			
					$projects = $db->query($sql)->fetch_all();
					print img_picto('', 'project').'
						<select id="projectid" name="projectid">';
							print "<option value='0' selected></option>";
							for($i = 0; $i < count($projects); $i++){
								if($selectedProject && $projects[$i][0] == $selectedProject){
									print "<option value=".$projects[$i][0]." selected>".$projects[$i][1]."</option>";
								} else {
									print "<option value=".$projects[$i][0].">".$projects[$i][1]."</option>";
								}
							}
						print '</select>
					';
				}elseif($user->socid){
					global $langs, $conf, $form, $db;
			
					$sql = "SELECT p.rowid, CONCAT(p.ref, ', ', p.title, ' - ', s.nom, ' (', s.name_alias, ')',
									CASE WHEN p.fk_statut = 0 THEN ' - Draft' ELSE '' END)
							FROM ".MAIN_DB_PREFIX."projet AS p
							LEFT JOIN ".MAIN_DB_PREFIX."societe AS s ON p.fk_soc = s.rowid";
					
					$sql .= " WHERE p.fk_soc = ".$user->socid;
					
					$sql .=" AND p.fk_statut = 1";
			
					$projects = $db->query($sql)->fetch_all();
					print img_picto('', 'project').'
						<select id="projectid" name="projectid">';
							print "<option value='0' selected></option>";
							for($i = 0; $i < count($projects); $i++){
								if($selectedProject && $projects[$i][0] == $selectedProject){
									print "<option value=".$projects[$i][0]." selected>".$projects[$i][1]."</option>";
								} else {
									print "<option value=".$projects[$i][0].">".$projects[$i][1]."</option>";
								}
							}
						print '</select>
					';
				}else{
					print img_picto('', 'project').'
						<select id="projectid" name="projectid">
							<option selected disabled>'.$langs->trans("selectThirdP").'</option>
						</select>
					';
				}
				print '</td></tr>';
			}
		}

		// Other attributes
		// $parameters = array();
		// $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $ticketstat, $action); // Note that $action and $object may have been modified by hook
		// if (empty($reshook)) {
		// 	print $ticketstat->showOptionals($extrafields, 'create');
		// }

		print '</table>';

		if ($withdolfichehead) {
			print dol_get_fiche_end();
		}

		print '<br><br>';

		print $form->buttonsSaveCancel(((isset($this->withreadid) && $this->withreadid > 0) ? "SendResponse" : "CreateTicket"), ($this->withcancel ? "Cancel" : ""));

		/*
		print '<div class="center">';
		print '<input type="submit" class="button" name="add" value="'.$langs->trans(($this->withreadid > 0 ? "SendResponse" : "CreateTicket")).'" />';
		if ($this->withcancel) {
			print " &nbsp; &nbsp; &nbsp;";
			print '<input class="button button-cancel" type="submit" name="cancel" value="'.$langs->trans("Cancel").'">';
		}
		print '</div>';
		*/

		print '<input type="hidden" name="page_y">'."\n";

		print "</form>\n";
		print "<!-- End form TICKET -->\n";
	}

	/**
	 * Show the form to input ticket
	 *
	 * @param  	int	 			$withdolfichehead		With dol_get_fiche_head() and dol_get_fiche_end()
	 * @param	string			$mode					Mode ('create' or 'edit')
	 * @param	int				$public					1=If we show the form for the public interface
	 * @param	Contact|null	$with_contact			[=NULL] Contact to link to this ticket if it exists
	 * @param	string			$action					[=''] Action in card
	 * @return 	void
	 */
	public function showForm_1($withdolfichehead = 0, $mode = 'edit', $public = 0, Contact $with_contact = null, $action = '', $third, $store)
	{
		global $conf, $langs, $user, $hookmanager;
		// Load translation files required by the page
		$langs->loadLangs(array('other', 'mails', 'ticket'));

		$form = new Form($this->db);
		$formcompany = new FormCompany($this->db);
		$ticketstatic = new Ticket($this->db);

		$soc = new Societe($this->db);

		$ticketstat = new Ticket($this->db);

		$extrafields = new ExtraFields($this->db);
		$extrafields->fetch_name_optionals_label($ticketstat->table_element);

		print "\n<!-- Begin form TICKET -->\n";

		if ($withdolfichehead) {
			print dol_get_fiche_head(null, 'card', '', 0, '');
		}

		print '<form method="POST" '.($withdolfichehead ? '' : 'style="margin-bottom: 30px;" ').'name="ticket" id="form_create_ticket" enctype="multipart/form-data" action="'.(!empty($this->param["returnurl"]) ? $this->param["returnurl"] : $_SERVER['PHP_SELF']).'?action=create">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="'.$this->action.'">';
		print '<input type="hidden" name="trackid" value="'.$this->trackid.'">';
		foreach ($this->param as $key => $value) {
			print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
		}
		print '<input type="hidden" name="fk_user_create" value="'.$this->fk_user_create.'">';

		print '<table class="border centpercent">';
		print '<script type="text/javascript">
					$(document).ready(function () {

						$("form").submit(function() {
							var formData = $(this).serializeArray();
							formData.forEach(function(field) {
								localStorage.setItem(field.name, field.value);
							});
						});
						var currentURL = window.location.href;
						var parts = currentURL.split("?");
						if (parts[1] == null) {
							$("form input, form select, form textarea, form span").each(function() {
								var storedValue = localStorage.getItem($(this).attr("name"));
								if (storedValue) {
									$(this).val(storedValue);
								}
							});
						}else{
							localStorage.clear();
						}

						const cookies = document.cookie.split(";");

						for (let i = 0; i < cookies.length; i++) {
							const cookie = cookies[i];
							if (cookie.startsWith("project=")) {
								var cookieValue = cookie.substring("project".length + 1);
								
								if (cookieValue !== "") {
									console.log(1);
									$(".button-save").removeAttr("disabled");
								}
							}
							const eqPos = cookie.indexOf("=");
							const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
							document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
						}

						var submitButton = document.getElementById("addfile");

						submitButton.addEventListener("click", function(event) {
							document.cookie = "customer=" + $("#options_customer").val();
							document.cookie = "ordervia=" + $("#options_ordervia").val();
							document.cookie = "store=" + $("#options_fk_store").val();
							document.cookie = "project=" + $("#projectid").val();
							document.cookie = "dateofuse=" + $("#options_dateofuse").val();
							document.cookie = "timehour=" + $("#options_timehour").val();
							document.cookie = "timeminute=" + $("#options_timeminute").val();
							document.cookie = "datemin=" + $("#options_datemin").val();
							document.cookie = "datehour=" + $("#options_datehour").val();
							document.cookie = "externalref=" + $("#options_externalticketnumber").val();
							document.cookie = "parentticket=" + $("#options_parentticket").val();
						});
					});';
		print '</script>';

		if ($this->withref) {
			// Ref
			$defaultref = $ticketstat->getDefaultRef();
			print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans("Ref").'</span></td><td>';
			print '<input type="text" name="ref" value="'.dol_escape_htmltag(GETPOST("ref", 'alpha') ? GETPOST("ref", 'alpha') : $defaultref).'">';
			print '</td></tr>';
		}
		
		// Parent Ticket
		$parentticket = "";
		if(isset($_COOKIE["parentticket"])){
			$parentticket = $_COOKIE["parentticket"];
		}
		print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans("Parent Ticket").'</span></td><td>';
		print $form->selectTickets($parentticket,'options_parentticket');
		print '</td></tr>';

		$externalref = "";
		if(isset($_COOKIE["externalref"])){
			$externalref = $_COOKIE["externalref"];
		}
		//External Ref
		print '<tr class="fieldrequired field_options_externalticketnumber ticket_extras_externalticketnumber trextrafields_collapse" data-element="extrafield" data-targetelement="ticket" data-targetid="">
					<td class="titlefieldcreate wordbreak">'.$langs->trans("External Ticket Number").'</td>
					<td class="valuefieldcreate ticket_extras_externalticketnumber">
						<input type="text" class="flat minwidth400 maxwidthonsmartphone" name="options_externalticketnumber" id="options_externalticketnumber" maxlength="255" value="'.$externalref.'">
					</td>
				</tr>';

		$soc = new Societe($this->db);
		$soc->fetch($third);

		$branch = new Branch($this->db);
		$branch->fetch($store);



		// ThirdParty
		print '<tr><td class="titlefield">'.$langs->trans("ThirdParty").'</td><td>';
		$events = array();
		$events[] = array('method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php', 1), 'htmlname' => 'contactid', 'params' => array('add-customer-contact' => 'disabled'));
		$st = array('method' => 'getContacts',
						'url' => dol_buildpath('/custom/stores/ajax/stores.php', 1),
						'htmlname' => 'contactid_customer',
						'params' => array('add-customer-contact' => 'disabled')
					);
		$pr = array('method' => 'getContacts',
						'url' => dol_buildpath('/ticket/ajax/tickets.php', 1),
						'htmlname' => 'contactid_customer',
						'params' => array('add-customer-contact' => 'disabled')
					);

		// $selectedCustomer = $proj->socid;
		// if(isset($_COOKIE["customer"])){
		// 	$selectedCustomer = $_COOKIE["customer"];
		// }
		print img_picto($selectedCompany, 'company', 'class="paddingright"');
		print $form->select_company($selectedCompany, 'socid', '', 1, 1, '', $events, 0, 'minwidth200');

		print '</td></tr>';
		$htmlname = 'socid';
		$customer='options_customer';
		print '<script type="text/javascript">
		$(document).ready(function () {
			jQuery("#'.$htmlname.'").change(function () {
				runJsProject();
			});

			function runJsProject(){
				var id = $("#'.$htmlname.'").val();
				var obj = '.json_encode($pr).';
				$.getJSON(obj["url"],
						{
							id: id,
						},
						function(response) {
					  
							// Get the select2-results__options element
							var optionsElement = $("#projectid");
							optionsElement.empty();
							var option1 = document.createElement("option");
							option1.text = "";
							option1.disabled = true;
							option1.selected = true;
							$("#projectid").append(option1);
							for(var i = 0; i < response["data"].length; i++){
								var option = document.createElement("option");
						
								// Set the text and value of the <option> element
								option.text = response["data"][i][1];
								option.value = response["data"][i][0];
								
								// Append the <option> element to the <select> element
								$("#projectid").append(option);
							}
						}
				);
			}
		});
		</script>';

		// Order Via
		print '<tr><td>'.$langs->trans("Order via ").'</td><td>';
		// If no socid, set to -1 to avoid full contacts list
		$selectedCompany = ($this->withfromsocid > 0) ? $this->withfromsocid : -1;
		$selectedOrderVia = 0;
		if(isset($_COOKIE["ordervia"])){
			$selectedOrderVia = $_COOKIE["ordervia"];
		}
		$branch->select_order_customer($selectedOrderVia);

		// Contact and type
		print '<tr><td>'.$langs->trans("Contact").'</td><td>';
		print img_picto('', 'contact', 'class="paddingright"');
		print $form->selectcontacts($selectedCompany, $this->withfromcontactid, 'contactid', 3, '', '', 0, 'minwidth200');
		print ' ';
		$formcompany->selectTypeContact($ticketstatic, '', 'type', 'external', '', 0, 'maginleftonly');
		print '</td></tr>';

		
		// Customer
		print '<tr><td>'.$langs->trans("Customer").'</td><td>';
		print '<span class="fas fa-building paddingright" style=" color: #6c6aa8;"></span> <a href="'.dol_buildpath('/societe/card.php',1).'?socid='.$third.'">'.$soc->name.'</a>';
		print '<input type="hidden" name="options_customer" value="'.$third.'"/>';
		print '</td></tr>';
		// Store
		if (isModEnabled('stores')){
			print '<tr><td>'.$langs->trans("Store").'</td><td>';
			print '<span class="fas fa-store paddingright" style=" color: #6c6aa8;"></span></span> <a href="'.dol_buildpath('/stores/branch_card.php', 1).'?id='.$store.'">'.$branch->b_number.'</a>';
			print '<input type="hidden" id="options_fk_store" name="options_fk_store" value="'.$store.'"/>';
			print '</td></tr>';
		}
		
		// Contact and type
		print '<tr><td>'.$langs->trans("Contact").'</td><td>';
		print img_picto('', 'contact', 'class="paddingright"');
		print $form->selectcontacts($third, $this->withfromcontactid, 'contactid_customer', 3, '', '', 0, 'minwidth200');
		print ' ';
		$formcompany->selectTypeContact($ticketstatic, '', 'type_customer', 'external', '', 0, 'maginleftonly');
		print '</td></tr>';
		
		// TITLE
		$email = GETPOSTISSET('email') ? GETPOST('email', 'alphanohtml') : '';
		if ($this->withemail) {
			print '<tr><td class="titlefield"><label for="email"><span class="fieldrequired">'.$langs->trans("Email").'</span></label></td><td>';
			print '<input class="text minwidth200" id="email" name="email" value="'.$email.'" autofocus>';
			print '</td></tr>';

			if ($with_contact) {
				// contact search and result
				$html_contact_search  = '';
				$html_contact_search .= '<tr id="contact_search_line">';
				$html_contact_search .= '<td class="titlefield">';
				$html_contact_search .= '<label for="contact"><span class="fieldrequired">' . $langs->trans('Contact') . '</span></label>';
				$html_contact_search .= '<input type="hidden" id="contact_id" name="contact_id" value="" />';
				$html_contact_search .= '</td>';
				$html_contact_search .= '<td id="contact_search_result"></td>';
				$html_contact_search .= '</tr>';
				print $html_contact_search;
				// contact lastname
				$html_contact_lastname = '';
				$html_contact_lastname .= '<tr id="contact_lastname_line" class="contact_field"><td class="titlefield"><label for="contact_lastname"><span class="fieldrequired">' . $langs->trans('Lastname') . '</span></label></td><td>';
				$html_contact_lastname .= '<input type="text" id="contact_lastname" name="contact_lastname" value="' . dol_escape_htmltag(GETPOSTISSET('contact_lastname') ? GETPOST('contact_lastname', 'alphanohtml') : '') . '" />';
				$html_contact_lastname .= '</td></tr>';
				print $html_contact_lastname;
				// contact firstname
				$html_contact_firstname  = '';
				$html_contact_firstname .= '<tr id="contact_firstname_line" class="contact_field"><td class="titlefield"><label for="contact_firstname"><span class="fieldrequired">' . $langs->trans('Firstname') . '</span></label></td><td>';
				$html_contact_firstname .= '<input type="text" id="contact_firstname" name="contact_firstname" value="' . dol_escape_htmltag(GETPOSTISSET('contact_firstname') ? GETPOST('contact_firstname', 'alphanohtml') : '') . '" />';
				$html_contact_firstname .= '</td></tr>';
				print $html_contact_firstname;
				// company name
				$html_company_name  = '';
				$html_company_name .= '<tr id="contact_company_name_line" class="contact_field"><td><label for="company_name"><span>' . $langs->trans('Company') . '</span></label></td><td>';
				$html_company_name .= '<input type="text" id="company_name" name="company_name" value="' . dol_escape_htmltag(GETPOSTISSET('company_name') ? GETPOST('company_name', 'alphanohtml') : '') . '" />';
				$html_company_name .= '</td></tr>';
				print $html_company_name;
				// contact phone
				$html_contact_phone  = '';
				$html_contact_phone .= '<tr id="contact_phone_line" class="contact_field"><td><label for="contact_phone"><span>' . $langs->trans('Phone') . '</span></label></td><td>';
				$html_contact_phone .= '<input type="text" id="contact_phone" name="contact_phone" value="' . dol_escape_htmltag(GETPOSTISSET('contact_phone') ? GETPOST('contact_phone', 'alphanohtml') : '') . '" />';
				$html_contact_phone .= '</td></tr>';
				print $html_contact_phone;

				// search contact form email
				$langs->load('errors');
				print '<script type="text/javascript">
                    jQuery(document).ready(function() {
                        var contact = jQuery.parseJSON("'.dol_escape_js(json_encode($with_contact), 2).'");
                        jQuery("#contact_search_line").hide();
                        if (contact) {
                        	if (contact.id > 0) {
                        		jQuery("#contact_search_line").show();
                        		jQuery("#contact_id").val(contact.id);
								jQuery("#contact_search_result").html(contact.firstname+" "+contact.lastname);
								jQuery(".contact_field").hide();
                        	} else {
                        		jQuery(".contact_field").show();
                        	}
                        }

                    	jQuery("#email").change(function() {
                            jQuery("#contact_search_line").show();
                            jQuery("#contact_search_result").html("'.dol_escape_js($langs->trans('Select2SearchInProgress')).'");
                            jQuery("#contact_id").val("");
                            jQuery("#contact_lastname").val("");
                            jQuery("#contact_firstname").val("");
                            jQuery("#company_name").val("");
                            jQuery("#contact_phone").val("");

                            jQuery.getJSON(
                                "'.dol_escape_js(dol_buildpath('/public/ticket/ajax/ajax.php', 1)).'",
								{
									action: "getContacts",
									email: jQuery("#email").val()
								},
								function(response) {
									if (response.error) {
                                        jQuery("#contact_search_result").html("<span class=\"error\">"+response.error+"</span>");
									} else {
                                        var contact_list = response.contacts;
										if (contact_list.length == 1) {
                                            var contact = contact_list[0];
											jQuery("#contact_id").val(contact.id);
											jQuery("#contact_search_result").html(contact.firstname+" "+contact.lastname);
                                            jQuery(".contact_field").hide();
										} else if (contact_list.length <= 0) {
                                            jQuery("#contact_search_line").hide();
                                            jQuery(".contact_field").show();
										}
									}
								}
                            ).fail(function(jqxhr, textStatus, error) {
    							var error_msg = "'.dol_escape_js($langs->trans('ErrorAjaxRequestFailed')).'"+" ["+textStatus+"] : "+error;
                                jQuery("#contact_search_result").html("<span class=\"error\">"+error_msg+"</span>");
                            });
                        });
                    });
                    </script>';
			}
		}

		// If ticket created from another object
		$subelement = '';
		if (isset($this->param['origin']) && $this->param['originid'] > 0) {
			// Parse element/subelement (ex: project_task)
			$element = $subelement = $this->param['origin'];
			$regs = array();
			if (preg_match('/^([^_]+)_([^_]+)/i', $this->param['origin'], $regs)) {
				$element = $regs[1];
				$subelement = $regs[2];
			}

			dol_include_once('/'.$element.'/class/'.$subelement.'.class.php');
			$classname = ucfirst($subelement);
			$objectsrc = new $classname($this->db);
			$objectsrc->fetch(GETPOST('originid', 'int'));

			if (empty($objectsrc->lines) && method_exists($objectsrc, 'fetch_lines')) {
				$objectsrc->fetch_lines();
			}

			$objectsrc->fetch_thirdparty();
			$newclassname = $classname;
			print '<tr><td>'.$langs->trans($newclassname).'</td><td colspan="2"><input name="'.$subelement.'id" value="'.GETPOST('originid').'" type="hidden" />'.$objectsrc->getNomUrl(1).'</td></tr>';
		}

		// Type of Ticket
		print '<tr><td class="titlefield"><span class="fieldrequired"><label for="selecttype_code">'.$langs->trans("TicketTypeRequest").'</span></label></td><td>';
		$this->selectTypesTickets((GETPOST('type_code', 'alpha') ? GETPOST('type_code', 'alpha') : $this->type_code), 'type_code', '', 2, 1, 0, 0, 'minwidth200');
		print '</td></tr>';

		// Group => Category
		print '<tr><td><span class="fieldrequired"><label for="selectcategory_code">'.$langs->trans("TicketCategory").'</span></label></td><td>';
		$filter = '';
		if ($public) {
			$filter = 'public=1';
		}
		$this->selectGroupTickets((GETPOST('category_code') ? GETPOST('category_code') : $this->category_code), 'category_code', $filter, 2, 1, 0, 0, 'minwidth200');
		print '</td></tr>';

		// Severity => Priority
		print '<tr><td><span class="fieldrequired"><label for="selectseverity_code">'.$langs->trans("TicketSeverity").'</span></label></td><td>';
		$this->selectSeveritiesTickets((GETPOST('severity_code') ? GETPOST('severity_code') : $this->severity_code), 'severity_code', '', 2, 1);
		print '</td></tr>';

		if (!empty($conf->knowledgemanagement->enabled)) {
			// KM Articles
			print '<tr id="KWwithajax" class="hidden"><td></td></tr>';
			print '<!-- Script to manage change of ticket group -->
			<script>
			jQuery(document).ready(function() {
				function groupticketchange(){
					console.log("We called groupticketchange, so we try to load list KM linked to event");
					$("#KWwithajax").html("");
					idgroupticket = $("#selectcategory_code").val();

					console.log("We have selected id="+idgroupticket);

					if (idgroupticket != "") {
						$.ajax({ url: \''.DOL_URL_ROOT.'/core/ajax/fetchKnowledgeRecord.php\',
							 data: { action: \'getKnowledgeRecord\', idticketgroup: idgroupticket, token: \''.newToken().'\', lang:\''.$langs->defaultlang.'\', public:'.($public).' },
							 type: \'GET\',
							 success: function(response) {
								var urllist = \'\';
								console.log("We received response "+response);
								if (typeof response == "object") {
									console.log("response is already type object, no need to parse it");
								} else {
									console.log("response is type "+(typeof response));
									response = JSON.parse(response);
								}
								for (key in response) {
									answer = response[key].answer;
									urllist += \'<li><a href="#" title="\'+response[key].title+\'" class="button_KMpopup" data-html="\'+answer+\'">\' +response[key].title+\'</a></li>\';
								}
								if (urllist != "") {
									$("#KWwithajax").html(\'<td>'.$langs->trans("KMFoundForTicketGroup").'</td><td><ul>\'+urllist+\'</ul></td>\');
									$("#KWwithajax").show();
									$(".button_KMpopup").on("click",function(){
										console.log("Open popup with jQuery(...).dialog() with KM article")
										var $dialog = $("<div></div>").html($(this).attr("data-html"))
											.dialog({
												autoOpen: false,
												modal: true,
												height: (window.innerHeight - 150),
												width: "80%",
												title: $(this).attr("title"),
											});
										$dialog.dialog("open");
										console.log($dialog);
									})
								}
							 },
							 error : function(output) {
								console.error("Error on Fetch of KM articles");
							 },
						});
					}
				};
				$("#selectcategory_code").on("change",function() { groupticketchange(); });
				if ($("#selectcategory_code").val() != "") {
					groupticketchange();
				}
			});
			</script>'."\n";
		}

		// Subject
		if ($this->withtitletopic) {
			print '<tr><td><label for="subject"><span class="fieldrequired">'.$langs->trans("Subject").'</span></label></td><td>';
			// Answer to a ticket : display of the thread title in readonly
			if ($this->withtopicreadonly) {
				print $langs->trans('SubjectAnswerToTicket').' '.$this->topic_title;
			} else {
				if (isset($this->withreadid) && $this->withreadid > 0) {
					$subject = $langs->trans('SubjectAnswerToTicket').' '.$this->withreadid.' : '.$this->topic_title;
				} else {
					$subject = GETPOST('subject', 'alpha');
				}
				print '<input class="text minwidth500" id="subject" name="subject" value="'.$subject.'"'.(empty($this->withemail)?' autofocus':'').' />';
			}
			print '</td></tr>';
		}
		
		print '<tr><td><label for="subject"><span class="fieldrequired">'.$langs->trans("Date of use / Estimated duration").'</span></label></td><td>';
		print $form->selectDate($open, 'options_dateofuse', 0, 0, 0, "perso", 1, 0);
		print '<span class="nowraponall">
					<select class="flat valignmiddle maxwidth50 " id="options_datehour" name="options_datehour">
						<option value="00" selected>00</option><option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option>
					</select>
					:
					<select class="flat valignmiddle maxwidth50 " id="options_datemin" name="options_datemin">
						<option value="00" selected>00</option><option value="01">01</option><option value="02">02</option><option value="03">03</option><option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option><option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option><option value="32">32</option><option value="33">33</option><option value="34">34</option><option value="35">35</option><option value="36">36</option><option value="37">37</option><option value="38">38</option><option value="39">39</option><option value="40">40</option><option value="41">41</option><option value="42">42</option><option value="43">43</option><option value="44">44</option><option value="45">45</option><option value="46">46</option><option value="47">47</option><option value="48">48</option><option value="49">49</option><option value="50">50</option><option value="51">51</option><option value="52">52</option><option value="53">53</option><option value="54">54</option><option value="55">55</option><option value="56">56</option><option value="57">57</option><option value="58">58</option><option value="59">59</option>
					</select>
				</span>
				&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';
		print '<span class="nowraponall">
					<input type="number" name="options_timehour" id="options_timehour" min="0" step="1" value="0" style="width:35px"> H
					<input type="number" name="options_timeminute" id="options_timeminute" min="0" step="1" value="0" style="width:35px"> mn
				</span>';
		print '</td></tr>';

		// MESSAGE
		$msg = GETPOSTISSET('message') ? GETPOST('message', 'restricthtml') : '';
		print '<tr><td><label for="message"><span class="fieldrequired">'.$langs->trans("Message").'</span></label></td><td>';

		// If public form, display more information
		$toolbarname = 'dolibarr_notes';
		if ($this->ispublic) {
			$toolbarname = 'dolibarr_details';
			print '<div class="warning hideonsmartphone">'.(getDolGlobalString("TICKET_PUBLIC_TEXT_HELP_MESSAGE", $langs->trans('TicketPublicPleaseBeAccuratelyDescribe'))).'</div>';
		}
		include_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
		$uselocalbrowser = true;
		$doleditor = new DolEditor('message', $msg, '100%', 230, $toolbarname, 'In', true, $uselocalbrowser, getDolGlobalInt('FCKEDITOR_ENABLE_TICKET'), ROWS_8, '90%');
		$doleditor->Create();
		print '</td></tr>';

		if ($public && !empty($conf->global->MAIN_SECURITY_ENABLECAPTCHA_TICKET)) {
			require_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
			print '<tr><td class="titlefield"><label for="email"><span class="fieldrequired">'.$langs->trans("SecurityCode").'</span></label></td><td>';
			print '<span class="span-icon-security inline-block">';
			print '<input id="securitycode" placeholder="'.$langs->trans("SecurityCode").'" class="flat input-icon-security width125" type="text" maxlength="5" name="code" tabindex="3" />';
			print '</span>';
			print '<span class="nowrap inline-block">';
			print '<img class="inline-block valignmiddle" src="'.DOL_URL_ROOT.'/core/antispamimage.php" border="0" width="80" height="32" id="img_securitycode" />';
			print '<a class="inline-block valignmiddle" href="" tabindex="4" data-role="button">'.img_picto($langs->trans("Refresh"), 'refresh', 'id="captcha_refresh_img"').'</a>';
			print '</span>';
			print '</td></tr>';
		}

		// Categories
		if (isModEnabled('categorie')) {
			include_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
			$cate_arbo = $form->select_all_categories(Categorie::TYPE_TICKET, '', 'parent', 64, 0, 1);

			if (count($cate_arbo)) {
				// Categories
				print '<tr><td class="wordbreak">'.$langs->trans("Categories").'</td><td>';
				print img_picto('', 'category', 'class="pictofixedwidth"').$form->multiselectarray('categories', $cate_arbo, GETPOST('categories', 'array'), '', 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);
				print "</td></tr>";
			}
		}

		// Attached files
		if (!empty($this->withfile)) {
			// Define list of attached files
			$listofpaths = array();
			$listofnames = array();
			$listofmimes = array();
			if (!empty($_SESSION["listofpaths"])) {
				$listofpaths = explode(';', $_SESSION["listofpaths"]);
			}

			if (!empty($_SESSION["listofnames"])) {
				$listofnames = explode(';', $_SESSION["listofnames"]);
			}

			if (!empty($_SESSION["listofmimes"])) {
				$listofmimes = explode(';', $_SESSION["listofmimes"]);
			}

			$out = '<tr>';
			$out .= '<td>'.$langs->trans("MailFile").'</td>';
			$out .= '<td>';
			// TODO Trick to have param removedfile containing nb of image to delete. But this does not works without javascript
			$out .= '<input type="hidden" class="removedfilehidden" name="removedfile" value="">'."\n";
			$out .= '<script type="text/javascript">';
			$out .= 'jQuery(document).ready(function () {';
			$out .= '    jQuery(".removedfile").click(function() {';
			$out .= '        jQuery(".removedfilehidden").val(jQuery(this).val());';
			$out .= '    });';
			$out .= '})';
			$out .= '</script>'."\n";
			if (count($listofpaths)) {
				foreach ($listofpaths as $key => $val) {
					$out .= '<div id="attachfile_'.$key.'">';
					$out .= img_mime($listofnames[$key]).' '.$listofnames[$key];
					if (!$this->withfilereadonly) {
						$out .= ' <input type="image" style="border: 0px;" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/delete.png" value="'.($key + 1).'" class="removedfile" id="removedfile_'.$key.'" name="removedfile_'.$key.'" />';
					}
					$out .= '<br></div>';
				}
			} else {
				$out .= $langs->trans("NoAttachedFiles").'<br>';
			}
			if ($this->withfile == 2) { // Can add other files
				$maxfilesizearray = getMaxFileSizeArray();
				$maxmin = $maxfilesizearray['maxmin'];
				if ($maxmin > 0) {
					$out .= '<input type="hidden" name="MAX_FILE_SIZE" value="'.($maxmin * 1024).'">';	// MAX_FILE_SIZE must precede the field type=file
				}
				$out .= '<input type="file" class="flat" id="addedfile" name="addedfile" value="'.$langs->trans("Upload").'" />';
				$out .= ' ';
				$out .= '<input type="submit" class="button smallpaddingimp reposition" id="addfile" name="addfile" value="'.$langs->trans("MailingAddFile").'" />';
			}
			$out .= "</td></tr>\n";

			print $out;
		}

		// User of creation
		if ($this->withusercreate > 0 && $this->fk_user_create) {
			print '<tr><td class="titlefield">'.$langs->trans("CreatedBy").'</td><td>';
			$langs->load("users");
			$fuser = new User($this->db);

			if ($this->withcreatereadonly) {
				if ($res = $fuser->fetch($this->fk_user_create)) {
					print $fuser->getNomUrl(1);
				}
			}
			print ' &nbsp; ';
			print "</td></tr>\n";
		}

			// Notify thirdparty at creation
			if (empty($this->ispublic)) {
				print '<tr><td><label for="notify_tiers_at_create">'.$langs->trans("TicketNotifyTiersAtCreation").'</label></td><td>';
				print '<input type="checkbox" id="notify_tiers_at_create" name="notify_tiers_at_create"'.($this->withnotifytiersatcreate ? ' checked="checked"' : '').'>';
				print '</td></tr>';
			}

			// User assigned
			print '<tr><td>';
			print $langs->trans("AssignedTo");
			print '</td><td>';
			print img_picto('', 'user', 'class="pictofixedwidth"');
			print $form->select_dolusers(GETPOST('fk_user_assign', 'int'), 'fk_user_assign', 1);
			print '</td>';
			print '</tr>';

		// if ($subelement != 'project') {
			if (isModEnabled('project')) {
				$formproject = new FormProjets($this->db);
				print '<tr><td><label for="project"><span class="">'.$langs->trans("Project").'</span></label></td><td>';

					global $langs, $conf, $form, $db;
			
					$sql = "SELECT p.rowid, CONCAT(p.ref, ', ', p.title, ' - ', s.nom, ' (', s.name_alias, ')',
									CASE WHEN p.fk_statut = 0 THEN ' - Draft' ELSE '' END)
							FROM ".MAIN_DB_PREFIX."projet AS p
							LEFT JOIN ".MAIN_DB_PREFIX."societe AS s ON p.fk_soc = s.rowid";
					$sql .= " WHERE p.fk_soc = ".$third;
					$sql .=" AND p.fk_statut = 1";
			
					$projects = $db->query($sql)->fetch_all();
					print img_picto('', 'project').'
						<select id="projectid" name="projectid">';
							print '<option selected disabled>'.$langs->trans("selectThirdP").'</option>';
						print '</select>
					';
				print '</td></tr>';
			}
		// }

		// Other attributes
		// $parameters = array();
		// $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $ticketstat, $action); // Note that $action and $object may have been modified by hook
		// if (empty($reshook)) {
		// 	print $ticketstat->showOptionals($extrafields, 'create');
		// }

		print '</table>';

		if ($withdolfichehead) {
			print dol_get_fiche_end();
		}

		print '<br><br>';

		print $form->buttonsSaveCancel(((isset($this->withreadid) && $this->withreadid > 0) ? "SendResponse" : "CreateTicket"), ($this->withcancel ? "Cancel" : ""));

		/*
		print '<div class="center">';
		print '<input type="submit" class="button" name="add" value="'.$langs->trans(($this->withreadid > 0 ? "SendResponse" : "CreateTicket")).'" />';
		if ($this->withcancel) {
			print " &nbsp; &nbsp; &nbsp;";
			print '<input class="button button-cancel" type="submit" name="cancel" value="'.$langs->trans("Cancel").'">';
		}
		print '</div>';
		*/

		print '<input type="hidden" name="page_y">'."\n";

		print "</form>\n";
		print "<!-- End form TICKET -->\n";
	}

	/**
	 *      Return html list of tickets type
	 *
	 *      @param  string|array	$selected		Id of preselected field or array of Ids
	 *      @param  string			$htmlname		Nom de la zone select
	 *      @param  string			$filtertype		To filter on field type in llx_c_ticket_type (array('code'=>xx,'label'=>zz))
	 *      @param  int				$format			0=id+libelle, 1=code+code, 2=code+libelle, 3=id+code
	 *      @param  int				$empty			1=peut etre vide, 0 sinon
	 *      @param  int				$noadmininfo	0=Add admin info, 1=Disable admin info
	 *      @param  int				$maxlength		Max length of label
	 *      @param	string			$morecss		More CSS
	 *      @param  int				$multiselect	Is multiselect ?
	 *      @return void
	 */
	public function selectTypesTickets($selected = '', $htmlname = 'tickettype', $filtertype = '', $format = 0, $empty = 0, $noadmininfo = 0, $maxlength = 0, $morecss = '', $multiselect = 0)
	{
		global $langs, $user;

		$selected = is_array($selected) ? $selected : (!empty($selected) ? explode(',', $selected) : array());
		$ticketstat = new Ticket($this->db);

		dol_syslog(get_class($this) . "::select_types_tickets " . implode(';', $selected) . ", " . $htmlname . ", " . $filtertype . ", " . $format . ", " . $multiselect, LOG_DEBUG);

		$filterarray = array();

		if ($filtertype != '' && $filtertype != '-1') {
			$filterarray = explode(',', $filtertype);
		}

		$ticketstat->loadCacheTypesTickets();

		print '<select id="select'.$htmlname.'" class="flat minwidth100'.($morecss ? ' '.$morecss : '').'" name="'.$htmlname.($multiselect ? '[]' : '').'"'.($multiselect ? ' multiple' : '').'>';
		if ($empty) {
			print '<option value="">&nbsp;</option>';
		}

		if (is_array($ticketstat->cache_types_tickets) && count($ticketstat->cache_types_tickets)) {
			foreach ($ticketstat->cache_types_tickets as $id => $arraytypes) {
				// On passe si on a demande de filtrer sur des modes de paiments particuliers
				if (count($filterarray) && !in_array($arraytypes['type'], $filterarray)) {
					continue;
				}

				// If 'showempty' is enabled we discard empty line because an empty line has already been output.
				if ($empty && empty($arraytypes['code'])) {
					continue;
				}

				if ($format == 0) {
					print '<option value="'.$id.'"';
				}

				if ($format == 1) {
					print '<option value="'.$arraytypes['code'].'"';
				}

				if ($format == 2) {
					print '<option value="'.$arraytypes['code'].'"';
				}

				if ($format == 3) {
					print '<option value="'.$id.'"';
				}

				// If text is selected, we compare with code, otherwise with id
				if (in_array($arraytypes['code'], $selected)) {
					print ' selected="selected"';
				} elseif (in_array($id, $selected)) {
					print ' selected="selected"';
				} elseif ($arraytypes['use_default'] == "1" && !$selected && !$empty) {
					print ' selected="selected"';
				}

				print '>';

				$value = '&nbsp;';
				if ($format == 0) {
					$value = ($maxlength ? dol_trunc($arraytypes['label'], $maxlength) : $arraytypes['label']);
				} elseif ($format == 1) {
					$value = $arraytypes['code'];
				} elseif ($format == 2) {
					$value = ($maxlength ? dol_trunc($arraytypes['label'], $maxlength) : $arraytypes['label']);
				} elseif ($format == 3) {
					$value = $arraytypes['code'];
				}

				print $value ? $value : '&nbsp;';
				print '</option>';
			}
		}
		print '</select>';
		if (isset($user->admin) && $user->admin && !$noadmininfo) {
			print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
		}

		print ajax_combobox('select'.$htmlname);
	}

	/**
	 *      Return html list of ticket anaytic codes
	 *
	 *      @param  string 		$selected   		Id pre-selected category
	 *      @param  string 		$htmlname   		Name of select component
	 *      @param  string 		$filtertype 		To filter on some properties in llx_c_ticket_category ('public = 1'). This parameter must not come from input of users.
	 *      @param  int    		$format     		0 = id+label, 1 = code+code, 2 = code+label, 3 = id+code
	 *      @param  int    		$empty      		1 = can be empty, 0 = or not
	 *      @param  int    		$noadmininfo		0 = ddd admin info, 1 = disable admin info
	 *      @param  int    		$maxlength  		Max length of label
	 *      @param	string		$morecss			More CSS
	 * 		@param	int 		$use_multilevel		If > 0 create a multilevel select which use $htmlname example: $use_multilevel = 1 permit to have 2 select boxes.
	 * 		@param	Translate	$outputlangs		Output language
	 *      @return string|void						String of HTML component
	 */
	public function selectGroupTickets($selected = '', $htmlname = 'ticketcategory', $filtertype = '', $format = 0, $empty = 0, $noadmininfo = 0, $maxlength = 0, $morecss = '', $use_multilevel = 0, $outputlangs = null)
	{
		global $conf, $langs, $user;

		dol_syslog(get_class($this)."::selectCategoryTickets ".$selected.", ".$htmlname.", ".$filtertype.", ".$format, LOG_DEBUG);

		if (is_null($outputlangs) || !is_object($outputlangs)) {
			$outputlangs = $langs;
		}
		$outputlangs->load("ticket");

		$publicgroups = ($filtertype == 'public=1' || $filtertype == '(public:=:1)');

		$ticketstat = new Ticket($this->db);
		$ticketstat->loadCacheCategoriesTickets($publicgroups ? 1 : -1);	// get list of active ticket groups

		if ($use_multilevel <= 0) {
			print '<select id="select'.$htmlname.'" class="flat minwidth100'.($morecss ? ' '.$morecss : '').'" name="'.$htmlname.'">';
			if ($empty) {
				print '<option value="">&nbsp;</option>';
			}

			if (is_array($ticketstat->cache_category_tickets) && count($ticketstat->cache_category_tickets)) {
				foreach ($ticketstat->cache_category_tickets as $id => $arraycategories) {
					// Exclude some record
					if ($publicgroups) {
						if (empty($arraycategories['public'])) {
							continue;
						}
					}

					// We discard empty line if showempty is on because an empty line has already been output.
					if ($empty && empty($arraycategories['code'])) {
						continue;
					}

					$label = ($arraycategories['label'] != '-' ? $arraycategories['label'] : '');
					if ($outputlangs->trans("TicketCategoryShort".$arraycategories['code']) != "TicketCategoryShort".$arraycategories['code']) {
						$label = $outputlangs->trans("TicketCategoryShort".$arraycategories['code']);
					} elseif ($outputlangs->trans($arraycategories['code']) != $arraycategories['code']) {
						$label = $outputlangs->trans($arraycategories['code']);
					}

					if ($format == 0) {
						print '<option value="'.$id.'"';
					}

					if ($format == 1) {
						print '<option value="'.$arraycategories['code'].'"';
					}

					if ($format == 2) {
						print '<option value="'.$arraycategories['code'].'"';
					}

					if ($format == 3) {
						print '<option value="'.$id.'"';
					}

					// If selected is text, we compare with code, otherwise with id
					if (isset($selected) && preg_match('/[a-z]/i', $selected) && $selected == $arraycategories['code']) {
						print ' selected="selected"';
					} elseif (isset($selected) && $selected == $id) {
						print ' selected="selected"';
					} elseif ($arraycategories['use_default'] == "1" && !$selected && !$empty) {
						print ' selected="selected"';
					} elseif (count($ticketstat->cache_category_tickets) == 1) {
						print ' selected="selected"';
					}

					print '>';

					$value = '';
					if ($format == 0) {
						$value = ($maxlength ? dol_trunc($label, $maxlength) : $label);
					}

					if ($format == 1) {
						$value = $arraycategories['code'];
					}

					if ($format == 2) {
						$value = ($maxlength ? dol_trunc($label, $maxlength) : $label);
					}

					if ($format == 3) {
						$value = $arraycategories['code'];
					}

					print $value ? $value : '&nbsp;';
					print '</option>';
				}
			}
			print '</select>';
			if (isset($user->admin) && $user->admin && !$noadmininfo) {
				print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
			}

			print ajax_combobox('select'.$htmlname);
		} elseif ($htmlname != '') {
			$selectedgroups = array();
			$groupvalue = "";
			$groupticket=GETPOST($htmlname, 'aZ09');
			$child_id=GETPOST($htmlname.'_child_id', 'aZ09') ? GETPOST($htmlname.'_child_id', 'aZ09') : 0;
			if (!empty($groupticket)) {
				$tmpgroupticket = $groupticket;
				$sql = "SELECT ctc.rowid, ctc.fk_parent, ctc.code";
				$sql .= " FROM ".$this->db->prefix()."c_ticket_category as ctc WHERE ctc.code = '".$this->db->escape($tmpgroupticket)."'";
				$resql = $this->db->query($sql);
				if ($resql) {
					$obj = $this->db->fetch_object($resql);
					$selectedgroups[] = $obj->code;
					while ($obj->fk_parent > 0) {
						$sql = "SELECT ctc.rowid, ctc.fk_parent, ctc.code FROM ".$this->db->prefix()."c_ticket_category as ctc WHERE ctc.rowid ='".$this->db->escape($obj->fk_parent)."'";
						$resql = $this->db->query($sql);
						if ($resql) {
							$obj = $this->db->fetch_object($resql);
							$selectedgroups[] = $obj->code;
						}
					}
				}
			}

			$arrayidused = array();
			$arrayidusedconcat = array();
			$arraycodenotparent = array();
			$arraycodenotparent[] = "";

			$stringtoprint = '<span class="supportemailfield bold">'.$langs->trans("GroupOfTicket").'</span> ';
			$stringtoprint .= '<select id="'.$htmlname.'" class="minwidth500" child_id="0">';
			$stringtoprint .= '<option value="">&nbsp;</option>';

			$sql = "SELECT ctc.rowid, ctc.code, ctc.label, ctc.fk_parent, ctc.public, ";
			$sql .= $this->db->ifsql("ctc.rowid NOT IN (SELECT ctcfather.rowid FROM llx_c_ticket_category as ctcfather JOIN llx_c_ticket_category as ctcjoin ON ctcfather.rowid = ctcjoin.fk_parent)", "'NOTPARENT'", "'PARENT'")." as isparent";
			$sql .= " FROM ".$this->db->prefix()."c_ticket_category as ctc";
			$sql .= " WHERE ctc.active > 0 AND ctc.entity = ".((int) $conf->entity);
			if ($filtertype == 'public=1') {
				$sql .= " AND ctc.public = 1";
			}
			$sql .= " AND ctc.fk_parent = 0";
			$sql .= $this->db->order('ctc.pos', 'ASC');

			$resql = $this->db->query($sql);
			if ($resql) {
				$num_rows_level0 = $this->db->num_rows($resql);
				$i = 0;
				while ($i < $num_rows_level0) {
					$obj = $this->db->fetch_object($resql);
					if ($obj) {
						$label = ($obj->label != '-' ? $obj->label : '');
						if ($outputlangs->trans("TicketCategoryShort".$obj->code) != "TicketCategoryShort".$obj->code) {
							$label = $outputlangs->trans("TicketCategoryShort".$obj->code);
						} elseif ($outputlangs->trans($obj->code) != $obj->code) {
							$label = $outputlangs->trans($obj->code);
						}

						$grouprowid = $obj->rowid;
						$groupvalue = $obj->code;
						$grouplabel = $label;

						$isparent = $obj->isparent;
						if (is_array($selectedgroups)) {
							$iselected = in_array($obj->code, $selectedgroups) ? 'selected' : '';
						} else {
							$iselected = $groupticket == $obj->code ? 'selected' : '';
						}
						$stringtoprint .= '<option '.$iselected.' class="'.$htmlname.dol_escape_htmltag($grouprowid).'" value="'.dol_escape_htmltag($groupvalue).'" data-html="'.dol_escape_htmltag($grouplabel).'">'.dol_escape_htmltag($grouplabel).'</option>';
						if ($isparent == 'NOTPARENT') {
							$arraycodenotparent[] = $groupvalue;
						}
						$arrayidused[] = $grouprowid;
						$arrayidusedconcat[] = $grouprowid;
					}
					$i++;
				}
			} else {
				dol_print_error($this->db);
			}
			if (count($arrayidused) == 1) {
				return '<input type="hidden" name="'.$htmlname.'" id="'.$htmlname.'" value="'.dol_escape_htmltag($groupvalue).'">';
			} else {
				$stringtoprint .= '<input type="hidden" name="'.$htmlname.'" id="'.$htmlname.'_select" class="maxwidth500 minwidth400">';
				$stringtoprint .= '<input type="hidden" name="'.$htmlname.'_child_id" id="'.$htmlname.'_select_child_id" class="maxwidth500 minwidth400">';
			}
			$stringtoprint .= '</select>&nbsp;';

			$levelid = 1;	// The first combobox
			while ($levelid <= $use_multilevel) {	// Loop to take the child of the combo
				$tabscript = array();
				$stringtoprint .= '<select id="'.$htmlname.'_child_'.$levelid.'" class="maxwidth500 minwidth400 groupticketchild" child_id="'.$levelid.'">';
				$stringtoprint .= '<option value="">&nbsp;</option>';

				$sql = "SELECT ctc.rowid, ctc.code, ctc.label, ctc.fk_parent, ctc.public, ctcjoin.code as codefather";
				$sql .= " FROM ".$this->db->prefix()."c_ticket_category as ctc";
				$sql .= " JOIN ".$this->db->prefix()."c_ticket_category as ctcjoin ON ctc.fk_parent = ctcjoin.rowid";
				$sql .= " WHERE ctc.active > 0 AND ctc.entity = ".((int) $conf->entity);
				$sql .= " AND ctc.rowid NOT IN (".$this->db->sanitize(join(',', $arrayidusedconcat)).")";

				if ($filtertype == 'public=1') {
					$sql .= " AND ctc.public = 1";
				}
				// Add a test to take only record that are direct child
				if (!empty($arrayidused)) {
					$sql .= " AND ctc.fk_parent IN ( ";
					foreach ($arrayidused as $idused) {
						$sql .= $idused.", ";
					}
					$sql = substr($sql, 0, -2);
					$sql .= ")";
				}
				$sql .= $this->db->order('ctc.pos', 'ASC');

				$resql = $this->db->query($sql);
				if ($resql) {
					$num_rows = $this->db->num_rows($resql);
					$i = 0;
					$arrayidused=array();
					while ($i < $num_rows) {
						$obj = $this->db->fetch_object($resql);
						if ($obj) {
							$label = ($obj->label != '-' ? $obj->label : '');
							if ($outputlangs->trans("TicketCategoryShort".$obj->code) != "TicketCategoryShort".$obj->code) {
								$label = $outputlangs->trans("TicketCategoryShort".$obj->code);
							} elseif ($outputlangs->trans($obj->code) != $obj->code) {
								$label = $outputlangs->trans($obj->code);
							}

							$grouprowid = $obj->rowid;
							$groupvalue = $obj->code;
							$grouplabel = $label;
							$isparent = $obj->isparent;
							$fatherid = $obj->fk_parent;
							$arrayidused[] = $grouprowid;
							$arrayidusedconcat[] = $grouprowid;
							$groupcodefather = $obj->codefather;
							if ($isparent == 'NOTPARENT') {
								$arraycodenotparent[] = $groupvalue;
							}
							if (is_array($selectedgroups)) {
								$iselected = in_array($obj->code, $selectedgroups) ? 'selected' : '';
							} else {
								$iselected = $groupticket == $obj->code ? 'selected' : '';
							}
							$stringtoprint .= '<option '.$iselected.' class="'.$htmlname.'_'.dol_escape_htmltag($fatherid).'_child_'.$levelid.'" value="'.dol_escape_htmltag($groupvalue).'" data-html="'.dol_escape_htmltag($grouplabel).'">'.dol_escape_htmltag($grouplabel).'</option>';
							if (empty($tabscript[$groupcodefather])) {
								$tabscript[$groupcodefather] = 'if ($("#'.$htmlname.($levelid > 1 ? '_child_'.($levelid-1) : '').'").val() == "'.dol_escape_js($groupcodefather).'"){
									$(".'.$htmlname.'_'.dol_escape_htmltag($fatherid).'_child_'.$levelid.'").show()
									console.log("We show childs tickets of '.$groupcodefather.' group ticket")
								}else{
									$(".'.$htmlname.'_'.dol_escape_htmltag($fatherid).'_child_'.$levelid.'").hide()
									console.log("We hide childs tickets of '.$groupcodefather.' group ticket")
								}';
							}
						}
						$i++;
					}
				} else {
					dol_print_error($this->db);
				}
				$stringtoprint .='</select>';

				$stringtoprint .='<script nonce="'.getNonce().'">';
				$stringtoprint .='arraynotparents = '.json_encode($arraycodenotparent).';';	// when the last visible combo list is number x, this is the array of group
				$stringtoprint .='if (arraynotparents.includes($("#'.$htmlname.($levelid > 1 ? '_child_'.($levelid-1) : '').'").val())){
					console.log("'.$htmlname.'_child_'.$levelid.'")
					if($("#'.$htmlname.'_child_'.$levelid.'").val() == "" && ($("#'.$htmlname.'_child_'.$levelid.'").attr("child_id")>'.$child_id.')){
						$("#'.$htmlname.'_child_'.$levelid.'").hide();
						console.log("We hide '.$htmlname.'_child_'.$levelid.' input")
					}
					if(arraynotparents.includes("'.$groupticket.'") && '.$child_id.' == 0){
						$("#ticketcategory_select_child_id").val($("#'.$htmlname.'").attr("child_id"))
						$("#ticketcategory_select").val($("#'.$htmlname.'").val()) ;
						console.log("We choose '.$htmlname.' input and reload hidden input");
					}
				}
				$("#'.$htmlname.($levelid > 1 ? '_child_'.($levelid-1) : '').'").change(function() {
					child_id = $("#'.$htmlname.($levelid > 1 ? '_child_'.$levelid : '').'").attr("child_id");

					/* Change of value to select this value*/
					if (arraynotparents.includes($(this).val()) || $(this).attr("child_id") == '.$use_multilevel.') {
						$("#ticketcategory_select").val($(this).val());
						$("#ticketcategory_select_child_id").val($(this).attr("child_id")) ;
						console.log("We choose to select "+ $(this).val());
					}else{
						if ($("#'.$htmlname.'_child_'.$levelid.' option").length <= 1) {
							$("#ticketcategory_select").val($(this).val());
							$("#ticketcategory_select_child_id").val($(this).attr("child_id"));
							console.log("We choose to select "+ $(this).val() + " and next combo has no item, so we keep this selection");
						} else {
							console.log("We choose to select "+ $(this).val() + " but next combo has some item, so we clean selected item");
							$("#ticketcategory_select").val("");
							$("#ticketcategory_select_child_id").val("");
						}
					}

					console.log("We select a new value into combo child_id="+child_id);

					// Hide all selected box that are child of the one modified
					$(".groupticketchild").each(function(){
						if ($(this).attr("child_id") > child_id) {
							console.log("hide child_id="+$(this).attr("child_id"));
							$(this).val("");
							$(this).hide();
						}
					})

					// Now we enable the next combo
					$("#'.$htmlname.'_child_'.$levelid.'").val("");
					if (!arraynotparents.includes($(this).val()) && $("#'.$htmlname.'_child_'.$levelid.' option").length > 1) {
						console.log($("#'.$htmlname.'_child_'.$levelid.' option").length);
						$("#'.$htmlname.'_child_'.$levelid.'").show()
					} else {
						$("#'.$htmlname.'_child_'.$levelid.'").hide()
					}
				';
				$levelid++;
				foreach ($tabscript as $script) {
					$stringtoprint .= $script;
				}
				$stringtoprint .='})';
				$stringtoprint .='</script>';
			}
			$stringtoprint .='<script nonce="'.getNonce().'">';
			$stringtoprint .='$("#'.$htmlname.'_child_'.$use_multilevel.'").change(function() {
				$("#ticketcategory_select").val($(this).val());
				$("#ticketcategory_select_child_id").val($(this).attr("child_id"));
				console.log($("#ticketcategory_select").val());
			})';
			$stringtoprint .='</script>';
			$stringtoprint .= ajax_combobox($htmlname);

			return $stringtoprint;
		}
	}

	/**
	 *      Return html list of ticket severitys (priorities)
	 *
	 *      @param  string  $selected    Id severity pre-selected
	 *      @param  string  $htmlname    Name of the select area
	 *      @param  string  $filtertype  To filter on field type in llx_c_ticket_severity (array('code'=>xx,'label'=>zz))
	 *      @param  int     $format      0 = id+label, 1 = code+code, 2 = code+label, 3 = id+code
	 *      @param  int     $empty       1 = can be empty, 0 = or not
	 *      @param  int     $noadmininfo 0 = add admin info, 1 = disable admin info
	 *      @param  int     $maxlength   Max length of label
	 *      @param  string  $morecss     More CSS
	 *      @return void
	 */
	public function selectSeveritiesTickets($selected = '', $htmlname = 'ticketseverity', $filtertype = '', $format = 0, $empty = 0, $noadmininfo = 0, $maxlength = 0, $morecss = '')
	{
		global $langs, $user;

		$ticketstat = new Ticket($this->db);

		dol_syslog(get_class($this)."::selectSeveritiesTickets ".$selected.", ".$htmlname.", ".$filtertype.", ".$format, LOG_DEBUG);

		$filterarray = array();

		if ($filtertype != '' && $filtertype != '-1') {
			$filterarray = explode(',', $filtertype);
		}

		$ticketstat->loadCacheSeveritiesTickets();

		print '<select id="select'.$htmlname.'" class="flat minwidth100'.($morecss ? ' '.$morecss : '').'" name="'.$htmlname.'">';
		if ($empty) {
			print '<option value="">&nbsp;</option>';
		}

		if (is_array($ticketstat->cache_severity_tickets) && count($ticketstat->cache_severity_tickets)) {
			foreach ($ticketstat->cache_severity_tickets as $id => $arrayseverities) {
				// On passe si on a demande de filtrer sur des modes de paiments particuliers
				if (count($filterarray) && !in_array($arrayseverities['type'], $filterarray)) {
					continue;
				}

				// We discard empty line if showempty is on because an empty line has already been output.
				if ($empty && empty($arrayseverities['code'])) {
					continue;
				}

				if ($format == 0) {
					print '<option value="'.$id.'"';
				}

				if ($format == 1) {
					print '<option value="'.$arrayseverities['code'].'"';
				}

				if ($format == 2) {
					print '<option value="'.$arrayseverities['code'].'"';
				}

				if ($format == 3) {
					print '<option value="'.$id.'"';
				}
				// If text is selected, we compare with code, otherwise with id
				if (preg_match('/[a-z]/i', $selected) && $selected == $arrayseverities['code']) {
					print ' selected="selected"';
				} elseif ($selected == $id) {
					print ' selected="selected"';
				} elseif ($arrayseverities['use_default'] == "1" && !$selected && !$empty) {
					print ' selected="selected"';
				}

				print '>';

				$value = '';
				if ($format == 0) {
					$value = ($maxlength ? dol_trunc($arrayseverities['code'], $maxlength) : $arrayseverities['code']);
				}

				if ($format == 1) {
					$value = $arrayseverities['code'];
				}

				if ($format == 2) {
					$value = ($maxlength ? dol_trunc($arrayseverities['code'], $maxlength) : $arrayseverities['code']);
				}

				if ($format == 3) {
					$value = $arrayseverities['code'];
				}

				print $value ? $value : '&nbsp;';
				print '</option>';
			}
		}
		print '</select>';
		if (isset($user->admin) && $user->admin && !$noadmininfo) {
			print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
		}

		print ajax_combobox('select'.$htmlname);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 * Clear list of attached files in send mail form (also stored in session)
	 *
	 * @return	void
	 */
	public function clear_attached_files()
	{
		// phpcs:enable
		global $conf, $user;
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		// Set tmp user directory
		$vardir = $conf->user->dir_output."/".$user->id;
		$upload_dir = $vardir.'/temp/'; // TODO Add $keytoavoidconflict in upload_dir path
		if (is_dir($upload_dir)) {
			dol_delete_dir_recursive($upload_dir);
		}

		if (!empty($this->trackid)) { // TODO Always use trackid (ticXXX) instead of track_id (abcd123)
			$keytoavoidconflict = '-'.$this->trackid;
		} else {
			$keytoavoidconflict = empty($this->track_id) ? '' : '-'.$this->track_id;
		}
		unset($_SESSION["listofpaths".$keytoavoidconflict]);
		unset($_SESSION["listofnames".$keytoavoidconflict]);
		unset($_SESSION["listofmimes".$keytoavoidconflict]);
	}

	/**
	 * Show the form to add message on ticket
	 *
	 * @param  	string  $width      	Width of form
	 * @return 	void
	 */
	public function showMessageForm($width = '40%')
	{
		global $conf, $langs, $user, $hookmanager, $form, $mysoc;

		$formmail = new FormMail($this->db);
		$addfileaction = 'addfile';

		if (!is_object($form)) {
			$form = new Form($this->db);
		}

		// Load translation files required by the page
		$langs->loadLangs(array('other', 'mails', 'ticket'));

		// Clear temp files. Must be done at beginning, before call of triggers
		if (GETPOST('mode', 'alpha') == 'init' || (GETPOST('modelselected') && GETPOST('modelmailselected', 'alpha') && GETPOST('modelmailselected', 'alpha') != '-1')) {
			$this->clear_attached_files();
		}

		// Define output language
		$outputlangs = $langs;
		$newlang = '';
		if (getDolGlobalInt('MAIN_MULTILANGS') && empty($newlang) && isset($this->param['langsmodels'])) {
			$newlang = $this->param['langsmodels'];
		}
		if (!empty($newlang)) {
			$outputlangs = new Translate("", $conf);
			$outputlangs->setDefaultLang($newlang);
			$outputlangs->load('other');
		}

		// Get message template for $this->param["models"] into c_email_templates
		$arraydefaultmessage = -1;
		if (isset($this->param['models']) && $this->param['models'] != 'none') {
			$model_id = 0;
			if (array_key_exists('models_id', $this->param)) {
				$model_id = (int) $this->param["models_id"];
			}

			$arraydefaultmessage = $formmail->getEMailTemplate($this->db, $this->param["models"], $user, $outputlangs, $model_id); // If $model_id is empty, preselect the first one
		}

		// Define list of attached files
		$listofpaths = array();
		$listofnames = array();
		$listofmimes = array();

		if (!empty($this->trackid)) {
			$keytoavoidconflict = '-'.$this->trackid;
		} else {
			$keytoavoidconflict = empty($this->track_id) ? '' : '-'.$this->track_id; // track_id instead of trackid
		}
		//var_dump($keytoavoidconflict);
		if (GETPOST('mode', 'alpha') == 'init' || (GETPOST('modelselected') && GETPOST('modelmailselected', 'alpha') && GETPOST('modelmailselected', 'alpha') != '-1')) {
			if (!empty($arraydefaultmessage->joinfiles) && !empty($this->param['fileinit']) && is_array($this->param['fileinit'])) {
				foreach ($this->param['fileinit'] as $file) {
					$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
				}
			}
		}
		//var_dump($_SESSION);
		//var_dump($_SESSION["listofpaths".$keytoavoidconflict]);
		if (!empty($_SESSION["listofpaths".$keytoavoidconflict])) {
			$listofpaths = explode(';', $_SESSION["listofpaths".$keytoavoidconflict]);
		}
		if (!empty($_SESSION["listofnames".$keytoavoidconflict])) {
			$listofnames = explode(';', $_SESSION["listofnames".$keytoavoidconflict]);
		}
		if (!empty($_SESSION["listofmimes".$keytoavoidconflict])) {
			$listofmimes = explode(';', $_SESSION["listofmimes".$keytoavoidconflict]);
		}

		// Define output language
		$outputlangs = $langs;
		$newlang = '';
		if (getDolGlobalInt('MAIN_MULTILANGS') && empty($newlang) && isset($this->param['langsmodels'])) {
			$newlang = $this->param['langsmodels'];
		}
		if (!empty($newlang)) {
			$outputlangs = new Translate("", $conf);
			$outputlangs->setDefaultLang($newlang);
			$outputlangs->load('other');
		}

		print "\n<!-- Begin message_form TICKET -->\n";

		$send_email = GETPOST('send_email', 'int') ? GETPOST('send_email', 'int') : 0;

		// Example 1 : Adding jquery code
		print '<script nonce="'.getNonce().'" type="text/javascript">
		jQuery(document).ready(function() {
			send_email=' . $send_email.';
			if (send_email) {
				if (!jQuery("#send_msg_email").is(":checked")) {
					jQuery("#send_msg_email").prop("checked", true).trigger("change");
				}
				jQuery(".email_line").show();
			} else {
				if (!jQuery("#private_message").is(":checked")) {
					jQuery("#private_message").prop("checked", true).trigger("change");
				}
				jQuery(".email_line").hide();
			}
		';

		// If constant set, allow to send private messages as email
		if (!getDolGlobalString('TICKET_SEND_PRIVATE_EMAIL')) {
			print 'jQuery("#send_msg_email").click(function() {
					console.log("Click send_msg_email");
					if(jQuery(this).is(":checked")) {
						if (jQuery("#private_message").is(":checked")) {
							jQuery("#private_message").prop("checked", false).trigger("change");
						}
						jQuery(".email_line").show();
					}
					else {
						jQuery(".email_line").hide();
					}
				});

				jQuery("#private_message").click(function() {
					console.log("Click private_message");
					if (jQuery(this).is(":checked")) {
						if (jQuery("#send_msg_email").is(":checked")) {
							jQuery("#send_msg_email").prop("checked", false).trigger("change");
						}
						jQuery(".email_line").hide();
					}
				});';
		}

		print '});
		</script>';


		print '<form method="post" name="ticket" id="ticket" enctype="multipart/form-data" action="'.$this->param["returnurl"].'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="'.$this->action.'">';
		print '<input type="hidden" name="actionbis" value="add_message">';
		print '<input type="hidden" name="backtopage" value="'.$this->backtopage.'">';
		if (!empty($this->trackid)) {
			print '<input type="hidden" name="trackid" value="'.$this->trackid.'">';
		} else {
			print '<input type="hidden" name="trackid" value="'.(empty($this->track_id) ? '' : $this->track_id).'">';
			$keytoavoidconflict = empty($this->track_id) ? '' : '-'.$this->track_id; // track_id instead of trackid
		}
		foreach ($this->param as $key => $value) {
			print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
		}

		// Get message template
		$model_id = 0;
		if (array_key_exists('models_id', $this->param)) {
			$model_id = $this->param["models_id"];
			$arraydefaultmessage = $formmail->getEMailTemplate($this->db, $this->param["models"], $user, $outputlangs, $model_id);
		}

		$result = $formmail->fetchAllEMailTemplate(!empty($this->param["models"]) ? $this->param["models"] : "", $user, $outputlangs);
		if ($result < 0) {
			setEventMessages($this->error, $this->errors, 'errors');
		}
		$modelmail_array = array();
		foreach ($formmail->lines_model as $line) {
			$modelmail_array[$line->id] = $line->label;
		}

		print '<table class="border" width="'.$width.'">';

		// External users can't send message email
		if ($user->hasRight("ticket", "write") && !$user->socid) {
			$ticketstat = new Ticket($this->db);
			$res = $ticketstat->fetch('', '', $this->track_id);

			print '<tr><td></td><td>';
			$checkbox_selected = (GETPOST('send_email') == "1" ? ' checked' : (getDolGlobalInt('TICKETS_MESSAGE_FORCE_MAIL') ? 'checked' : ''));
			print '<input type="checkbox" name="send_email" value="1" id="send_msg_email" '.$checkbox_selected.'/> ';
			print '<label for="send_msg_email">'.$langs->trans('SendMessageByEmail').'</label>';
			$texttooltip = $langs->trans("TicketMessageSendEmailHelp");
			if (!getDolGlobalString('TICKET_SEND_PRIVATE_EMAIL')) {
				$texttooltip .= ' '.$langs->trans("TicketMessageSendEmailHelp2b");
			} else {
				$texttooltip .= ' '.$langs->trans("TicketMessageSendEmailHelp2a", '{s1}');
			}
			$texttooltip = str_replace('{s1}', $langs->trans('MarkMessageAsPrivate'), $texttooltip);
			print ' '.$form->textwithpicto('', $texttooltip, 1, 'help');
			print '</td></tr>';

			// Private message (not visible by customer/external user)
			if (!$user->socid) {
				print '<tr><td></td><td>';
				$checkbox_selected = (GETPOST('private_message', 'alpha') == "1" ? ' checked' : '');
				print '<input type="checkbox" name="private_message" value="1" id="private_message" '.$checkbox_selected.'/> ';
				print '<label for="private_message">'.$langs->trans('MarkMessageAsPrivate').'</label>';
				print ' '.$form->textwithpicto('', $langs->trans("TicketMessagePrivateHelp"), 1, 'help');
				print '</td></tr>';
			}

			// Zone to select its email template
			if (count($modelmail_array) > 0) {
				print '<tr class="email_line"><td></td><td colspan="2"><div style="padding: 3px 0 3px 0">'."\n";
				print $langs->trans('SelectMailModel').': '.$formmail->selectarray('modelmailselected', $modelmail_array, $this->param['models_id'], 1, 0, "", "", 0, 0, 0, '', 'minwidth200');
				if ($user->admin) {
					print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
				}
				print ' &nbsp; ';
				print '<input type="submit" class="button" value="'.$langs->trans('Apply').'" name="modelselected" id="modelselected">';
				print '</div></td>';
			}
			// Subject/topic
			$topic = "";
			foreach ($formmail->lines_model as $line) {
				if ($this->param['models_id'] == $line->id) {
					$topic = $line->topic;
					break;
				}
			}
			print '<tr class="email_line"><td>'.$langs->trans('Subject').'</td>';
			if (empty($topic)) {
				print '<td><input type="text" class="text minwidth500" name="subject" value="['.getDolGlobalString('MAIN_INFO_SOCIETE_NOM').' - '.$langs->trans("Ticket").' '.$ticketstat->ref.'] '.$langs->trans('TicketNewMessage').'" />';
			} else {
				print '<td><input type="text" class="text minwidth500" name="subject" value="['.getDolGlobalString('MAIN_INFO_SOCIETE_NOM').' - '.$langs->trans("Ticket").' '.$ticketstat->ref.'] '.$topic.'" />';
			}
			print '</td></tr>';

			// Recipients / adressed-to
			print '<tr class="email_line"><td>'.$langs->trans('MailRecipients');
			print ' '.$form->textwithpicto('', $langs->trans("TicketMessageRecipientsHelp"), 1, 'help');
			print '</td><td>';
			if ($res) {
				// Retrieve email of all contacts (internal and external)
				$contacts = $ticketstat->getInfosTicketInternalContact(1);
				$contacts = array_merge($contacts, $ticketstat->getInfosTicketExternalContact(1));

				$sendto = array();

				// Build array to display recipient list
				if (is_array($contacts) && count($contacts) > 0) {
					foreach ($contacts as $key => $info_sendto) {
						if ($info_sendto['email'] != '') {
							$sendto[] = dol_escape_htmltag(trim($info_sendto['firstname']." ".$info_sendto['lastname'])." <".$info_sendto['email'].">").' <small class="opacitymedium">('.dol_escape_htmltag($info_sendto['libelle']).")</small>";
						}
					}
				}

				if ($ticketstat->origin_email && !in_array($ticketstat->origin_email, $sendto)) {
					$sendto[] = dol_escape_htmltag($ticketstat->origin_email).' <small class="opacitymedium">('.$langs->trans("TicketEmailOriginIssuer").")</small>";
				}

				if ($ticketstat->fk_soc > 0) {
					$ticketstat->socid = $ticketstat->fk_soc;
					$ticketstat->fetch_thirdparty();

					if (!empty($ticketstat->thirdparty->email) && !in_array($ticketstat->thirdparty->email, $sendto)) {
						$sendto[] = $ticketstat->thirdparty->email.' <small class="opacitymedium">('.$langs->trans('Customer').')</small>';
					}
				}

				if (getDolGlobalInt('TICKET_NOTIFICATION_ALSO_MAIN_ADDRESS')) {
					$sendto[] = getDolGlobalString('TICKET_NOTIFICATION_EMAIL_TO').' <small class="opacitymedium">(generic email)</small>';
				}

				// Print recipient list
				if (is_array($sendto) && count($sendto) > 0) {
					print img_picto('', 'email', 'class="pictofixedwidth"');
					print implode(', ', $sendto);
				} else {
					print '<div class="warning">'.$langs->trans('WarningNoEMailsAdded').' '.$langs->trans('TicketGoIntoContactTab').'</div>';
				}
			}
			print '</td></tr>';
		}

		$uselocalbrowser = false;

		// Intro
		// External users can't send message email
		/*
		if ($user->rights->ticket->write && !$user->socid && !empty($conf->global->TICKET_MESSAGE_MAIL_INTRO)) {
			$mail_intro = GETPOST('mail_intro') ? GETPOST('mail_intro') : $conf->global->TICKET_MESSAGE_MAIL_INTRO;
			print '<tr class="email_line"><td><label for="mail_intro">';
			print $form->textwithpicto($langs->trans("TicketMessageMailIntro"), $langs->trans("TicketMessageMailIntroHelp"), 1, 'help');
			print '</label>';

			print '</td><td>';
			include_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

			$doleditor = new DolEditor('mail_intro', $mail_intro, '100%', 90, 'dolibarr_details', '', false, $uselocalbrowser, getDolGlobalInt('FCKEDITOR_ENABLE_TICKET'), ROWS_2, 70);

			$doleditor->Create();
			print '</td></tr>';
		}
		*/

		// Attached files
		if (!empty($this->withfile)) {
			$out = '<tr>';
			$out .= '<td>'.$langs->trans("MailFile").'</td>';
			$out .= '<td>';
			// TODO Trick to have param removedfile containing nb of image to delete. But this does not works without javascript
			$out .= '<input type="hidden" class="removedfilehidden" name="removedfile" value="">'."\n";
			$out .= '<script nonce="'.getNonce().'" type="text/javascript">';
			$out .= 'jQuery(document).ready(function () {';
			$out .= '    jQuery("#'.$addfileaction.'").prop("disabled", true);';
			$out .= '    jQuery("#addedfile").on("change", function() {';
			$out .= '        if (jQuery(this).val().length) {';
			$out .= '            jQuery("#'.$addfileaction.'").prop("disabled", false);';
			$out .= '        } else {';
			$out .= '            jQuery("#'.$addfileaction.'").prop("disabled", true);';
			$out .= '        }';
			$out .= '    });';
			$out .= '    jQuery(".removedfile").click(function() {';
			$out .= '        jQuery(".removedfilehidden").val(jQuery(this).val());';
			$out .= '    });';
			$out .= '})';
			$out .= '</script>'."\n";

			if (count($listofpaths)) {
				foreach ($listofpaths as $key => $val) {
					$out .= '<div id="attachfile_'.$key.'">';
					$out .= img_mime($listofnames[$key]).' '.$listofnames[$key];
					if (!$this->withfilereadonly) {
						$out .= ' <input type="image" style="border: 0px;" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/delete.png" value="'.($key + 1).'" class="removedfile reposition" id="removedfile_'.$key.'" name="removedfile_'.$key.'" />';
					}
					$out .= '<br></div>';
				}
			} else {
				//$out .= $langs->trans("NoAttachedFiles").'<br>';
			}
			if ($this->withfile == 2) { // Can add other files
				$out .= '<input type="file" class="flat" id="addedfile" name="addedfile" value="'.$langs->trans("Upload").'" />';
				$out .= ' ';
				$out .= '<input type="submit" class="button smallpaddingimp reposition" id="'.$addfileaction.'" name="'.$addfileaction.'" value="'.$langs->trans("MailingAddFile").'" />';
			}
			$out .= "</td></tr>\n";

			print $out;
		}
		// MESSAGE

		$defaultmessage = "";
		if (is_object($arraydefaultmessage) && $arraydefaultmessage->content) {
			$defaultmessage = $arraydefaultmessage->content;
		}
		$defaultmessage = str_replace('\n', "\n", $defaultmessage);

		// Deal with format differences between message and signature (text / HTML)
		if (dol_textishtml($defaultmessage) && !dol_textishtml($this->substit['__USER_SIGNATURE__'])) {
			$this->substit['__USER_SIGNATURE__'] = dol_nl2br($this->substit['__USER_SIGNATURE__']);
		} elseif (!dol_textishtml($defaultmessage) && isset($this->substit['__USER_SIGNATURE__']) && dol_textishtml($this->substit['__USER_SIGNATURE__'])) {
			$defaultmessage = dol_nl2br($defaultmessage);
		}
		if (GETPOSTISSET("message") && !GETPOST('modelselected')) {
			$defaultmessage = GETPOST('message', 'restricthtml');
		} else {
			$defaultmessage = make_substitutions($defaultmessage, $this->substit);
			// Clean first \n and br (to avoid empty line when CONTACTCIVNAME is empty)
			$defaultmessage = preg_replace("/^(<br>)+/", "", $defaultmessage);
			$defaultmessage = preg_replace("/^\n+/", "", $defaultmessage);
		}

		print '<tr><td colspan="2"><label for="message"><span class="fieldrequired">'.$langs->trans("Message").'</span>';
		if ($user->hasRight("ticket", "write") && !$user->socid) {
			$texttooltip = $langs->trans("TicketMessageHelp");
			if (getDolGlobalString('TICKET_MESSAGE_MAIL_INTRO') || getDolGlobalString('TICKET_MESSAGE_MAIL_SIGNATURE')) {
				$texttooltip .= '<br><br>'.$langs->trans("ForEmailMessageWillBeCompletedWith").'...';
			}
			if (getDolGlobalString('TICKET_MESSAGE_MAIL_INTRO')) {
				$texttooltip .= '<br><u>'.$langs->trans("TicketMessageMailIntro").'</u><br>'.getDolGlobalString('TICKET_MESSAGE_MAIL_INTRO');
			}
			if (getDolGlobalString('TICKET_MESSAGE_MAIL_SIGNATURE')) {
				$texttooltip .= '<br><br><u>'.$langs->trans("TicketMessageMailFooter").'</u><br>'.getDolGlobalString('TICKET_MESSAGE_MAIL_SIGNATURE');
			}
			print $form->textwithpicto('', $texttooltip, 1, 'help');
		}
		print '</label></td></tr>';


		print '<tr><td colspan="2">';
		//$toolbarname = 'dolibarr_details';
		$toolbarname = 'dolibarr_notes';
		include_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
		$doleditor = new DolEditor('message', $defaultmessage, '100%', 200, $toolbarname, '', false, $uselocalbrowser, getDolGlobalInt('FCKEDITOR_ENABLE_TICKET'), ROWS_5, '90%');
		$doleditor->Create();
		print '</td></tr>';

		// Footer
		// External users can't send message email
		/*if ($user->rights->ticket->write && !$user->socid && !empty($conf->global->TICKET_MESSAGE_MAIL_SIGNATURE)) {
			$mail_signature = GETPOST('mail_signature') ? GETPOST('mail_signature') : $conf->global->TICKET_MESSAGE_MAIL_SIGNATURE;
			print '<tr class="email_line"><td><label for="mail_intro">'.$langs->trans("TicketMessageMailFooter").'</label>';
			print $form->textwithpicto('', $langs->trans("TicketMessageMailFooterHelp"), 1, 'help');
			print '</td><td>';
			include_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
			$doleditor = new DolEditor('mail_signature', $mail_signature, '100%', 90, 'dolibarr_details', '', false, $uselocalbrowser, getDolGlobalInt('FCKEDITOR_ENABLE_SOCIETE'), ROWS_2, 70);
			$doleditor->Create();
			print '</td></tr>';
		}
		*/

		print '</table>';

		print '<br><center>';
		print '<input type="submit" class="button" name="btn_add_message" value="'.$langs->trans("Add").'"';
		// Add a javascript test to avoid to forget to submit file before sending email
		if ($this->withfile == 2 && !empty($conf->use_javascript_ajax)) {
			print ' onClick="if (document.ticket.addedfile.value != \'\') { alert(\''.dol_escape_js($langs->trans("FileWasNotUploaded")).'\'); return false; } else { return true; }"';
		}
		print ' />';
		if (!empty($this->withcancel)) {
			print " &nbsp; &nbsp; ";
			print '<input class="button button-cancel" type="submit" name="cancel" value="'.$langs->trans("Cancel").'">';
		}
		print "</center>\n";

		print '<input type="hidden" name="page_y">'."\n";

		print "</form><br>\n";

		// Disable enter key if option MAIN_MAILFORM_DISABLE_ENTERKEY is set
		if (getDolGlobalString('MAIN_MAILFORM_DISABLE_ENTERKEY')) {
			print '<script type="text/javascript">';
			print 'jQuery(document).ready(function () {';
			print '		$(document).on("keypress", \'#ticket\', function (e) {		/* Note this is called at every key pressed ! */
	    					var code = e.keyCode || e.which;
	    					if (code == 13) {
								console.log("Enter was intercepted and blocked");
	        					e.preventDefault();
	        					return false;
	    					}
						});';
			print '})';
			print '</script>';
		}

		print "<!-- End form TICKET -->\n";
	}

	/**
	 * Show the form to add message on ticket
	 *
	 * @param  	string  $width      	Width of form
	 * @return 	void
	 */
	public function showMessageFormReport($width = '40%', $s)
	{
		global $conf, $langs, $user, $hookmanager, $form, $mysoc;

		$formmail = new FormMail($this->db);
		$addfileaction = 'addfile';

		if (!is_object($form)) {
			$form = new Form($this->db);
		}

		// Load translation files required by the page
		$langs->loadLangs(array('other', 'mails', 'ticket'));

		// Clear temp files. Must be done at beginning, before call of triggers
		if (GETPOST('mode', 'alpha') == 'init' || (GETPOST('modelselected') && GETPOST('modelmailselected', 'alpha') && GETPOST('modelmailselected', 'alpha') != '-1')) {
			$this->clear_attached_files();
		}

		// Define output language
		$outputlangs = $langs;
		$newlang = '';
		if (getDolGlobalInt('MAIN_MULTILANGS') && empty($newlang) && isset($this->param['langsmodels'])) {
			$newlang = $this->param['langsmodels'];
		}
		if (!empty($newlang)) {
			$outputlangs = new Translate("", $conf);
			$outputlangs->setDefaultLang($newlang);
			$outputlangs->load('other');
		}

		// Get message template for $this->param["models"] into c_email_templates
		$arraydefaultmessage = -1;
		if (isset($this->param['models']) && $this->param['models'] != 'none') {
			$model_id = 0;
			if (array_key_exists('models_id', $this->param)) {
				$model_id = (int) $this->param["models_id"];
			}

			$arraydefaultmessage = $formmail->getEMailTemplate($this->db, $this->param["models"], $user, $outputlangs, $model_id); // If $model_id is empty, preselect the first one
		}

		// Define list of attached files
		$listofpaths = array();
		$listofnames = array();
		$listofmimes = array();

		if (!empty($this->trackid)) {
			$keytoavoidconflict = '-'.$this->trackid;
		} else {
			$keytoavoidconflict = empty($this->track_id) ? '' : '-'.$this->track_id; // track_id instead of trackid
		}
		//var_dump($keytoavoidconflict);
		if (GETPOST('mode', 'alpha') == 'init' || (GETPOST('modelselected') && GETPOST('modelmailselected', 'alpha') && GETPOST('modelmailselected', 'alpha') != '-1')) {
			if (!empty($arraydefaultmessage->joinfiles) && !empty($this->param['fileinit']) && is_array($this->param['fileinit'])) {
				foreach ($this->param['fileinit'] as $file) {
					$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
				}
			}
		}
		// var_dump($_SESSION);
		// var_dump($_SESSION["listofpaths".$keytoavoidconflict]);
		if (!empty($_SESSION["listofpaths".$keytoavoidconflict])) {
			$listofpaths = explode(';', $_SESSION["listofpaths".$keytoavoidconflict]);
		}
		if (!empty($_SESSION["listofnames".$keytoavoidconflict])) {
			$listofnames = explode(';', $_SESSION["listofnames".$keytoavoidconflict]);
		}
		if (!empty($_SESSION["listofmimes".$keytoavoidconflict])) {
			$listofmimes = explode(';', $_SESSION["listofmimes".$keytoavoidconflict]);
		}

		// Define output language
		$outputlangs = $langs;
		$newlang = '';
		if (getDolGlobalInt('MAIN_MULTILANGS') && empty($newlang) && isset($this->param['langsmodels'])) {
			$newlang = $this->param['langsmodels'];
		}
		if (!empty($newlang)) {
			$outputlangs = new Translate("", $conf);
			$outputlangs->setDefaultLang($newlang);
			$outputlangs->load('other');
		}

		print "\n<!-- Begin message_form TICKET -->\n";

		$send_email = GETPOST('send_email', 'int') ? GETPOST('send_email', 'int') : 0;

		// Example 1 : Adding jquery code
		print '<script nonce="'.getNonce().'" type="text/javascript">
		jQuery(document).ready(function() {
			send_email=' . $send_email.';
			if (send_email) {
				if (!jQuery("#send_msg_email").is(":checked")) {
					jQuery("#send_msg_email").prop("checked", true).trigger("change");
				}
				jQuery(".email_line").show();
			} else {
				if (!jQuery("#private_message").is(":checked")) {
					jQuery("#private_message").prop("checked", true).trigger("change");
				}
				jQuery(".email_line").hide();
			}
		';

		// If constant set, allow to send private messages as email
		if (!getDolGlobalString('TICKET_SEND_PRIVATE_EMAIL')) {
			print '
					jQuery(".email_line").show();
				';
		}

		print '});
		</script>';

		// var_dump($_POST);
		// var_dump($_FILES);
		print '<form method="post" name="ticket" id="ticket" enctype="multipart/form-data" action="'.$this->param["returnurlForm"].'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="'.$this->action.'">';
		print '<input type="hidden" name="actionbis" value="add_message">';
		print '<input type="hidden" name="backtopage" value="'.$this->backtopage.'">';
		print '<input type="hidden" name="send_email" value="1" id="send_msg_email"/> ';
		if (!empty($this->trackid)) {
			print '<input type="hidden" name="trackid" value="'.$this->trackid.'">';
		} else {
			print '<input type="hidden" name="trackid" value="'.(empty($this->track_id) ? '' : $this->track_id).'">';
			$keytoavoidconflict = empty($this->track_id) ? '' : '-'.$this->track_id; // track_id instead of trackid
		}
		foreach ($this->param as $key => $value) {
			print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
		}

		// Get message template
		$model_id = 0;
		if (array_key_exists('models_id', $this->param)) {
			$model_id = $this->param["models_id"];
			$arraydefaultmessage = $formmail->getEMailTemplate($this->db, $this->param["models"], $user, $outputlangs, $model_id);
		}

		$result = $formmail->fetchAllEMailTemplate(!empty($this->param["models"]) ? $this->param["models"] : "", $user, $outputlangs);
		if ($result < 0) {
			setEventMessages($this->error, $this->errors, 'errors');
		}
		$modelmail_array = array();
		foreach ($formmail->lines_model as $line) {
			$modelmail_array[$line->id] = $line->label;
		}

		// print '<table class="border" width="'.$width.'">';

		// External users can't send message email
		if ($user->hasRight("ticket", "write") && !$user->socid) {
			$ticketstat = new Ticket($this->db);
			$res = $ticketstat->fetch('', '', $this->track_id);

			$topic = "";
			foreach ($formmail->lines_model as $line) {
				if ($this->param['models_id'] == $line->id) {
					$topic = $line->topic;
					break;
				}
			}
			print '<div class="row email_line">';
				print '<div class="col-12">';
					print $langs->trans('Subject');
					// if (empty($topic)) {
						print '<input type="text" class="text minwidth500" name="subject" value="" />';
					// } else {
					// 	// print '<input type="text" class="text minwidth500" name="subject" value="['.getDolGlobalString('MAIN_INFO_SOCIETE_NOM').' - '.$langs->trans("Ticket").' '.$ticketstat->ref.'] '.$topic.'" />';
					// }
				print '</div>';
			print "</div>";
			print '<br>';

			print '<div class="row email_line">';
				print '<div class="col-12">';
					print '<laebl>'.$langs->trans('MailRecipients').'</label>';
					if ($res) {
						// Retrieve email of all contacts (internal and external)
						$contacts = $ticketstat->getInfosTicketInternalContact(1);
						// var_dump($contacts);
						$contacts = array_merge($contacts, $ticketstat->getInfosTicketExternalContact(1));
						$contacts = [
							[
								"libelle" => "",
								"lastname" => "",
								"firstname" => "",
								"email" => "it-providermanagement@rossmann.de"
							],
							[
								"libelle" => "",
								"lastname" => "",
								"firstname" => "",
								"email" => "rollout-vkst4@rossmann.de"
							],
							[
								"libelle" => "",
								"lastname" => "",
								"firstname" => "",
								"email" => "Filialinfrastruktur@rossmann.de"
							],
							[
								"libelle" => "",
								"lastname" => "",
								"firstname" => "",
								"email" => "posteingangvkst-hotline@rossmann.de"
							],
							[
								"libelle" => "",
								"lastname" => "",
								"firstname" => "",
								"email" => "Timo.Woehler@rossmann.de"
							],
							[
								"libelle" => "",
								"lastname" => "",
								"firstname" => "",
								"email" => "f.mutschler@telonic.de"
							],
							[
								"libelle" => "",
								"lastname" => "",
								"firstname" => "",
								"email" => "rossmann.rollout@ncrvoyix.com"
							],
							[
								"libelle" => "",
								"lastname" => "",
								"firstname" => "",
								"email" => "Kristijan.Novakovic@ncrvoyix.com"
							],
							[
								"libelle" => "",
								"lastname" => "",
								"firstname" => "",
								"email" => "rollout@sesoco.de"
							]
						];
						// var_dump($contacts);
		
						$sendto = array();
		
						// Build array to display recipient list
						if (is_array($contacts) && count($contacts) > 0) {
							foreach ($contacts as $key => $info_sendto) {
								if ($info_sendto['email'] != '') {
									// $sendto[] = dol_escape_htmltag(trim($info_sendto['firstname']." ".$info_sendto['lastname'])." <".$info_sendto['email'].">").' <small class="opacitymedium">('.dol_escape_htmltag($info_sendto['libelle']).")</small>";
									$sendto[] = dol_escape_htmltag(trim($info_sendto['firstname']." ".$info_sendto['lastname'])." <".$info_sendto['email'].">");
								}
							}
						}
		
						// if ($ticketstat->origin_email && !in_array($ticketstat->origin_email, $sendto)) {
						// 	$sendto[] = dol_escape_htmltag($ticketstat->origin_email).' <small class="opacitymedium">('.$langs->trans("TicketEmailOriginIssuer").")</small>";
						// }
		
						// if ($ticketstat->fk_soc > 0) {
						// 	$ticketstat->socid = $ticketstat->fk_soc;
						// 	$ticketstat->fetch_thirdparty();
		
						// 	if (!empty($ticketstat->thirdparty->email) && !in_array($ticketstat->thirdparty->email, $sendto)) {
						// 		$sendto[] = $ticketstat->thirdparty->email.' <small class="opacitymedium">('.$langs->trans('Customer').')</small>';
						// 	}
						// }
		
						// if (getDolGlobalInt('TICKET_NOTIFICATION_ALSO_MAIN_ADDRESS')) {
						// 	$sendto[] = getDolGlobalString('TICKET_NOTIFICATION_EMAIL_TO').' <small class="opacitymedium">(generic email)</small>';
						// }
		
						// Print recipient list
						if (is_array($sendto) && count($sendto) > 0) {
							print img_picto('', 'email', 'class="pictofixedwidth"');
							print implode(', ', $sendto);
						} else {
							print '<div class="warning">'.$langs->trans('WarningNoEMailsAdded').' '.$langs->trans('TicketGoIntoContactTab').'</div>';
						}
					}
					// var_dump($sendto);
					// var_dump($contacts);
				print '</div>';
			print "</div>";
			print '<br>';

		}
		
		$uselocalbrowser = false;
		$listofnames = $this->param['imagesNames'];
		$listofpaths = $this->param['imagesPaths'];
		$listofmimes = $this->param['imagesMimes'];
		// Attached files
		if (!empty($this->withfile)) {
			$out = '<div class="row">';
				$out .= '<div class="col-12">';
					$out .= $langs->trans("MailFile");
					// TODO Trick to have param removedfile containing nb of image to delete. But this does not works without javascript
					$out .= '<input type="hidden" class="removedfilehidden" name="removedfile" value="">'."\n";
					$out .= '<script nonce="'.getNonce().'" type="text/javascript">';
					$out .= 'jQuery(document).ready(function () {';
					$out .= '    jQuery("#'.$addfileaction.'").prop("disabled", true);';
					$out .= '    jQuery("#addedfile").on("change", function() {';
					$out .= '        if (jQuery(this).val().length) {';
					$out .= '            jQuery("#'.$addfileaction.'").prop("disabled", false);';
					$out .= '        } else {';
					$out .= '            jQuery("#'.$addfileaction.'").prop("disabled", true);';
					$out .= '        }';
					$out .= '    });';
					$out .= '    jQuery(".removedfile").click(function() {';
					$out .= '        jQuery(".removedfilehidden").val(jQuery(this).val());';
					$out .= '    });';
					$out .= '})';
					$out .= '</script>'."\n";
					// var_dump($listofpaths);
					if (count($listofpaths)) {
						foreach ($listofpaths as $key => $val) {
							$out .= '<div id="attachfile_'.$key.'">';
							$out .= img_mime($listofnames[$key]).' '.$listofnames[$key];
							// if (!$this->withfilereadonly) {
							// 	$out .= ' <input type="image" style="border: 0px;" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/delete.png" value="'.($key + 1).'" class="removedfile reposition" id="removedfile_'.$key.'" name="removedfile_'.$key.'" />';
							// }
							$out .= '<br></div>';
						}
					} else {
						// $out .= $langs->trans("NoAttachedFiles").'<br>';
					}
					// if ($this->withfile == 2) { // Can add other files
					// 	$out .= '<input type="file" class="flat" id="addedfile" name="addedfile" value="'.$langs->trans("Upload").'" />';
					// 	$out .= ' ';
					// 	$out .= '<input type="submit" class="button smallpaddingimp reposition" id="'.$addfileaction.'" name="'.$addfileaction.'" value="'.$langs->trans("MailingAddFile").'" />';
					// }
				$out .= "</div>";
			$out .= "</div>\n";

			print $out;
			print '<br>';
		}

		
		// MESSAGE
		$defaultmessage = "";
		if (is_object($arraydefaultmessage) && $arraydefaultmessage->content) {
			$defaultmessage = $arraydefaultmessage->content;
		}
		$defaultmessage = str_replace('\n', "\n", $defaultmessage);

		// Deal with format differences between message and signature (text / HTML)
		if (dol_textishtml($defaultmessage) && !dol_textishtml($this->substit['__USER_SIGNATURE__'])) {
			$this->substit['__USER_SIGNATURE__'] = dol_nl2br($this->substit['__USER_SIGNATURE__']);
		} elseif (!dol_textishtml($defaultmessage) && isset($this->substit['__USER_SIGNATURE__']) && dol_textishtml($this->substit['__USER_SIGNATURE__'])) {
			$defaultmessage = dol_nl2br($defaultmessage);
		}
		if (GETPOSTISSET("message") && !GETPOST('modelselected')) {
			$defaultmessage = GETPOST('message', 'restricthtml');
		} else {
			$defaultmessage = make_substitutions($defaultmessage, $this->substit);
			// Clean first \n and br (to avoid empty line when CONTACTCIVNAME is empty)
			$defaultmessage = preg_replace("/^(<br>)+/", "", $defaultmessage);
			$defaultmessage = preg_replace("/^\n+/", "", $defaultmessage);
		}
		print '<div class="row">';
			print '<div class="col-12">';
				print '<label for="message"><span class="">'.$langs->trans("Message").'</span>';
				if ($user->hasRight("ticket", "write") && !$user->socid) {
					$texttooltip = $langs->trans("TicketMessageHelp");
					if (getDolGlobalString('TICKET_MESSAGE_MAIL_INTRO') || getDolGlobalString('TICKET_MESSAGE_MAIL_SIGNATURE')) {
						$texttooltip .= '<br><br>'.$langs->trans("ForEmailMessageWillBeCompletedWith").'...';
					}
					if (getDolGlobalString('TICKET_MESSAGE_MAIL_INTRO')) {
						$texttooltip .= '<br><u>'.$langs->trans("TicketMessageMailIntro").'</u><br>'.getDolGlobalString('TICKET_MESSAGE_MAIL_INTRO');
					}
					if (getDolGlobalString('TICKET_MESSAGE_MAIL_SIGNATURE')) {
						$texttooltip .= '<br><br><u>'.$langs->trans("TicketMessageMailFooter").'</u><br>'.getDolGlobalString('TICKET_MESSAGE_MAIL_SIGNATURE');
					}
					print $form->textwithpicto('', $texttooltip, 1, 'help');
				}
				print '</label>';
			print '</div>';
			print '<div class="col-12">';
				//$toolbarname = 'dolibarr_details';
				$toolbarname = 'dolibarr_notes';
				include_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
				$doleditor = new DolEditor('message', $s, '100%', 900, $toolbarname, '', false, $uselocalbrowser, True, ROWS_5, '100%');
				$doleditor->Create(0,'',false,'','','','');
			print '</div>';
		print '</div>';

		print '<br>';
		print '<div class="row" style="text-align:center">';
			print '<div class="col-6">';
				print '<input type="submit" class="button" name="btn_add_message" value="'.$langs->trans("Send").'"';
				// Add a javascript test to avoid to forget to submit file before sending email
				if ($this->withfile == 2 && !empty($conf->use_javascript_ajax)) {
					print ' onClick="if (document.ticket.addedfile.value != \'\') { alert(\''.dol_escape_js($langs->trans("FileWasNotUploaded")).'\'); return false; } else { return true; }"';
				}
				print ' />';
			print '</div>';
			print '<div class="col-6">';
				if (!empty($this->withcancel)) {
					print " &nbsp; &nbsp; ";
					print '<input class="button button-cancel" type="submit" name="cancel" value="'.$langs->trans("Cancel").'">';
				}
			print '</div>';
		print '</div>';
		// print "</center>\n";

		print '<input type="hidden" name="page_y">'."\n";

		print "</form><br>\n";

		// Disable enter key if option MAIN_MAILFORM_DISABLE_ENTERKEY is set
		if (getDolGlobalString('MAIN_MAILFORM_DISABLE_ENTERKEY')) {
			print '<script type="text/javascript">';
			print 'jQuery(document).ready(function () {';
			print '		$(document).on("keypress", \'#ticket\', function (e) {		/* Note this is called at every key pressed ! */
	    					var code = e.keyCode || e.which;
	    					if (code == 13) {
								console.log("Enter was intercepted and blocked");
	        					e.preventDefault();
	        					return false;
	    					}
						});';
			print '})';
			print '</script>';
		}

		print "<!-- End form TICKET -->\n";
	}
	function get_image_mime_type_by_extension($filename) {
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$mime_types = [
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif',
			// Add more extensions and MIME types as needed
		];
		return isset($mime_types[$ext]) ? $mime_types[$ext] : false;
	}
}
