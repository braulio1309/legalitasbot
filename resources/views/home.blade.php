@extends('layouts.app')

@section('content')
    <div class="header">
        <div class="header-content">
            <div class="justice-logo">
                <img src="{{ asset('assets/images/logo-legalitasbot.png') }}" alt="LegalitasBot Logo">
            </div>
            <h1>LegalitasBot.com</h1>
        </div>
        <div class="header-taglines">
            <p class="main-tagline"><strong>Sal de dudas con la ley en la mano en 5 minutos</strong></p>
            <p class="sub-tagline">Tu asistente legal personal con IA avanzada</p>
            <div class="live-stats">
                <div class="online-indicator"></div>
                <span id="userCount">{{ rand(40, 60) }}</span> usuarios consultando ahora
            </div>
        </div>
    </div>

    <div class="main-content">
        <!-- Componente Livewire para el chat -->
        @livewire('chat-interface')        
        <div class="info-section">
            <div class="feature-card">
                <h3>Velocidad Garantizada</h3>
                <ul>
                    <li>Respuestas en menos de 5 minutos</li>
                    <li>Cartas generadas al instante</li>
                    <li>Análisis legal inmediato</li>
                    <li>Sin esperas ni colas</li>
                    <li>Disponible 24/7 todo el año</li>
                    <li>Base de datos legal actualizada</li>
                    <li>Jurisprudencia más reciente</li>
                    <li>Legislación siempre al día</li>
                </ul>
            </div>

            <div class="feature-card">
                <h3>Autoridad Legal Completa</h3>
                <ul>
                    <li>Cartas con formato oficial</li>
                    <li>Referencias jurídicas exactas</li>
                    <li>Lenguaje técnico profesional</li>
                    <li>Artículos de ley específicos</li>
                    <li>Jurisprudencia del TS incluida</li>
                    <li>Normativa europea aplicable</li>
                    <li>Plazos legales calculados</li>
                    <li>Procedimientos paso a paso</li>
                </ul>
            </div>

            <div class="pricing">
                <h3>Planes Exclusivos</h3>
                
                <div class="price-option">
                    <h4>Starter</h4>
                    <div class="price">GRATIS</div>
                    <p>3 consultas mensuales<br>Respuestas básicas</p>
                    <button class="subscribe-btn">Actual</button>
                </div>

                <div class="price-option featured">
                    <h4>Premium</h4>
                    <div class="price">€9.99</div>
                    <p><strong>Consultas ILIMITADAS</strong><br>
                    <strong>Cartas personalizadas</strong><br>
                    Análisis jurídico profundo<br>
                    Formularios legales<br>
                    Soporte prioritario</p>
                    <button class="subscribe-btn" onclick="subscribe('premium')">Suscribirme!</button>
                </div>

                <div class="price-option">
                    <h4>Professional</h4>
                    <div class="price">€19.99</div>
                    <p><strong>Todo Premium +</strong><br>
                    <strong>Documentos complejos</strong><br>
                    Revisión de contratos<br>
                    Consultas por WhatsApp<br>
                    Asesoría estratégica</p>
                    <button class="subscribe-btn" onclick="subscribe('pro')">Contratar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonials Section -->
    <div class="testimonials">
        <h3>Lo que dicen nuestros usuarios</h3>
        <div class="testimonials-grid">
            <!-- Testimonios aquí -->
            @include('partials.testimonials')
        </div>
    </div>

    <div class="disclaimer">
        <strong>Aviso Legal:</strong> LegalitasBot proporciona información jurídica general basada en legislación española vigente. No constituye asesoramiento legal profesional específico. Para casos complejos o representación legal, recomendamos consultar con un abogado colegiado.
    </div>
@endsection

@push('scripts')
<script>
    // JavaScript específico para la página de inicio
    document.addEventListener('livewire:load', function() {
        // Inicializar partículas
        createParticles();
        
        // Actualizar contador de usuarios cada 8-15 segundos
        setInterval(updateUserCount, (8 + Math.random() * 7) * 1000);
        
        // Actualizar tiempo de respuesta cada 12-18 segundos
        setInterval(updateResponseTime, (12 + Math.random() * 6) * 1000);
    });

    // Funciones para actualizar la UI
    function updateUserCount() {
        const variation = Math.floor(Math.random() * 7) - 3;
        const currentCount = parseInt(document.getElementById('userCount').textContent);
        const newCount = Math.max(35, Math.min(78, currentCount + variation));
        document.getElementById('userCount').textContent = newCount;
    }

    function updateResponseTime() {
        const responseTimes = ['1m 45s', '2m 15s', '2m 30s', '3m 10s', '1m 55s', '2m 45s', '3m 20s', '2m 05s'];
        const randomTime = responseTimes[Math.floor(Math.random() * responseTimes.length)];
        document.getElementById('responseTimer').textContent = `Promedio: ${randomTime}`;
    }

    // Función de suscripción
    function subscribe(plan) {
        // Redirigir al proceso de suscripción
        window.location.href = `/subscription/${plan}`;
    }
</script>
@endpush