<?php
include ('.ht_cred.php');

$sname = 'index.php';
$cssname = 'lischde.css';
$add = 'hinzu';
$mehr = 'einfuegen';
$gek = 'gekauft';
$art = 'artkl';
$amnt = 'mng';
$artikel = ''; //Init
$anztop = 15; // 15 Top-Artikel

function query($par, $partype)
{
	if ($partype == '' )
		$partype = gettype($par);
	switch ($partype)
	{
		case 'string': 
            $ret = filter_input(INPUT_GET, $par, FILTER_SANITIZE_STRING);
            break;
		case 'int':
		default:
            $ret = filter_input(INPUT_GET, $par, FILTER_SANITIZE_NUMBER_INT);
	}
	return $ret;
}

function parse($par, $partype) // funktioniert noch nicht!!!
{
	if ($partype == '' )
		$partype = gettype($par);
	switch ($partype)
	{
		case 'string': 
            $ret = filter_input_array(INPUT_POST, $par, FILTER_SANITIZE_STRING);
            break;
		case 'int':
		default:
            $ret = filter_input_array(INPUT_POST, $par, FILTER_SANITIZE_NUMBER_INT);
	}
	return $ret;
}


echo "<!DOCTYPE HTML><html lang='de'><head><meta charset='utf-8'><title>Einkaufsliste 'Nigs vergessen'</title>";
echo "<link rel='stylesheet' href='".$cssname."'></head><body>";

// Create connection
$conn = new mysqli($dbserver, $user, $pw, $database);
// Check connection
if ($conn->connect_error) 
{
  die("Connection failed: " . $conn->connect_error);
}

// Parameter auslesen
$p1 = query($add, 'int');
$p2 = query($gek, 'int');
$p3 = query($mehr, 'int');
$artikel = query($art, 'string');
$menge = query($amnt, 'int'); // wird nicht mehr benutzt
$today = date('Y.m.d');

// Neue Einträge hinzufügen (POST-Methode)
if ($p3 == -1 ) 
{
  $artikel = filter_var($_POST[$art], FILTER_SANITIZE_STRING);
  $menge = 1; // Menge deaktiviert, immer 1

  if ($artikel != "")
  {
    $today = date('Y.m.d');
    $sql = "INSERT INTO $table (Titel, Menge, Aktiv, LastUsed, Wieoft) VALUES ('$artikel', $menge, 1, '$today', 1)";
    if ($conn->query($sql) === FALSE) 
    {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
  }
}

if ($p3 > 0) // vorhandener Eintrag hinzugefügt
{
	$sql = "UPDATE $table SET Aktiv=1 WHERE id=$p3";
	if ($conn->query($sql) === FALSE) 
	{
	  echo "Error activating record: " . $conn->error;
	}

  // Zähler hochsetzen
  $sql = "SELECT id, WieOft FROM $table WHERE id=$p3";
  $result = $conn->query($sql);
  if ($result === FALSE) 
	{
	  echo "Error getting usage: " . $conn->error;
	}
	$row = $result->fetch_assoc();
  $neu = $row["WieOft"] + 1;

	$sql = "UPDATE $table SET WieOft=$neu WHERE id=$p3";
  if ($conn->query($sql) === FALSE) 
	{
	  echo "Error increasing usage: " . $conn->error;
	}
}	

if ($p2 > 0) // Eintrag als gekauft markieren
{ 
	$sql = "UPDATE $table SET Aktiv=0 WHERE id=$p2";
	if ($conn->query($sql) === FALSE) 
	{
	  echo "Error deactivating record: " . $conn->error;
	}
	$sql = "UPDATE $table SET LastUsed='$today' WHERE id=$p2";
  if ($conn->query($sql) === FALSE) 
	{
	  echo "Error updating LastUsage: " . $conn->error;
	}
}

// Maximalwert für Fontgröße ermitteln
$maxSQL = "SELECT MAX(Wieoft) AS max FROM $table;";
$result = $conn->query($maxSQL);
if ($result === FALSE)
	  echo "Error calculating highest buy: " . $conn->error;
$row = $result->fetch_assoc();
$highest = $row['max'];

// Aktuelle Liste ausgeben
if ($p1 == 1) // Add-Modus
{
	$top_sql = "SELECT id, Titel, Menge, Aktiv, LastUsed, Wieoft FROM $table WHERE Aktiv=0 ORDER BY Wieoft DESC, Titel ASC";
 	$sql = "SELECT id, Titel, Menge, Aktiv, LastUsed, Wieoft FROM $table WHERE Aktiv=0 ORDER BY Titel ASC";
	$top_result = $conn->query($top_sql);
}
else if ($p1 == 2) // Statistik nach Datum
{
 	$sql = "SELECT id, Titel, Menge, Aktiv, LastUsed, Wieoft FROM $table ORDER BY LastUsed DESC, Titel ASC";
}
else if ($p1 == 4) // Statistik nach Anzahl
{
 	$sql = "SELECT id, Titel, Menge, Aktiv, LastUsed, Wieoft FROM $table ORDER BY Wieoft DESC, Titel ASC";
}
else // Show
{
 	$sql = "SELECT id, Titel, Menge, Aktiv, LastUsed, Wieoft FROM $table WHERE Aktiv=1 ORDER BY Wieoft DESC, Titel ASC";
}

$result = $conn->query($sql);
  
echo "<div class='Kopf'><h1><a href='".$sname."'>Liste aktualisieren</a></h1>";

if ($p1 != 1 ) // Show und Statistik
{
  echo "<h2><a href='".$sname."?" . $add . "=1'>Artikel hinzufügen</a></h2>";
  echo "<h3><a href='".$sname."?" . $add . "=2'>Statistik nach Datum</a> - <a href='".$sname."?" . $add . "=4'>Statistik nach Anzahl</a></h3></div>"; 
}

if ($p1 != 0 ) // Immer das Feld anzeigen, außer beim Standard-List
{ 
  // Textfeld für die Filterfunktion erweitert (id="filterInput")
  // echo "</div><div class='Kopf'>Wert P1=".$p1."<br>";
  echo "</div><div class='Formular'>";
  echo "<form action='".$sname."?".$mehr."=-1' method='post'>";
  echo "<p>Artikel: <input type='text' name='".$art."' id='filterInput' /></p>";
  echo "<input type='submit' value='Senden' />";
  echo "</form></div>";
}
 
echo "<div class='Liste'>";
if ($result->num_rows > 0) 
{
  if ($p1 == 1) // Add-Modus: Top-Artikel anzeigen
  {
	  echo "<h3>Top-Artikel</h3>";
	  for ($i = 0; $i < $anztop; $i++)
	  {
	    $row = $top_result->fetch_assoc();
	    if ($row != NULL)
	    {
		    echo "<a class='linkz4' href='".$sname."?";
				// Im Add-Modus wird der Parameter 'einfuegen' genutzt
				echo $mehr . "=" . $row["id"] . "'>";
				echo $row["Titel"] . " </a>&nbsp;-&nbsp;";
	     }
	  }
  }
  if ($p1 == 1) // Nur im Add-Modus
	  echo "<h3>Alle Artikel in alphabetischer Reihenfolge</h3>";
  
  // Alle Einträge ausgeben
  while($row = $result->fetch_assoc()) 
  {
    if ($p1 == 2 || $p1 == 4) // Statistik
    {
    	echo "<li>";
    	echo "<a href='".$sname."?";
			echo $mehr . "=" . $row["id"] . "'>";
			echo $row["Titel"] ."</a> (". $row["Wieoft"] . "x, last: ".$row["LastUsed"] .")";
    	echo "</li>";
    }
    else
    {
		  // Link mit variabler Fontgröße
		  echo "<a class='";
		  $fnsize = round(5 * $row["Wieoft"] / $highest);
		  switch ($fnsize)
		  {
		  	case 0:
		  	case 1:
		  		echo "linkz2";
		  		break;
		  	case 2:
		  	case 3:
		  		echo "linkz3";
		  		break;
		  	case 4:
		  		echo "linkz4";
		  		break;
		  	case 5:
		  	default:
		  		echo "linkz5";
		  }
		  echo "' href='".$sname."?";
			// Im Show-Modus wird der Parameter 'gekauft' genutzt, ansonsten 'einfuegen'
			if ($p1 == 0)
				echo $gek . "=" . $row["id"] . "'>";
			else
				echo $mehr . "=" . $row["id"] . "'>";
	 		echo $row["Titel"] ." </a>&nbsp;-&nbsp;";
		}
	}

} 
else 
{
  echo "Keine Einträge";
} 

echo "</div>";

// Wenn wir uns im Add-Modus befinden, binden wir ein JavaScript ein, das beim Tippen im Textfeld die Liste filtert.
if ($p1 == 1) {
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        var inputField = document.getElementById('filterInput');
        var listContainer = document.querySelector('.Liste');
        inputField.addEventListener('input', function() {
            var filter = inputField.value.toLowerCase();
            // Wähle alle Links und Listeneinträge in der Liste aus
            var items = listContainer.querySelectorAll('a, li');
            items.forEach(function(item) {
                // Überprüfe, ob der Text (in Kleinbuchstaben) den Suchbegriff enthält
                if(item.textContent.toLowerCase().includes(filter)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    </script>";
}

// Aufräumen
$conn->close();
echo "</body></html>";

