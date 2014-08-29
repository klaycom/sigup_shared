<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include "src/classes/Users.class.php";
	include "src/classes/Contratos.class.php";
	
		//$tpl->TESTE = $emp->getServicos()[2]->children();	
	
	
	redirectByPermission(3); // SETAR PERMISSÃO DA PÁGINA
	
	$tpl = new Template('html_libs/template.html');
	
	$tpl->addFile('SECONDMENU','html_libs/ger_secondMenu.html');
	
	
	if(!isset($_GET['form'])){
	
		$tpl->addFile('CONTEUDO','html_libs/ger_addContrato.html');
		
		$sql = "SELECT id FROM daee_contratos WHERE ativo = 1";
		$query = mysql_query($sql);
		
		while($res = mysql_fetch_array($query)){
		
			$tpl->CONTID	= $res['id'];
			$cont	= new Contrato($res['id']);
			$autos	= $cont->isAutos() ? "Autos " : "Processo ";
			$num	= $cont->isAutos() ? number_format($cont->get('numero'),0,',','.') : $cont->get('numero');
			$nome	= $cont->get('nome');
			$servico= $cont->get('empresa')->getServicos()[$cont->get('servico')]->children();
			//$tpl->CONTNOME	= $autos." n°".$num." - ".$nome."::".$servico;
			$tpl->CONTNOME = $cont->geraNome();
			$tpl->CONTCONT	= $cont->get('contrato');
			$tpl->CONTEMP	= $cont->get('empresa')->getNome();
			$tpl->CONTLICIT	= ($cont->get('comlicit') == 1 ) ? "Sim" : "Não";
			$de = $cont->get('data');
			$ate= $cont->get('vigencia');
			$tpl->CONTVIG	= ($de == "0000-00-00") ? "--" : $de." a ".$ate;
			$tpl->CONTEM	= ExplodeDateTime($cont->get('criado'));
			$tpl->CONTUDD	= $cont->get('unidade')->getSigla();
			
			$tpl->block('EACH_CONTRATO');
		
		}
		
	}else{
	
		$tpl->addFile('CONTEUDO','html_libs/ger_addContrato-form.html');
		
		$emp	= new Empresa();
		
		$alerta="";
		$valid = 0;
		if (getenv("REQUEST_METHOD") == "POST") {
		
			if(isset($_POST['tipo'],$_POST['numero'],$_POST['titulo'],$_POST['licit-bool'],$_POST['empresa'])){
			
				$tipo	= (int) $_POST['tipo'];
				$numero	= mysql_real_escape_string($_POST['numero']);
				$titulo	= mysql_real_escape_string($_POST['titulo']);
				$licit	= (int) $_POST['licit-bool'];
				$empresa= (int) $_POST['empresa'];
				$newServ= mysql_real_escape_string($_POST['nome-serv']);
				$newDesc= mysql_real_escape_string($_POST['desc-serv']);
				$contrat= mysql_real_escape_string($_POST['contrato']);
				$uddid	= (int) $_POST['udd'];
				$perm	= (int) $_POST['permissao'];
				
				if(is_string($tipo) || $numero=="" || $titulo=="" || is_string($empresa) || is_string($licit)){
				
					$alerta.= "Preencha todos os campos obrigatórios do formulário!<br />";
					$valid += 1;
				
				}
				
				if(isset($_POST['radioserv']) && $_POST['radioserv'] == 0){
				
					$servico = $_POST['servico-list'];
				
				}elseif($_POST['radioserv'] == 1 && $newServ!="" && $newDesc!=""){
				
					$servico = $i+1;
				
				}else{
				
					$alerta.= "Digite os campos do novo serviço a ser inserido! <br />";
					$valid	+= 1;
					
				}
				
				if($contrat == "" || strtoupper($contrat) == "SEM CONTRATO"){
				
					$contrato = "SEM CONTRATO";
				
				}else {
				
					$contrato = $contrat;
				
				}
				
				if($_POST['vig-de'] != "" && $_POST['vig-ate']!= ""){
				
					$de = explode($_POST['vig-de'],"/");
					$ate= explode($_POST['vig-ate'],"/");
					if(strlen($_POST['vig-de']) == 5 && strlen($_POST['vig-ate']) == 5 && sizeof($de) == 2 && sizeof($ate) == 2){
					
						$deCompl = $_POST['de-ano']."-".$de[1]."-".$de[0];
						$ateCompl = $_POST['ate-ano']."-".$ate[1]."-".$ate[0];
						
					}elseif(strlen($_POST['vig-de']) > 0 && strlen($_POST['vig-ate']) > 0){
					
						$alerta.= "Digite as datas nos formatos corretos!<br />";
						$valid += 1;
					
					}
				
				}else{
					
					$deCompl = "";
					$ateCompl= "";
					
				}
				
				if($valid < 1){
				
					$user = new User($_SESSION['usuario']);
					$newContrato = new Contrato('', $tipo,$numero,'',$titulo,$empresa,$servico,$uddid,$contrato,$licit,$deCompl,$ateCompl,$user->get('id'),$perm);
					if($_POST['radioserv'] == 1 ){
					
						$emp->addServico($newServ,$newDesc);
						$tpl->AVISO = "Serviço adicionado com sucesso!<br />";
					
					}
					$tpl->AVISO = "Contrato adicionado com sucesso! ".mysql_error();
				
				}else{
				
					$tpl->ALERTA = $alerta;
				
				}
			
			}else{
			
				$tpl->ALERTA = "Preencha o formulário corretamente";
			
			}
		
		}
		
		foreach($emp->getEmpresas() as $empresa){
		
			$tpl->EMPID		= $empresa['id'];
			$tpl->EMPNOME	= $empresa['nome'];
			$tpl->CNPJ		= $emp->transformCnpj($empresa['cnpj']);
			$tpl->block('EACH_EMP');
		
		}
		
		$i = 0;
		foreach($emp->getServicos() as $servi){
		
			$tpl->SERVVAL	= $i;
			$tpl->SERVNOME	= $servi->children();
			$tpl->SERVDESC	= $servi->children()[1];
			$tpl->block('EACH_SERV');
			$i++;
		
		}
		
		$ano = date('Y');
		for($i = $ano-7; $i <= $ano+7;$i++){
		
			$tpl->ANOVAL = $i;
			$tpl->block("EACH_ANO1");
			$tpl->block("EACH_ANO2");
		
		}
		
		$sqlUdd = "SELECT id FROM daee_udds ORDER BY id";
		$queryUdd =  mysql_query($sqlUdd);
		while($res = mysql_fetch_array($queryUdd)){
		
			$unid = new Unidade($res['id']);
			$tpl->UDDSIGLA	= $unid->getSigla();
			$tpl->UDDNOME	= $unid->getNome();
			$tpl->UDDID		= $unid->get('id');
			$tpl->block('EACH_UDD');
		
		}
	
	}

	
	$tpl->show();
	
?>