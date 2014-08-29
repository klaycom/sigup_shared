<?php 
	/*
	 * Classe de Relatorios Autoimáticos
	 * 
	 */
	 include_once "src/classes/Unidade.class.php";
	 
	Class AutoRelatorio{
	
		public $ucTipos = Array("Água e Esgoto","Energia de Baixa Tensão", "Telefonia","Gás");
		
		public $cores	= Array('#7B68EE','#00BFFF','#FFD700','#CD5C5C','#CD853F','#FF1493','#54FF9F','#EEB4B4','#7D26CD','#90EE90');
		public $tipoCont= Array("Autos","Processo");
		public $filters = Array('Valores menores que R$1.000'=>'n.valor < 1000', 'Valores menores que R$10.000'=>"n.valor < 10000",
								'Valores maiores que R$10.000'=>'n.valor > 10000','Consumo menor que 100'=>"n.consumo < 100",
								'Consumo maior que 100'=>"n.consumo > 100", 'Consumo maior que 1000'=>"n.consumo > 1000"
								);						
		public $uo;
		private $mes;
		private $ano;
		private $sigla;
		private $nome;
		private $ucQtd;
		private $uc_a;
		private $uc_i;
		private $tipo_cons;		// Array
		private $tipo_val_a;	// Array com valores atual
		private $tipo_val_b;	// Array com valores anteriores
		private $total	= 0;	// do Mês
		private $total_a= 0;	// do ano Anterior
		private $mes_a;			// Mês de Comparação anterior
		private $ano_a;			// Ano de Comparação anterior
		private $variacoes;		// Array[rgi][ano]
 		
		function __construct($uoid,$mes,$ano){
		
			$this->uo = new Unidade($uoid);
			$this->mes = $mes;
			$this->ano = $ano;
			$this->mes_a = ($this->mes == 1) ? 12 : $this->mes - 1;
			$this->ano_a = ($this->mes == 1) ? $ano - 1 : $ano;
			
			
			$this->sigla	= $this->uo->getSigla();
			$this->nome		= $this->uo->getNome();
			$this->ucQtd	= $this->uo->getQtdUc();
			$this->uc_a		= $this->uo->getQtdUcStatus(1);
			$this->uc_i		= $this->ucQtd - $this->uc_a;
			$this->tipo_cons= $this->uo->getTiposServicos();
			$data_ref		= $this->ano . "-" . $this->mes;
			$this->setHistory($_SESSION['usuario'], $this->sigla, $data_ref);
		
		}
		
		/*****************************************
		 * Método para retornar $param da do rel *
		 *****************************************/
		public function get($param){
		 
			return $this->$param;
		 
		}

		/***********************************
		 * Método para criar header do pdf *
		 ***********************************/		
		public function getHeader(){
		
			$header = '
				<table width="100%" style="border-bottom: 1px solid #000000; font-family: serif; font-size: 9pt; "><tr>
				<td width="12%"><img src="images/logo-daee.png" height="57px" /></td>
				<td width="73%" align="center" valign="middle" ><h3 style="margin:0;padding:0;">Sistema de Gerenciamento de Despesas com Utilidade Pública
				<br />DEPARTAMENTO DE ÁGUAS E ENERGIA ELÉTRICA<br /><small>Rua Boa Vista, 175 - 1°andar B - 3293-8543 - CEP01014-000 - São Paulo - SP</small></h3></td>
				<td width="15%" style="text-align: right;"><img src="images/logo-sigup.jpg" height="67px" /></td>
				</tr></table>
			';
			return $header;
		
		}

		/***********************************
		 * Método para criar footer do pdf *
		 ***********************************/			
		public function getFooter(){
		
			$footer = '
				<table width="100%" style="border-top: 1px solid #000000; font-family: serif;"><tr>
				<td width="25%" align="left"><span class="serif-small">Relatório SIGUP : '. $this->sigla .'</span></td>
				<td width="50%" align="center" valign="middle" ><span class="serif-small">Emissão: '. Date('d/m/Y') .'</span></td>
				<td width="25%" align="right" style="text-align: right;"><span class="serif-small">'. getMesNome($this->mes, false) . ' de ' . $this->ano .'</span></td>
				</tr></table>	
			';
			return $footer;
		
		}
		
		/***********************************
		 * Método para criar title do pdf *
		 ***********************************/	
		public function makeTitle(){
		
			$head = '<h2>Relatório <span class="bold-blue">' . getMesNome($this->mes, false) . ' de ' . $this->ano . 
					'</span> de <span class="bold-blue">' . $this->sigla .'</span></h2>' ;
			return $head;
		
		}
		
		/********************************
		 * Método para criar paragrafo1 *
		 ********************************/
		public function makeParagrafo1(){
		
			$p = '<p>O(a) <span class="bold-blue">' . $this->nome . '</span> possui <b>' . $this->ucQtd .'</b> unidades consumidoras estando no momento ';
			$p.= '<font color="green">' . $this->uc_a . '</font> ativa(s) e <font color="red">' . $this->uc_i . '</font> inativa(s). ';
			$p.= 'Salientando que além das Unidades Consumidoras (UCs) de água e energia elétrica, cada número de telefone é também considerado pelo sistema como UC.</p>';
			return $p;
		
		}
		
		/********************************
		 * Método para criar paragrafo2 *
		 ********************************/
		public function makeParagrafo2(){
		
			$this->setTotais();
			$p = '<p>Em ' . getMesNome($this->mes, false) . ' de ' . $this->ano . ' o(a) ' . $this->sigla . ' teve um total de <b>R$ ' . tratarValor($this->total,true) . '</b> de despesas com Utilidade Pública. ';
			if($this->ano != 2012){
			
				$varia = $this->total * 100 / $this->total_a - 100;
				$p .= 'No exercício do mesmo mês do ano anterior o gasto total foi de R$ ' . tratarValor($this->total_a, true) . ', representando uma variação de <b>' . getPorcentagem($varia,true) . '</b>. ';
				
			}
			$p.= 'Esses totais englobam valores de ' . sizeof($this->tipo_cons) . ' tipos de consumo, como demonstrados na tabela:</p>';
			return $p;
		
		}
		
		/******************************
		 * Método para criar tabela 1 *
		 ******************************/		
		public function makeTabela1(){
		
			$mes = getMesNome($this->mes);
			$anoa= $this->ano - 1;
			$t = '<table width="100%"><tr><th style="background-color:#00b2ec; color: white;">TIPO DE CONSUMIDORAS</th><th align="center" style="background-color:#00b2ec; color: white;">TOTAL (R$)<br />'.$mes.'/' . $anoa .'</th>';
			$t.= '<th align="center" style="background-color:#00b2ec; color: white;">TOTAL (R$)<br />'.$mes.'/' . $this->ano .'</th></tr>';
			$i = 0;
			foreach($this->tipo_cons as $tipo){
			
				$t.= '<tr><td width="60%" height="20" style="border-left:4px solid '. $this->cores[$tipo] .'">'. $this->ucTipos[$tipo] . '</td>';
				$t.= '<td width="20%" align="right">' . tratarValor($this->tipo_val_b[$i],true). '</td></tr>';
				$t.= '<td width="20%" align="right" style="background-color:#fafafa"> ' . tratarValor($this->tipo_val_a[$i],true) . '</td>';
				$i++;
	
			}
			$t.= '<tr><td height="29" style="background-color:#f0f0f0"><b>TOTAL:</b></td><td align="right" style="background-color:#f0f0f0"><b>R$ ' . tratarValor($this->total_a,true) . '</b></td>';
			$t.= '<td align="right" style="background-color:#f0f0f0"><b>R$ ' . tratarValor($this->total,true) . '</b></td></tr>';
			$t.= '</table>';
			$t.= '<small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tabela 1 : Valores Consolidados de ' . $anoa .' e ' . $this->ano . '</small>';
			
			return $t;
		
		}
		
		/********************************
		 * Método para criar paragrafo3 *
		 ********************************/	
		public function makeParagrafo3(){
		
			$p = '<p>As seguintes Unidades Consumidoras resultaram em aumento com relação ao mês anterior <b>('. getMesNome($this->mes_a,true) .'/'. $this->ano_a .')</b> :</p>';
			$table = $this->constructVarTable($this->setVariacoes());
			$p.= $table[0];
			$p.= '<p>Em contrapartida as seguintes UCs sofreram reduções com relação ao mês anterior <b>('. getMesNome($this->mes_a,true) .'/'. $this->ano_a .')</b> :</p>';
			$p.= $table[1];
			$p.= '<p>As demais UCs não apresentaram variações significativas.</p>';
			
			return $p;
		
		}
		
		/*************************************
		 * Método para criar Evolution Table *
		 *************************************/	
		public function makeEvolutionGraph(){
		
			$p = '<p>A evolução financeira em '.$this->ano.' do '. $this->sigla .' apresenta-se da seguinte forma.</p>';
			$table = '<table id="evolution-graph" style="display:hidden">';
			$array;
			$sql = 'SELECT n.mes_ref,SUM(n.valor) AS soma FROM daee_notas n,daee_uddc c WHERE n.ano_ref = '.$this->ano.' AND n.uc=c.id AND c.uo='. $this->uo->get('id') .' ';
			$sql.= 'GROUP BY n.mes_ref ORDER BY n.mes_ref ';
			$query = mysql_query($sql);
			while($res = mysql_fetch_array($query)){
			
				$array[getMesNome($res['mes_ref'])] = $res['soma'];
			
			}
			
			$data_send = serialize($array);
			$data_send = urlencode($data_send);
			$sigl_send = $this->sigla;
			$ano_send  = $this->ano;
			$img_src   = "data=$data_send&sigla=$sigl_send&ano=$ano_send";
			$p.= '<img src="images/evo_graph.php?'. $img_src .'&sigla='. $sigl_send .'&ano'. $ano_send .'" />';
			
			
			return $p;
		
		}		
		
		/***************************************
		 * Método para criar - Notas Faltantes *
		 ***************************************/
		public function makeNotasFaltantes(){
		
			$p = "";
			$faltam = $this->getFaltaNotas();
			if(count($faltam) > 0){
			
				if($faltam[0]['rgi'] != 0){
				
					$p = '<pagebreak /><p>Não foram encontrados lançamentos no mês de '. getMesNome($this->mes, false) .' para as UCs: </p>';
					foreach($faltam as $f){
					
						$p.= "<b>" . $f['rgi'] . "</b> - [ " . $f['endereco'] . " ] - [ <i>". $this->ucTipos[$f['tipo']] ."</i> ]<br /> " ;
					
					}
					$p.= "<p>Salientando que as notas podem estar faltando por diversos motivos como o não envio de contas à ADA, desativação de UC não informada, paralisação temporária de UC e etc. ";
					$p.= "O SIGUP depende da colaboração de todos os representantes para manter-se sempre ativo e atualizado.</p>";
				
				}
			
			}
			
			return $p;
		
		}
		
		/***********************************************
		 * Método para setar totais e variações em UCs *
		 ***********************************************/
		private function setTotais(){
		
			foreach($this->tipo_cons as $tipo){
				
				$anoant = $this->ano - 1;
				$totmes = $this->uo->getTotalTipoMes($tipo,$this->mes,$this->ano);
				$totant = $this->uo->getTotalTipoMes($tipo,$this->mes,$anoant);
				$this->total	+= $totmes;
				$this->total_a	+= $totant;
				$this->tipo_val_a[] = $totmes;
				$this->tipo_val_b[] = $totant;
				
				
			
			}
		
		}
		
		/*********************************************
		 * Método para setar array de variações de UC*
		 *********************************************/
		private function setVariacoes(){
		
			$ucs = $this->uo->getAllUcs();
			foreach($ucs as $ucid){
			
				$sql = "SELECT c.compl,c.rgi,c.tipo, SUM(n.valor) AS atual, s.anterior FROM (SELECT SUM(valor) AS anterior FROM daee_notas WHERE uc = $ucid AND mes_ref = ";
				$sql.= $this->mes_a . " AND ano_ref = ". $this->ano_a .") s, daee_notas n, daee_uddc c WHERE c.id = n.uc AND n.uc = $ucid AND n.mes_ref = ";
				$sql.= $this->mes ." AND n.ano_ref = " . $this->ano;
				$ret[] = mysql_fetch_array(mysql_query($sql));
			
			}
			
			return $ret;
		
		}
		
		private function constructVarTable($array){
		
			$table = '<table width="100%"><tr><th height="35pt">UNIDADE CONSUMIDORA</th><th>TIPO</th><th align="right">'. strtoupper(getMesNome($this->mes_a)) .'</th><th align="right">'. strtoupper(getMesNome($this->mes)) .'</th>';
			$table.= '<th align="right">VARIAÇÃO</th></tr>';
			$rowMais = "";
			$rowMenos= "";
			foreach($array as $arr){
			
				if($arr['anterior'] > 0){
				
					$varia = $arr['atual'] * 100 / $arr['anterior'] - 100;
					$row = '<tr><td><small><b>' . $arr['rgi'] . '</b> - '. $arr['compl'] . '</small></td><td><small>' . $this->ucTipos[$arr['tipo']] . '</small></td>';
					$row.= '<td align="right"><small>' . tratarValor($arr['anterior'],true) . '</small></td><td align="right"><small>' . tratarValor($arr['atual'],true) . '</small></td>';
					$row.= '<td align="right"><small>' . getPorcentagem($varia,true) . '</small></td></tr>';
					if(round($varia) > 0)
						$rowMais .= $row;
					elseif(round($varia) < 0)
						$rowMenos.= $row;
						
				}
			
			}
			$closeTable = '</table>';
			
			$ret[0] = $table . $rowMais . $closeTable;
			$ret[1] = $table . $rowMenos. $closeTable;
			return $ret;
		
		}
		
		private function getFaltaNotas(){
		
			$sql = "SELECT A.rgi AS rgi, CONCAT(A.rua,' ', A.compl) AS endereco, A.tipo AS tipo FROM daee_uddc A "; 
			$sql.= "LEFT JOIN daee_notas B ON A.id = B.uc AND B.mes_ref = ". $this->mes . " AND B.ano_ref = " . $this->ano . " ";
			$sql.= "WHERE B.uc IS NULL AND A.ativo = 1 AND A.uo=". $this->uo->get('id') ;
			$query = mysql_query($sql);
			if(mysql_num_rows($query) > 0){
			
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res;
				
				}
			
			}else{
			
				$ret[]['rgi']	= null;
			
			}
		
			return $ret;
			
		}

		/*********************************************
		 * Método Registrar Visualização de Relatório*
		 *********************************************/		
		private function setHistory($user, $sigla, $data_ref){
		
			$sql = "INSERT INTO rel_history VALUES ('','$user','$sigla', NOW(),'$data_ref')";
			mysql_query($sql);
		
		}
		
		
	}
	 
	 
	 
?>