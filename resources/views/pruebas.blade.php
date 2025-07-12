<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invitación</title>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Georgia&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">

<style>
@media (max-width: 399px) {

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
  overflow: hidden;
  display: block;
  }

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
  
  .nombres-principales {
    font-size: 1.8rem !important;
  }
  @font-face {
    font-family: '__Parisienne_f98ef7';
    src: url('images/boda/fonts/Parisienne-Regular.ttf') format('truetype');
    font-weight: 400;
    font-style: normal;
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

    .portada-contenido {
      position: relative;
      z-index: 1;
   /*    padding: 20px; */
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

  .adios{
    opacity: 0;
  }

  .adios2 {
  animation: fadeGrow 3.2s ease-out forwards;
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
    bottom: -91px; /* ajusta según necesidad */
    width: 100%;
    text-align: center;
    z-index: 2;
  /*  padding: 20px; */
    color: #5a5a5a;
  }

  .frase {
    font-size: 1.5rem;
    margin-top: 10px;
    margin-bottom: 122px;
  }

  .icono-hoja {
    width: 130px;
      display: block;
    margin-bottom: 10px;
    opacity: 0.8;
        margin-left: 218px;
      margin-bottom: 42px;
  }
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
    line-height: 1.0;
    font-size: xx-large;
  }

  .flor-decorativa {
    margin-top: 16px;
    width: 96px;
  }

  .negro{
    color: white
  }

  .q1{
    padding-top: 20px
  }

  .q2{
    margin: 0;
  }

  .x1{
    margin-top: 50px;
  }

  .q3{
    font-size: 1.5rem; margin: 0;
  }
  .q4{
    margin-bottom: 0px!important
  }
  .q5{
    font-size: 3.25rem; margin: 0;
  }
  .q6{
    width: 390px;height: 84px
  }
  .q7{
    margin-left: -24px;
  }
  .q8{
    height: 770px
  }
  .x3{
    margin-bottom: 480px;
    width: 790px!important;
    margin-left: -161px;transform: rotate(185deg);
  }
  .q9{
    margin-bottom: 288px;background: #eae1d2;
    padding-bottom: 50px;padding-top: 50px;
  }
  .w1{
    margin-bottom: 94px;
    width: 790px!important;
    margin-left: -161px;transform: rotate(1deg);
  }
  .w2{
    font-size: 26px; text-align: center;
  }
  .w3{
    font-size: 42px;
  }
  .fondo1{
     background-color: #9aa5a5;
  }
  .w4{
    padding-bottom: 0px;padding-top: 0px;
  }
  .w5{
    max-width: 800px; margin: 0 auto; text-align: center;
  }
  .w6{
    font-size: 2.5em; margin-bottom: 10px;
  }
  .w7{
    width: 95%; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); margin-bottom: 20px;
  }
  .w8{
    font-size: 1.7em; margin: 10px 0
  }
  .w9{
    font-size: 1em; margin: 5px 0;
  }
  .w10{
    font-size: 1.1em; margin: 5px 0;
  }
  .e1{
    width: 80px; height: auto; display: block; margin: 0 auto;
  }
  .slide-in-right {
    opacity: 0;
    transform: translateX(200px); /* desplazamiento lateral */
    transition: opacity 4s ease, transform 4s ease;
  }

  .slide-in-right.visible {
    opacity: 1;
    transform: translateX(0);
  }
  .e2{
    margin-top: 25px;
  }

  .e3{
    background-color: #9aa5a5;padding:5px;width: 380px;
    height: 90px;
  }
  .e4{
    width: 115px!important;margin-top:16px
  }

  .e5{
    padding-bottom: 0px;    padding-top: 20px;
  }
  .e6{
    max-width: 800px; margin: 0 auto; text-align: center;
  }
  .e7{
    width: 100%; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); margin-bottom: 20px;
  }
  .e8{
    font-size: 1.7em; margin: 10px 0;
  }
  .e9{
    width: 80px; height: auto; display: block; margin: 0 auto;
  }
  .r1{
    margin-top: 30px;
  }
  .r2{
    background-color: #9aa5a5;padding:5px
  }
  .r3{
    padding: 0 1rem; max-width: 800px; margin: 0 auto; font-size: 1.8em;
  }
  .r4{
    margin-top: 1rem; padding: 0 1rem;
  }
  .r5{
    display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin: 0 auto; background: none; border: none; cursor: pointer;
  }
  .r6{
    text-align: left;
  }
  .r7{margin: 0; font-size: 0.9em; color: rgba(0, 0, 0, 0.8);}
  .r8{margin: 0; font-weight: bold; color: rgba(0, 0, 0, 0.8);}
  .r9{text-align: center; font-size: 0.9em; margin-top: 0.5rem;font-family: Ariel;}

  .t1{text-align: center; font-size: 0.9em; color: white; margin-top: 0.5rem; display: none;}
  .t2{background-color: transparent; color: #575757; text-align: center; padding-bottom: 2rem; position: relative; padding: 0px 20px}
  .t3{color: #879696;
    margin-bottom: 1rem;
    font-size: 3.2rem;
    opacity: 1;
    font-weight: 500;}
  .t4{padding: 0 4rem; letter-spacing: 0.05em; opacity: 1;}
  .t5{padding: 0 4rem; letter-spacing: 0.05em; opacity: 1;}
  .t6{padding: 0 4rem; letter-spacing: 0.05em; opacity: 1;}
  .t7{padding: 0 4rem; letter-spacing: 0.05em; opacity: 1;}
  .t8{width: 100%; display: block;width: 100%; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);margin-bottom: 25px;}
  .t9{top: 439px;
    left: 50%;
    transform: translateX(-50%);
    width: 644px;}

.y1{font-size: 2rem; letter-spacing: 2px; margin-bottom: 2rem;font-weight: 400;}
.y2{background-color: #9aa5a5;padding:5px}
.y3{display: flex; justify-content: space-between; align-items: center; width: 100%;}
.y4{padding: 0px;margin-top: 40px;}
.y5{top: -12px;
    left: 76%;
    transform: translateX(-50%);
    width: 100px;
    transform: rotate(47deg);}
.y6{top: -12px;
    left: -1%;
    transform: translateX(-50%);
    width: 100px;
        transform: rotate(185deg);}
.y7{max-width: 600px; margin: 0 auto; text-align: left; padding: 30px; border-radius: 15px; background-color: #fff; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);}    
.y8{text-align: center; font-size: 2em; margin-bottom: 20px;font-weight: 400;}
.y9{margin-bottom: 20px;}



.u1{display: block;margin-bottom: 5px;}
.u2{width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 1em;}
.u3{margin-bottom: 20px;}
.u4{display: block; margin-bottom: 5px;}
.u5{display: inline-flex; align-items: center; margin-right: 20px;}
.u6{}
.u7{}
.u8{}
.u9{}

}








































@media (min-width: 380px) and (max-width: 399px) {
  .x2 {
    margin-left: -5px;
  }
}








@media (min-width: 410px) {
 .x2{margin-left: 0px;}

}









@media (min-width: 400px) {

  .x1{
    margin-top: 48px;
    margin-bottom: 31px;
  }

  .x2{margin-left: 0px;}

.x3{
  margin-bottom: 490px!important;

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
  overflow: hidden;
  display: block;
  }

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
  
  .nombres-principales {
    font-size: 2.3rem !important;
  }
  @font-face {
    font-family: '__Parisienne_f98ef7';
    src: url('images/boda/fonts/Parisienne-Regular.ttf') format('truetype');
    font-weight: 400;
    font-style: normal;
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

    .portada-contenido {
      position: relative;
      z-index: 1;
   /*    padding: 20px; */
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

  .adios{
    opacity: 0;
  }

  .adios2 {
  animation: fadeGrow 3.2s ease-out forwards;
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
    bottom: -91px; /* ajusta según necesidad */
    width: 100%;
    text-align: center;
    z-index: 2;
  /*  padding: 20px; */
    color: #5a5a5a;
  }

  .frase {
    font-size: 1.5rem;
    margin-top: 10px;
    margin-bottom: 122px;
  }

  .icono-hoja {
    width: 130px;
      display: block;
    margin-bottom: 10px;
    opacity: 0.8;
        margin-left: 218px;
      margin-bottom: 42px;
  }
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
    width: 466px;
    margin-top: -29px;
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
    line-height: 1.0;
    font-size: xx-large;
  }

  .flor-decorativa {
    margin-top: 16px;
    width: 96px;
  }

  .negro{
    color: white
  }
 
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
        <div class="relative w-full overflow-visible text-center q1";>
          <p class="__className_f98ef7 nombres-principales q2">Javi</p>
          <p class="__className_f98ef7 nombres-principales q2">&amp;</p>
          <p class="__className_f98ef7 nombres-principales q2">Maite</p>
        </div>
        <br>
        <div class="flex flex-col items-center x1" style="">
          <p class="text-2xl q3">Tenemos una noticia...</p>
          <div class="icono-container">
            <img class="bounce click-icon" src="images/boda/click.png" alt="clic">
          </div>
          <p class="text-sm">¡ Haz clic !</p>
        </div>
      </div>
    </div>
  </div>
</div>
    <!-- Pantalla inicial con el sobre -->
<!-- Pantalla inicial con el sobre -->
<div class="portada-invitacion __className_f98ef7 oculta q4" id="portadaInvitacion">

  <!-- Decoración superior -->
  <img src="{{ asset('images/boda/hoja1.png') }}" alt="🌿 hoja decorativa"
  class="hoja-decorativa animate-wind">

  <!-- Anillo con nombres -->
  <div class="contenedor-anillo">
    <img src="{{ asset('images/boda/ring.png') }}" alt="anillo" class="anillo">

    <div class="nombres">
          <p class="adios fadee-out  __className_f98ef7 q5">Javi</p>
          <p class="adios fadee-out  __className_f98ef7 q5">&amp;</p>
          <p class="adios fadee-out  __className_f98ef7 q5">Maite</p>
    </div>
  </div>

  <!-- Decoración inferior -->
  <img src="{{ asset('images/boda/hoja2.png') }}" alt="decoración inferior" class="decoracion-abajo animate-wind">
  <img src="{{ asset('images/boda/hoja3.png') }}" alt="decoración inferior" class="decoracion-lateral1 animate-wind">

  <!-- Texto inferior -->
  <div class="texto-inferior x2 q6">
    <p class="adios fadee-out  fecha q7">— 18.10.2025 —</p>
  </div>
</div>

<div class="fade-in portada q8">
  <div class="fade-in portada-contenido">
    <!-- Imagen de fondo -->
        <img class="imagen-borde x3" src="{{ asset('images/boda/photo-border-2.png') }}" alt="borde decorativo">

    <img class="imagen-base q9" src="{{ asset('images/boda/familia.jpg') }}" alt="foto pareja">
tan especial
    <!-- Imagen con borde roto transparente -->
    <img class="imagen-borde w1" src="{{ asset('images/boda/photo-border-2.png') }}" alt="borde decorativo">

    <!-- Contenido encima del borde roto -->
<div class="texto-superpuesto">
  <img src="{{ asset('images/boda/dec-flower.png') }}" class="icono-hoja" alt="hojita decorativa">
  <p class="frase w2">
    Después de nueve años,<br>
    entre huellas y pañales,<br>
    damos el paso:<br>
    <span class="w3">¡Nos casamos!</span>
  </p>
</div>

  </div>
</div>



<div class="section-container fade-in negro" style="margin-top: 0px;">
  <!-- Imagen decorativa superior -->
  <img src="{{ asset('images/boda/divisor.png') }}" alt="decoración superior" class="decoracion-superior">

  <section class="contenido fondo1">
    <div class="mensaje">
      Queremos que nos acompañes<br>
      en este día tan especial <br>
    </div>

    <img src="{{ asset('images/boda/dec-flower2.png') }}" alt="Decoración floral" class="flor-decorativa">
  </section>

  <!-- Imagen decorativa inferior -->
  <img src="{{ asset('images/boda/divisor2.png') }}" alt="decoración inferior" class="decoracion-inferior">
</div>






<section class="fade-in section-blanca w4">
  <div class="w5">
    <h2 class="__className_f98ef7 w6">
      Ceremonia Religiosa
    </h2>
    <br>
    <img src="{{ asset('images/boda/iglesia.jpeg') }}" alt="Lugar de la ceremonia" class="w7">
    
    <p class="w8">Parroquia Ntra. Sra. De la Fuensanta</p>
     <p class="w9">( Patiño- Murcia)</p>
    <p class="w10">11:30 hrs</p>
   

    <a href="https://www.google.es/maps/place/Parroquia+de+Ntra.+Sra.+de+la+Fuensanta/@37.9503922,-1.1532766,6282m/data=!3m1!1e3!4m10!1m2!2m1!1sParroquia+Ntra.+Sra.+De+la+Fuensanta!3m6!1s0xd6378b031a72e5b:0xd39481bf1bf212a7!8m2!3d37.9616103!4d-1.1276479!15sCiRQYXJyb3F1aWEgTnRyYS4gU3JhLiBEZSBsYSBGdWVuc2FudGFaJCIicGFycm9xdWlhIG50cmEgc3JhIGRlIGxhIGZ1ZW5zYW50YZIBD2NhdGhvbGljX2NodXJjaJoBI0NoWkRTVWhOTUc5blMwVkpRMEZuU1VOdFh6ZElOVUpSRUFFqgGCAQoNL2cvMTFsNjV4N3d3MhABKiYiInBhcnJvcXVpYSBudHJhIHNyYSBkZSBsYSBmdWVuc2FudGEoDjIfEAEiG_Xu5F0C0E6mvB4iqi-_tTCPuTAisZb_RtGRBDImEAIiInBhcnJvcXVpYSBudHJhIHNyYSBkZSBsYSBmdWVuc2FudGHgAQD6AQQIABA7!16s%2Fg%2F11gfhj5lp8?entry=ttu&g_ep=EgoyMDI1MDYzMC4wIKXMDSoASAFQAw%3D%3D" target="_blank" style="display: inline-block; margin: 15px 0;">
      <img src="{{ asset('images/boda/map-locator.png') }}" alt="Ver mapa en Google" class="e1">
    </a>

  </div>
</section>

<div class="section-container fade-in negro e2">
  <!-- Imagen decorativa superior -->
  <img src="{{ asset('images/boda/divisor.png') }}" alt="decoración superior" class="decoracion-superior3">

  <section class="contenido e3">
<img src="{{ asset('images/boda/dec-flower2.png') }}"
     alt="Decoración floral"
     class="slide-in-right e4"
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
          console.log('Imagen visible, activando animación');
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

<section class="fade-in section-blanca e5"> 
  <div class="e6">
    <h2 class="__className_f98ef7" style="font-size: 2.5em; margin-bottom: 10px;">
      Restaurante
    </h2>
    <br>
    <img src="{{ asset('images/boda/restaurante.jpeg') }}" alt="Lugar de la ceremonia" class="e7">
    
    <p class="e8">Restaurante Cason de la Vega. Santomera</p>

    <a href="https://www.google.es/maps/place/El+Cas%C3%B3n+de+la+Vega/@38.0838264,-1.0597009,907m/data=!3m1!1e3!4m14!1m7!3m6!1s0xd639b4b62496b11:0x6a007b2bb94835ec!2sEl+Cas%C3%B3n+de+la+Vega!8m2!3d38.0838264!4d-1.057126!16s%2Fg%2F1td2cpbg!3m5!1s0xd639b4b62496b11:0x6a007b2bb94835ec!8m2!3d38.0838264!4d-1.057126!16s%2Fg%2F1td2cpbg?entry=ttu&g_ep=EgoyMDI1MDYzMC4wIKXMDSoASAFQAw%3D%3D" target="_blank" style="display: inline-block; margin: 15px 0;">
      <img src="{{ asset('images/boda/map-locator.png') }}" alt="Ver mapa en Google" class="e9">

    </a>

  </div>
</section>





<div class="section-container fade-in negro r1">
  <!-- Imagen decorativa superior -->
  <img src="{{ asset('images/boda/divisor.png') }}" alt="decoración superior" class="decoracion-superior3">

  <section class="contenido r2">
    <div class="r3">
      Nuestro mayor regalo es vuestra presencia, pero si quieres ayudar, aquí está la cuenta bancaria:
    </div>

    <div class="r4">
      <button onclick="copiarCuenta()" class="r5">
        <img width="30" src="{{ asset('images/boda/copy.svg') }}" alt="copy">
        <div class="r6">
          <p class="r7">Maria Teresa Ruiz</p>
          <p class="r8" id="gift-num">ES82 0182 5319 7002 03 268016</p>
        </div>
      </button>
      <p class="r9">
        Concepto: <strong>BODA</strong> y nombre de la persona
      </p>
      <p id="copiado-msg" class="t1">¡Número de cuenta copiado!</p>
    </div>
  </section>

  <!-- Imagen decorativa inferior -->
  <img src="{{ asset('images/boda/divisor2.png') }}" alt="decoración inferior" class="decoracion-inferior2">
</div>

  <section class="fade-in t2">
  <h1 class="t3">
    Itinerario
  </h1>

  <div class="relative w-full pb-6">
    <!-- Ceremonia -->
    <img class="mx-auto" src="{{ asset('images/boda/tent.png') }}" alt="Ceremonia" width="100" style="opacity: 1;">
    <p class="mb-6 t4">
      <span>11:30 h</span> - <span>Ceremonia</span>
    </p>

    <!-- Cocktail -->
    <img class="mx-auto" src="{{ asset('images/boda/cocktail.png') }}" alt="Cocktail" width="100" style="opacity: 1;">
    <p class="mb-6 t5">
      <span>14:00 h</span> - <span>Cocktail</span>
    </p>

    <!-- Banquete -->
    <img class="mx-auto" src="{{ asset('images/boda/food.png') }}" alt="Banquete" width="100" style="opacity: 1;">
    <p class="mb-6 t6">
      <span>15:00 h</span> - <span>Banquete</span>
    </p>

    <!-- Fiesta -->
    <img class="mx-auto" src="{{ asset('images/boda/dance.png') }}" alt="Fiesta" width="100" style="opacity: 1;">
    <p class="mb-6 t7">
      <span>17:00 h</span> - <span>Fiesta</span>
    </p>
  </div>
</section>




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


<div class="section-container fade-in negro">
     <img src="{{ asset('images/boda/anillo.jpeg') }}" alt="Lugar de la ceremonia" class="t8">
  <img src="{{ asset('images/boda/divisor.png') }}" alt="decoración superior" class="decoracion-superior3 t9">

  <section class="contenido y2">
    <h2 class="y1">
      Nos vemos en...
    </h2>

    <div id="contador" class="y3">
      <!-- JS insertará aquí los 4 bloques -->
    </div>
  </section>

  <!-- Imagen decorativa inferior -->
  <img src="{{ asset('images/boda/divisor2.png') }}" alt="decoración inferior" class="decoracion-inferior2">
</div>











      <section class="fade-in section-blanca y4">
          <img src="{{ asset('images/boda/dec-flower3.png') }}" alt="decoración superior" class="decoracion-superior3 y5">
              <img src="{{ asset('images/boda/dec-flower3.png') }}" alt="decoración superior" class="decoracion-superior3 y6">
  <div class="y7">
    <h2 class="y8">Confirmación de asistencia</h2>
    
    <form action="#" method="POST">
      <!-- Nombre y Apellidos -->
      <div class="y9">
        <label for="nombre" class="u1">Nombre y Apellidos</label>
        <input type="text" id="nombre" name="nombre" required class="u2">
      </div>

      <!-- Confirmación de asistencia -->
      <div class="u3">
        <label class="u4">¿Vas a asistir?</label>
        <label class="u5">
          <input type="radio" name="asistencia" value="sí" required style="margin-right: 8px;"> Sí
        </label>
        <label style="display: inline-flex; align-items: center;">
          <input type="radio" name="asistencia" value="no" required style="margin-right: 8px;"> No
        </label>
      </div>

      <!-- Alergias / Intolerancias -->
      <div style="margin-bottom: 20px;">
        <label for="alergias" style="display: block; margin-bottom: 5px;">¿Tienes alguna alergia o intolerancia?</label>
        <textarea id="alergias" name="alergias" rows="3"
                  placeholder="Ej. gluten, lactosa, frutos secos..."
                  style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 1em;"></textarea>
      </div>

      <!-- Botón de enviar -->
      <div style="text-align: center;">
        <button type="submit"
                style="padding: 12px 25px; background-color: #9aa5a5; color: white; font-size: 1em;
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
  const fechaBoda = new Date("2025-10-18T11:30:00").getTime();
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

  contador.innerHTML = bloques.map((bloque, index) => `
    <div style="
      padding: 0.4rem;
      width: 24%;
      max-width: 24%;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-sizing: border-box;
      ${index < bloques.length - 1 ? 'border-right: 1px solid #ffffff;' : ''}
    ">
      <span style="font-size: 1.4rem; font-weight: bold; color: #575757;">${bloque.valor}</span>
      <span style="font-size: 1rem; color: #777;">${bloque.texto}</span>
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
