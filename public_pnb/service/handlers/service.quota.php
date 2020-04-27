<?php

# get quota per institution
# /service/quota
#
function sort_institutions($a, $b) {
    return strcasecmp($a['name'], $b['name']);
}

function sort_members($a, $b) {
    return strcasecmp($a['name'], $b['name']);
}

function service_quota_handler($params) {
  $result = array();

  // list active institutions / full
  // list active members / compact
  // convert

  include_once('institutions.list.php');
  include_once('members.list.php');

  $inst = json_decode( institutions_list_handler( array('status' => 'active') ), true );
  $mem  = json_decode( members_list_handler( array('status' => 'active', 'details' => 'full') ), true );

  $merge = true;
  if (!empty($params['merge']) && $params['merge'] == 'false') {
	$merge = false;
  }

  $institutions = array();

  foreach( $inst as $k => $v ) {
	$institutions[$k] = array( 'name' => $v['fields'][2], 'authors' => array() );
  }

  // select active members with LeaveDate within a year
  foreach( $mem as $k => $v ) {
	if ( empty($v['fields'][88]) ) { $v['fields'][88] = ''; }
	$inst_id = ( $inst[ $mem[$k]['fields'][17] ]['fields'][45] ? $inst[ $mem[$k]['fields'][17] ]['fields'][45] : $mem[$k]['fields'][17] );
	if ( $merge == false ) { $inst_id = $mem[$k]['fields'][17]; }
  	if ( empty($v['fields'][17]) || empty($institutions[$inst_id]) || ( !empty( $v['fields'][85] ) && $v['fields'][85] != '0000-00-00 00:00:00'
        && ( ( time(0) - strtotime($v['fields'][85]) ) > 31557600 ) ) ) {
        unset($mem[$k]);
	} else if ( strtolower($v['fields'][40]) != 'y' ) {
	  $institutions[$inst_id]['members'][] = array('name' => $v['fields'][3].' '.$v['fields'][1], 'login' => $v['fields'][88] );
  	} else {
	  $institutions[$inst_id]['authors'][] = array('name' => $v['fields'][3].' '.$v['fields'][1], 'login' => $v['fields'][88] );
	}
  }

  foreach($institutions as $k => $v) {
	if ( empty($v['authors']) && empty($v['members']) ) { unset($institutions[$k]); continue; }
	if ( !empty( $institutions[$k]['authors'] ) ) {
		uasort($institutions[$k]['authors'], 'sort_members');
	}
	if ( !empty($institutions[$k]['members']) ) {
		uasort($institutions[$k]['members'], 'sort_members');
	}
  }

  uasort($institutions, 'sort_institutions');

  switch ( $params['format'] ) {
	case 'xml':
	case 'XML':
	  return format_xml($institutions);
	  break;
	default:
	  return format_xml($institutions);
	  break;
  }
}

function format_xml($inst) {
  $output = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
  $output .= '<institutions>'."\n";
  $nologin = 0; $nologinnm = array();
  foreach($inst as $k => $v) {
	if ( !empty( $v['members'] ) ) {
		asort($v['members']);
	}
	$output .= '<institution name="'.$v['name'].'">'."\n";
	$output .= '<members>'."\n";
	if ( !empty($v['authors']) ) {
		foreach($v['authors'] as $k2 => $m) {
	  		if (empty($m['login'])) { $nologin += 1; $nologinnm[] = $m['name']; }
	  			$output .= '<member isAuthor="yes" login="'.$m['login'].'">'.$m['name'].'</member>'."\n";
			}
		}
	if ( !empty($v['members']) ) {
		foreach($v['members'] as $k2 => $m) {
	  		$output .= '<member isAuthor="no" login="'.$m['login'].'">'.$m['name'].'</member>'."\n";
		}
	}
	$output .= '</members>'."\n";
	$output .= '</institution>'."\n";
  }
  $output .= '</institutions>'."\n";
  //echo implode('<br>', $nologinnm); exit;
  //print_r($nologin); exit;
  header('Content-Type: text/xml');
  echo $output;
  exit;
}
