<div class="chat-section" wire:ignore.self>
    <div class="chat-header">
        <h2>Consulta Legal Instantánea</h2>
        <p>Respuesta legal completa en menos de 5 minutos</p>
        <div class="speed-indicator">
            <div class="timer-badge">
                <span id="responseTimer">Promedio: {{ $responseTime }}</span>
            </div>
        </div>
    </div>

    <div class="chat-container" id="chatContainer">
        @foreach($messages as $index => $message)
            <div class="message {{ $message['type'] }}-message">
                {!! $message['content'] !!}
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

@push('scripts')
<script>
    document.addEventListener('livewire:load', function() {
        // Scroll al final del chat cuando se agregan mensajes
        Livewire.hook('message.processed', (message, component) => {
            const chatContainer = document.getElementById('chatContainer');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    });
</script>
@endpush