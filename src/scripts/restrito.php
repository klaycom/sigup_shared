<?php

	/*
	 * Validar Sessão aberta
	 * 
	 */
	
	if (!isset($_SESSION)) session_start();
	
	if(!isset($_SESSION['usuario'],$_SESSION['nivel'],$_SESSION['lastAccess'])){
	
		gotoLogin('x');
	
	} else{
	
		$dataSalva	= $_SESSION['lastAccess'];
		$agora 		= date("Y-n-j H:i:s");
		$tempoDecor	= (strtotime($agora)-strtotime($dataSalva));
		if($tempoDecor >= 3600){
		
			gotoLogin('z');
		
		}else{
		
			$_SESSION['lastAccess'] = $agora;
		
		}
	
	}
	
	function gotoLogin($index=null){
	
		session_destroy();
		header("Location: login.php?".$index);
		exit;
	
	}
	
	function redirectByPermission($necess){
	
		if($_SESSION['nivel'] != 1 ){
				
			if($necess == 2){
				
				if($_SESSION['nivel'] < 1 || $_SESSION['nivel'] >= 6)
					gotoLogin('y');
				
			}elseif($_SESSION['nivel'] != $necess)
				gotoLogin('y');
		
		}
	
	}
?>