<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['usuario_id'])) {
    header('Location: finicio.html');
    exit;
}

// Debug info
echo "<!--\n";
echo "Debug info:\n";
echo "SESSION: " . print_r($_SESSION, true) . "\n";
echo "-->\n";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - MANEKI</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f5f5f5;
        }
        .chat-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            display: flex;
            gap: 20px;
            height: calc(100vh - 100px);
        }
        .chat-sidebar {
            width: 300px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            border: 1px solid #e1d0bd;
        }
        .add-chat-button {
            margin: 15px;
            padding: 10px 15px;
            background: #6e9277;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .add-chat-button:hover {
            background: #eab308;
            transform: translateY(-1px);
        }
        .add-chat-button i {
            font-size: 1.2em;
        }
        .chat-list {
            flex: 1;
            overflow-y: auto;
            border-top: 1px solid #e1d0bd;
        }
        .chat-item {
            padding: 15px;
            border-bottom: 1px solid #e1d0bd;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        .chat-item:hover {
            background: #f5f5f5;
        }
        .chat-item.active {
            background: #eab308;
        }
        .chat-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        .chat-user-name {
            font-weight: 500;
            color: #2c3e50;
        }
        .chat-time {
            font-size: 0.8rem;
            color: #666;
        }
        .chat-preview {
            font-size: 0.9rem;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .unread-badge {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: #6e9277;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        .chat-main {
            flex: 1;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            border: 1px solid #e1d0bd;
        }
        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #e1d0bd;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .chat-header h2 {
            margin: 0;
            color: #6e9277;
            flex: 1;
        }
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .message {
            max-width: 70%;
            margin-bottom: 15px;
            clear: both;
        }
        .message-content {
            padding: 12px 15px;
            border-radius: 12px;
            position: relative;
            word-wrap: break-word;
        }
        .message.sent {
            float: right;
        }
        .message.sent .message-content {
            background: #6e9277;
            color: white;
            border-bottom-right-radius: 4px;
        }
        .message.received {
            float: left;
        }
        .message.received .message-content {
            background: white;
            border: 1px solid #e1d0bd;
            color: #2c3e50;
            border-bottom-left-radius: 4px;
        }
        .message-time {
            font-size: 0.75rem;
            margin-top: 5px;
            opacity: 0.7;
            text-align: right;
        }
        .message-input-container {
            padding: 15px;
            border-top: 1px solid #e1d0bd;
            display: flex;
            gap: 10px;
            background: white;
        }
        .message-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #e1d0bd;
            border-radius: 8px;
            font-size: 0.95rem;
            resize: none;
        }
        .message-input:focus {
            outline: none;
            border-color: #6e9277;
            box-shadow: 0 0 0 3px rgba(110, 146, 119, 0.1);
        }
        .send-button {
            padding: 12px 24px;
            background: #6e9277;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }
        .send-button:hover {
            background: #eab308;
            transform: translateY(-1px);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-header h3 {
            margin: 0;
            color: #2c3e50;
        }
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
        .user-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .user-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border: 1px solid #e1d0bd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .user-item:hover {
            background: #f5f5f5;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #6e9277;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
        }
        .user-info {
            flex: 1;
        }
        .user-name {
            font-weight: 500;
            color: #2c3e50;
        }
        .user-role {
            font-size: 0.8rem;
            color: #666;
        }
        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e1d0bd;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .search-input:focus {
            outline: none;
            border-color: #6e9277;
            box-shadow: 0 0 0 3px rgba(110, 146, 119, 0.1);
        }
        .no-results {
            text-align: center;
            color: #666;
            padding: 20px;
        }
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            text-align: center;
            padding: 20px;
        }
        .empty-state-icon {
            font-size: 3rem;
            color: #e1d0bd;
            margin-bottom: 15px;
        }
        .error-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #ff4444;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .error-fatal {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            text-align: center;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px;
        }
        .error-fatal h3 {
            color: #ff4444;
            margin-bottom: 15px;
        }
        .error-fatal button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #6e9277;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .error-fatal button:hover {
            background: #eab308;
            transform: translateY(-1px);
        }
        .loading {
            padding: 20px;
            text-align: center;
            color: #666;
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


    <div class="chat-container">
        <div class="chat-sidebar">
            <div style="padding: 0 0 10px 0; background: #fff; border-bottom: 1px solid #e1d0bd;">
                <button id="newChatBtn" class="add-chat-button" style="width: 90%; margin: 15px 5%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-plus"></i> Iniciar chat
                </button>
            </div>
            <div class="chat-list" id="chatList">
                <!-- Los chats se cargarán aquí dinámicamente -->
            </div>
        </div>

        <div class="chat-main">
            <div id="chatContent" class="empty-state">
                <div class="empty-state-icon">💭</div>
                <h3>Selecciona un chat para comenzar</h3>
                <p>O inicia uno nuevo con el botón "Iniciar chat"</p>
            </div>
        </div>
    </div>

    <!-- Modal para nuevo chat -->
    <div id="newChatModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Iniciar chat con usuario</h3>
                <button class="close-modal">&times;</button>
            </div>
            <input type="text" class="search-input" id="userSearch" placeholder="Nombre o email del usuario">
            <button id="findUserBtn" class="send-button" style="margin-bottom:15px;">Buscar usuario</button>
            <div id="userSearchError" style="color:#ff4444; margin-bottom:10px; display:none;"></div>
            <div class="user-list" id="userList"></div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/your-kit-code.js"></script>
    <script>
        // Variables globales
        let currentChat = null;
        let searchTimeout = null;
        let updateInterval = null;

        // Funciones de depuración
        function debug(message, data = null) {
            const now = new Date().toLocaleTimeString();
            console.log(`[${now}] ${message}`, data || '');
        }

        function handleError(error, context) {
            const now = new Date().toLocaleTimeString();
            console.error(`[${now} - ${context}]`, error);
            // Mostrar mensaje de error al usuario
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = `Error: ${error.message || 'Ha ocurrido un error'}`;
            document.body.appendChild(errorDiv);
            setTimeout(() => errorDiv.remove(), 5000);
        }

        // Funciones del modal
        function showNewChatModal() {
            document.getElementById('newChatModal').style.display = 'flex';
            const searchInput = document.getElementById('userSearch');
            searchInput.value = '';
            searchInput.focus();
            document.getElementById('userList').innerHTML = '';
        }

        function hideNewChatModal() {
            document.getElementById('newChatModal').style.display = 'none';
            document.getElementById('userSearch').value = '';
            document.getElementById('userList').innerHTML = '';
        }

        // Search users
        // Búsqueda de usuarios
        async function searchUsers(query) {
            try {
                clearTimeout(searchTimeout);
                const userList = document.getElementById('userList');
                
                if (query.length < 2) {
                    userList.innerHTML = '<div class="no-results">Escribe al menos 2 caracteres para buscar</div>';
                    return;
                }

                userList.innerHTML = '<div class="loading">Buscando...</div>';

                const response = await fetch(`../php/chat.php?action=search_users&q=${encodeURIComponent(query)}`);
                if (!response.ok) throw new Error('Error en la búsqueda');
                
                const data = await response.json();
                userList.innerHTML = '';
                
                if (!data.users || data.users.length === 0) {
                    userList.innerHTML = '<div class="no-results">No se encontraron usuarios</div>';
                    return;
                }

                data.users.forEach(user => {
                    const div = document.createElement('div');
                    div.className = 'user-item';
                    div.innerHTML = `
                        <div class="user-avatar">${user.nombreCompleto.charAt(0).toUpperCase()}</div>
                        <div class="user-info">
                            <div class="user-name">${user.nombreCompleto}</div>
                            <div class="user-role">${user.rol}</div>
                        </div>
                    `;
                    div.onclick = () => startChat(user);
                    userList.appendChild(div);
                });
            } catch (error) {
                handleError(error, 'searchUsers');
                document.getElementById('userList').innerHTML = 
                    '<div class="error">Error al buscar usuarios. Intenta de nuevo.</div>';
            }
        }

        // Iniciar o abrir chat con usuario
        async function startChat(user) {
            try {
                const response = await fetch('../php/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'start_chat',
                        user_id: user.idUsuario
                    })
                });

                if (!response.ok) throw new Error('Error al iniciar chat');
                
                const data = await response.json();
                if (data.success && data.chat_id) {
                    hideNewChatModal();
                    await loadChats();
                    openChat(data.chat_id, user.nombreCompleto, user.idUsuario);
                } else {
                    throw new Error('Respuesta inválida del servidor');
                }
            } catch (error) {
                handleError(error, 'startChat');
                alert('Error al iniciar el chat. Por favor, intenta de nuevo.');
            }
        }

        // Cargar y mostrar lista de chats
        async function loadChats() {
            debug('Iniciando carga de chats');
            const chatList = document.getElementById('chatList');
            if (!chatList) {
                debug('Error: No se encontró el elemento chatList');
                return;
            }

            try {
                debug('Haciendo petición al servidor...');
                const response = await fetch('../php/chat.php?action=list_chats');
                debug('Respuesta recibida', {
                    status: response.status,
                    ok: response.ok
                });
                
                if (!response.ok) {
                    const text = await response.text();
                    debug('Error en la respuesta', text);
                    throw new Error(`Error al cargar chats: ${response.status} - ${text}`);
                }
                
                const data = await response.json();
                debug('Datos recibidos', data);
                chatList.innerHTML = '';

                if (!data.chats || data.chats.length === 0) {
                    chatList.innerHTML = '<div class="no-results">No hay chats activos</div>';
                    return;
                }

                data.chats.forEach(chat => {
                    const div = document.createElement('div');
                    div.className = `chat-item${currentChat?.id === chat.id ? ' active' : ''}`;
                    
                    const time = chat.last_message_time ? new Date(chat.last_message_time) : new Date(chat.last_message_at);
                    let timeString = '';
                    
                    const today = new Date();
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    
                    if (time.toDateString() === today.toDateString()) {
                        timeString = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    } else if (time.toDateString() === yesterday.toDateString()) {
                        timeString = 'Ayer';
                    } else {
                        timeString = time.toLocaleDateString();
                    }
                    
                    div.innerHTML = `
                        <div class="chat-item-header">
                            <span class="chat-user-name">${chat.chat_with_name}</span>
                            <span class="chat-time">${timeString}</span>
                        </div>
                        <div class="chat-preview">
                            ${chat.last_message || 'No hay mensajes'}
                            ${chat.unread_count > 0 ? `<span class="unread-badge">${chat.unread_count}</span>` : ''}
                        </div>
                    `;
                    
                    div.onclick = () => openChat(chat.id, chat.chat_with_name, chat.chat_with_id);
                    chatList.appendChild(div);
                });
            } catch (error) {
                handleError(error, 'loadChats');
                chatList.innerHTML = '<div class="error">Error al cargar chats. <button onclick="loadChats()">Reintentar</button></div>';
            }

        // Open chat
        function openChat(chatId, userName, userId) {
            currentChat = { id: chatId, name: userName, userId: userId };
            document.querySelectorAll('.chat-item').forEach(item => item.classList.remove('active'));
            document.querySelector(`.chat-item:nth-child(${Array.from(document.querySelectorAll('.chat-item')).findIndex(item => item.querySelector('.chat-user-name').textContent === userName) + 1})`).classList.add('active');

            const chatContent = document.getElementById('chatContent');
            chatContent.className = 'chat-main';
            chatContent.innerHTML = `
                <div class="chat-header">
                    <h2>${userName}</h2>
                </div>
                <div class="messages-container" id="messagesContainer"></div>
                <div class="message-input-container">
                    <textarea class="message-input" 
                             id="messageInput" 
                             placeholder="Escribe un mensaje..."
                             rows="1"
                             onkeypress="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); sendMessage(); }"></textarea>
                    <button class="send-button" onclick="sendMessage()">Enviar</button>
                </div>
            `;

            loadMessages();
        }

        // Cargar mensajes del chat actual
        async function loadMessages() {
            if (!currentChat) return;
            
            const container = document.getElementById('messagesContainer');
            if (!container) return;

            try {
                const response = await fetch(`../php/chat.php?action=get_messages&chat_id=${currentChat.id}`);
                if (!response.ok) throw new Error('Error al cargar mensajes');
                
                const data = await response.json();
                container.innerHTML = '';

                if (!data.messages || data.messages.length === 0) {
                    container.innerHTML = '<div class="no-results">No hay mensajes aún. ¡Sé el primero en escribir!</div>';
                    return;
                }

                let lastDate = null;
                data.messages.forEach(message => {
                    const date = new Date(message.created_at);
                    const dateStr = date.toLocaleDateString();
                    
                    if (dateStr !== lastDate) {
                        const dateDiv = document.createElement('div');
                        dateDiv.className = 'message-date';
                        dateDiv.textContent = dateStr;
                        container.appendChild(dateDiv);
                        lastDate = dateStr;
                    }

                    const div = document.createElement('div');
                    div.className = `message ${message.sender_id == <?php echo $_SESSION['usuario_id']; ?> ? 'sent' : 'received'}`;
                    
                    const timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    
                    div.innerHTML = `
                        <div class="message-content">
                            ${message.message}
                            <div class="message-time">${timeStr}</div>
                        </div>
                    `;
                    
                    container.appendChild(div);
                });

                // Scroll al último mensaje
                const shouldScroll = 
                    container.scrollTop + container.clientHeight >= 
                    container.scrollHeight - 100;
                    
                if (shouldScroll) {
                    container.scrollTop = container.scrollHeight;
                }
            } catch (error) {
                handleError(error, 'loadMessages');
                container.innerHTML = '<div class="error">Error al cargar mensajes. <button onclick="loadMessages()">Reintentar</button></div>';
            }

        // Enviar mensaje
        async function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message || !currentChat) return;

            try {
                const response = await fetch('../php/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'send_message',
                        chat_id: currentChat.id,
                        message: message
                    })
                });

                if (!response.ok) throw new Error('Error al enviar mensaje');
                
                const data = await response.json();
                if (data.success) {
                    input.value = '';
                    await Promise.all([loadMessages(), loadChats()]);
                    input.focus();
                }
            } catch (error) {
                handleError(error, 'sendMessage');
                alert('Error al enviar el mensaje. Por favor, intenta de nuevo.');
            }
        }

        // Inicialización y event listeners
        function initChat() {
            debug('Iniciando aplicación de chat');
            try {
                const newChatBtn = document.getElementById('newChatBtn');
                const closeModalBtn = document.querySelector('.close-modal');
                const userSearchInput = document.getElementById('userSearch');
                const findUserBtn = document.getElementById('findUserBtn');
                const userSearchError = document.getElementById('userSearchError');
                const userList = document.getElementById('userList');

                if (!newChatBtn || !closeModalBtn || !userSearchInput || !findUserBtn || !userSearchError) {
                    throw new Error('No se encontraron elementos necesarios del DOM');
                }

                newChatBtn.addEventListener('click', showNewChatModal);
                closeModalBtn.addEventListener('click', hideNewChatModal);

                // Buscar usuario al hacer click en el botón
                findUserBtn.addEventListener('click', async () => {
                    userSearchError.style.display = 'none';
                    userList.innerHTML = '';
                    const query = userSearchInput.value.trim();
                    if (!query) {
                        userSearchError.textContent = 'Ingresa el nombre o email del usuario.';
                        userSearchError.style.display = 'block';
                        return;
                    }
                    // Buscar usuario en backend
                    try {
                        const res = await fetch(`../php/chat.php?action=buscar_usuario&q=${encodeURIComponent(query)}`);
                        const data = await res.json();
                        if (!data.success || !data.user) {
                            userSearchError.textContent = 'El usuario no existe.';
                            userSearchError.style.display = 'block';
                            return;
                        }
                        // Mostrar usuario encontrado y botón para iniciar chat
                        userList.innerHTML = '';
                        const div = document.createElement('div');
                        div.className = 'user-item';
                        div.innerHTML = `
                            <div class="user-avatar">${data.user.nombreCompleto.charAt(0).toUpperCase()}</div>
                            <div class="user-info">
                                <div class="user-name">${data.user.nombreCompleto}</div>
                                <div class="user-role">${data.user.rol}</div>
                            </div>
                            <button class="send-button" style="margin-left:10px;" id="startChatBtn">Iniciar chat</button>
                        `;
                        userList.appendChild(div);
                        document.getElementById('startChatBtn').onclick = async () => {
                            // Iniciar chat solo si existe
                            try {
                                const resp = await fetch('../php/chat.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ action: 'start_chat', user_id: data.user.idUsuario })
                                });
                                const respData = await resp.json();
                                if (respData.success && respData.chat_id) {
                                    hideNewChatModal();
                                    await loadChats();
                                    openChat(respData.chat_id, data.user.nombreCompleto, data.user.idUsuario);
                                } else {
                                    userSearchError.textContent = 'No se pudo iniciar el chat.';
                                    userSearchError.style.display = 'block';
                                }
                            } catch (e) {
                                userSearchError.textContent = 'Error al iniciar el chat.';
                                userSearchError.style.display = 'block';
                            }
                        };
                    } catch (e) {
                        userSearchError.textContent = 'Error al buscar usuario.';
                        userSearchError.style.display = 'block';
                    }
                });

                // Cerrar modal al hacer clic fuera
                window.addEventListener('click', event => {
                    const modal = document.getElementById('newChatModal');
                    if (event.target === modal) {
                        hideNewChatModal();
                    }
                });

                // Manejar errores no capturados
                window.addEventListener('unhandledrejection', event => {
                    handleError(event.reason, 'unhandledRejection');
                });

                if (updateInterval) {
                    clearInterval(updateInterval);
                }
                loadChats();
                updateInterval = setInterval(() => {
                    if (document.visibilityState === 'visible') {
                        loadChats();
                        if (currentChat) {
                            loadMessages();
                        }
                    }
                }, 5000);
            } catch (error) {
                handleError(error, 'initChat');
                const container = document.querySelector('.chat-container');
                if (container) {
                    container.innerHTML = `
                        <div class="error-fatal">
                            <h3>Error al iniciar el chat</h3>
                            <p>${error.message}</p>
                            <button onclick="window.location.reload()">Reintentar</button>
                        </div>
                    `;
                }
            }
        }

        // Iniciar la aplicación cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', initChat);
    </script>
</body>
</html>