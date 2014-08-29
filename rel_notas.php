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
	$tpl->addFile('CONTEUDO','html_libs/rel_notas.html');
	$tpl->addFile("JSCRIPT", 'cssjs_libs/js_parts/js_rel_notas.html');
	
	/**********************
	MOSTRAR ANO_REF DE FORM
	**********************/
	$ano_atual = Date('Y');
	for($i= $ano_atual; $i >= 2012; $i--){
	
		$tpl->ANO_REF = $i;
		$tpl->block('EACH_ANOREF');
		$tpl->block('EACH_ANO');
	
	}
	
	/**********************
	MOSTRAR DATAS EM FORM
	**********************/
	for($i = 1; $i <= 31; $i++){
	
		$tpl->DIANUM = ($i < 10) ? '0' . $i : $i;
		$tpl->block('EACH_DIA');
	
	}
	for($i = 1;$i <=12; $i++){
	
		$tpl->MESNUM = ($i < 10) ? '0' . $i : $i;
		$tpl->MESNOME = getMesNome($i, false);
		$tpl->block('EACH_MES');
	
	}
	
	/**********************
	MOSTRAR FILTROS DE FORM
	**********************/	
	
	$rel = new Relatorio();
	$tipos = $rel->ucTipos;
	$qtdTipos = count($tipos);
	for($i=0; $i < $qtdTipos; $i++){
	
		$tpl->FILTER_NAME = "Apenas " . $tipos[$i];
		$tpl->FILTER_QUERY = "c.tipo = $i";
		
		$tpl->block('FILTER_TIPO');
	
	}
	
	$filters = $rel->filters;
	foreach($filters as $title => $query){
	
		$tpl->FILTER_NAME = $title;
		$tpl->FILTER_QUERY = $query;		
		
		$tpl->block('FILTER_TIPO');
	
	}
	
	/********************************************
	  MOSTRAR TABELA COM NOTAS VINDAS DE $_POST
	********************************************/
	
	if (getenv("REQUEST_METHOD") == "POST") {
	
		if(isset($_POST['mes_ref'],$_POST['ano_ref'],$_POST['order_by'])){
		
			$mes = ($_POST['mes_ref'] == "") ? "nulo" : $_POST['mes_ref'] ;
			$ano = ($_POST['ano_ref'] <= Date('Y') || $_POST['ano_ref'] >=2012) ? $_POST['ano_ref'] : Date('Y');
			$orientation = ($_POST['orientacao'] == "cresc") ? "ASC" : "DESC";
			
			switch($_POST['order_by']){
			
				case "n.criado":
					$orderby = "Data de Lançamento";
				break;
				case "n.emissao":
					$orderby = "Data de Emissão";
				break;
				case "n.vencto":
					$orderby = "Data de Vencimento";
				break;
				case "n.saida":
					$orderby = "Data de Saída ADA/DOF";
				break;
				case "n.pagto":
					$orderby = "Data de Pagamento";
				break;
				case "n.consumo":
					$orderby = "Consumo";
				break;
				case "n.valor":
					$orderby = "Valor de Nota";
				break;
				case "c.tipo":
					$orderby = "Tipo de Despesa";
				break;
				case "n.contrato ASC, n.prov":
					$orderby = "ID de Autos";
				break;
				case "n.uc":
					$orderby = "ID de Unidade Consumidora";
				break;
				case "n.usuario":
					$orderby = "ID de Usuario";
				break;
				case "c.uo":
					$orderby = "ID de Unidade Operacional";
				break;				
			
			}
			
			$tpl->ORDERBY = $orderby; 
			$tpl->MESNOME = strtoupper(getMesNome($mes, false));
			$tpl->ANO	  = $ano;
			$tpl->ORIENTATION = ($_POST['orientacao'] == "cresc") ? "Crescente" : "Decrescente";
			$filter = $_POST['filter'];
			$data	= ($_POST['dia-lanc'] != "" && $_POST['mes-lanc'] != "" && $_POST['ano-lanc'] != "") ? $_POST['ano-lanc']."-".$_POST['mes-lanc']."-".$_POST['dia-lanc'] : "";
			
			if($mes == "nulo" && ($_POST['dia-lanc'] == "" || $_POST['mes-lanc'] == "" && $_POST['ano-lanc'] == "")) {
			
				$data = Date('Y-m-d');
			
			}
			
			$notas = $rel->getRelNotas($mes, $ano, $filter, $_POST['order_by'], $orientation, $data);
			
			//$tpl->TESTE = $notas[0]['sql'];
			
			
			if($notas[0]['id'] != null){
			
				$tpl->NOTAQTD = count($notas);

				$i = 1;
				foreach($notas as $nota){
				
					$consTipo 		= $nota['constipo'];
					$cores			= $rel->cores;
					$tpl->UO 		= $nota['unidade'];
					$tpl->UC		= "<b>" . $nota['rgi'] . "</b> - [ <small>" . $nota['compl'] . "</small> ]";
					$tpl->UC        .= $nota['desc'] != "" ? " <font color='red'>*</font>" : "";
					$tpl->UC		.= "<br /><small>Tipo: <font color='". $cores[$consTipo] ."'>". $tipos[$consTipo] ."</font></small>";
					$tpl->UC		.= $nota['ativo'] == 0 ? " - <small><font color='red'>[ Inativo ]</font></small>" : " - <small><font color='green'>[ Ativo ]</font></small>";
					$tpl->STATUS	= $nota['ativo'] == 0 ? "<small><font color='red'>INATIVO</font></small>" : "<small><font color='green'>ATIVO</font></small>";
					$tpl->EMISSAO	= setDateDiaMesAno($nota['emissao']);
					$tpl->LANCTO	= ExplodeDateTime($nota['criado'],true);
					$tpl->VENCTO	= setDateDiaMesAno($nota['vencto']); 
					$tpl->SAIDA		= setDateDiaMesAno($nota['saida']);
					$tpl->PAGOEM	= setDateDiaMesAno($nota['pagto']);
					$tpl->CONSUMO	= tratarValor($nota['consumo'],false);
					$tpl->VALOR		= tratarValor($nota['valor'], true);
					$tpl->USUARIO	= $nota['login'];
					$tpl->NOTAID	= substr(sha1($nota['id']),0, 5);
					$tpl->NUMERO	= $nota['numero'];
					
					$autos1	= $nota['pasta'] == 0 ? "Autos " : "Processo ";
					$autos2	= $nota['pasta'] == 0 ? number_format($nota['num'],0,',','.') : $nota['num'];
					$autos3	= $nota['autos'];
					$tpl->AUTOS		= $autos1 . " n°" . $autos2 . " - " . $autos3;
					$tpl->PROV		= $nota['provisoria'];
					
					$tpl->COMPL 	= $nota['endereco'] == "" ? $nota['compl'] : $nota['endereco'];
					$tpl->EMPRESA	= $nota['empresa'];
					$tpl->OBS		= $rel->getObs($nota['desc']);
					$tpl->MESREF	= getMesNome($nota['mes_ref'], true) . "/" . $nota['ano_ref'];
					
					$tpl->INDEX = $i;
					$i++;
					
					
					
					$tpl->block('EACH_NOTAS');
				
				}
			
			}
			
			$tpl->block("RESULT_BLOCK");
		
		}
	
	}
	
	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos ";		
	$tpl->show();
	
?>