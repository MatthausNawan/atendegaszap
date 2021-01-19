<?php

$botman = resolve('botman');



$botman->hears('1', function ($bot) {
    $chat = $bot->getMessage()->getPayload();

    $bot->startConversation(new \App\Conversations\PedirGasConversation($chat));
});

$botman->hears('2', function ($bot) {

    $chat = $bot->getMessage()->getPayload();
    $bot->startConversation(new \App\Conversations\PedirAguaConversation($chat));
});



$botman->hears('sair', function ($bot) {
    $bot->reply('Atendimento Encerrado, Obrigado');
})->stopsConversation();


$botman->fallback(function ($bot) {
    $chat = $bot->getMessage()->getPayload();
    $chatName = $chat['name'];
    $bot->reply("*Olá _{$chatName}_ escolha uma das opções:*    
    \n1-Pedir um Gás
    \n2-Pedir Agua 
    \n3-Saber Preços
    \n4-Promoções 
    \nou Digite 'sair' a qualquer momento pra encerrar o atendimento");
});

