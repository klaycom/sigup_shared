<?php 
	/*
	 * Classe de Cidades e suas bacias do DAEE
	 * @retrieving data from sys_cidade of DB
	 */
	 
	 Class Cidade{
	 
		private $id;		// int
		private $nome;		// string (nome da cidade)
		private $estado=26;	// int (ID do Estado) - Constante SP = 26
		private $bacia;		// String (Sigla)  - Constante 2~9
		private $bNome;		// String (Nome da Bacia)
		private $location;	// String (Vetor de linhas em KML Google Maps) - Not implemented yet
		
		public $teste;		// PARA FINALIDADE DE TESTES
		
		/*
		 * Construtor de classe
		 * @param int - IDs Fixos 4706~5350
		 *
		 */
		
		function __construct($id=null){ 
		
			if(isset($id) && $id >= 4706 && $id <= 5350){
				
				$this->id = $id;
				$sql = "SELECT c.*,b.nome AS bnome, b.sigla FROM sys_cidade c, sys_bacias b WHERE c.bacia = b.id AND c.id = $id";
				$query = mysql_query($sql);
				
				if(mysql_num_rows($query) == 1){
					
					$res = mysql_fetch_array($query);
					$this->nome 	= $res['nome'];
					$this->bacia	= $res['sigla'];
					$this->bNome	= $res['bnome'];
					$this->location = $res['location'];
				
				}
			
			}
			
		}
		
		/*
		 * Método para retornar array de cidades
		 * @param int $bacia (ID da Bacia)
		 * @return Array ['id'],['nome'],['bnome'],['sigla'] etc
		 *
		*/
		
		function getCidades($bacia=null){
		
			if(isset($bacia) && $bacia >= 2 && $bacia <= 9){
			
				$sql = "SELECT c.*,b.nome AS bnome, b.sigla FROM sys_cidade c, sys_bacias b WHERE c.bacia = b.id AND c.bacia = $bacia ORDER BY c.nome ASC";
				$query = mysql_query($sql);
				while($a = mysql_fetch_array($query)){
					$ret[] = $a;
				}
				return $ret;
			
			}else{
			
				$sql = "SELECT c.*,b.nome AS bnome, b.sigla FROM sys_cidade c, sys_bacias b WHERE c.bacia = b.id ORDER BY c.nome ASC";
				$query = mysql_query($sql);
				while($a = mysql_fetch_array($query)){
					$ret[] = $a;
				}
				return $ret;				
			
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
		 * Funções Teste
		 * @return String ou
		 * @void echo String
		 */
		
		function getTeste(){
		
			return $this->teste;
			
		}
		
		/*
		 * Método para retornar SIGLA da Unidade
		 */
		 
		function get($param){
		 
			return $this->$param;
		 
		}		
	 
	 }
	 
	 
?>