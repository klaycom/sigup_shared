<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include "src/classes/Users.class.php";
	include "src/classes/UnidadeCons.class.php";

	redirectByPermission(0); // SETAR PERMISSÃO DA PÁGINA
	$tpl = new Template('html_libs/ucResumo.html');
	
	if(isset($_GET['chave'])){
	
		$chave = mysql_real_escape_string($_GET['chave']);
		if(strlen($chave) == 40){
		
			$queryVerif = mysql_query("SELECT id FROM daee_uddc WHERE SHA1(id) = '".$_GET['chave']."'");
			if(mysql_num_rows($queryVerif) == 1)	{
					
				$id = mysql_fetch_array($queryVerif)['id'];
				$uc = new UnidadeConsumidora($id);

				/*********************************
				  MOSTRAR INFORMAÇÕES DO RELATÓRIO
				 *********************************/ 
				
				$hasConsumo = $uc->hasConsumo();
				if($hasConsumo){
				
					$tpl->block('SHOW_HEADER_CONS');
				
				}			
				
				$distinct_sql	= "SELECT DISTINCT(ano_ref) FROM daee_notas WHERE uc = $id ORDER BY ano_ref ASC";
				$distinct_query	= mysql_query($distinct_sql);
				$exArray;
				$exArrayIndex = 0;
				while($ano = mysql_fetch_array($distinct_query)){
				
					$tpl->ANO = $ano = $ano['ano_ref'];
					$exercicio = $uc->getExercicioMensal($ano);
					$tpl->MESESPG = $meses_count = count($exercicio);
					
					$totCons = $totPago = 0;
					foreach($exercicio as $mes){
					
						if($hasConsumo)
							$totCons += $mes['consumo'];
						$totPago += $mes['valor'];
					
					}
					
					if($hasConsumo){
					
						$exArray[$exArrayIndex]['cons'] = ($totCons / $meses_count);
						$varCons		= ($exArrayIndex > 0) ? (round(($exArray[$exArrayIndex]['cons'] / $exArray[$exArrayIndex - 1]['cons'] - 1) * 100)) / 100  : 0;
						$tpl->TOTCONS	= tratarValor($totCons, false);
						$tpl->MEDCONS	= tratarValor($exArray[$exArrayIndex]['cons'], false);
						$tpl->VARCONS	= getPorcentagem($varCons * 100, true);
						$tpl->block('SHOW_CONS');
					
					}
					
					$exArray[$exArrayIndex]['pago'] = ($totPago / $meses_count);
					$varPago			= ($exArrayIndex > 0) ? (round(($exArray[$exArrayIndex]['pago'] / $exArray[$exArrayIndex - 1]['pago'] - 1) * 100)) / 100  : 0;
					$tpl->TOTPAGO 		= tratarValor($totPago, true);
					$tpl->MEDPAGO 		= tratarValor($exArray[$exArrayIndex]['pago'], true);
					$tpl->VARPAGO		= getPorcentagem($varPago * 100, true);					
					$tpl->block('EACH_ANO_RESUMO');
					$exArrayIndex++;
//				var_dump($exercicio);
				}
			
			
			}else{
			
				header("Location: udd_relatorioUC.php");
			
			}
		
		}else{
		
			header("Location: udd_relatorioUC.php");
		
		}
	
	}	
	
	$tpl->show();
	
?>