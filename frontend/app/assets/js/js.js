$(window).scroll(function(){
	var position = $(this).scrollTop();
	if (position > 520) {
		$(".header-scroll").fadeIn();
	}
	else if (position < 520 ) {
		$(".header-scroll").fadeOut();
	} 
});


$(function() {
  $('a[href*=#]:not([href=#])').click(function() {
   if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
      if (target.length) {
        $('html,body').animate({
          scrollTop: target.offset().top
        	}, 1000);
        			return false;
      }
    }
  });
});

$(function(){
  $('.item-close').click(function(){
    $('.overlay-hugeinc.open').addClass('close');
  });
});