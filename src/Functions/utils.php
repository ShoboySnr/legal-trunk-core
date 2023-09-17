<?php


function legal_trunk_hide_email($email) {
	// extract email text before @ symbol
	$em = explode("@", $email);

	$name = implode( '.', array_slice($em, 0, count($em) - 1) );

	$length = floor(strlen($name) / 2);
	
	$dot_em = explode(".", end($em));
	$end_length = strlen(end($em)) - 2;
	
	return substr($name, 0, 3) . str_repeat('*', $length) . "@" . substr(end($em), 0, 2). str_repeat('*', $end_length). substr($dot_em[0], strlen($dot_em[0]) - 2, strlen($dot_em[0])). '.'. end($dot_em);
}


function get_the_country_flag($country_slug = '' ) {
	if(empty($country_slug)) return '';
	
	if(!file_exists(LOCAL_TRUNK_CORE_SYSTEM_ASSETS_IMG_DIRECTORY.'/countries/'.$country_slug.'.png')) return '';
	
	$flag = LOCAL_TRUNK_CORE_SYSTEM_ASSETS_IMG_URL.'/countries/'.$country_slug.'.png';
	
	do_action('qm/debug', sprintf('<img src="%s" class="flag" alt="%s" />', $flag, $country_slug));
	return sprintf('<img src="%s" class="flag" alt="" />', $flag);
}



function legal_trunk_core_convert_num_to_words(float $amount)
{
	$amount_after_decimal = round($amount - ($num = floor($amount)), 2) * 100;
	
	$amt_hundred = null;
	$count_length = strlen($num);
	$x = 0;
	$string = array();
	$change_words = array(0 => '', 1 => 'One', 2 => 'Two',
	                      3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
	                      7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
	                      10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
	                      13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
	                      16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
	                      19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
	                      40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
	                      70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
	$here_digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
	while( $x < $count_length ) {
		$get_divider = ($x == 2) ? 10 : 100;
		$amount = floor($num % $get_divider);
		$num = floor($num / $get_divider);
		$x += $get_divider == 10 ? 1 : 2;
		if ($amount) {
			$add_plural = (($counter = count($string)) && $amount > 9) ? 's' : null;
			$amt_hundred = ($counter == 1 && $string[0]) ? ' and ' : null;
			$string [] = ($amount < 21) ? $change_words[$amount].' '. $here_digits[$counter]. $add_plural.'
     '.$amt_hundred:$change_words[floor($amount / 10) * 10].' '.$change_words[$amount % 10]. '
     '.$here_digits[$counter].$add_plural.' '.$amt_hundred;
		} else $string[] = null;
	}
	return implode('', array_reverse($string));
}