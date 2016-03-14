$(document).ready(function() {
	$("#owl-demo").owlCarousel({
   
         navigation: true,
        navigationText: [
        "<img src='http://s24.postimg.org/dizyq5qnl/atras.png' class='atras-sm'>",
        "<img src='http://s11.postimg.org/4dqi7963z/adelante.png' class='adelante-sm'>"
        ],
        slideSpeed : 300,
        paginationSpeed : 400,
        pagination : false,
        singleItem:true
    });

    $("#crecimiento").owlCarousel({
   
         navigation: true,
        navigationText: [
        "<img src='http://s24.postimg.org/dizyq5qnl/atras.png' class='atras-sm'>",
        "<img src='http://s11.postimg.org/4dqi7963z/adelante.png' class='adelante-sm'>"
        ],
        slideSpeed : 300,
        paginationSpeed : 400,
        pagination : false,
        singleItem:true
    });

    $("#ama-pago").owlCarousel({
   
         navigation: true,
        navigationText: [
        "<img src='http://s24.postimg.org/dizyq5qnl/atras.png' class='atras-sm'>",
        "<img src='http://s11.postimg.org/4dqi7963z/adelante.png' class='adelante-sm'>"
        ],
        slideSpeed : 300,
        paginationSpeed : 400,
        pagination : false,
        singleItem:true
    });

    $("#reconocimientos-carrusel").owlCarousel({
   
         navigation: true,
        navigationText: [
        "<img src='http://s28.postimg.org/4hftwhrmx/atras.png' class='atras-reco'>",
        "<img src='http://s28.postimg.org/5v7grsqw9/adelante.png' class='adelante-reco'>"
        ],
        slideSpeed : 300,
        paginationSpeed : 400,
        pagination : true,
        singleItem:true
    });
    $("#home").owlCarousel({
   
         navigation: true,
        navigationText: [
       "<img src='http://s28.postimg.org/4hftwhrmx/atras.png' class='atras-sm'>",
        "<img src='http://s28.postimg.org/5v7grsqw9/adelante.png' class='adelante-sm'>"
        ],
        slideSpeed : 300,
        paginationSpeed : 400,
        singleItem:true,
        pagination : false
        // "singleItem:true" is a shortcut for:
        // items : 1, 
        // itemsDesktop : false,
        // itemsDesktopSmall : false,
        // itemsTablet: false,
        // itemsMobile : false
   
    });

     $("#banner").owlCarousel({
   
         navigation: true,
        navigationText: [
       "<img src='http://s28.postimg.org/4hftwhrmx/atras.png' class='atras-banner'>",
        "<img src='http://s28.postimg.org/5v7grsqw9/adelante.png' class='adelante-banner'>"
        ],
        slideSpeed : 300,
        paginationSpeed : 400,
        singleItem:true,
        pagination : false
        // "singleItem:true" is a shortcut for:
        // items : 1, 
        // itemsDesktop : false,
        // itemsDesktopSmall : false,
        // itemsTablet: false,
        // itemsMobile : false
   
    });
});