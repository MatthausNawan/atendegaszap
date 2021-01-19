<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;

class ExampleConversation extends Conversation
{
    /**
     * First question
     */

    protected $product;

    public function askProduct()
    {
        $this->ask("Voce deseja pedir: \n1-Gas \n 2-Água \n sair-Para encerrar atendimento",function(Answer $answer){


            $this->product = $answer->getText();

            $this->say('Voce escolheu'.$this->product);
        });
    }

    /**
     * Start the conversation
     */
    public function run()
    {
        $this->askProduct();
    }
}
