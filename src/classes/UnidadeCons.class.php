<?php 
	/*
	 * Classe de Unidades Consumidoras do DAEE
	 * 
	 */
	 
	include_once "src/classes/Unidade.class.php";
	include_once "src/classes/Empresas.class.php"; 
	include_once "src/classes/Contratos.class.php"; 
	
	Class UnidadeConsumidora extends Unidade{
	 
		private $id;		// int
		private $rgi;		// Registro
		private $endereco;	// string
		private $numero;	// int
		private $nome;		// string outras infos de localização
		private $tipo;		// 0 = Água; 1 = Energia BT; 2 = Telefonia; 3 = Gás  
		private $uo;		// Unidade Operacional PAI
		private $lat;		// String (decimal)
		private $long;		// String (decimal)
		private $cidade;	// Int ID DA CIDADE
		private $empresa;
		private $contrato;
		private $notas;		// Todas as notas da UC
		private $anoNotas;	// Todas as notas do ANO da UC
		
		
		public $teste;		// PARA FINALIDADE DE TESTES
		
		/*
		 * Construtor de classe
		 *
		 * Caso forneça todos os argumentos, criar unidade no banco de dados,
		 * Caso contrário, apenas retornar valores
		 *
		 */
		
		function __construct($id, $rgi=null, $endereco=null, $numero=null, $compl=null, $contrato=null, $vencto=null, $uo=null, $cidade=null, $lat=null, $long=null,$tipo=null, $empresa=null){ 
		
			if(isset($rgi)){
				
				$endereco = $endereco;
				$sql 	= "INSERT INTO daee_uddc VALUES('','$rgi','$numero','$compl','$endereco',$tipo,$vencto,$uo,$cidade,'$lat','$long',1,$contrato,$empresa)";
				mysql_query($sql);
				//$this->teste = $sql;
				
			}elseif($id > 0){
			
				$this->id = $id;
				
				$sql = "SELECT * FROM daee_uddc WHERE id = $id";
				$query = mysql_query($sql);
				if(mysql_num_rows($query) == 1){
				
					$res = mysql_fetch_array($query);
					$this->rgi 		= $res['rgi'];
					$this->endereco	= $res['rua'];
					$this->numero	= $res['numero'];
					$this->nome		= $res['compl'];
					$this->tipo		= $res['tipo'];
					$this->vencto	= $res['vencto'];
					$this->cidade	= new Cidade($res['cidade']);
					$this->lat		= $res['lat'];
					$this->long		= $res['long'];
					$this->uo		= new Unidade($res['uo']);
					$this->empresa	= new Empresa($res['empresa']);
					$this->ativo	= $res['ativo'];
					if($res['contrato'] != ""){
					
						$this->contrato = new Contrato($res['contrato']);
					
					}
				
				}
				
			}elseif(isset($tipo)){
			
				$this->tipo = $tipo;
			
			}
			
		}

		/*
		 * Método para verificar se já existe Unidade Consumidora
		 * @return Boolean
		 */
		 
		function verificaExiste($rgi,$contrato){
		
			$sql = "SELECT * FROM daee_uddc WHERE rgi = '$rgi' AND contrato = $contrato AND ativo = 1";
			$query = mysql_query($sql);
			if(mysql_num_rows($query) >= 1) 
				return true;
			else
				return false;
			
		}		
		
		/*
		 * Método para retornar nome da Unidade
		 */
		 
		function getNome(){
		 
			return $this->nome;
		 
		}
		
		/*
		 * Método para retornar nome do Tipo
		 */
		 
		function getTipoNome(){
		
			if($this->tipo == 0) $ret = "Água e Esgoto";
			elseif($this->tipo == 1) $ret = "Energia de Baixa Tensão";
			elseif($this->tipo == 2) $ret = "Telefonia Fixa";
			elseif($this->tipo == 3) $ret = "Gás";
			else $ret = "NDA";
			return $ret;
		 
		}		
		
		/*
		 * Método para retornar Unidade de Medida de consumo
		 */
		 
		function getTipoMedida(){
		
			if($this->tipo == 0) $ret = "m³";
			elseif($this->tipo == 1) $ret = "KWh";
			elseif($this->tipo == 2) $ret = "Min";
			elseif($this->tipo == 3) $ret = "m³";
			else $ret = "NDA";
			return $ret;
		 
		}

		/*
		 * Método para verificar se UC tem consumo
		 */
		 
		function hasConsumo(){
			
			$ret = false;
			switch($this->tipo){
			
				case 0:
				case 1:
				case 3:
					$ret = true;
					break;
				default:
					$ret = false;
					break;
			
			}
			return $ret;
		
		}
		
		/*
		 * Método para retornar Nome da Cidade
		 */
		 
		function getCidadeNome(){
		 
			return $this->cidade->get('nome');
		 
		}
		
		/*
		 * Método para retornar Endereco
		 */
		 
		function getEndereco(){
		 
			if($this->tipo != 3){
				$numero	= ($this->numero == 0) ? "" : " n°".$this->numero;
				$ret = $this->endereco.$numero;
			}else 
				$ret = $this->endereco;
			
			return $ret;
		 
		}		

		/*
		 * Método para retornar Valores
		 */
		 
		function get($param){
		 
			return $this->$param;
		 
		}		
		
		/*
		 * Método para setar todas as notas
		 * @param int ANO
		 */
		
		public function setNotas($ano=null){
		
			if(isset($ano))
				$sql = "SELECT id FROM notas WHERE ano_ref = $ano AND uc = ".$this->id;
			else
				$sql = "SELECT id FROM notas WHERE uc = ".$this->id;
			$query = mysql_query($sql);
			
			while($res = mysql_fetch_array($query)){
			
				if(!isset($ano))	$this->notas[] = new Nota($re['id']);
				else 				$this->anoNotas[] = new Nota($re['id']);
			
			}
		
		}
		
		/*
		 * Método para somar todas as notas
		 */
		
		public function somaValorNotas($ano=null){
		
			if(isset($ano))
				$sql = "SELECT SUM(valor), SUM(consumo) FROM daee_notas WHERE tipo = 0 AND ano_ref = $ano AND uc = ".$this->id;
			else
				$sql = "SELECT SUM(valor), SUM(consumo) FROM daee_notas WHERE tipo = 0 AND uc = ".$this->id;
			$query = mysql_query($sql);
			
			return mysql_fetch_array($query);
		
		}
		
		/*
		 * Método para somar todas as notas
		 */
		
		public function getExercicioMensal($ano){
		
			$sql = "SELECT mes_ref, consumo, valor FROM daee_notas WHERE tipo = 0 AND ano_ref = $ano AND uc = " . $this->id . " ORDER BY mes_ref";
			$query = mysql_query($sql);
			
			$ref="";
			$ret;
			$i = 0;
			$ret = array();
			while($res = mysql_fetch_array($query)){
			
				if($ref == $res['mes_ref']){
				
					$ref	= $res['mes_ref'];
					$ret[$i]['consumo']	+= $res['consumo'];
					$ret[$i]['valor']	+= $res['valor'];
					
				}else{
				
					$ref	= $res['mes_ref'];
					$i++;
					$ret[$i] = $res;
				
				}
			
			}
			return $ret;
			
		}		
		
		/* 
		 * Método para pegar apenas UCs do mesmo tipo
		 */
		
		public function getMesmoTipo(){
		
			$sql = "SELECT id FROM daee_uddc WHERE tipo = ".$this->tipo;
			$query = mysql_query($sql);
			
			while($res = mysql_fetch_array($query)){
			
				$ret[] = $res['id'];
			
			}
			
			return $ret;
		
		}
		
		/* 
		 * Método para pegar apenas UCs do mesmo tipo por Unidade Operacional
		 */
		
		public function getMesmoTipoPorUO($uoId){
		
			$sql = "SELECT id FROM daee_uddc WHERE ativo = 1 AND tipo = ".$this->tipo ." AND uo = $uoId ORDER BY rgi";
			$query = mysql_query($sql);
			
			while($res = mysql_fetch_array($query)){
			
				$ret[] = $res['id'];
			
			}
			
			return $ret;
		
		}
		
		/* 
		 * Método para pegar todas as notas
		 */
		
		public function getAllNotas($all=true,$empenho=null){
		
			$sql;
			if($all){
			
				$sql = "SELECT id FROM daee_notas WHERE uc =".$this->id . " ORDER BY emissao ASC";
				
			}elseif(!$all){
			
				if(!isset($empenho)) $sql = "SELECT id FROM daee_notas WHERE tipo = 0 AND uc =".$this->id . " ORDER BY emissao ASC";
				elseif($empenho) $sql = "SELECT id FROM daee_notas WHERE tipo = 1 AND uc =".$this->id . " ORDER BY emissao ASC";
				else $sql = "SELECT id FROM daee_notas WHERE tipo = 2 AND uc =".$this->id . " ORDER BY emissao ASC";
			
			}
			
			$query = mysql_query($sql);
			
			if(mysql_num_rows($query) > 0){
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res;
				
				}
			} else{
				
				$ret[]['id'] = null;
			
			}
			
			return $ret;
		
		}

		
		/* 
		 * Método para pegar todas as notas
		 * @return boolean
		 */		
		public function isNotaRegistered($data_ref){
		
			$data = explode("-",$data_ref);
			$mes_ref = $data[0];
			$ano_ref = $data[1];
			
			$sql = "SELECT SUM(valor) FROM daee_notas WHERE mes_ref = $mes_ref AND ano_ref = $ano_ref AND uc=". $this->id;
			$query = mysql_query($sql);
			
			if(mysql_num_rows($query) == 1){
			
				$res = mysql_fetch_array($query);
				$ret = ($res['SUM(valor)'] == 0) ? -1: $res['SUM(valor)'];
			
			}else{
			
				$ret = -1;
			
			}
			return $ret;
		
		}
		
		/* 
		 * Método para pegar se é UC ativa ou não
		 */
		public function isAtivo(){
		
			return ($this->ativo == 1) ? true : false;
		
		}
		
		public function getAtivoText(){
		
			return ($this->isAtivo()) ? '<font color="green">Ativo</font>' : '<font color="red">Inativo</font>';
		
		}
		
		/* PARA APAGAR
		 * Método para pegar a variações com o mês anterior
		 */
		public function getVariacaoDoMes($mes=null,$ano =null){
		
			$mes = ($mes!=null) ? $mes : Date('n');
			$ano = ($ano!=null) ? $ano : Date('Y');
			$mes_ant = ($mes > 1) ? $mes-1 : 12;
			$ano_ant = ($mes > 1) ? $ano : $ano-1;
			
			if($mes > 1){
			
				$sql = "SELECT consumo, valor FROM daee_notas WHERE uc = ". $this->id ." AND mes_ref <= $mes AND ano_ref = $ano AND mes_ref >= $mes_ant AND ano_ref >= $ano_ant";
			
			}else{
			
				$sql = "SELECT consumo, valor FROM daee_notas WHERE uc = ". $this->id ." AND mes_ref >= $mes AND ano_ref <= $ano AND mes_ref <= $mes_ant AND ano_ref >= $ano_ant";
			
			}
			
			return $sql;
		
		} 
		
		
		
		
	}
	 
	 
	 
?>