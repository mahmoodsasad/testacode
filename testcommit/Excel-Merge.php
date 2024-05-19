<?php

require 'vendor/autoload.php';
error_reporting(0);

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['submit'])) {
  $mainFile = $_FILES['main_file'];
  $cpuFile = $_FILES['cpu_file'];
  $hddFile = $_FILES['hdd_file'];

  if (empty($mainFile) || empty($cpuFile) || empty($hddFile)) {
    $error = "Please select all files";
  } else {
    $mainFileType = IOFactory::identify($mainFile['tmp_name']);
    $cpuFileType = IOFactory::identify($cpuFile['tmp_name']);
    $hddFileType = IOFactory::identify($hddFile['tmp_name']);

    if (!in_array($mainFileType, ['Xlsx', 'Xls'])) {
      $error = "Invalid main file format. Please upload an Excel file.";
    } elseif (!in_array($cpuFileType, ['Xlsx', 'Xls'])) {
      $error = "Invalid CPU file format. Please upload an Excel file.";
    } elseif (!in_array($hddFileType, ['Xlsx', 'Xls'])) {
      $error = "Invalid Hard Drive file format. Please upload an Excel file.";
    } else {
      $mainReader = IOFactory::createReaderForFile($mainFile['tmp_name']);
      $cpuReader = IOFactory::createReaderForFile($cpuFile['tmp_name']);
      $hddReader = IOFactory::createReaderForFile($hddFile['tmp_name']);
      
      $mainSpreadsheet = $mainReader->load($mainFile['tmp_name']);
      $cpuSpreadsheet = $cpuReader->load($cpuFile['tmp_name']);
      $hddSpreadsheet = $hddReader->load($hddFile['tmp_name']);

      $mainSheet = $mainSpreadsheet->getActiveSheet();
      $cpuSheet = $cpuSpreadsheet->getActiveSheet();
      $hddSheet = $hddSpreadsheet->getActiveSheet();
      
      

      $mainData = $mainSheet->toArray();
      $cpuData = $cpuSheet->toArray();
      $hddData = $hddSheet->toArray();
      
       array_shift($mainData);
       array_shift($cpuData);
       array_shift($hddData);
       
         
       $tempDataMain=[];
       $sourceOfTruthHeading=['Name', 'Application', 'Entity', 'CPU', 'Memory', 'Disk', 'Status', 'OS_Version'];
    
       foreach ($mainData as $key=>$value){
           foreach ($value as $innerkey => $mainSheetVal){
                  $tempDataMain[$key][$sourceOfTruthHeading[$innerkey]]=$mainSheetVal;
           } 
       }
       
     //  echo "<pre>"; print_r($tempDataMain); die();
       
        
       $tempDataCpu=[];
       $cpuHeading=['Name', 'CPU_Usage', 'Memory_Usage'];
       
       foreach ($cpuData as $key=>$value){
           foreach ($value as $innerkey => $cpuSheetVal){
               if($cpuSheetVal!=''){
                  $tempDataCpu[$key][$cpuHeading[$innerkey]]=$cpuSheetVal; 
               }
                  
           } 
       }
       
       //echo "<pre>"; print_r($tempDataCpu); die();
      
       $tempDataHdd=[];
       $hddHeading=['Name', 'Partition', 'Usage'];
       
       foreach ($hddData as $key => $value){
           foreach ($value as $innerkey =>$hddSheetVal){
               if($hddSheetVal!=''){
                  $tempDataHdd[$key][$hddHeading[$innerkey]]=$hddSheetVal; 
               }
                  
           } 
       }
       
       /* Now i combine all arrays and convert into desired out put Priniting The Final Array will give you idea what format to extract*/
       
       $finalArray=[];
       
       foreach ($tempDataMain as $mainKey => $mainRow){
           $finalArray[$mainKey]=$mainRow;
           $finalArray[$mainKey]['CPU_Usage']='';
           $finalArray[$mainKey]['Memory_Usage']='';
           foreach ($tempDataCpu as $cpuKey => $cpuRow){
               if($mainRow['Name']===$cpuRow['Name']){
                  $finalArray[$mainKey]['CPU_Usage']=$cpuRow['CPU_Usage'];
                  $finalArray[$mainKey]['Memory_Usage']=$cpuRow['Memory_Usage'];
               }
           }
           
           $finalArray[$mainKey]['Partition']=[];
           $finalArray[$mainKey]['Usage']=[];
           $i=0;
           foreach ($tempDataHdd as $hddKey => $hddRow){
               if($mainRow['Name']===$hddRow['Name']){
                  $finalArray[$mainKey]['Partition'][$i]=$hddRow['Partition'];
                  $finalArray[$mainKey]['Usage'][$i]=$hddRow['Usage'];
                  $i++;
               }
               
           }
       }
       
      //echo "<pre>"; print_r($finalArray);die();
       
      
      // From Here Now i Am going to Put in Excel Library*/
      
      $outputSpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $outputSheet = $outputSpreadsheet->getActiveSheet();
      
      
      //=======Creating First Row As Header===========//
      $finalHeadings=['Name', 'Application', 'Entity', 'CPU', 'Memory', 'Disk', 'Status', 'OS_Version','CPU_Usage','Memory_Usage'];
           
      $sheetColumn = 'A';
      $sheetRow = 1;

      foreach($finalHeadings as $finalHeading){
        $outputSpreadsheet->getActiveSheet()->setCellValue($sheetColumn++ . $sheetRow, $finalHeading); 
      }
      
      $outputSpreadsheet->getActiveSheet()->setCellValue($sheetColumn++ . $sheetRow, 'Partition'); 
      $outputSpreadsheet->getActiveSheet()->setCellValue($sheetColumn++ . $sheetRow, 'Usage'); 
      
      //=============Now Let Print The inner Data of Final Array==========//
       
       $sheetRow=2;
       foreach ($finalArray as $outerKey => $outerValue) {       
            $sheetColumn = 'A';
            foreach ($finalHeadings as $heading) {
                $outputSpreadsheet->getActiveSheet()->setCellValue($sheetColumn++ . $sheetRow, $outerValue[$heading]);
            }
            
             if(count($outerValue['Partition']) > 0){
                  $innerRow = $sheetRow;
                  foreach ($outerValue['Partition'] as $key => $partition) {
                        $innerSheetColumn = $sheetColumn;
                        $outputSpreadsheet->getActiveSheet()->setCellValue($innerSheetColumn++ . $innerRow, $partition);
                        $outputSpreadsheet->getActiveSheet()->setCellValue($innerSheetColumn++ . $innerRow,  $outerValue['Usage'] [$key]);
                        $innerRow++;
                  }
                }else{
                    $innerSheetColumn = $sheetColumn;
                    $outputSpreadsheet->getActiveSheet()->setCellValue($innerSheetColumn++ . $innerRow, '-');
                    $outputSpreadsheet->getActiveSheet()->setCellValue($innerSheetColumn++ . $innerRow, '-');
                    $innerRow++;
                }
            $sheetRow= $innerRow;
      }
      
      
     
       header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
       header('Content-Disposition: attachment;filename="data.xlsx"');

      // Write the spreadsheet to the browser for download
      $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($outputSpreadsheet, 'Xlsx');
      $writer->save('php://output'); die();  
      
      //======= End Here From Jasim Side================//
                
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Merge Excel Data</title>
</head>
<body>

<?php if (isset($error)): ?>
  <p style="color: red;"><?php echo $error; ?></p>
<?php elseif (isset($message)): ?>
  <p style="color: green;"><?php echo $message; ?></p>
<?php endif; ?>

<h1>Merge Excel Data</h1>
<form method="post" enctype="multipart/form-data">
  <label for="main_file">Main Sheet (with Name, Application, etc.):</label>
  <input type="file" name="main_file" required><br><br>
  <label for="cpu_file">CPU Sheet (with Name, CPU_Usage, Memory_Usage):</label>
  <input type="file" name="cpu_file" required><br><br>
  <label for="hdd_file">Hard Drive Sheet (with Name, Partition, Usage):</label>
  <input type="file" name="hdd_file" required><br><br>
  <input type="submit" name="submit" value="Merge Data">
</form>

</body>
</html>
