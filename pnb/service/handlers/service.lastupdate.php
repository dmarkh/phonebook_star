<?php

function service_lastupdate_handler($params) {
    $db =& ServiceDb::Instance('phonebook_api');
	$query = 'SELECT UNIX_TIMESTAMP(`members`) as members, UNIX_TIMESTAMP(`institutions`) as institutions FROM `phonebook_api`.`last_update` WHERE 1 LIMIT 1';
	$res = $db->Query($query);
	$res = $res[0];
	$res['members'] = intval( $res['members'] );
	$res['institutions'] = intval( $res['institutions'] );
    return json_encode( $res );
}