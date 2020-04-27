<?php

# 
# Get institutions history for institution <id>
#
# /institutions/history/id:[N]
#
#

function institutions_history_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');
  $id = intval($params['id']);
  $query = 'SELECT * FROM `phonebook_api`.`institutions_history` WHERE `institutions_id` = '.$id.' ORDER BY `date` DESC';
  $history = $db->Query($query);
  return json_encode($history);
}
