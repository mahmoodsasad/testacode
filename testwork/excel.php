<?php

require 'vendor/autoload.php';
//error_reporting(E_ALL);

ini_set('display_errors', 0);

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

if (isset($_POST['submit'])) {
  $mainFile = $_FILES['main_file'];
  $cpuFile = $_FILES['cpu_file'];

  if (empty($mainFile) || empty($cpuFile)) {
    $error = "Please select both files";
  } else {
    $mainFileType = IOFactory::identify($mainFile['tmp_name']);
    $cpuFileType = IOFactory::identify($cpuFile['tmp_name']);

    if (!in_array($mainFileType, ['Xlsx', 'Xls'])) {
      $error = "Invalid main file format. Please upload an Excel file.";
    } elseif (!in_array($cpuFileType, ['Xlsx', 'Xls'])) {
      $error = "Invalid CPU file format. Please upload an Excel file.";
    } else {
      $mainReader = IOFactory::createReaderForFile($mainFile['tmp_name']);
      $cpuReader = IOFactory::createReaderForFile($cpuFile['tmp_name']);
      
      $mainSpreadsheet = $mainReader->load($mainFile['tmp_name']);
      $cpuSpreadsheet = $cpuReader->load($cpuFile['tmp_name']);

      $mainSheet = $mainSpreadsheet->getActiveSheet();
      $cpuSheet = $cpuSpreadsheet->getActiveSheet();
      
      $mainData = $mainSheet->toArray();
      $cpuData = $cpuSheet->toArray();
      
       array_shift($mainData);
       array_shift($cpuData);
       

         
       
       $tempDataMain=[];
       $sourceOfTruthHeading=['Name', 'IP', 'Application', 'Entity', 'CPU', 'Memory', 'Disk', 'Status', 'OS_Version'];
    
       foreach ($mainData as $key=>$value){
           foreach ($value as $innerkey => $mainSheetVal){
                  $tempDataMain[$key][$sourceOfTruthHeading[$innerkey]]=$mainSheetVal;
           } 
       }
       
     //echo "<pre>"; print_r($tempDataMain); die();
       
        
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
      
       /* Now combine main and CPU data */
       
       $finalArray = [];
       
       foreach ($tempDataMain as $mainKey => $mainRow){
           $finalArray[$mainKey] = $mainRow;
           $finalArray[$mainKey]['CPU_Usage'] = '';
           $finalArray[$mainKey]['Memory_Usage'] = '';
foreach ($tempDataCpu as $cpuKey => $cpuRow){
    $cpuIP = explode(':', $cpuRow['Name'])[0]; // Remove port number if present
    error_log("Comparing: Main IP: " . $mainRow['IP'] . " with CPU IP: " . $cpuIP);
    if(strtoupper($mainRow['IP']) == strtoupper($cpuIP)){
       $finalArray[$mainKey]['CPU_Usage'] = $cpuRow['CPU_Usage'];
       $finalArray[$mainKey]['Memory_Usage'] = $cpuRow['Memory_Usage'];
       error_log("Match found for IP: " . $mainRow['IP']);
    }
}
       }
       
      $result = [];
       
      foreach ($finalArray as $data) {
           $result[] = [
               "Name" => $data["Name"],
               "IP" => $data["IP"],
               "Application" => $data["Application"],
               "Entity" => $data["Entity"],
               "CPU" => $data["CPU"],
               "Memory" => $data["Memory"],
               "Disk" => $data["Disk"],
               "Status" => $data["Status"],
               "OS_Version" => $data["OS_Version"],
               "CPU_Usage" => $data["CPU_Usage"],
               "Memory_Usage" => $data["Memory_Usage"]
           ];
      }
      
      //echo "<pre>"; print_r($result);die();
      
      $outputSpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $outputSheet = $outputSpreadsheet->getActiveSheet();

      $headers = ['Name', 'IP', 'Application', 'Entity', 'CPU', 'Memory', 'Disk', 'Status', 'OS_Version', 'CPU_Usage', 'Memory_Usage', 'Remarks'];
      $outputSheet->fromArray([$headers], NULL, 'A1');

      $sheetRow = 2;
      foreach ($result as $value) {
           $remarks = '';
           $outputSheet->fromArray($value, NULL, 'A' . $sheetRow);
           
           if($value['CPU_Usage'] >= 80){
               $outputSheet->getStyle('J'.$sheetRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ff0000');
               $remarks .= "High CPU Utilization,";
           } elseif($value['CPU_Usage'] >= 60){
               $outputSheet->getStyle('J'.$sheetRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffff00');
               $remarks .= "Medium CPU Utilization,";
           }
           
           if($value['Memory_Usage'] >= 80){
               $outputSheet->getStyle('K'.$sheetRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ff0000');
               $remarks .= "High Memory Utilization,";
           } elseif($value['Memory_Usage'] >= 60){
               $outputSheet->getStyle('K'.$sheetRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ffff00');
               $remarks .= "Medium Memory Utilization,";
           }
           
           $outputSheet->setCellValue('L' . $sheetRow, rtrim($remarks, ','));
           
           $sheetRow++;
      }
    
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="data.xlsx"');

      // Write the spreadsheet to the browser for download
      $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($outputSpreadsheet, 'Xlsx');
      $writer->save('php://output');
      exit();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload and Processing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        header {
            background: #333;
            color: #fff;
            padding-top: 30px;
            min-height: 70px;
            border-bottom: #77aaff 3px solid;
        }
        header a {
            color: #fff;
            text-decoration: none;
            text-transform: uppercase;
            font-size: 16px;
        }
        header ul {
            padding: 0;
            list-style: none;
        }
        header li {
            float: left;
            display: inline;
            padding: 0 20px 0 20px;
        }
        .main {
            padding: 20px;
            background: #fff;
            margin-top: 20px;
        }
        .main h1 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input[type="file"] {
            display: block;
        }
        .form-group input[type="submit"] {
            background: #333;
            color: #fff;
            border: 0;
            padding: 10px 15px;
            cursor: pointer;
        }
        .form-group input[type="submit"]:hover {
            background: #555;
        }
        .error {
            color: red;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>File Upload and Processing</h1>
        </div>
    </header>
    <div class="container main">
        <h1>Upload Files</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php elseif (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="main_file">Main Sheet (with Name, Application, etc.):</label>
                <input type="file" name="main_file" required>
            </div>
            <div class="form-group">
                <label for="cpu_file">CPU Sheet (with Name, CPU_Usage, Memory_Usage):</label>
                <input type="file" name="cpu_file" required>
            </div>
            <div class="form-group">
                <input type="submit" name="submit" value="Merge Data">
            </div>
        </form>
    </div>
</body>
</html>
