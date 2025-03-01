<?php

namespace App\Http\Controllers\Payment\Checkout;

use App\{
    Models\Cart,
    Models\Order,
    Models\PaymentGateway,
    Classes\GeniusMailer
};
use App\Helpers\PriceHelper;
use App\Models\Country;
use App\Models\Reward;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Session;
use OrderHelper;
use Illuminate\Support\Str;

class AuthorizeController extends CheckoutBaseControlller
{
    public function store(Request $request)
    {
        $input = $request->all();

        $data = PaymentGateway::whereKeyword('authorize.net')->first();

        $total = $request->total;

        if ($request->pass_check) {
            $auth = OrderHelper::auth_check($input); // For Authentication Checking
            if (!$auth['auth_success']) {
                return redirect()->back()->with('unsuccess', $auth['error_message']);
            }
        }

        if (!Session::has('cart')) {
            return redirect()->route('front.cart')->with('success', __("You don't have any product to checkout."));
        }

        $item_name = $this->gs->title . " Order";
        $item_number = Str::random(4) . time();
        $item_amount = $total;
        $success_url = route('front.payment.return');

        // Validate Card Data

        $validator = \Validator::make($request->all(), [
            'cardNumber' => 'required',
            'cardCode' => 'required',
            'month' => 'required',
            'year' => 'required',
        ]);

        if ($validator->passes()) {
            $paydata = $data->convertAutoData();

            $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
            $merchantAuthentication->setName($paydata['login_id']);
            $merchantAuthentication->setTransactionKey($paydata['txn_key']);

            // Set the transaction's refId
            $refId = 'ref' . time();

            // Create the payment data for a credit card
            $creditCard = new AnetAPI\CreditCardType();
            $creditCard->setCardNumber(str_replace(' ', '', $request->cardNumber));
            $year = $request->year;
            $month = $request->month;
            $creditCard->setExpirationDate($year . '-' . $month);
            $creditCard->setCardCode($request->cardCode);

            // Add the payment data to a paymentType object
            $paymentOne = new AnetAPI\PaymentType();
            $paymentOne->setCreditCard($creditCard);

            // Create order information
            $orderr = new AnetAPI\OrderType();
            $orderr->setInvoiceNumber($item_number);
            $orderr->setDescription($item_name);

            // Create a TransactionRequestType object and add the previous objects to it
            $transactionRequestType = new AnetAPI\TransactionRequestType();
            $transactionRequestType->setTransactionType("authCaptureTransaction");
            $transactionRequestType->setAmount($item_amount);
            $transactionRequestType->setOrder($orderr);
            $transactionRequestType->setPayment($paymentOne);
            // Assemble the complete transaction request
            $requestt = new AnetAPI\CreateTransactionRequest();
            $requestt->setMerchantAuthentication($merchantAuthentication);
            $requestt->setRefId($refId);
            $requestt->setTransactionRequest($transactionRequestType);

            // Create the controller and get the response
            $controller = new AnetController\CreateTransactionController($requestt);
            if ($paydata['sandbox_check'] == 1) {
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
            } else {
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
            }

            if ($response != null) {

                // Check to see if the API request was successfully received and acted upon
                if ($response->getMessages()->getResultCode() == "Ok") {
                    // Since the API request was successful, look for a transaction response
                    // and parse it to display the results of authorizing the card
                    $tresponse = $response->getTransactionResponse();

                    if ($tresponse != null && $tresponse->getMessages() != null) {

                        $oldCart = Session::get('cart');
                        $cart = new Cart($oldCart);
                        OrderHelper::license_check($cart); // For License Checking
                        $t_oldCart = Session::get('cart');
                        $t_cart = new Cart($t_oldCart);
                        $new_cart = [];
                        $new_cart['totalQty'] = $t_cart->totalQty;
                        $new_cart['totalPrice'] = $t_cart->totalPrice;
                        $new_cart['items'] = $t_cart->items;
                        $new_cart = json_encode($new_cart);
                        $temp_affilate_users = OrderHelper::product_affilate_check($cart); // For Product Based Affilate Checking
                        $affilate_users = $temp_affilate_users == null ? null : json_encode($temp_affilate_users);

                        $orderCalculate = PriceHelper::getOrderTotal($input, $cart);
                        if (isset($orderCalculate['success']) && $orderCalculate['success'] == false) {
                            return redirect()->back()->with('unsuccess', $orderCalculate['message']);
                        }
                        if ($this->gs->multiple_shipping == 0) {
                            $orderTotal = $orderCalculate['total_amount'];
                            $shipping = $orderCalculate['shipping'];
                            $packeing = $orderCalculate['packeing'];
                            $is_shipping = $orderCalculate['is_shipping'];
                            $vendor_shipping_ids = $orderCalculate['vendor_shipping_ids'];
                            $vendor_packing_ids = $orderCalculate['vendor_packing_ids'];
                            $vendor_ids = $orderCalculate['vendor_ids'];

                            $input['shipping_title'] = $shipping->title;
                            $input['vendor_shipping_id'] = $shipping->id;
                            $input['packing_title'] = $packeing->title;
                            $input['vendor_packing_id'] = $packeing->id;
                            $input['shipping_cost'] = $packeing->price;
                            $input['packing_cost'] = $packeing->price;
                            $input['is_shipping'] = $is_shipping;
                            $input['vendor_shipping_ids'] = $vendor_shipping_ids;
                            $input['vendor_packing_ids'] = $vendor_packing_ids;
                            $input['vendor_ids'] = $vendor_ids;
                        } else {


                            // multi shipping

                            $orderTotal = $orderCalculate['total_amount'];
                            $shipping = $orderCalculate['shipping'];
                            $packeing = $orderCalculate['packeing'];
                            $is_shipping = $orderCalculate['is_shipping'];
                            $vendor_shipping_ids = $orderCalculate['vendor_shipping_ids'];
                            $vendor_packing_ids = $orderCalculate['vendor_packing_ids'];
                            $vendor_ids = $orderCalculate['vendor_ids'];
                            $shipping_cost = $orderCalculate['shipping_cost'];
                            $packing_cost = $orderCalculate['packing_cost'];

                            $input['shipping_title'] = $vendor_shipping_ids;
                            $input['vendor_shipping_id'] = $vendor_shipping_ids;
                            $input['packing_title'] = $vendor_packing_ids;
                            $input['vendor_packing_id'] = $vendor_packing_ids;
                            $input['shipping_cost'] = $shipping_cost;
                            $input['packing_cost'] = $packing_cost;
                            $input['is_shipping'] = $is_shipping;
                            $input['vendor_shipping_ids'] = $vendor_shipping_ids;
                            $input['vendor_packing_ids'] = $vendor_packing_ids;
                            $input['vendor_ids'] = $vendor_ids;
                            unset($input['shipping']);
                            unset($input['packeging']);
                        }

                        $order = new Order;
                        $input['cart'] = $new_cart;
                        $input['user_id'] = Auth::check() ? Auth::user()->id : NULL;
                        $input['affilate_users'] = $affilate_users;
                        $input['pay_amount'] = $orderTotal;
                        $input['order_number'] = $item_number;
                        $input['wallet_price'] = $request->wallet_price / $this->curr->value;
                        $input['payment_status'] = "Completed";
                        if ($input['tax_type'] == 'state_tax') {
                            $input['tax_location'] = State::findOrFail($input['tax'])->state;
                        } else {
                            $input['tax_location'] = Country::findOrFail($input['tax'])->country_name;
                        }
                        $input['tax'] = Session::get('current_tax');

                        $input['txnid'] = $tresponse->getTransId();
                        if ($input['dp'] == 1) {
                            $input['status'] = 'completed';
                        }
                        if (Session::has('affilate')) {
                            $val = $request->total / $this->curr->value;
                            $val = $val / 100;
                            $sub = $val * $this->gs->affilate_charge;
                            if ($temp_affilate_users != null) {
                                $t_sub = 0;
                                foreach ($temp_affilate_users as $t_cost) {
                                    $t_sub += $t_cost['charge'];
                                }
                                $sub = $sub - $t_sub;
                            }
                            if ($sub > 0) {
                                $user = OrderHelper::affilate_check(Session::get('affilate'), $sub, $input['dp']); // For Affiliate Checking
                                $input['affilate_user'] = Session::get('affilate');
                                $input['affilate_charge'] = $sub;
                            }
                        }

                        $order->fill($input)->save();
                        $order->tracks()->create(['title' => 'Pending', 'text' => 'You have successfully placed your order.']);
                        $order->notifications()->create();

                        if ($input['coupon_id'] != "") {
                            OrderHelper::coupon_check($input['coupon_id']); // For Coupon Checking
                        }

                        if (Auth::check()) {
                            if ($this->gs->is_reward == 1) {
                                $num = $order->pay_amount;
                                $rewards = Reward::get();
                                foreach ($rewards as $i) {
                                    $smallest[$i->order_amount] = abs($i->order_amount - $num);
                                }

                                asort($smallest);
                                $final_reword = Reward::where('order_amount', key($smallest))->first();
                                Auth::user()->update(['reward' => (Auth::user()->reward + $final_reword->reward)]);
                            }
                        }

                        OrderHelper::size_qty_check($cart); // For Size Quantiy Checking
                        OrderHelper::stock_check($cart); // For Stock Checking
                        OrderHelper::vendor_order_check($cart, $order); // For Vendor Order Checking

                        Session::put('temporder', $order);
                        Session::put('tempcart', $cart);
                        Session::forget('cart');
                        Session::forget('already');
                        Session::forget('coupon');
                        Session::forget('coupon_total');
                        Session::forget('coupon_total1');
                        Session::forget('coupon_percentage');

                        if ($order->user_id != 0 && $order->wallet_price != 0) {
                            OrderHelper::add_to_transaction($order, $order->wallet_price); // Store To Transactions
                        }

                        //Sending Email To Buyer
                        $data = [
                            'to' => $order->customer_email,
                            'type' => "new_order",
                            'cname' => $order->customer_name,
                            'oamount' => "",
                            'aname' => "",
                            'aemail' => "",
                            'wtitle' => "",
                            'onumber' => $order->order_number,
                        ];

                        $mailer = new GeniusMailer();
                        $mailer->sendAutoOrderMail($data, $order->id);

                        //Sending Email To Admin
                        $data = [
                            'to' => $this->ps->contact_email,
                            'subject' => "New Order Recieved!!",
                            'body' => "Hello Admin!<br>Your store has received a new order.<br>Order Number is " . $order->order_number . ".Please login to your panel to check. <br>Thank you.",
                        ];
                        $mailer = new GeniusMailer();
                        $mailer->sendCustomMail($data);

                        return redirect($success_url);
                    } else {
                        return back()->with('unsuccess', __('Payment Failed.'));
                    }
                    // Or, print errors if the API request wasn't successful
                } else {
                    return back()->with('unsuccess', __('Payment Failed.'));
                }
            } else {
                return back()->with('unsuccess', __('Payment Failed.'));
            }
        }
        return back()->with('unsuccess', __('Invalid Payment Details.'));
    }
}
