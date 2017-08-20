<?php require_once('../Connections/conn.php'); ?>
<?php
$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsBrowse = 10;
$pageNum_rsBrowse = 0;
if (isset($_GET['pageNum_rsBrowse'])) {
  $pageNum_rsBrowse = $_GET['pageNum_rsBrowse'];
}
$startRow_rsBrowse = $pageNum_rsBrowse * $maxRows_rsBrowse;

$colLatitude_rsBrowse = "0";
if (isset($_GET['latitude'])) {
  $colLatitude_rsBrowse = (get_magic_quotes_gpc()) ? $_GET['latitude'] : addslashes($_GET['latitude']);
}
$colLongitude_rsBrowse = "0";
if (isset($_GET['longitude'])) {
  $colLongitude_rsBrowse = (get_magic_quotes_gpc()) ? $_GET['longitude'] : addslashes($_GET['longitude']);
}
mysql_select_db($database_conn, $conn);
$query_rsBrowse = sprintf("SELECT *, (ROUND( DEGREES(ACOS(SIN(RADIANS(%s)) * SIN(RADIANS(latitude)) + COS(RADIANS(%s)) * COS(RADIANS(latitude)) * COS(RADIANS(%s -(longitude)))))*60*1.1515,2)) as distance FROM records WHERE record_type = 'student' AND (ROUND( DEGREES(ACOS(SIN(RADIANS(%s)) * SIN(RADIANS(latitude)) + COS(RADIANS(%s)) * COS(RADIANS(latitude)) * COS(RADIANS(%s -(longitude)))))*60*1.1515,2)) < 50000 order by distance", $colLatitude_rsBrowse,$colLatitude_rsBrowse,$colLongitude_rsBrowse,$colLatitude_rsBrowse,$colLatitude_rsBrowse,$colLongitude_rsBrowse);
$query_limit_rsBrowse = sprintf("%s LIMIT %d, %d", $query_rsBrowse, $startRow_rsBrowse, $maxRows_rsBrowse);
$rsBrowse = mysql_query($query_limit_rsBrowse, $conn) or die(mysql_error());
$row_rsBrowse = mysql_fetch_assoc($rsBrowse);

if (isset($_GET['totalRows_rsBrowse'])) {
  $totalRows_rsBrowse = $_GET['totalRows_rsBrowse'];
} else {
  $all_rsBrowse = mysql_query($query_rsBrowse);
  $totalRows_rsBrowse = mysql_num_rows($all_rsBrowse);
}
$totalPages_rsBrowse = ceil($totalRows_rsBrowse/$maxRows_rsBrowse)-1;

$queryString_rsBrowse = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsBrowse") == false && 
        stristr($param, "totalRows_rsBrowse") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsBrowse = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsBrowse = sprintf("&totalRows_rsBrowse=%d%s", $totalRows_rsBrowse, $queryString_rsBrowse);
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
</head>

<body>
<h3>Browse </h3>


<p>Location: 
  <input name="location" type="text" id="location" />
</p>
<form id="form1" name="form1" method="get" action="">
  Keyword: 
  <input type="text" name="textfield" />
  <input type="submit" name="Submit" value="Search" />
  <input name="latitude" type="text" id="latitude" />
  <input name="longitude" type="text" id="longitude" />
</form>
<?php if ($totalRows_rsBrowse > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td>title</td>
      <td>description</td>
      <td>record_date</td>
      <td>distance</td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo $row_rsBrowse['title']; ?></td>
        <td><?php echo $row_rsBrowse['description']; ?></td>
        <td><?php echo $row_rsBrowse['record_date']; ?></td>
        <td><?php echo $row_rsBrowse['distance']; ?> miles</td>
      </tr>
      <?php } while ($row_rsBrowse = mysql_fetch_assoc($rsBrowse)); ?>
      </table>
  <p> Records <?php echo ($startRow_rsBrowse + 1) ?> to <?php echo min($startRow_rsBrowse + $maxRows_rsBrowse, $totalRows_rsBrowse) ?> of <?php echo $totalRows_rsBrowse ?>
  <table border="0" width="50%" align="center">
        <tr>
          <td width="23%" align="center"><?php if ($pageNum_rsBrowse > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsBrowse=%d%s", $currentPage, 0, $queryString_rsBrowse); ?>">First</a>
                <?php } // Show if not first page ?>
          </td>
          <td width="31%" align="center"><?php if ($pageNum_rsBrowse > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsBrowse=%d%s", $currentPage, max(0, $pageNum_rsBrowse - 1), $queryString_rsBrowse); ?>">Previous</a>
                <?php } // Show if not first page ?>
          </td>
          <td width="23%" align="center"><?php if ($pageNum_rsBrowse < $totalPages_rsBrowse) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsBrowse=%d%s", $currentPage, min($totalPages_rsBrowse, $pageNum_rsBrowse + 1), $queryString_rsBrowse); ?>">Next</a>
                <?php } // Show if not last page ?>
          </td>
          <td width="23%" align="center"><?php if ($pageNum_rsBrowse < $totalPages_rsBrowse) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsBrowse=%d%s", $currentPage, $totalPages_rsBrowse, $queryString_rsBrowse); ?>">Last</a>
                <?php } // Show if not last page ?>
          </td>
        </tr>
  </table>
  <?php } // Show if recordset not empty ?></p>
<?php if ($totalRows_rsBrowse == 0) { // Show if recordset empty ?>
  <p>No Record Found. </p>
  <?php } // Show if recordset empty ?>


<script>

      var placeSearch, autocomplete;
      var componentForm = {
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'short_name',
        country: 'long_name',
        postal_code: 'short_name'
      };

      function initAutocomplete() {
        // Create the autocomplete object, restricting the search to geographical
        // location types.
        autocomplete = new google.maps.places.Autocomplete(
            /** @type {!HTMLInputElement} */(document.getElementById('location')),
            {types: ['geocode']});

        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
        autocomplete.addListener('place_changed', fillInAddress);
      }

      function fillInAddress() {
        // Get the place details from the autocomplete object.
        var place = autocomplete.getPlace();
		
          document.getElementById('latitude').value = place.geometry.location.lat();
		  document.getElementById('longitude').value = place.geometry.location.lng();

      }

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCWqKxrgU8N1SGtNoD6uD6wFoGeEz0xwbs&libraries=places&callback=initAutocomplete"
        async defer></script>
</body>
</html>
<?php
mysql_free_result($rsBrowse);
?>
