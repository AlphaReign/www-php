$('[data-unix]').each(function(){
	var unix = $(this).attr('data-unix');
	var time = moment.unix(unix);
	$(this).html(time.format('YYYY/MM/DD h:mm A'));
});
$('[data-unix-short]').each(function(){
	var unix = $(this).attr('data-unix-short');
	if(unix != 0){
		var time = moment.unix(unix);
		$(this).html(time.format('YYYY/MM/DD'));
		$(this).attr('alt', time.format('YYYY/MM/DD h:mm A'));
		$(this).attr('title', time.format('YYYY/MM/DD h:mm A'));
	}
});

$(function(){
	$(document).on('click', '.sort', function(event){
		event.preventDefault();
		var type = $(event.target).attr('data-sort');
		var results = $('#results > *').sort(function(a, b){
			if(type == 'name'){
				var aSort = a.getAttribute('data-'+type);
				var bSort = b.getAttribute('data-'+type);
				if(aSort > bSort) return 1;
				if(aSort < bSort) return -1;
				return 0;
			}else{
				var aSort = parseInt(a.getAttribute('data-'+type));
				var bSort = parseInt(b.getAttribute('data-'+type));
				if(aSort > bSort) return -1;
				if(aSort < bSort) return 1;
				return 0;
			}
		}).prependTo('#results');
	});
});