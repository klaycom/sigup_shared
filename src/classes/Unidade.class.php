<?php 
	/*
	 * Classe de Unidades Operacionais do DAEE
	 * 
	 */
	 include_once "src/classes/Cidades.class.php";
	 
	 Class Unidade{
	 
		private $id;		// int
		private $endereco;	// string
		private $numero;	// int
		private $udd;		// string (SIGLA)
		private $nome;		// Nome da Unidade
		private $cidade;	// int (sys_cidade foreign key)
		private $lat;		// String (decimal)
		private $long;		// String (decimal)
		private $bacia;		// String (SIGLA BACIA)
		private $telefone;	// int (Telefone para contato)
		
		public $teste;		// PARA FINALIDADE DE TESTES
		
		/*
		 * Construtor de classe
		 *
		 * Caso forneça todos os argumentos, criar unidade no banco de dados,
		 * Caso contrário, apenas retornar valores
		 *
		 */
		
		function __construct($id, $nome=null, $end=null, $num=null, $udd=null, $cidade=null, $lat=null, $long=null){ 
		
			if(isset($end)){
			
				$end		= strtoupper(mysql_real_escape_string($end));
				$nome		= mysql_real_escape_string($nome);
				$num		= (int) $num;
				$udd		= strtoupper(mysql_real_escape_string($udd));
				$cidade		= (int) $cidade;
				
				if($this->verificaExiste($end,$num)){
					
					$this->showDebug("Já existe uma unidade com o Endereço: $end e com o número: $num");
				
				}else{
				
					$sql = "INSERT INTO daee_udds VALUES ('', '$nome', '$end', '$num', '$udd', $cidade, '$lat', '$long')";
					$query = mysql_query($sql);
					
				}
				
			}else{
			
				$this->id = $id;
				$sql   = "SELECT c.nome,c.bacia,u.endereco,u.numero,u.unidade,u.latitude,u.longitude, u.nome AS unome FROM sys_cidade c, daee_udds u ";
				$sql  .= "WHERE c.id = u.cidade AND u.id = $id";
				$query = mysql_query($sql);
				
				if(mysql_num_rows($query) == 1){
				
					$result   = mysql_fetch_array($query);
					$this->endereco = $result['endereco'];
					$this->numero   = $result['numero'];
					$this->udd		= $result['unidade'];
					$this->cidade	= $result['nome'];
					$this->lat		= $result['latitude'];
					$this->long		= $result['longitude'];
					$this->bacia	= $result['bacia'];
					$this->nome		= $result['unome'];
				}
				
			}
			
		}
		
		/*
		 * Método para verificar se já existe localidade
		 * @return Boolean
		 */
		 
		function verificaExiste($end,$num){
		
			$sql = "SELECT * FROM daee_udds WHERE endereco = '$end' AND numero = $num";
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
		 * Método para retornar SIGLA da Unidade
		 */
		 
		function getSigla(){
		 
			return $this->udd;
		 
		}
		
		/*
		 * Método para retornar Endereco
		 */
		 
		function getEndereco(){
		 
			$numero	= ($this->numero != "") ? " n°".$this->numero : "";
			return $this->endereco.$numero;
		 
		}		

		/*
		 * Método para retornar SIGLA da Unidade
		 */
		 
		function get($param){
		 
			return $this->$param;
		 
		}

		/*
		 * Método para buscar soma de todas as notas cujo UC faz parte de UO
		 */
		 
		public function getTotalPorTipo($tipo,$ano=0){
		
			if($ano == 0){
				$sql = "SELECT SUM(n.valor) FROM daee_uddc c, daee_notas n WHERE c.uo = " . $this->id . " AND n.uc = c.id AND c.tipo=$tipo AND n.tipo=0 AND n.ano_ref = " . Date('Y');
			}elseif($ano >=2012 && $ano <= Date('Y')){
				$sql = "SELECT SUM(n.valor) FROM daee_uddc c, daee_notas n WHERE c.uo = " . $this->id . " AND n.uc = c.id AND c.tipo=$tipo AND n.tipo=0 AND n.ano_ref = " . $ano;			
			}
			$query = mysql_query($sql);
			$ret = mysql_fetch_array($query);
			return $ret['SUM(n.valor)'];
		
		}
		
		public function getTotalTipoMes($tipo,$mes,$ano){
		
			if($ano == 0){
				$sql = "SELECT SUM(n.valor) FROM daee_uddc c, daee_notas n WHERE c.uo = " . $this->id . " AND n.uc = c.id AND c.tipo=$tipo AND n.tipo=0 AND n.ano_ref = " . Date('Y') . " AND n.mes_ref = $mes";
			}elseif($ano >=2012 && $ano <= Date('Y')){
				$sql = "SELECT SUM(n.valor) FROM daee_uddc c, daee_notas n WHERE c.uo = " . $this->id . " AND n.uc = c.id AND c.tipo=$tipo AND n.tipo=0 AND n.ano_ref = " . $ano . " AND n.mes_ref = $mes";			
			}
			
			$query = mysql_query($sql);
			if($query)
				$ret = mysql_fetch_array($query);
			else
				$ret['SUM(n.valor)'] = 0;
			return $ret['SUM(n.valor)'];
		
		}		
		
		/*
		 * Método para retornar quantidade de UCs Vinculadas
		 */
		
		public function getQtdUc(){
		
			$sql = "SELECT id FROM daee_uddc WHERE uo = ".$this->id;
			$query = mysql_query($sql);
			return mysql_num_rows($query);
		
		}
		
		public function getQtdUcStatus($ativo = 1){
		
			$sql = "SELECT id FROM daee_uddc WHERE uo = " . $this->id . " AND ativo = " . $ativo;
			$query = mysql_query($sql);
			return mysql_num_rows($query);
		
		}		
		
		/*
		 * Funções Teste
		 * @return String ou
		 * @void echo String
		 */
		
		function getTeste(){
		
			return $this->teste;
			
		}
		
		function showDebug($msg){
		
			echo $msg;
		
		}
		
		/*
		 * Método para retornar tipos de serviços
		 */		
		public function getTiposServicos(){
		
			$sql = "SELECT DISTINCT(tipo) FROM daee_uddc WHERE uo=".$this->id;
			$query = mysql_query($sql);
			
			if(mysql_num_rows($query) > 0){
			
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res['tipo'];
				
				}
			
			}else{
			
				$ret = null;
			
			}
			return $ret;
		
		}

		/*
		 * Método para retornar UCs de acordo com o TIPO fornecido
		 */		
		public function getUcPorTipo($tipo){
		
			$sql = "SELECT id FROM daee_uddc WHERE uo=". $this->id . " AND tipo=".$tipo;
			$query = mysql_query($sql);
			
			if(mysql_num_rows($query) > 0){
			
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res['id'];
				
				}
			
			}else{
			
				$ret = null;
			
			}
			return $ret;
		
		}
		
		/*
		 * Método para retornar todas as UCs vinculadas Orednando por Tipo
		 */				
		public function getAllUcs(){
		
			$sql = "SELECT id FROM daee_uddc WHERE uo=". $this->id . " ORDER BY tipo";
			$query = mysql_query($sql);
			
			if(mysql_num_rows($query) > 0){
			
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res['id'];
				
				}
			
			}else{
			
				$ret = null;
			
			}
			return $ret;
		
		}

		/*
		 * Método para retornar todas as UCs vinculadas Orednando por Tipo
		 */	
		public function getUcSomaByType($type, $ano=2012){
		
			$sql = "SELECT c.id,c.rgi,c.compl,c.rua,c.tipo,SUM(n.valor),SUM(n.consumo),n.mes_ref,c.ativo ";
			$sql.= "FROM daee_uddc c, daee_notas n ";
			$sql.= "WHERE c.id=n.uc AND n.ano_ref = $ano AND c.tipo = $type AND c.uo=" . $this->id . " ";
			$sql.= "GROUP BY c.rgi ORDER BY c.id ASC";
			$query = mysql_query($sql);
			
			if(mysql_num_rows($query) > 0){
			
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res;
				
				}
			
			}else{
			
				$ret = null;
			
			}
			return $ret;
		
		}	
		 
	 }
	 
	 
	 
?>