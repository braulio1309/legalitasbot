<div>
    <!-- Chat Section -->
    <div class="chat-section">
        <div class="chat-header">
            <h2>Consulta Legal Instantánea</h2>
            <p>Respuesta legal completa en menos de 5 minutos</p>
            <div class="speed-indicator">
                <div class="timer-badge">
                    <span id="responseTimer">Promedio: {{ $responseTime }}</span>
                </div>
            </div>

            @auth
            <div style="margin-top: 10px;">
                <small style="color: #2d1b69;">
                    Conectado como: {{ Auth::user()->name }}
                    (<a href="#" wire:click="logout" style="color: #ff6b6b; text-decoration: underline;">Cerrar sesión</a>)
                </small>
            </div>
            @endauth
        </div>

        <div class="chat-container" id="chatContainer">
            @foreach($messages as $index => $message)
            <div class="message {{ $message['type'] }}-message">
                {!! $message['content'] !!}

                <!-- Mostrar errores de validación debajo del formulario correspondiente -->
                @if($message['type'] === 'bot' && str_contains($message['content'], 'Iniciar Sesión'))
                @error('login') <div style="color: #ff6b6b; text-align: center; margin-top: 10px; padding: 10px; background: rgba(255,107,107,0.1); border-radius: 8px;">{{ $message }}</div> @enderror
                @error('loginEmail') <div style="color: #ff6b6b; text-align: center; margin-top: 5px;">{{ $message }}</div> @enderror
                @error('loginPassword') <div style="color: #ff6b6b; text-align: center; margin-top: 5px;">{{ $message }}</div> @enderror
                @endif

                @if($message['type'] === 'bot' && str_contains($message['content'], 'Crear Cuenta'))
                @error('registerName') <div style="color: #ff6b6b; text-align: center; margin-top: 5px;">{{ $message }}</div> @enderror
                @error('registerEmail') <div style="color: #ff6b6b; text-align: center; margin-top: 5px;">{{ $message }}</div> @enderror
                @error('registerPassword') <div style="color: #ff6b6b; text-align: center; margin-top: 5px;">{{ $message }}</div> @enderror
                @endif



            </div>

            @endforeach

            @if($showSuscribe)
            <div wire:ignore class="message bot-message" style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 15px; margin: 15px 0;'>
                @if ($selectedPlan === 'premium')
                <div class="plan-details">
                    <h3 class="plan-title">PLAN PREMIUM</h3>
                    <ul>
                        <li>Consultas ILIMITADAS</li>
                        <li>Cartas personalizadas</li>
                        <li>Análisis jurídico profundo</li>
                        <li>Formularios legales</li>
                        <li>Soporte prioritario</li>
                    </ul>
                </div>
                @endif

                @if ($selectedPlan === 'professional')
                <div class="plan-details">
                    <h3 class="plan-title">PLAN PROFESSIONAL</h3>
                    <ul>
                        <li>Todo Premium </li>
                        <li>Documentos complejos</li>
                        <li>Revisión de contratos</li>
                        <li>Asesoría estratégica</li>
                    </ul>
                </div>
                @endif

                <form id="payment-form" wire:submit.prevent="subscribe" class="payment-form">
                    <div id="card-element" class="stripe-card-element"></div>
                    <input type="hidden" id="payment-method" wire:model="paymentMethod">
                    <button type="submit" class="subscribe-button">
                        Suscribirse
                    </button>
                </form>
            </div>
            @endif
        </div>


        <div class="input-section">
            <input type="text"
                id='messageInput'
                class="question-input"
                wire:model="newMessage"
                placeholder="Ej: ¿Mi casero puede entrar sin avisar? | Crea una carta para reclamar mi fianza"
                wire:keydown.enter="sendMessage"
                {{ $isLoading ? 'disabled' : '' }}>
            <button class="send-btn" wire:click="sendMessage" wire:loading.attr="disabled" {{ $isLoading ? 'disabled' : '' }}>
                <span wire:loading.remove>Consultar</span>
                <span wire:loading>Enviando...</span>
            </button>
        </div>

        <div class="loading" style="{{ $isLoading ? 'display: block;' : 'display: none;' }}">
            Analizando legislación aplicable a tu caso...
        </div>
    </div>

    @script
    <script>
        // Escuchar eventos de Livewire
        $wire.on('scroll-to-bottom', () => {
            const chatContainer = document.getElementById('chatContainer');
            if (chatContainer) {
                requestAnimationFrame(() => {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                });
            }
        });

        $wire.on('message-sent', () => {
            // Enfocar y limpiar el campo de entrada
            const messageInput = document.getElementById('messageInput');
            messageInput.value = ''
            if (messageInput) {
                messageInput.focus();
            }
        });

        $wire.on('show-auth-message', () => {
            setTimeout(() => {
                $wire.addBotResponse(`<strong>Hola! Para comenzar necesitas una cuenta.</strong><br><br>
            <strong>REGISTRO GRATUITO</strong> incluye:<br>
            ✅ 3 consultas legales mensuales<br>
            ✅ Respuestas con referencias legales<br>
            ✅ Acceso 24/7<br><br>
            <button onclick="Livewire.dispatch('show-register')" style="background: #ffd700; color: #2d1b69; border: none; padding: 12px 25px; border-radius: 25px; margin: 5px; cursor: pointer; font-weight: bold;">
                Crear cuenta gratis
            </button>
            <button onclick="Livewire.dispatch('show-login')" style="background: #4c3baa; color: white; border: none; padding: 12px 25px; border-radius: 25px; margin: 5px; cursor: pointer;">
                Iniciar sesión
            </button>`);
            }, 2000);
        });

        let stripe;

        // Inicializar Stripe





        // Función global para suscripción
        window.subscribe = function(plan) {
            // 1. Mostrar el formulario de suscripción
            $wire.showSubscriptionForm(plan);

            // 2. Retrasar la inicialización de Stripe para permitir que el DOM se actualice
            setTimeout(() => {
                // Inicialización de Stripe
                const stripe = Stripe("{{ env('STRIPE_KEY') }}");
                const elements = stripe.elements();
                const cardElement = elements.create('card', {
                    style: {
                        base: {
                            fontSize: '16px'
                        }
                    }
                });

                cardElement.mount('#card-element');

                // Manejo del formulario
                const form = document.getElementById('payment-form');
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const {
                        paymentMethod,
                        error
                    } = await stripe.createPaymentMethod({
                        type: 'card',
                        card: cardElement,
                    });

                    if (error) {
                        // Manejo de errores de Stripe
                        $wire.addBotResponse(`<div style='background: rgba(255,107,107,0.1); padding: 20px; border-radius: 15px; margin: 15px 0; border: 2px solid #ff6b6b;'>
                    <h4 style='color: #ff6b6b; text-align: center; margin-bottom: 15px;'>Error en el pago</h4>
                    <p style='text-align: center; color: #2d1b69;'>
                        Hubo un problema procesando tu pago, intentar mas tarde
                    </p>
                </div>`);
                    } else {
                        // Éxito: enviar el ID del método de pago y el plan a Livewire
                        @this.set('paymentMethod', paymentMethod.id);
                        // Aquí, 'plan' es la variable que se pasó a la función 'subscribe'
                        // y es accesible en este ámbito (cierre o closure).
                        @this.call('subscribe', plan);
                    }
                });
            }, 2000);
        }



        // Función para procesar pago directo (alternativa)
        async function processDirectPayment(plan) {
            try {
                initStripe();

                // Crear payment method
                const {
                    paymentMethod,
                    error
                } = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                });

                if (error) {
                    throw new Error(error.message);
                }

                // Enviar a Livewire para procesar con Cashier
                $wire.call('processSubscription', plan, paymentMethod.id);

            } catch (error) {
                console.error('Error en pago directo:', error);
                $wire.set('stripeError', error.message);
            }
        }

        // Scroll automático
        Livewire.hook('message.processed', (message, component) => {
            const chatContainer = document.getElementById('chatContainer');
            if (chatContainer && component.name === 'chat-interface') {
                setTimeout(() => {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }, 100);
            }
        });

        // Escuchar eventos de suscripción
        Livewire.on('show-subscription', ({
            plan
        }) => {
            if (typeof $wire !== 'undefined') {
                $wire.showSubscriptionForm(plan);
            }
        });

        // Inicializar Stripe cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            initStripe();

            // Verificar si hay un mensaje de éxito después de redirección
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('checkout') === 'success') {
                $wire.addBotResponse(`<strong>¡Pago exitoso! 🎉</strong><br><br>
            Tu suscripción ha sido activada correctamente. ¡Disfruta de consultas ilimitadas!`);
            }
        });

        // Manejar redirección de vuelta desde Stripe
        if (window.location.search.includes('session_id')) {
            // Aquí podrías verificar el estado del pago
            console.log('Volviendo de Stripe Checkout');

            // Opcional: Recargar el componente para actualizar el estado
            setTimeout(() => {
                if (typeof $wire !== 'undefined') {
                    $wire.dispatch('checkout-completed');
                }
            }, 1000);
        }
    </script>
    @endscript
</div>