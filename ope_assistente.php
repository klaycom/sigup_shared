<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include_once "src/classes/Users.class.php";
	include_once "src/classes/Notas.class.php";
	include_once "src/classes/UnidadeCons.class.php";
	
	
	redirectByPermission(2); // SETAR PERMISSÃO DA PÁGINA
	
	$tpl = new Template('html_libs/template_ope.html');
	
	$tpl->addFile("MENU", 'html_libs/ope_secondMenu.html');
	
	$tpl->addFile("JSCRIPT", 'cssjs_libs/js_parts/js_ope_assistente.html');
	$tpl->addFile("CONTEUDO", 'html_libs/ope_assistente-form.html');
	$tpl->block('LIMPAR_BLOCK');
	
	$nivel	= $_SESSION['nivel'];
	$user	= new User($_SESSION['usuario']);
	
	if(isset($_GET['limpar'])){
	
		unset($_SESSION['emissao']);
		unset($_SESSION['vencto']);
		unset($_SESSION['saida']);
		unset($_SESSION['provisoria']);
		unset($_SESSION['data_ref']);
	
	}
	
	/*****************************************************
	       RECEBER DADOS DO FORMULÁRIO E CADASTRAR
	*****************************************************/	
	if (getenv("REQUEST_METHOD") == "POST") {

		/***************************************
		      COLOCAR VALORES EM SESSION
		***************************************/	
		
		$mes_ref;
		$ano_ref;
		if(isset($_POST['mantem-data_ref'])){
		
			$_SESSION['data_ref'] = $_POST['hide-mes_ref'];
			$g = explode("-",$_POST['hide-mes_ref']);
			$mes_ref = $g[0];
			$ano_ref = $g[1];
			
		}else{
		
			$mes_ref = $_POST['mes_ref'];
			$ano_ref = $_POST['ano_ref'];
			unset($_SESSION['data_ref']);
		
		}
		
		$emissao = $_POST['emissao'];
		if(isset($_POST['mantem-emissao'])){
		
			$_SESSION['emissao'] = $_POST['emissao'];
		
		}else{
		
			unset($_SESSION['emissao']);
		
		}
		
		$vencto = $_POST['vencto'];
		if(isset($_POST['mantem-vencto'])){
		
			$_SESSION['vencto'] = $_POST['vencto'];	
			
		}else{
		
			unset($_SESSION['vencto']);
		
		}
		
		$saida = $_POST['saida'];
		if(isset($_POST['mantem-saida'])){
		
			$_SESSION['saida'] = $_POST['saida'];
		
		}else{
		
			unset($_SESSION['saida']);
		
		}
		$prov = $_POST['prov'];
		if(isset($_POST['mantem-prov'])){
		
			$_SESSION['provisoria'] = $_POST['prov'];
		
		}else{
		
			unset($_SESSION['provisoria']);
		
		}
		
		$rgi = str_replace('["',"",$_POST['rgi']);
		$rgi = str_replace('"]',"",$rgi);

		/***************************************
		        RECEBER VALORES E VALIDAR
		***************************************/	
			
		$aviso 	= "";
		$alerta	= "";
		$invalidos = 0;
		if(isset($_POST['nota'],$_POST['emissao'],$_POST['vencto'],$_POST['valor']) && $rgi != "" && $mes_ref != "" && $ano_ref != ""){
		
			if($rgi <= 0){
			
				$invalidos++;
				$alerta.= $invalidos . " - Campo Registro de UC deve ser preenchido obrigatoriamente.<br />" . $rgi;
			
			}
			$nota = $_POST['nota'];
			if(strlen($_POST['nota']) <= 4 || strlen($_POST['nota']) >= 15 ){
			
				$invalidos++;
				$alerta.= $invalidos . " - Digite um número de nota com no mínimo 4 e no máximo 15 caracteres.<br />";
			
			}
			if(!isValidMesAno($mes_ref, $ano_ref)){
			
				$invalidos++;
				$alerta.= $invalidos . " - Mes e Ano de referência inválidos.<br />";			
			
			}
			$emissao = getTransformDate($_POST['emissao']);
			$emi = explode("-",$emissao);
			if(!isValidDiaMesAno($emi[2],$emi[1],$emi[0])){
			
				$invalidos++;
				$alerta.= $invalidos . " - Data de emissão inválida.<br />";						
			
			}
			$vencto = getTransformDate($_POST['vencto']);
			$ven = explode("-",$vencto);
			if(!isValidDiaMesAno($ven[2],$ven[1],$ven[0])){
			
				$invalidos++;
				$alerta.= $invalidos . " - Data de vencimento inválida.<br />";						
			
			}			
			
			$saida = "";
			if($_POST['saida'] != ""){
			
				$saida = getTransformDate($_POST['saida']);
				$sai = explode("-",$saida);
				if(!isValidDiaMesAno($sai[2],$sai[1],$sai[0])){
				
					$invalidos++;
					$alerta.= $invalidos . " - Data de saída inválida.<br />";						
				
				}
			
			}
			
			$consumo = (int) $_POST['consumo'];
			if($_POST['consumo'] != "" && $consumo < 0){
			
				$invalidos++;
				$alerta.= $invalidos . " - Consumo digitado inválido.<br />";			
			
			}
			
			$valor = mysqlNumber($_POST['valor']);
			if($valor < 0 ){
			
				$invalidos++;
				$alerta.= $invalidos . " - Valor de nota digitado inválido.<br />";				
			
			}
			
			$prov = (int) $_POST['prov'];
			if($_POST['prov'] != "" && $prov <= 0){
			
				$invalidos++;
				$alerta.= $invalidos . " - Número de provisória digitado inválido.<br />";	
			
			}
					
			if($invalidos > 0){
			
				$tpl->ALERTA = "Número de Alertas: ". $invalidos . "<br />". $alerta;
				$uc = new UnidadeConsumidora($rgi);
				$tpl->RGID 		= $id; 
				$tpl->RGI		= $uc->get('rgi');
				$tpl->RGINOME	= $uc->getEndereco() . " ~> " .$uc->getNome();
				//$tpl->DISABLE_RGI = 'disabled="disabled"';				
				$tpl->block('EACH_RGI');

				$tpl->NOTAVAL	= $nota;
				$tpl->COMPLVAL	= $_POST['compl'];
				$tpl->CONSVAL	= $consumo;
				$tpl->VALORVAL	= $_POST['valor'];
				$tpl->OBS		= $_POST['obs'];
				
				$tpl->AVISO = $valor;
				
			}else{

				$compl		= mysql_real_escape_string($_POST['compl']);;
				$uc			= new UnidadeConsumidora($rgi);
				$contrato	= $uc->get('contrato')->get('id');
				$obs		= nl2br(mysql_real_escape_string($_POST['obs']));
				$nota		= new Nota("",0,$nota,$compl,0,$emissao,$mes_ref,$ano_ref,$consumo,$valor,$contrato,$user->get('id'),$uc->get('id'),$saida,'',$prov,$obs,$vencto);
				
				if($nota->saveNew()){
				
					$tpl->AVISO = "Nota lançada com sucesso!";
				
				} else {
				
					$tpl->ALERTA = "Houve um erro: " . mysql_error();
				
				}
			
			}

		
		}else{
		
			$tpl->ALERTA = "Número de Alertas: ". 1 ."<br />Algum campo obrigatório não foi preenchido. ". $_POST['rgi'] .$_POST['nota'] .$_POST['mes_ref'] .$_POST['ano_ref']. $_POST['emissao'] .$_POST['vencto'] .$_POST['valor'] . "--";
		
		}
	
	}
	
	/***************************************************
	  MOSTRAR TODOS OS RGI OU DESABILITAR COM APENAS UM
	***************************************************/
	$sql_verif;
	
	if((isset($_GET['dataref']) && count(explode('-',$_GET['dataref'])) == 2) || isset($_SESSION['data_ref'])){
		
		$data_ref	= (isset($_GET['dataref'])) ? explode('-',$_GET['dataref']) : explode('-',$_SESSION['data_ref']);
		$mes 		= ($data_ref[0] > 0 && $data_ref[0] < 13) ? (int) $data_ref[0] : 1;
		$ano		= ($data_ref[1] > 2000) ? (int) $data_ref[1] : date('Y');
		$conts		= $user->getContratos();
		
		if($conts[0]['id'] != null){
		
			$qtdCont	= count($conts);
			$z			= 1;
			foreach($conts as $cont){
			
				$contrato = new Contrato($cont['id']);
				//$tpl->CONTNOME		= "-------- " .$contrato->geraNome(). " --------";
				
				$rgis = $contrato->getAllUcs(true);
				if($rgis[0]['id'] != null){
				
					$qtdRgi	= count($rgis);
					$w		= 1;
					foreach($rgis as $rgi){
					
						$uc = new UnidadeConsumidora($rgi['id']);
						$tpl->RGID 			= $rgi['id'];
						$tpl->DISABLE_OPT	= "";
						$tpl->RGI		= $uc->get('rgi');
						$tpl->RGINOME	= $uc->getEndereco() . " ~> " .$uc->getNome();
						$tpl->block('EACH_RGI');

						if($z < $qtdCont) $tpl->MS_COMA = ",";
						elseif($z == $qtdCont){
						
							$tpl->MS_COMA = ($w < $qtdRgi) ? "," : "";
						
						}
						$w++;
						$tpl->block('MAGIC_SUGGEST');
					
					}
				
				}
				
				$z++;
				//$tpl->block('EACH_OPTGROUP');
			
			}
			
		}
		
		$tpl->DISABLE_DATAREF = 'disabled="disabled"';
		$tpl->MES = $mes;
		$tpl->MESNOME = getMesNome($mes,false);
		$tpl->block('EACH_MESREF');
		$tpl->ANO = $ano;
		$tpl->block('EACH_ANOREF');
		$tpl->HIDEMESREF 	= $mes."-".$ano;
		$tpl->CHECKED_DATA 	= 'checked="checked"';
		
	}else{
	
		$conts	= $user->getContratos();
		if($conts[0]['id'] != null){

			$qtdCont	= count($conts);
			$z			= 1;		
			foreach($conts as $cont){
			
				$contrato = new Contrato($cont['id']);
				//$tpl->CONTNOME		= "-------- ".$contrato->geraNome(). " --------";
				
				$rgis = $contrato->getAllUcs(true);
				if($rgis[0]['id'] != null){

					$qtdRgi	= count($rgis);
					$w		= 1;				
					foreach($rgis as $rgi){
					
						$uc = new UnidadeConsumidora($rgi['id']);
						$tpl->RGID 			= $rgi['id'];
						$tpl->DISABLE_OPT	= "";
						$tpl->RGI		= $uc->get('rgi');
						$tpl->RGINOME	= $uc->getEndereco() . " ~> " .$uc->getNome();
						$tpl->block('EACH_RGI');

						if($z < $qtdCont) $tpl->MS_COMA = ",";
						elseif($z == $qtdCont){
						
							$tpl->MS_COMA = ($w < $qtdRgi) ? "," : "";
						
						}
						$w++;
						$tpl->block('MAGIC_SUGGEST');						
					
					}
				
				}
				
				//$tpl->block('EACH_OPTGROUP');
			
			}	
			
		}	
	
	}
		
	/*****************************************************
	MOSTRAR VALORES CASO ESTEJAM ARMAZENADAS EM $_SESSION
	*****************************************************/
	
	if(isset($_SESSION['emissao'])){
	
		$tpl->EMISSAO = $_SESSION['emissao'];
		$tpl->EMISCHK = 'checked="checked"';
		$tpl->DISABLE_EMIS = 'readonly="readonly"';
	
	}
	
	if(isset($_SESSION['vencto'])){
	
		$tpl->VENCTO = $_SESSION['vencto'];
		$tpl->VENCCHK= 'checked="checked"';
		$tpl->DISABLE_VENC = 'readonly="readonly"';
	
	}
	
	if(isset($_SESSION['saida'])){
	
		$tpl->SAIDA = $_SESSION['saida'];
		$tpl->SAIDACHK = 'checked="checked"';		
		$tpl->DISABLE_SAID = 'readonly="readonly"';
	
	}
	
	if(isset($_SESSION['provisoria'])){
	
		$tpl->PROVISORIA = $_SESSION['provisoria'];
		$tpl->PROVCHK = 'checked="checked"';		
		$tpl->DISABLE_PROV = 'readonly="readonly"';
	
	}	

	/***************************************************
	 MOSTRAR MESES E ANOS NO FORMULÁRIO DE LANÇAMENTO
	***************************************************/
	for($i= Date('Y'); $i>= 2010;$i--){
		
		$tpl->ANO = $i;
		$tpl->block('EACH_ANOREF');
	
	}
	
	for($i=1; $i<=12; $i++){
		
		$tpl->MES = $i;
		$tpl->MESNOME = getMesNome($i,false);
		$tpl->block('EACH_MESREF');
	
	}
	
	$tpl->show();
	
?>