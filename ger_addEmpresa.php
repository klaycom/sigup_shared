<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	require "src/classes/Template.class.php";
	include_once "src/classes/Users.class.php";
	include "src/classes/Contratos.class.php";
	
	
	redirectByPermission(1); // SETAR PERMISSÃO DA PÁGINA
	
	$tpl = new Template('html_libs/template.html');
	
	$tpl->addFile('SECONDMENU','html_libs/ger_secondMenu.html');
	
	

	if(!isset($_GET['form'])){
	
		$tpl->addFile('CONTEUDO','html_libs/ger_addEmpresa.html');
		
		$emp = new Empresa();
		foreach($emp->getEmpresas() as $empresa){
		
			$tpl->EMPID		= $empresa['id'];
			$tpl->EMPNOME	= $empresa['nome'];
			$tpl->EMPCNPJ	= $emp->transformCnpj($empresa['cnpj']);
			$tpl->EMPDESC	= $empresa['servico'];
			$tpl->EMPREPRE	= $empresa['representante'];
			$tpl->EMPCONTACT= $empresa['contato'];
			
			$tpl->block('EACH_EMPRESA');
		
		}
	
	}else{
	
		$tpl->addFile('CONTEUDO','html_libs/ger_addEmpresa-form.html');
		if (getenv("REQUEST_METHOD") == "POST") {
		
			$emp = new Empresa();
			if(isset($_POST['nome'],$_POST['cnpj'],$_POST['desc'])){
			
				$nome		= $_POST['nome'];
				$cnpj		= $_POST['cnpj'];
				$desc		= $_POST['desc'];
				$repre		= $_POST['represent'];
				$contato	= $_POST['contato'];
				$empresa	= $emp->addEmpresa($nome,$cnpj,$desc,$repre,$contato);
				switch($empresa){
					case -2:
						$tpl->ALERTA = "Digite um nome para a emprea!";
					break;
					case -1:
						$tpl->ALERTA = "O CNPJ fornecido não é válido!";
					break;
					case 0:
						$tpl->ALERTA = "Houve um erro ao inserir no banco de dados. Tente novamente";
					break;
					default:
						$tpl->AVISO  = "Empresa cadastrada com sucesso!";
					break;
					
				}
			
			}
		
		}
	
	}
	$tpl->show();
	
?>