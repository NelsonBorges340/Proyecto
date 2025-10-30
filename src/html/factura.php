<?php
// html/factura.php
declare(strict_types=1);
session_start();
header('Content-Type: text/html; charset=utf-8');

require __DIR__ . '/../php/db.php';

function db() {
  global $conn, $mysqli;
  return (isset($mysqli) && $mysqli instanceof mysqli) ? $mysqli
       : ((isset($conn) && $conn instanceof mysqli) ? $conn : null);
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$DB = db();
if (!$DB) { http_response_code(500); echo 'DB error'; exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); echo 'Falta id'; exit; }

$sql = "SELECT
          r.idReserva, r.fechaReserva, r.estado,
          s.idServicio, s.titulo, s.precio, s.ubicacion,
          pr.nombreNegocio,
          u.nombreCompleto, u.email
        FROM Reserva r
        JOIN Servicio s    ON s.idServicio = r.idServicio
        LEFT JOIN Proveedor pr ON pr.idProveedor = s.idProveedor
        LEFT JOIN Usuario u    ON u.idUsuario  = r.idCliente
        WHERE r.idReserva = ?";
$st = $DB->prepare($sql);
$st->bind_param('i', $id);
$st->execute();
$rec = $st->get_result()->fetch_assoc();
$st->close();

if (!$rec) { http_response_code(404); echo 'Reserva no encontrada'; exit; }

$total = (float)$rec['precio'];
$emitida = (new DateTime())->format('Y-m-d H:i');
?>
<!doctype html>
<html lang="es">
<link rel="stylesheet" href="../css/nav.css">
<head>
<meta charset="utf-8">
<title>Factura #<?= (int)$rec['idReserva'] ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root{ --text:#0f172a; --muted:#6b7280; --line:#e5e7eb; --brand:#2563eb; --card:#fff; --bg:#f5f7fb; }
*{ box-sizing:border-box }
body{ font-family:system-ui,Segoe UI,Arial,sans-serif; color:var(--text); background:var(--bg); margin:0; }
main{ max-width:900px; margin:24px auto; background:#fff; border:1px solid var(--line); border-radius:10px; padding:24px; }
h1{ margin:0 0 6px }
.head{ display:flex; justify-content:space-between; align-items:flex-start; gap:16px; }
.badge{ background:var(--brand); color:#fff; padding:6px 10px; border-radius:999px; font-size:12px; }
table{ width:100%; border-collapse:collapse; margin-top:16px; }
th,td{ border-bottom:1px solid var(--line); padding:10px; text-align:left; }
tfoot td{ font-weight:700; }
.actions{ display:flex; gap:8px; margin-top:16px; }
button{ background:var(--brand); color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; }
button:hover{ filter:brightness(0.95); }
.small{ color:var(--muted); }
</style>
</head>
<body>
    <header class="site-nav">
  <div class="inner">
    <a class="brand" href="index.php">MANEKI</a>
    <a class="back" href="index.php">Volver al inicio</a>
  </div>
</header>
<main>
  <div class="head">
    <div>
      <h1>Factura</h1>
      <div class="badge">#<?= (int)$rec['idReserva'] ?> · <?= h(strtoupper((string)$rec['estado'])) ?></div>
      <div class="small">Emitida: <?= h($emitida) ?></div>
    </div>
    <div style="text-align:right;">
      <strong><?= h($rec['nombreNegocio'] ?? 'Proveedor') ?></strong><br>
      Servicio: <?= h($rec['titulo']) ?><br>
      Ubicación: <?= h($rec['ubicacion'] ?? '-') ?><br>
      Cliente: <?= h($rec['nombreCompleto'] ?? 'N/D') ?> (<?= h($rec['email'] ?? '-') ?>)<br>
      Fecha reservada: <?= h($rec['fechaReserva']) ?>
    </div>
  </div>

  <table>
    <thead>
      <tr><th>Concepto</th><th>Cant.</th><th>Precio unit.</th><th>Subtotal</th></tr>
    </thead>
    <tbody>
      <tr>
        <td><?= h($rec['titulo']) ?> (Reserva día <?= h($rec['fechaReserva']) ?>)</td>
        <td>1</td>
        <td>$ <?= number_format((float)$rec['precio'], 2, ',', '.') ?></td>
        <td>$ <?= number_format($total, 2, ',', '.') ?></td>
      </tr>
    </tbody>
    <tfoot>
      <tr><td colspan="3" style="text-align:right;">Total</td><td>$ <?= number_format($total, 2, ',', '.') ?></td></tr>
    </tfoot>
  </table>

  <div class="actions">
    <button onclick="window.print()">Imprimir</button>
    <button onclick="window.location.href='ver-servicio.php?id=<?= (int)$rec['idServicio'] ?>'">Volver al servicio</button>
  </div>
</main>
</body>
</html>
