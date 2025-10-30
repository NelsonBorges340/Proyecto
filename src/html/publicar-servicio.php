<?php
session_start();
require __DIR__ . '/../php/db.php';

// Traer categorías
$cats = [];
$res = $conn->query("SELECT idCategoria, nombre FROM Categoria ORDER BY nombre ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $cats[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Publicar Servicio</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 
<style>
  body{
     background:  linear-gradient(to bottom, rgb(110, 146, 119), #e1d0bd);
  }
    .form-publicar {
      max-width: 500px;
      margin: 40px auto;
      padding: 32px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
      display: flex;
      flex-direction: column;
      gap: 18px;
    }
    .form-publicar label {
      font-weight: 600;
      margin-bottom: 6px;
    }
    .form-publicar input,
    .form-publicar textarea,
    .form-publicar select {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 1rem;
      width: 100%;
    }
    .form-publicar button {
      background: #eab308;
      color: #222;
      border: none;
      padding: 12px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.2s;
    }
    .form-publicar button:hover {
      background: #facc15;
    }
    .form-publicar h2 {
      text-align: center;
      margin-bottom: 18px;
    }
</style>
</head>
<body>
  <a href="index.php" class="back">&#8592; Volver</a>

  <!-- AQUI: añadimos class="form-publicar" y movemos el H2 dentro -->
  <form class="form-publicar" method="post" action="../php/publicar-servicio.php" enctype="multipart/form-data">
    <h2>Publicar Servicio</h2>

    <!-- Si querés mostrar errores: <div class="alert">Mensaje…</div> -->

    <label for="titulo">Título del servicio</label>
    <input type="text" id="titulo" name="titulo" required>

    <label for="descripcion">Descripción</label>
    <textarea id="descripcion" name="descripcion" rows="4" required></textarea>

    <label for="categoria_id">Categoría</label>
    <select id="categoria_id" name="categoria_id">
      <option value="">Sin categoría</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= htmlspecialchars($c['idCategoria']) ?>">
          <?= htmlspecialchars($c['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <!-- AQUI: row -> grid-2 -->
    <div class="grid-2">
      <div>
        <label for="precio">Precio</label>
        <input type="number" id="precio" name="precio" min="0" step="0.01" required>
      </div>
      <div>
        <label for="ubicacion">Ubicación</label>
        <select id="ubicacion" name="ubicacion" required>
          <option value="">Selecciona</option>
          <option value="Montevideo">Montevideo</option>
          <option value="Canelones">Canelones</option>
          <option value="Maldonado">Maldonado</option>
          <option value="Colonia">Colonia</option>
        </select>
      </div>
    </div>

    <label for="imagen">Imagen del servicio (opcional)</label>
    <input type="file" id="imagen" name="imagen" accept="image/*">

    <button type="submit" class="btn">Publicar</button>
  </form>
</body>
</html>
