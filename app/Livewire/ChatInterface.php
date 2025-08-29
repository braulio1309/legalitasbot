<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;

class ChatInterface extends Component
{
    public $messages = [];
    public $newMessage = '';
    public $isLoading = false;
    public $userCount = 47;
    public $responseTime = '2m 30s';
    
    // Propiedades para autenticación
    public $showLoginForm = false;
    public $showRegisterForm = false;
    public $loginEmail = '';
    public $loginPassword = '';
    public $registerName = '';
    public $registerEmail = '';
    public $registerPassword = '';

    public function mount()
    {
        // Mensaje inicial del bot
        $this->showWelcomeMessage();
    }

    private function showWelcomeMessage()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->messages[] = [
                'type' => 'bot',
                'content' => "<strong>¡Bienvenido de nuevo, {$user->name}!</strong><br><br>
                Tu plan actual: <strong>" . strtoupper($user->plan) . "</strong><br>
                Consultas disponibles: " . ($user->plan === 'free' ? '3 mensuales' : 'ILIMITADAS') . "<br><br>
                ¿En qué puedo ayudarte hoy?"
            ];
        } else {
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

            // Mostrar mensaje de registro después de 2 segundos
            $this->dispatch('show-auth-message');
        }
    }

     public function sendMessage()
    {
        if (!Auth::check()) {
            $this->showLoginForm = true;
            $this->addAuthMessage();
            return;
        }

        if (!trim($this->newMessage)) return;

        // Agregar mensaje del usuario
        $this->messages[] = [
            'type' => 'user',
            'content' => $this->newMessage
        ];

        $userMessage = $this->newMessage;
        $this->newMessage = '';
        $this->isLoading = true;

        // Simular respuesta del bot (ahora desde PHP)
        $this->simulateBotResponse($userMessage);
    }

    private function simulateBotResponse($userMessage)
    {
        // Aquí es donde en el futuro integrarás con tu API
        // Por ahora simulamos una respuesta localmente
        
        $responses = [
            "Entiendo tu consulta sobre <strong>" . e($userMessage) . "</strong>. Según el artículo 18 de la Ley de Arrendamientos Urbanos, el propietario necesita tu consentimiento para entrar, excepto en casos de emergencia.",
            "Analizando tu situación con <strong>" . e($userMessage) . "</strong>. El Código Civil establece en su artículo 1902 que el que por acción u omisión causa daño a otro, interviniendo culpa o negligencia, está obligado a reparar el daño causado.",
            "Para <strong>" . e($userMessage) . "</strong>, la jurisprudencia del Tribunal Supremo ha establecido que se deben considerar varios factores antes de proceder.",
            "Voy a generar una carta personalizada para <strong>" . e($userMessage) . "</strong>. Necesitaré algunos datos adicionales para completarla correctamente."
        ];
        
        $randomResponse = $responses[array_rand($responses)];
        
        // Simular delay de procesamiento (2 segundos)
        sleep(2);
        
        $this->addBotResponse($randomResponse);
    }

    public function addBotResponse($response)
    {
        $this->messages[] = [
            'type' => 'bot',
            'content' => $response
        ];
        
        $this->isLoading = false;
        $this->dispatch('scroll-to-bottom');
    }


    private function addAuthMessage()
    {
        $authMessage = "<strong>Hola! Para comenzar necesitas una cuenta.</strong><br><br>
        <strong>REGISTRO GRATUITO</strong> incluye:<br>
        ✅ 3 consultas legales mensuales<br>
        ✅ Respuestas con referencias legales<br>
        ✅ Acceso 24/7<br><br>";

        if ($this->showLoginForm) {
            $authMessage .= $this->getLoginForm();
        } elseif ($this->showRegisterForm) {
            $authMessage .= $this->getRegisterForm();
        } else {
            $authMessage .= "
            <div style='text-align: center; margin: 15px 0;'>
                <button onclick='Livewire.dispatch(\"show-login\")' style='background: #4c3baa; color: white; padding: 12px 25px; border: none; border-radius: 25px; margin: 5px; cursor: pointer; font-size: 16px;'>
                    Iniciar Sesión
                </button>
                <button onclick='Livewire.dispatch(\"show-register\")' style='background: #ffd700; color: #2d1b69; padding: 12px 25px; border: none; border-radius: 25px; margin: 5px; cursor: pointer; font-size: 16px; font-weight: bold;'>
                    Crear Cuenta
                </button>
            </div>";
        }

        $this->messages[] = [
            'type' => 'bot',
            'content' => $authMessage
        ];
        
        $this->dispatch('scroll-to-bottom');
    }

    private function getLoginForm()
    {
        return "
        <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 15px; margin: 15px 0;'>
            <h4 style='color: #2d1b69; margin-bottom: 15px; text-align: center;'>Iniciar Sesión</h4>
            
            <div style='margin-bottom: 15px;'>
                <input type='email' wire:model='loginEmail' placeholder='Tu email' 
                       style='width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;'>
            </div>
            
            <div style='margin-bottom: 20px;'>
                <input type='password' wire:model='loginPassword' placeholder='Tu contraseña' 
                       style='width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;'>
            </div>
            
            <div style='display: flex; gap: 10px; justify-content: center;'>
                <button wire:click='login' style='background: #ffd700; color: #2d1b69; padding: 12px 25px; border: none; border-radius: 25px; cursor: pointer; font-weight: bold;'>
                    Entrar
                </button>
                <button wire:click='cancelAuth' style='background: #ccc; color: #666; padding: 12px 20px; border: none; border-radius: 25px; cursor: pointer;'>
                    Cancelar
                </button>
            </div>
            
            <p style='text-align: center; margin-top: 15px; color: #666;'>
                ¿No tienes cuenta? <a href='#' wire:click='showRegister' style='color: #2d1b69; text-decoration: underline;'>Regístrate aquí</a>
            </p>
        </div>";
    }

    private function getRegisterForm()
    {
        return "
        <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 15px; margin: 15px 0;'>
            <h4 style='color: #2d1b69; margin-bottom: 15px; text-align: center;'>Crear Cuenta Gratis</h4>
            
            <div style='margin-bottom: 15px;'>
                <input type='text' wire:model='registerName' placeholder='Tu nombre completo' 
                       style='width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;'>
            </div>
            
            <div style='margin-bottom: 15px;'>
                <input type='email' wire:model='registerEmail' placeholder='Tu email' 
                       style='width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;'>
            </div>
            
            <div style='margin-bottom: 20px;'>
                <input type='password' wire:model='registerPassword' placeholder='Contraseña (min. 6 caracteres)' 
                       style='width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;'>
            </div>
            
            <div style='display: flex; gap: 10px; justify-content: center;'>
                <button wire:click='register' style='background: #ffd700; color: #2d1b69; padding: 12px 25px; border: none; border-radius: 25px; cursor: pointer; font-weight: bold;'>
                    Crear Cuenta
                </button>
                <button wire:click='cancelAuth' style='background: #ccc; color: #666; padding: 12px 20px; border: none; border-radius: 25px; cursor: pointer;'>
                    Cancelar
                </button>
            </div>
            
            <p style='text-align: center; margin-top: 15px; color: #666;'>
                ¿Ya tienes cuenta? <a href='#' wire:click='showLogin' style='color: #2d1b69; text-decoration: underline;'>Inicia sesión</a>
            </p>
        </div>";
    }

    // Métodos de autenticación
    public function showLogin()
    {
        $this->showLoginForm = true;
        $this->showRegisterForm = false;
        $this->addAuthMessage();
    }

    public function showRegister()
    {
        $this->showRegisterForm = true;
        $this->showLoginForm = false;
        $this->addAuthMessage();
    }

    public function cancelAuth()
    {
        $this->showLoginForm = false;
        $this->showRegisterForm = false;
        $this->reset(['loginEmail', 'loginPassword', 'registerName', 'registerEmail', 'registerPassword']);
        $this->resetErrorBag();
        $this->addAuthMessage();
    }

    public function login()
    {
        $this->validate([
            'loginEmail' => 'required|email',
            'loginPassword' => 'required|min:6'
        ]);

        if (Auth::attempt(['email' => $this->loginEmail, 'password' => $this->loginPassword])) {
            $user = Auth::user();
            
            // Mostrar mensaje de éxito
            $this->messages[] = [
                'type' => 'bot',
                'content' => "<strong>¡Bienvenido de nuevo, {$user->name}!</strong><br><br>
                Sesión iniciada correctamente. Ya puedes hacer tus consultas legales.<br><br>
                Plan actual: <strong>" . strtoupper($user->plan) . "</strong><br>
                Consultas disponibles: " . ($user->plan === 'free' ? '3 mensuales' : 'ILIMITADAS')
            ];

            $this->showLoginForm = false;
            $this->showRegisterForm = false;
            $this->reset(['loginEmail', 'loginPassword']);
            $this->dispatch('scroll-to-bottom');
        } else {
            $this->addError('login', 'Credenciales incorrectas. Inténtalo de nuevo.');
        }
    }

    public function register()
    {
        $this->validate([
            'registerName' => 'required|min:3',
            'registerEmail' => 'required|email|unique:users,email',
            'registerPassword' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $this->registerName,
            'email' => $this->registerEmail,
            'password' => Hash::make($this->registerPassword),
            'plan' => 'free'
        ]);

        Auth::login($user);

        // Mostrar mensaje de éxito
        $this->messages[] = [
            'type' => 'bot',
            'content' => "<strong>¡Cuenta creada exitosamente, {$user->name}!</strong><br><br>
            Ya tienes acceso a LegalitasBot con el plan <strong>GRATUITO</strong>.<br><br>
            ✅ 3 consultas mensuales incluidas<br>
            ✅ Respuestas legales profesionales<br>
            ✅ Acceso 24/7<br><br>
            ¡Comienza haciendo tu primera consulta legal!"
        ];

        $this->showLoginForm = false;
        $this->showRegisterForm = false;
        $this->reset(['registerName', 'registerEmail', 'registerPassword']);
        $this->dispatch('scroll-to-bottom');
    }

    public function logout()
    {
        Auth::logout();
        $this->messages = [];
        $this->showWelcomeMessage();
    }

    #[On('show-login')]
    public function handleShowLogin()
    {
        $this->showLogin();
    }

    #[On('show-register')]
    public function handleShowRegister()
    {
        $this->showRegister();
    }

    public function render()
    {
        return view('livewire.chat-interface');
    }
}