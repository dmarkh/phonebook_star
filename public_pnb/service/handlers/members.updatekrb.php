<?php

# 
# Update member details
#
# /members/update
#
# JSON body: 
# "data": {
#	"<id>" : {
#				<field_id_1> : <new_value>,
#				...
#				<field_id_N> : <new_value>
#			},
#	"<id_N>" : { <same as above> }
# }

function members_updatekrb_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $old_db =& ServiceDb::Instance('old_phonebook');

  $data = $params;
  // check for data
  if ( !isset($data['fields']) || !is_array($data['fields']) ) { return json_encode(array( 'error' => true, 'message' => 'no changes found' )); }

  // expect: array 'data' ( 'memberid' => 147, 'rcflogin' => 'dmitry', 'rcfpass' => 'test', 'fields' => array( 1 => '', 2 => '' , ... N => '' )

  // check for login/pass
  if ( empty($data['memberid']) || empty($data['rcflogin']) || empty($data['rcfpass']) ) { return json_encode(array( 'error' => true, 'message' => 'no credentials found' )); }

  $member_id = intval($data['memberid']);

  $fields = get_members_fields(); // array ( <id1> : {field_descriptor1}, <id2> : {field_descriptor2} );

  // field id for the 'RCF login name' field
  $rcf_login_field_id = false;
  foreach( $fields as $k => $v ) {
	if ( $v['name_fixed'] == 'rcf_login' ) { $rcf_login_field_id = $k; break; }
  }
  if ( !$rcf_login_field_id ) { return json_encode( array( 'error' => true, 'message' => 'no rcf field id found' ) ); }

  // get rcf login on file for member X:
  include_once('members.get.php');
  $record = json_decode( members_get_handler( array( 'id' => intval($data['memberid']) ) ), true );
  $rcf_id_on_file = $record['fields'][$rcf_login_field_id];

  // check for empty rcf login on file
  if ( empty($rcf_id_on_file) ) { return json_encode( array( 'error' => true, 'message' => 'member has no rcf id on file' ) ); }
  if ( $rcf_id_on_file != $data['rcflogin'] ) { return json_encode( array( 'error' => true, 'message' => 'wrong rcf id' ) ); }

  // check for Kerberos auth:
  $princ = new KRB5CCache();
  try { $princ->initPassword($data['rcflogin'], $data['rcfpass']); } catch (Exception $e) { }
  $res = $princ->getEntries();
  if ( empty($res) ) { return json_encode( array( 'error' => true, 'message' => 'authentication failed' ) ); }

  // save data
	foreach($data['fields'] as $field_id => $field_value) {
		$history_field = '';
		$history_to_value = '';
		$history_from_value = '';
		switch ( $fields[$field_id]['type'] ) {
			case 'string':
				$history_field = 'string';
				$history_to_value = '"'.$db->Escape($field_value).'"';

				$query = 'SELECT `value` FROM `phonebook_api`.`members_data_'.$history_field.'s` WHERE `members_id` = '.intval($member_id).' AND `members_fields_id` = '.intval($field_id).' LIMIT 1';
				$result = $db->Query($query);
				$history_from_value = '"'.$db->Escape($result['value'][0]).'"';
				if (!empty($result)) {
					$history_from_value = '"'.$db->Escape($result['value'][0]).'"';
				} else {
					$history_from_value = '""';
				}

				$query = 'INSERT INTO `phonebook_api`.`members_data_strings` (`members_id`, `members_fields_id`, `value`) VALUES ('.intval($member_id).', '.intval($field_id).', "'.$db->Escape($field_value).'") ON DUPLICATE KEY UPDATE `value` = "'.$db->Escape($field_value).'"';
				$error = $db->Query($query);
				if ( $error && !empty($error['error']) ) {
				  return json_encode( array( 'error' => true, 'message' => 'db error: '.$error['error'].', query: '.$query ) );
				}

				// backpropagation - strings:
				$fixed_name = $fields[$field_id]['name_fixed'];
				$old_name = convert_members_new_old($fixed_name);
				if (!empty($old_name)) {
				    $query = 'UPDATE `starweb`.`members` SET `'.$old_name.'` = "'.$old_db->Escape($field_value).'" WHERE `Id` = '.$member_id;
				    $error = $old_db->Query($query);
	  				if ( $error && !empty($error['error']) ) {
					  return json_encode( array( 'error' => true, 'message' => 'db error: '.$error['error'].', query: '.$query ) );
					}
				}

				break;

			case 'date':
				$history_field = 'date';
				$history_to_value = '"'.$db->Escape($field_value).'"';

				$query = 'SELECT `value` FROM `phonebook_api`.`members_data_'.$history_field.'s` WHERE `members_id` = '.intval($member_id).' AND `members_fields_id` = '.intval($field_id).' LIMIT 1';
				$result = $db->Query($query);
				if (!empty($result)) {
					$history_from_value = '"'.$db->Escape($result['value'][0]).'"';
				} else {
					$history_from_value = '"0000-00-00 00:00:00"';
				}

				$query = 'INSERT INTO `phonebook_api`.`members_data_dates` (`members_id`, `members_fields_id`, `value`) VALUES ('.intval($member_id).', '.intval($field_id).', "'.$db->Escape($field_value).'") ON DUPLICATE KEY UPDATE `value` = "'.$db->Escape($field_value).'"';
				$error = $db->Query($query);
				if ( $error && !empty($error['error']) ) {
				  return json_encode( array( 'error' => true, 'message' => 'db error: '.$error['error'].', query: '.$query ) );
				}

				// backpropagation - dates:
				$fixed_name = $fields[$field_id]['name_fixed'];
				$old_name = convert_members_new_old($fixed_name);
				if (!empty($old_name)) {
				    $query = 'UPDATE `starweb`.`members` SET `'.$old_name.'` = "'.$old_db->Escape($field_value).'" WHERE `Id` = '.$member_id;
				    $error = $old_db->Query($query);
					if ( $error && !empty($error['error']) ) {
					  return json_encode( array( 'error' => true, 'message' => 'db error: '.$error['error'].', query: '.$query ) );
					}
				}
				break;

			case 'int':
				$history_field = 'int';
				$history_to_value = intval($field_value);

				$query = 'SELECT `value` FROM `phonebook_api`.`members_data_'.$history_field.'s` WHERE `members_id` = '.intval($member_id).' AND `members_fields_id` = '.intval($field_id).' LIMIT 1';
				$result = $db->Query($query);
				$history_from_value = intval($result['value'][0]);
				if (!empty($result)) {
					$history_from_value = intval($result['value'][0]);
				} else {
					$history_from_value = 0;
				}

				$query = 'INSERT INTO `phonebook_api`.`members_data_ints` (`members_id`, `members_fields_id`, `value`) VALUES ('.intval($member_id).', '.intval($field_id).', '.intval($field_value).') ON DUPLICATE KEY UPDATE `value` = '.intval($field_value);
				$error = $db->Query($query);
				if ( $error && !empty($error['error']) ) {
				  return json_encode( array( 'error' => true, 'message' => 'db error: '.$error['error'].', query: '.$query ) );
				}

				// backpropagation - ints:
				$fixed_name = $fields[$field_id]['name_fixed'];
				$old_name = convert_members_new_old($fixed_name);
				if (!empty($old_name)) {
				    $query = 'UPDATE `starweb`.`members` SET `'.$old_name.'` = '.intval($field_value).' WHERE `Id` = '.$member_id;
				    $error = $old_db->Query($query);
					if ( $error && !empty($error['error']) ) {
					  return json_encode( array( 'error' => true, 'message' => 'db error: '.$error['error'].', query: '.$query ) );
					}
				}

				break;

			default:
				break;
		}
		// history support:
		$query = 'INSERT INTO `phonebook_api`.`members_history` (`members_id`, `members_fields_id`, date, `value_from_'.$history_field.'`, `value_to_'.$history_field.'`) 
			VALUES ('.intval($member_id).', '.intval($field_id).', NOW(), '.$history_from_value.', '.$history_to_value.')';
		$db->Query($query);
	}

  return json_encode(true);
}
