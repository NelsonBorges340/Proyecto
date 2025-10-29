 document.addEventListener("DOMContentLoaded", () => {


  const inputComentario = document.querySelector(".subir-coment input");
  const botonEnviar = document.querySelector(".subir-coment button");
  const comentariosContainer = document.querySelector(".comentarios");

  botonEnviar.addEventListener("click", () => {
    const texto = inputComentario.value.trim();
    if (texto === "") return;

    const comentario = document.createElement("div");
    comentario.classList.add("comentario");
    comentario.innerHTML = `
      <img src="https://i.pravatar.cc/40?u=${Date.now()}" alt="Usuario">
      <div>
        <h1>Usuario</h1>
        <p>${texto}</p>
      </div>
    `;

    comentariosContainer.prepend(comentario);
    inputComentario.value = "";
    comentariosContainer.scrollTop = 0;
  });

});
