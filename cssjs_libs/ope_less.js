var url = window.location.href;
var baseName = url.split("/");
var simpleBaseName = baseName[baseName.length-1];

$(document).ready(function(){

	$(".mantem-text").change(function(){
	
		var inputClass = $(this).attr('name').replace('mantem-','');
		if($(this).is(':checked')){
		
			$('.' + inputClass).attr('readonly',true);
		
		}else{
			
			$('.' + inputClass).attr('readonly',false);
		
		}
	
	});
	
	$(".mantem-select").change(function(){
	
		var inputClass = $(this).attr('name').replace('mantem-','');
		if($(this).is(':checked')){
		
			$('.' + inputClass).attr('disabled',true);
		
		}else{
			
			$('.' + inputClass).attr('disabled',false);
		
		}
	
	});	
	//("teste");
	$("tbody tr:odd").css("background-color", "#F8F8FF");
	
});

