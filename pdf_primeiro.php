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
	$mes	= 1;
	$ano	= 2012;
	
	$insta	= new AutoRelatorio($uoid,$mes,$ano);
	$allUcs = $insta->uo->getAllUcs();
	$ucQtd	= sizeof($allUcs);
	$sigla	= $insta->uo->getSigla();
	$nome	= $insta->uo->getNome();
	$tipos	= $insta->uo->getTiposServicos();
	$total	= 0;
	
	$linha_tab = "";
	foreach($tipos as $tipo){
	
		$totmes = $insta->uo->getTotalTipoMes($tipo,$mes,$ano);
		$linha_tab.= '<tr><td width="75%" height="25" style="border-left:4px solid '. $insta->cores[$tipo] .'">'. $insta->ucTipos[$tipo] .'</td><td align="right" style="background-color:#fafafa"> ' . tratarValor($totmes,true) . '</td></tr>';
		$total += $totmes;
	
	}
	$linha_tab.= '<tr><td height="29" style="background-color:#f0f0f0"><b>TOTAL:</b></td><td align="right" style="background-color:#f0f0f0"><b>R$ ' . tratarValor($total,true) . '</b></td></tr>';
	
	$mpdf=new mPDF('utf-8', 'A4','10','serif',19,15,42,42,10,10); 
	
	$stylesheet = file_get_contents('cssjs_libs/pdf_style.css');
	
	$header = '
		<table width="100%" style="border-bottom: 1px solid #000000; font-family: serif; font-size: 9pt; "><tr>
		<td width="12%"><img src="images/logo-daee.png" height="57px" /></td>
		<td width="73%" align="center" valign="middle" ><h3 style="margin:0;padding:0;">Sistema de Gerenciamento de Despesas com Utilidade Pública
		<br />DEPARTAMENTO DE ÁGUAS E ENERGIA ELÉTRICA<br /><small>Rua Boa Vista, 175 - 1°andar B - 3293-8543 - CEP01014-000 - São Paulo - SP</small></h3></td>
		<td width="15%" style="text-align: right;"><img src="images/logo-sigup.jpg" height="67px" /></td>
		</tr></table>
	';
	$mpdf->SetHTMLHeader($header);
	$footer = '
		<table width="100%" style="border-top: 1px solid #000000; font-family: serif;"><tr>
		<td width="25%" align="left"><span class="serif-small">Relatório SIGUP : '. $sigla .'</span></td>
		<td width="50%" align="center" valign="middle" ><span class="serif-small">Emissão: '. Date('d/m/Y') .'</span></td>
		<td width="25%" align="right" style="text-align: right;"><span class="serif-small">'. getMesNome($mes, false) . ' de ' . $ano .'</span></td>
		</tr></table>	
	';
	$mpdf->SetHTMLFooter($footer);
	
	$head		= '<h2>Relatório <span class="bold-blue">' . getMesNome($mes, false) . ' de ' . $ano . '</span> de <span class="bold-blue">' . $sigla .'</span></h2>' ;
	$paragrafo1	= '<p>O(a) <span class="bold-blue">' . $nome . '</span> possui <b>' . $ucQtd .'</b> unidades consumidoras.</p>';
	$paragrafo2 = '<p>Em ' . getMesNome($mes, false) . ' de ' . $ano . ' o(a) ' . $sigla . ' teve um total de <b>R$' . tratarValor($total,true) . '</b> de despesas com Utilidade Pública. Este total engloba valores 
				de ' . sizeof($tipos) . ' tipos de consumo, como demonstrados na tabela a seguir:</p>';
	$tabela1	= '<table width="100%"><tr><th height="35" style="background-color:#00b2ec; color: white;">TIPO DE CONSUMIDORAS</th><th align="center" style="background-color:#00b2ec; color: white;">TOTAL (R$)</th></tr>' . $linha_tab . '</table>';
	$legenda1	= '<p><small>Tabela 1 : Despesas por tipo de consumidora do '. $sigla .' de '.getMesNome($mes, true).'/' . $ano .'</small></p>';
	$paragrafo3 = '<p>Em virtude deste ser o primeiro exercício mensal cadastrado e controlado pelo SIGUP, pode-se considerar este como o ponto de partida para as análises de relatórios. Exclusivamente neste relatório não há demonstração de variações e outros cálculos devido ao fato de não termos dados anteriores cadastrados.</p>';
	
	

	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	
	$html_all = $head . $paragrafo1 . $paragrafo2 . $tabela1 . $legenda1 . $paragrafo3 ;

	$mpdf->WriteHTML($stylesheet,1);	
	$mpdf->WriteHTML($html_all);
	//$mpdf->WriteHTML("asa");
	$exec = "Tempo de Execução: <b>".$tempo."</b> segundos";
	$filename = "Relatorio " . $insta->get("sigla") . " - " . $ano . " " . getMesNome($mes) ;
	$mpdf->Output($filename, "D");	

?>