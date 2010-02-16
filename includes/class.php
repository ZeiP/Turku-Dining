<?php

class TurkuDining {
    var $db;
    var $usersettings;

	function TurkuDining($db) {
		$this->db = $db;
	}

    function fetch_menu($id) {
	    $sql = 'SELECT id, name, url, parser
		    FROM restaurants
		    WHERE id = ' . $id . '
		    ORDER BY name';
	    $res = $this->db->query($sql);
	    $row = $res->fetch();
	    $file = file_get_contents($row['url']);
	    if ($row['parser'] == 'unica') {
		    $file = iconv('iso-8859-15', 'utf-8', $file);
		    preg_match('/<div class="ruokalista">(.*)<div id="oikea_palsta">.*/s', $file, $matches);

		    $file = $matches[1];

		    $regexp_parts = array(
			    '<h1>\s*Lounaat viikolla (\d+)\s*<\/h1>', // Viikot
			    '<h3><a[^>]*>(.*?)<\/a>.*?<\/h3>', // P�iv�t
			    '<div class="ruoka">\s+<b>(.*?)<\/b>\s+(<br \/>\s+([LVGMPEH\(\)\/ ]+))?\s+(<p><b>Hinta:<\/b> ([^\<]*)<\/p>)?.*?<\/div>', // Ruoat
			    );
		    preg_match_all('/' . implode($regexp_parts, '|') . '/s', $file, $matches);
		    $return = $matches;
		    $this->handle_unica_menu($return, $row['id']);
	    }
	    elseif ($row['parser'] == 'fazeramica') {
		    preg_match('/<span>ruokalista<\/span>(.*)?<\/span>/s', $file, $matches);

		    $file = $matches[1];
		    
		    preg_match('/&nbsp;\s+(\d{1,2})\.(\d{1,2}\.?)\s*-\s*\d{1,2}\.\d{1,2}\.?/', $file, $date);
		    $date = array(date('Y'), str_pad($date[2], 2, 0, STR_PAD_LEFT), str_pad($date[1], 2, 0, STR_PAD_LEFT));
		    $regexp_parts = array(
			    '<span class="breadtext"><p>(.*?)<\/p><\/span>',
			    );
		    preg_match_all('/' . implode($regexp_parts, '|') . '/s', $file, $matches);
		    $rows = explode('<br />', $matches[1][0]);
		    foreach ($rows as $index => $row) {
			    $rows[$index] = explode('&nbsp;', $row);
		    }
		    $return = $rows;
		    $this->handle_fazeramica_menu($return, $date, $id);
	    }
	    else {
		    return FALSE;
	    }
    }

    function handle_unica_menu($array, $id) {
	    $currweek = NULL;
	    $currday = NULL;
	    $date = NULL;
	    $sql = 'INSERT INTO servings
		    (description, diet, price, date, restaurant_id)
		    VALUES(:descr, :diet, :price, DATE(:date), :resid)';
	    $q = $this->db->prepare($sql);
		$reset_dates = array();
	    for ($i = 0; $i <= count($array[0]); $i++) {
		    if (!empty($array[1][$i])) {
			    $currweek = $array[1][$i];
			    if ($currday !== NULL) {
				    $date = $this->format_date($this->weekday_to_numeral($currday), $currweek);
			    }
		    }
		    if (!empty($array[2][$i])) {
			    $currday = $array[2][$i];
			    if ($currweek !== NULL) {
				    $date = $this->format_date($this->weekday_to_numeral($currday), $currweek);
			    }
		    }
		    if (!empty($array[3][$i])) {
				if (!in_array($date, $reset_dates)) {
				$sql = 'DELETE FROM servings
					WHERE restaurant_id = :resid
						AND date = :date';
				$delq = $this->db->prepare($sql);
				$delq->execute(array('resid' => $id, 'date' => $date));
				$reset_dates[] = $date;
				}
			    echo 'Inserting ' . $date . ': ' . $array[3][$i] . ' (' . $array[5][$i] . ') @ ' . $array[7][$i] . '<br />';
			    $q->execute(array('descr' => $array[3][$i], 'diet' => $array[5][$i], 'price' => $array[7][$i], 'date' => $date, 'resid' => $id));
		    }
	    }
    }

    function handle_fazeramica_menu($array, $dateparts, $id) {
	    $date = NULL;
	    $sql = 'INSERT INTO servings
		    (description, diet, price, date, restaurant_id)
		    VALUES(:descr, :diet, :price, DATE(:date), :resid)';
	    $q = $this->db->prepare($sql);
		$reset_dates = array();
	    foreach ($array as $row) {
		    if (empty($row[0]))
		    {
			    continue;
		    }
		    elseif (empty($row[1]) && (empty($row[2]) || $row[2] == 'opiskelijat' || $row[3] == 'muut')) {
			    $wday = $this->weekday_to_numeral(trim($row[0]));
			    if ($wday) {
				    $tmp = $dateparts;
				    $tmp[2] = str_pad($tmp[2] + ($wday - 1), 2, 0, STR_PAD_LEFT);
				    $date = implode('-', $tmp);
			    }
			    echo 'Doing date ' . $wday . '<br />';
		    }
		    else {
				if (!in_array($date, $reset_dates)) {
				$sql = 'DELETE FROM servings
					WHERE restaurant_id = :resid
						AND date = :date';
				$delq = $this->db->prepare($sql);
				$delq->execute(array('resid' => $id, 'date' => $date));
				$reset_dates[] = $date;
				}
			    echo 'Inserting ' . $date . ': ' . $row[0] . ' (' . $row[1] . ') @ ' . $row[2] . ' / ' . $row[3] . '<br />';
			    $q->execute(array('descr' => $row[0], 'diet' => $row[1], 'price' => $row[2] . ' / ' . $row[3], 'date' => $date, 'resid' => $id));
		    }
	    }
    }

    function weekday_to_numeral($weekday) {
	    switch (strtolower($weekday)) {
		    case 'maanantai':
			    return 1;
		    case 'tiistai':
			    return 2;
		    case 'keskiviikko':
			    return 3;
		    case 'torstai':
			    return 4;
		    case 'perjantai':
			    return 5;
		    case 'lauantai':
			    return 6;
		    case 'sunnuntai':
			    return 7;
		    default:
			    return NULL;
	    }
    }

    function format_date($weekday, $week) {
	    $datestring = $week . '-' . $weekday;
	    echo 'Formatting ' . $datestring;
	    $dateparts = strptime($datestring, '%U-%u');
	    $date = date('Y') . '-' . str_pad($dateparts['tm_mon'] + 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad($dateparts['tm_mday'], 2, 0, STR_PAD_LEFT);
	    print_r($dateparts);
	    return $date;
    }

    function print_array_to_table($matches) {
	    $output = '<table border="1">';
	    for ($i = 0; $i <= count($matches[0]); $i++) {
		    $output.= '<tr>';
		    for ($j = 1; $j <= count($matches); $j++) {
			    $output.= '<td>' . $matches[$j][$i] . '</td>';
		    }
		    $output.= '</tr>';
	    }
	    $output.= '</table>';
	    return $output;
    }

    function print_menutable($date) {
    $output = '';
    $sql = 'SELECT id, name, url
	    FROM restaurants
	    WHERE url IS NOT NULL
	    ORDER BY shortname';
    $res = $this->db->query($sql);
    $output.= '<table>';
    $dbdate = strftime('%Y-%m-%d', $date);
    while ($row = $res->fetch()) {
	    $output.= '<tr><th colspan="3"><a href="' . $this->html_encode($row['url']) . '">' . $this->html_encode($row['name']) . '</a></th></tr>';
	    $sql2 = 'SELECT description, diet, price, studentprice, staffprice, normalprice
		    FROM servings
		    WHERE restaurant_id = :id
			    AND DATE(date) = DATE(:date)';
	    $qry2 = $this->db->prepare($sql2);
	    $qry2->execute(array($row['id'], $dbdate));
	    while ($row2 = $qry2->fetch()) {
		    if (empty($row['studentprice']) && empty($row['normalprice'])) {
			    $price = explode('/', $row2['price']);
			    foreach ($price as $key => $item) {
				    $price[$key] = trim($item);
			    }
			    if ($this->usersettings['studentprice'] && preg_match('/^[0-9\,\. \/]+$/', $price[0])) {
				    $price = $price[0];
			    }
			    else {
				    $price = implode(' / ', $price);
			    }
		    }
		    else {
			    $price = (($this->usersettings['studentprice']) ? $row['studentprice'] : implode(' / ', array($row['studentprice'], $row['staffprice'], $row['normalprice'])));
		    }
		    $output.= '<tr><td class="description">' . $this->html_encode($row2['description']) . '</td><td class="diet">' . $this->html_encode($row2['diet']) . '</td><td class="price">' . $this->html_encode($price) . '</td></tr>' . "\n";
	    }
    }
    $output.= '</table>';
    return $output;
    }

    function print_full_menutable() {
    $output = '';
    $sql = 'SELECT r.name, s.description, s.date
		FROM servings s
		JOIN restaurants r
		ON r.id = s.restaurant_id
		ORDER BY date DESC';
    $res = $this->db->query($sql);
    $output.= '<table>';
    $dbdate = strftime('%Y-%m-%d', $date);
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
	    $output.= '<tr>';
		foreach ($row as $key => $val) {
			$output.= '<td>' . $val . '</td>';
		}
	    $output.= '</tr>';
    }
    $output.= '</table>';
    return $output;
    }

	function html_encode($string) {
		return htmlspecialchars(html_entity_decode($string, ENT_NOQUOTES, 'UTF-8'));
	}
}
