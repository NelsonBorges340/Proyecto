// ====================== VARIABLES Y REFERENCIAS ======================
const modales = document.querySelectorAll('.modal-general');
const botonesAbrir = document.querySelectorAll('[data-modal]');
const botonesCerrar = document.querySelectorAll('.cerrar-modal');

// ====================== FUNCIONES GENERALES ==========================
function abrirModal(id) {
    cerrarTodosLosModales();
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('visible');
}

function cerrarModal(modal) {
    if (!modal) return;
    modal.classList.remove('visible');
}

function cerrarTodosLosModales() {
    modales.forEach(modal => cerrarModal(modal));
}

// ====================== EVENTOS GLOBALES DE MODALES ==================
botonesAbrir.forEach(btn => {
    btn.addEventListener('click', () => {
        const idModal = btn.getAttribute('data-modal');
        abrirModal(idModal);
    });
});

botonesCerrar.forEach(btn => {
    btn.addEventListener('click', () => {
        const modal = btn.closest('.modal-general');
        cerrarModal(modal);
    });
});

modales.forEach(modal => {
    modal.addEventListener('click', e => {
        if (e.target === modal) cerrarModal(modal);
    });
});

window.addEventListener('keydown', e => {
    if (e.key === 'Escape') cerrarTodosLosModales();
});

// ====================== NAVEGACIÓN ENTRE MODALES ====================
const btnContrasenia = document.getElementById('btn-contrasenia');
if (btnContrasenia) btnContrasenia.addEventListener('click', () => abrirModal('modal-recuperar-contrasenia'));

const btnRegistrarseEnInicio = document.getElementById('btn-registrarse-en-inicio');
if (btnRegistrarseEnInicio) btnRegistrarseEnInicio.addEventListener('click', () => abrirModal('modal-registrarse'));

const btnVolverIniciar = document.getElementById('btn-volver-iniciar');
if (btnVolverIniciar) btnVolverIniciar.addEventListener('click', () => abrirModal('modal-iniciar'));

// ======================  REGISTRO / GESTIÓN DE CÉDULA =================
document.addEventListener('DOMContentLoaded', () => {
    // Tomamos **todos** los selects y divs de cedula
    const rolSelects = document.querySelectorAll('#rol-select'); 
    const cedulaDivs = document.querySelectorAll('#cedula-vendedor');

    rolSelects.forEach((rolSelect, i) => {
        const cedulaDiv = cedulaDivs[i];
        if (!cedulaDiv) return;

        function toggleCedulaInput() {
            cedulaDiv.style.display = rolSelect.value === 'vendedor' ? 'block' : 'none';
        }

        rolSelect.addEventListener('change', toggleCedulaInput);
        toggleCedulaInput();
    });
});

// ======================  MODAL DE AJUSTES ==============
const ajustesMenuBotones = document.querySelectorAll('.ajustes-menu button');
const ajustesPaneles = document.querySelectorAll('.ajustes-contenido .panel');

ajustesMenuBotones.forEach(btn => {
    btn.addEventListener('click', () => {
        const panelId = btn.getAttribute('data-panel');
        ajustesMenuBotones.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        ajustesPaneles.forEach(panel => panel.classList.remove('active'));
        const panelActivo = document.getElementById(panelId);
        if (panelActivo) panelActivo.classList.add('active');
    });
});

const panelPerfil = document.getElementById('perfil');
const botonPerfil = document.querySelector('.ajustes-menu button[data-panel="perfil"]');
if (panelPerfil && botonPerfil) {
    panelPerfil.classList.add('active');
    botonPerfil.classList.add('active');
}

// ====================== MODAL DE ADMIN =================
const adminMenuBotones = document.querySelectorAll('.admin-menu button');
const adminPaneles = document.querySelectorAll('.admin-contenido .panel-admin');

adminMenuBotones.forEach(btn => {
    btn.addEventListener('click', () => {
        const panelId = btn.getAttribute('data-panel');
        adminMenuBotones.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        adminPaneles.forEach(panel => panel.classList.remove('active'));
        const panelActivo = document.getElementById(panelId);
        if (panelActivo) panelActivo.classList.add('active');
    });
});

const panelUsuarios = document.getElementById('usuarios');
const botonUsuarios = document.querySelector('.admin-menu button[data-panel="usuarios"]');
if (panelUsuarios && botonUsuarios) {
    panelUsuarios.classList.add('active');
    botonUsuarios.classList.add('active');
}

// ====================== NUEVOS MODALES ===========================

// Modal Ajustar Horarios
const formHorarios = document.getElementById('form-horarios');
if (formHorarios) {
    formHorarios.addEventListener('submit', e => {
        e.preventDefault();
        alert('Horarios guardados correctamente');
        cerrarModal(document.getElementById('modal-ajustar-horarios'));
    });
}



// Modal Registrar Servicio (para clientes) (es para el calendario )
const formRegistrarServicio = document.getElementById('form-registrar-servicio');
if (formRegistrarServicio) {
    formRegistrarServicio.addEventListener('submit', e => {
        e.preventDefault();
        alert('Servicio agendado correctamente');
        cerrarModal(document.getElementById('modal-registrar-servicio'));
    });
}
