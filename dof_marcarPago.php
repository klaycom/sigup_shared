<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include_once "src/classes/Users.class.php";
	include_once "src/classes/Notas.class.php";
	include_once "src/classes/Contratos.class.php";
	
	
	redirectByPermission(100); // SETAR PERMISSÃO DA PÁGINA
	$inicio = execucao();
	
	$tpl = new Template('html_libs/template_livre.html');	
	$tpl->addFile("JSCRIPT", 'cssjs_libs/js_parts/js_dof_marcarPago.html');
	//$tpl->addFile("MENU", 'html_libs/ope_secondMenu.html');
	//$tpl->block('EDITAR_MENU');
	
	$nivel = $_SESSION['nivel'];
		
	$tpl->addFile("CONTEUDO", 'html_libs/dof_marcarPago.html');
	$sql_cont	= "SELECT id FROM daee_contratos";
	$query_cont	= mysql_query($sql_cont);
	$qtd = 0;
	
	/*********************
	 MOSTRAR AUTOS EM FORM
	**********************/
	if(mysql_num_rows($query_cont) > 0){
	
		while($res = mysql_fetch_array($query_cont)){
		
			$contrato		= new Contrato($res['id']);
			$tpl->AUTOS_ID 	= $res['id'];
			$tpl->AUTOS_NAME 	= $contrato->geraNome();
			$tpl->block('EACH_AUTOS');
		
		}
	
	}
	
	/*********************
	 MOSTRAR DATAS EM FORM
	**********************/
	for($i=1;$i<=12; $i++){
	
		$tpl->MES_NUM = $i;
		$tpl->MES_NOME = getMesNome($i,false);
		$tpl->block('EACH_MES_FORM');
	
	}
	for($i=2012; $i<= Date('Y');$i++){
	
		$tpl->ANO_NUM = $i;
		$tpl->block("EACH_ANO_FORM");
	
	}
	
	if (getenv("REQUEST_METHOD") == "POST") {
	
		if(isset($_POST['autos'], $_POST['prov'])){
		
			$autos	= (int) $_POST['autos'];
			$prov	= (int) $_POST['prov'];
			$cont = new Contrato($autos);
			$tpl->AUTOS = $cont->geraNome();
			
			if(isset($_POST['mes_ref'],$_POST['ano_ref']) && $_POST['mes_ref'] != "" && $_POST['ano_ref'] != ""){
			
				$mes_post = (int) $_POST['mes_ref'];
				$ano_post = (int) $_POST['ano_ref'];
				$notas = $cont->getAllNotasforDof($prov,$mes_post,$ano_post);
			
			}else{
			
				$notas = $cont->getAllNotasforDof($prov);
			
			}
			
			if(count($notas) > 0){
			
				if($notas[0]['id'] != null){
				
					foreach($notas as $nota){
					
						$tpl->UO		= $nota['unidade'];
						$tpl->UC		= $nota['rgi'] . " - [ <small>" . $nota['compl'] . "</small> ]";
						$tpl->EMPRESA	= $nota['nome'];
						$tpl->DATAREF	= getMesNome($nota['mes_ref']) . "/" . $nota['ano_ref'];
						$tpl->CONSUMO	= tratarValor($nota['consumo']);
						$tpl->VALOR		= tratarValor($nota['valor'],true);
						$tpl->EMISSAO	= setDateDiaMesAno($nota['emissao']);
						$tpl->LANCTO	= ExplodeDateTime($nota['criado'],true);
						$tpl->SUBMIT	= ($nota['pagto']=='0000-00-00') ? '<input type="button" value="PAGO" class="button" name="'. $nota['id'] .'" />' : setDateDiaMesAno($nota['pagto']);
						$tpl->NOTAID	= $nota['id'];
						$tpl->PROV		= $nota['provisoria'];
						
						$tpl->block('EACH_NOTAS');
					
					}

				}else{
				
					$tpl->SQL = $notas[0]['sql'];
				
				}

			}else{
			
				$tpl->block('SEM_NOTAS');
			
			}
			
			$tpl->block('POSTED');
		
		}
	
	}
	
	/*if(mysql_num_rows($query_cont) > 0){
		
		$i = 1;
		while($res = mysql_fetch_array($query_cont)){
		
			$contrato		= new Contrato($res['id']);
			$tpl->AUTOS 	= $i. " - " . $contrato->geraNome();
			$tpl->PERIODO	= getMesNome($mes_ref,false) . "/" . $ano_ref;
			$notas = $contrato->getAllNotasByRef($mes_ref,$ano_ref,false);
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
						$tpl->SUBMIT	= ($nota['pagto']=='0000-00-00') ? '<input type="button" value="PAGO" class="button" name="'. $nota['id'] .'" />' : setDateDiaMesAno($nota['pagto']);
						$tpl->NOTAID	= $nota['id'];
						$tpl->PROV		= $nota['provisoria'];
						
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
	
	}*/	


	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos";	
	$tpl->show();
	
?>