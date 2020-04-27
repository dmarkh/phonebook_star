<?php

# 
# Get members details
#
# /members/get/id:[N]
#

function members_get_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');

  $id = intval($params['id']);

  $query = 'SELECT * FROM `phonebook_api`.`members` WHERE `id` = '.$id.' LIMIT 1';
  $memb = $db->Query($query);
  if (!empty($memb)) {
  	$memb = $memb[0];
  }
 // print_r($id); exit;

  $query = 'SELECT * FROM `phonebook_api`.`members_fields`';
  $fields_res = $db->Query($query);
  $fields = array();
  foreach($fields_res as $k => $v) {
	$fields[$v['id']] = $v;
  }

  $memb_fields = array();
  foreach(array('string','int','date') as $k => $v) {
  	$query = 'SELECT members_fields_id as field_id, value as field_value FROM `phonebook_api`.`members_data_'.$v.'s` WHERE members_id = '.$id;
	$res = $db->Query($query);
	if (empty($res)) continue;
	foreach($res as $k2 => $v2) {
		if ($v == 'int') { $v2['field_value'] = intval($v2['field_value']); }
		$memb_fields[$v2['field_id']] = $v2['field_value'];
	}
  }
  return json_encode(array('member' => $memb, 'fields' => $memb_fields));
}
