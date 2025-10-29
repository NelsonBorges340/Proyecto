// app.js

const inputBusqueda = document.getElementById('q');
const precioInput = document.getElementById('precio');
const precioVal = document.getElementById('precioVal');
const btnLimpiar = document.getElementById('btnLimpiar');
const contadorResultados = document.getElementById('contador');
const etiquetas = document.querySelectorAll('.etiqueta');
const categorias = document.querySelectorAll('.categoria input');
const ordenSelect = document.getElementById('orden');
const resultados = document.querySelectorAll('.resultados .card');

function aplicarFiltros() {
  let filtrados = Array.from(resultados);

  const texto = inputBusqueda.value.toLowerCase();
  const precioMax = parseInt(precioInput.value, 10);

  // Filtrar por texto
  if (texto) {
    filtrados = filtrados.filter(card => {
      const nombre = card.dataset.nombre.toLowerCase();
      const servicio = card.dataset.servicio.toLowerCase();
      return nombre.includes(texto) || servicio.includes(texto);
    });
  }

  // Filtrar por categorías
  const categoriasChecked = Array.from(categorias).filter(c => c.checked).map(c => c.value);
  if (categoriasChecked.length) {
    filtrados = filtrados.filter(card => categoriasChecked.includes(card.dataset.servicio));
  }

  // Filtrar por etiquetas
  const etiquetasActivas = Array.from(etiquetas).filter(e => e.classList.contains('activo')).map(e => e.dataset.etiqueta);
  if (etiquetasActivas.length) {
    filtrados = filtrados.filter(card => {
      const cardEtiquetas = card.dataset.etiquetas.split(',');
      return cardEtiquetas.some(t => etiquetasActivas.includes(t));
    });
  }

  // Filtrar por precio
  filtrados = filtrados.filter(card => parseInt(card.dataset.precio, 10) <= precioMax);

  // Ocultar todos y mostrar solo filtrados
  resultados.forEach(card => card.style.display = 'none');
  filtrados.forEach(card => card.style.display = 'block');

  // Actualizar contador
  contadorResultados.textContent = `${filtrados.length} resultados`;
}

// Manejo de clic en etiquetas
etiquetas.forEach(e => {
  e.addEventListener('click', () => {
    e.classList.toggle('activo');
    aplicarFiltros();
  });
});

// Inputs y selects
inputBusqueda.addEventListener('input', aplicarFiltros);
precioInput.addEventListener('input', () => {
  precioVal.textContent = precioInput.value + '$';
  aplicarFiltros();
});
categorias.forEach(c => c.addEventListener('change', aplicarFiltros));
ordenSelect.addEventListener('change', () => {
  const filtrados = Array.from(resultados).filter(card => card.style.display !== 'none');

  let cardsOrdenadas = filtrados.slice();
  if (ordenSelect.value === 'nombre') {
    cardsOrdenadas.sort((a, b) => a.dataset.nombre.localeCompare(b.dataset.nombre));
  } else if (ordenSelect.value === 'precio_asc') {
    cardsOrdenadas.sort((a, b) => parseInt(b.dataset.precio) - parseInt(a.dataset.precio));
  } else if (ordenSelect.value === 'precio_desc') {
    cardsOrdenadas.sort((a, b) => parseInt(a.dataset.precio) - parseInt(b.dataset.precio));
  }

  // Reordenar en DOM
  const contenedor = document.getElementById('resultados');
  cardsOrdenadas.forEach(card => contenedor.appendChild(card));
});

// Botón limpiar
btnLimpiar.addEventListener('click', () => {
  inputBusqueda.value = '';
  precioInput.value = 10000;
  precioVal.textContent = '10000$';
  etiquetas.forEach(e => e.classList.remove('activo'));
  categorias.forEach(c => c.checked = false);
  aplicarFiltros();
});

// Inicializar
aplicarFiltros();
