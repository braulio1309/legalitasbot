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
        </div>

        <div class="input-section">
            <input type="text" 
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
                chatContainer.scrollTop = chatContainer.scrollHeight;
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

        // Scroll automático cuando se agregan mensajes
        Livewire.hook('message.processed', (message, component) => {
            const chatContainer = document.getElementById('chatContainer');
            if (chatContainer && component.name === 'chat-interface') {
                setTimeout(() => {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }, 100);
            }
        });
    </script>
    @endscript
</div>