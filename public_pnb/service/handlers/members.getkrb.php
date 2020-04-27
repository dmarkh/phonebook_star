<?php

# 
# Get members details
#
# /members/get/id:[N]
#

function xor_string($string, $key) {
    $str_len = strlen($string);
    $key_len = strlen($key);

    for($i = 0; $i < $str_len; $i++) {
        $string[$i] = $string[$i] ^ $key[$i % $key_len];
    }

    return $string;
}

function members_getkrb_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');

  $id = intval($params['id']);
  $rcflogin = base64_decode(xor_string($params['rl'], 'S'));
  $rcfpass  = base64_decode(xor_string($params['rp'],'S')); // don't forget to urlencode if sent via GET

  $query = 'SELECT * FROM `phonebook_api`.`members` WHERE `id` = '.$id.' LIMIT 1';
  $memb = $db->Query($query);
  if (!empty($memb)) {
  	$memb = $memb[0];
  }

  if ( empty($memb) ) { return json_encode(array( 'error' => true, 'message' => 'no member found' )); }

  // get field id for the 'RCF login name' field
  $fields = get_members_fields();
  $rcf_login_field_id = false;
  foreach( $fields as $k => $v ) {
    if ( $v['name_fixed'] == 'rcf_login' ) { $rcf_login_field_id = $k; break; }
  }
  if ( !$rcf_login_field_id ) { return json_encode(array( 'error' => true, 'message' => 'member has no rcf login name on file' )); }

  // get member fields

  $fields = get_members_fields();

/*
  $query = 'SELECT * FROM `phonebook_api`.`members_fields`';
  $fields_res = $db->Query($query);
  $fields = array();
  foreach($fields_res as $k => $v) {
	$fields[$v['id']] = $v;
  }
*/

  foreach( $fields as $k => $v ) {
	if ( $v['privacy'] != 'public' ) { unset($fields[$k]); }
  }

  $memb_fields = array();
  foreach(array('string','int','date') as $k => $v) {
  	$query = 'SELECT members_fields_id as field_id, value as field_value FROM `phonebook_api`.`members_data_'.$v.'s` WHERE members_id = '.$id;
	$res = $db->Query($query);
	if (empty($res)) continue;
	foreach($res as $k2 => $v2) {
		if ($v == 'int') { $v2['field_value'] = intval($v2['field_value']); }
		if ( !isset($fields[ $v2['field_id'] ]) ) { continue; }
		$memb_fields[$v2['field_id']] = $v2['field_value'];
	}
  }

  // check rcf login match
  $rcf_login = $memb_fields[$rcf_login_field_id];

  if ( $rcf_login != $rcflogin ) { return json_encode(array( 'error' => true, 'message' => 'rcf login does not match request login' )); }

  // check for Kerberos auth:
  $princ = new KRB5CCache();
  try { $princ->initPassword( $rcflogin, $rcfpass ); } catch ( Exception $e ) { }
  $res = $princ->getEntries();
  if ( empty( $res ) ) { return json_encode(array( 'error' => true, 'message' => 'kerberos auth failed' )); }

  return json_encode(array('member' => $memb, 'fields' => $memb_fields));
}
