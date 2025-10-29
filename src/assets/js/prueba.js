
document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  const formData = new FormData(e.target);
  const res = await fetch('/PHP/Usuario/Usuario.php', { method: 'POST', body: formData });

  const data = await res.json();


  if (data.success) {
    location.reload(); // o redirigir donde necesites
  } else {
    document.getElementById('loginError').textContent = data.message;
  }
});

document.getElementById('CambiarContraseña').addEventListener('submit', async (e) => {
  e.preventDefault();

  const formData = new FormData(e.target);
  const res = await fetch('/PHP/Usuario/Usuario.php', { method: 'POST', body: formData });
  const data = await res.json();


  if (data.success) {
    document.getElementById('CambioGood').textContent = '';
    document.getElementById('CambioError').textContent = '';
    document.getElementById('CambioGood').textContent = data.message;
    document.querySelectorAll('.PRUEBA').forEach(el => {
  el.value = '';});

  } else {
    document.getElementById('CambioError').textContent = '';
    document.getElementById('CambioGood').textContent = '';
    document.getElementById('CambioError').textContent = data.message;
    document.querySelectorAll('.PRUEBA').forEach(el => {
    el.value = '';});

  }
});

