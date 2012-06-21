$(document).ready(function(){
	$('.aval button').click(function(e){
		e.preventDefault();
		$(this).parent().addClass('well').text($(this).prev().prev('textarea').val()); // 2 prev por causa do <br>
	});

	$('.sinaliza').click(function(e){
		e.preventDefault();
		$(this).find('i').toggleClass('icon-white');
		$(this).toggleClass('btn-danger');
	});
	
	$('.avalComent').click(function(e){
		e.preventDefault();
		$(this).addClass('disabled');
		$(this).nextAll().removeClass('hide');
	});
	
	$('.comentComent button').click(function(e){
		e.preventDefault();
		if ($(this).prevAll().hasClass('active'))
			var classe = 'alert';
		else
			var classe = 'well';

		$(this).parent().addClass(classe).text($(this).prevAll('textarea').val());
	});
});