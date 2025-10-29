
const contactos = document.querySelectorAll('.contacto');
const listaContactos = document.getElementById('listaContactos');
const ventanaChat = document.getElementById('ventanaChat');
const nombreChat = document.getElementById('nombreChat');
const imagenChat = document.getElementById('imagenChat');
const mensajesChat = document.getElementById('mensajesChat');
const entradaMensaje = document.getElementById('entradaMensaje');
const botonEnviar = document.getElementById('botonEnviar');
const botonVolver = document.getElementById('botonVolver');

// Detectar parámetro 'contacto' en la URL y abrir el chat automáticamente
function getUrlParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

const contactoParam = getUrlParam('contacto');
if (contactoParam) {
    // Buscar el contacto en la lista y simular click
    let contacto = Array.from(contactos).find(c => c.dataset.id === contactoParam);
    if (contacto) {
        contacto.click();
        ventanaChat.style.display = 'flex';
        listaContactos.style.display = 'none';
    } else {
        // Si no existe en la lista, pedirlo al backend y agregarlo dinámicamente
        fetch('../PHP/Chat/Chat.php', {
            method: 'POST',
            body: new URLSearchParams({ Select: 'RecibirUsuarios', IDreceptor: contactoParam })
        })
        .then(res => res.text())
        .then(html => {
            // Eliminar cualquier contacto duplicado
            document.querySelectorAll('.contacto').forEach(c => {
                if (c.dataset.id === contactoParam) c.parentNode.removeChild(c);
            });
            // Agregar el contacto a la lista
            listaContactos.insertAdjacentHTML('beforeend', html);
            // Buscar el nuevo contacto y asignar el event listener SIEMPRE
            contacto = Array.from(document.querySelectorAll('.contacto')).find(c => c.dataset.id === contactoParam);
            if (contacto) {
                contacto.addEventListener('click', () => {
                    const { id, nombre, img } = contacto.dataset;
                    chatID = id;
                    nombreChat.textContent = nombre;
                    imagenChat.src = img;
                    cargarMensajes();
                });
                contacto.click();
                ventanaChat.style.display = 'flex';
                listaContactos.style.display = 'none';
            } else {
                chatID = contactoParam;
                nombreChat.textContent = 'Usuario';
                imagenChat.src = 'https://i.pravatar.cc/90?img=1';
                ventanaChat.style.display = 'flex';
                listaContactos.style.display = 'none';
                cargarMensajes();
            }
        });
    }
}

contactos.forEach(contacto => {
  contacto.addEventListener('click', () => {
    const { id, nombre, img } = contacto.dataset;
    chatID = id; // Set the chatID when clicking a contact
    nombreChat.textContent = nombre;
    imagenChat.src = img;
    
   //ventanaChat.style.display = 'flex';
   // listaContactos.style.display = "none";
    cargarMensajes(); // Load existing messages
  });
});

 // ID del usuario con el que estás chateando

botonEnviar.addEventListener('click', () => {
    const texto = entradaMensaje.value.trim();
    if(!texto || !chatID) return; // <-- aquí el return

    const formData = new FormData();
    formData.append('Select', 'EnviarMensaje');
    formData.append('Mensaje', texto);
    formData.append('IDreceptor', chatID);
try{
    fetch('../PHP/Chat/Chat.php', { 
      method: 'POST', 
      body: formData 
    })
    .then(res => res.text())
    .then(resp => {
        if(resp === 'ok'){
            entradaMensaje.value = '';
            cargarMensajes(); // refrescar mensajes
        }
        
    });
    }catch{console.error('Fetch error:', err)}

});

// Enter para enviar
entradaMensaje.addEventListener('keypress', e => {
  if(e.key === 'Enter') botonEnviar.click();
});

// Función para cargar mensajes
function cargarMensajes(){
    if(!chatID) return;

    const formData = new FormData();
    formData.append('Select', 'RecibirMensaje');
    formData.append('chat_id', chatID);

    fetch('../PHP/Chat/Chat.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(html => {
        const mensajesDiv = document.getElementById('mensajesChat');
        mensajesDiv.innerHTML = html;
        mensajesDiv.scrollTop = mensajesDiv.scrollHeight;
    });
}

// Auto refresco cada 2s
setInterval(() => {
    if(chatID) cargarMensajes();
}, 2000);

// For desktop
if(window.innerWidth >= 768) {
    ventanaChat.style.display = 'flex';
    listaContactos.style.display = 'block';
}
