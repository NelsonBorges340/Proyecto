<?php
// html/ver-imagen-servicio.php
session_start();
require_once __DIR__ . '/../php/db.php';

/* ---------- CONFIG (ajusta si ya tenés tus propios endpoints) ---------- */
$UPLOAD_ENDPOINT   = '../php/servicio_imagen_subir.php';
$PRINCIPAL_ENDPOINT= '../php/servicio_imagen_principal.php';
$BORRAR_ENDPOINT   = '../php/servicio_imagen_borrar.php';

/* ---------- helpers ---------- */
function db(){ global $conn,$mysqli; return isset($conn)&&$conn instanceof mysqli ? $conn : (isset($mysqli)?$mysqli:null); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$DB = db(); if(!$DB){ die('Sin DB'); }

/* ---------- idServicio ---------- */
$idServicio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($idServicio <= 0) { die('Falta ?id= de servicio'); }

/* ---------- datos del servicio y propietario ---------- */
$svc = null;
$sql = "SELECT s.idServicio, s.titulo, s.descripcion, s.idProveedor, p.nombreNegocio
        FROM Servicio s LEFT JOIN Proveedor p ON p.idProveedor=s.idProveedor
        WHERE s.idServicio=?";
if ($st = $DB->prepare($sql)) {
  $st->bind_param('i',$idServicio);
  $st->execute();
  $svc = $st->get_result()->fetch_assoc();
  $st->close();
}
if (!$svc) { die('Servicio no encontrado'); }

/* ---------- sesión / permisos ---------- */
$yoId = isset($_SESSION['idUsuario']) ? (int)$_SESSION['idUsuario'] : 0;
$miRol = $_SESSION['rol'] ?? null;
$soyAdmin = ($miRol === 'administrador');
$soyDuenio = ($yoId > 0 && (int)$svc['idProveedor'] === $yoId);
$puedeGestionar = $soyAdmin || $soyDuenio;

/* ---------- imágenes (principal primero) ---------- */
$imgs = [];
$st = $DB->prepare("SELECT idImagen, ruta_archivo, tipo_mime, es_principal, fecha_subida
                    FROM ImagenServicio
                    WHERE idServicio=?
                    ORDER BY es_principal DESC, idImagen DESC");
$st->bind_param('i',$idServicio);
$st->execute();
$r = $st->get_result();
while($row = $r->fetch_assoc()){ $imgs[] = $row; }
$st->close();

$hero = count($imgs) ? $imgs[0]['ruta_archivo'] : 'img/placeholder_servicio.png';
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Imágenes del servicio</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root{
  --bg:#f5f7fb; --card:#ffffff; --text:#0f172a; --muted:#6b7280; --line:#e5e7eb;
  --brand:#2563eb; --brand-hover:#1e4ed8; --accent:#10b981;
  --shadow:0 10px 20px rgba(2,6,23,.08),0 4px 8px rgba(2,6,23,.04);
  --radius:12px;
}
*{box-sizing:border-box}
body{font-family:system-ui,Segoe UI,Arial,sans-serif;background:var(--bg);margin:0;color:var(--text)}
nav{background:var(--brand);color:#fff;padding:14px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:var(--shadow)}
nav a{color:#fff;text-decoration:none;margin-left:20px;font-weight:500}
nav a:hover{text-decoration:underline}
main{max-width:1000px;margin:32px auto;padding:0 12px}
.card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);padding:18px;margin-bottom:16px}
h1{margin:8px 0 12px;font-size:1.6rem}
h2{margin:0 0 10px;font-size:1.2rem}
.row{display:grid;grid-template-columns:1fr;gap:12px}
@media(min-width:900px){ .row{grid-template-columns:1.2fr .8fr} }

.hero{width:100%;max-height:460px;object-fit:cover;border-radius:12px;border:1px solid var(--line)}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-top:12px}
.item{border:1px solid var(--line);border-radius:10px;padding:8px;text-align:center;background:#fff}
.item img{width:100%;height:120px;object-fit:cover;border-radius:8px;border:1px solid var(--line)}
.badge{display:inline-block;padding:2px 6px;border-radius:6px;background:#dbeafe;color:#1e40af;font-size:.75rem;margin-top:6px}
.btn{display:inline-block;margin:6px 4px 0;padding:6px 10px;border-radius:8px;border:1px solid var(--line);background:#f8fafc;cursor:pointer}
.btn.primary{background:var(--brand);color:#fff;border-color:var(--brand)}
.btn.danger{background:#fee2e2;color:#991b1b;border-color:#fecaca}
form.uploader{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:8px}
small.muted{color:var(--muted)}
</style>
</head>
<body>
<nav>
  <div><strong>Servicio</strong></div>
  <div>
    <a href="index.php">Inicio</a>
    <a href="ver-servicio.php?id=<?= (int)$svc['idServicio'] ?>">Volver al servicio</a>
  </div>
</nav>

<main>
  <div class="card">
    <h1>Imágenes de: <?=h($svc['titulo'])?></h1>
    <div class="row">
      <div>
        <img class="hero" id="hero" src="../<?=h($hero)?>" alt="Imagen principal">
        <div class="grid" id="galeria">
          <?php foreach($imgs as $im): ?>
            <div class="item" data-id="<?= (int)$im['idImagen'] ?>">
              <img src="../<?=h($im['ruta_archivo'])?>" alt="" onclick="document.getElementById('hero').src=this.src">
              <?php if((int)$im['es_principal']===1): ?>
                <div class="badge">Principal</div>
              <?php endif; ?>
              <?php if($puedeGestionar): ?>
                <div>
                  <?php if((int)$im['es_principal']!==1): ?>
                  <button class="btn primary" onclick="marcarPrincipal(<?= (int)$im['idImagen'] ?>)">Hacer principal</button>
                  <?php endif; ?>
                  <button class="btn danger" onclick="borrarImg(<?= (int)$im['idImagen'] ?>)">Eliminar</button>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <aside>
        <div class="card">
          <h2>Información</h2>
          <p><strong>Proveedor ID:</strong> <?= (int)$svc['idProveedor'] ?></p>
          <?php if(!empty($svc['nombreNegocio'])): ?>
            <p><strong>Negocio:</strong> <?=h($svc['nombreNegocio'])?></p>
          <?php endif; ?>
          <p class="small muted">Solo el dueño del servicio o un administrador pueden gestionar imágenes.</p>
        </div>

        <?php if($puedeGestionar): ?>
        <div class="card">
          <h2>Subir imágenes</h2>
          <form class="uploader" id="formUp" method="post" enctype="multipart/form-data" action="<?=h($UPLOAD_ENDPOINT)?>">
            <input type="hidden" name="idServicio" value="<?= (int)$idServicio ?>">
            <input type="file" name="imagenes[]" accept=".jpg,.jpeg,.png,.gif,.webp" multiple>
            <button class="btn primary" type="submit">Subir</button>
          </form>
          <small class="muted">Formatos: JPG, PNG, GIF, WEBP. Máx ~8MB por archivo.</small>
          <div id="msg" class="small muted"></div>
        </div>
        <?php endif; ?>
      </aside>
    </div>
  </div>
</main>

<?php if($puedeGestionar): ?>
<script>
const UP = <?= json_encode($UPLOAD_ENDPOINT) ?>;
const PR = <?= json_encode($PRINCIPAL_ENDPOINT) ?>;
const BO = <?= json_encode($BORRAR_ENDPOINT) ?>;
const formUp = document.getElementById('formUp');
const galeria = document.getElementById('galeria');
const msg = document.getElementById('msg');
const hero = document.getElementById('hero');
const ID = <?= (int)$idServicio ?>;

formUp?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  msg.textContent = 'Subiendo...';
  const fd = new FormData(formUp);
  const r = await fetch(UP, {method:'POST', body:fd});
  const j = await r.json().catch(()=>({ok:false,error:'Respuesta inválida'}));
  if(!j.ok && !j.subidas){ msg.textContent = (j.error||'Error'); return; }
  if(j.errores && j.errores.length) alert('Algunas imágenes fallaron:\n- '+j.errores.join('\n- '));
  msg.textContent = 'OK';
  await recargar();
});

async function marcarPrincipal(id){
  const fd = new FormData(); fd.append('idImagen', id);
  const r = await fetch(PR, {method:'POST', body:fd});
  const j = await r.json().catch(()=>({ok:false}));
  if(j.ok) await recargar();
}

async function borrarImg(id){
  if(!confirm('¿Eliminar imagen?')) return;
  const fd = new FormData(); fd.append('idImagen', id);
  const r = await fetch(BO, {method:'POST', body:fd});
  const j = await r.json().catch(()=>({ok:false}));
  if(j.ok) await recargar();
}

async function recargar(){
  const r = await fetch('../php/servicio_imagen_listar.php?idServicio='+ID).catch(()=>null);
  if(!r) return;
  const j = await r.json().catch(()=>null);
  if(!j || !j.ok) return;
  galeria.innerHTML = j.imagenes.map(it => `
    <div class="item" data-id="${it.idImagen}">
      <img src="../${it.ruta_archivo}" alt="" onclick="document.getElementById('hero').src=this.src">
      ${Number(it.es_principal)===1 ? '<div class="badge">Principal</div>' : ''}
      <div>
        ${Number(it.es_principal)===1 ? '' : `<button class="btn primary" onclick="marcarPrincipal(${it.idImagen})">Hacer principal</button>`}
        <button class="btn danger" onclick="borrarImg(${it.idImagen})">Eliminar</button>
      </div>
    </div>
  `).join('');
  const principal = j.imagenes.find(x=>Number(x.es_principal)===1);
  if(principal) hero.src = '../'+principal.ruta_archivo;
}
</script>
<?php endif; ?>
</body>
</html>
