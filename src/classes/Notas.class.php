<?php
	
	/*
	 * Classe de Notas
	 * 
	 */
	 
	include_once "Contratos.class.php";
	include_once "UnidadeCons.class.php";
	 
	Class Nota extends Contrato{
	
		private $id;		// Identificador
		private $tipo;		// 0=Nota Fiscal 1=Empenho 2=Cancelamento
		private $tipo_cons;	// Tipo de Consumo
		private $nome;
		private $numero;	
		private $sequencia;
		private $provisoria;
		private $emissao;
		private $vencto;	
		private $data_ref;
		private $mes_ref;
		private $ano_ref;
		private $consumo;	// Consumo se houver
		private $valor;		// Valor em Reais
		private $contrato;	// Vínculo a qual contrato
		private $usuario;	// Quem cadastrou
		private $uc;		// Unidade Consumidora vinculada
		private $criado;	// Criado em DATETIME
		private $saida;
		private $desc;
		private $pagoem;
		private $ultimovencto;
		public  $sql;
		
		function __construct($id,$tipo=null,$numero=null,$nome=null,$sequencia=null,$emissao=null,$mes_ref=null,$ano_ref=null,$consumo=null,$valor=null,$contrato=null,$usuario=null,$uc=null,$saida=null,$pagto=null,$prov=null,$desc=null,$vencto=null){
		
			if($id == ""){
			
				//if(!$this->verificaDuplicidade($numero)){
				
					$sql = "INSERT INTO daee_notas VALUES('','$numero','$nome',$sequencia,$tipo,'$emissao',$mes_ref,$ano_ref,$consumo,$valor,$contrato,'$prov',$usuario,$uc,'$vencto','$saida','$pagto',NOW(),'$desc')";
					$this->sql = $sql;
					$this->ultimovencto = ($numero == "Indefinido") ? $vencto : "";
					$this->uc = $uc;
					$this->contrato = $contrato;
					$this->numero = $numero;
					$this->mes_ref = $mes_ref;
					$this->ano_ref = $ano_ref;
					$this->uc = $uc;
					//mysql_query($sql);
					
				//}
			
			}elseif($id > 0){
			
				$this->id = $id;
				$sql = "SELECT n.*,u.login FROM daee_notas n,sys_users u WHERE n.id=$id AND n.usuario = u.id";
				$query = mysql_query($sql);
				if(mysql_num_rows($query) == 1){
				
					$res = mysql_fetch_array($query);
					$this->numero		= $res['numero'];
					$this->nome			= $res['nome'];
					$this->sequencia	= $res['sequencia'];
					$this->emissao 		= $res['emissao'];
					$this->data_ref		= $this->getMesNome($res['mes_ref'])."/".$res['ano_ref'];
					$this->valor		= $res['valor'];
					$this->provisoria	= $res['provisoria'];
					$this->contrato		= new Contrato($res['contrato']);
					$this->usuario		= new User(false,null,null,null,null,null,$res['usuario']);
					$this->tipo			= $res['tipo'];
					$this->saida		= $res['saida'];
					$this->pagoem		= $res['pagto'];
					$this->vencto		= $res['vencto'];
					$this->desc			= $res['desc'];
					
					if($res['uc'] != null){
					
						$this->uc = new UnidadeConsumidora($res['uc']);
						$this->tipo_cons= $this->uc->get('tipo');
						$this->consumo	= $res['consumo'];
					
					}
					
				}
			
			}
		
		}
		
		/*
		 * Método para verificar se a fatura já foi cadastrada
		 */
		
		public function verificaDuplicidade(){
		
			$sql = "SELECT numero FROM daee_notas WHERE numero = '" . $this->numero . "' AND contrato=" . $this->contrato . " ";
			$sql.= "AND mes_ref =" . $this->mes_ref . " AND ano_ref=" . $this->ano_ref . " AND uc=" . $this->uc;
			$query = mysql_query($sql);
			$this->teste = $sql;
			
			if(mysql_num_rows($query) > 0) return true;
			else return false;
		
		}		
		
		/*
		 * Método para retornar nome do mês
		 */		
		
		public function getMesNome($param){
		
			switch($param){case"01":$mes="Jan";break;case"02":$mes="Fev";break;case"03":$mes="Mar";break;case"04":$mes="Abr";break;case"05":$mes="Mai";break;
				case"06":$mes="Jun";break;case"07":$mes="Jul";break;case"08":$mes="Ago";break;case"09":$mes="Set";break;case"10":$mes="Out";break;
				case"11":$mes="Nov";break;case"12":$mes="Dez";break; default: "NDA";
			}
			return $mes;			
		
		}
		
		/*
		 * Verificar se é Empenho ou não
		 * @return BOOLEAN
		 */
		
		public function isEmpenho(){
		
			if($this->tipo > 0) $ret = true;
			else $ret = false;
			return $ret;
		
		}
		
		/*
		 * Método para retornar $param da Nota
		 */
		 
		public function get($param){
		 
			return $this->$param;
		 
		}		

		/*
		 * Método para salvar Nota no banco de dados
		 */
		 
		public function saveNew(){
		
			if($this->ultimovencto != "") 
				mysql_query("UPDATE daee_uddc SET vencto = '". $this->ultimovencto ."' WHERE id = " . $this->uc );
			return mysql_query($this->sql);
		 
		}
		
		/*
		 * Método para salvar Nota no banco de dados
		 */
		 
		public function marcarPago($date){
		
			$sql = "UPDATE daee_notas SET pagto='$date' WHERE id=" . $this->id ;
			mysql_query($sql);
		
		}

		/*
		 * Método para editar infos de nota
		 * @return [tipo,msg]
		 */
		 
		public function editarNota($campo, $valor, $usertime, $motivo){
		
			if($this->$campo == $valor){
				
				$ret['tipo'] = "alerta";
				$ret['msg']	 = "As informações fornecidas não podem ser iguais as anteriores.";
			
			}else{
			
				$act = " editou o campo $campo para <b>$valor</b> por: ";
				$sql = "UPDATE daee_notas SET $campo = '$valor', `desc` = CONCAT(`desc`,'<br />','$usertime','$act','$motivo') WHERE id = " . $this->id;
				
				if(mysql_query($sql)){
				
					$ret['tipo'] = "aviso";
					$ret['msg']  = "Nota editada com sucesso!";
				
				}else{
				
					$ret['tipo'] = "alerta";
					$ret['msg']  = "Houve um erro ao tentar editar! Erro: " . mysql_error;					
				
				}
				
			}
			return $ret;
		
		}

		/*
		 * Método para pegar OBS de notas
		 * @return [tipo,msg]
		 */
		public function getObs(){
		 
			if($this->desc != ""){
			
				$texto	= explode("=@=",$this->desc);
				$ret	= "";
				if(count($texto) > 0){
				
					for($i = 0; $i < count($texto); $i++){
					
						if($i % 2 == 0){
						
							$ret .= $texto[$i];
							
						}else{
						
							$ret .= "<b>" . ExplodeDateTime($texto[$i]) . "</b>";
						
						}
					
					}
				
				}else{
				
					$ret .= $this->desc;
				
				}
				
				return $ret;
			
			}else{
			
				return "";
				
			}
		 
		}
		
	}

?>