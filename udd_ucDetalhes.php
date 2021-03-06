<?php
	
	require "src/scripts/conecta.php";
	require "src/scripts/restrito.php";
	include "src/scripts/functions.php";
	require "src/classes/Template.class.php";
	include "src/classes/Users.class.php";
	include "src/classes/UnidadeCons.class.php";
	include "src/classes/Notas.class.php";
	
		//$tpl->TESTE = $emp->getServicos()[2]->children();	
	
	
	redirectByPermission(1); // SETAR PERMISSÃO DA PÁGINA
	$inicio = execucao();
	
	$tpl = new Template('html_libs/template.html');
	
	$tpl->addFile('SECONDMENU','html_libs/udd_secondMenu.html');
	$tpl->addFile('JSCRIPT','cssjs_libs/js_parts/js_udd_uoDetalhes.html');
	$anoAtual = (isset($_GET['ano'])) ? $_GET['ano'] : Date('Y');
        
        $tipoVal;
	
	if(isset($_GET['chave'])){
	
		$chave = $_GET['chave'];
		if(strlen($chave) == 40){
		
			$tpl->addFile('CONTEUDO','html_libs/udd_uoDetalhes.html');
			$queryVerif = mysql_query("SELECT id FROM daee_udds WHERE SHA1(id) = '".$_GET['chave']."'");
			if(mysql_num_rows($queryVerif) == 1){

			
				$id = mysql_fetch_array($queryVerif)['id'];			
				/*********************************
				  MOSTRAR INFORMAÇÕES DO RELATÓRIO
				 *********************************/ 				
				$uo = new Unidade($id);
				$tpl->UOSIGLA		= $uo->getSigla();
				$tpl->UONOME		= $uo->getNome();

				$uoTipoServicos		= $uo->getTiposServicos();
				$tpl->UOUCQTD		= $uo->getQtdUc();
				$tpl->ANO_ATU		= $anoAtual;
				$tpl->TIPOSSERVICOS = "";
				
				$z = 0;
				if(count($uoTipoServicos) > 0 ) foreach($uoTipoServicos as $tipo){
				
					$ucTipo = new UnidadeConsumidora(0, null, null, null, null, null, null, null, null, null, null, $tipo);
					$mesmoTipo = $uo->getUcPorTipo($tipo);
					$tpl->TIPOSSERVICOS .= "<br /> - <b><font color='navy'>" . $ucTipo->getTipoNome() . "</font></b>: <a href='udd_uoCharts.php?chave=". $_GET['chave'] ."&svc=$tipo&start=". $anoAtual ."'>";
					$tpl->TIPOSSERVICOS .= "Relatório ". $anoAtual ." de " . count($mesmoTipo) . " UC(s)</a>";
					$tpl->TIPOSERVICO = $ucTipo->getTipoNome();
					$tpl->CHART_VALOR = $uo->getTotalPorTipo($tipo,$anoAtual);
					
					if($z < count($uoTipoServicos)) $tpl->COMA1 = ",";
					else $tpl->COMA1 = "";
					$z++;
					
					$tpl->block('EACH_CHART_SERVICO');
                                        
                                        $tipoVal[$tipo]['nome'] = $ucTipo->getTipoNome();
                                        $tipoVal[$tipo]['mes'][1] = 0; $tipoVal[$tipo]['mes'][2] = 0;$tipoVal[$tipo]['mes'][3] = 0; $tipoVal[$tipo]['mes'][4] = 0;$tipoVal[$tipo]['mes'][5] = 0; $tipoVal[$tipo]['mes'][6] = 0;
                                        $tipoVal[$tipo]['mes'][7] = 0; $tipoVal[$tipo]['mes'][8] = 0;$tipoVal[$tipo]['mes'][9] = 0; $tipoVal[$tipo]['mes'][10] = 0;$tipoVal[$tipo]['mes'][11] = 0; $tipoVal[$tipo]['mes'][12] = 0;				
				}
                                
				/**********************************
				     MOSTRAR PAGAMENTOS POR UC
				**********************************/
				$uoUcs = $uo->getAllUcs();
				$pag_mensal;
				$totalMensal;
				$totalMensal[1] = 0; $totalMensal[2] = 0; $totalMensal[3] = 0; $totalMensal[4] = 0;
				$totalMensal[5] = 0; $totalMensal[6] = 0; $totalMensal[7] = 0; $totalMensal[8] = 0;
				$totalMensal[9] = 0; $totalMensal[10] = 0; $totalMensal[11] = 0; $totalMensal[12] = 0;				
				$totalMensal[13] = 0; // TOTAL DA UO
				foreach($uoUcs as $uoUcId){
				
					$uc = new UnidadeConsumidora($uoUcId);
					$tpl->RGI = $uc->get('rgi');
					$tpl->UCNOME = $uc->getNome();
					$tpl->UCSTATUS = "<br>" . $uc->getAtivoText();
					$tpl->UCTIPO = $uc->getTipoNome();
					$tpl->UCEMPRESA = $uc->get('empresa')->getNome();
					$tpl->UCCHAVE = SHA1($uc->get('id'));
                                        $tipo = $uc->get('tipo');
					
					/**********************************
						MOSTRAR PAGAMENTOS MENSAIS
					**********************************/
					$pagMensal = $uc->getExercicioMensal($anoAtual);
					$index = 1;
					$totalUc = 0;
					
					for($i=1;$i<=12; $i++){
					
						if(array_key_exists($index,$pagMensal) && $pagMensal[$index]['mes_ref'] == $i){
						
							$val = $pagMensal[$index]['valor'];
							$tpl->UCMESVAL = tratarValor($val, true);
							$index++;
						
						}else{
							
							$val = 0;
							$tpl->UCMESVAL = "";
						
						}
						
						$totalUc += $val;
						$totalMensal[$i] += $val;
						$totalMensal[13] += $val;
						
						$tpl->block('EACH_MESUC');
                                                
                                                $tipoVal[$tipo]['mes'][$i] += $val;
					
					}
					$tpl->UCTOTAL = tratarValor($totalUc, true);
					
					
					$tpl->block('EACH_UC');
				
				}

                                $mediaMensal = $totalMensal[13] / getDivisorParaMedia($totalMensal);
                                //echo $mediaMensal;
				/**********************************
					MOSTRAR TOTAIS MENSAIS
				**********************************/				
				for($i=1;$i<=12; $i++){
				
					$tpl->UCMESTOTAL = tratarValor($totalMensal[$i],true);
					$tpl->block('EACH_MESUCTOTAL');
					
					$tpl->MESANO = getMesNome($i);
					$tpl->AREA_VAL = $totalMensal[$i];
                                        
                                       $tpl->MEDTOT_VAL = $mediaMensal;
					
					if($i < 12) $tpl->COMA = ",";
					else $tpl->COMA = "";
					
					$tpl->block('EACH_AREACHART');
				
				}
				
				$tpl->UOTOTAL = tratarValor($totalMensal[13],true);
                                
                                $chrt_index = 0;
                                foreach($tipoVal as $tp){
                                    
                                    $tpl->CHRT_TIPO_NOME = $tp['nome'];
                                    $tpl->CHRT_INDEX = $chrt_index;
                                    $chrt_index++;
                                    
                                    $ar_med = round(array_sum($tp['mes']) / getDivisorParaMedia($tp['mes']), 2);
                                    
                                    for($i=1;$i<=12; $i++){
                                        
                                        $tpl->MESANO = getMesNome($i);
                                        $tpl->AR_VAL = ($tp['mes'][$i] > 0) ? $tp['mes'][$i] : 0;
                                        $tpl->COMA   = ($i < 12) ? ',' : '';
                                        $tpl->AR_MED = $ar_med;
                                        
                                        $tpl->block('EACH_AR_VAL');
                                        
                                    }                                    
                                                                        
                                    $tpl->block('EACH_AR_CHART');
                                    $tpl->block('EACH_TIPO_CHART');

                                }

                                $tpl->ANO_OPT = $anoAtual;
                                $tpl->ANO_OPT_TXT = 'Visualizando exercício de ' . $anoAtual;
                                $tpl->block('VER_ANO_OPT');
                                
                                
                                for($i = 2012; $i<=Date('Y'); $i++){
                                    
                                    $tpl->ANO_OPT = $i;
                                    $tpl->ANO_OPT_TXT = 'Exercício de ' . $i;
                                    $tpl->block('VER_ANO_OPT');
                                    
                                }
			}else{
		
				header("Location: udd_relatorioUO.php");
		
			}

		
		}else{
		
			header("Location: udd_relatorioUO.php");
		
		}
	
	}
        
	$fim = execucao();
	$tempo = number_format(($fim-$inicio),6);
	$tpl->EXECTIME = "Tempo de Execução: <b>".$tempo."</b> segundos";
	
	$tpl->show();

        function getDivisorParaMedia(array $array){
            
            $ret = 0;
            $loop = 12;
            for ($i=1; $i <= $loop; $i++) {
                if($array[$i] > 0) $ret++;
            }
            return ($ret == 0) ? 1 : $ret;
            
        }
?>
