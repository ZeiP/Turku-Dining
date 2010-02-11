<?php

function print_menutable($date, $db, $usersettings = array()) {
$output = '';
$sql = 'SELECT id, name, url
	FROM restaurants
	WHERE url IS NOT NULL
	ORDER BY shortname';
$res = $db->query($sql);
$output.= '<table>';
$dbdate = strftime('%Y-%m-%d', $date);
while ($row = $res->fetch()) {
	$output.= '<tr><th colspan="3"><a href="' . htmlspecialchars($row['url']) . '">' . htmlspecialchars($row['name']) . '</a></th></tr>';
	$sql2 = 'SELECT description, diet, price, studentprice, staffprice, normalprice
		FROM servings
		WHERE restaurant_id = :id
			AND DATE(date) = DATE(:date)';
	$qry2 = $db->prepare($sql2);
	$qry2->execute(array($row['id'], $dbdate));
	while ($row2 = $qry2->fetch()) {
		if (empty($row['studentprice']) && empty($row['normalprice'])) {
			$price = explode('/', $row2['price']);
			foreach ($price as $key => $item) {
				$price[$key] = trim($item);
			}
			if ($usersettings['studentprice'] && preg_match('/^[0-9\,\. \/]+$/', $price[0])) {
				$price = $price[0];
			}
			else {
				$price = implode(' / ', $price);
			}
		}
		else {
			$price = (($usersettings['studentprice']) ? $row['studentprice'] : implode(' / ', array($row['studentprice'], $row['staffprice'], $row['normalprice'])));
		}
		$output.= '<tr><td class="description">' . htmlspecialchars($row2['description']) . '</td><td class="diet">' . htmlspecialchars($row2['diet']) . '</td><td class="price">' . $price . '</td></tr>' . "\n";
	}
}
$output.= '</table>';
return $output;
}
