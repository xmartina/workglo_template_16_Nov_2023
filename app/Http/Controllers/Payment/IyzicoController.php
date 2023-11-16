<?php

namespace App\Http\Controllers\Payment;

use Session;
use Redirect;
use Illuminate\Http\Request;
use App\Utility\SettingsUtility;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\PackagePaymentController;
use App\Http\Controllers\ServicePaymentController;
use App\Http\Controllers\MilestonePaymentController;
use App\Models\User;

class IyzicoController extends Controller
{
    public function pay(){
        $data = array();
        $amount = Session::get('payment_data')['amount'];
        $payment_type = Session::get('payment_data')['payment_type'];

        $data['payment_type'] = $payment_type;
        $data['amount'] = $amount;
        $data['payment_method'] = Session::get('payment_data')['payment_method'];
        $data['package_id'] = ($payment_type == 'package_payment') ? Session::get('payment_data')['package_id'] : 0;
        $data['milestone_request_id'] = ($payment_type == 'milestone_payment') ? Session::get('payment_data')['milestone_request_id'] : 0;
        $data['service_package_id'] = ($payment_type == 'service_payment') ? Session::get('payment_data')['service_package_id'] : 0;
        $data['user_id'] = Auth::user()->id;

        $options = new \Iyzipay\Options();
        $options->setApiKey(env('IYZICO_API_KEY'));
        $options->setSecretKey(env('IYZICO_SECRET_KEY'));

        if(SettingsUtility::get_settings_value('iyzico_sandbox_checkbox') == 1) {
            $options->setBaseUrl("https://sandbox-api.iyzipay.com");
        } else {
            $options->setBaseUrl("https://api.iyzipay.com");
        }

        $iyzicoRequest = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
        $iyzicoRequest->setLocale(\Iyzipay\Model\Locale::TR);
        $iyzicoRequest->setConversationId("123456789");
        $iyzicoRequest->setPrice(round($amount));
        $iyzicoRequest->setPaidPrice(round($amount));
        $iyzicoRequest->setCurrency(env('IYZICO_CURRENCY_CODE', 'TRY'));
        $iyzicoRequest->setBasketId(rand(000000,999999));
        $iyzicoRequest->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
        $iyzicoRequest->setCallbackUrl(route('iyzico.callback', $data));

        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId("BY789");
        $buyer->setName("John");
        $buyer->setSurname("Doe");
        $buyer->setEmail(Auth::user()->email);
        $buyer->setIdentityNumber("74300864791");
        $buyer->setRegistrationAddress("Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
        $buyer->setCity("Istanbul");
        $buyer->setCountry("Turkey");
        $iyzicoRequest->setBuyer($buyer);

        $shippingAddress = new \Iyzipay\Model\Address();
        $shippingAddress->setContactName("Jane Doe");
        $shippingAddress->setCity("Istanbul");
        $shippingAddress->setCountry("Turkey");
        $shippingAddress->setAddress("Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
        $iyzicoRequest->setShippingAddress($shippingAddress);

        $billingAddress = new \Iyzipay\Model\Address();
        $billingAddress->setContactName("Jane Doe");
        $billingAddress->setCity("Istanbul");
        $billingAddress->setCountry("Turkey");
        $billingAddress->setAddress("Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
        $iyzicoRequest->setBillingAddress($billingAddress);

        $basketItems = array();
        $firstBasketItem = new \Iyzipay\Model\BasketItem();
        $firstBasketItem->setId(rand(1000,9999));
        $firstBasketItem->setName(ucfirst(str_replace('_', ' ', $payment_type)));
        $firstBasketItem->setCategory1(ucfirst(explode('_',$payment_type)[0]));
        $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
        $firstBasketItem->setPrice(round($amount));
        $basketItems[0] = $firstBasketItem;
        $iyzicoRequest->setBasketItems($basketItems);

        
        # make request
        $payWithIyzicoInitialize = \Iyzipay\Model\PayWithIyzicoInitialize::create($iyzicoRequest, $options);

        # print result
        return Redirect::to($payWithIyzicoInitialize->getPayWithIyzicoPageUrl());
    }

    public function callback(Request $request, $payment_type, $amount = null, $payment_method = null, $package_id = null, $milestone_request_id = null, $service_package_id = null, $user_id = null){
        $options = new \Iyzipay\Options();
        $options->setApiKey(env('IYZICO_API_KEY'));
        $options->setSecretKey(env('IYZICO_SECRET_KEY'));

        if(SettingsUtility::get_settings_value('iyzico_sandbox_checkbox') == 1) {
            $options->setBaseUrl("https://sandbox-api.iyzipay.com");
        } else {
            $options->setBaseUrl("https://api.iyzipay.com");
        }

        $iyzicoRequest = new \Iyzipay\Request\RetrievePayWithIyzicoRequest();
        $iyzicoRequest->setLocale(\Iyzipay\Model\Locale::TR);
        $iyzicoRequest->setConversationId('123456789');
        $iyzicoRequest->setToken($request->token);
        # make request
        $payWithIyzico = \Iyzipay\Model\PayWithIyzico::retrieve($iyzicoRequest, $options);

        if ($payWithIyzico->getStatus() == 'success') {
            $payment = $payWithIyzico->getRawResult();
            Auth::login(User::find($user_id));

            if($payment_type == 'package_payment'){
                $data['amount'] = $amount;
                $data['payment_method'] = $payment_method;
                $data['package_id'] = $package_id;
                $data['payment_type'] = $payment_type;
                
                return (new PackagePaymentController)->package_payment_done($data, json_encode($payment));
            } elseif ($payment_type == 'milestone_payment') {
                $data['amount'] = $amount;
                $data['payment_method'] = $payment_method;
                $data['milestone_request_id'] = $milestone_request_id;
                
                return (new MilestonePaymentController)->milestone_payment_done($data, json_encode($payment));
            } elseif ($payment_type == 'service_payment') {
                $data['amount'] = $amount;
                $data['payment_method'] = $payment_method;
                $data['service_package_id'] = $service_package_id;
                
                return (new ServicePaymentController)->service_package_payment_done($data, json_encode($payment));
            } elseif ($payment_type == 'wallet_payment') {
                $data['amount'] = $amount;
                $data['payment_method'] = $payment_method;
                
                return (new WalletController)->wallet_payment_done($data, json_encode($payment));
            } else {
                dd($payment_type);
            }
        }
    }
}
