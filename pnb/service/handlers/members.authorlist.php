<?php

#
# Get author list:
#
# /members/authorlist/format:[APS,IOP,ARXIV]
#

function members_authorlist_handler($params) {
  $cnf =& ServiceConfig::Instance();
  $db =& ServiceDb::Instance('phonebook_api');

  

  return json_encode( $authors );
}
