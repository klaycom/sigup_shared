<?php 
	/*
	 * Classe de Usuários
	 * 
	 */
	 
	Class User{
	
		private $id;		// Id no Sistema
		private $login;		// Nome de Usuário
		private $senha;		// Senha
		private $nome;		// Nome do Funcionário
		private $pront;		// Prontuário do Funcionário
		private $nivel;		// Nivel de permissão no sistema
		private $lastlogin;	// Último Login do Usuário
		private $criado_em;	// Data de Criação do Usuário
		private $ativo;		// Se = 1 Ativo se = 0 Inativo
		private $valid;		// Boolean Usuário Válido
		private $bacia;
		
		function __construct($login,$senha=null,$nome=null,$pront=null,$nivel=null,$cargo=null,$id=null,$bacia=0){
		
			if(isset($login,$senha,$nome,$pront,$nivel,$cargo,$bacia)){ // Cadastrar
			
				$login	= mysql_real_escape_string($login);
				$senha	= sha1(mysql_real_escape_string($senha));
				$nome	= mysql_real_escape_string($nome);
				$pront	= (int) $pront;
				$nivel	= (int) $nivel;
				$cargo	= mysql_real_escape_string($cargo);
				
				$sql	= "INSERT INTO sys_users VALUES('','$login','$senha','$nome','$cargo',$pront,$nivel,$bacia,'',NOW(),1)";
				$query	= mysql_query($sql);
				
				$this->id		= mysql_insert_id();
				$this->login	= $login;
				$this->senha	= $senha;
				$this->nome		= $nome;
				$this->pront	= $pront;
				$this->nivel	= $nivel;
				$this->ativo	= 1;
			
			}elseif(isset($login,$senha)){
			
				$query = $this->userQuery($login,$senha);
				if(mysql_num_rows($query) == 1){
					
					$user = mysql_fetch_array($query);
					
					$this->id		= $user['id'];
					$this->login	= $user['login'];
					$this->senha	= $user['senha'];
					$this->nome		= $user['nome'];
					$this->pront	= $user['pront'];
					$this->nivel	= $user['nivel'];
					$this->lastlogin= $user['lastlogin'];
					$this->criado_em= $user['criado'];
					$this->bacia	= $user['bacia'];
					$this->valid	= true;
					$this->updateLastLogin();
				
				}else
				
					$this->valid = false;
			
			}elseif(isset($login) && !isset($id)){
			
				$query = $this->userQuery($login,$senha);
				if(mysql_num_rows($query) == 1){
				
					$user = mysql_fetch_array($query);
					
					$this->id		= $user['id'];
					$this->login	= $user['login'];
					$this->senha	= $user['senha'];
					$this->nome		= $user['nome'];
					$this->pront	= $user['pront'];
					$this->nivel	= $user['nivel'];
					$this->lastlogin= $user['lastlogin'];
					$this->criado_em= $user['criado'];
					$this->bacia	= $user['bacia'];
					$this->valid	= true;
					$this->updateLastLogin();
				
				}else
				
					$this->valid = false;			
			
			}else{
			
				$sql 	= "SELECT * FROM sys_users WHERE id = $id";
				$query = mysql_query($sql);
			
				$user = mysql_fetch_array($query);
					
				$this->id		= $user['id'];
				$this->login	= $user['login'];
				$this->senha	= $user['senha'];
				$this->nome		= $user['nome'];
				$this->pront	= $user['pront'];
				$this->nivel	= $user['nivel'];
				$this->lastlogin= $user['lastlogin'];
				$this->criado_em= $user['criado'];
				$this->bacia	= $user['bacia'];
				$this->valid	= true;				
			
			}
		
		}
		
		/*
		 * Método para retornar Query
		 * @param $login e $senha
		 * @return php mysql_query
		 */
		
		function userQuery($login,$senha=null){
		
			$login = mysql_real_escape_string($login);
			if(isset($senha)){
			
				$senha = sha1(mysql_real_escape_string($senha));
				$sql   = "SELECT * FROM sys_users WHERE ativo = 1 AND login = '$login' AND senha = '$senha'";
				return mysql_query($sql);
			
			}else {
			
				$senha = sha1(mysql_real_escape_string($senha));
				$sql   = "SELECT * FROM sys_users WHERE ativo = 1 AND login = '$login'";
				return mysql_query($sql);			
			
			}
		
		}
		
		/*
		 * Método para atualizar LastLogin
		 */
		 
		function updateLastLogin(){
		
			$sql = "UPDATE sys_users SET lastlogin = NOW() WHERE id=".$this->id;
			mysql_query($sql);
		
		}
		
		/*
		 * Método para retornar Usuário válido
		 * @return Boolean
		 */
		
		function isValidUser(){
		
			return $this->valid;
		
		}
		
		/*
		 * Método para Iniciar Sessão
		 * @return Boolean
		 */
		
		function startSession(){
		
			session_start();
			$_SESSION['usuario']	= $this->login;
			$_SESSION['nivel']		= $this->nivel;
			$_SESSION['lastAccess']	= date("Y-n-j H:i:s");
			$_SESSION['bacia']		= $this->bacia;

			
		}

		/*
		 * Método para retornar variáveis
		 */
		
		function get($param){
		
			return $this->$param;
		
		}			
		
		/*
		 * Método para redirecionar para página correta
		 */
		
		public function gotoRightPage(){
		
			switch($this->nivel){
			
				case 0: $loc = "resumo_index"; break;
				case 1: $loc = "udd_relatorioUO"; break;
				case 2:
				case 3:
				case 4:
					$loc = "ope_index"; break;
				case 100: $loc = "dof_marcarPago"; break;
				default: $loc = "deny"; break;
			
			}
			
			header("Location: $loc.php");
		
		}
		
		/*
		 * Método para pegar todas os contratos com os quais fazem parte da permissão deste usuário
		 */
		 
		public function getContratos(){
		
			$sql = ($this->nivel == 1) ? "SELECT id FROM daee_contratos" : "SELECT id FROM daee_contratos WHERE permissao=".$this->nivel;
			$query = mysql_query($sql);
			
			if(mysql_num_rows($query) > 0){
			
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res;
				
				}
			
			}else {
			
				$ret[]['id'] = null;
			
			}
			
			return $ret;
		
		}
		
		/*
		 * Método para inserir histórico de alterações
		 */
		public function colocarNoHistorico($tipo, $identificador, $fk, $campo, $valor, $motivo){
		
			switch($tipo){
			
				case "Editar Nota";
				
					$obs = "editou o campo ". $campo ." da nota de número ". $identificador . " para " . $valor;
					$link = "ope_editarNotas.php?chave=". sha1($fk);
				
				break;
			
			}
			
			$user = $this->id;
			$sql = "INSERT INTO sys_history VALUES('', $user, '$tipo', '$link', '$motivo', '$obs', NOW())";
			mysql_query($sql);
			
		
		} 
		
	
	}
?>