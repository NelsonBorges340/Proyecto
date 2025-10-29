
        const modalNuevoServicio = document.getElementById('modal-nuevo-servicio');
    const cerrarModalServicioBtn = document.getElementById('cerrar-modal-servicio');
    const guardarNuevoServicioBtn = document.getElementById('guardar-nuevo-servicio');
    const inputNombreServicio = document.getElementById('input-nombre-servicio');
    const inputDescripcionServicio = document.getElementById('input-descripcion-servicio');

    const selectcategoria = document.getElementById('select-Categoria');
    const inputPrecioServicio = document.getElementById('input-precio-servicio');
    const inputArchivosServicio = document.getElementById('input-archivos-servicio');
    const previewArchivos = document.getElementById('preview-archivos');


     if (guardarNuevoServicioBtn) {
        guardarNuevoServicioBtn.addEventListener('click', () => {
            const nombre = inputNombreServicio.value.trim();
            const descripcion = inputDescripcionServicio.value.trim();

            const precio = inputPrecioServicio.value.trim();
            const archivos = inputArchivosServicio.files;
            const IDcategoria = selectcategoria.value;


           const formData = new FormData();
            formData.append('ServiciosCrear', '1');
            formData.append('Nombre', nombre);
            formData.append('Descripcion', descripcion);
            formData.append('Precio', precio);
            formData.append('Categoria', IDcategoria);
for (let pair of formData.entries()) {
    console.log(pair[0]+ ': '+ pair[1]);
}

            fetch('../PHP/Servicios/servicios.php', {
        method: 'POST',
        body: formData // FormData se encarga del content-type
    })
   


            const nuevoServicioHTML = document.createElement('div');
            nuevoServicioHTML.classList.add('servicio');
            const idServicio = `servicio-${Date.now()}`;
            
            // Añadir el botón de eliminar aquí
            let servicioHTML = `
                <button class="eliminar-servicio-btn" data-id="${idServicio}">Eliminar</button>
                <h3 data-nombre="${nombre}">${nombre}</h3>
                <p><strong>Ubicación:</strong> <span>${selectUbicacion.options[selectUbicacion.selectedIndex].text}</span></p>
                <p><strong>Precio:</strong> $<span data-precio="${precio}">${precio}</span></p>
                <p class="descripcion-corta" data-descripcion="${descripcion}">${descripcion.substring(0, 100)}${descripcion.length > 100 ? '...' : ''}</p>
                <div class="servicio-galeria" id="galeria-${idServicio}"></div>
                <button class="detalles-btn">detalles</button>
            `;

            nuevoServicioHTML.innerHTML = servicioHTML;
            nuevoServicioHTML.dataset.id = idServicio; // Añadir el ID al elemento del servicio

            const galeria = nuevoServicioHTML.querySelector(`#galeria-${idServicio}`);
            
            Array.from(archivos).forEach(archivo => {
                const mediaElement = document.createElement(archivo.type.startsWith('image') ? 'img' : 'video');
                mediaElement.src = URL.createObjectURL(archivo);
                if (archivo.type.startsWith('video')) mediaElement.controls = true;
                galeria.appendChild(mediaElement);
            });
            
            serviciosContainer.appendChild(nuevoServicioHTML);
            
            const servicioInfo = {
                id: idServicio,
                nombre,
                descripcion,
                ubicacion,
                precio,
                archivos: Array.from(archivos)
            };
            servicios.push(servicioInfo);

            
            toggleModal(modalNuevoServicio, false);
            alert('Servicio agregado exitosamente!');
        
})};