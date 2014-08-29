<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include "src/classes/Users.class.php";
	include "src/classes/UnidadeCons.class.php";
	include "src/classes/Notas.class.php";
	include_once "src/classes/Relatorio.class.php";
	
		//$tpl->TESTE = $emp->getServicos()[2]->children();	
	
	
	redirectByPermission(0); // SETAR PERMISSÃO DA PÁGINA
	$inicio = execucao();
		
	$tpl = new Template('html_libs/template_livre.html');
	
	$tpl->MENU_NAME = "Relatórios desta UC nos anos de:";
	$tpl->addFile('JSCRIPT','cssjs_libs/js_parts/js_udd_ucDetalhes.html');
	
	$anoAtual = (isset($_GET['ano']) && $_GET['ano']>=2012 && $_GET['ano']<=Date('Y')) ? $_GET['ano'] : Date('Y');
	$anoAnterior = $anoAtual - 1;
	
	if(isset($_GET['chave'])){
	
		$chave = mysql_real_escape_string($_GET['chave']);
		if(strlen($chave) == 40){
		
			$queryVerif = mysql_query("SELECT id FROM daee_uddc WHERE SHA1(id) = '".$_GET['chave']."'");
			$tpl->CHAVE	 = $chave;
			if(mysql_num_rows($queryVerif))	{

				/***********************
				 MOSTRAR MENU COM ANOS
				***********************/
				$iMin = 2012;
				$iCalc= $anoAtual-5;
				$iFor = $iCalc < $iMin ? $iMin : $iCalc;
				for($i=$iFor; $i<=Date('Y');$i++){
				
					$tpl->ITEM_MENU_URL = "resumo_uc.php?chave=" . $_GET['chave'] . "&ano=" . $i;
					$tpl->ITEM_MENU_LINK= $i;
					$tpl->block('ITEM_MENU');
				
				}
			
				$id = mysql_fetch_array($queryVerif)['id'];
				$uc = new UnidadeConsumidora($id);
				$contrato = $uc->get('contrato');

				if($_SESSION['bacia'] == $uc->get('uo')->get('id') || $_SESSION['nivel'] == 1){
				
					$tpl->addFile('CONTEUDO','html_libs/resumo_uc.html');
					
					/*********************************
					  MOSTRAR INFORMAÇÕES DO RELATÓRIO
					 *********************************/ 
					
					$rel = new Relatorio();
					$cores = $rel->cores;					
					$tpl->UCCOLOR	= $cores[$uc->get('tipo')];					
					
					$tpl->ANO = $anoAtual;
					$tpl->ANO_ANT = $anoAnterior;
					$tpl->TIPOCONS	= $uc->getTipoMedida();
					$tpl->RGI 		= $uc->get('rgi');					
					$tpl->NOME		= $uc->getNome();
					$tpl->TIPONOME	= $uc->getTipoNome();
					$tpl->EMPRESA	= $uc->get('empresa')->getNome(). " CNPJ ".$uc->get('empresa')->transformCnpj($uc->get('empresa')->get('cnpj'));
					$tpl->ENDERECO	= $uc->getEndereco() . " - " . $uc->getCidadeNome();
					$tpl->CONTRATO	= $contrato->geraNome();
					$tpl->UNIDADE	= $uc->get('uo')->getSigla();
					
					
					$eachMes = $uc->getExercicioMensal($anoAtual);

					$mesConsTotal = 0;
					$mesPagoTotal = 0;
					$z = 1;
					$somaAnt = $somaPost = 0; 
					foreach($eachMes as $mes){
					
						$mesConsTotal += $mes['consumo'];
						$mesPagoTotal += $mes['valor'];				
						$tpl->MESSOMA = strtoupper(getMesNome($mes['mes_ref']));
						$tpl->CONSSOMA	= tratarValor($mes['consumo']);
						$tpl->PAGOSOMA	= tratarValor($mes['valor'], true);
						
						if($z > 1 && $z <= count($eachMes)){
						
							$divisor = ($eachMes[$z-1]['valor'] == 0) ? 1 : $eachMes[$z-1]['valor'];
							$varia = (($eachMes[$z]['valor'] * 100) / $divisor) - 100;
							$tpl->VARIASOMA = getPorcentagem($varia, true);
						
						}else{
						
							$tpl->VARIASOMA = "";
						
						}
						
											
						$tpl->BLOCK('EACH_MENSAL');
						$z++;
					
					}
					$tpl->CONSTOTAL = tratarValor($mesConsTotal);
					$tpl->PAGOTOTAL = tratarValor($mesPagoTotal, true);
					
					$mediaUC = (count($eachMes) > 0) ? $mesPagoTotal / count($eachMes) : 1;
					$tpl->MEDIA	= tratarValor($mediaUC, true);
					//$tpl->AUMENTO = getPorcentagem((end($eachMes)['valor'] * 100) / $mediaUC - 100 ,true);
					
					
					/*********************************
					   MOSTRAR RELATÓRIOS ANALÍTICOS
					 *********************************/ 				
					
					$exercicioPassado = $uc->getExercicioMensal($anoAnterior);
					$r = 1;
					$s = 1;
					$media['cons_ant'] = 0;
					$media['pago_ant'] = 0;
					$media['cons_post'] = 0;
					$media['pago_post'] = 0;
					
					//print_r($exercicioPassado);
					
					for($i=1;$i<=13;$i++){
					
						$tpl->MESANALITICO = strtoupper(getMesNome($i));
						
						if(array_key_exists($r, $exercicioPassado) && $exercicioPassado[$r]['mes_ref'] == $i){ 
						
							$tpl->CHART_CONSANT = $consant = $exercicioPassado[$r]['consumo'];
							$tpl->CHART_PAGOANT = $pagoant = $exercicioPassado[$r]['valor'];
							$tpl->CONSANT = tratarValor($exercicioPassado[$r]['consumo']);
							$tpl->PAGOANT = tratarValor($exercicioPassado[$r]['valor'],true);
							$r++;
						
						}else{
						
							$tpl->CHART_CONSANT = $tpl->CHART_PAGOANT = $consant = $pagoant = 0;
							$tpl->CONSANT = 0;
							$tpl->PAGOANT = tratarValor(0, true);
						
						}
						
						if(array_key_exists($s, $eachMes) && $eachMes[$s]['mes_ref'] == $i){
						
							$tpl->CHART_CONSPOST = $conspost = $eachMes[$s]['consumo'];
							$tpl->CHART_PAGOPOST = $pagopost = $eachMes[$s]['valor'];
							$tpl->CONSPOST = tratarValor($eachMes[$s]['consumo']);
							$tpl->PAGOPOST = tratarValor($eachMes[$s]['valor'],true);
							$s++;
						
						}else{
						
							$tpl->CHART_CONSPOST = $tpl->CHART_PAGOPOST = $conspost = $pagopost = 0;
							$tpl->CONSPOST = 0;
							$tpl->PAGOPOST = tratarValor(0,true);
						
						}
						
						$media['cons_ant']	+= $consant; 
						$media['pago_ant']	+= $pagoant;
						$media['cons_post']	+= $conspost;
						$media['pago_post']	+= $pagopost;
						
						if($i == 13){ // MOSTRAR MÉDIAS
						
							$r = ($r == 1) ? 2 : $r;
							$s = ($s == 1) ? 2 : $s;
							
							$tpl->CHART_CONSANT = $media_consant	= round($media['cons_ant'] / ($r-1) * 100) / 100;
							$tpl->CHART_PAGOANT = $media_pagoant	= round($media['pago_ant'] / ($r-1) * 100) / 100;
							$tpl->CHART_CONSPOST = $media_conspost	= round($media['cons_post'] / ($s-1) * 100) / 100;
							$tpl->CHART_PAGOPOST = $media_pagopost	= round($media['pago_post'] / ($s-1) * 100) / 100;
							
							$tpl->CONSANT	= tratarValor($media_consant);
							$tpl->PAGOANT	= tratarValor($media_pagoant,true);					
							$tpl->CONSPOST	= tratarValor($media_conspost);
							$tpl->PAGOPOST	= tratarValor($media_pagopost, true);
							$tpl->COMA = "";
						
						}else{
						
							$tpl->COMA = ",";
						
						}
						
						if($conspost != 0 && $pagopost != 0 && $consant != 0 && $pagoant != 0){
						
							$cons_varia = (($conspost * 100) / $consant) - 100;
							$pago_varia = (($pagopost * 100) / $pagoant) - 100;
							
						
						} else{
						
							$cons_varia = $pago_varia = 0;
						
						}
						
						
						$tpl->CONSVARIA	= getPorcentagem($cons_varia, true);
						$tpl->PAGOVARIA = getPorcentagem($pago_varia, true);
						
						$tpl->block('EACH_CONSANALITICO');
						$tpl->block('EACH_PAGOANALITICO');
						
						$tpl->block('EACH_CHART_CONS');
						$tpl->block('EACH_CHART_PAGO');
					
					}
					if($uc->get('tipo') <= 2) $tpl->block('CONSUMOANALITICO');
				
				}else{
					
					$tpl->CONTEUDO = "<div class='alerta' align='center' style='height:200px;padding-top:100px'>Você não tem permissão para acessar os dados ";
					$tpl->CONTEUDO.= "desta Unidade Consumidora. <a href='resumo_index.php?ano=$anoAtual'><b>Clique aqui para voltar.</b></a></div>";
					
				}
			
			}else{
			
				header("Location: udd_relatorioUC.php");
			
			}
		
		}else{
		
			header("Location: udd_relatorioUC.php");
		
		}
	
	}	
	
	
	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos";
	
	$tpl->show();
	
?>