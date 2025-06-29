/*
  Creación de una función personalizada para jQuery que detecta cuando se detiene el scroll en la página
*/
$.fn.scrollEnd = function(callback, timeout) {
  $(this).scroll(function(){
    var $this = $(this);
    if ($this.data('scrollTimeout')) {
      clearTimeout($this.data('scrollTimeout'));
    }
    $this.data('scrollTimeout', setTimeout(callback,timeout));
  });
};
/*
  Función que inicializa el elemento Slider
*/

function inicializarSlider(){
  $("#rangoPrecio").ionRangeSlider({
    type: "double",
    grid: false,
    min: 0,
    max: 100000,
    from: 200,
    to: 80000,
    prefix: "$"
  });
}
/*
  Función que reproduce el video de fondo al hacer scroll, y deteiene la reproducción al detener el scroll
*/
function playVideoOnScroll(){
  var ultimoScroll = 0,
      intervalRewind;
  var video = document.getElementById('vidFondo');
  $(window)
    .scroll((event)=>{
      var scrollActual = $(window).scrollTop();
      if (scrollActual > ultimoScroll){
       video.play();
     } else {
        //this.rewind(1.0, video, intervalRewind);
        video.play();
     }
     ultimoScroll = scrollActual;
    })
    .scrollEnd(()=>{
      video.pause();
    }, 10)
}
$(function() {
  // Cargar opciones al iniciar
  $.getJSON("load_options.php", function(data) {
    data.ciudades.forEach(function(c) {
      $("#selectCiudad").append(`<option value="${c}">${c}</option>`);
    });
    data.tipos.forEach(function(t) {
      $("#selectTipo").append(`<option value="${t}">${t}</option>`);
    });
    $('select').formSelect(); // Re-inicializar Materialize
  });

  // Rango de precios
  $("#rangoPrecio").ionRangeSlider({
    type: "double",
    grid: true,
    min: 0,
    max: 100000,
    from: 10000,
    to: 50000,
    prefix: "$"
  });

  // Enviar formulario sin recargar
  $("#formulario").submit(function(e) {
    e.preventDefault();
    $.post("buscador.php", $(this).serialize(), function(data) {
      $(".colContenido").append(data);
    });
  });

  // Mostrar todos
  $("#mostrarTodos").click(function() {
    $.post("buscador.php", {}, function(data) {
      $(".colContenido").append(data);
    });
  });
});
document.addEventListener('DOMContentLoaded', function() {
  var elems = document.querySelectorAll('select');
  M.FormSelect.init(elems);
});

inicializarSlider();
playVideoOnScroll();
