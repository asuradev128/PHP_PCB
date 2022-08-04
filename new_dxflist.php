
	
    <?php
        
        if(!empty($_POST['code']) && !empty($_FILES['input_XLSX']) && !empty($_FILES['input_DXF']) ) {
			
			
			$code = $_POST['code'];
			$version = $_POST['version'];
			if(!empty($_POST['version']))
			$moduleName = $code.'_'.$version;
			else
			$moduleName = $code;	
            $dxfFolderName = 'data/' . $moduleName . '/' . $moduleName . '_dxf';
			$pdfFolderName = 'data/' . $moduleName . '/' . $moduleName . '_pdf';
            //The name of the directory that we need to create.
            $uploadDirectory = 'data';
                    
            //Check if the directory already exists.
            if(!is_dir($uploadDirectory))
                mkdir($uploadDirectory, 0755);

            if(!is_dir('data/' . $moduleName))
                mkdir('data/' . $moduleName , 0755);

            if(!is_dir($dxfFolderName))
                mkdir($dxfFolderName, 0755);
			
			  if(!is_dir($pdfFolderName))
                mkdir($pdfFolderName, 0755);
			
            $target_dir = 'data/' . $moduleName . '/';
            
            $uploadOk = 1;
            
            $target_file = $target_dir . $moduleName . '_list.xlsx';
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $excelFile = $target_file;
            
            if ($_FILES["input_XLSX"]["size"] > 200000) {
                echo "Sorry, your Excel file is too large." . "<br />";
                $uploadOk = 0;
            }
            // Allow certain file formats
            else if($fileType != "xlsx" && $fileType != "xls" ) {
                echo "Sorry, only xlsx, xls files are allowed." . "<br />";
                $uploadOk = 0;
            }
            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                echo "Sorry, your Excel file was not uploaded." . "<br />";
            // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["input_XLSX"]["tmp_name"], $target_file)) {
                    echo "<br>The file ". basename( $_FILES["input_XLSX"]["name"]). " has been uploaded." . "<br />";
                } else {
                    echo "Sorry, there was an error uploading your Excel file." . "<br />";
                }
            }
        
            $count = 0;

            if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                foreach ($_FILES['input_DXF']['name'] as $i => $name) {
                    if (strlen($_FILES['input_DXF']['name'][$i]) > 1) {
                        $fileType = strtolower(pathinfo($_FILES['input_DXF']['name'][$i], PATHINFO_EXTENSION));

                        if($fileType != "dxf" ) {
                            echo "Sorry, only dxf files are allowed." . "<br />";
                            break;
                        }

                        if (move_uploaded_file($_FILES['input_DXF']['tmp_name'][$i], 'data/'. $moduleName . '/' . $moduleName . '_dxf/' . $name)) 
                            $count ++;
                    }
                }
            }
			
			$con=mysqli_connect("localhost","root","","eboard");
            mysqli_set_charset($con, 'utf8');
            // Check connection
            if (mysqli_connect_errno())
            {
            echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br />";
            }
			else
			{
				$result = mysqli_query($con,"SELECT ID from ebs where item_code='".$_POST['code']."' and version='".$_POST['version']."'");
				$numrow = mysqli_num_rows($result);
				if($numrow > 0)
				{
					die("Attenzione: code e version esistenti");
					
				}
				mysqli_query($con,"INSERT INTO ebs (item_code,version,description) value ('".$_POST['code']."','".$_POST['version']."','".$_POST['description']."')");
				$result = mysqli_query($con,"SELECT id from ebs where code='".$_POST['code']."' and version='".$_POST['version']."' and description='".$_POST['description']."'");
				$row = mysqli_fetch_array($result);
				$moduleName = $row['ID'];
			}
			
            if($count == 0)
                echo "Sorry, there was an error upload your DXF files" . "<br />";
            else
			{
                echo "<br>The DXF Folder has been uploaded" . "<br />";
				echo "<br> ExcelFile: $excelFile <br>";
				echo "<br> dxfFolderName: $dxfFolderName <br>";
				echo "<br> IDDXFTES: $moduleName <br>";
				$runPythonCmd = 'python getDxfProperties.py ' . $excelFile . ' ' . $dxfFolderName . ' ' . $moduleName;
						
			ini_set("max_execution_time", "600");
            $command = escapeshellcmd($runPythonCmd);
            $output = shell_exec($command);
			
			}
			//echo $output;
			write_table($moduleName);
			unset($_FILES['input_DXF']);
			unset($_FILES['input_XLSX']);
		} 
			
		//divido per 1000 una stringa numerica e la formatto -- Es. 13 = 0.013	
        function pad($number, $min_digits){
                return strrev(
                    implode(",",str_split(str_pad(strrev($number), $min_digits, "0", STR_PAD_RIGHT),3))
                );
            }
            
		function write_table($code)
		{
            $con=mysqli_connect("localhost","root","","dxf");
            mysqli_set_charset($con, 'utf8');
            // Check connection
            if (mysqli_connect_errno())
            {
            echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br />";
            }

            $result = mysqli_query($con,"SELECT * FROM boms inner join ebs on ebs.id = boms.ebId where ebId='".$code."'");
			
			$row = mysqli_fetch_array($result);
			
			if($row['version'] != '' )
			$moduleName = $row['code'].'_'.$row['version'];
			else
			$moduleName = $row['code'];	

            echo '
            <br />
            <br />
			<center>
			<h2> LISTA: '.$row['code'].' - '.$row['description'].' </h2>
			<h3>version : '.$row['version'].' </h3>
			<h4> Ultimo Aggiornamento : '.$row['inserimento'].' </h4>
			</center>
            <table id="dxfTable1" class="table table-striped table-bordered" style="margin-left: auto; margin-right: auto; ">
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
                    <th> Productor
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
        }
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#dxfTable1').DataTable(
				{
				} 
			);
        } );
    </script>