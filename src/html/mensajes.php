<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: finicio.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - MANEKI</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .mensajes-container {
            max-width: 1200px;
            margin: 20px auto;
            display: flex;
            gap: 20px;
            height: calc(100vh - 100px);
        }

        .lista-conversaciones {
            width: 300px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .conversacion-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .conversacion-item:hover {
            background-color: #f5f5f5;
        }

        .conversacion-item.activa {
            background-color: #e9ecef;
        }

        .conversacion-item h3 {
            margin: 0 0 5px 0;
            font-size: 1rem;
            color: #2c3e50;
        }

        .ultimo-mensaje {
            font-size: 0.9rem;
            color: #666;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-container {
            flex: 1;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
            background-color: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }

        .chat-header h2 {
            margin: 0;
            color: #2c3e50;
        }

        .mensajes-lista {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .mensaje {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 12px;
            margin-bottom: 5px;
        }

        .mensaje.enviado {
            background-color: #6e9277;
            color: white;
            align-self: flex-end;
        }

        .mensaje.recibido {
            background-color: #f0f2f5;
            color: #2c3e50;
            align-self: flex-start;
        }

        .mensaje .tiempo {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-top: 5px;
            text-align: right;
        }

        .chat-input {
            padding: 15px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        .chat-input input {
            flex: 1;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .chat-input input:focus {
            outline: none;
            border-color: #6e9277;
        }

        .chat-input button {
            padding: 12px 24px;
            background-color: #6e9277;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chat-input button:hover {
            background-color: #5d7b63;
            transform: translateY(-1px);
        }

        .sin-seleccion {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            font-size: 1.1rem;
        }

        .fecha-mensaje {
            font-size: 0.8rem;
            color: #888;
            text-align: center;
            margin: 10px 0;
        }

        .mensaje-estado {
            font-size: 0.8rem;
            margin-top: 3px;
        }

        .no-mensajes {
            text-align: center;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <nav>
        <div class="contenedor-logo">
            <a href="index.php" class="logo-link">
                <img src="../img/MANEKI.png" alt="Logo" class="logo">
            </a>
        </div>
    </nav>

    <div class="mensajes-container">
        <div class="lista-conversaciones" id="lista-conversaciones">
            <!-- Las conversaciones se cargarán aquí dinámicamente -->
        </div>

        <div class="chat-container">
            <div id="chat-content">
                <div class="sin-seleccion">
                    Selecciona una conversación para comenzar
                </div>
            </div>
        </div>
    </div>

    <script>
        let conversacionActual = null;
        let ultimoMensaje = null;

        // Cargar conversaciones
        function cargarConversaciones() {
            fetch('../php/mensajes.php?action=conversaciones')
                .then(res => res.json())
                .then(data => {
                    const lista = document.getElementById('lista-conversaciones');
                    lista.innerHTML = '';
                    
                    if (data.length === 0) {
                        lista.innerHTML = '<div class="no-mensajes">No hay conversaciones</div>';
                        return;
                    }

                    data.forEach(conv => {
                        const fecha = new Date(conv.fechaUltimoMensaje);
                        const elemento = document.createElement('div');
                        elemento.className = 'conversacion-item';
                        elemento.innerHTML = `
                            <h3>${conv.nombreInterlocutor}</h3>
                            <p class="ultimo-mensaje">${conv.ultimoMensaje || 'No hay mensajes'}</p>
                            <small>${fecha.toLocaleDateString()}</small>
                        `;
                        elemento.onclick = () => seleccionarConversacion(conv.idConversacion, conv.nombreInterlocutor, conv.idInterlocutor);
                        lista.appendChild(elemento);
                    });
                });
        }

        // Seleccionar una conversación
        function seleccionarConversacion(idConversacion, nombreInterlocutor, idInterlocutor) {
            conversacionActual = {
                id: idConversacion,
                nombre: nombreInterlocutor,
                idInterlocutor: idInterlocutor
            };

            document.querySelectorAll('.conversacion-item').forEach(item => {
                item.classList.remove('activa');
            });
            event.currentTarget.classList.add('activa');

            const chatContainer = document.querySelector('.chat-container');
            chatContainer.innerHTML = `
                <div class="chat-header">
                    <h2>${nombreInterlocutor}</h2>
                </div>
                <div class="mensajes-lista" id="mensajes-lista"></div>
                <div class="chat-input">
                    <input type="text" id="mensaje-input" placeholder="Escribe un mensaje...">
                    <button onclick="enviarMensaje()">Enviar</button>
                </div>
            `;

            cargarMensajes(idConversacion);

            // Configurar el envío con Enter
            document.getElementById('mensaje-input').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    enviarMensaje();
                }
            });
        }

        // Cargar mensajes de una conversación
        function cargarMensajes(idConversacion) {
            fetch(`../php/mensajes.php?action=mensajes&conversacion=${idConversacion}`)
                .then(res => res.json())
                .then(data => {
                    const lista = document.getElementById('mensajes-lista');
                    lista.innerHTML = '';
                    
                    if (data.length === 0) {
                        lista.innerHTML = '<div class="no-mensajes">No hay mensajes en esta conversación</div>';
                        return;
                    }

                    let fechaAnterior = null;
                    data.forEach(msg => {
                        const fecha = new Date(msg.fechaEnvio);
                        const fechaStr = fecha.toLocaleDateString();
                        
                        if (fechaStr !== fechaAnterior) {
                            lista.innerHTML += `<div class="fecha-mensaje">${fechaStr}</div>`;
                            fechaAnterior = fechaStr;
                        }

                        lista.innerHTML += `
                            <div class="mensaje ${msg.idEmisor == <?php echo $_SESSION['usuario_id']; ?> ? 'enviado' : 'recibido'}">
                                <div class="contenido">${msg.mensaje}</div>
                                <div class="tiempo">${fecha.toLocaleTimeString()}</div>
                            </div>
                        `;
                    });
                    
                    lista.scrollTop = lista.scrollHeight;
                });
        }

        // Enviar un mensaje
        function enviarMensaje() {
            const input = document.getElementById('mensaje-input');
            const mensaje = input.value.trim();
            
            if (!mensaje || !conversacionActual) return;

            const data = {
                receptor_id: conversacionActual.idInterlocutor,
                mensaje: mensaje
            };

            fetch('../php/mensajes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    cargarMensajes(conversacionActual.id);
                    cargarConversaciones();
                }
            });
        }

        // Actualizar mensajes periódicamente
        function actualizarMensajes() {
            if (conversacionActual) {
                cargarMensajes(conversacionActual.id);
            }
        }

        // Cargar conversaciones al inicio
        cargarConversaciones();

        // Actualizar cada 10 segundos
        setInterval(() => {
            cargarConversaciones();
            actualizarMensajes();
        }, 10000);
    </script>
</body>
</html>