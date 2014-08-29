<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include_once "src/classes/Users.class.php";
	include_once "src/classes/Notas.class.php";
	include_once "src/classes/Contratos.class.php";
	
	
	//redirectByPermission(2); // SETAR PERMISSÃO DA PÁGINA
	$inicio = execucao();
	
	$tpl = new Template('html_libs/template_ope.html');	
	$tpl->addFile("JSCRIPT", 'cssjs_libs/js_parts/js_ope_editarNota.html');
	$tpl->addFile("MENU", 'html_libs/ope_secondMenu.html');
	$tpl->addFile("CONTEUDO", 'html_libs/ope_editarNota.html');
	$tpl->block('EDITAR_MENU');
	
	$nivel = $_SESSION['nivel'];
	

	/***************************************
		    RECEBER DADOS POR $_POST
	***************************************/
	if (getenv("REQUEST_METHOD") == "POST") {
	
		$invalid = 0;
		$alerta	 = "Erro: ";
		if(!isset($_POST['editar'])){
		
			$invalid++;
			$alerta .= "Escolha um campo a ser editado.";
			
		}
		if($_POST['editar'] == "numero" && $_POST['novo-numero'] == ""){
		
			$invalid++;
			$alerta .= "Digite um número de nota válido.";
		
		}
		if($_POST['editar'] == "emissao" && $_POST['novo-emissao'] == ""){
		
			$invalid++;
			$alerta .= "Digite uma data de emissão válida.";
		
		}
		if($_POST['editar'] == "vencto" && $_POST['novo-vencto'] == ""){
		
			$invalid++;
			$alerta .= "Digite uma data de vencimento válida.";
		
		}
		if($_POST['editar'] == "saida" && $_POST['novo-saida'] == ""){
		
			$invalid++;
			$alerta .= "Digite uma data de saída válida.";
		
		}
		if($_POST['editar'] == "provisoria" && $_POST['novo-provisoria'] <= 0){
		
			$invalid++;
			$alerta .= "Digite um número de provisória válido.";
		
		}
		if($_POST['editar'] == "consumo" && $_POST['novo-consumo'] <= 0){
		
			$invalid++;
			$alerta .= "Digite um consumo válido.";
		
		}
		if($_POST['editar'] == "valor" && mysqlNumber($_POST['valor']) <= 0){
		
			$invalid++;
			$alerta .= "Digite um número de nota válido.";
		
		}
		if($_POST['obs'] == ""){
		
			$invalid++;
			$alerta .= "Digite uma justificativa.";
		
		}

		/**********************************************
		VERIFICAR ERROS E IMPRIMIR OU EDITAR E TERMINAR
		**********************************************/		
		if($invalid > 0){
		
			$tpl->ALERTA = $alerta;
		
		}else{
		
			$valor;
			switch($_POST['editar']){
			
				case "emissao":
				case "saida":
				case "vencto":
				
					$valor = getTransformDate($_POST['novo-' . $_POST['editar']]);
				
				break;
				default:
				
					$valor = mysql_real_escape_string($_POST['novo-' . $_POST['editar']]);
				
				break;
			
			}
			$obs = mysql_real_escape_string($_POST['obs']);
			$usertime = $_SESSION['usuario'] . " =@=" . Date('Y-m-d H:i:s') . "=@=";
			
			$nota = new Nota($_GET['chave']);
			$editar = $nota->editarNota($_POST['editar'], $valor, $usertime, $obs);
			if($editar['tipo'] == "alerta"){
			
				$tpl->ALERTA = $editar['msg'];
			
			}elseif($editar['tipo'] == "aviso"){
			
				$tpl->AVISO = $editar['msg'];
			
			}
		
		}
		
	
	}
	
	/***************************************
	MOSTRAR OU UMA NOTA OU TODAS NO PERIODO
	***************************************/
	$mes_ref = (isset($_GET['mes']) && $_GET['mes'] >= 1 && $_GET['mes'] <= 12) ? $_GET['mes'] : Date('n');
	$ano_ref = (isset($_GET['ano']) && $_GET['ano'] >= 2012 && $_GET['ano'] <= Date('Y')) ? $_GET['ano'] : Date('Y');
	if(isset($_GET['chave']) && $_GET['chave'] > 0){
	
		$notaid = (int) $_GET['chave'];
		$nota = new Nota($notaid);
		$permissao = $nota->get('contrato')->get('permissao');
		
		if($_SESSION['nivel'] == 1 || $_SESSION['nivel'] == $permissao){
		
			$tpl->NOTANUM	= $nota->get('numero');
			$tpl->UO		= $nota->get('uc')->get('uo')->getSigla();
			$tpl->UC		= $nota->get('uc')->get('rgi') . " - [ " . $nota->get('uc')->getNome() . " ]";
			$tpl->DATA_REF	= $nota->get('data_ref');
			$tpl->EMPRESA	= $nota->get('uc')->get('empresa')->getNome();
			$tpl->EMISSAO	= setDateDiaMesAno($nota->get('emissao'));
			$tpl->VENCTO	= setDateDiaMesAno($nota->get('vencto'));
			$tpl->SAIDA		= setDateDiaMesAno($nota->get('saida'));
			$tpl->PROV		= $nota->get('provisoria');
			$tpl->CONSUMO	= tratarValor($nota->get('consumo'));
			$tpl->VALOR		= tratarValor($nota->get('valor'), true);
			$tpl->USUARIO	= $nota->get('usuario')->get('login');
			$tpl->OBS		= $nota->getObs();
			$tpl->NID		= $notaid;
		
			$tpl->block('FORM_BODY');
			
		} // SE TIVER NA PERMISSÃO CORRETA
	
	}else{
	
		/************************** 
		 MOSTRAR MESES PARA ESCOLHA
		**************************/	
		for($i=1;$i<=12;$i++){
		
			$tpl->MESLISTNUM = $i;
			$tpl->MESLIST	 = getMesNome($i). "/" . $ano_ref;
			$tpl->block('EACH_MESLIST');
		
		}
	
		$sql_cont	= ($_SESSION['nivel'] != 1) ? "SELECT id FROM daee_contratos WHERE permissao=$nivel" : "SELECT id FROM daee_contratos";
		$query_cont	= mysql_query($sql_cont);
		$qtd = 0;
	
		if(mysql_num_rows($query_cont) > 0){
			
			$i = 1;
			while($res = mysql_fetch_array($query_cont)){
			
				$contrato		= new Contrato($res['id']);
				$tpl->AUTOS 	= $i. " - " . $contrato->geraNome();
				$tpl->PERIODO	= getMesNome($mes_ref,false) . "/" . $ano_ref;
				$notas = $contrato->getAllNotasByRef($mes_ref, $ano_ref, false);
				if(count($notas) > 0){
				
					if($notas[0]['id'] != null){
					
						foreach($notas as $nota){
						
							$tpl->NOTANUM	= $nota['numero'];
							$tpl->UO		= $nota['unidade'];
							$tpl->UC		= $nota['rgi'] . " - [ <small>" . $nota['compl'] . "</small> ]";
							$tpl->EMPRESA	= $nota['nome'];
							$tpl->DATAREF	= getMesNome($nota['mes_ref']) . "/" . $nota['ano_ref'];
							$tpl->CONSUMO	= tratarValor($nota['consumo']);
							$tpl->VALOR		= tratarValor($nota['valor'],true);
							$tpl->EMISSAO	= setDateDiaMesAno($nota['emissao']);
							$tpl->LANCTO	= ExplodeDateTime($nota['criado'],true);
							$tpl->SUBMIT	= '<input type="button" value="EDITAR" class="button" name="ope_editarNota.php?chave='. $nota['id'] .'" />';
							$tpl->NOTAID	= $nota['id'];
							
							$tpl->block('EACH_NOTAS');
						
						}
					
					}else{
					
						$tpl->block('SEM_NOTAS');
					
					}
				
				}
				//$tpl->SQL = $notas;
				
				$tpl->block("EACH_CONTRATO");
				$i++;
			
			}
			
			$tpl->block("TABLE_BODY");
		}	

	}

	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos";	
	$tpl->show();
	
?>