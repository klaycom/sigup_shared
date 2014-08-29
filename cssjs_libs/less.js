var url = window.location.href;
var baseName = url.split("/");
var simpleBaseName = baseName[baseName.length-1];

$(document).ready(function(){

	var boxAtual = "";
	$(".click-topic").click(function(){
	
		//$(this).css("background-color", "#f7f7f7"); 
		//$(".hidden").hide();
		var box = $(this).attr('name');
		
		$("#" + box).fadeIn(1000);
		
	});
	
	/*
	
	$("tbody tr").mouseout(function(){
	
		$(this).css("background-color", ""); 
		//alert('lalal');
		
	});
	
	*/
	
	$("tbody tr:odd").css("background-color", "#F8F8FF");
	
	
	switch(simpleBaseName){
	
		case "ger_addContrato.php?form":
		
			$("input[name='radioserv']").change(function(){
			
				var val = $(this).val();
				if(val == 1){
				
					$('#serv-form').show();
					$("select[name='servico-list']").attr("disabled", true);
					$("select[name='servico-list']").css("background-color", "#fafafa"); 
				
				}else if(val == 0){
				
					$('#serv-form').hide();
					$("select[name='servico-list']").attr("disabled", false);
					$("select[name='servico-list']").css("background-color", "#fff"); 
				
				}
			
			});
		
		break;
		
		case "ger_addUnidade.php":
		
			$("#uo-link").click(function(){
				document.location.href= "ger_addUnidade.php?form=uo";
			});
			$("#uc-link").click(function(){
				document.location.href= "ger_addUnidade.php?form=uc";
			});			
		
		break;
	
	}


});