<?php

//divido per 1000 una stringa numerica e la formatto -- Es. 13 = 0.013	
function pad($number, $min_digits){
		return strrev(
			implode(",",str_split(str_pad(strrev($number), $min_digits, "0", STR_PAD_RIGHT),3))
		);
	}

$iddxftes = 'nessuna';

if(isset($_POST['iddxftes']))
	$iddxftes = $_POST['iddxftes'];


$con=mysqli_connect("localhost","root","","eboard");
mysqli_set_charset($con, 'utf8');
// Check connection
if (mysqli_connect_errno())
{
echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br />";
}


$result = mysqli_query($con,"SELECT * FROM boms inner join ebs on ebs.id = boms.ebId where boms.ebId='".$iddxftes."'");

if(mysqli_num_rows($result)===0 )
	die ('<script type="text/javascript">alert("No details")</script>');

$row = mysqli_fetch_array($result);

if($row['version'] != '' )
$moduleName = $row['item_code'].'_'.$row['version'];
else
$moduleName = $row['item_code'];	

$descrizione = $row['item_code'].' - '.$row['description'];

echo '
<br />
<br />
<center>
<h2> EB: '.$descrizione.' </h2>
<h3>Versione : '.$row['version'].' </h3>
<h4> Ultimo Aggiornamento : '.$row['last_update'].' </h4>
</center>
<div align="right" style="margin: 0px 0px 10px 0px;" >
			<span style="font-size: 32px; color: Dodgerblue;" >
			<i class="fas fa-file-pdf" id="pdf"></i>
			</span>
			<span style="font-size: 32px; color: Dodgerblue;" >
			<i class="fas fa-file-archive" id="zip"></i>
			</span>
			<span style="font-size: 32px; color: Dodgerblue;" >
			<i class="fas fa-euro-sign" id="euro"></i>
			</span>
</div>
<table id="dxfLista" class="table table-striped table-bordered" style="margin-left: auto; margin-right: auto; ">
<thead>
	<tr>
		<th>Q.ty
        </th>
		<th>Parts
        </th>
		<th>Description
        </th>
		<th>Package
        </th>
		<th>Productor
        </th>
		<th>Manufacturer Part Number ( MPN )
        </th>
		<th>Supplier part number ( SPN )
        </th>
		<th>Remark
        </th>
	</tr>
</thead>'
;
echo "<tbody>";
$total_surf = array();
$total_paint = 0;
$counter_lav = 0;
$counter_prod = 0;
$spessori = array();
$taglio = 0;
$costo = array();
do {
	echo "<tr>";
		echo "<td>" . $row['qty'] . "</td>";
		echo "<td>" . $row['parts'] . "</td>";
		echo "<td>" . $row['description'] . "</td>";
		echo "<td>" . $row['package'] . "</td>";
		echo "<td>" . $row['productor'] . "</td>";
		echo "<td>" . $row['mpn'] . "</td>";
		echo "<td>" . $row['spn'] . "</td>";
		echo "<td>" . $row['remark'] . "</td>";
		
    echo "</tr>";
	
} while($row = mysqli_fetch_array($result));
echo "</tbody>";
echo "</table>";

mysqli_close($con);
?>


<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">DXF Manager</h5>
      </div>
      <div class="modal-body" id="msg">
        Lista "<?php echo $descrizione; ?>" in elaborazione, attendere...
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
  $(document).ready(function() {
	  
	

		$('#pdf').click(function()
        {
        
        var param =  <?php echo $iddxftes; ?>;
            $.ajax(
                    {  
                        url: "./lista_pdf.php",
                        type: "post",
                        data: { iddxftes: param
                        },
                        beforeSend: function(result) {
                            $('#msg').html('Lista "<?php echo $descrizione; ?>" in elaborazione, attendere...');
                            $('#exampleModalCenter').modal('show');
                            },
                        success: function(result) {
                            $('#exampleModalCenter').modal('hide');
                            window.open('./data/<?php echo $moduleName; ?>/<?php echo $moduleName; ?>_lista.pdf','_blank');	
                            }
                    }
                );
        });
		

		$('#zip').click(function()
        {
        
        var param =  <?php echo $iddxftes; ?>;
            $.ajax(
                    {  
                        url: "./lista_pdf.php",
                        type: "post",
                        data: { iddxftes: param
                        },
                        beforeSend: function(result) {
                            $('#msg').html('Archivio Zip "<?php echo $moduleName; ?>.zip" in elaborazione, attendere...');
                            $('#exampleModalCenter').modal('show');
                            }
                    }
                ).done( function() {
                        $('#exampleModalCenter').modal('hide');
                        window.location = 'dxfzipper.php?id=<?php echo $moduleName;?>';
                    }
                );
        });
							     
								 
		$('#euro').click(function()
        {
        
        var param =  <?php echo $iddxftes; ?>;
            $.ajax(
                    {  
                        url: "./lista_pdf.php",
                        type: "post",
                        data: { iddxftes: param,
                                euro: '1'
                        },
                        beforeSend: function(result) {
                            $('#msg').html('Lista costi "<?php echo $descrizione; ?>" in elaborazione, attendere...');
                            $('#exampleModalCenter').modal('show');
                            },
                        success: function(result) {
                            $('#exampleModalCenter').modal('hide');
                            window.open('./data/<?php echo $moduleName; ?>/<?php echo $moduleName; ?>_lista_costi.pdf','_blank');	
                            }
                    }
                );
        });
        
        } );						 

</script>