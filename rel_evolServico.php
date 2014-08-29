<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include "src/classes/Users.class.php";
	include "src/classes/UnidadeCons.class.php";
	include "src/classes/Notas.class.php";
	
		//$tpl->TESTE = $emp->getServicos()[2]->children();	
		/*
		SELECT c.rgi, n.id, n.valor FROM daee_uddc c, daee_notas n WHERE c.id = n.uc AND n.mes_ref = 7 AND n.ano_ref = 2013 ORDER BY n.criado 

--SELECT sum(n.valor) FROM daee_uddc c, daee_notas n WHERE c.id = n.uc AND n.mes_ref = 7 AND n.ano_ref = 2013
		*/
	
	
	redirectByPermission(1); // SETAR PERMISSÃO DA PÁGINA
	$inicio = execucao();
	
	$tpl = new Template('html_libs/template.html');
	
	$tpl->addFile('SECONDMENU','html_libs/udd_secondMenu.html');
	$tpl->addFile('JSCRIPT','cssjs_libs/js_parts/js_rel_evolServico.html');
	
	
	$tpl->addFile('CONTEUDO','html_libs/rel_evolServico.html');	
	
	$tiposArray;
	$sqlTipos = "SELECT DISTINCT(tipo) FROM daee_uddc ORDER BY tipo";
	$queryTipos = mysql_query($sqlTipos);
	if(mysql_num_rows($queryTipos) > 0)	{	
	
		while($tipos = mysql_fetch_array($queryTipos)){
		
			$ucTipo = new UnidadeConsumidora(0, null, null, null, null, null, null, null, null, null, null, $tipos['tipo']);
			$tpl->TIPOID = $tipos['tipo'];
			$tpl->TIPONOME = $ucTipo->getTipoNome();
			$tiposArray[$tipos['tipo']] = $ucTipo->getTipoNome();
			
			$tpl->block('EACH_TIPOSERV');
		
		}
	
	}
	

	if(isset($_GET['servico'])){
	
		$tipoServ = $_GET['servico'];
		if(array_key_exists($tipoServ, $tiposArray)){
		
			$tpl->TITULO_EVO = $tiposArray[$tipoServ];
			
			$tpl->block('AREA_CHART');
			
			
			$ano_x = Date('Y') - 1;
			$mesAtual = Date('n');
			
			$tpl->ANO_ANT = "Jan/".$ano_x;
			$tpl->ANO_ATU = getMesNome($mesAtual) . "/" . ($ano_x + 1);
			$tpl->LEGENDS = "'".$tiposArray[$tipoServ]."'";
			
			for($i=1;$i<=(12+$mesAtual);$i++){
			
				if($i > 12)	$controle = 12;
				else $controle = 0;
				
				$mes = $i - $controle;			
				$ano_x = ($i > 12) ? Date('Y') : Date('Y') - 1;
				
				$sql = "SELECT SUM(n.valor), COUNT(n.id) FROM daee_notas n, daee_uddc c WHERE c.id = n.uc AND n.mes_ref = $mes ";
				$sql.= "AND n.ano_ref = $ano_x AND c.tipo =" . $_GET['servico'];
				$query = mysql_query($sql);
				$res = mysql_fetch_array($query);
				
				$tpl->VAL = ($res['SUM(n.valor)'] > 0) ? $res['SUM(n.valor)'] : 0;
				
				$tpl->MESANO	= getMesNome($mes)."/".$ano_x .' ' . $res['COUNT(n.id)'] . " Nota(s)";
				
				if($i != 12+$mesAtual) $tpl->COMA = ",";
				else $tpl->COMA = "";
				
				$tpl->block('EACH_CHART');
			
			}
		
		}else if($_GET['servico'] == "All"){
		
			$tpl->TITULO_EVO = "Agregado de todos os serviços";
			
			$tpl->block('AREA_CHART');
			
			
			$ano_x = Date('Y') - 1;
			$mesAtual = Date('n');
			
			$tpl->ANO_ANT = "Jan/".$ano_x;
			$tpl->ANO_ATU = getMesNome($mesAtual) . "/" . ($ano_x + 1);
			$tpl->LEGENDS = "'Agregado'";
			
			for($i=1;$i<=(12+$mesAtual);$i++){
			
				if($i > 12)	$controle = 12;
				else $controle = 0;
				
				$mes = $i - $controle;			
				$ano_x = ($i > 12) ? Date('Y') : Date('Y') - 1;
				
				$sql = "SELECT SUM(n.valor), COUNT(n.id) FROM daee_notas n, daee_uddc c WHERE c.id = n.uc AND n.mes_ref = $mes ";
				$sql.= "AND n.ano_ref = $ano_x";
				$query = mysql_query($sql);
				$res = mysql_fetch_array($query);
				
				$tpl->VAL = ($res['SUM(n.valor)'] > 0) ? $res['SUM(n.valor)'] : 0;
				
				$tpl->MESANO	= getMesNome($mes)."/".$ano_x;
				
				if($i != 12+$mesAtual) $tpl->COMA = ",";
				else $tpl->COMA = "";
				
				$tpl->block('EACH_CHART');
			
			}			
		
		}else{
		
			header("Location: rel_evolServico.php");
		
		}
	
	}else {
		
		$tpl->TITULO_EVO = "Comparação entre serviços";
		
		$tpl->block('AREA_CHART');
		$ano_x = Date('Y') - 1;
		$mesAtual = Date('n');
		
		$tpl->ANO_ANT = "Jan/".$ano_x;
		$tpl->ANO_ATU = getMesNome($mesAtual) . "/" . ($ano_x + 1);
		
		$tpl->LEGENDS = "";
		for($z = 0;$z < count($tiposArray); $z++){
		
			$tpl->LEGENDS .= "'". $tiposArray[$z] ."'";
			if($z < count($tiposArray) - 1) $tpl->LEGENDS .= ",";
		
		}
		
		for($i=1;$i<=(12+$mesAtual);$i++){
		
			if($i > 12)	$controle = 12;
			else $controle = 0;
			
			$mes = $i - $controle;			
			$ano_x = ($i > 12) ? Date('Y') : Date('Y') - 1;
			
			$tpl->VAL = "";
			for($z = 0;$z < count($tiposArray); $z++){
			
				$sql = "SELECT SUM(n.valor) FROM daee_notas n, daee_uddc c WHERE c.id = n.uc AND n.mes_ref = $mes ";
				$sql.= "AND n.ano_ref = $ano_x AND c.tipo =" . $z;
				$query = mysql_query($sql);
				$res = mysql_fetch_array($query);
				
				$tpl->VAL .= ($res['SUM(n.valor)'] > 0) ? $res['SUM(n.valor)'] : 0;
				if($z < count($tiposArray) - 1) $tpl->VAL .= ",";
			
			}

			
			$tpl->MESANO	= getMesNome($mes)."/".$ano_x;
			
			if($i != 12+$mesAtual) $tpl->COMA = ",";
			else $tpl->COMA = "";
			
			$tpl->block('EACH_CHART');
		
		}
	
	}
	
	
	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos";
	
	$tpl->show();
	
?>