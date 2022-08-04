<?php

    $con=mysqli_connect("localhost","root","","eboard");
    mysqli_set_charset($con, 'utf8');
    // Check connection
    if (mysqli_connect_errno())
    {
    echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br />";
    }

    $result = mysqli_query($con, "SELECT * FROM ebs");
    
    if (!mysqli_num_rows($result))
        die('No records');
    
    echo '
    <br />
    <br />
    <center>
    <h2>E-Board list</h2>
    </center>
    <div class="row">
        <div style=""></div>
        <button class="btn btn-primary" id="insert" style="border-radius: 100%; width:60px;height:60px; margin-left:auto;margin-right: 12px; margin-bottom: 12px; font-size: 28px;">+</button>
    </div>
    <table id="ebTable" class="table table-striped table-bordered" style="margin-left: auto; margin-right: auto; ">
    <thead>
        <tr>
            <th>Item</th>
            <th>Version</th>
            <th>Description</th>
            <th>Last Updates</th>
            <th>Status</th>
        </tr>
    </thead>'
    ;
    echo "<tbody>";
    while($row = mysqli_fetch_array($result)) 
    {
        
        switch ($row['status']) {
            case 0:
                $status = '<button type="button" class="btn btn-danger">In review</button>';
                break;
            case 1:
                $status = '<button type="button" class="btn btn-success">All datas available</button>';
                break;
            case 2:
                $status = '<button type="button" class="btn btn-secondary">Deprecate</button>';
                break;
            default:
                $status = '<button type="button" class="btn btn-warning">Partial data</button>';
                break;
        }
        
        echo "<tr>";
            echo "<td><a href='#' class='ebItem' key=".$row['id'].">" . $row['item_code'] . "</a></td>";
            echo "<td>" . $row['version'] . "</td>";
            echo "<td>" . $row['description'] . "</td>";
            echo "<td  data-sort=".  strtotime($row['last_update']) .">" . date('d-m-Y H:i',strtotime($row['last_update'])) . "</td>";
            echo "<td>" . $status . "</td>";
        echo "</tr>";
    };
    echo "</tbody>";
    echo "</table>";

    mysqli_close($con);
      
  ?>
  <div id='riempi'></div>
    <br />
    <br />

  <script type="text/javascript">

    $(document).ready(function() {
        $('#ebTable').DataTable(
                    {
            "order": [[ 3, "desc" ]],
            "lengthMenu": [[25, 50, -1], [25, 50, "All"]]	
            } 			
        );
    });
      
    $(".ebItem").click(function()
    {
    var param =  $(this).attr("key");
    $.ajax(
        {  
        url: "details.php",
        type: "POST",
        data: { iddxftes: param
        },
        success: function(response)
                        {
                        $('#riempi').html('');
                        $("#riempi").html(response);
                        $('#dxfLista').DataTable(
                                        {
                                        "dom": 'Blfrtip',
                                        "buttons": [ 'excel', 'pdf', 'copy' ],
                                        "order": [[ 1, "asc" ]]
                                        }
                        );
                        } 
        }
        );
    });
                                  
    $("#insert").click(function() {
        $.ajax(
        {  
        url: "./insert.php",
        type: "POST",
        success: function(response) {
            $('#main').html(response);
        }
        });
    })                              
                                  
  </script>
