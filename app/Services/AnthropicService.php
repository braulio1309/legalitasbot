<?php

namespace App\Services;

use Anthropic\Laravel\Facades\Anthropic;
use League\CommonMark\CommonMarkConverter;


class AnthropicService
{
    protected $client;


    public function sendMessage(string $prompt): string
    {
       $response = Anthropic::messages()->create([
            'model' => 'claude-opus-4-20250514', // puedes cambiar a opus, haiku, etc.
            'max_tokens' => 1200,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $responseText = $response->content[0]->text ?? 'No se obtuvo respuesta';
    
        $converter = new CommonMarkConverter();
        $html = $converter->convert($responseText)->getContent();


        return $html;
    }
}