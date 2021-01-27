
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
		$partype=gettype($par);
	switch ($partype)
	{
		case 'string': 
                $ret=filter_input(INPUT_GET,$par,FILTER_SANITIZE_STRING);
		case 'int':
		default:
                $ret=filter_input(INPUT_GET,$par,FILTER_SANITIZE_NUMBER_INT);
        }
	return $ret;
}

function parse($par, $partype) // funktioniert noch nicht!!!
{
	if ($partype == '' )
		$partype=gettype($par);
	switch ($partype)
	{
		case 'string': 
                $ret=filter_input_array(INPUT_POST,$par,FILTER_SANITIZE_STRING);
		case 'int':
		default:
                $ret=filter_input_array(INPUT_POST,$par,FILTER_SANITIZE_NUMBER_INT);
        }
	return $ret;
}


echo "<!DOCTYPE HTML><HTML lang='de'><HEAD><TITLE>Einkaufsliste 'Nigs vergessen'</TITLE>";
echo "<link rel='stylesheet' href='".$cssname."'></HEAD><BODY>";


// Zeige alle Elemente der Datenbank, die aktiv sind
// Hinzufuegen: entweder aus sortierter Liste der nicht aktiven oder neuer Eintrag
// Gekauft: aktiv-bit loeschen, Datum aktualisieren, Wieoft eins erhöhen, 
// Menge wird gerade nicht benutzt

// Create connection
$conn = new mysqli($dbserver, $user, $pw, $database);
// Check connection
if ($conn->connect_error) 
{
  //echo "<BR>Datenbankfehler" . $conn->connect_error . "<BR></BODY></HTML>";
  die("Connection failed: " . $conn->connect_error);
}

// Je nach Parameter Aktion ausführen
// hinzu: neues Item hinzufügen - Liste nicht aktiver anzeigen und Feld für Neueingabe
// gekauft: Item mit der id wird von der Liste der aktiven entfernt

$p1=query($add,'int');
$p2=query($gek,'int');
$p3=query($mehr,'int');
$artikel=query($art,'string');
$menge=query($amnt,'int'); // wird nicht mehr benutzt
$today=date('Y.m.d');

//echo "Debug - Signal: ".$p3;

if ($p3 == -1 ) // POST Methode aktiviert, neue Einträge zum Hinzufügen
{
	
  //echo "Vorher: ".$_POST[$art];
  $artikel=filter_var($_POST[$art],FILTER_SANITIZE_STRING);
  //echo "Nachher: ".$artikel;
  //$menge=filter_var($_POST[$amnt],FILTER_SANITZE_NUMBER);

  //if ($menge == "") // Eingabefehler verhindern
	$menge=1; // Menge deaktiviert, immer 1

  $today=date('Y.m.d');
	
  $sql = "INSERT INTO $table (Titel, Menge, Aktiv, LastUsed, Wieoft) VALUES ('$artikel', $menge, 1,'$today',1)";

  if ($conn->query($sql) === FALSE) 
  {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
}

if ($p3 > 0) // vorhandener Eintrag hinzugefügt
{
  // Aktiv setzen
	$sql = "UPDATE $table SET Aktiv=1 WHERE id=$p3";
  if ($conn->query($sql) === FALSE) 
	{
	  echo "Error activating record: " . $conn->error;
	}

  // Zähler hochsetzen
  $sql = "SELECT id, WieOft FROM $table WHERE id=$p3";
  $result=$conn->query($sql);
  if ( $result === FALSE) 
	{
	  echo "Error getting usage: " . $conn->error;
	}
	$row = $result->fetch_assoc();
  $neu = $row["WieOft"] + 1;
  // Debug echo "Alt: ".$row["WieOft"]." - Neu: ".$neu;

	$sql = "UPDATE $table SET WieOft=$neu WHERE id=$p3";
  if ($conn->query($sql) === FALSE) 
	{
	  echo "Error increasing usage: " . $conn->error;
	}

 
}	

if ($p2 > 0) // Eintrag aus Liste gekauft
{ 
	$sql = "UPDATE $table SET Aktiv=0 WHERE id=$p2";

	if ($conn->query($sql) === FALSE) 
	{
	  echo "Error deactivating record: " . $conn->error;
	}
  // Datum aktualisieren 

	$sql = "UPDATE $table SET LastUsed='$today' WHERE id=$p2";
  if ($conn->query($sql) === FALSE) 
	{
	  echo "Error updating LastUsage: " . $conn->error;
	}
}

// Maxwert für Fontgröße ermitteln
$maxSQL = "SELECT MAX(Wieoft) AS max FROM $table;";
$result= $conn->query($maxSQL);
if ($result === FALSE)
	  echo "Error calculating highest buy: " . $conn->error;
$row = $result->fetch_assoc();
$highest=$row['max'];

// Aktuelle Liste ausgeben
if ($p1 == 1) // Add
{
	$top_sql = "SELECT id, Titel, Menge, Aktiv, LastUsed, Wieoft FROM $table WHERE Aktiv=0 ORDER BY Wieoft DESC,Titel ASC";
 	$sql = "SELECT id, Titel, Menge, Aktiv, LastUsed, Wieoft FROM $table WHERE Aktiv=0 ORDER BY Titel ASC";
	$top_result = $conn->query($top_sql);
}
else if ($p1 == 2) // Statistik
 	$sql = "SELECT id, Titel, Menge, Aktiv, LastUsed, Wieoft FROM $table ORDER BY LastUsed DESC, Titel ASC";
else // Show
 	$sql = "SELECT id, Titel, Menge, Aktiv, LastUsed, Wieoft FROM $table WHERE Aktiv=1 ORDER BY Wieoft DESC, Titel ASC";


$result = $conn->query($sql);
  
echo "<div class='Kopf'><H1><a href='".$sname."'>Liste aktualisieren</a></h1>";

if ($p1 != 1 ) //Show and Statistik
{
  echo "<H2><a href='".$sname."?" . $add . "=1'>Artikel hinzufügen</a></H2>";
  echo "<H2><a href='".$sname."?" . $add . "=2'>Statistik anzeigen</a></h2></div>"; 
}
else
  //echo "</Kopf><Formular><form action='".$sname."?".$mehr."=-1' method='post'> <p>Artikel: <input type='text' name='".$art."' /> </p> <p>Menge: <input type='int' name='".$amnt."' /> </p> <input type='submit' /> </form></Formular>";
  echo "</div><div class='Formular'><form action='".$sname."?".$mehr."=-1' method='post'> <p>Artikel: <input type='text' name='".$art."' /> </p> <input type='submit' value='Senden' /> </form></div>";
 
echo "<div class='Liste'>";
if ($result->num_rows > 0) 
{
  if ($p1 == 1) //Add
  {
	  // output top $anztop articles 
	  echo "<H3>Top-Artikel</H3>";
	  for ($i=0;$i<$anztop;$i++)
	  {
	    $row = $top_result->fetch_assoc();
	    if ($row != NULL) // sind noch Daten drin
	    {
		    echo "<a class='linkz4' href='".$sname."?";
				if ($p1 != 1 ) // Show
					echo $gek . "=" . $row["id"] . "'>";
				else
					echo $mehr . "=" . $row["id"] . "'>";
		 		if ($p1 != 1) // Show
					echo $row["Titel"] ."&nbsp;<span id='Klein'>".$row["LastUsed"]."</span> </a>&nbsp;-&nbsp;";
				else
					//echo $row["Titel"] ."(". $row["Wieoft"] . "x, last: ".$row["LastUsed"] . ") </a> - ";
					echo $row["Titel"] . " </a>&nbsp;-&nbsp;";
	     }
	  }
  }
  if ($p1 == 1) // Nur bei Add
  	echo "<H3>Alle Artikel in alphabetischer Reihenfolge</H3>";
  // output all alphabetically data of each row
  while($row = $result->fetch_assoc()) 
  {
    if ($p1 == 2) // Statistik
    {
    	echo "<li>";
    	echo $row["Titel"] ." (". $row["Wieoft"] . "x, last: ".$row["LastUsed"] .")";
    	echo "</li>";
    }
    else
    {
		  // Link
		  echo "<a class='";
		  // Rechnen
		  $fnsize=round(5*$row["Wieoft"]/$highest);
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
			if ($p1 == 0 ) // Show
				echo $gek . "=" . $row["id"] . "'>";
			else
				echo $mehr . "=" . $row["id"] . "'>";
	 		// Linktext
			echo $row["Titel"] ." </a>&nbsp;-&nbsp;";
		}
	}

} 
else 
{
  echo "Keine Einträge";
} 

// Aufräumen
$conn->close();
echo "</div></BODY></HTML>";

