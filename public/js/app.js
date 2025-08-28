// Funciones generales para la aplicación

// Inicializar partículas
function createParticles() {
    const particlesContainer = document.getElementById('particles');
    if (!particlesContainer) return;
    
    for (let i = 0; i < 50; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 6 + 's';
        particle.style.animationDuration = (3 + Math.random() * 3) + 's';
        particlesContainer.appendChild(particle);
    }
}

// Inicializar cuando se carga el documento
document.addEventListener('DOMContentLoaded', function() {
    createParticles();
});

// Livewire event listeners
document.addEventListener('livewire:load', function() {
    // Escuchar evento para scroll al final del chat
    Livewire.on('scroll-to-bottom', () => {
        const chatContainer = document.getElementById('chatContainer');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    });
    
    // Escuchar evento para simular respuesta del bot
    Livewire.on('trigger-bot-response', (message) => {
        // Simular delay de respuesta
        setTimeout(() => {
            // En una implementación real, aquí llamarías a tu API
            const responses = [
                "Entiendo tu consulta sobre <strong>" + message + "</strong>. Según el artículo 18 de la Ley de Arrendamientos Urbanos, el propietario necesita tu consentimiento para entrar, excepto en casos de emergencia.",
                "Analizando tu situación con <strong>" + message + "</strong>. El Código Civil establece en su artículo 1902 que...",
                "Para <strong>" + message + "</strong>, la jurisprudencia del Tribunal Supremo ha establecido que...",
                "Voy a generar una carta personalizada para <strong>" + message + "</strong>. Necesitaré algunos datos adicionales para completarla."
            ];
            
            const randomResponse = responses[Math.floor(Math.random() * responses.length)];
            
            // Emitir evento con la respuesta
            Livewire.emit('messageSent', randomResponse);
        }, 2000);
    });
});