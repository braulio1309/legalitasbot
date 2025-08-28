<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ChatInterface extends Component
{
    public $messages = [];
    public $newMessage = '';
    public $isLoading = false;
    public $userCount = 47;
    public $responseTime = '2m 30s';

    protected $listeners = ['messageSent' => 'receiveMessage'];

    public function mount()
    {
        // Mensaje inicial del bot
        $this->messages[] = [
            'type' => 'bot',
            'content' => "<strong>Bienvenido a LegalitasBot!</strong><br><br>
            Soy tu asistente legal especializado. No solo respondo consultas, <strong>también creo documentos legales personalizados:</strong>
            <br><br>
            <strong>Cartas de reclamación</strong> (inquilinos, consumidores)<br>
            <strong>Escritos de alegaciones</strong> (multas, sanciones)<br>
            <strong>Cartas laborales</strong> (despidos, reclamaciones)<br>
            <strong>Comunicaciones inmobiliarias</strong> (avisos, requerimientos)<br>
            <strong>Contratos básicos</strong> (servicios, arrendamientos)<br><br>
            <strong>¿Necesitas una consulta o un documento personalizado?</strong>"
        ];
    }

    public function sendMessage()
    {
        if (!trim($this->newMessage)) return;

        // Agregar mensaje del usuario
        $this->messages[] = [
            'type' => 'user',
            'content' => $this->newMessage
        ];

        $userMessage = $this->newMessage;
        $this->newMessage = '';
        $this->isLoading = true;

        // Simular respuesta después de un delay
        $this->dispatchBrowserEvent('message-sent');

        // En una implementación real, aquí harías la llamada a tu API
        // Por ahora simulamos una respuesta después de 2 segundos
        $this->dispatchBrowserEvent('trigger-bot-response', ['message' => $userMessage]);
    }

    public function receiveMessage($message)
    {
        $this->isLoading = false;
        
        // Agregar respuesta del bot
        $this->messages[] = [
            'type' => 'bot',
            'content' => $message
        ];
        
        // Scroll al final del chat
        $this->dispatchBrowserEvent('scroll-to-bottom');
    }

    public function render()
    {
        return view('livewire.chat-interface');
    }
}