<?php

#
# Deletes member
#
# /members/delete/id:[X]
#

/*
function members_delete_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $id = intval($params['id']);
  $query = 'DELETE FROM `phonebook_api`.`members` WHERE `id` = '.$id;
//  $res = $db->Query($query);
  $query = 'DELETE FROM `phonebook_api`.`members_data_dates` WHERE `members_id` = '.$id;
//  $res = $db->Query($query);
  $query = 'DELETE FROM `phonebook_api`.`members_data_ints` WHERE `members_id` = '.$id;
//  $res = $db->Query($query);
  $query = 'DELETE FROM `phonebook_api`.`members_data_strings` WHERE `members_id` = '.$id;
//  $res = $db->Query($query);
  $query = 'DELETE FROM `phonebook_api`.`members_history` WHERE `members_id` = '.$id;
//  $res = $db->Query($query);
  return json_encode(true);
}
*/