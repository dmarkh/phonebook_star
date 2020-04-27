<?php

# Get active members count per institution:
# /members/count

function members_count_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');

  $status_query = '`status` = "active"';
//  $status_query = '1';

  $query = 'SELECT * FROM `phonebook_api`.`institutions` WHERE '.$status_query;
  $inst = $db->Query($query);
  $institutions = array();
  foreach($inst as $k => $v) {
	$institutions[$v['id']] = true;
  }
//  file_put_contents('/tmp/phonebook_test.txt', print_r($institutions, true));

  
//  $status_query = '1';

  $query = 'SELECT * FROM `phonebook_api`.`members` WHERE '.$status_query;
  $mem = $db->Query($query);

  $members = array();

  foreach($mem as $k => $v) {
	$members[$v['id']] = true;
  }

  $query = 'SELECT * FROM `phonebook_api`.`members_data_dates` where `members_fields_id` = 85';
  $res = $db->Query($query);
  $time = time();
  $cnt = 0;
  foreach($res as $k => $v) {
	if ($v['members_fields_id'] == 85) {
	  if ( !empty($v['value']) && $v['value'] != '0000-00-00 00:00:00' && $time > strtotime($v['value']) ) {
		unset( $members[$v['members_id']] );
	  }
	}
  }

//  file_put_contents('/tmp/phonebook_test.txt', count($members));

  $query = 'SELECT * FROM `phonebook_api`.`members_data_ints` where `members_fields_id` = 17';
  $res = $db->Query($query);

  $memlist = array();

  $count = array();
  //file_put_contents('/tmp/phonebook_test.txt', count($res));
  foreach($res as $k => $v) {
	if ($members[ $v['members_id'] ] != true) { continue; }
	if ($institutions[ $v['value'] ] != true) { continue; }
	if (empty($count[$v['value']])) { $count[$v['value']] = 0; }
	$count[$v['value']] += 1;
	$memlist[$v['members_id']] = $v['members_id'];
  }

//  file_put_contents('/tmp/phonebook_test.txt', array_sum($count));

  $max = 0;
  $min = 99999;
  foreach($count as $k => $v) {
	if ($v > $max) { $max = $v; };
	if ($v < $min) { $min = $v; };
  }

  return json_encode( array( 'data' => $count, 'max' => $max, 'min' => $min ) );
}
