<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include "src/classes/Users.class.php";
	include "src/classes/UnidadeCons.class.php";
	
		//$tpl->TESTE = $emp->getServicos()[2]->children();	
	
	
	redirectByPermission(1); // SETAR PERMISSÃO DA PÁGINA
	$inicio = execucao();
	
	$tpl = new Template('html_libs/template.html');
	
	$tpl->addFile('SECONDMENU','html_libs/udd_secondMenu.html');
	
	$tpl->addFile('CONTEUDO','html_libs/udd_relatorioUC.html');

	if(isset($_GET['tipo']) && $_GET['tipo'] > 0 && $_GET['tipo'] <=10)
		$tipoId = $_GET['tipo'];
	else
		$tipoId = 0;
	
	$sqlTipos = "SELECT DISTINCT(tipo) FROM daee_uddc";
	$queryTipos = mysql_query($sqlTipos);
	
	$consTotal;
	$pagoTotal;
	$consAnoTotal;
	$pagoAnoTotal;
	$x = 0;
	while($tipos = mysql_fetch_array($queryTipos)){
	
		$consTotal[$x] = 0;
		$pagoTotal[$x] = 0;
		$consAnoTotal[$x] = 0;
		$pagoAnoTotal[$x] = 0;

		$ucTipo = new UnidadeConsumidora(0, null, null, null, null, null, null, null, null, null, null, $tipos['tipo']);
		$tpl->TIPO_NOME = $ucTipo->getTipoNome();
		$tpl->ANO		= date('Y');
		$tpl->MEDIDA	= $ucTipo->getTipoMedida();
		$tpl->TIPO_ID	= $tipos['tipo'];
		$tpl->block("EACH_TIPOBUTTON");
		
		if($tipos['tipo'] == $tipoId){
			
			//FAZER APARECER UCS E SEUS RESULTADOS
			$ucqtd = 0;
			foreach($ucTipo->getMesmoTipo() as $res){
				
				//$tpl->UCID		= $res;
				$uc = new UnidadeConsumidora($res);
				
				$tpl->UCRGI		= $uc->get('rgi');
				$tpl->UCCHAVE	= sha1($uc->get('id'));
				$tpl->UCNOME	= $uc->getNome();
				$tpl->UCEND		= $uc->getEndereco();
				$tpl->UCCID		= $uc->getCidadeNome();
				$tpl->UCSIGLA	= $uc->get('uo')->getSigla();
				$tpl->EMPRESA	= $uc->get('empresa')->getNome();
				$tpl->STATUS	= $uc->getAtivoText();
				
				$totalAno		= $uc->somaValorNotas(Date('Y'));
				$tpl->CONSANO	= tratarValor($totalAno['SUM(consumo)'], false);
				$tpl->VALANO	= tratarValor($totalAno['SUM(valor)'], true);			
				$consAnoTotal[$x] += $totalAno['SUM(consumo)'];
				$pagoAnoTotal[$x] += $totalAno['SUM(valor)'];
				
				$ucqtd++;
				$tpl->block("EACH_UC");
			
			}
			
			$tpl->UCQTD = $ucqtd;
			//$tpl->TIPO_NOME .= " " . $ucqtd;
			$tpl->SOMACONSANO	= tratarValor($consAnoTotal[$x]);
			$tpl->SOMAPAGOANO	= tratarValor($pagoAnoTotal[$x], true);
			
			$x++;
			$tpl->block("EACH_TIPO");
			
		}
		
		
	
	}
	
	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos";		
	$tpl->show();
	
?>