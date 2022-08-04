<?php

function pad($number, $min_digits){
		return strrev(
			implode(",",str_split(str_pad(strrev($number), $min_digits, "0", STR_PAD_RIGHT),3))
		);
	}

if(isset($_POST['iddxftes']))
	$iddxftes = $_POST['iddxftes'];
else
	$iddxftes = 17; //die("Riferimento non valido");

// Preparo i dati base


$con=mysqli_connect("localhost","root","","dxf");
mysqli_set_charset($con, 'utf8');
// Check connection
if (mysqli_connect_errno())
{
echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br />";
}

$result_lav = mysqli_query($con,"SELECT * FROM lavorazioni");
$lavorazione=array();
while($row = mysqli_fetch_array($result_lav))
{
	$lavorazione[$row['descr']]=$row['valore'];
}


$result = mysqli_query($con,"SELECT * FROM dxfrig inner join dxftes on dxftes.id = dxfrig.iddxftes where iddxftes='".$iddxftes."'");

$estratto=array();
while($row = mysqli_fetch_array($result))
{
	array_push($estratto, $row);
}
mysqli_close($con);

//Almeno un record deve esistere
if($estratto[0]['versione'] != '' )
{
$intestazione = $estratto[0]['codice'].' Ver. '.$estratto[0]['versione'];
$moduleName = $estratto[0]['codice'].'_'.$estratto[0]['versione'];
}
else
{
$intestazione = $estratto[0]['codice'];	
$moduleName = $estratto[0]['codice'];	
}


$descrizione = $estratto[0]['codice'].' - '.$estratto[0]['descrizione'];
$subdescr = $estratto[0]['descrizione'].' - Ultimo Aggiornamento : '.date('d-m-Y H:i',strtotime($estratto[0]['inserimento']));

$total_surf = array();
$total_paint = 0;
$counter_lav = 0;
$counter_prod = 0;
$spessori = array();
$taglio = 0;
$costo = array();

foreach ($estratto as $row)
{
	$perimeter = round($row['Perimeter']);
	$surface = $row['extSurface'];
	$counter_prod += $row['Quantità'];
	$counter_lav ++;
	$taglio += ($perimeter/1000) * $row['Quantità'];
	
	$spessore_string = explode(" ",$row['Spessore']);
	$spessore = $spessore_string[0];
	
	if(!in_array($spessore,$spessori))
	{
		array_push($spessori,$spessore);
		$key = array_search($spessore,$spessori);
	}
	else
		$key = array_search($spessore,$spessori);
	
	if (!isset($total_surf[$key]))
		array_push($total_surf, ($surface*$row['Quantità']));
	else
		$total_surf[$key] += ($surface*$row['Quantità']);

	if($row['Materiale']=='LAMIERA ZNVN')
		$total_paint += ($surface*$row['Quantità']);
	
	if($row['Materiale']=='LAMIERA ZNVV')
		$total_paint += (2*$surface*$row['Quantità']);
} 

// calcolo costi attesi
// TBDone



// Include the main TCPDF library (search for installation path).
require '../vendor/autoload.php';


class MYPDF extends TCPDF {


    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', '', 10);
		$this->SetTextColor(0,64,128);
        // Page number
		
		$footer = 'Pagina '.$this->getAliasNumPage().'/'.$this->getAliasNbPages();
		$footer1 = 'Stampato il '.date('d-m-Y H:i');
		$style2 = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,64,128));
        $this->Cell(0, 10, $footer1, 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 10, $footer, 0, false, 'R', 0, '', 0, false, 'T', 'M');
		$this->Line(15, 195, 282, 195, $style2);
		
    }
}







// create new PDF document
$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Avensys');
$pdf->SetTitle($intestazione);


// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $intestazione, $subdescr, array(0,64,128), array(0,64,128));
//$pdf->setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('dejavusans', '', 10, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// set text shadow effect
//$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

// Set some content to print
$html ='
<table cellpadding="1" cellspacing="0" border="1" style="text-align:center;">
<thead >
	<tr>
		<th width="117.3">Immagine</th>
		<th width="237.3">Nome File</th>
		<th width="107.3">Materiale</th>
		<th width="77.3">Spessore</th>
		<th width="67.3">Quantità</th>
		<th width="117.3">Dimensioni</th>
		<th width="107.3">Lunghezza di taglio</th>
		<th width="107.3">Superficie di verniciatura</th>
	</tr>
</thead>
<tbody>';

foreach ($estratto as $row)
{
	$perimeter = round($row['Perimeter']);
	$surface = $row['extSurface'];
	$width = $row['Width'];
	$height = $row['Height'];
	$html = $html."<tr>";
	$html = $html.'<td width="117.3">' . '<img src="./data/' . $moduleName . '/' . $moduleName . '_svg/'. $row['Nome File'] . '.dxf.svg'.'"' . " />" . "</td>";
	$html = $html.'<td width="237.3">' . $row['Nome File'] . "</td>";
	$html = $html.'<td width="107.3">' . $row['Materiale'] . "</td>";
	$html = $html.'<td width="77.3">' . $row['Spessore'] . "</td>";
	$html = $html.'<td width="67.3">' . $row['Quantità'] . "</td>";
	$html = $html.'<td width="117.3">' . number_format($width, 0) . ' x ' . number_format($height, 0) . ' mm' . "</td>";
	$html = $html.'<td width="107.3">' . pad($perimeter, 4) . " m</td>";
		if($row['Materiale']=='LAMIERA ZNVN')
		{
		$html = $html. '<td width="107.3">' . number_format($surface,3) . " m2</td>";
		}
		elseif($row['Materiale']=='LAMIERA ZNVV')
		{
		$html = $html. '<td width="107.3">' . number_format(2*$surface,3) . " m2</td>";
		}
		else
		$html = $html. '<td width="107.3">0.00 m2</td>';	
	$html = $html. "</tr>";
}

$html = $html.'</tbody></table>';

// Materiali senza costi
$html2 ='
<center>
<h2>Riepilogo codice: '.$descrizione.' </h2>
</center>
<br>';

$costo_lav = $counter_lav*$lavorazione['disegni']; //20 pezzi ora (uno ogni 3 min) 50 euro ora.
$costo_paint = $total_paint*$lavorazione['verniciatura'];
$costo_taglio = $taglio*$lavorazione['taglio'];

$html2 = $html2. "<b><br>Materiali e lavorazioni:</b><br>";
$html2 = $html2. "<ul>";
$html2 = $html2. "<li>Totale disegni da lavorare: ".number_format($counter_lav,0)."</li>";
$html2 = $html2. "<li>Totale pezzi da produrre: ".number_format($counter_prod,0)."</li>";
$html2 = $html2. "<li>Fogli Teorici (riemp. medio 70%):</li>";
$html2 = $html2. "<ul>";
for ($item = 0; $item< count($spessori); $item++)
	if ($spessori[$item]<=1.2)
		{
		$numero_fogli = ceil(round($total_surf[$item]*1.1/4.5*10))/10;
		if ($numero_fogli< 0.1)
				$numero_fogli=0.1;
		$peso_totale = $numero_fogli * 7.86 * $spessori[$item] * 4.5;
		array_push($costo, $peso_totale * $lavorazione['materiale']);
		$html2 = $html2. "<li>3000x1000x".$spessori[$item]." mm: ".$numero_fogli." fogli (".number_format($peso_totale,1)." kg) </li>";
		}
	else
	{
		$numero_fogli = ceil(round($total_surf[$item]*1.1/2*10))/10;
		if ($numero_fogli< 0.1)
				$numero_fogli=0.1;
		$peso_totale = $numero_fogli * 7.86 * $spessori[$item] * 2;
		array_push($costo, $peso_totale * $lavorazione['materiale']);
		$html2 = $html2. "<li>2000x1000x".$spessori[$item]." mm: ".$numero_fogli." fogli (".number_format($peso_totale,1)." kg) </li>";
	}
$html2 = $html2. "</ul>";
$html2 = $html2. "<li>Percorso di taglio: ".number_format($taglio,2)." m</li>";
$html2 = $html2. "<li>Superficie Verniciatura: ".number_format($total_paint,2)." m2</li>";
$html2 = $html2. "</ul>";

// Materiali e costi completi

$html3 ='
<center>
<h2>Riepilogo codice: '.$descrizione.' </h2>
</center>
<br>';


$html3 = $html3. "<b><br>Dettaglio costi:</b>";
$html3 = $html3. "<ul>";
$html3 = $html3. "<li>Totale disegni da lavorare: ".number_format($counter_lav,0)." - ".number_format($costo_lav,2)." &euro; </li>";
$html3 = $html3. "<li>Totale pezzi da produrre: ".number_format($counter_prod,0)."</li>";
$html3 = $html3. "<li>Fogli Teorici (riemp. medio 90%):</li>";
$html3 = $html3. "<ul>";
for ($item = 0; $item< count($spessori); $item++)
	if ($spessori[$item]<=1.2)
		{
		$numero_fogli = ceil(round($total_surf[$item]*1.1/4.5*10))/10;
		if ($numero_fogli< 0.1)
				$numero_fogli=0.1;
		$peso_totale = $numero_fogli * 7.86 * $spessori[$item] * 4.5;
		array_push($costo, $peso_totale *$lavorazione['materiale']);
		$html3 = $html3. "<li>3000x1000x".$spessori[$item]." mm: ".$numero_fogli." fogli (".number_format($peso_totale,1)." kg) - ".number_format($costo[$item],2)." &euro;</li>";
		}
	else
	{
		$numero_fogli = ceil(round($total_surf[$item]*1.1/2*10))/10;
		if ($numero_fogli< 0.1)
				$numero_fogli=0.1;
		$peso_totale = $numero_fogli * 7.86 * $spessori[$item] * 2;
		array_push($costo, $peso_totale * $lavorazione['materiale']);
		$html3 = $html3. "<li>2000x1000x".$spessori[$item]." mm: ".ceil($total_surf[$item]*1.3/2)." fogli (".number_format($peso_totale,1)." kg) - ".number_format($costo[$item],2)." &euro;</li>";
	}
$html3 = $html3. "</ul>";
$html3 = $html3. "<li>Percorso di taglio: ".number_format($taglio,2)." m - ".number_format($costo_taglio,2)." &euro;</li>";
$html3 = $html3. "<li>Superficie Verniciatura: ".number_format($total_paint,2)." m2 - ".number_format($costo_paint,2)." &euro;</li>";
$html3 = $html3. "</ul>";

$costo_materiale=0;

for ($item = 0; $item< count($spessori); $item++)
		$costo_materiale += $costo[$item];
	
$costo_tot_carp_1  = $costo_lav  + $costo_taglio + $costo_materiale;
$costo_tot_carp_5  = $costo_lav/5  + $costo_taglio + $costo_materiale;
$costo_tot_carp_10 = $costo_lav/10  + $costo_taglio + $costo_materiale;
$costo_tot_carp_20 = $costo_lav/20  + $costo_taglio + $costo_materiale;


$html3 = $html3. "<b><br>Costo totale atteso carpenteria per:</b>";
$html3 = $html3. "<ul>";
$html3 = $html3. "<li>1 pezzo: ".number_format($costo_tot_carp_1,2)." &euro;/kit </li>";
$html3 = $html3. "<li>5 pezzi: ".number_format($costo_tot_carp_5,2)." &euro;/kit </li>";
$html3 = $html3. "<li>10 pezzi: ".number_format($costo_tot_carp_10,2)." &euro;/kit </li>";
$html3 = $html3. "<li>20 pezzi: ".number_format($costo_tot_carp_20,2)." &euro;/kit </li>";
$html3 = $html3. "<li>costo kit atteso carpenteria (no ufficio tecnico): ".number_format($costo_taglio + $costo_materiale,2)." &euro;/kit</li>";
$html3 = $html3. "</ul>";

$html = <<<EOD
$html
EOD;

$html2 = <<<EOD
$html2
EOD;

$html3 = <<<EOD
$html3
EOD;

// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

$pdf->AddPage();

if(!isset($_POST['euro']))
{
	$pdf->writeHTMLCell(0, 0, '', '', $html2, 0, 1, 0, true, '', true);
	$pdf->Output(__DIR__.'/data/'.$moduleName .'/'.$moduleName.'_lista.pdf', 'F');
}
else
{
	$pdf->writeHTMLCell(0, 0, '', '', $html3, 0, 1, 0, true, '', true);	
	$pdf->Output(__DIR__.'/data/'.$moduleName . '/'.$moduleName.'_lista_costi.pdf', 'F');
}


//============================================================+
// END OF FILE
//============================================================+