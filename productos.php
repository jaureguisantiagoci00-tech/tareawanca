<?php
session_start(); // AGREGAR ESTA LÍNEA AL PRINCIPIO
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Productos - Puphub</title>
  <!-- resto del head... -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="estilos.css">
  <style>
    /* Asegúrate de que este estilo esté en tu estilos.css para la uniformidad de las imágenes */
    .product-img {
      height: 250px; 
      object-fit: cover; 
      width: 100%; 
      max-width: 250px; 
      display: block;
      margin: 0 auto;
      border-radius: 0.5rem; /* Añadido para consistencia con el diseño de Puphub */
      box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Añadido para consistencia */
    }
    .product-card {
        padding: 15px;
        border: 1px solid #e9ecef;
        border-radius: 1rem;
        transition: transform 0.2s;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }
  </style>
</head>
<body>

    <div class="border-bottom py-3 px-4">
      <div class="row align-items-center">

           <nav class="col-md-6 d-none d-md-flex justify-content-center">
           <a href="index.php" class="col-auto px-3 text-decoration-none">Inicio</a>
           <a href="acerca_de_nosotros.php" class="col-auto px-3 text-decoration-none">Acerca de Nosotros</a>
           <a href="index.php#servicios" class="col-auto px-3 text-decoration-none text-primary fw-bolder">Servicios</a> 
           <a href="#productos-section" class="col-auto px-3 text-decoration-none">Productos</a>
           <a href="index.php#contacto" class="col-auto px-3 text-decoration-none">Contáctenos</a>
           </nav>
           <div class="col-6 col-md-3 text-end">
            <button class="btn btn-outline-success border-2 me-2 fw-bold" 
            type="button" 
            data-bs-toggle="offcanvas" 
            data-bs-target="#cartOffcanvas" 
            aria-controls="cartOffcanvas"
            id="btnOpenCart">
            <i class="fas fa-shopping-cart"></i> 🛒 Carrito 
            </button>
  
           <?php if(isset($_SESSION['user_id'])): ?>
    
            <div class="dropdown d-inline-block">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
           <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Mi Cuenta'; ?>
            </button>
           <ul class="dropdown-menu">
           <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
           <li><hr class="dropdown-divider"></li>
           <li><a class="dropdown-item" href="php/logout.php">Cerrar Sesión</a></li>
           </ul>
           </div>
            <?php else: ?>
   
           <button class="btn btn-primary fw-bold me-2" data-bs-toggle="modal" data-bs-target="#modalIngreso">🔑 Ingresar</button>
           <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalRegistro">📝 Registrarse</button>
           <?php endif; ?>
       </div>
      </div>
    </div>

  <div class="container py-5" id="productos-section">
    <h2 class="fw-bold text-center mb-5 display-4 text-primary">🛍️ Nuestro Catálogo de Productos</h2>

    <div class="row g-4 text-center">


     <div class="col-12 mt-3 mb-3">
    <h3 class="fw-bold text-center text-dark">🍖 Alimento y Nutrición Premium</h3>
    <p class="text-center text-muted">La mejor selección de comida seca y húmeda para cada etapa de la vida de tu mascota.</p>
</div>
<div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/Producto01.jpg" alt="Drools 3KG" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Drools | 3KG</h6>
            <p class="text-secondary small">Alimento seco para perros adultos.</p>
            <p class="fw-bold fs-5 text-success">S/ 45.90</p>
            
            <div class="d-grid mt-2">
    <button class="btn btn-success fw-bold btn-add-to-cart"
            data-product-id="1"
            data-product-name="Drools | 3KG"
            data-product-price="45.90">
        🛒 Agregar al Carrito
    </button>
</div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/producto02.jpg" alt="Canine Creek 4KG" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Canine Creek | 4KG</h6>
            <p class="text-secondary small">Alimento seco para perros adultos.</p>
            <p class="fw-bold fs-5 text-success">S/ 59.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="2"
                      data-product-name="Canine Creek | 4KG"
                      data-product-price="59.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/Producto03.jpg" alt="Biscrok Biscuits" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Biscrok Biscuits</h6>
            <p class="text-secondary small">Galletas para perros adultos.</p>
            <p class="fw-bold fs-5 text-success">S/ 14.50</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="3"
                      data-product-name="Biscrok Biscuits"
                      data-product-price="14.50">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/comida15cachorrro.webp" alt="Ricocan Cachorro" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Ricocan | Cachorro 15KG</h6>
            <p class="text-secondary small">Alimento para cachorros. Venta en Perú.</p>
            <p class="fw-bold fs-5 text-success">S/ 99.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="4"
                      data-product-name="Ricocan | Cachorro 15KG"
                      data-product-price="99.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/producto_mimaskot_adulto.webp" alt="MIMASKOT Adulto" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">MIMASKOT | Adulto Carne</h6>
            <p class="text-secondary small">Alimento seco para perros adultos.</p>
            <p class="fw-bold fs-5 text-success">S/ 85.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="5"
                      data-product-name="MIMASKOT | Adulto Carne"
                      data-product-price="85.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/pedigrisenior.jpg" alt="Pedigree Senior" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Pedigree | Senior 3KG</h6>
            <p class="text-secondary small">Fórmula para perros de edad avanzada.</p>
            <p class="fw-bold fs-5 text-success">S/ 39.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="6"
                      data-product-name="Pedigree | Senior 3KG"
                      data-product-price="39.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/nutranu.jpeg" alt="Nutra Nuggets Large" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Nutra Nuggets | Adultos grandes</h6>
            <p class="text-secondary small">Alimento premium para razas grandes.</p>
            <p class="fw-bold fs-5 text-success">S/ 210.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="7"
                      data-product-name="Nutra Nuggets | Adultos grandes"
                      data-product-price="210.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/Proplan.jpg" alt="Pro Plan Salmon" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Pro Plan | Piel Sensible 3KG</h6>
            <p class="text-secondary small">Alimento seco con salmón para alergias.</p>
            <p class="fw-bold fs-5 text-success">S/ 75.50</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="8"
                      data-product-name="Pro Plan | Piel Sensible 3KG"
                      data-product-price="75.50">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/dogshow.webp" alt="Dog Chow Adultos" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Dog Chow | Adultos Minis</h6>
            <p class="text-secondary small">Alimento seco para razas pequeñas.</p>
            <p class="fw-bold fs-5 text-success">S/ 62.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="9"
                      data-product-name="Dog Chow | Adultos Minis"
                      data-product-price="62.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/perfo.jpg" alt="Eukanuba Performance" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Eukanuba | High Performance</h6>
            <p class="text-secondary small">Para perros activos y de alto rendimiento.</p>
            <p class="fw-bold fs-5 text-success">S/ 185.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="10"
                      data-product-name="Eukanuba | High Performance"
                      data-product-price="185.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/ricocatç.webp" alt="Ricocat Gatitos" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Ricocat | Gatitos 1KG</h6>
            <p class="text-secondary small">Alimento seco para la etapa inicial.</p>
            <p class="fw-bold fs-5 text-success">S/ 15.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="11"
                      data-product-name="Ricocat | Gatitos 1KG"
                      data-product-price="15.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/mimaadul.webp" alt="MIMASKOT Gato Atún" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">MIMASKOT | Gato Atún 1KG</h6>
            <p class="text-secondary small">Alimento seco con sabor a atún y salmón.</p>
            <p class="fw-bold fs-5 text-success">S/ 18.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="12"
                      data-product-name="MIMASKOT | Gato Atún 1KG"
                      data-product-price="18.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/whisca.webp" alt="Whiskas Pouch" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Whiskas | Pouch Pollo</h6>
            <p class="text-secondary small">Alimento húmedo en sobres. (12 x 85g)</p>
            <p class="fw-bold fs-5 text-success">S/ 32.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="13"
                      data-product-name="Whiskas | Pouch Pollo"
                      data-product-price="32.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/caturi.webp" alt="Cat Chow Urinary" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Cat Chow | Vías Urinarias</h6>
            <p class="text-secondary small">Fórmula para la salud del tracto urinario.</p>
            <p class="fw-bold fs-5 text-success">S/ 79.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="14"
                      data-product-name="Cat Chow | Vías Urinarias"
                      data-product-price="79.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/nutri.webp" alt="Nutrican perros" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Nutrican | Perros 2KG</h6>
            <p class="text-secondary small">Alimento para perros adultos .</p>
            <p class="fw-bold fs-5 text-success">S/ 35.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="15"
                      data-product-name="Nutrican | Perros 2KG"
                      data-product-price="35.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/royalcani.webp" alt="Royal Canin Indoor" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Royal Canin | Indoor 27</h6>
            <p class="text-secondary small">Especial para gatos que viven en interior.</p>
            <p class="fw-bold fs-5 text-success">S/ 95.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="16"
                      data-product-name="Royal Canin | Indoor 27"
                      data-product-price="95.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/wild.jpg" alt="Taste of the Wild Gato" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Taste of the Wild | Gato Rocky</h6>
            <p class="text-secondary small">Con proteína de venado y salmón ahumado.</p>
            <p class="fw-bold fs-5 text-success">S/ 89.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="17"
                      data-product-name="Taste of the Wild | Gato Rocky"
                      data-product-price="89.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/hills.jpg" alt="Hill's Gato Pelo y Piel" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Hill's | Gato Hairball</h6>
            <p class="text-secondary small">Para el control de bolas de pelo.</p>
            <p class="fw-bold fs-5 text-success">S/ 65.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="18"
                      data-product-name="Hill's | Gato Hairball"
                      data-product-price="65.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/propaç.jpg" alt="Pro Plan Gato Esterilizado" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Pro Plan | Esterilizados 7+</h6>
            <p class="text-secondary small">Fórmula para gatos adultos esterilizados.</p>
            <p class="fw-bold fs-5 text-success">S/ 72.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="19"
                      data-product-name="Pro Plan | Esterilizados 7+"
                      data-product-price="72.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/feli.jpg" alt="Felix Sensaciones" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Felix | Sensaciones Pescado</h6>
            <p class="text-secondary small">Comida húmeda en gelatina para gatos.</p>
            <p class="fw-bold fs-5 text-success">S/ 4.50</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="20"
                      data-product-name="Felix | Sensaciones Pescado"
                      data-product-price="4.50">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>
      
      <div class="col-12 mt-5 mb-3">
          <h3 class="fw-bold text-center text-dark">🏥 Salud y Farmacia Veterinaria</h3>
          <p class="text-center text-muted">Productos esenciales para el cuidado médico y prevención.</p>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card bg-light">
            <img src="imagenes/pasti.png" alt="Pastilla Desparasitante Perro" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Desparasitante | Drontal Plus</h6>
            <p class="text-secondary small">Tabletas para parásitos internos caninos.</p>
            <p class="fw-bold fs-5 text-success">S/ 28.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="21"
                      data-product-name="Desparasitante | Drontal Plus"
                      data-product-price="28.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card bg-light">
            <img src="imagenes/anti.png" alt="Pipeta Antipulgas Bravecto" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Pipeta | Bravecto Perros 10-20KG</h6>
            <p class="text-secondary small">Protección contra pulgas y garrapatas (3 meses).</p>
            <p class="fw-bold fs-5 text-success">S/ 140.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="22"
                      data-product-name="Pipeta | Bravecto Perros 10-20KG"
                      data-product-price="140.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card bg-light">
            <img src="imagenes/arti.webp" alt="Suplemento Articular" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Suplemento | Condroprotector</h6>
            <p class="text-secondary small">Glucosamina y Condroitina para articulaciones.</p>
            <p class="fw-bold fs-5 text-success">S/ 89.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="23"
                      data-product-name="Suplemento | Condroprotector"
                      data-product-price="89.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card bg-light">
            <img src="imagenes/amo.webp" alt="Antibiótico Amoxicilina" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Antibiótico | Amoxi-tabs 500mg</h6>
            <p class="text-secondary small">Amplio espectro para infecciones (con receta).</p>
            <p class="fw-bold fs-5 text-success">S/ 45.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="24"
                      data-product-name="Antibiótico | Amoxi-tabs 500mg"
                      data-product-price="45.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card bg-light">
            <img src="imagenes/gota.png" alt="Gotas Oftálmicas y Óticas" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Gotas | Oftálmicas/Óticas</h6>
            <p class="text-secondary small">Para el tratamiento de conjuntivitis y otitis.</p>
            <p class="fw-bold fs-5 text-success">S/ 35.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="25"
                      data-product-name="Gotas | Oftálmicas/Óticas"
                      data-product-price="35.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card bg-light">
            <img src="imagenes/purapura.webp" alt="Probiótico para Gatos" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Probiótico | FortiFlora Gato</h6>
            <p class="text-secondary small">Para restaurar la flora intestinal.</p>
            <p class="fw-bold fs-5 text-success">S/ 70.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="26"
                      data-product-name="Probiótico | FortiFlora Gato"
                      data-product-price="70.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card bg-light">
            <img src="imagenes/cica.webp" alt="Crema Cicatrizante" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Crema | Cicatrizante Veterinaria</h6>
            <p class="text-secondary small">Para heridas, quemaduras y abrasiones.</p>
            <p class="fw-bold fs-5 text-success">S/ 29.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="27"
                      data-product-name="Crema | Cicatrizante Veterinaria"
                      data-product-price="29.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card bg-light">
            <img src="imagenes/omega.jpg" alt="Aceite de Salmón Suplemento" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Aceite de Salmón | Omega 3</h6>
            <p class="text-secondary small">Mejora pelo, piel y sistema inmunológico.</p>
            <p class="fw-bold fs-5 text-success">S/ 55.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="28"
                      data-product-name="Aceite de Salmón | Omega 3"
                      data-product-price="55.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card bg-light">
            <img src="imagenes/suple.png" alt="Vitaminas para Cachorros" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Suplemento | Multivitamínico Jarabe</h6>
            <p class="text-secondary small">Refuerzo para cachorros en crecimiento.</p>
            <p class="fw-bold fs-5 text-success">S/ 42.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="29"
                      data-product-name="Suplemento | Multivitamínico Jarabe"
                      data-product-price="42.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card bg-light">
            <img src="imagenes/ga.jpg" alt="Tratamiento Antidiarreico" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Tratamiento | Antidiarreico</h6>
            <p class="text-secondary small">Regulador intestinal para perros y gatos.</p>
            <p class="fw-bold fs-5 text-success">S/ 24.50</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="30"
                      data-product-name="Tratamiento | Antidiarreico"
                      data-product-price="24.50">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>
      
      <div class="col-12 mt-5 mb-3">
          <h3 class="fw-bold text-center text-dark">🛁 Accesorios y Cuidado Personal</h3>
          <p class="text-center text-muted">Todo lo necesario para el paseo, higiene y confort de tu mascota.</p>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/pana.jpg" alt="Pañales Desechables Perro Macho" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Pañales Macho | Talla M (12 Uds)</h6>
            <p class="text-secondary small">Para incontinencia o entrenamiento.</p>
            <p class="fw-bold fs-5 text-success">S/ 39.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="31"
                      data-product-name="Pañales Macho | Talla M (12 Uds)"
                      data-product-price="39.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/correa.webp" alt="Correa Retráctil Grande" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Correa Retráctil | 5 Metros</h6>
            <p class="text-secondary small">Para perros de hasta 50KG.</p>
            <p class="fw-bold fs-5 text-success">S/ 65.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="32"
                      data-product-name="Correa Retráctil | 5 Metros"
                      data-product-price="65.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/furmi.jpg" alt="Cepillo Deslanador" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Cepillo Deslanador | Furminator</h6>
            <p class="text-secondary small">Reduce la caída de pelo muerto.</p>
            <p class="fw-bold fs-5 text-success">S/ 99.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="33"
                      data-product-name="Cepillo Deslanador | Furminator"
                      data-product-price="99.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/corta.webp" alt="Cortaúñas de Guillotina" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Cortaúñas | Tipo Guillotina</h6>
            <p class="text-secondary small">Para corte preciso de uñas de perros.</p>
            <p class="fw-bold fs-5 text-success">S/ 25.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="34"
                      data-product-name="Cortaúñas | Tipo Guillotina"
                      data-product-price="25.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/pulgoso.webp" alt="Shampoo Antipulgas" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Shampoo | Antipulgas 500ml</h6>
            <p class="text-secondary small">Elimina y previene parásitos externos.</p>
            <p class="fw-bold fs-5 text-success">S/ 48.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="35"
                      data-product-name="Shampoo | Antipulgas 500ml"
                      data-product-price="48.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/rasca.jpg" alt="Rascador para Gato Torre" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Rascador | Torre 3 Niveles</h6>
            <p class="text-secondary small">Centro de juegos y descanso para gatos.</p>
            <p class="fw-bold fs-5 text-success">S/ 160.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="36"
                      data-product-name="Rascador | Torre 3 Niveles"
                      data-product-price="160.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/casa.webp" alt="Arenero Cerrado para Gato" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Arenero | Cerrado con Puerta</h6>
            <p class="text-secondary small">Caja de arena para mayor higiene.</p>
            <p class="fw-bold fs-5 text-success">S/ 85.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="37"
                      data-product-name="Arenero | Cerrado con Puerta"
                      data-product-price="85.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/colla.webp" alt="Collar Isabelino M" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Collar Isabelino | Talla M</h6>
            <p class="text-secondary small">Protector post-operatorio para mascotas.</p>
            <p class="fw-bold fs-5 text-success">S/ 19.90</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="38"
                      data-product-name="Collar Isabelino | Talla M"
                      data-product-price="19.90">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/come.jpg" alt="Comedero Doble Acero Inox" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Comedero | Doble Acero</h6>
            <p class="text-secondary small">Para comida y agua, antideslizante.</p>
            <p class="fw-bold fs-5 text-success">S/ 55.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="39"
                      data-product-name="Comedero | Doble Acero"
                      data-product-price="55.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>

      <div class="col-md-3 col-6">
        <div class="product-card">
            <img src="imagenes/jugue.jpg" alt="Juguete Kong Classic" class="img-fluid mb-3 product-img">
            <h6 class="fw-bold">Juguete | Kong Classic L</h6>
            <p class="text-secondary small">Para dispensar premios y morder.</p>
            <p class="fw-bold fs-5 text-success">S/ 60.00</p>
            <div class="d-grid mt-2">
              <button class="btn btn-success fw-bold btn-add-to-cart"
                      data-product-id="40"
                      data-product-name="Juguete | Kong Classic L"
                      data-product-price="60.00">
                  🛒 Agregar al Carrito
              </button>
            </div>
        </div>
      </div>
      
    </div>
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
    <div class="offcanvas-header bg-success text-white">
      <h5 class="offcanvas-title fw-bold" id="cartOffcanvasLabel">🛍️ Tu Carrito de Puphub</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">

      <div id="cartItemsContainer" class="flex-grow-1 overflow-auto">
        <p class="text-center text-muted mt-5">Tu carrito está vacío. ¡Añade algunos productos!</p>
      </div>

      <hr>

      <div class="cart-summary p-2 border-top">
          <h4 class="d-flex justify-content-between">
              <span class="fw-bold">Total:</span>
              <span id="cartTotal" class="text-success fw-bolder">S/ 0.00</span>
          </h4>
          <div class="d-grid mt-3">
              <button id="btnCheckout" class="btn btn-primary fw-bold" disabled>
                  Finalizar Compra
              </button>
          </div>
      </div>
    </div>
  </div>
  <footer class="bg-dark text-white py-4 mt-4"> 
    <div class="container text-center text-md-start">
      <div class="row">
        <div class="col-md-4 mb-3">
          <h6 class="fw-bold">Síguenos en redes sociales</h6>
          <a href="#"><img src="imagenes/facebook.png" alt="Facebook" class="me-2"></a>
          <a href="#"><img src="imagenes/insta.png" alt="Instagram"></a>
        </div>

        <div class="col-md-4 mb-3 small">
          <h6 class="fw-bold">Internado y guardería</h6>
          <p class="mb-1 fw-bold">(51) 999 888-862</p>
          <p>Av. Los Ruiseñores 1234<br>Santa Anita - Lima</p>
        </div>

        <div class="col-md-4 mb-3 small">
          <h6 class="fw-bold">Cuidado de la salud (Veterinaria)</h6>
          <p class="mb-1 fw-bold">(51) 966-589-123</p>
          <p>Av. Gallito de las Rocas 567<br>San Borja - Lima</p>
        </div>
      </div>
      <div class="text-center pt-3 border-top mt-3">
        <p class="mb-0 small text-secondary">&copy; 2023 **Puphub**. Todos los derechos reservados. | Diseño y Desarrollo Web.</p>
      </div>
    </div>
  </footer>

  <div class="modal fade" id="modalIngreso" tabindex="-1" aria-labelledby="modalIngresoLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="modalIngresoLabel">🔑 Ingresar a Puphub</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="php/procesar_login.php" method="POST">
            <div class="mb-3">
              <label for="emailIngreso" class="form-label">Correo Electrónico</label>
              <input type="email" class="form-control" id="emailIngreso" name="email" required>
            </div>
            <div class="mb-3">
              <label for="passwordIngreso" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="passwordIngreso" name="password" required>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="checkRecordar">
              <label class="form-check-label" for="checkRecordar">Recordarme</label>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary fw-bold">Iniciar Sesión</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalRegistro" tabindex="-1" aria-labelledby="modalRegistroLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="modalRegistroLabel">📝 Crear una Cuenta</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="php/procesar_registro.php" method="POST">
            <div class="mb-3">
              <label for="nombreRegistro" class="form-label">Nombre Completo</label>
              <input type="text" class="form-control" id="nombreRegistro" name="nombre_completo" required>
            </div>
            <div class="mb-3">
              <label for="emailRegistro" class="form-label">Correo Electrónico</label>
              <input type="email" class="form-control" id="emailRegistro" name="email" required>
            </div>
            <div class="mb-3">
              <label for="passwordRegistro" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="passwordRegistro" name="password" required>
            </div>
            <div class="mb-3">
              <label for="passwordConfirmacion" class="form-label">Confirmar Contraseña</label>
              <input type="password" class="form-control" id="passwordConfirmacion" name="password_confirmacion" required>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success fw-bold">Crear Cuenta</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
   
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function getUserIdFromSession() {
        // Usar la sesión PHP real
        const userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
        if (!userId) {
            console.warn("User ID no encontrado. Debes iniciar sesión para usar el carrito.");
            return null;
        }  
        return userId;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const cartOffcanvas = new bootstrap.Offcanvas(document.getElementById('cartOffcanvas'));
        const cartItemsContainer = document.getElementById('cartItemsContainer');
        const cartTotalDisplay = document.getElementById('cartTotal');
        const btnCheckout = document.getElementById('btnCheckout');
        const cartButton = document.getElementById('btnOpenCart'); 

        // Función para hacer llamadas AJAX
        async function apiCall(url, method = 'GET', data = null) {
            try {
                const options = {
                    method: method,
                    headers: {
                        'Accept': 'application/json',
                    }
                };
        
                if (method === 'POST' && data) {
                    options.body = data;
                }
        
                const response = await fetch('php/' + url, options);
                const textResponse = await response.text();
                
                console.log("Respuesta del servidor (" + url + "):", textResponse.substring(0, 200));
      
                let result;
                try {
                    result = JSON.parse(textResponse);
                } catch (e) {
                    console.error("❌ NO ES JSON VÁLIDO en " + url + ":", e.message);
                    console.error("Respuesta completa:", textResponse);
                    return { 
                        success: false, 
                        message: "Error del servidor: respuesta no es JSON válido" 
                    };
                }
               
                if (!response.ok || result.success === false) {
                    throw new Error(result.message || `Error en la solicitud a ${url}`);
                }
                
                return result;
            } catch (error) {
                console.error("Error en API Call:", error.message);
                return { 
                    success: false, 
                    message: error.message 
                };
            }
        }

        // Función 1: Agregar producto al carrito
        document.querySelectorAll('.btn-add-to-cart').forEach(button => {
            button.addEventListener('click', async (e) => {
                const userId = getUserIdFromSession();
                if (!userId) {
                    alert('Por favor, inicia sesión para poder agregar productos al carrito.');
                    return; 
                }

                const productId = button.getAttribute('data-product-id');
                const name = button.getAttribute('data-product-name');
                const price = button.getAttribute('data-product-price');
                const quantity = 1;
                
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('name', name);
                formData.append('price', price);
                formData.append('quantity', quantity);

                const result = await apiCall('add_to_cart.php', 'POST', formData);

                if (result.success) {
                    alert('✅ Producto añadido al carrito: ' + name);
                    loadCart();
                    cartOffcanvas.show();
                } else {
                    alert('❌ No se pudo añadir el producto: ' + result.message);
                }
            });
        });

        // Función 2: Cargar el carrito completo desde PHP
        async function loadCart() {
            const userId = getUserIdFromSession();
            if (!userId) {
                cartItemsContainer.innerHTML = `<p class="text-center text-danger mt-5">Necesitas iniciar sesión para ver tu carrito.</p>`;
                cartTotalDisplay.textContent = 'S/ 0.00';
                btnCheckout.disabled = true;
                return;
            }

            const result = await apiCall(`get_cart.php?user_id=${userId}`);

            if (result.success) {
                renderCart(result.items);
            } else {
                renderCart([]);
            }
        }

        // Función 3: Renderizar los items del carrito
        function renderCart(items) {
            cartItemsContainer.innerHTML = '';
            let total = 0;

            if (!items || items.length === 0) {
                cartItemsContainer.innerHTML = `<p class="text-center text-muted mt-5">Tu carrito está vacío. ¡Añade algunos productos!</p>`;
                cartTotalDisplay.textContent = 'S/ 0.00';
                btnCheckout.disabled = true;
                return;
            }

            items.forEach(item => {
                const itemPrice = parseFloat(item.price) || 0;
                const itemQuantity = parseInt(item.quantity) || 1;
                const itemTotal = itemPrice * itemQuantity;
                total += itemTotal;

                const itemHtml = `
                    <div class="d-flex align-items-center border-bottom py-2">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold">${item.name}</h6>
                            <small class="text-muted">Precio: S/ ${itemPrice.toFixed(2)}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-secondary me-2">${itemQuantity}x</span>
                            <span class="fw-bolder text-success">S/ ${itemTotal.toFixed(2)}</span>
                        </div>
                    </div>
                `;
                cartItemsContainer.innerHTML += itemHtml;
            });

            cartTotalDisplay.textContent = `S/ ${total.toFixed(2)}`;
            btnCheckout.disabled = false;
        }

        // Función 4: Manejar el Checkout
        btnCheckout.addEventListener('click', async () => {
            if (confirm('¿Estás seguro de que quieres finalizar la compra?')) {
                const userId = getUserIdFromSession();
                
                const formData = new FormData();
                formData.append('user_id', userId);

                const result = await apiCall('checkout.php', 'POST', formData);

                if (result.success) {
                    alert('✅ Compra finalizada con éxito. ¡Gracias!');
                    loadCart();
                } else {
                    alert('❌ Error al procesar la compra: ' + result.message);
                }
            }
        });

        // Cargar el carrito cuando el Offcanvas se abre
        if(cartButton) {
            cartButton.addEventListener('click', loadCart);
        }
    });
</script>
</body>
</html>
</body>
</html>