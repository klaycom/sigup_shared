<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	require "src/classes/Template.class.php";
	include_once "src/classes/Users.class.php";
	include "src/classes/Contratos.class.php";
	
	
	//redirectByPermission(3); // SETAR PERMISSÃO DA PÁGINA
	
	$tpl = new Template('html_libs/template.html');
	

	

	
	$tpl->show();
	
?>