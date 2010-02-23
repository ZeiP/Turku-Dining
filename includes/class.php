<?php

class TurkuDining {
	/* Contains the database object used for DB access. */
	var $db;
	/* Contains the current user's user settings. */
	var $usersettings;

	/*
	 * Constructor. Sets the DB object and user settings to their rightful variables.
	 */
	function TurkuDining($db, $usersettings) {
		$this->db = $db;
		$this->usersettings = $usersettings;
	}

	/*
	 * Fetch the menu for a given restaurant (restaurant given as database ID, rest of the information is fetched from DB). 
	 */
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

	/*
	 * Handle an Unica menu input and save it to database. Quite a bit of overlapping code with handle_fazeramica_menu().
	 */
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
				$descr = html_entity_decode($array[3][$i], ENT_QUOTES, 'UTF-8');
				$diet = html_entity_decode($array[5][$i], ENT_QUOTES, 'UTF-8');
				$price = html_entity_decode($array[7][$i], ENT_QUOTES, 'UTF-8');
				echo 'Inserting ' . $date . ': ' . $descr . ' (' . $diet . ') @ ' . $price . '<br />';
				$q->execute(array('descr' => $descr, 'diet' => $diet, 'price' => $price, 'date' => $date, 'resid' => $id));
			}
		}
	}

	/*
	 * Handle a Fazer-Amica menu input and save it to database. Quite a bit of overlapping code with handle_unica_menu().
	 */
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
				$descr = html_entity_decode($row[0], ENT_QUOTES, 'UTF-8');
				$diet = html_entity_decode($row[1], ENT_QUOTES, 'UTF-8');
				$price = html_entity_decode(($row[2] . ' / ' . $row['3']), ENT_QUOTES, 'UTF-8');
				echo 'Inserting ' . $date . ': ' . $descr . ' (' . $diet . ') @ ' . $price . '<br />';
				$q->execute(array('descr' => $descr, 'diet' => $diet, 'price' => $price, 'date' => $date, 'resid' => $id));
			}
		}
	}

	/*
	 * Return a Finnish weekday translated to an integer 1 to 7.
	 */
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

	/*
	 * Make a decent date out of a Finnish weekday and a week number...
	 */
	function format_date($weekday, $week) {
		echo 'Formatting ' . $weekday . ' / ' . $week . "\n";
		$fourth_january_weekday = date('N', mktime(0, 0, 0, 1, 4));
		$week1_sunday = 4+(7-$fourth_january_weekday); // Day number of the Sunday of the 1st week
		$weeks_sunday = $week1_sunday+(($week-1)*7);
		$weeks_day = $weeks_sunday-(7-$weekday);
		$dateparts = strptime($weeks_day . '-' . date('Y'), '%j-%Y');
		$date = date('Y') . '-' . str_pad($dateparts['tm_mon'] + 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad($dateparts['tm_mday'], 2, 0, STR_PAD_LEFT);
		echo $date;
		return $date;
	}

	/*
	 * Print an array to a table – debug functionality?
	 */
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

	/*
	 * Returns the menu table for the given date; abides user settings.
	 */
	function print_menutable($date) {
		$dbdate = strftime('%Y-%m-%d', $date);
		$output = '';
		$sql = 'SELECT DISTINCT r.id, r.name, r.url, r.shortname
			FROM restaurants r
			JOIN servings s
			ON s.restaurant_id = r.id
			WHERE r.url IS NOT NULL AND
				s.date = :date
			ORDER BY r.shortname';
		$qry = $this->db->prepare($sql);
		$qry->execute(array($dbdate));
		$output.= '<table>';
			$first = TRUE;
		while ($row = $qry->fetch()) {
			if (!empty($this->usersettings['exclude_restaurants']) && in_array($row['id'], $this->usersettings['exclude_restaurants'])) {
				continue;
			}
			if (!$first) {
				$output.= '</tbody>';
				$first = FALSE;
			}
			$output.= '<thead><tr><th colspan="3"><a href="#" onclick="return toggleDisplayNode(document.getElementById(\'list_' .  $row['shortname'] . '\'));">' . $this->html_encode($row['name']) . '</a> <span class="restaurantlinks">(<a href="' . $this->html_encode($row['url']) . '">WWW</a>)</span></th></tr></thead><tbody id="list_' . $row['shortname'] . '">';
			$sql2 = 'SELECT description, diet, price, studentprice, staffprice, normalprice
				FROM servings
				WHERE restaurant_id = :id
					AND DATE(date) = DATE(:date)';
			$qry2 = $this->db->prepare($sql2);
			$qry2->execute(array($row['id'], $dbdate));
			while ($row2 = $qry2->fetch()) {
				$price = '';
				if (empty($row['studentprice']) && empty($row['normalprice']) && !empty($row2['price'])) {
					$pricestr = str_replace(html_entity_decode('&euro;', ENT_QUOTES, 'UTF-8'), '', $row2['price']);
					$price = explode('/', $pricestr);
//					if (count($price) == 3 || count($price) == 1) {
					if (preg_match('/^[0-9\.\,\/ ]+$/', $pricestr)) {
						foreach ($price as $key => $value) {
							$price[$key] = number_format(str_replace(',', '.', trim($value)), 2, ',', ' ');
						}
						if ($this->usersettings['studentprice']) {
							$price = $price[0];
						}
						else {
							$price = implode(' / ', $price);
						}
					}
					else {
						foreach ($price as $key => $value) {
							$price[$key] = trim($value);
						}
						$price = implode(' / ', $price);
					}
				}
				elseif (!empty($row['studentprice']) && !empty($row['normalprice'])) {
					if ($this->usersettings['studentprice']) {
						$price = $row['studentprice'];
					}
					else {
						$price = implode(' / ', array($row['studentprice'], $row['staffprice'], $row['normalprice']));
					}
				}
				$output.= '<tr><td class="description">' . $this->html_encode($row2['description']) . '</td><td class="diet">' . $this->html_encode($row2['diet']) . '</td><td class="price">' . $this->html_encode($price) . '</td></tr>' . "\n";
			}
		}
		$output.= '</tbody></table>';
		return $output;
	}

	/*
	 * Prints all the data in our database. Used only in listall (admin functionality.) Not too pretty...
	 */
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

	function select_date($selected_date) {
		$sql = 'SELECT DISTINCT STRFTIME(\'%s\', s.date) AS date
			FROM servings s
			WHERE s.date = DATE(s.date)
			ORDER BY s.date ASC';
		$res = $this->db->query($sql);
		$result = '<form method="post" action="">
<p>
<label for="show_date">Näytä päivä</label>
<select name="show_date" id="show_date">';
		while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$result.= '<option value="' . $row['date'] . '"' . ((date('j.n.Y', $selected_date) == date('j.n.Y', $row['date'])) ? ' selected="selected"' : '') . '>' . date('j.n.Y', $row['date']) . '</option>' . "\n";
		}
		$result.= '</select>
</p>
<p><input type="submit" name="change_date" value="Valitse päivä" /></p>
</form>';
		return $result;
	}

	/*
	 * First decodes and then encodes a string so that all necessary chars are entitizied, and
	 *  that for example &#[whatever]; is translated to &euro; instead of &amp;#[whatever];.
	 */
	function html_encode($string) {
		return htmlspecialchars(html_entity_decode($string, ENT_NOQUOTES, 'UTF-8'));
	}

	/*
	 * Returns the menu system base url appended by argument $target.
	 */
	function url ($target = '') {
		return 'http://' . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['PHP_SELF']) . $target;
	}
}
