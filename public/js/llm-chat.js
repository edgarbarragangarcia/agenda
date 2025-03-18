/**
 * Chat con LLM Local - Funcionalidad JS
 * Este archivo maneja las interacciones del chat con un LLM local
 */

document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.getElementById('chat-container');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatSendBtn = document.getElementById('chat-send-btn');
    const chatTypingIndicator = document.getElementById('chat-typing');
    
    // Mensajes predefinidos para simular un LLM local
    const predefinedResponses = [
        "Hola, soy el asistente virtual de la Clínica. ¿En qué puedo ayudarte con los grupos médicos?",
        "Los grupos médicos están organizados por especialidad y sucursal. Cada grupo tiene horarios específicos de atención.",
        "Para crear un nuevo grupo, debes asignarle un nombre, descripción, seleccionar la sucursal y asignar médicos.",
        "Los pacientes pueden ser asignados a grupos específicos según sus necesidades de tratamiento.",
        "Puedes ver la agenda completa de un grupo haciendo clic en el botón 'Ver agenda' en la tarjeta del grupo.",
        "La frecuencia de las citas por grupo depende del tipo de tratamiento que se esté realizando.",
        "Las estadísticas de éxito por grupo son confidenciales y solo visibles para administradores.",
        "Para editar un grupo, haz clic en el ícono de edición en la tarjeta correspondiente.",
        "Los médicos pueden pertenecer a múltiples grupos según su especialización y disponibilidad horaria.",
        "Existen protocolos específicos para cada grupo dependiendo del tipo de tratamiento de fertilidad."
    ];
    
    // Definir algunas palabras clave para respuestas específicas
    const keywords = {
        "crear": "Para crear un nuevo grupo, usa el botón 'Nuevo Grupo' y completa todos los campos requeridos. Recuerda asignar al menos un médico y configurar los horarios de atención.",
        "eliminar": "La eliminación de grupos debe ser aprobada por un administrador. Asegúrate de que no haya citas pendientes asociadas a ese grupo antes de eliminar.",
        "horarios": "Los horarios de cada grupo se pueden configurar seleccionando los días de la semana y el rango horario. Esta información aparecerá disponible para agendar citas.",
        "médicos": "Puedes asignar varios médicos a un mismo grupo. Esto permite flexibilidad en la atención a pacientes según la disponibilidad de cada profesional.",
        "estadísticas": "Las estadísticas detalladas por grupo están disponibles en el panel de administración. Incluyen tasas de éxito, número de pacientes atendidos y procedimientos realizados.",
        "sucursal": "Cada grupo debe estar asociado a una sucursal específica. Esto ayuda en la organización logística y asignación de recursos."
    };

    // Mensaje inicial del asistente
    addBotMessage(predefinedResponses[0]);

    // Manejar envío de mensajes
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (message === '') return;
        
        // Mostrar mensaje del usuario
        addUserMessage(message);
        chatInput.value = '';
        
        // Mostrar indicador de escritura
        showTypingIndicator();
        
        // Simular respuesta del LLM después de un retraso
        setTimeout(() => {
            // Ocultar indicador de escritura
            hideTypingIndicator();
            
            // Generar respuesta basada en palabras clave o respuesta aleatoria
            let response = getResponseForMessage(message);
            addBotMessage(response);
            
            // Scroll al final del chat
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }, 1500);
    });

    // Función para agregar mensaje del usuario al chat
    function addUserMessage(message) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('chat-message', 'user');
        messageElement.textContent = message;
        chatContainer.appendChild(messageElement);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Función para agregar mensaje del bot al chat
    function addBotMessage(message) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('chat-message', 'bot');
        messageElement.textContent = message;
        chatContainer.appendChild(messageElement);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Función para mostrar indicador de escritura
    function showTypingIndicator() {
        chatTypingIndicator.classList.remove('d-none');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Función para ocultar indicador de escritura
    function hideTypingIndicator() {
        chatTypingIndicator.classList.add('d-none');
    }

    // Función para obtener respuesta basada en el mensaje
    function getResponseForMessage(message) {
        // Convertir mensaje a minúsculas para comparación
        const messageLower = message.toLowerCase();
        
        // Verificar palabras clave
        for (const [keyword, response] of Object.entries(keywords)) {
            if (messageLower.includes(keyword)) {
                return response;
            }
        }
        
        // Si no hay coincidencia de palabras clave, devolver respuesta aleatoria
        const randomIndex = Math.floor(Math.random() * predefinedResponses.length);
        return predefinedResponses[randomIndex];
    }
});
