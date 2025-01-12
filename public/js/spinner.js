$(window).on('load', function() {
	window.addEventListener('displaySpinner', function(e){
		// console.log('displaySpinner!');
    	// console.log(e.detail);
    	// data = e.detail.toString();
    	$("#overlay").fadeIn(300);
    });
    window.addEventListener('hideSpinner', function(e){
    	// console.log('hideSpinner!');
    	// console.log(e.detail);
    	// data = e.detail.toString();
    	$("#overlay").fadeOut(300);
    });
});