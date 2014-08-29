<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include_once "src/classes/Users.class.php";
	include_once "src/classes/Notas.class.php";
	include_once "src/classes/Contratos.class.php";
	
	
	redirectByPermission(2); // SETAR PERMISSÃO DA PÁGINA
	$inicio = execucao();
	
	$tpl = new Template('html_libs/template_ope.html');	
	$tpl->addFile("MENU", 'html_libs/ope_secondMenu.html');
	$tpl->block('EDITAR_MENU');
	
	$nivel = $_SESSION['nivel'];
	$periodo = (isset($_GET['periodo'])) ? $_GET['periodo'] : 2;
	
	if(isset($_GET['chave']) && strlen($_GET['chave']) == 40){
	
		//$tpl->addFile("CONTEUDO", 'html_libs/ope_index.html');
		return;
	
	}else{
	
		$tpl->addFile("CONTEUDO", 'html_libs/ope_apagarNotas.html');
		$sql_cont	= ($_SESSION['nivel'] != 1) ? "SELECT id FROM daee_contratos WHERE permissao=$nivel" : "SELECT id FROM daee_contratos";
		$query_cont	= mysql_query($sql_cont);
		$qtd = 0;
	
		if(mysql_num_rows($query_cont) > 0){
			
			$i = 1;
			while($res = mysql_fetch_array($query_cont)){
			
				$contrato		= new Contrato($res['id']);
				$tpl->AUTOS 	= $i. " - " . $contrato->geraNome();
				$tpl->PERIODO	= $periodo;
				$notas = $contrato->getAllNotasPorPeriodo($periodo,false);
				if(count($notas) > 0){
				
					if($notas[0]['id'] != null){
					
						foreach($notas as $nota){
						
							$tpl->NOTANUM	= $nota['numero'];
							$tpl->UO		= $nota['unidade'];
							$tpl->UC		= $nota['rgi'] . " - " . $nota['compl'];
							$tpl->EMPRESA	= $nota['nome'];
							$tpl->DATAREF	= getMesNome($nota['mes_ref']) . "/" . $nota['ano_ref'];
							$tpl->CONSUMO	= tratarValor($nota['consumo']);
							$tpl->VALOR		= tratarValor($nota['valor'],true);
							$tpl->EMISSAO	= setDateDiaMesAno($nota['emissao']);
							$tpl->LANCTO	= ExplodeDateTime($nota['criado'],true);
							$tpl->LANCPOR	= $nota['login'];
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
		
		}	

	}

	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos";	
	$tpl->show();
	
?>