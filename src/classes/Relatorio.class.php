<?php 
	/*
	 * Classe de Relatorios A parte
	 * 
	 */
	 
	Class Relatorio{
	
		public $ucTipos = Array("Água e Esgoto","Energia de Baixa Tensão", "Telefonia","Gás");
		
		public $cores	= Array('#7B68EE','#00BFFF','#FFD700','#CD5C5C','#CD853F','#FF1493','#54FF9F','#EEB4B4','#7D26CD','#90EE90');
		public $tipoCont= Array("Autos","Processo");
		public $filters = Array('Valores menores que R$1.000'=>'n.valor < 1000', 'Valores menores que R$10.000'=>"n.valor < 10000",
								'Valores maiores que R$10.000'=>'n.valor > 10000','Consumo menor que 100'=>"n.consumo < 100",
								'Consumo maior que 100'=>"n.consumo > 100", 'Consumo maior que 1000'=>"n.consumo > 1000"
								);
		public $udds;
		
	 
		public function getRelNotas($mes_ref,$ano_ref,$filter,$order,$orientation, $data=null){
		
			$mes = (int) $mes_ref;
			$ano = (int) $ano_ref;
			$where3;
			if($mes == "nulo") $where3 = "AND n.ano_ref = $ano ";
			else $where3 = "AND n.mes_ref = $mes AND n.ano_ref = $ano ";
			
			
			$like = "";
			if(isset($data) && strlen($data) == 10) $like = " AND n.criado LIKE '$data%' ";
			
			$select = "SELECT n.*,o.unidade,c.rgi,c.compl,c.tipo AS constipo, CONCAT(c.rua,' n° ',c.numero) AS endereco, c.id AS ucid,";
			$select.= "c.ativo, c.compl, e.nome AS empresa, u.login, a.pasta, a.numero AS num, a.nome AS autos ";
			$from   = "FROM daee_notas n, daee_udds o, daee_uddc c, sys_empresas e, sys_users u, daee_contratos a ";
			$where1 = "WHERE n.uc=c.id AND c.uo=o.id AND c.empresa=e.id AND n.usuario=u.id AND n.contrato = a.id ";
			$where2 = "AND " . $filter . " ";
			
			$order  = "ORDER BY $order $orientation";
			
			$sql = $select.$from.$where1.$where2.$where3.$like.$order;
			
			$query	= mysql_query($sql);
			
			if(mysql_num_rows($query) > 0){
			
				while($res = mysql_fetch_array($query)){
				
					$ret[] = $res;
				
				}
			
			}else{
			
				$ret[]['id']	= null;
			
			}
			$ret[0]['sql'] = $sql;
		
			return $ret;			
			
		
		}
		
		public function getTipoNome($tipo){
		
			if($tipo == 0) $ret = "Água e Esgoto";
			elseif($tipo == 1) $ret = "Energia de Baixa Tensão";
			elseif($tipo == 2) $ret = "Telefonia";
			elseif($tipo == 3) $ret = "Gás";
			else $ret = "NDA";
			return $ret;
		 
		}		

		public function getObs($obs){
		 
			if($obs != ""){
			
				$texto	= explode("=@=",$obs);
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
				
					$ret .= $obs;
				
				}
				
				return $ret;
			
			}else{
			
				return "";
				
			}
		 
		}
		
		/*
		 * Método para retornar array com UNIDADES OPERACIONAIS
		 * void
		 */
		public function setUdds(){
		
			$sql = "SELECT id, unidade FROM daee_udds";
			$query = mysql_query($sql);
			
			while($res = mysql_fetch_array($query)){
				
				$ret[$res['id']] = $res['unidade'];
			
			}
			
			$this->udds = $ret;
		
		}
	
	}
	 
	 
	 
?>