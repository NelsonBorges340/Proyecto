<?php
// html/ver-servicio.php (MODO DIAGNÓSTICO con navbar unificada)
declare(strict_types=1);
session_start();

// Mostrar errores mientras depuramos
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../php/db.php';

function db() {
  global $conn, $mysqli;
  return (isset($mysqli) && $mysqli instanceof mysqli) ? $mysqli
       : ((isset($conn) && $conn instanceof mysqli) ? $conn : null);
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$DB = db();
if (!$DB) {
  echo "<!doctype html><meta charset='utf-8'><pre><b>Error:</b> no hay conexión a la base de datos.</pre>";
  exit;
}

$idServicio = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Si no vino id, NO redirigimos: mostramos una lista de servicios disponibles
if ($idServicio <= 0) {
  $rs = $DB->query("SELECT idServicio, titulo FROM Servicio ORDER BY idServicio ASC LIMIT 20");
  echo "<!doctype html><meta charset='utf-8'><h2>Falta ?id=</h2>";
  if ($rs && $rs->num_rows) {
    echo "<p>Elegí uno de los servicios disponibles:</p><ul>";
    while ($row = $rs->fetch_assoc()) {
      $i = (int)$row['idServicio'];
      echo "<li><a href='ver-servicio.php?id={$i}'>#{$i} - ".h($row['titulo'])."</a></li>";
    }
    echo "</ul>";
  } else {
    echo "<p><b>No hay servicios cargados en la tabla Servicio.</b></p>";
  }
  exit;
}

// Traer el servicio solicitado
$svc = null;
if ($st = $DB->prepare("SELECT s.idServicio, s.titulo, s.descripcion, s.precio, s.ubicacion, s.idProveedor,
                               c.nombre AS categoria, p.nombreNegocio
                        FROM Servicio s
                        LEFT JOIN Categoria c ON c.idCategoria = s.idCategoria
                        LEFT JOIN Proveedor p ON p.idProveedor = s.idProveedor
                        WHERE s.idServicio = ?")) {
  $st->bind_param('i', $idServicio);
  if (!$st->execute()) {
    echo "<!doctype html><meta charset='utf-8'><pre><b>Error SQL (execute):</b> ".h($st->error)."</pre>";
    exit;
  }
  $svc = $st->get_result()->fetch_assoc();
  $st->close();
} else {
  echo "<!doctype html><meta charset='utf-8'><pre><b>Error SQL (prepare):</b> ".h($DB->error)."</pre>";
  exit;
}

// Si no existe ese id, mostrar ayuda + lista de ids válidos
if (!$svc) {
  $rs = $DB->query("SELECT idServicio, titulo FROM Servicio ORDER BY idServicio ASC LIMIT 20");
  echo "<!doctype html><meta charset='utf-8'>";
  echo "<h2>Servicio no encontrado (id=". (int)$idServicio .")</h2>";
  if ($rs && $rs->num_rows) {
    echo "<p>Probá con alguno de estos:</p><ul>";
    while ($row = $rs->fetch_assoc()) {
      $i = (int)$row['idServicio'];
      echo "<li><a href='ver-servicio.php?id={$i}'>#{$i} - ".h($row['titulo'])."</a></li>";
    }
    echo "</ul>";
  } else {
    echo "<p><b>La tabla Servicio está vacía.</b></p>";
  }
  exit;
}

// Imágenes (si falla, mostramos el error y seguimos sin “matar” la página)
$imgs = [];
if ($st = $DB->prepare("SELECT idImagen, ruta_archivo, tipo_mime, imagen, es_principal
                        FROM ImagenServicio
                        WHERE idServicio = ?
                        ORDER BY es_principal DESC, idImagen DESC")) {
  $st->bind_param('i', $idServicio);
  if ($st->execute()) {
    $res = $st->get_result();
    while ($r = $res->fetch_assoc()) $imgs[] = $r;
  } else {
    echo "<!-- Aviso: error al traer imágenes: ". h($st->error) ." -->";
  }
  $st->close();
} else {
  echo "<!-- Aviso: error al preparar imágenes: ". h($DB->error) ." -->";
}

$hero = '../img/placeholder_servicio.png';
if (!empty($imgs)) {
  $im = $imgs[0];
  if (!empty($im['ruta_archivo'])) {
    $hero = '../' . ltrim($im['ruta_archivo'], '/');
  } elseif (!empty($im['imagen'])) {
    $mime = ($im['tipo_mime'] ?? '') ?: 'image/jpeg';
    $hero = 'data:' . $mime . ';base64,' . base64_encode($im['imagen']);
  }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title><?= h($svc['titulo']) ?> - Servicio</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<!-- ✅ Navbar unificada como en index -->
<link rel="stylesheet" href="../css/nav.css">

<style>
:root{
  --bg:#f5f7fb; --card:#fff; --line:#e5e7eb; --text:#0f172a;
  --brand:#2563eb; --brand-600:#1d4ed8; --muted:#6b7280;
}
*{ box-sizing:border-box }
body{ font-family:system-ui,Segoe UI,Arial,sans-serif; background:var(--bg); color:var(--text); margin:0; }
main{ max-width:1000px; margin:20px auto; padding:0 12px; }
.card{ background:var(--card); border:1px solid var(--line); border-radius:12px; padding:18px; box-shadow:0 10px 20px rgba(2,6,23,.05); }
.tabs{ display:flex; gap:8px; border-bottom:2px solid var(--line); margin-top:8px; }
.tab{ padding:10px 14px; cursor:pointer; border-radius:8px 8px 0 0; }
.tab.active{ border-bottom:3px solid var(--brand); color:var(--brand); font-weight:600; }
.tab-content{ display:none; padding-top:14px; }
.tab-content.active{ display:block; }
.svc-hero{ width:100%; max-height:420px; object-fit:cover; border-radius:10px; }
.svc-thumb{ width:90px; height:90px; object-fit:cover; border-radius:8px; margin:4px; cursor:pointer; border:2px solid transparent; }
.svc-thumb.sel{ border-color:var(--brand); }
.btn{ background:var(--brand); color:#fff; border:none; padding:10px 14px; border-radius:10px; cursor:pointer; }
.btn:hover{ background:var(--brand-600); }
.small{ color:var(--muted); font-size:12px; }
.row{ display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.blocked{ display:inline-block; padding:2px 8px; border-radius:999px; background:#f3f4f6; margin:2px; }
</style>
</head>
<body>

<!-- ✅ Header igual que index -->
<header class="site-nav">
  <div class="inner">
    <a class="brand" href="index.php">MANEKI</a>
    <a class="back" href="index.php">Volver al inicio</a>
  </div>
</header>

<script>
  // Toggle del menú usuario
  const btn = document.getElementById('userBtn');
  const menu = document.getElementById('userMenu');
  btn?.addEventListener('click', (e)=>{ e.stopPropagation(); btn.classList.toggle('open'); });
  document.addEventListener('click', ()=> btn.classList.remove('open'));
</script>


<main>
  <div class="card">
    <h1><?= h($svc['titulo']) ?> <span class="small">(#<?= (int)$svc['idServicio'] ?>)</span></h1>

    <div class="tabs">
      <div class="tab active" data-tab="detalles">Detalles</div>
      <div class="tab" data-tab="reservas">Reservas</div>
    </div>

    <div id="detalles" class="tab-content active">
      <img id="hero" class="svc-hero" src="<?= h($hero) ?>" alt="Imagen servicio">
      <?php if (count($imgs) > 1): ?>
        <div style="display:flex;flex-wrap:wrap;margin-top:8px;">
        <?php foreach ($imgs as $i):
          if (!empty($i['ruta_archivo']))      { $src = '../' . ltrim($i['ruta_archivo'], '/'); }
          elseif (!empty($i['imagen']))        { $src = 'data:' . (($i['tipo_mime'] ?? '') ?: 'image/jpeg') . ';base64,' . base64_encode($i['imagen']); }
          else                                 { $src = '../img/placeholder_servicio.png'; }
        ?>
          <img class="svc-thumb" src="<?= h($src) ?>" onclick="document.getElementById('hero').src=this.src;">
        <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <p><strong>Categoría:</strong> <?= h($svc['categoria'] ?? 'Sin categoría') ?></p>
      <p><strong>Precio:</strong> $<?= number_format((float)$svc['precio'], 2, ',', '.') ?></p>
      <p><strong>Ubicación:</strong> <?= h($svc['ubicacion'] ?? '') ?></p>
      <p><strong>Proveedor:</strong> <?= h($svc['nombreNegocio'] ?? '') ?></p>
      <p><strong>Descripción:</strong><br><?= nl2br(h($svc['descripcion'] ?? '')) ?></p>

      <div class="small">URL actual: <?= h($_SERVER['REQUEST_URI'] ?? '') ?></div>
    </div>

    <div id="reservas" class="tab-content">
      <h3>Reservar fecha</h3>
      <div class="row">
        <label for="fecha">Fecha:</label>
        <input type="date" id="fecha" name="fecha" required>
        <button class="btn" id="btn-reservar">Confirmar reserva</button>
      </div>
      <div id="bloqueadasWrap" style="margin-top:10px;">
        <div class="small">Fechas no disponibles:</div>
        <div id="bloqueadas"></div>
      </div>
      <div id="msg" class="small" style="margin-top:8px;"></div>
    </div>
  </div>
</main>

<script>
// Tabs
document.querySelectorAll('.tab').forEach(t=>{
  t.addEventListener('click',()=>{
    document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
    t.classList.add('active');
    document.getElementById(t.dataset.tab).classList.add('active');
  });
});

// Datos básicos para JS
const idServicio = <?= (int)$idServicio ?>;

// Abrir reservas y cargar calendario
document.querySelector('[data-tab="reservas"]').addEventListener('click', async ()=>{
  await cargarCalendario();
});

async function cargarCalendario(){
  const fechaInput = document.getElementById('fecha');
  const bloqueadasDiv = document.getElementById('bloqueadas');
  const msg = document.getElementById('msg');
  msg.textContent = 'Cargando disponibilidad...';
  try{
    const res = await fetch(`../php/reserva_get_calendario.php?servicio_id=${idServicio}`, {cache:'no-store'});
    const data = await res.json();
    if(!data.ok){ msg.textContent = 'No se pudo cargar la disponibilidad.'; return; }
    // Tu endpoint devuelve 'desde' → úsalo como mínimo
    fechaInput.min = data.min || data.desde || '';
    fechaInput.dataset.booked = JSON.stringify(data.reservados || []);
    bloqueadasDiv.innerHTML = '';
    (data.reservados || []).forEach(d=>{
      const span = document.createElement('span'); span.className='blocked'; span.textContent=d;
      bloqueadasDiv.appendChild(span);
    });
    fechaInput.onchange = ()=>{
      const sel = fechaInput.value;
      const booked = JSON.parse(fechaInput.dataset.booked || '[]');
      if(booked.includes(sel)){ alert('Esa fecha ya está reservada.'); fechaInput.value=''; }
    };
    msg.textContent = 'Selecciona una fecha y confirma.';
  }catch(e){
    console.error(e);
    msg.textContent = 'Error al cargar el calendario.';
  }
}

// Confirmar reserva
document.getElementById('btn-reservar').addEventListener('click', async ()=>{
  const fechaInput = document.getElementById('fecha');
  const msg = document.getElementById('msg');
  const fecha = fechaInput.value;
  if(!fecha){ alert('Selecciona una fecha.'); return; }
  const booked = JSON.parse(fechaInput.dataset.booked || '[]');
  if(booked.includes(fecha)){ alert('Esa fecha ya está reservada.'); return; }
  msg.textContent = 'Guardando reserva...';
  try{
    const form = new FormData();
    form.append('servicio_id', String(idServicio));
    form.append('fecha', fecha);
    const res = await fetch('../php/reserva_crear.php', { method:'POST', body: form });
    const data = await res.json();
    if(!data.ok){
      alert(data.error || 'No se pudo crear la reserva.');
      return;
    }
    if (data.factura) {
      window.location.href = data.factura;
    } else {
      alert('Reserva creada, pero el servidor no devolvió URL de factura.');
    }
  }catch(e){
    console.error(e);
    alert('Error al crear la reserva.');
  }
});
</script>
</body>
</html>
