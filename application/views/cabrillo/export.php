<?php
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="'.$callsign.'-'.$contest_id.'-'.date('dmY-Hi').'.cbr"');

$CI =& get_instance();
$CI->load->library('Cabrilloformat');

echo $CI->cabrilloformat->header($contest_id, $callsign, $claimed_score, $operators, $club, $name, $address1, $address2, $address3, $soapbox);
foreach ($qsos->result() as $row) {
	echo $CI->cabrilloformat->qso($row);
}
echo $CI->cabrilloformat->footer();