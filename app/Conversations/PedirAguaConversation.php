<?php

namespace App\Conversations;

use App\Models\Customer;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;

class PedirAguaConversation extends Conversation
{
    private $chat;
    protected $customer;
    protected $quantity;
    protected $shippingAddress;

    public function __construct($chat)
    {
        $this->chat = $chat;
    }


    public function checkCustomerExists()
    {

        $customer = Customer::where('chatId', $this->chat['chatId'])->first();
        if (!$customer) {
            $customer = new Customer();
            $customer->name = $this->chat['name'];
            $customer->chatId = $this->chat['chatId'];
            $customer->save();

            $this->askQuantity();
        }

        $this->askQuantity();
    }


    public function askQuantity()
    {
        $this->ask('Informe uma quantidade. _Exemplo: 2_', function (Answer $answer) {

            $qty = $answer->getValue();

            //TODO Aplicar regex pra verificar se foi digitado um numero válido.
            if (intval($qty) <= 0) {
                $this->say('Informe uma quantidade válida');
                $this->repeat();
            }

            $this->askAddress();
        });
    }

    public function askAddress()
    {


        $this->ask("Qual será o endereço de entrega?", function (Answer $answer) {

            $shippingAddress = $answer->getText();

            if ($shippingAddress == '') {
                $this->say('Informe seu Endereco');
                $this->repeat();
            }
            $this->shippingAddress = $shippingAddress;

            $this->ask("Qual forma de Pagamento: 1-Dinheiro(a vista) \n2-Cartao de Crédito \n3-Cartao de Débito", function (Answer $answer) {

                $possibles = array('1', '2', '3');
                $pm = $answer->getText();

                if (!in_array($pm, $possibles)) {
                    $this->say("Escolha uma das opções! -Dinheiro(a vista) \n2-Cartao de Crédito \n3-Cartao de Débito");
                }

                switch ($pm) {
                    case "1":
                        return $this->say('Pagamento a Vista');
                        break;
                    case "2":
                        return $this->say('Pagamento cartao de credito');
                        break;
                    case "3":
                        return $this->say('Pagamento cartao de debito');
                        break;
                }
            });
        });
    }

    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->checkCustomerExists();
    }
}
