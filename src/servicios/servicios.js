// --- GALERÍA MODAL ---
const modal = document.getElementById('modal');
const modalImg = document.getElementById('modal-img');
const images = document.querySelectorAll('.galeria img');

images.forEach(img => {
  img.addEventListener('click', () => {
    modal.classList.add('active');
    modalImg.src = img.src;
  });
});

modal.addEventListener('click', () => modal.classList.remove('active'));

// --- PUNTUACIÓN ESTRELLAS ---
const estrellas = document.querySelectorAll('#puntuacion-servicio .estrella');
const puntaje = document.querySelector('#puntuacion-servicio .puntaje');

estrellas.forEach(star => {
  star.addEventListener('click', () => {
    const value = parseInt(star.dataset.value);
    estrellas.forEach(s => s.classList.toggle('seleccionada', parseInt(s.dataset.value) <= value));
    if(puntaje) puntaje.textContent = `${value}/5`;
  });
});

// --- FORMULARIO COMENTARIOS ---
const form = document.querySelector('.subir-coment');
const input = form.querySelector('input');
const button = form.querySelector('button');
const comentarios = document.querySelector('.comentarios');

button.addEventListener('click', (e) => {
  e.preventDefault();
  const text = input.value.trim();
  if(text !== '') {
    const div = document.createElement('div');
    div.className = 'comentario';
    // lea esta img es la q pone fotos random
    div.innerHTML = `
      <img src="https://i.pravatar.cc/40?img=1" alt="Tú"> 
      <div>
        <h1>Tú</h1>
        <p>${text}</p>
      </div>
    `;
    comentarios.appendChild(div);
    input.value = '';
    comentarios.scrollTop = comentarios.scrollHeight;
  }
});
