<div id="map-msg" style="width: 600px; text-align: center;">Napsauta merkkiä nähdäksesi ravintolan nimen<span id="map-msg-restaurantname">.</span></div>
<div id="kartta" style="width: 600px; height: 525px"></div>

<script type="text/javascript" src="http://tile.cloudmade.com/wml/latest/web-maps-lite.js"></script>
<script type="text/javascript">
	var cloudmade = new CM.Tiles.CloudMade.Web({key: '<?php echo $cloudmade_id; ?>'});
	var map = new CM.Map('kartta', cloudmade);

	map.setCenter(new CM.LatLng(60.4537, 22.2877407073974628774070739746), 15);
<?php
$sql = 'SELECT name, longitude, latitude, shortname
        FROM restaurants
        ORDER BY shortname';
$res = $db->query($sql);
$markers = array();
while ($row = $res->fetch()) {
	if (!empty($row['latitude']) && !empty($row['longitude'])) {
		echo '
	var marker' . htmlspecialchars(ucfirst($row['shortname'])) . ' = new CM.Marker(new CM.LatLng(' . $row['latitude'] . ', ' . $row['longitude'] . '), {
		title: "' . htmlspecialchars($row['name']) . '",
		clickable: true
	});
	map.addOverlay(marker' . htmlspecialchars(ucfirst($row['shortname'])) . ');
	CM.Event.addListener(marker' . htmlspecialchars(ucfirst($row['shortname'])) . ', \'click\', function() {
	        displayMessage(": ' . htmlspecialchars($row['name']) . '");
	});
';
	}
}
?>

function displayMessage(msg) {
	document.getElementById('map-msg-restaurantname').innerHTML = msg;
}
</script>
