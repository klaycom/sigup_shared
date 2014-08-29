<?php

	/*
	 * Validar Sessão aberta
	 * 
	 */
	
	if (!isset($_SESSION)) session_start();
	
		session_destroy();
		header("Location: login.php?".$index);
		exit;	
	

?>