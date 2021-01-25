<?php

namespace App\Conversations;

use App\Models\Customer;
use App\Models\Product;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

class IniciarPedidoConversation extends Conversation
{

    protected $products;
    protected $cart;
    protected $customer;
    protected $chatId;
    protected $shipping;
    protected $payment_method;
    protected $change;
    protected $newProduct;
    protected $newAddress;
    protected $chat;

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

            $this->customer = $customer;
            $this->askProduct();
        }
        $this->customer = $customer;
        $this->askProduct();
    }


    public function askProduct()
    {

        $_products = Product::all();

        foreach ($_products as  $product) {
            $this->products .= "{$product->id}) {$product->description} - R$ {$product->price} \n";
        }

        $this->ask('O que deseja pedir?' . $this->products, function (Answer $answer) use ($_products) {

            $expected = $_products->pluck('id')->toArray();
            $selected = intval($answer->getText());

            if (!in_array($selected, $expected)) {

                $this->say('Selecione uma das opções' . $this->products);
                return $this->repeat();
            }

            $newProduct = new stdClass();
            $newProduct->id = $selected;
            $newProduct->description = $_products->find($selected)->description;


            $this->askQuantity($newProduct);
        });
    }

    public function askQuantity($newProduct)
    {
        $this->ask("Quantos você precisa?", function (Answer $answer) use ($newProduct) {
            $selected = intval($answer->getText());

            if (intval($selected <= 0)) {
                return $this->repeat();
            }

            $newProduct->qty = $selected;

            $this->cart = collect();
            $this->cart->push($newProduct);

            $this->AskAddAnotherProductOrFinish();
        });
    }

    public function AskAddAnotherProductOrFinish()
    {

        $this->ask("Escolha uma opção: 
            \n1-Adicionar outro produto
            \n2-Finalizar Pedido", function (Answer $answer) {
            $expected = [1, 2];

            $selected = intval($answer->getText());

            if (!in_array(intval($selected), $expected)) {
                return $this->repeat();
            }

            if ($selected == 1) {
                $this->askProduct();
            } else {
                $this->askForShipping();
            }
        });
    }

    public function askForShipping()
    {
        $addresses = $this->customer->addresses()->get();

        if ($addresses->count() > 0) {
            foreach ($addresses as $address) {
                $this->shipping .= "{$address->id} {$address->street} - N {$address->street_no} \n";
            }
        } else {
            return $this->askForStreet();
        }

        $this->ask("Escolha qual será o endereço de entrega ou digite *novo* para cadastrar um novo
            \n " . $this->shipping, function (Answer $answer) use ($addresses) {

            $selected = $answer->getText();

            if (trim($selected) == 'novo') {
                $this->askForStreet();
            } else {
                $expected = $addresses->pluck('id')->toArray();
                $selected = intval($answer->getText());
                if (!in_array($selected, $expected)) {
                    return $this->repeat();
                }
                $this->shipping = $addresses->find($selected);

                $this->askForPaymentMethod();
            }
        });
    }

    public function askForPaymentMethod()
    {
        $this->ask('Qual a forma de Pagamento?', function (Answer $answer) {
            $payment_method = $answer->getText();


            $this->askForConfirmation();
        });
    }

    public function askForConfirmation()
    {

        $this->ask('Você Confirma o seu Pedido? \n 1-Sim ou 2-Não', function (Answer $answer) {

            $option = $answer->getText();
        });
    }

    public function askForStreet()
    {
        $this->ask('Digite seu Endereço completo', function (Answer $answer) {

            $text = $answer->getText();

            if (trim($text) == '') {
                return $this->repeat();
            }
            $newAddress = new stdClass();
            $newAddress->street = $text;

            $this->askForStreetNo($newAddress);
        });
    }

    public function askForStreetNo($newAddress)
    {
        $this->ask('Qual número da sua residencia?', function (Answer $answer) use ($newAddress) {

            $text = $answer->getText();

            if (trim($text) == '') {
                return $this->repeat();
            }
            $newAddress->street_no = $text;

            $this->askForDistrict($newAddress);
        });
    }

    public function askForDistrict($newAddress)
    {
        $this->ask('Qual Bairro está localizada? ', function (Answer $answer) use ($newAddress) {

            $text = $answer->getText();

            if (trim($text) == '') {
                return $this->repeat();
            }

            $newAddress->district = $text;

            $this->askForCity($newAddress);
        });
    }

    public function askForCity($newAddress)
    {
        $this->ask('Qual Cidade está localizada? ', function (Answer $answer) use ($newAddress) {

            $text = $answer->getText();

            if (trim($text) == '') {
                return $this->repeat();
            }

            $newAddress->city = $text;

            $this->askForZipCode($newAddress);
        });
    }

    public function askForZipCode($newAddress)
    {
        $this->ask('Digite o *CEP* da sua localizade(somente números), se não souber digite *não sei*', function (Answer $answer) use ($newAddress) {

            $text = $answer->getText();

            if (trim($text) == '') {
                return $this->repeat();
            }

            $newAddress->zip = $text;

            $this->askforNewAddressConfirmation($newAddress);
        });
    }

    public function askforNewAddressConfirmation($newAddress)
    {


        $newAddressConfirmation = "
        {Rua: {$newAddress->street} 
        \nNumero:{$newAddress->street_no}
        \nCidade: {$newAddress->city}
        \nBairro: {$newAddress->district}
        \nCep: {$newAddress->zip}
        \n 1- Confirmar
        \n 2- Repetir";

        $this->ask("Seu novo endereço é: " . $newAddressConfirmation, function (Answer $answer) use ($newAddress) {

            $expected = [1, 2];

            $selected = intval($answer->getText());

            if (!in_array(intval($selected), $expected)) {
                return $this->repeat();
            }

            if ($selected == 1) {
                info($this->customer);
                $this->shipping = $this->customer->addresses()->create([
                    'street' =>  $newAddress->street,
                    'street_no' => $newAddress->street_no,
                    'city' => $newAddress->city,
                    'district' => $newAddress->district,
                    'zip' => $newAddress->zip
                ]);
                $this->askForPaymentMethod();
            } else {
                $this->askForShipping();
            }
        });
    }

    public function run()
    {
        $this->checkCustomerExists();
    }
}
