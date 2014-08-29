<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include "src/classes/Users.class.php";
	include "src/classes/Unidade.class.php";
	
		//$tpl->TESTE = $emp->getServicos()[2]->children();	
	
	
	redirectByPermission(0); // SETAR PERMISSÃO DA PÁGINA
	$inicio = execucao();
	
	$tpl = new Template('html_libs/template_livre.html');
	
	$tpl->addFile('CONTEUDO','html_libs/resumo_dl.html');
	
	$anoAtual	= Date('Y');
	$anoInicio	= 2012;

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
	$uo = new Unidade($_SESSION['bacia']);
	$tpl->MENU_NAME = "Sua Unidade : " . $uo->getSigla();
	$tpl->UDD_NAME	= $uo->getSigla() . " - " . $uo->getNome();

	/*****************************
	 BLOCOS DE ANOS DE RELATÓRIOS
	******************************/
	for($i=$anoAtual; $i >= $iMin; $i-- ){
	
		$mesQtd;
		if($i == $anoAtual && Date('j') < 15)
			$mesQtd = Date('n') - 1;
		elseif($i == $anoAtual && Date('j') >= 15)
			$mesQtd = Date('n');
		else
			$mesQtd = 12;
		
		for($z = 1; $z <= $mesQtd; $z++){
		
			$tpl->MESNOME =  "<b>" . $i . "</b> - " . getMesNome($z,false) ;
			if($i == $iMin && $z == 1)
				$tpl->HREF = "pdf_primeiro.php";
			else
				$tpl->HREF = "pdf_relatorio.php?mes=" . $z . "&ano=" . $i;
			$tpl->block('MES_BLOCK');
		
		}
		
		$tpl->block('ANO_BLOCK');
	
	}
	
	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos";
	
	$tpl->show();		
	
?>