<?php 
	/*
	 * Classe de Contratos e Autos do DAEE
	 * 
	 */
	 
	include_once "Empresas.class.php";
	include_once "Unidade.class.php";
	 
	Class Contrato{
	 
		private $id;		// int
		private $tipo_pasta;// 0 = Autos, 1 = Processo
		private $numero;	// Número de Autos ou Processo
		private $volume;	// int
		private $nome;		// Nome do Autos/Processo
		private $empresa;	// Object Empresa()
		private $servico;	// Classe de Serviço em Serviços.xml
		private $unidade;	// Object Unidade()
		private $contrato;	// String Contrato
		private $comlicit;	// 1 = com / 0 = sem - Com Licitação?
		private $data;		// Data de Início
		private $vigencia;	// Data de Vigência
		private $criado;	// Datetime do cadastro no BD
		private $criador;	// Object:: Usuário criador
		private $pastas = Array(0=>'Autos', 1=>'Processo');
		private $permissao;
		
		public $teste;
		
		
		/*
		 * Construtor de classe
		 *
		 */
		
		function __construct($id,$tipo_pasta=null,$numero=null,$volume=null,$nome=null,$empresaId=null,$servico=null,$unidadeId=null,$contrato=null,$comlicit=null,$data=null,$vigencia=null,$userid=null,$permissao=0){ 
		
			if($id != ""){
			
				$this->id	= (int) $id;
				$sql 		= "SELECT * FROM daee_contratos WHERE id = $id";
				$query		= mysql_query($sql);
				
				if(mysql_num_rows($query) == 1){
				
					$res				= mysql_fetch_array($query);
					$this->tipo_pasta 	= $res['pasta'];
					$this->numero		= $res['numero'];
					$this->volume		= $res['volume'];
					$this->nome			= $res['nome'];
					$this->empresa		= new Empresa($res['empresa']);
					$this->servico		= $res['servico'];
					$this->unidade		= new Unidade($res['unidade']);
					$this->contrato		= $res['contrato'];
					$this->comlicit		= $res['comlicit'];
					$this->data			= $res['data'];
					$this->vigencia		= $res['vigencia'];
					$this->criado		= $res['criado'];
					$this->permissao	= $res['permissao'];
					
				}
				
			}else if(isset($tipo_pasta,$numero,$volume,$nome,$empresaId,$servico,$unidadeId,$contrato,$comlicit,$data,$vigencia,$userid,$permissao)){
			
				$tipo_pasta = ( (int)$tipo_pasta > 0) ? 1 : 0;
				$numero		= mysql_real_escape_string($numero);
				$volume		= (int) $volume;
				$nome		= mysql_real_escape_string($nome);
				$empresaId	= (int) $empresaId;
				$servico	= (int) $servico;
				$unidadeId	= (int) $unidadeId;
				$contrato	= mysql_real_escape_string($contrato);
				$comlicit	= (int) $comlicit;
				$permissao 	= (int) $permissao;
				
				$sql = "INSERT INTO daee_contratos VALUES('',$tipo_pasta,'$numero',$volume,'$nome',$empresaId,$servico,$unidadeId,'$contrato',$comlicit,'$data','$vigencia',NOW(),1,$userid,$permissao)";
				$query = mysql_query($sql);
				
				$this->teste = $sql;
				
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
		 * Método para verificar se é Autos ou Processo
		 * @return boolean
		 */
		
		function isAutos(){
		
			if($this->tipo_pasta == 0) return true;
			else return false;
		
		}
		
		/*
		 * Método para retornar uma variáveis
		 */
		
		function get($param){
		
			return $this->$param;
		
		}
		
		/*
		 * Método para retornar uma variáveis
		 */
		
		function geraNome(){
		
			$autos	= $this->isAutos() ? "Autos " : "Processo ";
			$num	= $this->isAutos() ? number_format($this->get('numero'),0,',','.') : $this->get('numero');
			$nome	= $this->get('nome');
			//$servico= $this->get('empresa')->getServicos()[$this->get('servico')]->children();
			return	$autos." n°".$num." - ".$nome." ".$this->empresa->getNome();			
		
		}

		/*
		 * Método para retornar RGI de notas que estão faltando
		 */
		 
		public function getFaltaNotas($mes, $ano){
		
			$sql = "SELECT A.id AS ucid, A.rgi AS rgi, CONCAT(A.rua,' ', A.compl) AS endereco, A.numero, A.tipo, A.uo, A.vencto FROM daee_uddc A "; 
			$sql.= "LEFT JOIN daee_notas B ON A.id = B.uc AND B.mes_ref = $mes AND B.ano_ref = $ano ";
			$sql.= "WHERE B.uc IS NULL AND A.ativo = 1 AND A.contrato=". $this->id ;
			$query = mysql_query($sql);
			if(mysql_num_rows($query) > 0){
			
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res;
				
				}
			
			}else{
			
				$ret[]['ucid']	= null;
				$ret[]['rgi']	= null;
			
			}
		
			return $ret;
		}

		/*
		 * Método para retornar RGI ativas
		 */		
		public function getAllUcs($ativo = false){
		
			$sql = "SELECT id FROM daee_uddc WHERE contrato =" .$this->id;
			if($ativo) $sql.=" AND ativo = 1";
			$query = mysql_query($sql);
			if(mysql_num_rows($query) > 0){
			
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res;
				
				}
			
			}else{
			
				$ret[]['id']	= null;
			
			}
		
			return $ret;			
		
		}
		
		/*
		 * Método para retornar todas as notas de UCs no ultimos meses
		 */
		public function getAllNotasPorPeriodo($numMeses, $tudo=true, $pagos=false){
		
			if($numMeses < 0) $numMeses = 1;
			elseif($numMeses >12) $numMeses = 12;

			$mes_ini	= ($numMeses >= Date('n')) ? 12 - ($numMeses - Date('n')) : Date('n') - $numMeses;
			$ano_ini	= ($numMeses >= Date('n')) ? Date('Y') - 1 : Date('Y');
			$limite		= Date('Y-m-d');
			$data_ini	= $ano_ini . "-" . $mes_ini . "-01";
			
			$pagoSql = (!$tudo) ? "AND ": "";
			$pagoSql.= ($pagos && !$tudo) ? "n.pagto <> '0000-00-00'" : "";
			$pagoSql.= (!$pagos && !$tudo) ? "n.pagto = '0000-00-00'" : "";
			
			$sql 	= "SELECT n.id,n.mes_ref,n.ano_ref,n.numero,o.unidade,c.rgi,c.compl,e.nome,n.consumo,n.valor,n.emissao,n.criado,n.saida,";
			$sql   .= "n.provisoria,n.pagto,u.login FROM daee_notas n, daee_udds o, daee_uddc c, sys_empresas e, sys_users u ";
			$sql   .= "WHERE n.uc=c.id AND c.uo=o.id AND c.empresa=e.id AND n.usuario=u.id $pagoSql AND n.emissao >= '$data_ini' ";
			$sql   .= "AND n.contrato = " . $this->id . " AND n.pagto <> '2013-01-01' ORDER BY rgi";
			$query	= mysql_query($sql);
			
			if(mysql_num_rows($query) > 0){
			
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res;
				
				}
			
			}else{
			
				$ret[]['id']	= null;
			
			}
		
			return $ret;

		
		}

		/*
		 * Método para retornar todas as notas de UCs por REFERENCIA
		 */
		public function getAllNotasByRef($mes_ref, $ano_ref, $pagos=false){
		
			$mes = ($mes_ref <= 12 && $mes_ref >= 1) ? $mes_ref : Date('n');
			$ano = ($ano_ref >= 2012 && $ano_ref <= Date('Y')) ? $ano_ref : Date('Y');
			
			$pagoSql = ($pagos) ? "AND n.pagto <> '0000-00-00'" : "";
			
			$sql 	= "SELECT n.id,n.mes_ref,n.ano_ref,n.numero,o.unidade,c.rgi,c.compl,e.nome,n.consumo,n.valor,n.emissao,n.criado,n.saida,";
			$sql   .= "n.provisoria,n.pagto,u.login FROM daee_notas n, daee_udds o, daee_uddc c, sys_empresas e, sys_users u ";
			$sql   .= "WHERE n.uc=c.id AND c.uo=o.id AND c.empresa=e.id AND n.usuario=u.id $pagoSql AND n.mes_ref= $mes AND n.ano_ref= $ano ";
			$sql   .= "AND n.contrato = " . $this->id . " AND n.pagto <> '2013-01-01' ORDER BY rgi";
			$query	= mysql_query($sql);
			
			if(mysql_num_rows($query) > 0){
			
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res; 
				
				}
			
			}else{
			
				$ret[]['id']	= null;
			
			}
		
			return $ret;

		}

		/*
		 * Método para retornar todas as notas de UCs por REFERENCIA
		 */
		public function getAllNotasforDof($prov,$mes=null,$ano=null){		
		
			$refData = isset($mes,$ano) ? "AND n.mes_ref=$mes AND n.ano_ref=$ano " : "";
			$sql 	= "SELECT n.id,n.mes_ref,n.ano_ref,n.numero,o.unidade,c.rgi,c.compl,e.nome,n.consumo,n.valor,n.emissao,n.criado,n.saida,";
			$sql   .= "n.provisoria,n.pagto,u.login FROM daee_notas n, daee_udds o, daee_uddc c, sys_empresas e, sys_users u ";
			$sql   .= "WHERE n.uc=c.id AND c.uo=o.id AND c.empresa=e.id AND n.usuario=u.id $refData";
			$sql   .= "AND n.contrato = " . $this->id . " AND n.provisoria=$prov ORDER BY rgi";			
			$query	= mysql_query($sql);
			
			if(mysql_num_rows($query) > 0){
			
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res; 
				
				}
			
			}else{
			
				$ret[0]['id']	= null;
				$ret[0]['sql']	= $sql;
			
			}
		
			return $ret;
			
		}
		
	}
	 
	 
?>