$(document).ready(function(){	
	$('.recoment').click(function(e){
		e.preventDefault();
		$(this).parent().prevAll('a.aval').addClass('hide');
		var opcao = $(this).parent().data('aval');
		var texto = $(this).prev().prev().attr('value');
		$(this).parent().text(texto);
	});
	
	$('.aval button').click(function(e){
		e.preventDefault();
		$(this).parent().parent().nextAll().removeClass('hide');
	});
	
	$('.aval button.aval-pos').click(function(){
		$(this).next().removeClass('btn-danger').children('i').removeClass('icon-white');
		$(this).parent().parent().next('.recoment').data('aval', +1);
		$(this).addClass('btn-success').children('i').addClass('icon-white');
	});
	
	$('.aval button.aval-neg').click(function(){
		$(this).prev().removeClass('btn-success').children('i').removeClass('icon-white');
		$(this).parent().parent().next('.recoment').data('aval', -1);
		$(this).addClass('btn-danger').children('i').addClass('icon-white');
	});
		
	$('.recoment button').click(function(){
		// Variáveis para serem utilizadas na req. POST
		var recomentId = $(this).parent().data('id');
		var recomentAval = $(this).parent().data('aval');
		var recomentTexto = $(this).prev().prev('textarea').val();

		// Esconder a caixa de recomentários
		$(this).parent().prev().addClass('hide');
		$(this).parent().text($(this).prev().prev('textarea').val());

		$.post('recomentario.php', {
			referencia: recomentId,
			aval: recomentAval,
			texto: recomentTexto			
		}, function(data){
			if (data == 'true')
				console.log('comentário enviado');	
		});
	});
	
	$('form').validate({
		errorClass:'error',
    validClass:'success',
    errorElement:'span',
    highlight: function (element, errorClass, validClass) { 
        $(element).parents("div[class='clearfix']").addClass(errorClass).removeClass(validClass); 
    }, 
    unhighlight: function (element, errorClass, validClass) { 
        $(element).parents(".error").removeClass(errorClass).addClass(validClass); 
    },
    rules: {
    	comentario: {
      	required: true,
        minlength: 3
    	}
    }
 });
});