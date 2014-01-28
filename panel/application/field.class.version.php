<?php
/***
This version is for Pardis project only base on 0.3.0.2
*/

class field {

	var $action       = '';
	var $method       = 'post';
	var $source       = ''; // to keep name of table
	var $kind_sort    = 'ASC';
	var $column       = 'id';
	var $resualt      = '';
	var $field        = array(); // to keep element of fields
	var $array_member = array();
	var $being_empty  = array(); // to identify field can be empty or not
	var $db           = '';
	var $error        = '';
	var $successfull  = '';
	var $help         = array();

	// {{{ __construct
    function __construct($method, $action, $source, $columns, $k_sort) {

		$this->db 		   = db::getInstance();
		$this->kind_sort   = $k_sort;
		$this->column      = $columns;
		$this->action      = $action;
		$this->method  	   = $method;
		$this->source      = $source;
		$this->error       = '';
		$this->successfull = '';
	}
	// }}}
	// {{{ fixSlashes : fix bug for quote
	function fixSlashes() {
		foreach ($_POST as $key => $value) {
			$_POST[$key] = addslashes($value);
		}
	}
	// }}}
	// {{{ check_table
	function check_table() {

		$table_name = $this->source;
		$value	    = $this->field;
		$index	    = 0;

		$m_query  = "CREATE TABLE IF NOT EXISTS `$table_name`(`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY";
		$m_query .= ", `order` INT NOT NULL";
		while ($index < count($this->field['name'])) {

			if (($value['type'][$index] == 'text' and ($value['maxlen'][$index] == 'disable' or $value['maxlen'][$index] == '')) or
			    ($value['type'][$index] == 'img') or ($value['type'][$index] == 'droplist1') or ($value['type'][$index] == 'tag')) {
					$field_type = 'varchar';
					$length		= 128;
			} else if ($value['type'][$index] == 'text' and $value['maxlen'][$index] <= 250) {
					$field_type = 'varchar';
					$length     = $value['maxlen'][$index];
			} else if ($value['maxlen'][$index] > 250 or $value['type'][$index] == 'textarea') {
					$field_type = 'longtext';
			} else {
					$field_type = 'varchar';
			}

			$length = 128;

			if ($value['name'][$index] == 'submit1') {
				unset($value['name'][$index], $value['option'][$index], $value['maxlen'][$index]);
			} else {
				if ($index < count($this->field['name'])) {
					$m_query .= ',';
				}
				$fieldType  = ($field_type == 'longtext') ? $field_type : $field_type . '(' . $length . ')';
				$m_query   .= "`" . $value['name'][$index] . "`" . ' ' . $fieldType . ' CHARACTER SET utf8';
			}
			$index++;
		}
		$m_query .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 ";
		$stmt     = $this->db->prepare($m_query);
		$stmt->execute();
	}
	// }}}
	// {{{ a : Process based on the current action
    function a() {

		$this->check_table();

		switch ($this->action) {
			case ('insert_items') :
				$this->resualt .= '<a href="?page='.$_GET['page'].'&action=insert">insert group</a><br />';
				$this->resualt .= '<a href="?page='.$_GET['page'].'&action=show">list of groups</a>';
				break;
			case ('insert') :
				$this->showform();
				break;
			case ('insert_new') :
				$this->func_insert();
				break;
			case ('show_update') :
				$this->show_update($_GET['id']);
				break;
			case ('update') :
				$this->func_update($_GET['id']);
				break;
			case ('delete') :
				$this->func_delete($_GET['id']);
				break;
			case ('show') :
				$this->show_list();
				break;
			case ('show_delete') :
				$this->show_delete($_GET['id']);
				break;
			case ('pardis') :
				$this->show_insert();
				break;
			case ('pagging') :
				$this->pagging();
				break;
			case ('autocomplete') :
				$this->autocomplete();
				break;
			case ('updateRowOrder') :
				$this->updateRowOrder();
				break;
		}
	}
	// }}}
	// {{{ autocomplete
	function autocomplete() {

		$utf = $this->db->prepare('SET NAMES utf8');
		$utf->execute();

		if ($_GET['action'] == 'autocomplete') {
			$autocomplete = '';
			$search		  = $_GET['term'];
			$my_query_tag = "select * from `tag` where `table_name`='" . $this->source . "' and name like '" . $search . "%'";
			$stmt         = $this->db->prepare($my_query_tag);
			$stmt->execute();
			$my_tag = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($my_tag as $key => $value) {
				$autocomplete[] = $value['name'];
			}
			echo json_encode($autocomplete);
			exit;
		}
	}
	// }}}
	// {{{ pagging
	function pagging() {

		$utf = $this->db->prepare('SET NAMES utf8');
		$utf->execute();

		$end  = 15;
		$i    = 1;
		$name = '';
		$utf  = $this->db->prepare('SET NAMES utf8');
	    $utf->execute();

		foreach ($this->field['name'] as $i => $val) {
		  	if ($val == 'condition') {
		  		$condition = $this->field['value'][$i];
		  	}
	  	}

		foreach ($this->field['long_record'] as $i => $val) {
		  	if ($val == 'selectbox') {
		  		$long_record_name = $this->field['name'][$i];
		  	}
			if ($val == 'checkbox') {
				$checkbox_fild_name = $this->field['name'][$i];
			}
	  	}

		$group = $_GET['group'];
		if ($group != '' or $group != 0) {
			$where = ($_GET['group'] == '' or $_GET['group'] == 'last') ? '' : "where `$long_record_name` = $_GET[group]";
			$q     = "select * from `$this->source` $where order by `order` $this->kind_sort";
		} else {
			$q = "select * from `$this->source` order by `order` $this->kind_sort";
		}

		$r = $this->db->prepare($q);
		$r->execute();
		$re = $r->fetchAll(PDO::FETCH_ASSOC);

		if ($_GET['start_limit'] == 'last') {
			$mod   = intval(count($re)) % 15;
			$start = ($mod) ? (intval(count($re)) - $mod) : (intval(count($re)) - 15);
		} else if ($_GET['start_limit'] == 'pre_last') {
			$mod   = intval(count($re)) % 15;
			$start = ($mod) ? (intval(count($re)) - $mod) + 15 - (2 * 15) : intval(count($re)) - (2 * 15);
		} else if ($_GET['start_limit'] >= count($re)) {
			return;
		} else {
			$start = $_GET['start_limit'];
		}

		foreach ($this->field['type'] as $i => $val) {
			switch ($val) {
				case ('submit') :
					break;
				case ('reset') :
					break;
			    case ('hyperlink') :
					break;
			    case ('hidden') :
					break;
			    case ('password') :
					break;
			    case ('img') :

					if ($this->field['maxlen'][$i] != 'disable') {
						if ($this->field['upload_img'][$i] === "url") {
							$arr_not_disable[] = $i;
							$arr[]       	   = $this->field['name'][$i];
							$idex_arr[]  	   = $i;
							$name_img 		   = $this->field['name'][$i];
						}
					}

					$arr_not_disable[] = $i;
					$check_img		   = true;
					$arr[]       	   = $this->field['name'][$i];
					$idex_arr[]  	   = $i;
					$name_img 		   = $this->field['name'][$i];
					$check_show_img    = true;

					break;
				case ('droplist1') :
					if ($this->field['maxlen'][$i] != 'disable') {
						$arr_not_disable[] = $i;
						$drop[]			   = $this->field['name'][$i];
						$drop_idx[]        = $i;
					}
					break;
			    case ('droplist_parent') :
					$droplist_parent = $i;
					break;
			    case ('file') :
					if ($this->array_member[$this->field['name'][$i]] == 'img') {
						if ($this->field['maxlen'][$i] != 'disable') {
							$arr_not_disable[] = $i;
						}
					    $check_show_img = true;
						$img_name       = $this->field['name'][$i];
						$arr[]          = $this->field['name'][$i];
						$idex_arr[]     = $i;
					}
					break;
			    default :
					if ($this->field['maxlen'][$i] != 'disable' && $this->field['maxlen'][$i] != 'stick') {
				   		if ($this->field['maxlen'][$i] != 'disable') {
									$arr_not_disable[] = $i;
						}
						$arr[]      = $this->field['name'][$i];
						$idex_arr[] = $i;
					}
					break;
			}
		}

		if ($group != '') {
			if ($long_record_name != '') {
				$where   = ($_GET['group'] == '' or $_GET['group'] == 'last') ? '' : "where `$long_record_name` = $_GET[group]";
				$myquery = "select * from `$this->source` $where order by `order` $this->kind_sort limit $start,$end";
			} else {
				if (is_array($where)) {
					$where_str = 'where '. join(" and ", $where);
				}
				$myquery = "select * from `$this->source` ".$condition." $where_str order by `order` $this->kind_sort limit $start,$end";
			}
		} else {
			if (is_array($where)) {
				$where_str = 'where '. join(" and ", $where);
			}
			$myquery = "select * from `$this->source` $where_str order by `order` $this->kind_sort limit $start,$end";
		}

		$stmt = $this->db->prepare($myquery);
		$stmt->execute();

		foreach ($idex_arr as $i => $t) {
			if ($check_show_img == true && $name_img === $arr[$i]) {
				$check_show_img = false;
				$img_idx		= $i;
				$check_img      = true;
			}
		}

		while ($re = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$show_pardis .= '<tr>';
			$show_pardis .= '<input type="hidden" name="order" value="' . $re['order'] . '" />';
			$show_pardis .= '<td><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></td>';
			$show_pardis .= '<td><input type="checkbox" checkbox-id="'.$re['id'].'" /></td>';
			foreach ($arr as $i => $v) {
				foreach ($re as $ii => $value) {
					if ($v === $ii) {
						if ($check_img == true && $i == $img_idx && $value != '') {
							$show_pardis .= '<td><img name="kk" src="'.img::check_img('../'.$value,100,100,'../resize/').
											'" width="100px" height="100px"/></td>';
						} else {
							if (mb_strlen($value) > 100) {
								$show_pardis .= '<td>' . mb_substr(strip_tags($value), 0, 100) . '...' . '</td>';
							} else {
								$show_pardis .= '<td>' . $value . '</td>';
							}
						}
					}
				}
			}

			if ($drop != '') {
				foreach ($drop as $i => $v) {
					foreach ($re as $ii => $value) {
						if ($v === $ii) {
							foreach ($this->array_member[$ii] as $iii => $valu) {
								if ($iii == $value) {
									if (mb_strlen($valu) > 100) {
										$value = mb_substr(strip_tags($valu), 0, 100) . '...';
									}
									$show_pardis .= '<td>' . $valu . '</td>';
								}
							}
						}
					}
				}
			}

			$show_pardis .= '<td>'.
							'<a page="' . $_GET['page'] . '" row-id="' . $re['id'] . '" name="delete" href="?page=' . $_GET['page'] .
							'&id=' . $re['id'] . '&action=show_delete" title="Delete">' .
						    '<img src="images/icons/cross.png" alt="Delete" /></a>' .
					  		'<a name="update" href="?page=' . $_GET['page'] . '&id=' . $re['id'] . '&action=show_update" title="Edit Meta">' .
						  	'<img src="images/icons/hammer_screwdriver.png" alt="Edit" />' .
							'</a>' .
				    		'</td>' .
							'</tr>';
		}

		echo ($show_pardis);
		exit;
	}
	// }}}
	// {{{ show_insert
	function show_insert() {

		$show_list_pardis   = $this->show_list();
		$form_insert_pardis = $this->showform();

		$this->resualt .= '<div class="content-box"><!-- Start Box Content -->' .
						  '<div class="content-box-header">' .
						  '<h3>Box Content</h3>' .
						  '<ul class="content-box-tabs">' .
						  '<li> <!-- href must be unique and match the id of target div -->' .
						  '<a href="#tab1" class="default-tab">Content</a>' .
						  '</li>' .
						  '<li><a href="#tab2">Creating a new record</a></li>' .
						  '</ul>' .
						  '<div class="clear"></div>' .
						  '</div> <!-- End .content-box-header -->';

		$this->resualt .= '<div class="content-box-content">' . $show_list_pardis . $form_insert_pardis;

		$this->resualt .= '</div> <!-- End .content-box-content -->' .
						  '</div> <!-- End .content-box -->' .
						  '<div class="clear"></div>';
	}
	// }}}
    // {{{ addfield
    function addfield($Label = '', $name = '', $type = '', $value = NULL, $maxlen = 50, $array_of_member = '', $empty = true, $option = 'string',
				      $long_record = 'none', $upload_img = 'null') {

		if ($empty == true) {
	  		$this->being_empty[] = 1;
		} else {
			$this->being_empty[] = 0;
		}

		if ($maxlen == false) {
			$maxlength = '';
		} else {
			$maxlength = 'maxlength="' . $maxlen . '"';
		}

		$this->field['option'][]      = $option;
		$this->field['label'][]       = $Label;
		$this->field['name'][]        = $name;
		$this->field['value'][]       = $value;
		$this->field['type'][]        = $type;
		$this->field['maxlen'][]      = $maxlen;
		$this->field['long_record'][] = $long_record;
		$this->field['upload_img'][]  = $upload_img;
		$this->array_member[$name]    = $array_of_member;
	}
	// }}}
	// {{{ showform
    function showform() {

	    switch ($this->action) {
			case ('pardis') :
				$form_pardis .= '<div class="tab-content" id="tab2"><form enctype="multipart/form-data" method="' . $this->method .
								'" action="?page=' . $_GET['page'] . '&action=insert_new">';
				break;
			case ('insert') :
				$form_pardis .= '<div class="tab-content" id="tab2"><form enctype="multipart/form-data" method="' . $this->method .
							    '" action="?page=' . $_GET['page'] . '&action=insert_new">';
				break;
			case ('show_update') :
				$form_pardis .= '<div class="content-box column-right">' .
								'<div class="content-box-header">' .
								'<h3>Edit</h3>' .
								'</div> <!-- End .content-box-header -->' .
								'<div class="content-box-content">' .
								'<div class="tab-content default-tab"><p>';
				$form_pardis .= '<form enctype="multipart/form-data" method="' . $this->method . '" action="?page=' .
							    $_GET['page'] . '&action=update&id=' . $_GET['id'] . '">';
				break;
			case ('show_delete') :
				$form_pardis .= '<form method="' . $this->method . '" action="?page=' . $_GET['page'] . '&action=delete&id=' . $_GET['id'] . '">';
				break;
		}

		$form_pardis .= '<fieldset>';
		foreach ($this->field['type'] as $t => $type) {
			$name  = $this->field['name'][$t];
			$value = $this->field['value'][$t];
			switch ($type) {
				case ('radio') :
					if ($this->array_member[$name] == '') {
						$this->error .= '  '.$this->field['label'][$t].' Is not defined ';
						break;
					}
					$form_pardis .= '<p>';
					if ($this->field['value'][$t] == NULL) {
						foreach ($this->array_member[$name] as $i => $val) {
								if ($i === 'checked') {
									$form_pardis .= $this->field['field'][$name][] = '<p>' . $val . '<input type="' . $type .
												    '" name="' . $name . '" value="' . $val . '" checked="checked"/></p>';
								} else {
									$form_pardis .= $this->field['field'][$name][] = '<p>' . $val . '<input type="' . $type .
												    '" name="' . $name . '" value="' . $val . '"/> </p>';
								}
						}
					} else {
						foreach ($this->array_member[$name] as $i => $val) {
							if ($val == $this->field['value'][$t]) {
								$form_pardis .= $this->field['field'][$name][] = '<p>' . $val . '<input type="' . $type . '" name="' .
											    $name . '" value="' . $val . '" checked="checked"/> </p> ';
							} else {
								$form_pardis .= $this->field['field'][$name][] = '<p>' . $val . '<input type="' . $type . '" name="' . $name .
											    '" value="' . $val . '"/> </p> ';
							}
						}
					}
					$form_pardis .= '' . $this->field['label'][$t] . '</p>';
					break;
				case ('checkbox') :
					if ($this->array_member[$name] == '') {
						$this->error .= '  ' . $this->field['label'][$t] . ' Is not defined ';
						break;
					}
					$form_pardis .= '<p>';

					if ($this->array_member[$name] === 'checked') {
						$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] . '</label><input type="' .
									    $type . '" name="' . $name . '" value="' . $value . '" checked="checked" />' . $value;
					} else {
						$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] . '</label><input type="' .
									    $type . '" name="' . $name . '" value="' . $value . '"  />' . $value;
					}
					$form_pardis .= '</p>';
					break;
				case ('droplist') :
					if ($this->field['value'][$t] != NULL) {
						$form_pardis .= '<p>' . $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] . '</label><select name="' .
									    $name . '" class="small-input">';
						foreach ($this->array_member[$name] as $i => $val) {
							if ($this->field['value'][$t] == $val) {
								$form_pardis .= $this->field['field'][$name][] = '<option  value="' . $val . '" selected="selected">' . $val . '</option>';
							} else {
								$form_pardis .= $this->field['field'][$name][] = '<option value="' . $val . '">' . $val . '</option>';
							}
						}
						$form_pardis .= $this->field['field'][$name][] = '</select></p>';
					} else {
						if ($this->array_member[$name] == '') {
							$this->error .= '  ' . $this->field['label'][$t] . ' Is not defined ';
							break;
						}
						$form_pardis .= '<p>';
						$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] .
									    '</label><select class="small-input" name="' . $name . '" >';
						foreach ($this->array_member[$name] as $i => $val) {
							if ($i === 'selected') {
								$form_pardis .= $this->field['field'][$name][] = '<option value="' . $val . '" selected="selected">' . $val . '</option>';
							} else {
								$form_pardis .= $this->field['field'][$name][] = '<option value="' . $val . '">' . $val . '</option>';
							}
						}
						$form_pardis .= $this->field['field'][$name][] = '</select></p>';
					}
					break;
				case ('droplist1') :
					if ($this->field['value'][$t] != NULL) {
						$form_pardis .= '<p>' . $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] .
									    '</label><select name="' . $name . '" class="small-input">';
						foreach ($this->array_member[$name] as $i => $val) {
							if ($this->field['value'][$t] ==  $i) {
								$form_pardis .= $this->field['field'][$name][] = '<option value="' . $i . '" selected="selected">' . $val . '</option>';
							} else {
								$form_pardis .= $this->field['field'][$name][] = '<option value="' . $i . '">' . $val . '</option>';
							}
						}
						$form_pardis .= $this->field['field'][$name][] = '</select></p>';
					} else {
						if ($this->array_member[$name] == '') {
							$this->error .= '  ' . $this->field['label'][$t] . ' Is not defined ';
							break;
						}
						$form_pardis .= '<p>';
						$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] . '</label><select name="' .
								        $name . '" class="small-input">';
						foreach ($this->array_member[$name] as $i => $val) {
							if ($i === 'selected') {
								$form_pardis .= $this->field['field'][$name][] = '<option value="' . $i . '" selected="selected">' . $val . '</option>';
							} else {
								$form_pardis .= $this->field['field'][$name][] = '<option value="' . $i . '">' . $val . '</option>';
							}
						}
						$form_pardis .= $this->field['field'][$name][] = '</select></p>';
					}
					break;

					case ('tag') :
						$form_pardis .= '<p>';
						if ($this->field['value'][$t] != NULL) {
							$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] .
										    '</label><input tag="autocomplete"type="text" id="tags_complete"/>' .
										    '<input class="button" type="button" id="add_tag"  value="Add" style=" margin-right:2px"/><br /><br />';
							$where_tag = '';
							$tag_id    = explode(',', $value);
							foreach ($tag_id as $k => $v) {
								$where_tag .= '`id`=' . $v;
								if ($v != end($tag_id)) {
									$where_tag.=' or ';
								}
							}
							$q = "select * from `tag` where $where_tag";
							$s = $this->db->prepare($q);
							$s->execute();
							$re_tag = $s->fetchAll(PDO::FETCH_ASSOC);
							$form_pardis .= '<ul id="all_tags" style="margin-bottom: 23px;">';
							foreach ($re_tag as $key_tag => $value_tag) {
								$form_pardis .= '<li class="ui-state-default ui-corner-all">' . $value_tag['name'] .
											    '<span class="ui-icon ui-icon-close" style="cursor: pointer;"></span>' .
											    '<input type="hidden" name="tag[]" value="' . $value_tag['name'] . '"></li>';
							}
							$form_pardis .= '</ul>';
							$form_pardis .= $this->field['field'][$name][] = '</p>';

						} else {
							$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] .
											'</label><input tag="autocomplete"type="text" id="tags_complete"/>' .
										    '<input class="button" type="button" id="add_tag"  value="Add" style=" margin-right:2px"/><br /><br />';
							$form_pardis .= '<ul id="all_tags" style="margin-bottom: 23px;"></ul>';
							$form_pardis .= $this->field['field'][$name][] = '</p>';
						}
						break;

					case ('droplist_parent') :
						if ($this->field['value'][$t] != NULL) {
							$form_pardis .= '<p>' . $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] .
										    '</label><select name="' . $name . '" class="small-input">';
							foreach ($this->array_member[$name] as $i => $val) {
								$form_pardis .= $this->field['field'][$name][] = '<optgroup label="' . $val['name'] . '">';
								foreach ($val['sub'] as $key => $value) {
									if ($this->field['value'][$t] == $value['id']) {
										$form_pardis .= $this->field['field'][$name][] = '<option value="' . $value['id'] .
													    '" selected="selected">'.$value['name'].'</option>';
									} else {
										$form_pardis .= $this->field['field'][$name][] = '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
									}
								}
								$form_pardis .= $this->field['field'][$name][] = '</optgroup>';
							}
							$form_pardis .= $this->field['field'][$name][] = '</select></p>';
						} else {
							if ($this->array_member[$name] == '') {
								$this->error .= '  '.$this->field['label'][$t].'Is not defined ';
								break;
							}
							$form_pardis .= '<p>';
							$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] . '</label><select name="' .
										    $name . '" class="small-input">';
							foreach ($this->array_member[$name] as $i => $val) {
								$form_pardis .= $this->field['field'][$name][] = '<optgroup label="' . $val['name'] . '">';
								foreach ($val['sub'] as $key => $value) {
									if ($i === 'selected') {
										$form_pardis .= $this->field['field'][$name][] = '<option value="' . $value['id'] .
														'" selected="selected">' . $value['name'] . '</option>';
									} else {
											$form_pardis .= $this->field['field'][$name][] = '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
									}
								}
								$form_pardis .= $this->field['field'][$name][] = '</optgroup>';
							}
							$form_pardis .= $this->field['field'][$name][] = '</select></p>';
						}
						break;
					case ('combobox') :
						$form_pardis .= '<p>';
						$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] . '</label><select name="' .
									    $name . '" multiple="multiple" >';
						if ($value != NULL) {
							foreach ($this->array_member[$name] as $a1 => $a2) {
								if ($a2 === $this->field['value'][$t]) {
									$form_pardis .= $this->field['field'][$name][] = '<option value="' . $a2 . '" selected="selected">' . $a2 . '</option>';
								} else {
								    $form_pardis .= $this->field['field'][$name][] = '<option value="' . $a2 . '">' . $a2 . '</option>';
								}
							}
						} else {
							foreach ($this->array_member[$name] as $a => $aa) {
								if ($a === 'selected') {
									$form_pardis .= $this->field['field'][$name][] = '<option value="' . $aa . '" selected="selected">' . $aa . '</option>';
								} else {
									$form_pardis .= $this->field['field'][$name][] = '<option value="' . $aa . '">' . $aa . '</option>';
								}
							}
						}
						$form_pardis .= $this->field['field'][$name][] = '</select></p>';
						break;
					case ('textarea') :
						if ($j == 8) {
							$form_pardis .= '</fieldset><fieldset ><p>';
						} else {
							$form_pardis .= '<p>';
						}
						if ($this->array_member[$name] == 'disabled') {
							$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] . '</label><textarea name="' .
										    $name . '" disabled="disabled" rows="5" class="text-input textarea wysiwyg"/>' . $value . '</textarea></p>';
						} else {
						 	$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] . '</label><textarea id="tinymce" name="' .
											$name . '" rows="5" class="text-input textarea wysiwyg"/>' . $value . '</textarea></p>';
						}
						break;
					case ('password') :
						if ($j == 7) {
							$form_pardis .= '</fieldset><fieldset ><p>';
						} else {
							$form_pardis .= '<p>';
						}
						$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] . '</label><input class="text-input small-input" type="' .
									    $type . '" name="' . $name . '" value="' . $value . '" /></p>';
					    break;
					case ('text') :
						$form_pardis .= '<p>';
						if ($this->array_member[$name] == 'disabled') {
							$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] .
											'</label><input class="text-input small-input" type="' . $type . '" name="' . $name . '" value="' . $value .
											'" disabled="disabled" /><span></span></p>';
						} else {
							$form_pardis .= $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] .
											'</label><input class="text-input small-input" type="' . $type . '" name="' . $name . '" value="' . $value .
										    '" /><span></span></p>';
						}
						break;
					case ('hidden') :
						$form_pardis .= '<p>' . $this->field['field'][$name][] = '<input type="' . $type . '" name="' . $name . '" value="' . $value . '" /></p>';
						break;
					case ('submit') :
						$form_pardis .= $this->field['field'][$name][] = '<br />' . '<input id="insert-submit" class="button" type="' . $type .
										'" name="' . $name . '" value="' . $value . '" />';
						break;
					case ('reset') :
						$form_pardis .= $this->field['field'][$name][] = '' . '<input id="insert-submit" class="button" type="' . $type .
									    '" name="' . $name . '" value="' . $value . '" /></p>';
						break;
					case ('hyperlink') :
						$form_pardis .= $this->field['field'][$name][] = '<p>' . '<a href="' . $value . '" name="' . $name . '">' . $Label . '</a></p>';
						break;
					case ('file') :
						$form_pardis .= '<p>' . $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] .
									    '</label><input type="file" name="' . $name . '" value="' . $value . '" /></p>';
						break;
					case ('img') :
						$form_pardis .= '<p>' . $this->field['field'][$name][] = '<label>' . $this->field['label'][$t] .
										'</label><input type="file" name="' . $name . '" value="' . $value . '"/></p>';
						break;
					case ('hidden_date') :
						$form_pardis .= '<p>' . $this->field['field'][$name][] = '<input type="hidden" name="' . $name . '" value="' . time() . '" /></p>';
			}
			$j++;
		}

		$form_pardis .= '</fieldset><div class="clear"></div>';

		return $form_pardis;
	}
	// }}}
	// {{{ func_insert
	function func_insert() {

			$t                = '';
			$val_insert       = '';
			$u                = '';
			$chek_empty       = false;
			$chek_mail        = true;
			$control          = true;
			$count_type       = count($this->field['type']);
			$count            = 2;
			$q                = 0;
			$check_size       = true;
			$invalid_file     = false;
			$check_img        = false;
			$stick            = array();
			$stick_value      = array();
			$stick_value_temp = '';
			$check_stick      = false;
			$stick_select     = '';
			$stick_insert     = '';
			$stick_insert_val = '';
			$stick_b          = '';
			$stick_after      = false;
			$check_submit     = false;
			$check_tag		  = false;
			$index_tag		  = '';

			$this->fixSlashes();

			foreach ($this->field['maxlen'] as $i => $v) {
				if ($stick_b != '' &&  $this->field['option'][$i] != $stick_b && $stick_after == false) {
					if ($this->field['type'][$i] == 'submit') {
					   $check_submit = true;
					}
					$stick_value[]    = $stick_value_temp;
					$stick_value_temp = '';
					$stick_after 	  = true;
				}
				if ($v == 'stick' && $this->field['name'][$i] != $stick_b && $this->field['name'][$i] == $this->field['option'][$i]) {
					$stick[] 	 = $this->field['name'][$i];
					$stick_b 	 = $this->field['name'][$i];
					$check_stick = true;
					$stick_after = false;
			  	}
				if ($v == 'stick' && ($stick_b == '' || $this->field['option'][$i] == $stick_b)) {
					$stick_value_temp .= $this->field['value'][$i].$_POST[$this->field['name'][$i]];
					$stick_after 	   = false;
			    }
			}

			if ($check_stick == true) {
				foreach ($stick as $ii => $value) {
					$stick_select     .= 'and`' . $value . '` = \'' . $stick_value[$ii] . '\'';
				    $stick_insert_val .= ',\'' . $stick_value[$ii] . '\'';
				    $stick_insert     .= ',`' . $value . '`';
				}
				if ($check_submit == true) {
					$stick_select 	  = substr($stick_select, 3);
				    $stick_insert_val = substr($stick_insert_val, 1);
				}
			}

			foreach ($this->field['type'] as $i => $v) {
				switch ($v) {
					case ('submit') :
					case ('reset') :
					case ('radio') :
					case ('droplist') :
					case ('combobox') :
					case ('hyperlink') :
					case ('hidden') :
					case ('hidden_date') :
					case ('checkbox') :
						break;
					case ('tag') :
						$value_tag = $this->field['value'][$i];
						$check_tag = true;
						$index_tag = $i;
						break;
					case ('img') :
						$check_img = true;
						break;
					default :
						$temp = $this->field['name'][$i];
						break;
				}
			}
			$count      = 1;
			$count_type = count($this->field['type']);
			foreach ($this->field['type'] as $i => $v) {
				switch ($v) {
					case ('submit') :
						$count_type--;
						break;
					case ('reset') :
						$count_type--;
						break;
					case ('hyperlink') :
						$count_type--;
						break;
					default :
						break;
				}
			}

			foreach ($this->field['type'] as $i => $v) {
				switch ($v) {
					case ('submit') :
						break;
					case ('reset') :
						break;
					case ('hyperlink') :
						break;
					case ('img') :
						if ($_FILES[$this->field['name'][$i]]['name'] != '') {
							$imagename = mt_rand(0, 999) . preg_replace("/[^a-zA-Z0-9\s]/", "", img::findname($_FILES[$this->field['name'][$i]]['name']));
							$source    = $_FILES[$this->field['name'][$i]]['tmp_name'];
							$imagepath = $imagename . '.' . img::findexts($_FILES[$this->field['name'][$i]]['name']);
							$target    = "../upload/" . $imagepath ;
							move_uploaded_file($source, $target);
							if (filesize($target) > 504800) {
								$check_size = false;
								unlink($target);
								$this->error .= "<script language=\"javascript\" type=\"text/javascript\">" .
												"alert('File size selected is larger than 500 kb !')</script>";
								$pic = '';
							} else {
								$pic = 'upload/' . $imagepath;
							}
						} else {
							$pic = 0;
						}
						if ($count < $count_type) {
							$t   		.= '`' . $this->field['name'][$i] . '`,';
						    $temp        = $this->field['name'][$i];
						    $val_insert .= '\'' . $pic . '\',';
						    $u          .= '`' . $this->field['name'][$i] . '`=\'' . $pic . '\'and';
						} else {
							$t   		.= '`' . $this->field['name'][$i] . '`';
						    $temp 		 = $this->field['name'][$i];
						    $val_insert .= '\'' . $pic . '\'';
						    $u          .= '`' . $this->field['name'][$i] . '`=\'' . $pic . '\'';
						}
                        $count++;
						break;
					case ('file') :
						if ($_FILES[$this->field['name'][$i]]['name'] != '') {
							$filename = mt_rand(0, 999) . preg_replace("/[^a-zA-Z0-9\s]/", "", img::findname($_FILES[$this->field['name'][$i]]['name']));
							$source   = $_FILES[$this->field['name'][$i]]['tmp_name'];
							$ext 	  = img::findexts($_FILES[$this->field['name'][$i]]['name']);
							$filepath = $filename . '.' . $ext;
							$target   = "../upload/" . $filepath;
							if (in_array($ext, array('php', 'php5', 'exe', 'cgi', 'php4'))) {
								$this->error .= 'You are not allowed to file. <a href="javascript:history.back(-1);">Back</a> <br />';
								exit;
							}
							move_uploaded_file($source, $target);
							if (filesize($target) > 10004800) {
								$check_size = false;
								unlink($target);
								$this->error .= "<script language=\"javascript\" type=\"text/javascript\">" .
												"alert('File size selected is larger than 10 MB !')</script>";
								$file = '';
							} else {
								$file = 'upload/' . $filepath;
							}
						} else {
							$file = 0;
						}
						if ($count < $count_type) {
							$t   		.= '`' . $this->field['name'][$i] . '`,';
							$temp	     = $this->field['name'][$i];
						    $val_insert .= '\'' . $file . '\',';
						    $u          .= '`' . $this->field['name'][$i] . '`=\'' . $file . '\'and';
						} else {
							$t    		.= '`' . $this->field['name'][$i] . '`';
						    $temp		 = $this->field['name'][$i];
						    $val_insert .= '\'' . $file . '\'';
						    $u          .= '`' . $this->field['name'][$i] . '`=\'' . $file . '\'';
						}
                        $count++;
						break;
					case ('tag') :
						$utf = $this->db->prepare('SET NAMES utf8');
						$utf->execute();
						if ($_POST['tag'] != '') {
							$tags		= '';
							$table_name = $this->source;
							foreach ($_POST['tag'] as $key => $value) {
								$query_all_tag = "select * from `tag` where `table_name`='" . $table_name . "' and `name`='" . $value . "'";
								$all_tags      = $this->db->prepare($query_all_tag);
								$all_tags->execute();
								$re_tag = $all_tags->fetch(PDO::FETCH_ASSOC);
								if (!empty($re_tag)) {
									$tags .= $re_tag['id'];
									if ($value != end($_POST['tag'])) {
										$tags .= ',';
									}
								} else {
									$query_tag  = "insert into `tag` (`name`,`table_name`) values ('" . $value . "','" . $table_name . "')";
									$insert_tag = $this->db->prepare($query_tag);
									$insert_tag->execute();
									$tags .= $this->db->lastInsertId();
									if ($value != end($_POST['tag'])) {
										$tags .= ',';
									}
								}
							}
						} else if ($_POST['tag'] == '') {
							$tags = '';
						}
						$temp = $this->field['name'][$i];
						if ($count < $count_type) {
							$t   		.= '`' . $this->field['name'][$i] . '`, ';
							$val_insert .= '\'' . $tags . '\', ';
							$u          .= ' `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\'and ';
						} else {
							$t   		.= '`' . $this->field['name'][$i] . '`';
							$val_insert .= '\'' . $tags . '\'';
							$u          .= '`' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\'';
						}
						break;

					default :
						if ($count < $count_type && $this->field['maxlen'][$i] != 'stick') {							
							$t    .= '`' . $this->field['name'][$i] . '`,';
							$temp  = $this->field['name'][$i];
							if ($v == 'password') {
								$val_insert .= '\'' . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . '\',';
								$u          .= '`' . $this->field['name'][$i] . '`=\'' . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . '\'and';
							} else {
								if ($v == 'text' and $this->array_member[$this->field['name'][$i]] == 'email') {
									$chek_mail = (!empty($_POST[$temp])) ? $this->checkEmail($_POST[$temp]) : true;
									if ($chek_mail == false) {
										$this->error .= 'Please enter a valid email address.  <a href="javascript:history.back(-1);">Back</a>';
									}
								}
								if ($v == 'text' and $this->array_member[$this->field['name'][$i]] == 'control') {
									$utf = $this->db->prepare('SET NAMES utf8');
									$utf->execute();
									$m_query = "select " . $this->field['name'][$i] . " from $this->source where `" .
											   $this->field['name'][$i] . "`='" . $_POST[$temp] . "'";
									$stmt    = $this->db->prepare($m_query);
									$stmt->execute();
									$result = $stmt->fetch(PDO::FETCH_ASSOC);
									if ($result != '') {
										$this->error .= 'This username is available ! <a href="javascript:history.back(-1);">Back</a>';
										$control 	  = false;
									}
								}
								$val_insert .= '\'' . $_POST[$temp] . '\',';
								$u          .= '`' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\'and';
							}
							if ($_POST[$temp] == '') {
								if ($this->being_empty[$i] == 0) {
									$chek_empty = true;
								}
							}
						} else {
							if ($this->field['maxlen'][$i] != 'stick') {
								$t    .= '`' . $this->field['name'][$i] . '`';
								$temp  = $this->field['name'][$i];
								if ($v == 'password') {
									$val_insert .= '\'' . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . '\'';
									$u          .= '`' . $this->field['name'][$i] . '`=\'' . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . '\'';
									if (array_key_exists($i+2, $this->field['type'])) {
										$u          .= ' and ';
										$val_insert .= ',';
									}
								} else {
									if ($v == 'text' and $this->array_member == 'email') {
										$chek_mail = (!empty($_POST[$temp])) ? $this->checkEmail($_POST[$temp]) : true;
										if ($chek_mail == false) {
											$this->error .= ' Please enter a valid email address. <a href="javascript:history.back(-1);">Back</a>';
										}
									}
									if ($v == 'text' and $this->array_member[$this->field['name'][$i]] == 'control') {
										$utf = $this->db->prepare('SET NAMES utf8');
										$utf->execute();
										$m_query = "select " . $this->field['name'][$i] . "from $this->source where `" .
												   $this->field['name'][$i] . "`='" . $_POST[$temp] . "'";
										$stmt    = $this->db->prepare($m_query);
										$stmt->execute();
										$result = $stmt->fetch(PDO::FETCH_ASSOC);
										if ($result == '') {
											$this->error .= 'This username is available ! <a href="javascript:history.back(-1);">Back</a>';
											$control      = false;
										}
									}
									$u			.= '`' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\'';
									$val_insert .= '\'' . $_POST[$temp] . '\'';
								}
								if ($_POST[$temp] == '') {
									if ($this->being_empty[$i] == 0) {
										$chek_empty = true;
									}
								}
							}
						}
						$count++;
						break;
				}
			}

			$utf = $this->db->prepare('SET NAMES utf8');
			$utf->execute();

			if ($check_stick == true) {
				if ($check_submit == true) {
					$t = substr($t, 0, -1);
				}
				$m_query = "select " . $t . " from `$this->source` where " . $u . $stick_select;
			} else {
				$m_query = "select " . $t . " from `$this->source` where " . $u;
			}
			$stmt = $this->db->prepare($m_query);
			$stmt->execute();
   			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($result != '') {
				if ($check_size == true) {
					$this->error .= 'These records are available. <a href="javascript:history.back(-1);">Back</a>';
				} else {
					$this->error .= 'Please select a photo with less volume. <a href="javascript:history.back(-1);">Back</a>';
				}
			} else {

					if ($chek_empty == false && $invalid_file == false) {
						if ($chek_mail == true and $control == true) {
							if ($q == 0 && $check_size == true) {
								$myquery = "insert into `$this->source` (" . $t . ") values (" . $val_insert . ")";
								if ($check_stick == true) {
									$myquery = "insert into `$this->source` (" . $t . $stick_insert . ") values (" . $val_insert . $stick_insert_val . ")";
								}
								$stmt = $this->db->prepare($myquery);
								$bool = $stmt->execute();

								if ($bool == 1) {
									$insertedId   = $this->db->lastInsertId();
									$setOrder     = "update `$this->source` set `order` = " . $insertedId . " where `id` = " . $insertedId;
									$setOrderStmt = $this->db->prepare($setOrder);
									$orderResult  = $setOrderStmt->execute();
									if ($orderResult == 1) {
										$this->successfull .= 'The record was successfully added.  <a href="javascript:history.go(-1);"> Back </a>';
									} else {
										$deleteRow = "delete from `$this->source` where id = " . $insertedId;
										$deleteRowStmt = $this->db->prepare($deleteRow);
										$deleteRowStmt->execute();
										$this->error .= 'Unfortunately it is not possible to add this record.  <a href="javascript:history.back(-1);">Back</a>';
									}
								} else {
									$this->error .= 'Unfortunately it is not possible to add this record.  <a href="javascript:history.back(-1);">Back</a>';
								}
							} else if ($check_size == true) {
								$this->error .= 'Please fill in fields carefully.  <a href="javascript:history.back(-1);">Back</a>';
							}
						}
					} else if ($invalid_file == false) {
						$this->error .= 'Please fill the required fields.  <a href="javascript:history.back(-1);">Back</a>';
					}
			}
	}
	// }}}
	// {{{ show_list
	function show_list() {

			$check_show_img 	= false;
			$check_img 			= false;
			$condition 			= '';
			$img_name			= '';
			$scr			    = '';
			$long_record_name   = '';
			$checkbox_fild_name = '';
			$show_pardis		= '';
			$list_empty 		= true;

			$myquery = "select * from `$this->source` order by `order` $this->kind_sort";
			$r       = $this->db->prepare($myquery);
			$r->execute();
			$result = $r->fetchAll(PDO::FETCH_ASSOC);
			$count_records = count($result);

		    foreach ($this->field['name'] as $i => $val) {
			  	if ($val == 'condition') {
			  		$condition = $this->field['value'][$i];
			  	}
			}

			foreach ($this->field['long_record'] as $i => $val) {
				if ($val == 'selectbox') {
					$long_record_name = $this->field['name'][$i];
				}
				if ($val == 'checkbox') {
					$checkbox_fild_name = $this->field['name'][$i];
				}
			}

			foreach ($this->field['option'] as $i => $val) {
				if ($val == 'need') {
			  		$where[] = $this->field['name'][$i] . "= '" . $this->field['value'][$i] . "'";
			  	}
		  	}

		    $utf = $this->db->prepare('SET NAMES utf8');
			$utf->execute();

			if ($long_record_name != '') {
				if ($_GET['group'] != '' && $_GET['group'] != 'last') {
					$where[] = " `$long_record_name` = $_GET[group]";
				}
				if (is_array($where)) {
						$where_str = 'where '. join(" and ", $where);
				}
				$limit   = ($_GET['group'] == ''  or $_GET['group'] == 'last') ?  'LIMIT 0 ,15' : '';
			    $myquery = "select * from `$this->source`  $where_str order by `order` $this->kind_sort $limit";
			} else {
				if (is_array($where)) {
						$where_str = 'where ' . join(" and ", $where);
				}
				$myquery = "select * from `$this->source` " . $condition . " $where_str order by `order` $this->kind_sort limit 0,15 ";
			}

			$stmt = $this->db->prepare($myquery);
			$stmt->bindParam(':condition', $condition, PDO::PARAM_INT);
			$stmt->execute();

			if ($count_records != 0) {
				$list_empty = false;
			}

			$drop     = array();
			$drop_idx = array();
			foreach ($this->field['type'] as $i => $val) {
				switch ($val) {
					case ('submit') :
						break;
					case ('reset') :
						break;
					case ('hyperlink') :
						break;
					case ('hidden') :
						break;
					case ('hidden_date') :
						break;
					case ('password') :
						break;
					case ('img') :
						if ($this->field['maxlen'][$i] != 'disable' && $this->field['maxlen'][$i] != 'stick') {
							if ($this->field['upload_img'][$i] === "url") {
								$arr_not_disable[] = $i;
								$arr[]       	   = $this->field['name'][$i];
								$idex_arr[]  	   = $i;
								$name_img   	   = $this->field['name'][$i];
							}
							$arr_not_disable[] = $i;
							$check_img 		   = true;
							$arr[]             = $this->field['name'][$i];
							$idex_arr[]        = $i;
							$name_img 		   = $this->field['name'][$i];
							$check_show_img    = true;
						}
						break;
					case ('droplist1') :
						if ($this->field['maxlen'][$i] != 'disable') {
							  $arr_not_disable[] = $i;
							  $drop[] 		     = $this->field['name'][$i];
							  $drop_idx[]        = $i;
						}
						break;
					case ('droplist_parent') :
						$droplist_parent = $i;
						break;
					case ('file') :
						if ($this->array_member[$this->field['name'][$i]] == 'img') {
							if ($this->field['maxlen'][$i] != 'disable') {
								$arr_not_disable[] = $i;
							}
							$check_show_img = true;
							$img_name 	    = $this->field['name'][$i];
							$arr[]          = $this->field['name'][$i];
							$idex_arr[]     = $i;
						}
						break;
					default :
						if ($this->field['maxlen'][$i] != 'disable' && $this->field['maxlen'][$i] != 'stick') {
							if ($this->field['maxlen'][$i] != 'disable') {
								$arr_not_disable[] = $i;
							}
							$arr[]      = $this->field['name'][$i];
							$idex_arr[] = $i;
						}
						break;
				}
			}

			if ($long_record_name != '') {
				if ($this->field['type'][$droplist_parent] == 'droplist_parent') {
					$show_pardis .= '<select onchange="window.location =\'?page=' . $_GET['page'] .
									'&action=pardis&group=\'+this.options[this.selectedIndex].value"><option value="last">Last Records</option>';
					foreach ($this->array_member[$long_record_name] as $i => $val) {
						$show_pardis .= '<optgroup label="' . $val['name'] . '">';
						foreach ($val['sub'] as $key => $value) {
							if ($_GET['group'] == $value['id']) {
								$show_pardis .= '<option value="' . $value['id'] . '" selected="selected">' . $value['name'] . '</option>';
							} else {
								$show_pardis .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
							}
						}
						$show_pardis .= '</optgroup>';
					}
					$show_pardis .= '</select><br />';
				} else {
					$show_pardis .= '<select style="margin-bottom:15px" onchange="window.location =\'?page=' . $_GET['page'] .
									'&action=pardis&group=\'+  this.options[this.selectedIndex].value">' .
									'<option value="last">Last Records</option>';
					foreach ($this->array_member[$long_record_name] as $key => $value) {
						if ($_GET['group'] == $key) {
							$show_pardis .= '<option selected="selected" value="' . $key . '">' . $value . '</option>';
						} else {
							$show_pardis .= '<option value="' . $key . '">' . $value . '</option>';
						}
					}
					$show_pardis .= '</select>';
				}
			}

			$show_pardis .= '<div class="tab-content default-tab" id="tab1">' .
							'<div class="notification information png_bg">' .
							'<a href="#" class="close"><img src="images/icons/cross_grey_small.png" title="Close this notification" alt="close" /></a>' .
							'<div>';

			if ($list_empty == false) {
				$show_pardis .= 'Here you can see the list of records and you can edit and delete them.';
			} else if ($list_empty == true) {
				$show_pardis .= 'The list is empty !';
			}

			$show_pardis .= '</div>' .
							'</div>' .
							'<div id="overflow" style="display:none"><div id="loading" align="center" ><img src="images/loading-icon.gif"/></div>' .
							'</div><div class="clearfix"></div>';

			if ($list_empty == false) {
				$show_pardis .= '<table><thead><tr class="ui-sortable"><th></th><th><input class="check-all" type="checkbox" /></th>';

				foreach ($idex_arr as $i => $t) {
					if ($check_show_img == true && $name_img === $arr[$i]) {
						$show_pardis    .= '<th>Display Image</th>';
						$check_show_img  = false;
						$img_idx 	     = $i;
						$check_img       = true;
					} else {
					  	$show_pardis .= '<th>' . $this->field['label'][$t] . '</th>';
					}
				}

				foreach ($drop_idx as $i => $t) {
					$show_pardis .= '<th>' . $this->field['label'][$t] . '</th>';
				}

				$show_pardis .= '<th>tools</th></tr></thead><tbody class="sortable">';

				while ($re = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$show_pardis .= '<tr>';
					$show_pardis .= '<input type="hidden" name="order" value="' . $re['order'] . '" />';
					$show_pardis .= '<td><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></td>';
					$show_pardis .= '<td><input type="checkbox" checkbox-id="' . $re['id'] . '" /></td>';
					foreach ($arr as $i => $v) {
						foreach ($re as $ii => $value) {
							if ($v === $ii) {
								if ($check_img == true && $i == $img_idx && $value != '') {
									$show_pardis .= '<td><img name="kk" src="' . img::check_img('../' . $value, 100, 100, '../resize/') .
													'" width="100px" height="100px"/></td>';
								} else {
									if (strlen($value) > 100) {
										$show_pardis .= '<td>' . substr(strip_tags($value), 0, 100) . '...' . '</td>';
									} else {
										$show_pardis .= '<td>' . $value . '</td>';
									}
								}
							}
						}
					}

					foreach ($drop as $i => $v) {
						foreach ($re as $ii => $value) {
							if ($v === $ii) {
								foreach ($this->array_member[$ii] as $iii => $valu) {
									if ($iii == $value) {
										if (strlen($valu) > 100) {
											$value = substr(strip_tags($valu), 0, 100) . '...';
										}
										$show_pardis .= '<td>' . $valu . '</td>';
									}
								}
							}
						}
					}

					$show_pardis .= '<td>';
					$show_pardis .= '<a page="' . $_GET['page'] . '" row-id="' . $re['id'] . '" name="delete" href="?page=' . $_GET['page'] .
									'&id=' . $re['id'] . '&action=show_delete" title="Delete">' .
							 	    '<img src="images/icons/cross.png" alt="Delete" />' .
							 		'</a>' .
							 		'<a name="update" href="?page=' . $_GET['page'] . '&id=' . $re['id'] . '&action=show_update" title="Edit Meta">' .
						 			'<img src="images/icons/hammer_screwdriver.png" alt="Edit" />' .
						 			'</a>';
					$show_pardis .= '</td>';
					$show_pardis .= '</tr>';
				}

				if ($checkbox_fild_name != '') {
					$checkbox_key = array_search($checkbox_fild_name, $this->field['name']);
					foreach ($this->array_member[$checkbox_fild_name] as $key => $value) {
						$show_pardis .= '<a page="' . $_GET['page'] . '" key="' . $checkbox_key . '" value="' . $key .
									    '" class="checkbox button ui-corner-all-state-default" href="#"><span>' . $value . '</span> list selectet items </a>';
					}
				}

				$show_pardis .= '</tbody>' .
								'<tfoot>' .
								'<tr>' .
								'<td colspan="7">' .
								'<div class="bulk-actions align-left">' .
								'<select name="dropdown">' .
								'<option value="option1">Please select an option</option>' .
								'<option value="delete">Delete</option>' .
								'</select>' .
								'<a href="#" class="button">Do</a>' .
								'</div>';

				if ($count_records != '') {
					if ($count_records > 15) {
						$j = 1;
						$i = 1;
						$show_pardis .= '<div class="pagination">' .
										'<a title="First Page"  href="#">« first</a>' .
										'<a title="Previous Page" href="#">« pre</a>';
						while ($i <= $count_records) {
							if ($j == 1) {
								$show_pardis .= '<a class="number current" href="#">' . $j . '</a>';
							} else {
								$show_pardis .='<a class="number" href="#">' . $j . '</a>';
							}
							$i = $i + 15;
							$j++;
						}
						$show_pardis .= '<a title="Next Page" href="#">next »</a>' .
									    '<a title="Last Page" href="#">last »</a>' .
										'</div> <!-- End .pagination -->';
					}
				}

				$show_pardis .= '<div class="clear"></div>' .
								'</td></tr>' .
								'</tfoot></table>';
			}

			$show_pardis .= '</div>';

			return $show_pardis;
	}
	// }}}
	// {{{ show_update
	function show_update($id) {

				$utf = $this->db->prepare('SET NAMES utf8');
			    $utf->execute();
				$myquery = "select * from `$this->source` where `id`= :id";
				$stmt    = $this->db->prepare($myquery);
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
   				$stmt->execute();
   				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				foreach ($row as $i => $s) {
					$row[$i] = stripslashes($s);
				    foreach ($this->field['name'] as $n => $name) {
				    	if ($name === $i) {
							if ($this->field['type'][$n] == 'img') {
								if ($row[$i] != 0) {
									list($width, $height) = getimagesize('../' . $row[$i]);
									$img = ($width < 500 && $height < 500) ? '../' . $row[$i] : img::check_img('../' . $row[$i], 500, 500, '../resize/');
								    $this->resualt .= '<div style="text-align:center"><br><img name="" src="' . $img . '" style="" /><br></div>';
								}
							}

							if ($this->field['type'][$n] == 'password') {
							  	$this->field['value'][$n] = '';
						    } else {
						    	$this->field['value'][$n] = $row[$i];
							}
						}
					}
				}

			  	$this->resualt = $this->showform() . '</p>' .
													 '</div> <!-- End #tab3 -->' .
												     '</div> <!-- End .content-box-content -->' .
													 '</div> <!-- End .content-box -->' .
													 '<div class="clear"></div>';			
	}
	// }}}
	// {{{ func_update
	function func_update($id) {

				$this->fixSlashes();
				if ($_GET['method'] == 'ajax-checkbox') {
					$array = split(',', $id);
					foreach ($array as $key => $value) {
				 		db::runQuery("update `$this->source` set " . $this->field['name'][$_GET['key']] . " = " . $_GET['value'] . " where `id`=" . (int)$value);
					}
					echo 'Selected records were edited.';
					exit;
				}

		        $chek_empty       = false;
				$chek_mail		  = true;
				$count_type       = count($this->field['type']);
				$count            = 1;
				$strselet         = '';
				$strselect_where  = '';
				$strupdate        = '';
				$val_insert       = '';
				$user_select      = '';
				$user             = '';
				$pass             = '';
				$is_account       = false;
				$pic              = '';
				$modwidth         = '';
				$modheight        = '';
				$check_photo      = false;
				$invalid_file	  = false;
				$stick            = array();
				$stick_value      = array();
				$stick_value_temp = '';
				$check_stick      = false;
				$stick_select     = '';
				$stick_insert     = '';
				$stick_insert_val = '';
				$stick_b          = '';
				$check_submit     = false;
				$stick_after      = false;
				foreach ($this->field['maxlen'] as $i => $v) {
					if ($stick_b != '' &&  $this->field['option'][$i] != $stick_b && $stick_after == false) {
						if ($this->field['type'][$i] == 'submit') {
					        $check_submit = true;
						}
						$stick_value[]    = $stick_value_temp;
						$stick_value_temp = '';
						$stick_after 	  = true;
					}
			 		if ($v == 'stick' && $this->field['name'][$i] != $stick_b && $this->field['name'][$i] == $this->field['option'][$i]) {
						$stick[] 	 = $this->field['name'][$i];
						$stick_b 	 = $this->field['name'][$i];
					  	$check_stick = true;
					  	$stick_after = false;
			    	}
					if ($v == 'stick' && ($stick_b == '' || $this->field['option'][$i] == $stick_b)) {
			  	    	$stick_value_temp .= $this->field['value'][$i].$_POST[$this->field['name'][$i]];
				    	$stick_after 	   = false;
			  		}
				}
			    if ($check_stick == true) {
					foreach ($stick as $ii => $value) {
						$strselect_where .= 'and`' . $value . '` = \'' . $stick_value[$ii] . '\'';
					    $strupdate       .= ',`' . $value . '`=\'' . $stick_value[$ii] . '\'';
			 		}
					$strselect_where  = substr($strselect_where, 3);
					$strupdate 		  = substr($strupdate, 1);
					$strselect_where .= ' and ';
					$strupdate 	     .= ' , ';
				}
				foreach ($this->field['type'] as $i => $v) {
					switch ($v) {
						case ('submit') :
							$count_type--;
						    break;
					    case ('reset') :
						    $count_type--;
						    break;
					    case ('hidden') :
						    $count_type--;
						    break;
					    case ('hidden_date') :
							$count_type--;
						    break;
					    default :
						    break;
					}
				}
				foreach ($this->field['type'] as $i => $v) {
					switch ($v) {
						case ('submit') :
							break;
						case ('reset') :
							break;
						case ('hidden') :
							break;
						case ('hidden_date') :
							break;
						case ('img') :
							if ($_FILES[$this->field['name'][$i]]['name'] != '') {
								$check_photo = true;
								$imagename   = mt_rand(0, 999) . preg_replace("/[^a-zA-Z0-9\s]/", "", img::findname($_FILES[$this->field['name'][$i]]['name']));
								$source 	 = $_FILES[$this->field['name'][$i]]['tmp_name'];
								$imagepath   = $imagename . '.' . img::findexts($_FILES[$this->field['name'][$i]]['name']);
								$target 	 = "../upload/" . $imagepath;
								move_uploaded_file($source, $target);
								if (filesize($target) > 2004800) {
									unlink($target);
									$this->error .= "<script language=\"javascript\" type=\"text/javascript\">" .
											   	    "alert('File size selected is larger than 2 mb !')</script>";
								} else {
									$pic = 'upload/' . $imagepath;
								}

								if ($count < $count_type) {
									$strselect_where .= ($strselect_where) ? ' and `' . $this->field['name'][$i] . '`=\'' . $pic . '\' ' :
											    	    ' `' . $this->field['name'][$i] . '`=\'' . $pic . '\' ';
									$strupdate 		 .= ($strupdate) ? ' , `' . $this->field['name'][$i] . '`=\'' . $pic . '\' ' :
										      		    ' `' . $this->field['name'][$i] . '`=\'' . $pic . '\' ';
									$strselet  		 .= ($strselet) ? ' , `' . $this->field['name'][$i] . '` ' : ' `' . $this->field['name'][$i] . '` ';
								} else {
									$strselect_where .= ($strselect_where) ? ' and `' . $this->field['name'][$i] . '`=\'' . $pic . '\' ' :
													    ' `' . $this->field['name'][$i] . '`=\'' . $pic . '\' ';
									$strupdate 		 .= ($strupdate) ? ' , `' . $this->field['name'][$i] . '`=\'' . $pic . '\' ' :
													    ' `' . $this->field['name'][$i] . '`=\'' . $pic . '\' ';
									$strselet 		 .= ($strselet) ? ' , `' . $this->field['name'][$i] . '` ' : ' `' . $this->field['name'][$i] . '` ';
								}

								$count++;
							} else {
								$count_type--;
							}
							break;
						case ('file') :
							$check_file = false;
							if ($_FILES[$this->field['name'][$i]]['name'] != '') {
								$check_file = true;
								$filename   = mt_rand(0, 999) . preg_replace("/[^a-zA-Z0-9\s]/", "", img::findname($_FILES[$this->field['name'][$i]]['name']));
								$source     = $_FILES[$this->field['name'][$i]]['tmp_name'];
								$filepath   = $filename . '.' . img::findexts($_FILES[$this->field['name'][$i]]['name']);
								$target 	= "../upload/" . $filepath;
								if (in_array($ext, array('php', 'php5', 'exe', 'cgi', 'php4'))) {
									$this->error .= 'You are not allowed to file. <a href="javascript:history.back(-1);">Back</a> <br />';
									exit;
							    }
								move_uploaded_file($source, $target);
								if (filesize($target) > 10004800) {
									unlink($target);
									$this->error .= "<script language=\"javascript\" type=\"text/javascript\">" .
												    "alert('File size selected is larger than 10 MB !')</script>";
								} else {
									$file = 'upload/' . $filepath;
								}
						 	}
							if ($check_file == true) {
								if ($count < $count_type) {
									$strselect_where .= ($strselect_where) ? ' and `' . $this->field['name'][$i] . '`=\'' . $file . '\' ' :
											    		' `' . $this->field['name'][$i] . '`=\'' . $file . '\' ';
									$strupdate 		 .= ($strupdate) ? ' , `' . $this->field['name'][$i] . '`=\'' . $file . '\' ' :
													    ' `' . $this->field['name'][$i] . '`=\'' . $file . '\' ';
									$strselet 		 .= ($strselet) ? ' , `' . $this->field['name'][$i] . '` ' : ' `' . $this->field['name'][$i] . '` ';
								} else {
									$strselect_where .= ($strselect_where) ? ' and `' . $this->field['name'][$i] . '`=\'' . $file . '\' ' :
													    ' `' . $this->field['name'][$i] . '`=\'' . $file . '\' ';
									$strupdate 		 .= ($strupdate) ? ' , `' . $this->field['name'][$i] . '`=\'' . $file . '\' ' :
													    ' , `' . $this->field['name'][$i] . '`=\'' . $file . '\' ';
									$strselet 		 .= ($strselet) ? ' , `' . $this->field['name'][$i] . '` ' : ' `' . $this->field['name'][$i] . '` ';
								}
						    }
							$count++;
							break;

						case ('tag') :
							$utf = $this->db->prepare('SET NAMES utf8');
							$utf->execute();
							$temp = $this->field['name'][$i];
							if ($_POST[$temp] != '') {
								$tags       = '';
								$table_name = $this->source;
								foreach ($_POST[$temp] as $key => $value) {
									$query_all_tag = "select * from `tag` where `table_name`= '" . $table_name . "'  and `name`='" . $value . "'";
									$all_tags      = $this->db->prepare($query_all_tag);
									$all_tags->execute();
									$re_tag = $all_tags->fetch(PDO::FETCH_ASSOC);
									if (!empty($re_tag)) {
										$tags .= $re_tag['id'];
										if ($value != end($_POST[$temp])) {
											$tags .= ',';
										}
									} else {
										$query_tag  = "insert into `tag` (`name`,`table_name`) values ('" . $value . "','" . $table_name . "')";
										$insert_tag = $this->db->prepare($query_tag);
										$insert_tag->execute();
										$tags .= $this->db->lastInsertId();
										if ($value != end($_POST[$temp])) {
											$tags .= ',';
										}
									}
								}
								if ($count < $count_type) {
									$strselet 		 .= ($strselet) ? ' , `' . $this->field['name'][$i] . '` ' : ' `' . $this->field['name'][$i] . '` ';
									$strselect_where .= ($strselect_where) ? ' and `' . $this->field['name'][$i] . '`=\'' . $tags . '\' ' :
													    ' `' . $this->field['name'][$i] . '`=\'' . $tags . '\' ';
									$strupdate 		 .= ($strupdate) ? ' , `' . $this->field['name'][$i] . '`=\'' . $tags . '\' ' :
												        ' `' . $this->field['name'][$i] . '`=\'' . $tags . '\' ';
								} else {
									$strselet 		 .= ($strselet) ? ' , `' . $this->field['name'][$i] . '` ' : ' `' . $this->field['name'][$i] . '` ';
									$strselect_where .= ($strselect_where) ? ' and `' . $this->field['name'][$i] . '`=\'' . $tags . '\' ' :
													    ' `' . $this->field['name'][$i] . '`=\'' . $tags . '\' ';
									$strupdate 		 .= ($strupdate) ? ' , `' . $this->field['name'][$i] . '`=\'' . $tags . '\' ' :
													    ' `' . $this->field['name'][$i] . '`=\'' . $tags . '\' ';
								}

							} else if ($_POST[$temp] == '') {
								if ($count < $count_type) {
									$strselet 		 .= ($strselet) ? ' , `' . $this->field['name'][$i] . '` ' : ' `' . $this->field['name'][$i] . '` ';
									$strselect_where .= ($strselect_where) ? ' and `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ' :
													    ' `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ';
									$strupdate 		 .= ($strupdate) ? ' , `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ' :
									      			    ' `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ';
								} else {
									$strselet 		 .= ($strselet) ? ' , `' . $this->field['name'][$i] . '` ' : ' `' . $this->field['name'][$i] . '` ';
									$strselect_where .= ($strselect_where) ? ' and `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ' :
													    ' `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ';
									$strupdate 		 .= ($strupdate) ? ' , `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ' :
													    ' `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ';
								}
							}
							break;
						default :
							if ($count < $count_type) {
								if ($this->field['maxlen'][$i] != 'stick') {
									$strselet .= ($strselet) ? ' , `' . $this->field['name'][$i] . '` ' : ' `' . $this->field['name'][$i] . '` ';
									$temp      = $this->field['name'][$i];
									if ($v == 'password') {
										$is_account 	  = true;
										$strupdate 		 .= ($strupdate) ? ' , `' . $this->field['name'][$i] . '`=\'' .
															md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . '\' ' :
														    ' `' . $this->field['name'][$i] . '`=\'' . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . '\' ';
								        $strselect_where .= ($strselect_where) ?
															' and `' . $this->field['name'][$i] . '`=\'' . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . '\' ' :
															'`' . $this->field['name'][$i] . '`=\'' . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . '\' ';
										$pass 			  = " and `" . $temp . "` = '" . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . "'";
									} else {
										if ($v == 'text' and $this->array_member[$this->field['name'][$i]] == 'email') {
											$chek_mail = (!empty($_POST[$temp])) ? $this->checkEmail($_POST[$temp]) : true;
											if ($chek_mail == false) {
												$this->error .= 'Please enter a valid email address. <a href="javascript:history.back(-1);">Back</a>';
										    } else if (strcmp($temp, 'username') == 0) {
												$user_select = $this->field['name'][$i];
												$user 		 = '`' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\'';
										    }
										}
										if ($v == 'text' and $this->array_member[$this->field['name'][$i]] == 'control') {
											$user_select = $this->field['name'][$i];
											$user 		 = '`' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\'';
										}
										$strselect_where .= ($strselect_where) ? ' and `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ' :
															' `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ';
										$strupdate 		 .= ($strupdate) ? ' , `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ' :
													        ' `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ';
									}
									if ($_POST[$temp] == '') {
										if ($this->being_empty[$i] == 1) {
											$chek_empty = false;
										} else {
											$chek_empty = true;
										}
									}
								}
							} else {
								if ($this->field['maxlen'][$i] != 'stick') {
									$strselet .= ($strselet) ? ' , `' . $this->field['name'][$i] . '` ' : ' `' . $this->field['name'][$i] . '` ';
									$temp 	   = $this->field['name'][$i];
									if ($v == 'password') {
										$is_account 	  = true;
										$strupdate  	 .= ($strupdate) ?
														    ' , `' . $this->field['name'][$i] . '`=\'' . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . '\' ' :
														    ' `' . $this->field['name'][$i] . '`=\'' . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . '\' ';
										$strselect_where .= ($strselect_where) ?
														    ' and `' . $this->field['name'][$i] . '`=\'' . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . '\' ' :
												      		' `' . $this->field['name'][$i] . '`=\'' . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . '\' ';
										$pass 			  = " and `" . $temp . "` = '" . md5(md5($_POST[$temp] . 'hash password') . 'w1e3c3') . "'";
									} else {
								    	if ($v == 'text' and $this->array_member == 'email') {
											$chek_mail = (!empty($_POST[$temp])) ? $this->checkEmail($_POST[$temp]) : true;
											if ($chek_mail == false) {
												$this->error .= 'Please enter a valid email address. <a href="javascript:history.back(-1);">Back</a>';
											} else if (strcmp($temp, 'username') == 0) {
												$user_select = $this->field['name'][$i];
												$user 	     = '`' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\'';
											}
										}
										if ($v == 'text' and $this->array_member[$this->field['name'][$i]] == 'control') {
											$user_select = $this->field['name'][$i];
											$user 		 = '`' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\'';
										}
										$strselect_where .= ($strselect_where) ? ' and `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ' :
												      		' `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ';
										$strupdate 		 .= ($strupdate) ? ' , `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ' :
													        ' `' . $this->field['name'][$i] . '`=\'' . $_POST[$temp] . '\' ';
									}
									if ($_POST[$temp] == '') {
										if ($this->being_empty[$i] == 1) {
											$chek_empty = false;
										} else {
											$chek_empty = true;
										}
									}
								}
							}
							$count++;
							break;
					}
				}

				if ($check_submit == true) {
					$strselect_where = substr($strselect_where, 0, -3);
				    $strselet 		 = substr($strselet, 0, -1);
				    $strupdate 		 = substr($strupdate, 0, -1);
				}

				if ($invalid_file == false) {

					$utf = $this->db->prepare('SET NAMES utf8');
					$utf->execute();

			        $query = "select $strselet from `$this->source` where " . $strselect_where . " AND `id` = :id";
					$stmt  = $this->db->prepare($query);
					$stmt->bindParam(':id', $id, PDO::PARAM_INT);
					$stmt->execute();
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($row[$this->field['name']['0']] == '') {
						if ($chek_empty == false) {

							if ($chek_mail == true) {
								$utf = $this->db->prepare('SET NAMES utf8');
								$utf->execute();
								if ($is_account == true) {
									$m_query = "select " . $user_select . " from $this->source where " . $user . " AND `id` != :id";
									$stmt    = $this->db->prepare($m_query);
									$stmt->bindParam(':id', $id, PDO::PARAM_INT);
									$stmt->execute();
									$result = $stmt->fetch(PDO::FETCH_ASSOC);
									if ($result != '') {
										$this->error .= 'This username is available ! ';
										$control      = false;
									} else {
									    $myquery = "update `$this->source` set $strupdate where `id`=:id";
										$stmt    = $this->db->prepare($myquery);
										$stmt->bindParam(':id', $id, PDO::PARAM_INT);
										$bool = $stmt->execute();
										if ($bool == 1) {
											$this->successfull .= 'The record was updated successfully. <a href="javascript:history.go(-2);">Back</a>';
										} else {
									   		$this->error .= 'Unfortunately it is not possible to update this record. <a href="javascript:history.back(-1);">Back</a>';
										}
									 }
								} else {
							    	$myquery = "update `$this->source` set $strupdate where `id`='$id'";
								 	$stmt    = $this->db->prepare($myquery);
								 	$bool    = $stmt->execute();
								    if ($bool == 1) {
										$this->successfull .= 'The record was updated successfully. <a href="javascript:history.go(-2);">Back</a>';
								    } else {
										$this->error .= 'Unfortunately it is not possible to update this record. <a href="javascript:history.back(-1);">Back</a>';
									}
							    }
							}

						} else {
							$this->error .= 'Please fill the required fields.  <a href="javascript:history.back(-1);">Back</a>';
						}
					} else {
						$this->error .= 'These records are available. <a href="javascript:history.back(-1);">Back</a>';
					}
				}
	}
	// }}}
	// {{{ show_delete
	function show_delete($id) {
		$this->resualt .= '<div class="content-box column-right">' .
						  '<div class="content-box-header">' .
						  '<h3>remove a item</h3>' .
						  '</div> <!-- End .content-box-header -->' .
						  '<div class="content-box-content">' .
						  '<div class="tab-content default-tab">';
		$this->resualt .= '<fieldset><p>Are you sure?</p>';
		$this->resualt .= '<p>' .
	 					  '<a class="button_ok" style="width:35px;text-align:center;" href="?page=' . $_GET['page'] .
						  '&action=delete&id=' . $id . '">delete </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
						  '<a class="button" style="width:35px;text-align:center;" href="?page=' . $_GET['page'] . '&action=pardis">back</a>' .
						  '</p>' .
						  '</fieldset>' .
						  '</div> <!-- End #tab3 -->' .
						  '</div> <!-- End .content-box-content -->' .
						  '</div> <!-- End .content-box -->' .
						  '<div class="clear"></div>';
	}
	// }}}
	// {{{ func_delete
	function func_delete($id) {
		if ($_GET['method'] == 'ajax-checkbox') {
		 	$array = split(',', $id);
			foreach ($array as $key => $value) {
				$where .= ($where != '') ? 'or' : '';
				$where .= " `id` = '" . (int)$value . "'" ;
			}
		} else {
		 	$where = ' `id`=' . (int)$id;
		}

		$myquery = "delete from `$this->source` where " . $where;
		$stmt    = $this->db->prepare($myquery);
   		$bool    = $stmt->execute();
		if ($bool == 1) {
			if ($_GET['method'] == 'ajax') {
		    	echo 'The record was successfully deleted.';
				exit;
			} elseif ($_GET['method'] == 'ajax-checkbox') {
				echo 'The selected record has been deleted.';
				exit;
			} else {
				$this->successfull .= 'The record was successfully deleted.';
			}
		} else {
			if ($_GET['method'] == 'ajax') {
		    	echo 'Unfortunately it is not possible to delete the record.';
				exit;
		 	} elseif ($_GET['method'] == 'ajax-checkbox') {
				echo 'Unfortunately it is not possible to delete the record.';
				exit;
			} else {
				$this->error .= 'Unfortunately it is not possible to delete the record.';
			}
		}
	}
	// }}}
	// {{{ option
	function option($value, $i) {
		$type = gettype($value);
		$this->field['option'][$i];
		if ($this->field['option'][$i] == $type) {
			return true;
		} else {
			return false;
		}
	}
	// }}}
	// {{{ selet_items
	function selet_items($table, $column, $field) {

		$utf = $this->db->prepare('SET NAMES utf8');
		$utf->execute();

		$myquery = "select distinct `$column` from `$table`";
		$stmt    = $this->db->prepare($myquery);
		$stmt->execute();

		$arr = array();
		$row = $stmt->fetchAll();
		$t   = NULL;
		foreach ($row as $i => $val) {
			$t 			   = $i;
			$arr[$field][] = $val[$column];
		}

		if (is_null($t)) {
			return;
		}

		return $arr[$field];
	}
	// }}}
	// {{{ selet_items2
	function selet_items2($table, $column1, $column2, $field) {

		$utf = $this->db->prepare('SET NAMES utf8');
		$utf->execute();

		$myquery = "select distinct `$column1`,`$column2` from `$table`";
		$stmt    = $this->db->prepare($myquery);
		$stmt->execute();

		$arr = array();
		$t   = NULL;
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$arr[$field][$row[$column1]] = $row[$column2];
			$t 						     = $row[$column1];
		}

		if (is_null($t)) {
			return;
		}

		return $arr[$field];
	}
	// }}}
	// {{{ selet_items_parent
	function selet_items_parent($table, $id, $parent) {

		$utf = $this->db->prepare('SET NAMES utf8');
		$utf->execute();

		$sth = $this->db->prepare("SELECT * FROM `$table` where $parent = 0  ORDER BY `$id` asc");
		$sth->execute();
		$category = $sth->fetchAll();
		foreach ($category as $key => $value) {
			$sth = $this->db->prepare("SELECT * FROM `$table` where $parent = " . $value[$id] . "  ORDER BY `$id` DESC");
			$sth->execute();
			$category[$key]['sub'] = $sth->fetchAll();
		}

	    return $category;
	}
	// }}}
	// {{{ checkEmail
	function checkEmail($email22) {
		if (!preg_match("/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i", $email22)) {
			return false;
		} else {
		   return true;
		}
	}
	// }}}
	// {{{ return_editor
	function return_editor() {

		return 'tinyMCE.init({
				// General options
				mode : "textareas",
				editor_selector : "textarea",
				theme : "advanced",

				plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

				// Theme options
				theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
				theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
				theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,

				// Example content CSS (should be your site CSS)
				//content_css : "css/example.css",

				// Drop lists for link/image/media/template dialogs
				template_external_list_url : "js/template_list.js",
				external_link_list_url : "js/link_list.js",
				external_image_list_url : "js/image_list.js",
				media_external_list_url : "js/media_list.js",

				// Replace values for the template plugin
				template_replace_values : {
					username : "Some User",
					staffid : "991234"
				}
			})';
	}
	// }}}
	// {{{ selet_items3
	function selet_items3($table, $column1, $column2, $field, $condition) {

		$utf = $this->db->prepare('SET NAMES utf8');
		$utf->execute();

		$myquery = "select distinct `$column1`,`$column2` from `$table` where " . $condition;
		$stmt    = $this->db->prepare($myquery);
		$stmt->execute();

		$arr = array();
		$t   = NULL;
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$arr[$field][$row[$column1]] = $row[$column2];
			$t 							 = $row[$column1];
		}

		if (is_null($t)) {
			return;
		}

		return $arr[$field];
	}
	// }}}
	// {{{ upload_img
	function upload_img($t, $val) {
	}
	// }}}
	// {{{ join_tables
 	function join_tables($tables, $fields, $add_con = '', $show_clmn = '*') {

		$str_tables = '';
		$str_con    = '';
		$str_clmn   = '';
		$tbl	    = array();
		$fld 	    = array();

		foreach ($tables as $i => $val) {
			$tbl[] = $i;
			$tbl[] = $val;
		}

		foreach ($fields as $i => $val) {
			$fld[] = $i;
			$fld[] = $val;
		}

		$num = sizeof($tbl);
	    $num--;
		foreach ($tbl as $i => $val) {
			if ($i == $num) {
				$str_tables .= '`' . $val . '`';
			} else {
				$str_tables .= '`' . $val . '`,';
			}
		}

		foreach ($fld as $i => $val) {
			if ($i % 2 == 0) {
				$str_con .= 'and' . $tbl[$i] . '.' . $fld[$i] . '=';
			} else {
				$str_con .= '' . $tbl[$i] . '.' . $fld[$i] . '';
			}
		}

		$str_con = substr($str_con, 3);
	    if ($add_con != '') {
			$str_con .= 'and';
			foreach ($add_con as $i => $val) {
				$str_con .= '\'' . $i . '\' = \'' . $val . '\'and';
			}
			$str_con = substr($str_con, -3);
		}

		if ($show_clmn != '*') {
			$num = count($show_clmn);
			$num--;
			foreach ($show_clmn as $i => $val) {
				if ($i == $num) {
					$str_clmn .= '\'' . $show_clmn[$i] . '\'';
				} else {
					$str_clmn .= '\'' . $show_clmn[$i] . '\'';
				}
			}
		}

		$str  = "select $show_clmn from $str_tables where $str_con";
	    $stmt = $this->db->prepare($str);
		$stmt->execute();

	    return $stmt->fetchAll();
	}
	// }}}

	// {{{ buildGoogleDriveService
	// Google Drive functions
	/**
	 * Google Drive Build Service
	 */
	function buildGoogleDriveService() {

		$DRIVE_SCOPE			  = 'https://www.googleapis.com/auth/drive';
		$SERVICE_ACCOUNT_EMAIL		  = '1090424424188-mlu1692fseovcrg7clfeojrfkpgj358b@developer.gserviceaccount.com';
		$SERVICE_ACCOUNT_PKCS12_FILE_PATH = 'application/googleDrive/2ce3ce00f0b9b6e11f3d5bf087f88f7a22d7d577-privatekey.p12';

		$key  = file_get_contents($SERVICE_ACCOUNT_PKCS12_FILE_PATH);
		$auth = new Google_AssertionCredentials($SERVICE_ACCOUNT_EMAIL, array($DRIVE_SCOPE), $key);

		$client = new Google_Client();
		$client->setUseObjects(true);
		$client->setAssertionCredentials($auth);

		return new Google_DriveService($client);
	}
	// }}}

	// {{{ insertGoogleDriveFile
	/**
	 * Insert new file.
	 *
 	 * @param Google_DriveService $service Drive API service instance.
	 * @param string $title Title of the file to insert, including the extension.
	 * @param string $description Description of the file to insert.
	 * @param string $parentId Parent folder's ID.
	 * @param string $mimeType MIME type of the file to insert.
	 * @param string $filename Filename of the file to insert.
	 * @return Google_DriveFile The file that was inserted. NULL is returned if an API error occurred.
	 */
	function insertGoogleDriveFile($service, $title, $description, $parentId, $mimeType, $filename) {
		$file = new Google_DriveFile();
		$file->setTitle($title);
		$file->setDescription($description);
		$file->setMimeType($mimeType);

		// Set the parent folder.
		if ($parentId != null) {
			$parent = new ParentReference();
			$parent->setId($parentId);
			$file->setParents(array($parent));
		}

		try {
			$data = file_get_contents($filename);
			$createdFile = $service->files->insert($file, array(
											'data'     => $data,
											'convert'  => true,
											'mimeType' => $mimeType));

			// Uncomment the following line to print the File ID
			// print 'File ID: %s' % $createdFile->getId();

			return $createdFile;

		} catch (Exception $e) {
			print "An error occurred: " . $e->getMessage();
		}
	}
	// }}}

	// {{{ downloadGoogleDriveFile
	/**
	 * Google Drive Download File
	 */
	function downloadGoogleDriveFile($service, $file) {
		$downloadUrl = $file->exportLinks["application/pdf"];
		if ($downloadUrl) {
			$request = new Google_HttpRequest($downloadUrl, 'GET', null, null);
			$httpRequest = Google_Client::$io->authenticatedRequest($request);
			if ($httpRequest->getResponseHttpCode() == 200) {
				return $httpRequest->getResponseBody();
			} else {
				// An error occurred.
				return null;
			}
		} else {
			// The file doesn't have any content stored on Drive.
			return null;
		}
	}
	// }}}

	// {{{ updateRowOrder
	function updateRowOrder() {

		$orders = (!empty($_POST['orders'])) ? $_POST['orders'] : null;
		if ($orders) {
			foreach ($orders as $key => $order) {
				$query = "update `" . $this->source . "` set `order` = " . $order["order"] . " where `id` = " . $order["id"];
				$stmt  = $this->db->prepare($query);
				$stmt->execute();
			}
		}
	}
	// }}}
};
?>
