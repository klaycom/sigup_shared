<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include_once "src/classes/Users.class.php";
	include_once "src/classes/Notas.class.php";
	include_once "src/classes/Contratos.class.php";
	include_once "src/classes/Relatorio.class.php";
	
	
	redirectByPermission(1); // SETAR PERMISSÃO DA PÁGINA
	$inicio = execucao();
	
	$tpl = new Template('html_libs/template.html');
	
	$tpl->addFile('SECONDMENU','html_libs/udd_secondMenu.html');
	$tpl->addFile('CONTEUDO','html_libs/rel_history.html');

	$sql = "SELECT * FROM rel_history GROUP BY sigla, data";
	$query = mysql_query($sql);
	while($res = mysql_fetch_array($query)){
	
		$tpl->USER	= $res['usuario'];
		$tpl->SIGLA	= $res['sigla'];
		$data_ref = explode("-", $res['data_ref']);
		$tpl->MESREF= getMesNome($data_ref[1]) . "/" . $data_ref[0];
		$tpl->DLDATE= ExplodeDateTime($res['data']);
		$tpl->block('EACH_HIST');
	
	
	}
	
	
	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos ";		
	$tpl->show();
	
?>