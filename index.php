<?php error_reporting(1);?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Servers Data</title>
<!--<link rel = "icon" href =  ".icon.png" type = "image/x-icon"> -->
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">SERVERS DETAILS</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>


<div class="panel panel-default">
  <div class="panel-heading">This data has been fethed from the live servers which is automatically refreshed an hour</div>
  <div class="panel-body">
    <div class="row gutters">
         <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12" >
        <div class="card">
        <div class="card-body">
        <div class="table-responsive">
        <table class="table table-grid" id="example" >
        <thead><tr>
	        <th>S.NO</th> 
		<th>Server NAME</th>  
	        <th>IPAddress</th>  
		<th>BootUpTime</th>     
	        <th>UpTime</th>  
		<th>C Total Size</th> 
	        <th>C: Free space %</th>
	        <th>D Total Size</th>
		<th>D: Free space %</th>   
	        <th>Physical RAM</th> 
		<th>Memory %</th>
		<th>CPU %</th>  
    	</tr></thead>
        <tbody>
          <?php 

          if (($open = fopen("E:\serverdata.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($open, 1000, ",")) !== FALSE) {        
              $array[] = $data; 
            }
            fclose($open);
          } 
          $counter=1;
          foreach ($array as $key => $value) {
            if($key > 0){?>
            <tr>
            <td><?php echo $counter++; ?></td>
            <td><?php echo $value['0']; ?></td>
            <td><?php echo $value['1']; ?></td>
            <td><?php echo $value['2']; ?></td>
            <td><?php echo $value['3']; ?></td>
	    <td><?php echo $value['4']; ?></td>
	    <td><?php echo $value['5']; ?></td>
	    <td><?php echo $value['6']; ?></td>
	    <td><?php echo $value['7']; ?></td>
	    <td><?php echo $value['8']; ?></td>
	    <td><?php echo $value['9']; ?></td>
	    <td><?php echo $value['10']; ?></td>

          </tr>

          <?php } } ?>
  
        </tbody>
    </table>
  </div>
</div>

</body>

<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/bootstrap.min.js" type="text/javascript"></script>

    

<link rel="stylesheet" href="datatables/dataTables.bs4.css" />
<link rel="stylesheet" href="datatables/dataTables.bs4-custom.css" />


<script src="datatables/dataTables.min.js"></script>
<script src="datatables/dataTables.bootstrap.min.js"></script>

<script src="datatables/dataTables.buttons.min.js"></script>
<script src="datatables/jszip.min.js"></script>
<script src="datatables/pdfmake.min.js"></script>
<script src="datatables/vfs_fonts.js"></script>
<script src="datatables/buttons.html5.min.js"></script>
<script src="datatables/buttons.print.min.js"></script>
</html>
<script>
$(document).ready(function() {
      $('#example').DataTable({
            pageLength: 100,
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'excel', 'pdf', 'print' ,
            ],
            "aaSorting": [[0, "asc"]]
        });
} );
</script>