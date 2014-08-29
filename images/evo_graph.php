<?php
	require '../src/php_lot/phplot.php';

	if(isset($_GET['data'], $_GET['sigla'], $_GET['ano'])){
	
		$data = urldecode($_GET['data']);
		$data = stripslashes($data);
		$data = unserialize($data);
		$ano  = $_GET['ano'];
		$sigla= $_GET['sigla'];
		$show;
		$Ymin = min($data);
		foreach($data as $k=>$d){
			
			$show[] = array($k, (int) $d);
		
		}
		//var_dump($show);
		$plot = new PHPlot(800,400);
		$plot->SetDataValues($show);
		$plot->SetDataType('text-data');
		//$plot->SetTitle("");
		$plot->SetXTitle("Meses de " . $ano . " - " . $sigla);
		//$plot->SetNumYTicks(8);
		$plot->SetPointShapes('dot');
		$plot->SetPointSizes(10);
		$plot->SetPlotAreaWorld(NULL, $Ymin);
		$plot->SetYTitle("Valor Pago (R$)");
		$plot->DrawGraph();
		
	}
?>