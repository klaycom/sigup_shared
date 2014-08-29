<?php
	
	include("src/mpdf/mpdf.php");
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include "src/classes/Users.class.php";
	include "src/classes/AutoRelatorio.class.php";

		//$tpl->TESTE = $emp->getServicos()[2]->children();	
	
	
	redirectByPermission(0); // SETAR PERMISSÃO DA PÁGINA
	$inicio = execucao();
	
	$uoid	= $_SESSION['bacia'];
	$mes	= (isset($_GET['mes']) && $_GET['mes'] > 0 && $_GET['mes'] < 13) ? $_GET['mes'] : Date('n') - 1;
	$ano	= (isset($_GET['ano']) && $_GET['ano'] >2011 && $_GET['ano'] <= Date('Y')) ?  $_GET['ano'] : Date('Y');
	
	$insta	= new AutoRelatorio($uoid,$mes,$ano);
	
	$mpdf=new mPDF('utf-8', 'A4','10','serif',16,12,42,42,10,10); 
	
	$stylesheet = file_get_contents('cssjs_libs/pdf_style.css');
	
	$mpdf->SetHTMLHeader($insta->getHeader());
	$mpdf->SetHTMLFooter($insta->getFooter());
	
	$html_all = $insta->makeTitle() . $insta->makeParagrafo1() . $insta->makeParagrafo2() . $insta->makeTabela1() . $insta->makeParagrafo3();// . $insta->setVariacoes();
	
	$html_all.= $insta->makeEvolutionGraph() . $insta->makeNotasFaltantes();

	
	
	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$exec = "<p>Qualquer dúvidas, correções ou sugestões, envie um email para <b>gnakano@sp.gov.br</b> ou <b>avelez@sp.gov.br</b> . Contamos com a sua participação!</p><font color='white'>Tempo de Execução: <b>".$tempo."</b> segundos</font>";
	

	$mpdf->WriteHTML($stylesheet,1);
	$mpdf->WriteHTML($html_all . $exec);
	//$mpdf->WriteHTML("asa");
	
	
	$filename = "Relatorio " . $insta->get("sigla") . " - " . $ano . " " . getMesNome($mes) ;
	$mpdf->Output($filename, "D");

?>