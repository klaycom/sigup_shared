<?php
	function ExplodeDateTime($dateTime, $br=false){
		if($dateTime != ""){
			$sep = explode(" ",$dateTime);
			$dia = $sep[0];
			$hora = $sep[1];
			$sepDia = explode("-",$dia);
			$sepHora = explode(":",$hora);
			$separador = ($br) ? "<br />" : "às";
			$explodeDateTime = "$sepDia[2]/$sepDia[1]/$sepDia[0] ". $separador ." $sepHora[0]:$sepHora[1]";
			return $explodeDateTime;
		}else
			return "";
	}
	function ExplodeDateTime2($dateTime){
		if($dateTime != ""){
			$sep = explode(" ",$dateTime);
			$dia = $sep[0];
			$hora = $sep[1];
			$sepDia = explode("-",$dia);
			$sepHora = explode(":",$hora);
			$explodeDateTime = "$sepDia[2]/$sepDia[1]/$sepDia[0]";
			return $explodeDateTime;
		}else 
			return "";
	}
	function ExplodeMesAno($dateTime){
		$sep = explode(" ",$dateTime);
		$dia = $sep[0];
		$hora = $sep[1];
		$sepDia = explode("-",$dia);
		$sepHora = explode(":",$hora);
		switch($sepDia[1]){case"01":$mes="Jan";break;case"02":$mes="Fev";break;case"03":$mes="Mar";break;case"04":$mes="Abr";break;case"05":$mes="Mai";break;
			case"06":$mes="Jun";break;case"07":$mes="Jul";break;case"08":$mes="Ago";break;case"09":$mes="Set";break;case"10":$mes="Out";break;
			case"11":$mes="Nov";break;case"12":$mes="Dez";break;
		}
		return $mes." ".$sepDia[0];
	}
	function calcularDimensoes($xmax, $orix, $oriy){
            $ymax = ($xmax * $oriy) / $orix;
            return $ymax; 
    }
	
	function tratarValor($val, $dinheiro = false){
		
		if(!$dinheiro){
		
			return number_format($val, 0, ",", ".");
		
		}else{
		
			return number_format($val, 2, ",", ".");
		
		}
	
	}
	
	function getMesNome($mes,$abrev=true){
	
		if($abrev){
			switch($mes){
				case 1:$mes="Jan";break;case 2:$mes="Fev";break;case 3:$mes="Mar";break;case 4:$mes="Abr";break;case 5:$mes="Mai";break;
				case 6:$mes="Jun";break;case 7:$mes="Jul";break;case 8:$mes="Ago";break;case 9:$mes="Set";break;case 10:$mes="Out";break;
				case 11:$mes="Nov";break;case 12:$mes="Dez";break;case"13":$mes="MÉDIA";break;
			}
		}else{
			switch($mes){
				case 1:$mes="Janeiro";break;case 2:$mes="Fevereiro";break;case 3:$mes="Março";break;case 4:$mes="Abril";break;case 5:$mes="Maio";break;
				case 6:$mes="Junho";break;case 7:$mes="Julho";break;case 8:$mes="Agosto";break;case 9:$mes="Setembro";break;case 10:$mes="Outubro";break;
				case 11:$mes="Novembro";break;case 12:$mes="Dezembro";break;case"13":$mes="MÉDIA";break;
			}		
		}
		return $mes;
		
	}

	function setDateDiaMesAno($date, $string=false){
		
		if($date != "0000-00-00"){
		
			$sepDia = explode("-",$date);
			if($string){
				switch($sepDia[1]){case"01":$mes="Jan";break;case"02":$mes="Fev";break;case"03":$mes="Mar";break;case"04":$mes="Abr";break;case"05":$mes="Mai";break;
					case"06":$mes="Jun";break;case"07":$mes="Jul";break;case"08":$mes="Ago";break;case"09":$mes="Set";break;case"10":$mes="Out";break;
					case"11":$mes="Nov";break;case"12":$mes="Dez";break;
				}
			}else{
			
				$mes = $sepDia[1];
				
			}
			return $sepDia[2]."/".$mes."/".$sepDia[0];
			
		}else{
		
			return "--";
		
		}
	}
	
	function getPorcentagem($num,$html=false){
	
		$num = round($num);
		if($html){
		
			if($num == -100){
				$color = "black";
				$num = "--";
			}elseif($num > 0 && $num < 1000){
				$color = "red";
				$num = $num."%";
			}elseif($num > 1000){
				$color = "red";
				$num = "+1000%";
			}elseif($num < 0){
				$color = "green";
				$num = $num."%";
			}else{
				$color = "black";
				$num = "--";
			}
			
			$ret = "<font color='". $color ."'>". $num . "</font>";
			return $ret;
		
		}else 
			return $num;
	
	}
	
	function execucao(){ 
		$sec = explode(" ",microtime());
		$tempo = $sec[1] + $sec[0];
		return $tempo; 
	}	

	function isValidDiaMesAno($dia,$mes,$ano){

		$invalid = 0;
		$dia = (int) $dia;
		$mes = (int) $mes;
		$ano = (int) $ano;
		
		if($mes <= 0 || $mes > 12)
			$invalid++;
		if($dia <= 0 || $dia > 31)
			$invalid++;			
		if($dia == 31 && ($mes == 2 || $mes == 4 || $mes == 6 || $mes == 9 || $mes == 11))
			$invalid++;
		if($dia > 29 && $mes == 2)
			$invalid++;
		if($ano < 1900)
			$invalid++;	
		if($invalid > 0)
			return false;
		else
			return true;
	
	}
	
	function isValidMesAno($mes,$ano){
	
		$invalid = 0;
		if($mes <= 0 || $mes > 12)
			$invalid++;
		if($ano < 1900)
			$invalid++;			
		if($invalid > 0)
			return false;
		else
			return true;
	
	}	
	
	function getTransformDate($date){
	
		$date = explode("/", $date);
		return $date[2]."-".$date[1]."-".$date[0];
	
	}
	
	function mysqlNumber($num){
	
		$ret= str_replace(",", ".", $num);
		return $ret;
	
	}
	

?>