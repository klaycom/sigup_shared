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
	
	$tpl->addFile('CONTEUDO','html_libs/udd_relatorioUO.html');
	$tpl->addFile('JSCRIPT','cssjs_libs/js_parts/js_udd_relatorioUO.html');
	
	$anoAtual = (isset($_GET['ano'])) ? $_GET['ano'] : Date('Y');
	$sqlTipos = "SELECT DISTINCT(tipo) FROM daee_uddc ORDER BY tipo";
	$queryTipos = mysql_query($sqlTipos);
	$tpl->DISTINCT_TYPE = $numtipos = mysql_num_rows($queryTipos);
	$tpl->ANO = $anoAtual;
	
	$x=0;
	$eachTotal;
	$tiposArray;
	while($tipos = mysql_fetch_array($queryTipos)){
	
		$ucTipo = new UnidadeConsumidora(0, null, null, null, null, null, null, null, null, null, null, $tipos['tipo']);
		$tpl->DISTINCT_NOME = $ucTipo->getTipoNome();
		$tpl->block("EACH_DISTINCT");
		$eachTotal[$x] = 0;
		$tiposArray[] = $ucTipo->getTipoNome();
		$x++;
	
	}
	
	$sqlUo = "SELECT id FROM daee_udds WHERE id < 75 ORDER BY id";
	$queryUo = mysql_query($sqlUo);
	$uctotal = 0;
	
	$eachUoByType; // array para mostrar nos gráficos
	$eachTotalPorUnidade; // array com cada total por unidade
	while($uoArray = mysql_fetch_array($queryUo)){
	
		$uo = new Unidade($uoArray['id']);
		$tpl->UOID		= $uoArray['id'];
		$tpl->UOSIGLA	= $uo->getSigla();
		$tpl->UONOME	= $uo->getNome();
		$tpl->UCQTD		= $uo->getQtdUc();
		$tpl->UOKEY		= sha1($uoArray['id']);
		
		$uctotal += $uo->getQtdUc();
		$totalUo 	= 0;
		
		
		for($i=0;$i<$numtipos;$i++){
		
			$valor = $uo->getTotalPorTipo($i,$anoAtual);
			$tpl->TIPOVALOR = ($valor> 0) ? "<a href='udd_uoCharts.php?chave=". sha1($uoArray['id']) ."&svc=$i&start=". $anoAtual ."'>" . tratarValor($valor, true) . "</a>" : tratarValor($valor, true);
			$tpl->BLOCK('EACH_TIPOVALOR');
			$totalUo += $valor;
			$eachTotal[$i] += $valor;
			$eachUoByType[$i][$uo->getSigla()] = $valor;

			
		}
		
		$tpl->UOTOTAL = tratarValor($totalUo, true);
		$eachTotalPorUnidade[$uo->getSigla()] = $totalUo;
		
		$tpl->block("EACH_UO");
		
	
	}
	
	$y;
	$totalPago = 0;
	for($i=0;$i<count($eachTotal);$i++){
	
		$tpl->TIPOTOTAL = tratarValor($eachTotal[$i], true);
		$tpl->BLOCK('EACH_TIPOTOTAL');
		$totalPago += $eachTotal[$i];
		
		/**************************
		 MOSTRAR GRÁFICOS POR TIPO
		**************************/
		$tpl->CHART_INDEX = $i;
		$tpl->CHART_TIPO_NOME = "Gastos de " . $tiposArray[$i] . " por Unidade";
		
		$z = 1;
		foreach($eachUoByType[$i] as $nome => $val){
		
			$tpl->CHART_UO	= $nome;
			$tpl->CHART_VAL	= ($val != null) ? $val : 0;
			$tpl->CHART_COMA= ( $z == count($eachUoByType[$i])) ? "" : ",";
			$tpl->block('EACH_CHART_VAL');
			
			$z++;
		
		}
		
		$tpl->block('CHART_JS');
		$tpl->block('CHART_DIVS');
		$y = $i;
	
	}	
	
	$tpl->TOTALPAGO = tratarValor($totalPago, true);
	
	$tpl->UCTOTAL = $uctotal;
	
	/**************************
		MOSTRAR GRÁFICO GERAL
	**************************/
	$x = 1;
	$tpl->CHART_INDEX = $y + 1;
	$tpl->CHART_TIPO_NOME = "Gráfico Total de Gastos por Unidades";	
	foreach($eachTotalPorUnidade as $nome => $val){
	
			$tpl->CHART_UO	= $nome;
			$tpl->CHART_VAL	= ($val != null) ? $val : 0;
			$tpl->CHART_COMA= ( $x == count($eachTotalPorUnidade)) ? "" : ",";
			$tpl->block('EACH_CHART_VAL');
			
			$x++;	
	
	}
	$tpl->block('CHART_JS');
	$tpl->block('CHART_DIVS');	
	
	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos";	
	$tpl->show();
	
	//var_dump($eachTotalPorUnidade);
	
	
?>