<?php

# 
# Create institution (one at a time) details: 
#	status = institution status
#	field ids => fields to be inserted
#
# /institutions/create
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

function institutions_create_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $old_db =& ServiceDb::Instance('old_phonebook');

  $data = $params['data'];
  if ( !isset($params['data']) || !is_array($data) ) { return json_encode(false); }

  if ( !isset($params['data']['status']) || empty($params['data']['status'])
		|| !isset($params['data']['fields']) || empty($params['data']['fields']) || !is_array($params['data']['fields'])
	) { return json_encode(false); }

  $fields = get_institutions_fields(); // array ( <id1> : {field_descriptor1}, <id2> : {field_descriptor2} );

  // create institution in the `institutions` table:
  $query = 'INSERT INTO `phonebook_api`.`institutions` (`status`, `status_change_date`, `status_change_reason`, `last_update_date`, `join_date`) 
	VALUES ("'.$db->Escape($params['data']['status']).'", NOW(), "new institution created", NOW(), NOW() )';
  $db->Query($query);
  $id = $db->LastID();

  // update last_update table
  $query = 'UPDATE `phonebook_api`.`last_update` SET institutions = NOW()';
  $db->Query($query);

  // populate fields in `institutions_data_dates`/_data_strings/_data_ints tables:
  if (!empty($id)) {

    // backpropagation:
	$old_query = 'INSERT INTO `starweb`.`institutions` (`Id`, `InstitutionName`, `Organization`,`Address1`,`Address2`,`City`,`State`,`Country`,`PostCode`,`BnlOffice`,`InstitutionUrl`,`GroupUrl`,`JoinDate`,`LeaveDate`,`GroupName`,`LatexAffiliation`,`NameToSortBy`) VALUES ('.$id.', "", "", "", "", "",  "", "", "", "", "", "", NOW(), "", "", "", "")';
	$old_db->Query($old_query);

	foreach($params['data']['fields'] as $k => $v) {
		$query = '';

		// backpropagation: k = field_id, v = value
        $fixed_name = $fields[intval($k)]['name_fixed'];
        $old_name = convert_institutions_new_old($fixed_name);
		$old_query = '';

		switch ($fields[$k]['type']) {
			case 'string':
				$query = 'INSERT INTO `phonebook_api`.`institutions_data_strings` (`institutions_id`, `institutions_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', "'.$db->Escape($v).'")';
				// backpropagation:
				if (!empty($old_name)) {
				 		$old_query = 'UPDATE `starweb`.`institutions` SET `'.$old_name.'` = "'.$old_db->Escape($v).'" WHERE Id = '.intval($id);
						$old_db->Query($old_query);
				}
				break;
			case 'int':
				$query = 'INSERT INTO `phonebook_api`.`institutions_data_ints` (`institutions_id`, `institutions_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', '.intval($v).')';
				// backpropagation:
				if (!empty($old_name)) {
				 		$old_query = 'UPDATE `starweb`.`institutions` SET `'.$old_name.'` = '.intval($v).' WHERE Id = '.intval($id);
						$old_db->Query($old_query);
				}
				break;
			case 'date':
				$query = 'INSERT INTO `phonebook_api`.`institutions_data_dates` (`institutions_id`, `institutions_fields_id`, `value`) VALUES ('.intval($id).', '.intval($k).', "'.$db->Escape($v).'")';
				// backpropagation:
				if (!empty($old_name)) {
				 		$old_query = 'UPDATE `starweb`.`institutions` SET `'.$old_name.'` = "'.$old_db->Escape($v).'" WHERE Id = '.intval($id);
						$old_db->Query($old_query);
				}
				break;
			default:
				break;
		}
		if (!empty($query)) {
			$db->Query($query);
		}
	}
  } else {
 	return json_encode(false);
  }

  return json_encode(true);
}
