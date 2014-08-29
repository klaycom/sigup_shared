<?php
	require "src/classes/Template.class.php";
	require "src/scripts/conecta.php";
	include_once "src/classes/Users.class.php";
	//require "src/scripts/restrito.php";
	
	$tpl = new Template('html_libs/template_livre.html');
	
	
	$tpl->addFile("CONTEUDO","html_libs/sys_login.html");
	
	/* 
	 * Recebendo dados por S_POST
	 * para fazer login
	*/
	
	if (getenv("REQUEST_METHOD") == "POST") {
	
		if(isset($_POST['user'], $_POST['pass'])){
		
			$user = $_POST['user'];
			$pass = $_POST['pass'];
			if(strlen($user) >= 6 && strlen($pass) >=4){
			
				$user = new User($user,$pass);
				if($user->isValidUser()){
				
					$user->startSession();
					$user->gotoRightPage();
					//$tpl->ALERTA = "Você está logado como ".$_SESSION['usuario'];
				
				}else{
				
					$tpl->ALERTA = "Os dados fornecidos estão incorretos!";
				
				}
			
			}else{
			
				$tpl->ALERTA = "O usuario deve ter no mínimo 6 caracteres e a senha 4.";
			
			}
		
		}
	
	}
	
	/*
	 * Mostrar Página Restrita
	 *
	 */
	
	if(isset($_GET['x'])){
	
		$tpl->ALERTA = "Página Restrita: Faça o login";
	
	}elseif(isset($_GET['y'])){
	
		$tpl->ALERTA = "Permissão incorreta: Faça o login novamente";
	
	}elseif(isset($_GET['z'])){
	
		$tpl->ALERTA = "Sessão expirada por inatividade: Faça o login";
	
	}


	
	$tpl->show();
	
?>