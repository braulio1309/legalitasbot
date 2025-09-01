<?php

namespace App\Livewire;

use App\Models\Query;
use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use App\Services\AnthropicService;
use Stripe\StripeClient;

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

    // Propiedades para suscripción
    public $showPaymentForm = false;
    public $selectedPlan = null;
    public $plans;
    public $stripeError = '';
    public $paymentProcessing = false;
    public $showSuscribe = false;

    public $response = '';

    public $client_secret = false;


    public function mount()
    {

        $this->plans = [
            'premium' => [
                'name' => 'Premium',
                'price' => 9.99,
                'features' => ['Consultas ILIMITADAS', 'Cartas personalizadas', 'Análisis jurídico profundo', 'Formularios legales', 'Soporte prioritario'],
                'stripe_price_id' => env('STRIPE_PREMIUM_PRICE_ID') // Agregar esto a tu .env
            ],
            'professional' => [
                'name' => 'Professional',
                'price' => 19.99,
                'features' => ['Todo Premium +', 'Documentos complejos', 'Revisión de contratos', 'Consultas por WhatsApp', 'Asesoría estratégica'],
                'stripe_price_id' => env('STRIPE_PROFESSIONAL_PRICE_ID') // Agregar esto a tu .env
            ]
        ];

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
        $this->simulateBotResponse('Eres un asistente legal especializado en legislación española. Responde esta consulta si tiene que ver con la legislacion española, si no tiene nada que ver con la ley por favor responde que eres un asistente exclusivamente de la ley: '.$userMessage);
    }

    private function simulateBotResponse($userMessage)
    {

        $anthropic = new AnthropicService();
        $this->response = $anthropic->sendMessage($userMessage);


        $this->addBotResponse($this->response, $userMessage);
        $this->dispatch('message.processed');

    }

    public function addBotResponse($response, $message = '')
    {

        if (Auth::check() && !Auth::user()->canMakeQuery()) {
            $response = 'Ya consumiste sus consultas mensuales suscribete para disfrutas de consultas ILIMITADAS';
        }

        $this->messages[] = [
            'type' => 'bot',
            'content' => $response
        ];
        $question = explode(':', $message, 2);
        $this->isLoading = false;
        if(Auth::check()){
            Query::create([
                'user_id' => Auth::user()->id,
                'question' => trim($question[1]),
                'response' => $response,
                'model_used' => 'claude-opus-4-20250514',
                'token_count' => 1200,
                'cost' => 0,
                'response_time' => 1,
                'ip_address' => '127.0.0.1',
                'user_agent' => '1',
                'metadata' => '0'
            ]);
            $user = Auth::user();
            $user->increment('queries_this_month');

        }
        
        $this->dispatch('scroll-to-bottom');
        $this->dispatch('message.processed');
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

    public function showSubscriptionForm($plan)
    {
        if (!Auth::check()) {
            $this->showLoginForm = true;
            $this->addAuthMessage();
            return;
        }

        $this->selectedPlan = $plan;
        $this->showPaymentForm = true;
        $this->showSuscribe = true;
        $this->dispatch('scroll-to-bottom');

        
    }
    
    public function continueChatting()
    {
        $this->selectedPlan = null;
        $this->showPaymentForm = false;

        $this->messages[] = [
            'type' => 'bot',
            'content' => "¡Perfecto! ¿En qué más puedo ayudarte?"
        ];

        $this->dispatch('scroll-to-bottom');
    }

    public function cancelSubscription()
    {
        $this->showPaymentForm = false;
        $this->selectedPlan = null;
        $this->stripeError = '';

        $this->messages[] = [
            'type' => 'bot',
            'content' => "Entendido. ¿En qué más puedo ayudarte?"
        ];

        $this->dispatch('scroll-to-bottom');
        $this->dispatch('destroy-stripe');
    }

    #[On('show-subscription')]
    public function handleShowSubscription($plan)
    {
        $this->showSubscriptionForm($plan);
    }

    public $paymentMethod;

    public function subscribe($plan = false)
    {
        $user = Auth::user();
        try {
            $plan = $this->plans[$this->selectedPlan];
            // Suscribir al usuario al plan mensual
            $user->newSubscription($plan['name'], $plan['stripe_price_id'])
                ->create($this->paymentMethod);

            $stripe = new StripeClient(env('STRIPE_SECRET'));

            // Crear suscripción en Stripe
            $subscription = $stripe->subscriptions->create([
                'customer' => $user->stripe_id,
                'items' => [[
                    'price' => $plan['stripe_price_id'],
                ]],
            ]);

            // Guardar en tu base de datos
            $user->update([
                'plan' => $plan['name'],
                'stripe_subscription_id' => $subscription->id,
            ]);

            $this->messages[] = [
                'type' => 'bot',
                'content' => "
                <div style='background: rgba(107, 255, 154, 0.1); padding: 20px; border-radius: 15px; margin: 15px 0; border: 2px solid #6bff72ff;'>
                    <h4 style='color: #6bff72ff; text-align: center; margin-bottom: 15px;'>Pago exitoso</h4>
                    <p style='text-align: center; color: #2d1b69;'>
                        Pago exitoso, gracias por suscribirte
                    </p>
                </div>"
            ];
            $this->showSuscribe = False;
            $this->messages[] = [
                'type' => 'bot',
                'content' => "
                En que puedo ayudarte?"
            ];
        } catch (\Exception $e) {
            $this->messages[] = [
                'type' => 'bot',
                'content' => "
                <div style='background: rgba(255,107,107,0.1); padding: 20px; border-radius: 15px; margin: 15px 0; border: 2px solid #ff6b6b;'>
                    <h4 style='color: #ff6b6b; text-align: center; margin-bottom: 15px;'>Error en el pago</h4>
                    <p style='text-align: center; color: #2d1b69;'>
                        Hubo un problema procesando tu pago: {$e->getMessage()}
                    </p>
                    <div style='text-align: center; margin-top: 15px;'>
                        <button onclick='initStripe()' style='background: #ffd700; color: #2d1b69; padding: 12px 25px; border: none; border-radius: 25px; cursor: pointer; font-weight: bold; margin: 5px;'>
                            Reintentar pago
                        </button>
                        <button wire:click='cancelSubscription' style='background: #ccc; color: #666; padding: 12px 25px; border: none; border-radius: 25px; cursor: pointer; margin: 5px;'>
                            Cancelar
                        </button>
                    </div>
                </div>"
            ];
        }
        $this->dispatch('scroll-to-bottom');

    }


    public function render()
    {
        return view('livewire.chat-interface');
    }
}
