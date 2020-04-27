<?php

# 
# Create member (one at a time) details: 
#	status = user status
#	field ids => fields to be inserted
#
# /members/create
#
# JSON body: 
# "data": {
#	"status": "active|onhold|inactive",
#	"fields": {
#				<field_id_1> : <new_value>,
#				...
#				<field_id_N> : <new_value>
#			}
# }

function members_create_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $old_db =& ServiceDb::Instance('old_phonebook');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }


  if ( !isset($params['data']['status']) || empty($params['data']['status'])
		|| !isset($params['data']['fields']) || empty($params['data']['fields']) || !is_array($params['data']['fields'])
	) { return json_encode(false); }

  $fields = get_members_fields(); // array ( <id1> : {field_descriptor1}, <id2> : {field_descriptor2} );

  // create member in the `members` table:
  $query = 'INSERT INTO `phonebook_api`.`members` (`status`, `status_change_date`, `status_change_reason`, `last_update_date`, `join_date`) 
	VALUES ("'.$db->Escape($params['data']['status']).'", NOW(), "new user created", NOW(), NOW() )';
  $db->Query($query);
  $id = $db->LastID();

  // update last_update table
  $query = 'UPDATE `phonebook_api`.`last_update` SET members = NOW()';
  $db->Query($query);

  // populate fields in `members_data_dates`/_data_strings/_data_ints tables:
  if (!empty($id)) {

    $old_query = 'INSERT INTO `starweb`.`members` (`Id`, `isAuthor`, `isShifter`, `isExpert`, `isJunior`, `isEmeritus`, `JoinDate`, `LeaveDate`) VALUES ('.$id.', "N", "N", "N", "N", "N",  NOW(), "")';
    $old_db->Query($old_query);

	foreach($params['data']['fields'] as $k => $v) {
		$query = '';

        // backpropagation: k = field_id, v = value
        $fixed_name = $fields[intval($k)]['name_fixed'];
        $old_name = convert_members_new_old($fixed_name);
        $old_query = '';
		$history_field = '';

		switch ($fields[$k]['type']) {
			case 'string':
				$history_field = 'string';
				$query = 'INSERT INTO `phonebook_api`.`members_data_strings` (`members_id`, `members_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', "'.$db->Escape($v).'")';
                if (!empty($old_name)) {
                        $old_query = 'UPDATE `starweb`.`members` SET `'.$old_name.'` = "'.$db->Escape($v).'" WHERE Id = '.intval($id);
                        $old_db->Query($old_query);
                }
				break;
			case 'int':
				$history_field = 'int';
				$query = 'INSERT INTO `phonebook_api`.`members_data_ints` (`members_id`, `members_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', '.intval($v).')';
                if (!empty($old_name)) {
                        $old_query = 'UPDATE `starweb`.`members` SET `'.$old_name.'` = '.intval($v).' WHERE Id = '.intval($id);
                        $old_db->Query($old_query);
                }
				break;
			case 'date':
				$history_field = 'date';
				$query = 'INSERT INTO `phonebook_api`.`members_data_dates` (`members_id`, `members_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', "'.$db->Escape($v).'")';
                if (!empty($old_name)) {
                        $old_query = 'UPDATE `starweb`.`members` SET `'.$old_name.'` = "'.$db->Escape($v).'" WHERE Id = '.intval($id);
                        $old_db->Query($old_query);
                }
				break;
			default:
				break;
		}
		if (!empty($query)) {
			$db->Query($query);
		}

        // history support:
        $query = 'INSERT INTO `phonebook_api`.`members_history` (`members_id`, `members_fields_id`, `date`, `value_to_'.$history_field.'`) VALUES ('.intval($id).', '.intval($k).', NOW(), "'.$db->Escape($v).'" )';
        //$db->Query($query);
		//file_put_contents('/tmp/dmitry-debug-'.time(0).'.txt', $query, FILE_APPEND);

	}
  } else {
 	return json_encode(false);
  }

  return json_encode(true);
}
