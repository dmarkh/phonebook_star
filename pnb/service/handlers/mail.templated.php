<?php
ini_set("memory_limit","512M");

function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
  $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
  mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
}

function prepare_description($m, $mfields, $inst, $ifields, $mfieldgrps) {

  $desc = array('email' => '', 'name' => '', 'data' => array() );
  $desc['email'] = $m['fields'][20];
  $desc['name'] = $m['fields'][1].' '.$m['fields'][3];

  if ( empty($m['fields'][10]) && empty($m['fields'][11]) && empty($m['fields'][12]) ) {
	  $m['fields'][10] = $inst[ $m['fields'][17] ]['fields'][10];
	  $m['fields'][11] = $inst[ $m['fields'][17] ]['fields'][11];
  }

  if ( empty($m['fields'][4]) )  { $m['fields'][4]  = $m['fields'][3]; }
  if ( empty($m['fields'][13]) ) { $m['fields'][13] = $inst[ $m['fields'][17] ]['fields'][12]; }
  if ( empty($m['fields'][14]) ) { $m['fields'][14] = $inst[ $m['fields'][17] ]['fields'][13]; }
  if ( empty($m['fields'][15]) ) { $m['fields'][15] = $inst[ $m['fields'][17] ]['fields'][14]; }
  if ( empty($m['fields'][16]) ) { $m['fields'][16] = $inst[ $m['fields'][17] ]['fields'][15]; }
  if ( empty($m['fields'][25]) ) { $m['fields'][25] = ( $inst[ $m['fields'][17] ]['fields'][8] ) ? ( $inst[ $m['fields'][17] ]['fields'][8] ) : ( $inst[ $m['fields'][17] ]['fields'][7] ); }

//  foreach( $m['fields'] as $k => $v ) {
//	if ( $k == 84 || $k == 85 || $k == 86 || $k == 5 
//	  || $k == 40 || $k == 41 || $k == 42 || $k == 43 || $k == 44 || $k == 45 || $k == 46 ) { continue; }
  foreach( $mfields as $k => $v ) {
	if ( $k == 17 ) {
	  $desc['data'][] = array( 0 => $mfields[$k]['name_desc'], 1 => $inst[ $m['fields'][17] ]['fields'][1] );
	} else if ( $k == 89 ) {
		$tmpinst = explode(',', trim( $m['fields'][89] ) );
		$tmpinstlist = array();
		foreach ($tmpinst as $tmpk => $v) {
			$v = intval($v);
			$tmpinstlist[] = $inst[ $v ]['fields'][1];
		}
		$tmpinstlist = implode(', ', $tmpinstlist);
		$desc['data'][] = array( 0 => $mfields[$k]['name_desc'], 1 => $tmpinstlist );
	} else {
		$desc['data'][] = array( 0 => $mfields[$k]['name_desc'], 1 => ( !empty( $m['fields'][$k] ) ? $m['fields'][$k] : 'N/A' ) );
	}
  }

  return $desc;
}

function send_mail_to_member( $desc, $params ) {
    $to = trim( $desc['email'] );
	if ( empty($to) || $to == 'undefined' ) { return; } // error, no email found
    $from = 'Liz Mogavero <mogavero@bnl.gov>';
	if ( $params && isset( $params['replyto'] ) ) {
	  $params['replyto'] = intval( $params['replyto'] );
	  switch( $params['replyto'] ) {
		case 1:
		  $from = '"Liz Mogavero" <mogavero@bnl.gov>';
		  break;
		case 2:
		  $from = '"Nieves, Rachel" <irachel@bnl.gov>';
		  break;
		case 3:
		  $from = '"Jerome LAURET" <jlauret@bnl.gov>';
		  break;
		case 4:
		  $from = '"Van Buren, Gene" <gene@bnl.gov>';
		  break;
		case 0:
		  $from = '"Do Not Reply" <no-reply@star.bnl.gov>';
		default:
		  break;
	  }
	}
    $subject = ( !empty($params) && !empty( $params['subject'] ) ) ? $params['subject'] : 'STAR PhoneBook information - please review';

	$message = 'Error occured, please ignore.';
	if ( !empty($params) && !empty($params['template']) ) {

	  $template = str_replace( '%name%', $desc['name'], $params['template'] );
	  $phonebook_info = '*****************************************'."\n";
	  foreach($desc['data'] as $k => $v) {
		$phonebook_info .= $v[0].' = '.$v[1]." -> \n";
	  }
	  $phonebook_info .= '*****************************************'."\n";
	  $message = str_replace( '%phonebook_info%', $phonebook_info, $template );

	} else {

      $message = 'Dear '.$desc['name'].",\n\n"
	  .'This is an automatically generated Email showing below the information we have for you as a STAR user in the STAR PhoneBook.'
  	  .'If corrections are needed, please do not remove or delete the original information but'."\n"
  	  .'- correct the fields that are incorrect by adding the proper information after the arrow "->"'."\n"
  	  .'- reply to this Email with the corrected information'."\n\n"
  	  .'*****************************************'."\n";
	  foreach($desc['data'] as $k => $v) {
		$message .= $v[0].' = '.$v[1]." -> \n";
	  }
	  $message .= '*****************************************'."\n\n"
  	  .'We appreciate your efforts to keep our records accurate.'."\n"
	  .'STAR automated phonebook system';

	}

    if ( isset( $from ) and strlen( $from) ) {
        $additional = "From: " . $from;
    }

	mail_utf8($to,$subject,$message,$additional);

	return array('success' => true);
}

function mail_templated_handler($params) {

  $mem = file_get_contents('http://www.star.bnl.gov/public_pnb/service/?q=/members/list/status:active/details:full');
  $mem = json_decode($mem, true);

  $mfields = file_get_contents('http://www.star.bnl.gov/public_pnb/service/?q=/service/list/object:fields/type:members');
  $mfields = json_decode($mfields, true);

  unset($mfields[84]);
  unset($mfields[85]);
  unset($mfields[86]);

  //unset($mfields[5]);
  unset($mfields[87]);

  unset($mfields[40]);
  unset($mfields[41]);
  unset($mfields[42]);
  unset($mfields[43]);
  unset($mfields[44]);
  unset($mfields[45]);
  unset($mfields[46]);

  $mfieldgrps = file_get_contents('http://www.star.bnl.gov/public_pnb/service/?q=/service/list/object:fieldgroups/type:members');
  $mfieldgrps = json_decode($mfieldgrps, true);

  $inst = file_get_contents('http://www.star.bnl.gov/public_pnb/service/?q=/institutions/list/status:active/details:full');
  $inst = json_decode($inst, true);

  $ifields = file_get_contents('http://www.star.bnl.gov/public_pnb/service/?q=/service/list/object:fields/type:institutions');
  $ifields = json_decode($ifields, true);

  $memlist = explode( ',', $params['memlist'] );

  $selected_members = array();
  foreach ( $memlist as $k => $v ) {
	if ( !empty( $mem[$v] ) ) {
		$selected_members[$v] = $mem[$v];
	}
  }

  // process one by one:
  $cnt_desc = 0; $cnt_mem = 0;
  foreach( $selected_members as $k => $v ) {
	$desc = prepare_description($v, $mfields, $inst, $ifields, $mfieldgrps);
	if ( empty($desc) || !is_array($desc) ) { continue; }
	$cnt_desc += 1;
	$result = send_mail_to_member( $desc, $params );
	if (!empty($result)) {
	  $cnt_mem += 1;
	}
  }
  echo json_encode( array( 'cnt_desc' => $cnt_desc, 'cnt_mem' => $cnt_mem ) );
  exit;
}
