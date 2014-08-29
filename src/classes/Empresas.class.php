
<?php 
	include_once "Cidades.class.php";
	
	/*
	 * Classe de Empresas Prestadoras de serviços ao DAEE
	 * @retrieving data from sys_empresas of DB
	 */
	 
	 Class Empresa{
	 
		private $id;			// int
		private $nome;			// string (nome da empresa)
		private $cnpj;			// int (ID do Estado) - Constante SP = 26
		private $contato;		// String (Telefone)
		private $representante;	// String (Nome do Representante se houver)
		private $servico;		// int (ID do serviço em XML)
		
		public $teste;			// PARA FINALIDADE DE TESTES
		
		/*
		 * Construtor de classe
		 * @param int - ID da Empresa ou NULO
		 *
		 */
		
		function __construct($id=null, $cnpj=null){ 
		
			if(isset($cnpj) && $this->isCnpjValid($cnpj)){
				
				$sql = "SELECT * FROM sys_empresas WHERE cnpj = $cnpj";
				$query = mysql_query($sql);
				
				if(mysql_num_rows($query) == 1){
					
					$res = mysql_fetch_array($query);
					$this->id		= $id;
					$this->nome 	= $res['nome'];
					$this->cnpj		= $res['cnpj'];
					$this->contato	= $res['contato'];
					$this->representante = $res['representante'];
					//$this->servico	= $res['servico']; //pegar nome de serviço em XML
				
				}
			
			}else if(isset($id)){
			
				$sql = "SELECT * FROM sys_empresas WHERE id = $id";
				$query = mysql_query($sql);
				
				if(mysql_num_rows($query) == 1){
					
					$res = mysql_fetch_array($query);
					$this->id		= $id;
					$this->nome 	= $res['nome'];
					$this->cnpj		= $res['cnpj'];
					$this->contato	= $res['contato'];
					$this->representante = $res['representante'];
					//$this->servico	= $res['servico']; //pegar nome de serviço em XML
				
				}
				
			}
			
		}
		
		/*
		 * Método para retornar array de empresas
		 * @return Array ['id'],['cnpj'],['nome'],['contato'] etc
		 *
		*/
		
		function getEmpresas(){
			
			$sql = "SELECT * FROM sys_empresas ORDER BY nome ASC";
			$query = mysql_query($sql);
			while($a = mysql_fetch_array($query)){
				$ret[] = $a;
			}
			return $ret;							
		
		}
		
		/*
		 * Método para buscar Serviços em XML
		 * @return Array ['id'],['nome'],['descricao']
		 */
		
		function getServicos(){
			$xml = simplexml_load_file("src/data/Servicos.xml");
			foreach($xml->servico as $servico){
			
				$ret[] = $servico;
				
			}
			return $ret;
			
		}
		
		/*
		 * Método para adicionar chaves ao XML
		 * @param $nome STRING
		 * @param $descricao 
		 * @return Debug Message
		 */
		 
		function addServico($nome, $descricao){
		 
			$nome		= utf8_decode($nome);
			$descricao	= utf8_decode($descricao);
			$arquivo = 'src/data/Servicos.xml';
			if(is_writable($arquivo)) {
			
				$manipular = fopen("$arquivo", "r+");

				if(!$manipular) {
				
					return "Não foi possível abrir arquivo";
					
				}else{
					
					//$conteudo = 
					$conteudo = file_get_contents($arquivo);
					$mudanca  = "<servico>\n<nome>$nome</nome>\n<descricao>$descricao</descricao>\n</servico>\n</servicos>";
					$conteudo = str_replace("</servicos>", $mudanca, $conteudo);
					
					if(!fwrite($manipular, $conteudo))
						return "Não foi possível editar conteúdo do XML";
					else
						return "XML Editado com sucesso!";
					fclose($manipular);
					
				}	
				
			} else {
			
				return "O $arquivo não tem permissões de leitura e/ou escrita.";
				
			}		 
		 }
		
		/* 
		 * Método para adicionar Empresa no Banco de Dados
		 * @return int
		 */
		 
		function addEmpresa($nome,$cnpj,$desc,$repre,$contato){
		
			$nome	= strtoupper(mysql_real_escape_string($nome));
			$cnpj	= mysql_real_escape_string($cnpj);
			$desc	= mysql_real_escape_string($desc);
			$contato= mysql_real_escape_string($contato);
			$repre	= mysql_real_escape_string($repre);
			if($nome != ""){
				if($this->isCnpjValid($cnpj)){
					
					$sql	= "INSERT INTO sys_empresas VALUES('','$cnpj','$nome','$contato','$desc','$repre',NOW())";
					if(mysql_query($sql))
						return mysql_insert_id();
						
					else
						return 0;
						
				}else
					return -1;
					
			} else
				return -2;
		
		}
		
		/*
		 * Método para validação de CNPJ fornecido
		 * @param String (18 digitos)
		 * @return Boolean
		 */
		 
		function isCnpjValid($cnpj){
			//Etapa 1: Cria um array com apenas os digitos numéricos, isso permite receber o cnpj em diferentes formatos como "00.000.000/0000-00", "00000000000000", "00 000 000 0000 00" etc...
			$j=0;
			for($i=0; $i<(strlen($cnpj)); $i++)
				{
					if(is_numeric($cnpj[$i]))
						{
							$num[$j]=$cnpj[$i];
							$j++;
						}
				}
			//Etapa 2: Conta os dígitos, um Cnpj válido possui 14 dígitos numéricos.
			if(count($num)!=14)
				{
					$isCnpjValid=false;
				}
			//Etapa 3: O número 00000000000 embora não seja um cnpj real resultaria um cnpj válido após o calculo dos dígitos verificares e por isso precisa ser filtradas nesta etapa.
			if ($num[0]==0 && $num[1]==0 && $num[2]==0 && $num[3]==0 && $num[4]==0 && $num[5]==0 && $num[6]==0 && $num[7]==0 && $num[8]==0 && $num[9]==0 && $num[10]==0 && $num[11]==0)
				{
					$isCnpjValid=false;
				}
			//Etapa 4: Calcula e compara o primeiro dígito verificador.
			else
				{
					$j=5;
					for($i=0; $i<4; $i++)
						{
							$multiplica[$i]=$num[$i]*$j;
							$j--;
						}
					$soma = array_sum($multiplica);
					$j=9;
					for($i=4; $i<12; $i++)
						{
							$multiplica[$i]=$num[$i]*$j;
							$j--;
						}
					$soma = array_sum($multiplica);	
					$resto = $soma%11;			
					if($resto<2)
						{
							$dg=0;
						}
					else
						{
							$dg=11-$resto;
						}
					if($dg!=$num[12])
						{
							$isCnpjValid=false;
						} 
				}
			//Etapa 5: Calcula e compara o segundo dígito verificador.
			if(!isset($isCnpjValid))
				{
					$j=6;
					for($i=0; $i<5; $i++)
						{
							$multiplica[$i]=$num[$i]*$j;
							$j--;
						}
					$soma = array_sum($multiplica);
					$j=9;
					for($i=5; $i<13; $i++)
						{
							$multiplica[$i]=$num[$i]*$j;
							$j--;
						}
					$soma = array_sum($multiplica);	
					$resto = $soma%11;			
					if($resto<2)
						{
							$dg=0;
						}
					else
						{
							$dg=11-$resto;
						}
					if($dg!=$num[13])
						{
							$isCnpjValid=false;
						}
					else
						{
							$isCnpjValid=true;
						}
				}
			//Trecho usado para depurar erros.
			/*
			if($isCnpjValid==true)
				{
					echo "<p><font color="GREEN">Cnpj é Válido</font></p>";
				}
			if($isCnpjValid==false)
				{
					echo "<p><font color="RED">Cnpj Inválido</font></p>";
				}
			*/
			//Etapa 6: Retorna o Resultado em um valor booleano.
			return $isCnpjValid;			
		}

		
		/*
		 * Método para retornar String CNPJ
		 * @param $int com 14 chars
		 * @return String
		 */
		
		function transformCnpj($int){
		
			$array = $int;
			$ret = $array[0].$array[1].".".$array[2].$array[3].$array[4].".".$array[5].$array[6].$array[7]."/".$array[8].$array[9].$array[10].$array[11]."-".$array[12].$array[13];
			return $ret;
			
		}
		
		/*
		 * Método para retornar nome da Empresa
		 * @return String Nome
		 */
		
		function getNome(){
		
			return $this->nome;
		
		}
		
		/*
		 * Método para retornar uma variáveis
		 */
		
		function get($param){
		
			return $this->$param;
		
		}		
		
		
		function showDebug($msg){
		
			echo $msg;
		
		}
		
	 
	 }
	 
	 
?>