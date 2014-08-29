<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include "src/classes/Users.class.php";
	include "src/classes/UnidadeCons.class.php";
	include "src/classes/Notas.class.php";
	
		//$tpl->TESTE = $emp->getServicos()[2]->children();	
	
	
	redirectByPermission(1); // SETAR PERMISSÃO DA PÁGINA
	$inicio = execucao();
	
	$tpl = new Template('html_libs/template.html');
	
	$tpl->addFile('SECONDMENU','html_libs/udd_secondMenu.html');
	$tpl->addFile('JSCRIPT','cssjs_libs/js_parts/js_udd_uoCharts.html');
	
	$ucArray;
	$uoArray;
	if(isset($_GET['chave'])){
	
		$chave = $_GET['chave'];
		if(strlen($chave) == 40){
	
			$svc	= (isset($_GET['svc']) && $_GET['svc'] > 0) ? $_GET['svc'] : 0;
			$start	= (isset($_GET['start']) && $_GET['start'] >= 2010) ? $_GET['start'] : Date('Y');
			
			$tpl->addFile('CONTEUDO','html_libs/udd_uoCharts.html');
			$queryVerif = mysql_query("SELECT id FROM daee_udds WHERE SHA1(id) = '".$_GET['chave']."'");
			if(mysql_num_rows($queryVerif) == 1)	{	

				$id = mysql_fetch_array($queryVerif)['id'];			
				/*********************************
				 MOSTRAR INFORMAÇÕES DO RELATÓRIO
				**********************************/
				$uo = new Unidade($id);
				if(in_array($svc, $uo->getTiposServicos())){
				
					$tpl->PERIODO	= $start;
					$tpl->UOSIGLA	= $uo->getSigla();
					
					$numMeses		= ( 12 * (Date('Y') - $start) ) + Date('n');
					$ucTipo			= new UnidadeConsumidora(0, null, null, null, null, null, null, null, null, null, null, $svc);
					$tpl->TIPONOME	= $ucTipo->getTipoNome();
					
					$ucids			= $ucTipo->getMesmoTipoPorUO($id);
					$tpl->UCQTD 	= count($ucids);
					$tpl->RGISINSTA	= "";

					/*********************************
					 MONTAR ARRAY COM TODOS OS VALORES
					**********************************/					
					foreach($ucids as $ucid){
					
						$uc = new UnidadeConsumidora($ucid);
						$tpl->RGISINSTA .= "<br />". $uc->getAtivoText() ." - <a href='udd_ucDetalhes.php?chave=". sha1($uc->get('id')) ."'>";
						$tpl->RGISINSTA .= $uc->get('rgi') . " - " . $uc->getNome() . "</a> "; //. $uc->getVariacaoDoMes(1, 2013)
						$tpl->TIPOCONS = $uc->getTipoMedida();
						
						$exerc_atu	= $uc->getExercicioMensal($start);
						$exerc_ant	= $uc->getExercicioMensal($start - 1);
						$mes_atu	= Date('n');
						
						$tpl->ANO_ANT	= $start-1;
						$tpl->ANO		= $start;
						
						for ($i=1; $i<=12; $i++){
						
							if(array_key_exists($i,$exerc_ant)){
							
								$ucArray[$ucid][$start-1]['consumo'][$i] = $exerc_ant[$i]['consumo'];
								$ucArray[$ucid][$start-1]['valor'][$i] = $exerc_ant[$i]['valor'];
								
							}else{
							
								$ucArray[$ucid][$start-1]['consumo'][$i] = 0;
								$ucArray[$ucid][$start-1]['valor'][$i] = 0;
							
							}
							
							if(array_key_exists($i,$exerc_atu)){
							
								$ucArray[$ucid][$start]['consumo'][$i] = $exerc_atu[$i]['consumo'];
								$ucArray[$ucid][$start]['valor'][$i] = $exerc_atu[$i]['valor'];
							
							}else{
							
								$ucArray[$ucid][$start]['consumo'][$i] = 0;
								$ucArray[$ucid][$start]['valor'][$i] = 0;
							
							}
						
						}
						
						if($start == Date('Y')){
							
							$tpl->VARIAHEADER = " - Variação de " . getMesNome($mes_atu - 2). " a " . getMesNome($mes_atu - 1) . " de " . Date('Y');
							$atuC = ($ucArray[$ucid][$start]['consumo'][$mes_atu - 1] > 0) ? $ucArray[$ucid][$start]['consumo'][$mes_atu - 1] : 0;
							$antC = ($ucArray[$ucid][$start]['consumo'][$mes_atu - 2] > 0) ? $ucArray[$ucid][$start]['consumo'][$mes_atu - 2] : 1;
							$atuV = ($ucArray[$ucid][$start]['valor'][$mes_atu - 1] > 0) ? $ucArray[$ucid][$start]['valor'][$mes_atu - 1] : 0;
							$antV = ($ucArray[$ucid][$start]['valor'][$mes_atu - 2] > 0) ? $ucArray[$ucid][$start]['valor'][$mes_atu - 2] : 1;
							$variaConsumo	= $atuC * 100 / $antC - 100;
							$variaPago		= $atuV * 100 / $antV - 100;
							$tpl->RGISINSTA.= "[CONSUMO: " . getPorcentagem($variaConsumo,true) . "] [PAGO: ". getPorcentagem($variaPago, true). "]";
							
						}
						
					}
					
					/************************************
					MONTAR TOTAIS DA UNIDADE EM UM ARRAY
					************************************/
					$uoTotal;
					$tpl->TESTE = "";
					foreach($ucArray as $array){
						
						foreach($array as $ano => $var){
						
							foreach($var as $tipo => $valores){
								
								foreach($valores as $mes =>$valor){
								
									if(!isset($uoTotal[$ano][$tipo][$mes])){
									
										$uoTotal[$ano][$tipo][$mes] = $valor;
									
									}else{
									
										$uoTotal[$ano][$tipo][$mes]+= $valor;
									
									}
									
								}	
							
							}
						
						}
					
					}
					
					/************************************
					      MONTAR GRÁFICOS E TABELAS
					************************************/
					for($i=1; $i <=12; $i++){
					
						$tpl->MESANALITICO = getMesNome($i);

						/************************************
								   INFOS DE TABELAS
						************************************/						
						$tpl->CONSANT	= tratarValor($uoTotal[$start-1]['consumo'][$i]);
						$tpl->CONSPOST	= tratarValor($uoTotal[$start]['consumo'][$i]);
						
						$consant = ($uoTotal[$start-1]['consumo'][$i] > 0) ? $uoTotal[$start-1]['consumo'][$i] : 1;
						$consatu = ($uoTotal[$start]['consumo'][$i] > 0) ? $uoTotal[$start]['consumo'][$i] : 0;
						$tpl->CONSVARIA	= ($uoTotal[$start-1]['consumo'][$i] > 0) ? getPorcentagem($consatu * 100 / $consant - 100, true) : "--";
						
						$tpl->block('EACH_CONSALL');
						
						$tpl->PAGOANT = tratarValor($uoTotal[$start-1]['valor'][$i], true);
						$tpl->PAGOPOST = tratarValor($uoTotal[$start]['valor'][$i],true);
						
						$pagoant = ($uoTotal[$start-1]['valor'][$i] > 0) ? $uoTotal[$start-1]['valor'][$i] : 1;
						$pagoatu = ($uoTotal[$start]['valor'][$i] > 0) ? $uoTotal[$start]['valor'][$i] : 0;
						$tpl->PAGOVARIA = ($uoTotal[$start-1]['valor'][$i] > 0) ? getPorcentagem($pagoatu * 100 / $pagoant - 100, true) : "--";
						
						$tpl->block('EACH_PAGOALL');
						
						/************************************
								   INFOS DE GRÁFICOS
						************************************/												
						$tpl->CHART_CONSANT	= $uoTotal[$start-1]['consumo'][$i];
						$tpl->CHART_CONSPOST= $uoTotal[$start]['consumo'][$i];
						
						$tpl->COMA = ($i < 12) ? "," : "";
						
						$tpl->CHART_PAGOANT = $uoTotal[$start-1]['valor'][$i];
						$tpl->CHART_PAGOPOST= $uoTotal[$start]['valor'][$i];
						
						$tpl->block('EACH_CHART_CONS');
						$tpl->block('EACH_CHART_PAGO');
						
						/************************************
								  INFOS DE GRÁFICOS
						************************************/
						
						$tpl->MESCHART = getMesNome($i);
						
						$tpl->block('EACH_AREACHART1');
						$tpl->block('EACH_AREACHART2');
						
						
					}
				
				
				}else{
					
					header("Location: udd_relatorioUO.php");
					
				}
				
			
			}else{
					
				header("Location: udd_relatorioUO.php");
					
			}			
			
		}else{
		
			header("Location: udd_relatorioUO.php");
		
		}
	
	}else{
		
			header("Location: udd_relatorioUO.php");
		
	}
	
	
	
	
	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos";
	
	$tpl->show();
	
	//var_dump($uoTotal);

?>