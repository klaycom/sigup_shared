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
	
	//$tpl->addFile('JSCRIPT','cssjs_libs/js_parts/js_udd_ucDetalhes.html');
	$tpl->addFile('CONTEUDO','html_libs/resumo_index.html');
	
	$anoAtual = (isset($_GET['ano']) && $_GET['ano']>=2012 && $_GET['ano']<=Date('Y')) ? $_GET['ano'] : Date('Y');
	$anoAnterior = $anoAtual - 1;
	
	$uo = new Unidade($_SESSION['bacia']);
	$tpl->MENU_NAME = "Sua Unidade : " . $uo->getSigla();
	$tpl->UDD_NAME	= "<em>relatório " . $anoAtual . "</em>" . $uo->getSigla() . " : " . $uo->getNome();
	$tpl->QTD_ATIVO = $uo->getQtdUcStatus();
	$tpl->QTD_INATIVO=$uo->getQtdUcStatus(0);
	$tpl->UO_SIGLA	= $uo->getSigla();
	
	/***********************
	 MOSTRAR MENU COM ANOS
	***********************/
	$iMin = 2012;
	$iCalc= $anoAtual-5;
	$iFor = $iCalc < $iMin ? $iMin : $iCalc;
	for($i=$iFor; $i<=Date('Y');$i++){
				
		$tpl->ITEM_MENU_URL = "resumo_index.php?ano=" . $i;
		$tpl->ITEM_MENU_LINK= $i;
		$tpl->block('ITEM_MENU');
	
	}

	/***********************
	 MOSTRAR MENU COM ANOS
	***********************/
	$rel = new Relatorio();
	$cores = $rel->cores;
	$ucTipos = $uo->getTiposServicos();
	
	foreach($ucTipos as $tipo){
	
		//$tpl->BDCOLOR = "#4096EE";
		$tpl->BDCOLOR = $cores[$tipo];
		$ucSomas = $uo->getUcSomaByType($tipo, $anoAtual);
		$totais[0] = $totais[1] = 0;
		
		if($ucSomas != null){
			foreach($ucSomas as $uc){
			
				$tpl->UCRGI  = $uc['rgi'];
				$tpl->UCNOME = $uc['compl'] . " - " . $uc['rua'];
				$tpl->UCTIPO = $rel->getTipoNome($uc['tipo']);
				$tpl->UCCHAVE= sha1($uc['id']);
				$tpl->ANOATUAL= $anoAtual;
				$tpl->STATUS = getAtivoText($uc['ativo']);
				$tpl->CONSUMO= tratarValor($uc['SUM(n.consumo)']);
				$tpl->VALOR  = tratarValor($uc['SUM(n.valor)'],true);
				$tpl->block('EACH_UC');
				
				$totais[0] += $uc['SUM(n.consumo)'];
				$totais[1] += $uc['SUM(n.valor)'];
				
			
			}
		}
		$tpl->UCTIPOIND = $rel->getTipoNome($tipo);
		
		$tpl->TOTCONSUMO = tratarValor($totais[0]);
		$tpl->TOTVALOR	 = tratarValor($totais[1], true);
		
		$tpl->block('EACH_TIPO');
	
	}
	
	
	
	
	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos";
	
	$tpl->show();
	
	function getAtivoText($ativo){
		
		return ($ativo == 1) ? '<font color="green">Ativo</font>' : '<font color="red">Inativo</font>';
		
	}		
	
?>