<?php
session_start();
include("config.php");
include('classes/Utilities.class.php');
include('classes/SimpleImage.class.php');
include('PHPExcel/IOFactory.php');

if(isset($_SESSION['id']) AND isset($_SESSION['fullname'])){
	if(isset($_FILES['file0'])){
		$file = Utilities::upload($_FILES['file0'],'uploads/');
		echo $file;
	}
	elseif(isset($_FILES['file2'])){
		$inputFileName = 'uploads/'.Utilities::uploadFile($_FILES['file2'],'uploads/');
		//  Read your Excel workbook
		try {
			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
		} 
		catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		//  Get worksheet dimensions
		$sheet = $objPHPExcel->getSheet(0); 
		$highestRow = $sheet->getHighestRow(); 
		$highestColumn = $sheet->getHighestColumn();
		//  Loop through each row of the worksheet in turn
		for ($row = 2; $row <= $highestRow; $row++){
			//  Read a row of data into an array	
			$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
				if($rowData[0][1] != ""){
					$back = $bdd->query("SELECT city FROM shippingfees WHERE city='".sanitize_vars($rowData[0][2])."' LIMIT 0,1");
					if($back->rowCount() != 0){
						if(count(explode(";",sanitize_vars($rowData[0][5]))) == count(explode(";",sanitize_vars($rowData[0][4])))){
							$city = $back->fetch();
							$stocks = "";
							$qtys = explode(";",sanitize_vars($rowData[0][5]));
							$back = $bdd->query("SELECT id FROM stocks WHERE ref IN('".str_replace(";","','",sanitize_vars($rowData[0][4]))."') ORDER BY FIELD(ref,'".str_replace(";","','",sanitize_vars($rowData[0][4]))."')");
							if($back->rowCount() > 0){
								while($row1 = $back->fetch()){
									$stocks .= ",".$row1['id'];
								}
								$stocks = substr($stocks,1);
							}
							else{
								$stocks = str_replace(";",",",sanitize_vars($rowData[0][4]));
							}
							$rand = '';
							if($rowData[0][7] != ""){
								$back = $bdd->query("SELECT fullname FROM users WHERE id='".$_POST['client']."' AND trash='1'");
								$row1 = $back->fetch();
								$rand = sanitize_vars($rowData[0][7]);
							}
							else{
								do{
									$rand = 'CMD-'.gmdate('dmY').'-'.random();
									$back = $bdd->query("SELECT id FROM commands WHERE code='".$rand."'");
								}
								while($back->rowCount() != 0);						
							}
							if($rowData[0][8] == "1"){
								$rand = "CHANGE-".$rand;
							}
							$req = $bdd->prepare("INSERT INTO commands(id,code,product,qty,dlm,subdlm,client,fullname,phone,address,city,price,package,state,phase,datereported,note,collected,invoiced,invoiceddlm,confirmed,treated,archived,echange,openpackage,dateadd,dateupdate,trash) 
							VALUES ('0','".$rand."','".sanitize_vars($stocks)."','".str_replace(";",",",sanitize_vars($rowData[0][5]))."','0','0','".$_POST['client']."','".sanitize_vars($rowData[0][0])."','".sanitize_vars($rowData[0][1])."','".sanitize_vars($rowData[0][3])."','".sanitize_vars($city['city'])."','".sanitize_vars($rowData[0][6])."','0','".sanitize_vars($_POST['state'])."','".sanitize_vars($_POST['phase'])."','','".sanitize_vars($rowData[0][10])."','off','off','off','off','".sanitize_vars($_POST['confirmed'])."','0','".sanitize_vars($rowData[0][8])."','".sanitize_vars($rowData[0][9])."','".time()."','".time()."','1')");
							$req->execute();
							$req = $bdd->prepare("INSERT INTO commandshistory(id,command,state,agent,dateadd) VALUES ('0','".$rand."','".sanitize_vars($_POST['state'])."','".$_SESSION['fullname']."','".time()."')");
							$req->execute();
						}
						else{
							$_SESSION['errorimport'] .= "<tr>";
							$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][0])."</td>";
							$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][1])."</td>";
							$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][2])."</td>";
							$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][3])."</td>";
							$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][4])."</td>";
							$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][5])."</td>";
							$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][6])."</td>";
							$_SESSION['errorimport'] .= "<td>Verifier le Nb des produits et quantites</td>";
							$_SESSION['errorimport'] .= "</tr>";							
						}
					}
					else{
						$_SESSION['errorimport'] .= "<tr>";
						$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][0])."</td>";
						$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][1])."</td>";
						$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][2])."</td>";
						$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][3])."</td>";
						$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][4])."</td>";
						$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][5])."</td>";
						$_SESSION['errorimport'] .= "<td>".sanitize_vars($rowData[0][6])."</td>";
						$_SESSION['errorimport'] .= "<td>Ville n'existe pas</td>";
						$_SESSION['errorimport'] .= "</tr>";	
					}
				}				
		}
		unlink($inputFileName);	
	}
	elseif(isset($_FILES['file3'])){
		$inputFileName = 'uploads/'.Utilities::uploadFile($_FILES['file3'],'uploads/');
		//  Read your Excel workbook
		try {
			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
		} 
		catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		//  Get worksheet dimensions
		$sheet = $objPHPExcel->getSheet(0); 
		$highestRow = $sheet->getHighestRow(); 
		$highestColumn = $sheet->getHighestColumn();
		//  Loop through each row of the worksheet in turn
		for ($row = 2; $row <= $highestRow; $row++){
			//  Read a row of data into an array	
			$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
			//  Insert row data array into your database of choice here
			if($_POST['type'] == "client"){
				$back = $bdd->query("SELECT city FROM cities WHERE city='".$rowData[0][0]."' AND trash='1'");
				if($back->rowCount() > 0 AND is_numeric($rowData[0][1]) AND is_numeric($rowData[0][2])){
					$row1 = $back->fetch();
					$back = $bdd->query("SELECT city FROM clientfees WHERE client='".$_POST['client']."' AND city='".$rowData[0][0]."' AND trash='1'");
					if($back->rowCount() == 0){
						$req = $bdd->prepare("INSERT INTO clientfees(id,client,city,deliveredfees,refusedfees,returnedfees,trash) 
						VALUES ('0','".$_POST['client']."','".ucfirst(strtolower($row1['city']))."','".$rowData[0][1]."','".$rowData[0][2]."','0','1')");	
						$req->execute();
					}
				}				
			}
			else{
				$back = $bdd->query("SELECT city FROM shippingfees WHERE dlm='".$_POST['client']."' AND city='".$rowData[0][0]."' AND trash='1'");
				if($back->rowCount() == 0){
					$req = $bdd->prepare("INSERT INTO shippingfees(id,dlm,city,deliveredfees,refusedfees,trash) 
					VALUES ('0','".$_POST['client']."','".sanitize_vars($rowData[0][0])."','".$rowData[0][1]."','".$rowData[0][2]."','1')");	
					$req->execute();
				}				
			}
		}
		unlink($inputFileName);	
	}
	elseif(isset($_FILES['file4'])){
		$inputFileName = 'uploads/'.Utilities::uploadFile($_FILES['file4'],'uploads/');
		//  Read your Excel workbook
		try {
			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
		} 
		catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		//  Get worksheet dimensions
		$sheet = $objPHPExcel->getSheet(0); 
		$highestRow = $sheet->getHighestRow(); 
		$highestColumn = $sheet->getHighestColumn();
		//  Loop through each row of the worksheet in turn
		for ($row = 2; $row <= $highestRow; $row++){
			//  Read a row of data into an array	
			$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
			//  Insert row data array into your database of choice here
			if($rowData[0][0] != "" AND $rowData[0][1] != ""){
				$req = $bdd->query("UPDATE commands SET state='".$rowData[0][1]."',note='".$rowData[0][2]."',dateupdate='".time()."' WHERE code='".$rowData[0][0]."'");
				$req->execute();
			}
		}
		unlink($inputFileName);	
	}
}

function sanitize_vars($var){
	return htmlspecialchars(addslashes(trim($var)));
}

function random(){
	$alphabet = "0123456789";
	$pass = array(); //remember to declare $pass as an array
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($j = 0; $j < 5; $j++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass);
}
?>