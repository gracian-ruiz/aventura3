<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invitación</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Georgia&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">

<style>
  .icono-hotel {
  -webkit-text-size-adjust: 100%;
  tab-size: 4;
  -webkit-tap-highlight-color: transparent;
  --foreground-rgb: 0,0,0;
  --background-start-rgb: 214,219,220;
  --background-end-rgb: 255,255,255;
  --trsn: all 0.5s ease 0s;
  --black: #000;
  --tw-bg-opacity: 1;
  font-family: inherit;
  font-feature-settings: inherit;
  font-variation-settings: inherit;
  font-size: 100%;
  font-weight: inherit;
  line-height: inherit;
  text-transform: none;
  cursor: pointer;
  text-align: center;
  --tw-text-opacity: 1;
  color: rgb(0 0 0/var(--tw-text-opacity));
  width: 80px;
  aspect-ratio: 1 / 1;
  box-sizing: border-box;
  border: 0 solid #e5e7eb;
  display: block;
  vertical-align: middle;
  max-width: 100%;
  height: auto;
  margin-left: auto;
  margin-right: auto;
  background: #9aa5a5;
}

.boton-sin-borde {
  background: none;
  border: none;
  padding: 0;
  margin: 0;

}



.fade-in {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 1.6s ease-out, transform 1.6s ease-out;
}
.fade-in.visible {
  opacity: 1;
  transform: translateY(0);
}


@keyframes fadeGrow {
  0% {
    opacity: 0;
    transform: scale(0.9);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

.portada-invitacion.oculta {
  opacity: 0;
}

.portada-invitacion.mostrar {
  animation: fadeGrow 1.2s ease-out forwards;
}

</style>







  <style>
    body{
      font-family: '__Parisienne_f98ef7', '__Parisienne_Fallback_f98ef7'!important;
    }
.__className_f98ef7 {
  font-family: '__Parisienne_f98ef7', '__Parisienne_Fallback_f98ef7';
  font-weight: 400;
  font-style: normal;
}

    .icono-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;     /* o el ancho que necesites */
  height: 100%;    /* o una altura fija si aplica */
}

.click-icon {
  width: 30px;
  height: 30px;
  object-fit: contain;
}


.click-icon {
  width: 30px;
  height: 30px;
  object-fit: contain;
  overflow: hidden;
  display: block;
}





    @media (max-width: 768px) {
  .pantalla-inicial {
    align-items: stretch;
    padding: 0;
  }

  .sobre {
    width: 100vw;
    height: 100vh;
    max-width: none;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .sobre .img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 0;
  }

  .contenido-superpuesto {
    padding: 0.5rem;
  }
}




    @media (max-width: 768px) {
  .nombres-principales {
    font-size: 1.5rem !important;
  }
}


    .nombres-boda p {
  margin: 0;
  line-height: 1.2; /* opcional para ajustar altura entre líneas */
}


@font-face {
  font-family: '__Parisienne_f98ef7';
  src: url('images/boda/fonts/Parisienne-Regular.ttf') format('truetype');
  font-weight: 400;
  font-style: normal;
}

@font-face {
  font-family: '__Parisienne_Fallback_f98ef7';
  src: local("Arial");
  ascent-override: 108.70%;
  descent-override: 53.04%;
  line-gap-override: 0.00%;
  size-adjust: 84.23%;
}




.contenido-superpuesto {
  position: absolute;
  inset: 0;
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  pointer-events: none;
}





.sobre .img {
  width: 100%;
  height: auto;
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}


.bounce {
  animation: bounce 1s infinite;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);  
  }
  50% {
    transform: translateY(-10px);
  }
}


    body, html {
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }

    .pantalla-inicial {
      width: 100vw;
      height: 100vh;
      background: #fce4ec;
      display: flex;
      justify-content: center;
      align-items: center;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      transition: opacity 1s ease;
    }

    .pantalla-inicial.oculto {
      opacity: 0;
      pointer-events: none;
    }

    .sobre .img {
      width: 100%;
      height: auto;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    .portada {
      width: 100%;
      height: 90vh;
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      color: white;
    }

/*     .portada::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.4);
      z-index: 0;
    } */

    .portada-contenido {
      position: relative;
      z-index: 1;
   /*    padding: 20px; */
    }

    .novios {
      font-size: 3em;
      font-family: 'Great Vibes', cursive;
      text-shadow: 3px 3px 6px rgba(0,0,0,0.8);
    }

    .fecha {
      font-size: 1.8em;
      margin-top: 20px;
    }

    section {
      padding: 80px 20px;
      text-align: center;
    }

    .section-rosa {
      background-color: #fce4ec;
    }

    .section-blanca {
      background-color: #fff;
    }

    @media (max-width: 768px) {
      .novios {
        font-size: 2em;
      }
      .fecha {
        font-size: 1.2em;
      }
    }

    .contador-seccion {
      height: 100vh;
      background-image: url('{{ asset('images/boda/contador.png') }}');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      text-align: center;
      position: relative;
    }

    .contador-seccion::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      z-index: 0;
    }

    .contador-contenido {
      position: relative;
      z-index: 1;
    }

    .titulo-contador {
      font-size: 3em;
      font-family: 'Great Vibes', cursive;
      margin-bottom: 10px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.6);
    }

    .contador-estilo {
      font-size: 2.5em;
      font-family: 'Georgia', serif;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.6);
      margin: 10px 0;
    }

    .contador-estilo span {
      font-size: 0.5em;
      font-weight: normal;
      margin-right: 8px;
    }

    .subtitulo-contador {
      font-size: 1.8em;
      font-family: 'Great Vibes', cursive;
      margin-top: 10px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.6);
    }
  </style>

  <style>
.portada-invitacion {
  position: relative;
  width: 100%;
  max-width: 500px;
  margin: 50px auto;
  text-align: center;
  font-family: 'Georgia', serif;
  color: #5a5a5a;
}

/* Anillo central */
.contenedor-anillo {
  position: relative;
  width: 100%;
  padding-top: 100%; /* proporción cuadrada */
}

.anillo {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 95%;
  height: 95%;
  transform: translate(-50%, -50%);
  object-fit: contain;
  z-index: 1;
}


/* Texto dentro del anillo */
.nombres {
  position: absolute;
  top: 50%;
  left: 50%;
  z-index: 2;
  transform: translate(-50%, -50%);
  font-size: 2rem;
  line-height: 1.5;
}

/* Decoraciones */
.decoracion-arriba,
.decoracion-abajo {
  position: absolute;
  width: 100%;
  max-width: 500px;
  left: 50%;
  transform: translateX(-50%);
  pointer-events: none;
}

.decoracion-lateral1 {
  position: absolute;
  width: 100%;
  max-width: 500px;
  left: 50%;
  transform: translateX(-50%);
  pointer-events: none;
}

.decoracion-arriba {
  top: 0;
  animation: flotar 15s ease-in-out infinite;
}

.decoracion-abajo {
  bottom: 0;
  animation: flotar-reversa 18s ease-in-out infinite;
    bottom: 146px;
  left: 17px;
   width: 63%;
  transform: scaleX(-1) scaleY(-1); /* voltear horizontal y vertical */
}

.decoracion-lateral1 {
  bottom: 0;
  animation: flotar-reversa 18s ease-in-out infinite;
    bottom: 263px;
  left: -41px;
   width: 41%;
  transform: scaleX(-1) scaleY(-1); /* voltear horizontal y vertical */
}

/* Texto inferior */
.texto-inferior {
  margin-top: 85px;
}

.texto-inferior .titulo {
  font-weight: bold;
  font-size: 2rem;
  margin-bottom: 0.3rem;
}

.texto-inferior .fecha {
  font-size: 1.5rem;
  letter-spacing: 2px;
}

.hoja-decorativa {
  position: absolute;
  top: -29px;
  right: 28px;
  width: 66%;
  pointer-events: none;
}

/* Animación tipo viento */
@keyframes wind {
  0%, 100% {
    transform: rotate(0deg) translateX(0px) translateY(0px) scale(1);
  }
  25% {
    transform: rotate(1.5deg) translateX(2px) translateY(-2px) scale(1.01);
  }
  50% {
    transform: rotate(0deg) translateX(1px) translateY(1px) scale(1.005);
  }
  75% {
    transform: rotate(-1.5deg) translateX(-2px) translateY(2px) scale(1.01);
  }
}

.animate-wind {
  animation: wind 8s ease-in-out infinite;
}




  </style>
</head>
<body>

  <!-- Pantalla inicial con el sobre -->
<!-- Pantalla inicial con el sobre -->
<div class="pantalla-inicial" id="pantallaInicial">
  <div class="sobre" onclick="abrirInvitacion()">
    <!-- Imagen de fondo del sobre -->
    <img class="img" src="{{ asset('images/boda/entrada.jpg') }}" alt="Sobre de invitación">

    <!-- Contenido superpuesto -->
    <div class="contenido-superpuesto __className_f98ef7">
      <div class="flex h-96 w-full flex-col items-center justify-center gap-6 text-3xl leading-10 sm:text-4xl">
        <div class="relative w-full overflow-visible text-center">
          <p class="__className_f98ef7 nombres-principales" style="font-size: 2.25rem; margin: 0;">Javi</p>
          <p class="__className_f98ef7 nombres-principales" style="font-size: 2.25rem; margin: 0;">&amp;</p>
          <p class="__className_f98ef7 nombres-principales" style="font-size: 2.25rem; margin: 0;">Maite</p>
        </div>
        <br>
        <div class="flex flex-col items-center" style="margin-top: 50px;">
          <p class="text-2xl" style="font-size: 1.5rem; margin: 0;">Tenemos una noticia...</p>
          <div class="icono-container">
            <img class="bounce click-icon" src="images/boda/click.png" alt="clic">
          </div>
          <p class="text-sm">¡ Haz clic !</p>
        </div>
      </div>
    </div>
  </div>
</div>


<style>
  .adios{
    opacity: 0;
  }

  .adios2 {
  animation: fadeGrow 3.2s ease-out forwards;
}

</style>


    <!-- Pantalla inicial con el sobre -->
<!-- Pantalla inicial con el sobre -->
<div class="portada-invitacion __className_f98ef7 oculta" id="portadaInvitacion" style="margin-bottom: 0px!important">

  <!-- Decoración superior -->
  <img src="{{ asset('images/boda/hoja1.png') }}" alt="🌿 hoja decorativa"
  class="hoja-decorativa animate-wind">

  <!-- Anillo con nombres -->
  <div class="contenedor-anillo">
    <img src="{{ asset('images/boda/ring.png') }}" alt="anillo" class="anillo">

    <div class="nombres">
          <p class="adios fadee-out  __className_f98ef7" style="font-size: 3.25rem; margin: 0;">Javi</p>
          <p class="adios fadee-out  __className_f98ef7" style="font-size: 3.25rem; margin: 0;">&amp;</p>
          <p class="adios fadee-out  __className_f98ef7" style="font-size: 3.25rem; margin: 0;">Maite</p>
    </div>
  </div>

  <!-- Decoración inferior -->
  <img src="{{ asset('images/boda/hoja2.png') }}" alt="decoración inferior" class="decoracion-abajo animate-wind">
  <img src="{{ asset('images/boda/hoja3.png') }}" alt="decoración inferior" class="decoracion-lateral1 animate-wind">

  <!-- Texto inferior -->
  <div class="texto-inferior" style="width: 390px;height: 84px">
    <p class="adios fadee-out  titulo">NOS CASAMOS</p>
    <p class="adios fadee-out  fecha">— 18.10.2025 —</p>
  </div>
</div>

<style>
.portada {
  position: relative;
  width: 100%;
  max-width: 600px; /* Ajusta al tamaño deseado */
  margin: auto;
  overflow: hidden;
}

.portada-contenido {
  position: relative;
  width: 100%;
}

.imagen-base {
  width: 100%;
  display: block;
}

.imagen-borde {
  position: absolute!important;
  bottom: 0!important;
  left: 0!important;
  width: 100%!important;
  pointer-events: none!important;
}

.portada {
  position: relative;
  width: 100%;
  max-width: 600px;
  margin: auto;
  overflow: hidden;
}

.portada-contenido {
  position: relative;
  width: 100%;
}

.imagen-base {
  width: 100%;
  display: block;
}

.imagen-borde {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  pointer-events: none;
  z-index: 1;
}

/* NUEVO: Contenido encima del borde */
.texto-superpuesto {
  position: absolute;
  bottom: -106px; /* ajusta según necesidad */
  width: 100%;
  text-align: center;
  z-index: 2;
 /*  padding: 20px; */
  color: #5a5a5a;
  font-family: 'Great Vibes', cursive;
}

.frase {
  font-size: 1.5rem;
  margin-top: 10px;
  font-family: 'Georgia', serif;
  margin-bottom: -3px;
}

.icono-hoja {
  width: 130px;
    display: block;
  margin-bottom: 10px;
  opacity: 0.8;
      margin-left: 218px;
    margin-bottom: 24px;
}




</style>

<div class="fade-in portada" style="height: 880px">
  <div class="fade-in portada-contenido">
    <!-- Imagen de fondo -->
    <img style="margin-bottom: 5px" class="imagen-base" src="{{ asset('images/boda/foto1.jpg') }}" alt="foto pareja">

    <!-- Imagen con borde roto transparente -->
    <img class="imagen-borde" src="{{ asset('images/boda/photo-border-2.png') }}" alt="borde decorativo">

    <!-- Contenido encima del borde roto -->
<div class="texto-superpuesto">
  <img src="{{ asset('images/boda/dec-flower.png') }}" class="icono-hoja" alt="hojita decorativa">
  <p class="frase" style="font-size: 21px; text-align: center;">
    Después de 9 años,<br>
    entre huellas y pañales,<br>
    hemos decidido dar el paso más importante:<br>
    <strong>¡NOS CASAMOS!</strong>
  </p>
</div>

  </div>
</div>

<style>
  .section-container {
  position: relative;
  width: 100%;
  color: white;
  overflow: hidden;
  padding: 60px 0;
  text-align: center;
}

.decoracion-superior {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 400px;
  margin-top: -26px;
}

.decoracion-superior2 {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 400px;
  margin-top: 2px;
}


.decoracion-superior3 {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 400px;
  margin-top: -14px;
}

.decoracion-inferior {
  position: absolute;
  bottom: 0;
  left: -16%;
  width: 497px;
  margin-bottom: -35px;
}

.decoracion-inferior2 {
  position: absolute;
  bottom: 0;
  left: -16%;
  width: 497px;
  margin-bottom: -33px;
}

.contenido {
  position: relative;
  z-index: 10;
  padding: 0 24px;
}

.mensaje {
  font-size: 28px;
  font-style: italic;
  line-height: 1.6;
  font-size: xx-large;
}

.flor-decorativa {
  margin-top: 16px;
  width: 96px;
}

.negro{
  color: white
}

</style>

<div class="section-container fade-in negro" style="margin-top: 100px;">
  <!-- Imagen decorativa superior -->
  <img src="{{ asset('images/boda/divisor.png') }}" alt="decoración superior" class="decoracion-superior">

  <section class="contenido" style="  background-color: #9aa5a5;">
    <div class="mensaje">
      Queremos que nos acompañes en<br>
      este día tan especial
    </div>

    <img src="{{ asset('images/boda/dec-flower2.png') }}" alt="Decoración floral" class="flor-decorativa">
  </section>

  <!-- Imagen decorativa inferior -->
  <img src="{{ asset('images/boda/divisor2.png') }}" alt="decoración inferior" class="decoracion-inferior">
</div>






<section class="fade-in section-blanca"  style="padding-bottom: 0px;padding-top: 20px;">
  <div style="max-width: 800px; margin: 0 auto; text-align: center;">
    <h2 class="__className_f98ef7" style="font-size: 2.5em; margin-bottom: 10px;">
      Ceremonia Religiosa
    </h2>
    <br>
    <img src="{{ asset('images/boda/iglesia.jpeg') }}" alt="Lugar de la ceremonia"
         style="width: 100%; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); margin-bottom: 20px;">
    
    <p style="font-size: 1.2em; margin: 10px 0;"><strong>Parroquia Ntra. Sra. De la Fuensanta</strong></p>
     <p style="font-size: 1em; margin: 5px 0;">( Patiño- Murcia)</p>
    <p style="font-size: 1.1em; margin: 5px 0;">11:30 hrs</p>
   

    <a href="https://www.google.es/maps/place/Parroquia+de+Ntra.+Sra.+de+la+Fuensanta/@37.9503922,-1.1532766,6282m/data=!3m1!1e3!4m10!1m2!2m1!1sParroquia+Ntra.+Sra.+De+la+Fuensanta!3m6!1s0xd6378b031a72e5b:0xd39481bf1bf212a7!8m2!3d37.9616103!4d-1.1276479!15sCiRQYXJyb3F1aWEgTnRyYS4gU3JhLiBEZSBsYSBGdWVuc2FudGFaJCIicGFycm9xdWlhIG50cmEgc3JhIGRlIGxhIGZ1ZW5zYW50YZIBD2NhdGhvbGljX2NodXJjaJoBI0NoWkRTVWhOTUc5blMwVkpRMEZuU1VOdFh6ZElOVUpSRUFFqgGCAQoNL2cvMTFsNjV4N3d3MhABKiYiInBhcnJvcXVpYSBudHJhIHNyYSBkZSBsYSBmdWVuc2FudGEoDjIfEAEiG_Xu5F0C0E6mvB4iqi-_tTCPuTAisZb_RtGRBDImEAIiInBhcnJvcXVpYSBudHJhIHNyYSBkZSBsYSBmdWVuc2FudGHgAQD6AQQIABA7!16s%2Fg%2F11gfhj5lp8?entry=ttu&g_ep=EgoyMDI1MDYzMC4wIKXMDSoASAFQAw%3D%3D" target="_blank" style="display: inline-block; margin: 15px 0;">
      <img src="{{ asset('images/boda/map-locator.png') }}" alt="Ver mapa en Google"
           style="width: 80px; height: auto; display: block; margin: 0 auto;">
    </a>

  </div>
</section>

<style>
.slide-in-right {
  opacity: 0;
  transform: translateX(200px); /* desplazamiento lateral */
  transition: opacity 4s ease, transform 4s ease;
}

.slide-in-right.visible {
  opacity: 1;
  transform: translateX(0);
}





</style>

<div class="section-container fade-in negro" style="margin-top: 25px;">
  <!-- Imagen decorativa superior -->
  <img src="{{ asset('images/boda/divisor.png') }}" alt="decoración superior" class="decoracion-superior3">

  <section class="contenido" style="  background-color: #9aa5a5;padding:5px;width: 383px;
    height: 90px;">
<img src="{{ asset('images/boda/dec-flower2.png') }}"
     alt="Decoración floral"
     class="slide-in-right"
     style="width: 115px!important;margin-top:16px"
     id="florDecorativa">




      
  </section>

  <!-- Imagen decorativa inferior -->
  <img src="{{ asset('images/boda/divisor2.png') }}" alt="decoración inferior" class="decoracion-inferior2">
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const target = document.getElementById('florDecorativa');

    if (!target) {
      console.warn('No se encontró el elemento con ID florDecorativa');
      return;
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          console.log('🌸 Imagen visible, activando animación');
          target.classList.add('visible');
          observer.unobserve(target);
        }
      });
    }, {
      threshold: 0.1
    });

    observer.observe(target);
  });
</script>



{{-- <div class="section-container fade-in" style="margin-top: 100px;">
  <!-- Imagen decorativa superior -->
  <img src="{{ asset('images/boda/divisor.png') }}" alt="decoración superior" class="decoracion-superior2">

  <section class="contenido" style="  background-color: #9aa5a5;">
    <div class="mensaje">
      <h1 style="font-size: 40px;" class="__className_f98ef7 text-3xl font-cursive mb-2">Dress Code</h1>
      <p class="text-gray-800 mb-4">El blanco está reservado para la novia,<br>¡sorpréndenos con otros colores!</p>
    </div>

    <img src="{{ asset('images/boda/dress-code.png') }}" alt="Decoración floral" class="flor-decorativa" style="120px!important">
  </section>

  <!-- Imagen decorativa inferior -->
  <img src="{{ asset('images/boda/divisor2.png') }}" alt="decoración inferior" class="decoracion-inferior">
</div> --}}

<section class="fade-in section-blanca" style="padding-bottom: 0px;    padding-top: 40px;"> 
  <div style="max-width: 800px; margin: 0 auto; text-align: center;">
    <h2 class="__className_f98ef7" style="font-size: 2.5em; margin-bottom: 10px;">
      Restaurante
    </h2>
    <br>
    <img src="{{ asset('images/boda/restaurante.jpeg') }}" alt="Lugar de la ceremonia"
         style="width: 100%; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); margin-bottom: 20px;">
    
    <p style="font-size: 1.2em; margin: 10px 0;"><strong>Restaurante Cason de la Vega. Santomera</strong></p>

    <a href="https://www.google.es/maps/place/El+Cas%C3%B3n+de+la+Vega/@38.0838264,-1.0597009,907m/data=!3m1!1e3!4m14!1m7!3m6!1s0xd639b4b62496b11:0x6a007b2bb94835ec!2sEl+Cas%C3%B3n+de+la+Vega!8m2!3d38.0838264!4d-1.057126!16s%2Fg%2F1td2cpbg!3m5!1s0xd639b4b62496b11:0x6a007b2bb94835ec!8m2!3d38.0838264!4d-1.057126!16s%2Fg%2F1td2cpbg?entry=ttu&g_ep=EgoyMDI1MDYzMC4wIKXMDSoASAFQAw%3D%3D" target="_blank" style="display: inline-block; margin: 15px 0;">
      <img src="{{ asset('images/boda/map-locator.png') }}" alt="Ver mapa en Google"
           style="width: 80px; height: auto; display: block; margin: 0 auto;">
    </a>

  </div>
</section>





<div class="section-container fade-in negro" style="margin-top: 100px;">
  <!-- Imagen decorativa superior -->
  <img src="{{ asset('images/boda/divisor.png') }}" alt="decoración superior" class="decoracion-superior3">

  <section class="contenido" style="  background-color: #9aa5a5;padding:5px">
    <h1 style="margin-bottom: 1rem; font-family: 'Airthay', cursive; font-size: 4rem; font-weight: normal;">
      Regalo
    </h1>

    <div style="padding: 0 1rem; max-width: 800px; margin: 0 auto; font-size: 1.1em;">
      Nuestro mayor regalo es vuestra presencia, pero si quieres ayudar, aquí está la cuenta bancaria:
    </div>

    <div style="margin-top: 1rem; padding: 0 1rem;">
      <button onclick="copiarCuenta()" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin: 0 auto; background: none; border: none; cursor: pointer;">
        <img width="30" src="{{ asset('images/boda/copy.svg') }}" alt="copy">
        <div style="text-align: left;">
          <p style="margin: 0; font-size: 0.9em; color: rgba(0, 0, 0, 0.8);">Nuria Rodríguez</p>
          <p style="margin: 0; font-weight: bold; color: rgba(0, 0, 0, 0.8);" id="gift-num">ES82 0182 5319 7002 03 268016</p>
        </div>
      </button>
      <p style="text-align: center; font-size: 0.9em; margin-top: 0.5rem;">
        Concepto: <strong>BODA</strong> y nombre de la persona
      </p>
      <p id="copiado-msg" style="text-align: center; font-size: 0.9em; color: white; margin-top: 0.5rem; display: none;">¡Número de cuenta copiado!</p>
    </div>
  </section>

  <!-- Imagen decorativa inferior -->
  <img src="{{ asset('images/boda/divisor2.png') }}" alt="decoración inferior" class="decoracion-inferior2">
</div>

  <section class="fade-in" style="background-color: transparent; color: #575757; text-align: center; padding-bottom: 2rem; position: relative; padding-top:0px">
  <h1 style="color: #879696; margin-bottom: 1rem; font-family: 'Airthay', cursive; font-size: 4rem; font-weight: normal; opacity: 1;">
    Itinerario
  </h1>

  <div class="relative w-full pb-6">
    <!-- Ceremonia -->
    <img class="mx-auto" src="{{ asset('images/boda/tent.png') }}" alt="Ceremonia" width="100" style="opacity: 1;">
    <p class="mb-6" style="padding: 0 4rem; letter-spacing: 0.05em; opacity: 1;">
      <span>18:00 h</span> - <span>Ceremonia</span>
    </p>

    <!-- Cocktail -->
    <img class="mx-auto" src="{{ asset('images/boda/cocktail.png') }}" alt="Cocktail" width="100" style="opacity: 1;">
    <p class="mb-6" style="padding: 0 4rem; letter-spacing: 0.05em; opacity: 1;">
      <span>19:30 h</span> - <span>Cocktail</span>
    </p>

    <!-- Banquete -->
    <img class="mx-auto" src="{{ asset('images/boda/food.png') }}" alt="Banquete" width="100" style="opacity: 1;">
    <p class="mb-6" style="padding: 0 4rem; letter-spacing: 0.05em; opacity: 1;">
      <span>20:30 h</span> - <span>Banquete</span>
    </p>

    <!-- Fiesta -->
    <img class="mx-auto" src="{{ asset('images/boda/dance.png') }}" alt="Fiesta" width="100" style="opacity: 1;">
    <p class="mb-6" style="padding: 0 4rem; letter-spacing: 0.05em; opacity: 1;">
      <span>23:00 h</span> - <span>Fiesta</span>
    </p>
  </div>
</section>

{{-- <section class="relative grid place-items-center gap-4 px-8 py-12 md:px-16 bg-cover bg-center" style="background-image: url('{{ asset('images/boda/bg-xl.png') }}');">
  <!-- Fondo blanco semitransparente para mejor lectura -->
  <div class="bg-white bg-opacity-70 p-6 rounded-xl text-center max-w-md">
    <h1 style="font-size: 40px;" class="__className_f98ef7 text-3xl font-cursive mb-2">Dress Code</h1>
    <p class="text-[#cb717e] text-xl font-semibold mb-2">Formal</p>
    <p class="text-gray-800 mb-4">El blanco está reservado para la novia,<br>¡sorpréndenos con otros colores!</p>
    <img src={{ asset('images/boda/dress-code.png') }} alt="dress clothes" class="mx-auto w-24 opacity-90" style="width: 210px">
  </div>
</section> --}}


  <!-- Imagen decorativa -->



{{--   <section style="background-color: #9aa5a5; color: #575757; padding: 1rem 0;">
  <div style="text-align: center;">
    <h1 style="color: white; margin-bottom: 1rem; font-family: 'Airthay', cursive; font-size: 4rem; font-weight: normal;">
      Regalo
    </h1>

    <div style="padding: 0 1rem; max-width: 800px; margin: 0 auto; font-size: 1.1em;">
      Nuestro mayor regalo es vuestra presencia, pero si quieres ayudar, aquí está la cuenta bancaria:
    </div>

    <div style="margin-top: 1rem; padding: 0 1rem;">
      <button onclick="copiarCuenta()" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin: 0 auto; background: none; border: none; cursor: pointer;">
        <img width="30" src="{{ asset('images/boda/copy.svg') }}" alt="copy">
        <div style="text-align: left;">
          <p style="margin: 0; font-size: 0.9em; color: rgba(0, 0, 0, 0.8);">Nuria Rodríguez</p>
          <p style="margin: 0; font-weight: bold; color: rgba(0, 0, 0, 0.8);" id="gift-num">ES82 0182 5319 7002 03 268016</p>
        </div>
      </button>
      <p style="text-align: center; font-size: 0.9em; color: #ffffff; margin-top: 0.5rem;">
        Concepto: <strong>BODA</strong> y nombre de la persona
      </p>
      <p id="copiado-msg" style="text-align: center; font-size: 0.9em; color: white; margin-top: 0.5rem; display: none;">¡Número de cuenta copiado!</p>
    </div>
  </div>
</section> --}}


<script>
  function copiarCuenta() {
    const cuenta = document.getElementById("gift-num").textContent;
    navigator.clipboard.writeText(cuenta).then(() => {
      document.getElementById("copiado-msg").style.display = 'block';
      setTimeout(() => {
        document.getElementById("copiado-msg").style.display = 'none';
      }, 2000);
    });
  }
</script>

</script>

<section class="fade-in" style="padding: 0; margin: 0;margin-top: 130px;">
  <div style="margin: 0; padding: 0;">
    <img src="{{ asset('images/boda/anillo.jpeg') }}" alt="Lugar de la ceremonia"
         style="width: 100%; display: block;">
  </div>
</section>

{{-- <section class="fade-in" style="background-color: #a7b3af; padding: 2rem 1rem; text-align: center; font-family: 'Georgia', serif;">
  <h2 style="color: white; font-size: 1rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2rem;">
    Nos vemos en...
  </h2>

  <div id="contador" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
    <!-- JS insertará aquí los 4 bloques -->
  </div>
</section> --}}


<div class="section-container fade-in negro" style="margin-top: 100px;">
  <!-- Imagen decorativa superior -->
  <img src="{{ asset('images/boda/divisor.png') }}" alt="decoración superior" class="decoracion-superior3">

  <section class="contenido" style="  background-color: #9aa5a5;padding:5px">
  <h2 style="font-size: 1rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2rem;">
    Nos vemos en...
  </h2>

  <div id="contador" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
    <!-- JS insertará aquí los 4 bloques -->
  </div>
  </section>

  <!-- Imagen decorativa inferior -->
  <img src="{{ asset('images/boda/divisor2.png') }}" alt="decoración inferior" class="decoracion-inferior2">
</div>











      <section class="fade-in section-blanca" style="padding: 0px">
  <div style="max-width: 600px; margin: 0 auto; text-align: left; padding: 30px; border-radius: 15px; background-color: #fff; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
    <h2 style="text-align: center; font-size: 2em; margin-bottom: 20px;">Confirmación de asistencia</h2>
    
    <form action="#" method="POST">
      <!-- Nombre y Apellidos -->
      <div style="margin-bottom: 20px;">
        <label for="nombre" style="display: block; font-weight: bold; margin-bottom: 5px;">Nombre y Apellidos</label>
        <input type="text" id="nombre" name="nombre" required
               style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 1em;">
      </div>

      <!-- Confirmación de asistencia -->
      <div style="margin-bottom: 20px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">¿Vas a asistir?</label>
        <label style="display: inline-flex; align-items: center; margin-right: 20px;">
          <input type="radio" name="asistencia" value="sí" required style="margin-right: 8px;"> Sí
        </label>
        <label style="display: inline-flex; align-items: center;">
          <input type="radio" name="asistencia" value="no" required style="margin-right: 8px;"> No
        </label>
      </div>

      <!-- Alergias / Intolerancias -->
      <div style="margin-bottom: 20px;">
        <label for="alergias" style="display: block; font-weight: bold; margin-bottom: 5px;">¿Tienes alguna alergia o intolerancia?</label>
        <textarea id="alergias" name="alergias" rows="3"
                  placeholder="Ej. gluten, lactosa, frutos secos..."
                  style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 1em;"></textarea>
      </div>

      <!-- Botón de enviar -->
      <div style="text-align: center;">
        <button type="submit"
                style="padding: 12px 25px; background-color: #c2185b; color: white; font-size: 1em;
                       border: none; border-radius: 25px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
          ENVIAR RESPUESTA
        </button>
      </div>
    </form>
  </div>
</section>

  



  <!-- Scripts -->
<audio id="audioBoda" preload="auto">
  <source src="{{ asset('audio/song1.mp3') }}" type="audio/mpeg">
  Tu navegador no soporta el audio.
</audio>

<script>
function abrirInvitacion() {
  const pantalla = document.getElementById('pantallaInicial');
  pantalla.classList.add('oculto');
  setTimeout(() => {
    pantalla.style.display = 'none';

    // Mostrar portada con animación
    const portada = document.getElementById('portadaInvitacion');
    portada.classList.remove('oculta');
    portada.classList.add('mostrar');
  }, 2000);

setTimeout(() => {
  document.querySelectorAll('.adios').forEach(el => {
    el.classList.remove('adios');
    el.classList.add('adios2');
  });
}, 3000);



  // Reproducir música al abrir
  const audio = document.getElementById('audioBoda');
  audio.play().catch((e) => {
    console.log("Error al reproducir música automáticamente:", e);
  });
}


  // Contador regresivo
function actualizarContador() {
    const fechaBoda = new Date("2025-10-19T00:00:00").getTime();
    const ahora = new Date().getTime();
    const diferencia = fechaBoda - ahora;

    const contador = document.getElementById("contador");

    if (diferencia <= 0) {
      contador.innerHTML = "<span style='font-size: 1.5rem; color: white;'>¡Es el gran día!</span>";
      return;
    }

    const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));
    const horas = Math.floor((diferencia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
    const segundos = Math.floor((diferencia % (1000 * 60)) / 1000);

    const bloques = [
      { valor: dias, texto: 'Días' },
      { valor: horas, texto: 'Horas' },
      { valor: minutos, texto: 'Minutos' },
      { valor: segundos, texto: 'Segundos' }
    ];

    contador.innerHTML = bloques.map(bloque => `
      <div style="
        background-color: white;
        padding: 0.4rem;
        border-radius: 8px;
        width: 24%;
        max-width: 24%;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        box-sizing: border-box;
      ">
        <span style="font-size: 1rem; font-weight: bold; color: #333;">${bloque.valor}</span>
        <span style="font-size: 0.65rem; color: #777;">${bloque.texto}</span>
      </div>
    `).join('');
  }



  setInterval(actualizarContador, 1000);
  actualizarContador();
</script>


</body>

<script>
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
</script>
</html>
