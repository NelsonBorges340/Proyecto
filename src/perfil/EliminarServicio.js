document.querySelectorAll('.btn-eliminar').forEach(boton => {
  boton.addEventListener('click', () => {
    const id = boton.dataset.id;

    const formData = new FormData();
    formData.append('ServiciosEliminar', 1);
    formData.append('id', id);
    fetch('../PHP/Servicios/Servicios.php', {
      method: 'POST',
      body: formData
    })

      .then(res => res.text())
      .then(() => {
        // eliminar la fila del DOM
        document.querySelectorAll(`[data-id='${id}']`).forEach(el => el.remove());
      });

  });
});



document.querySelectorAll('.btn-editar').forEach(boton => {
  boton.addEventListener('click', () => {
    const id = boton.dataset.id;
    fetch(`../PHP/Servicios/ObtenerServicio.php?id=${id}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById('editar-nombre').value = data.Nombre_Servicio || '';
      document.getElementById('editar-descripcion').value = data.Descripcion || '';
      document.getElementById('editar-precio').value = data.Precio || '';
      document.getElementById('editar-id-servicio').value = id || '';
      // Aquí puedes mostrar el modal si lo ocultas con JS
      document.getElementById('modal-editar-servicio').style.display = 'block';
    });
  });
});


//Categorias
document.querySelectorAll('.delcat').forEach(boton => {
  boton.addEventListener('click', () => {
    const id = boton.dataset.idcat;

    const formData = new FormData();
    formData.append('CategoriaEliminar', 1);
    formData.append('id', id);
    fetch('../PHP/Categoria/Categoria.php', {
      method: 'POST',
      body: formData
    })

      .then(res => res.text())
      .then(() => {
        // eliminar la fila del DOM
        document.querySelectorAll(`[data-idcat='${id}']`).forEach(el => el.remove());
      });

  });
});

document.querySelectorAll('.crearCat').forEach(boton => {
  boton.addEventListener('click', () => {
    const Nombre = inputBusqueda.value.trim();

    const formData = new FormData();
    formData.append('CategoriaCrear', 1);
    formData.append('Nombre', Nombre);
    fetch('../PHP/Categoria/Categoria.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Crear el div con datos reales
          const nuevoDiv = document.createElement('div');
          nuevoDiv.className = 'categoria-item';
          nuevoDiv.dataset.idcat = data.id;
          nuevoDiv.dataset.nombre = data.nombre;

          // Crear contenido interno
          const p = document.createElement('p');
          p.textContent = data.nombre;
          nuevoDiv.appendChild(p);

          const btn = document.createElement('button');
          btn.dataset.idcat = data.id;
          btn.className = 'btn-peligro';
          btn.textContent = 'X';
          nuevoDiv.appendChild(btn);

          // Agregar al contenedor
          document.querySelector('.lista-categorias').appendChild(nuevoDiv);

          // Agregar el evento de eliminación
          btn.addEventListener('click', () => {
            const id = btn.dataset.idcat;
            const formData = new FormData();
            formData.append('CategoriaEliminar', 1);
            formData.append('id', id);
            fetch('../PHP/Categoria/Categoria.php', {
              method: 'POST',
              body: formData
            })
              .then(res => res.text())
              .then(() => {
                document.querySelectorAll(`[data-idcat='${id}']`).forEach(el => el.remove());
              });
          });
        } else {
          alert(data.message || "No se pudo crear la categoría");
        }
      });

  });
});

document.querySelectorAll('.updcat').forEach(boton => {
  boton.addEventListener('click', () => {


    const id = boton.dataset.idcat;
    // Buscar el input dentro del mismo div de la categoría
    const categoriaDiv = boton.closest('.categoria-item');
    const inputNewName = categoriaDiv.querySelector('input[name="NombreCat"]');
    const Nombre = inputNewName.value.trim();
    
    const formData = new FormData();
    formData.append('CategoriaEditar', 1);
    formData.append('id', id);
    formData.append('Nombre', Nombre);
    
    fetch('../PHP/Categoria/Categoria.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
          // actualizar el nombre en el DOM solo para este div
          categoriaDiv.dataset.nombre = Nombre;
          inputNewName.value = Nombre;
          // Si tienes un <p> para mostrar el nombre, actualízalo también
          const p = categoriaDiv.querySelector('p');
          if (p) p.textContent = Nombre;
        } else {
          alert(data.message || "No se pudo editar la categoría");
        }
      });
    });
  });
  
  const inputBusqueda = document.getElementById('q');
  const resultados = document.querySelectorAll('.lista-categorias .categoria-item');
  const contadorResultados = document.getElementById('contador-resultados');
  
  
  function aplicarFiltrosCategoria() {
    let filtrados = Array.from(resultados);
  
    const texto = inputBusqueda.value.toLowerCase();
  
    if (texto) {
      filtrados = filtrados.filter(card => {
        const nombre = card.dataset.nombre.toLowerCase();
        const incluye = nombre.includes(texto);
        return incluye;
      });
    }
  
  
    resultados.forEach(card => card.style.display = 'none');
    filtrados.forEach(card => card.style.display = 'flex');
  }
  
  
  inputBusqueda.addEventListener('input', () => {
    aplicarFiltrosCategoria();
  });
  
  //USUARIOS
  document.querySelectorAll('.updusr').forEach(boton => {
    boton.addEventListener('click', () => {


    const id = boton.dataset.idusr;
    const usuarioDiv = boton.closest('.usuario');
    const inputNewName = usuarioDiv.querySelector('input[name="NombreUsr"]');
    const inputNewCorreo = usuarioDiv.querySelector('input[name="CorreoUsr"]');
    const inputNewTel = usuarioDiv.querySelector('input[name="TelUsr"]');
    const inputNewCI = usuarioDiv.querySelector('input[name="CedlaUsr"]');

    const Nombre = inputNewName.value.trim();
    const Correo = inputNewCorreo.value.trim();
    const Telefono = inputNewTel.value.trim();
    const CI = inputNewCI.value.trim();




    const formData = new FormData();
    formData.append('UsuarioEditar', 1);
    formData.append('id', id);
    formData.append('Nombre', Nombre);
    formData.append('Correo', Correo);
    formData.append('Telefono', Telefono);
    formData.append('CI', CI);

    fetch('../PHP/Usuario/Usuario.php', {
      method: 'POST',
      body: formData
    })

      .then(res => res.json())
      .then(data => {

        if (data.success) {
          // Actualizar los inputs
          inputNewName.value = Nombre;
          inputNewCorreo.value = Correo;
          inputNewTel.value = Telefono;
          inputNewCI.value = CI;
          // Actualizar los <p> que no tienen input
          usuarioDiv.querySelectorAll('p').forEach(p => {
            if (p.querySelector('input')) return;
            if (p.textContent.includes('Nombre:')) p.innerHTML = `<strong>Nombre:</strong> ${Nombre}`;
            if (p.textContent.includes('Correo:')) p.innerHTML = `<strong>Correo:</strong> ${Correo}`;
            if (p.textContent.includes('Telefono:')) p.innerHTML = `<strong>Telefono:</strong> ${Telefono}`;
            if (p.textContent.includes('Cédula:')) p.innerHTML = `<strong>Cédula:</strong> ${CI}`;
          });
        } else {
          alert(data.message || "No se pudo editar el usuario");
        }
      });
  });
});

document.querySelectorAll('.delusr').forEach(boton => {
  boton.addEventListener('click', () => {
    const id = boton.dataset.idusr;

    const formData = new FormData();
    formData.append('UsuarioEliminarAdmin', 1);
    formData.append('id', id);
    fetch('../PHP/Usuario/Usuario.php', {
      method: 'POST',
      body: formData
    })

      .then(res => res.text())
      .then(() => {
        // eliminar la fila del DOM
        document.querySelectorAll(`[data-idusr='${id}']`).forEach(el => el.remove());
      });

  });
});


const inputBusquedaUsr = document.getElementById('BuscarUsrAdmin');
const resultadosUsr = document.querySelectorAll('.lista-usuarios .usuario-item');


function aplicarFiltrosUsuario() {
  let filtrados = Array.from(resultadosUsr);

  const texto = inputBusquedaUsr.value.toLowerCase();

  if (texto) {
    filtrados = filtrados.filter(card => {
      const nombre = card.dataset.nombre.toLowerCase();
      const incluye = nombre.includes(texto);
      return incluye;
    });
  }


  resultadosUsr.forEach(card => card.style.display = 'none');
  filtrados.forEach(card => card.style.display = 'flex');
}


inputBusquedaUsr.addEventListener('input', () => {
  aplicarFiltrosUsuario();
});


//SERVICIOS

document.querySelectorAll('.delServicio').forEach(boton => {
  boton.addEventListener('click', () => {
    const id = boton.dataset.idserv;

    const formData = new FormData();
    formData.append('ServiciosEliminarAdmin', 1);
    formData.append('id', id);
    fetch('../PHP/Servicios/Servicios.php', {
      method: 'POST',
      body: formData
    })

      .then(res => res.text())
      .then(() => {
        // eliminar la fila del DOM
        document.querySelectorAll(`[data-idserv='${id}']`).forEach(el => el.remove());
      });

  });
});



document.querySelectorAll('.updServicio').forEach(boton => {
  boton.addEventListener('click', () => {


    const id = boton.dataset.idserv;
    // Buscar el input dentro del mismo div de la categoría
    const servDiv = boton.closest('.divServicio');
    const inputNewName = servDiv.querySelector('input[name="NombreServ"]');
    const inputNewdesc = servDiv.querySelector('textarea[name="descripcion"]');
    const inputNewprecio = servDiv.querySelector('input[name="PrecioServ"]');
    const inputNewcat = servDiv.querySelector('select[name="Categoria"]');
    const inputIDusr = servDiv.querySelector('input[name="IDusuario"]');
    const Nombre = inputNewName.value.trim();
    const IDusr = inputIDusr.value.trim();
    const descripcion = inputNewdesc.value.trim();
    const precio = inputNewprecio.value.trim();
    const categoria = inputNewcat.value.trim();
    
    console.group("📦 Datos capturados del servicio");
console.log("Nombre:", Nombre);
console.log("Descripción:", descripcion);
console.log("Precio:", precio);
console.log("Categoría:", categoria);
console.log("ID Usuario:", IDusr);
console.groupEnd();

    const formData = new FormData();
    formData.append('ServiciosEditarAdmin', 1);
    formData.append('id', id);
    formData.append('IDusr', IDusr);
    formData.append('Nombre', Nombre);
    formData.append('Descripcion', descripcion);
    formData.append('Precio', precio);
    formData.append('Categoria', categoria);

    
    fetch('../PHP/Servicios/Servicios.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
        } else {
          alert(data.message || "No se pudo editar la categoría");
        }
      });
    });
  });
  
  const inptSrv = document.getElementById('BuscarServiciosAdmin');
  const resultadosSrv = document.querySelectorAll('.lista-servicios .divServicio');
  
  
  function aplicarFiltrosServicio() {
    let filtrados = Array.from(resultadosSrv);
  
    const texto = inptSrv.value.toLowerCase();
  
    if (texto) {
      filtrados = filtrados.filter(card => {
        const nombre = card.dataset.nombreserv.toLowerCase();
        const incluye = nombre.includes(texto);
        return incluye;
      });
    }
  
  
    resultadosSrv.forEach(card => card.style.display = 'none');
    filtrados.forEach(card => card.style.display = 'flex');
  }
  
  
  inptSrv.addEventListener('input', () => {
    aplicarFiltrosServicio();
  });


  // --------------notificaciones chat ----------------

  document.querySelectorAll('.notificacion').forEach(boton => {
  boton.addEventListener('click', () => {
    const id = boton.dataset.idnoti;

    const formData = new FormData();
    formData.append('ServiciosNotificacionesLeidas', 1);
    formData.append('id', id);
    fetch('../PHP/Servicios/Servicios.php', {
      method: 'POST',
      body: formData
    })

      .then(res => res.text())
      .then(() => {
        // eliminar la fila del DOM
        document.querySelectorAll(`[data-idnoti='${id}']`).forEach(el => el.remove());
      });

  });
});