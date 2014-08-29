<?php
	require "src/scripts/conecta.php";
	require "src/scripts/functions.php";
	include_once "src/classes/Users.class.php";
	include_once "src/classes/Notas.class.php";
	include_once "src/classes/UnidadeCons.class.php";
	
	/**************************************************
	 ARQUIVO DE SCRIPTS DE AÇÃO RECEBIDOS POR S_POST
	**************************************************/

	if (getenv("REQUEST_METHOD") == "POST") {
	
		if(isset($_POST['action'])){
		
			switch($_POST['action']){
			
				case "verificaNota":
				
					$uc = new UnidadeConsumidora($_POST['ucid']);
					echo $uc->isNotaRegistered($_POST['data']);

				break;
				case "marcarPago":
				
					$data = getTransformDate($_POST['data']);
					$nota = new Nota($_POST['nid']);
					$nota->marcarPago($data);
					echo "<font color='red'>" . $_POST['data'] . "</font>";

				break;			
				case "getProvs":
				
					$autos = (int) $_POST['autos'];
					$sql = "SELECT DISTINCT(provisoria) FROM daee_notas WHERE contrato = $autos ORDER BY provisoria DESC";
					$query = mysql_query($sql);
					if(mysql_num_rows($query) > 0){
					
						while($prov = mysql_fetch_array($query)){
						
							$num = $prov['provisoria'];
							if($num == 0){
							
								echo "<option value='$num'>Desconhecida</option>";
							
							}else{
							
								echo "<option value='$num'>$num</option>";
							
							}
							
						}
						
					}else{
						
						echo "<option value='$num'>". mysql_error() ."</option>";
					
					}

				break;						
			
			
			
			
			}
		
		}
	
	}
	
?>