if($('.complexity-range').length > 0){
	$('.complexity-range').noUiSlider({
		start: [ 1, 20 ],
		step:5,
		connect: true,
		margin: 2,
		range: {
			'min': 1,
			'max': 20
		}
	});
	var rightValue = $('#rightValue').val(),
		leftValue = $('#leftValue').val();
	if(leftValue && rightValue){
		$(".complexity-range").val([leftValue, rightValue]);
	}
	

	
	// Tags after '-inline-' are inserted as HTML.
	// noUiSlider writes to the first element it finds.
	$(".complexity-range").Link("upper").to("-inline-<div class='tooltip'></div>", function ( value ) {
		$('#rightValue').val(value);

		// The tooltip HTML is 'this', so additional
		// markup can be inserted here.
		if(value >=1 && value <=5){
			value = "Simple";
		}else if(value >5 && value <=10){
			value = "Average";
		}else if(value >10 && value <=15){
			value = "Complex";
		}else if(value >15 && value <=20){
			value = "Chaotic";
		}
		$(this).html(
			'<span>' + value + '</span>'
		);
	});
	// Tags after '-inline-' are inserted as HTML.
	// noUiSlider writes to the first element it finds.
	$(".complexity-range").Link("lower").to("-inline-<div class='tooltip'></div>", function ( value ) {
		$('#leftValue').val(value);
		// The tooltip HTML is 'this', so additional
		// markup can be inserted here.
		if(value >=1 && value <=5){
			value = "Simple";
		}else if(value >5 && value <=10){
			value = "Average";
		}else if(value >10 && value <=15){
			value = "Complex";
		}else if(value >15 && value <=20){
			value = "Chaotic";
		}
		$(this).html(
			'<span>' + value + '</span>'
		);
	});
}
$('.ui.card .image.dimmable')
  .dimmer({
    on: 'hover'
  });
$('.ui.accordion').accordion();
$('.message-me-btn').click(function(){
	$('#message-me-modal').modal('show');
})
$('#profile-rating')
  .rating({
    initialRating: 0,
    maxRating: 5
  });

$('.heart.rating')
  .rating({
    initialRating: 0,
    maxRating: 1,
    clearable: true
  });



