<?php

$botman = resolve('botman');

$botman->hears('1', function ($bot) {
    $chat = $bot->getMessage()->getPayload();
    $bot->startConversation(new \App\Conversations\IniciarPedidoConversation($chat));
});

$botman->hears('2', function ($bot) {
    $chat = $bot->getMessage()->getPayload();
    $bot->startConversation(new \App\Conversations\PedirAguaConversation($chat));
});

$botman->hears('sair', function ($bot) {
    $bot->reply('Seu atendimento foi encerrado, Volte sempre.');
})->stopsConversation();

$botman->fallback(function ($bot) {
    $chat = $bot->getMessage()->getPayload();
    $chatName = $chat['name'];
    $bot->reply("*Olá _{$chatName}_ escolha uma das opções:*    
    \n1-Fazer um pedido.        
    \n2-Promoções.
    \n3-Fazer uma Sugestão ou Critica.
    \n4-Avaliar atendimento. 
    \nou Digite *sair* a qualquer momento pra encerrar o atendimento.");
});
