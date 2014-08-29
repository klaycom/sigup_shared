<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include_once "src/classes/Users.class.php";
	include_once "src/classes/Notas.class.php";
	include_once "src/classes/Contratos.class.php";
	include_once "src/classes/Relatorio.class.php";
	
	
	redirectByPermission(1); // SETAR PERMISSÃO DA PÁGINA
	$inicio = execucao();
	
	$tpl = new Template('html_libs/template.html');
	
	$tpl->addFile('SECONDMENU','html_libs/udd_secondMenu.html');
	$tpl->addFile('CONTEUDO','html_libs/rel_pendentes.html');
	//$tpl->addFile("JSCRIPT", 'cssjs_libs/js_parts/js_rel_notas.html');
	
	/**********************
	MOSTRAR ANO_REF DE FORM
	**********************/
	$ano_atual = Date('Y');
	for($i= $ano_atual; $i >= 2012; $i--){
	
		$tpl->ANO_REF = $i;
		$tpl->block('EACH_ANOREF');
	
	}


	/********************************************
	  MOSTRAR TABELA COM NOTAS VINDAS DE $_POST
	********************************************/
	
	if (getenv("REQUEST_METHOD") == "POST") {
	
		if(isset($_POST['mes_ref'],$_POST['ano_ref'])){
		
			$mes = ($_POST['mes_ref'] == "") ? "nulo" : $_POST['mes_ref'] ;
			$ano = ($_POST['ano_ref'] <= Date('Y') || $_POST['ano_ref'] >=2012) ? $_POST['ano_ref'] : Date('Y');
			
			$tpl->MESNOME = strtoupper(getMesNome($mes, false));
			$tpl->ANO	  = $ano;
			
			$sql_cont	= "SELECT id FROM daee_contratos";
			
			$query_cont	= mysql_query($sql_cont);
			$qtd	= 0;
			$i		= 0;
			
			$rel = new Relatorio();
			$rel->setUdds();
			$udds	= $rel->udds;
			$cores	= $rel->cores;
			$tipos	= $rel->ucTipos;
			
			if(mysql_num_rows($query_cont) > 0){		
				
				while($res = mysql_fetch_array($query_cont)){	
				
					$contrato = new Contrato($res['id']);
					$notas = $contrato->getFaltaNotas($mes, $ano);
				
					if($notas[0]['ucid'] != null){

						$qtd += count($notas);
						foreach($notas as $nota){

							$consTipo 		= $nota['tipo'];				
							$tpl->UO 		= $udds[$nota['uo']];
							$tpl->AUTOS		= $contrato->geraNome();
							$tpl->UC		= "<b>" . $nota['rgi'] . "</b> - [ <small>" . $nota['endereco'] . "</small> ]";
							$tpl->UC		.= "<br /><small><font color='". $cores[$consTipo] ."'>". $tipos[$consTipo] ."</font></small>";	
							$tpl->VENCTO	= setDateDiaMesAno($nota['vencto']);
							
							$i++;
							$tpl->INDEX = $i;
							$tpl->block('EACH_NOTAS');
						
						}
					
					}
				
				}
			
			}
			
			$tpl->NOTAQTD = $qtd;

			
			$tpl->block("RESULT_BLOCK");
		
		}
	
	}
	
	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos ";		
	$tpl->show();
	
?>