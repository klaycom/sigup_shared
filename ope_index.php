<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include_once "src/classes/Users.class.php";
	include "src/classes/Contratos.class.php";
	
	
	redirectByPermission(2); // SETAR PERMISSÃO DA PÁGINA
	
	$tpl = new Template('html_libs/template_ope.html');
	
	$tpl->addFile("MENU", 'html_libs/ope_secondMenu.html');
	$tpl->addFile("CONTEUDO", 'html_libs/ope_index.html');
	$tpl->block('SHOW_1STBUTTON');
	
	$nivel = $_SESSION['nivel'];
	
	if(isset($_GET['data_ref']) && (strlen($_GET['data_ref']) >=6 && strlen($_GET['data_ref']) <=7)){
	
		$mes = explode("-", $_GET['data_ref']);
		$mes_ant= $mes[0];
		$ano 	= $mes[1];
		$tpl->MES_ANT = getMesNome($mes_ant, false);
		$tpl->ANO = $ano;		
	
	}else{
	
		$mes_ant= (Date('n') > 1) ? Date('n') - 1 : 12;
		$ano 	= (Date('n') > 1) ? Date('Y'): Date('Y') - 1;
		$tpl->MES_ANT = getMesNome($mes_ant, false);
		$tpl->ANO = $ano;
	
	}
	
	for($i = 1; $i <=12; $i++){
	
		$tpl->MESREF = $i . "-" . Date('Y');
		$tpl->MESANOREF= getMesNome($i) . "/" . Date('y');
		$tpl->block('EACH_MESMENU');
		
	}
	
	$sql_cont	= ($_SESSION['nivel'] != 1) ? "SELECT id FROM daee_contratos WHERE permissao=$nivel" : "SELECT id FROM daee_contratos";
	
	$query_cont	= mysql_query($sql_cont);
	$qtd = 0;
	
	if(mysql_num_rows($query_cont) > 0){
		
		$i = 1;
		while($res = mysql_fetch_array($query_cont)){
		
			$contrato = new Contrato($res['id']);
			$tpl->AUTOS = $i. " - " . $contrato->geraNome();
			$ucs = $contrato->getFaltaNotas($mes_ant, $ano);
			if(count($ucs) > 0){
			
				if($ucs[0]['ucid'] != null){
				
					foreach($ucs as $uc){
					
						$tpl->RGI		= $uc['rgi'];
						$tpl->ENDERECO	= $uc['endereco'];
						$tpl->ID		= $uc['ucid'];
						$tpl->MES_ANO	= $mes_ant."-".$ano;
						$tpl->block('EACH_FALTA');
						$qtd++;
					
					}
				
				}
			
			}
			
			$tpl->block("EACH_CONTRATO");
			$i++;
		
		}
	
	}
	

	$tpl->CONTAQTD = $qtd;
	$tpl->show();
	
?>