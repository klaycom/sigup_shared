<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include "src/classes/Users.class.php";
	include "src/classes/UnidadeCons.class.php";
	include_once "src/classes/Contratos.class.php";
	
		//$tpl->TESTE = $emp->getServicos()[2]->children();	
	
	
	redirectByPermission(3); // SETAR PERMISSÃO DA PÁGINA
	
	$tpl = new Template('html_libs/template.html');
	
	$tpl->addFile('SECONDMENU','html_libs/ger_secondMenu.html');
	
	
	if(!isset($_GET['form'])){
	
		$tpl->addFile('CONTEUDO','html_libs/ger_addUnidadeIndex.html');
		
		$sql = "SELECT id FROM daee_udds WHERE nome <> '' AND unidade <> ''";
		$query = mysql_query($sql);
		
		while($res = mysql_fetch_array($query)){
		
			$udd = new Unidade($res['id']);
			$tpl->UOID		= $udd->get('id');
			$tpl->UOSIGLA	= $udd->getSigla();
			$tpl->UONOME	= $udd->getNome();
			$tpl->UOEND		= $udd->getEndereco();
			$tpl->UOCIDADE	= $udd->get('cidade');
			$tpl->UOUCQTD	= $udd->getQtdUc();
			$tpl->block('EACH_UO');
		
		}
		
		$sql2	= "SELECT id FROM daee_uddc WHERE ativo = 1";
		$query2	= mysql_query($sql2);
		
		while($res = mysql_fetch_array($query2)){
		
			$tpl->UCID = $res['id'];
			$uc = new UnidadeConsumidora($res['id']);
			$tpl->UCTIPO	= $uc->getTipoNome();
			$tpl->UCNOME	= $uc->getNome();
			$tpl->UCEND		= $uc->getEndereco();
			$tpl->UCCID		= $uc->getCidadeNome();
			$tpl->UCVINC	= $uc->get('uo')->getSigla();
			$tpl->UCRGI		= $uc->get('rgi');
			
			
			$tpl->block('EACH_UC');
		
		}
		
	}else{
		
		if($_GET['form'] == "uo"){// FORMULÁRIO PARA UNIDADES OPERACIONAIS
		
			$tpl->addFile('CONTEUDO','html_libs/ger_addUnidadeO-form.html');

			$sql_cidade	= "SELECT id,nome FROM sys_cidade";
			$query_cid	= mysql_query($sql_cidade);			
			while($res = mysql_fetch_array($query_cid)){
			
				$tpl->CIDID		= $res['id'];
				$tpl->CIDNOME	= $res['nome'];
				$tpl->block("EACH_CIDADE");
			
			}			
			
			$alerta="";
			$valid = 0;
			if (getenv("REQUEST_METHOD") == "POST") {
			
				if(isset($_POST['nome'],$_POST['sigla'],$_POST['endereco'],$_POST['cidade'])){
				
					$nome		= mysql_real_escape_string($_POST['nome']);
					$sigla		= mysql_real_escape_string($_POST['sigla']);
					$endereco	= mysql_real_escape_string($_POST['endereco']);
					$numero		= (int) $_POST['numero'];
					$cidade		= (int) $_POST['cidade'];
					$lat		= $_POST['lat'];
					$long		= $_POST['long'];
					if($nome == "" || $sigla == "" || $endereco == ""){
					
						$alerta.= "Preencha todos os campos obrigatórios marcados com *".$nome.$sigla.$endereco;
						$valid++;
					
					}else if($cidade < 4706 || $cidade > 5350){
					
						$alerta.= "A cidade fornecida não foi encontrada no banco de dados.";
						$valid++;					
					
					}
					
					if($valid == 0){
					
						$udd = new Unidade('',$nome, $endereco, $numero, $sigla, $cidade, $lat, $long);
						if(mysql_insert_id() > 0){
						
							$tpl->AVISO = "Unidade Operacional cadastrada com sucesso!";
						
						}else{
						
							$tpl->ALERTA = mysql_error();
						
						}
					
					}else{
					
						$tpl->ALERTA = $alerta;
					
					}
				
				}
				
			}		
		
		}elseif($_GET['form'] == "uc"){ // FORMULÁRIO PARA UNIDADES CONSUMIDORAS
		
			$tpl->addFile('CONTEUDO','html_libs/ger_addUnidadeC-form.html');
			
			$sql_cidade	= "SELECT id,nome FROM sys_cidade ORDER BY nome";
			$query_cid	= mysql_query($sql_cidade);			
			while($res = mysql_fetch_array($query_cid)){
			
				$tpl->CIDID		= $res['id'];
				$tpl->CIDNOME	= $res['nome'];
				$tpl->block("EACH_CIDADE");
			
			}
			
			for($i = 1; $i <= 31; $i++){
			
				$tpl->DIA = $i;
				$tpl->block("EACH_DATA");
			
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
			
			$sql = "SELECT id FROM daee_contratos WHERE ativo = 1";
			$query = mysql_query($sql);
			
			while($res = mysql_fetch_array($query)){
			
				$tpl->SELECTED1 = ($res['id'] == 13) ? "SELECTED" : ""; //REDE TELEMÉTRICA PADRÃO
				$cont	= new Contrato($res['id']);
				$tpl->CONNOME	= $cont->geraNome();
				$tpl->CONID		= $res['id'];
				$tpl->block('EACH_CON');
			
			}
			
			$emp	= new Empresa();
			foreach($emp->getEmpresas() as $empresa){
			
				$tpl->SELECTED2 = ($empresa['id'] == 8) ? "SELECTED" : "";
				$tpl->EMPID		= $empresa['id'];
				$tpl->EMPNOME	= $empresa['nome'];
				$tpl->CNPJ		= $emp->transformCnpj($empresa['cnpj']);
				$tpl->block('EACH_EMP');
			
			}			
		
			$alerta="";
			$valid = 0;
			if (getenv("REQUEST_METHOD") == "POST") {
		
				if(isset($_POST['rgi'],$_POST['vencto'],$_POST['end'],$_POST['cidade'],$_POST['unidade'],$_POST['contrato'])){
				
					$rgi		= mysql_real_escape_string($_POST['rgi']);
					$tipo		= (int) $_POST['tipo'];
					$vencto		= (int) $_POST['vencto'];
					$contrato	= (int) $_POST['contrato'];
					$end		= mysql_real_escape_string($_POST['end']);
					$num		= (int) $_POST['numero'];
					$compl		= mysql_real_escape_string($_POST['compl']);
					$cidade		= (int) $_POST['cidade'];
					$udd		= (int) $_POST['unidade'];
					$lat		= $_POST['lat'];
					$long		= $_POST['long'];
					$empresa	= (int) $_POST['empresa'];
					
					if($rgi=="" || $vencto=="" || $contrato=="" || $end=="" || $cidade=="" || $udd==""){
					
						$alerta.= "Preencha todos os campos marcados como obrigatórios. <br />";
						$valid++;
					
					}
					
					$ver = new UnidadeConsumidora('1');
					if($ver->verificaExiste($rgi,$contrato)){
					
						$valid++;
						$alerta.= "Já existe uma Unidade Consumidora com o mesmo RGI/Instalação";
					
					}
					
					if($valid < 1){
					
						$uddc = new UnidadeConsumidora('',$rgi,$end,$num,$compl,$contrato,$vencto,$udd,$cidade,$lat,$long,$tipo,$empresa);
						$tpl->AVISO = "Unidade Consumidora criada com sucesso!".mysql_error();
					
					}else{
					
						$tpl->ALERTA = $alerta;
					
					}
				
				}else{
				
					$tpl->ALERTA = "Houve um erro ao receber os dados.";
				
				}
				
			}				
		
		
		}

	
	}

	
	$tpl->show();
	
?>