<?php
require_once('vendor/autoload.php');

class StripePay {

    public $secret_key = '';
    public $publishable_key = '';
    public $client_id = '';
    public $currency = '';
    public $fields = array('stripe_id' => 'id', 'status' => 'status', 'txn_id' => 'balance_transaction', 'amount' => 'amount', 'amount_refunded' => 'amount_refunded', 'application_fee' => 'application_fee', 'created' => 'created', 'currency' => 'currency', 'usd' => 'usd', 'customer' => 'customer', 'card_id' => 'default_source', 'paid' => 'paid', 'source' => 'source');

    public function __construct() {
        $this->secret_key = "STRIPE_SECRET_KEY";
        $this->publishable_key = "STRIPE_PUBLISHER_KEY";
        $this->client_id = "STRIPE_CLIENT_ID";
        $this->currency = "usd";
        \Stripe\Stripe::setApiKey($this->secret_key);
    }

    public function _formatResult($response) {
        $result = array();
        foreach ($this->fields as $local => $stripe) {
            if (is_array($stripe)) {
                foreach ($stripe as $obj => $field) {
                    if (isset($response->$obj->$field)) {

                        $result[$local] = $response->$obj->$field;
                    }
                }
            } else {
                if (isset($response->$stripe)) {
                    $result[$local] = $response->$stripe;
                }
            }
        }
        // if no local fields match, return the default stripe_id
        if (empty($result)) {
            $result['stripe_id'] = $response->id;
        }
        return $result;
    }

    public function customerCreate($data) {

        // for API compatibility with version 1.x of this component
        if (isset($data['stripeToken'])) {
            $data['source'] = $data['stripeToken'];
            unset($data['stripeToken']);
        }
        $error = null;

        try {
            $customer = \Stripe\Customer::create($data);
        } catch (\Stripe\Error\Card $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];
            CakeLog::error(
                    'Customer::Stripe_CardError: ' . $err['type'] . ': ' . $err['code'] . ': ' . $err['message'], 'stripe'
            );
            $error = $err['message'];
        } catch (\Stripe\Error\InvalidRequest $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];
            CakeLog::error(
                    'Customer::Stripe_InvalidRequestError: ' . $err['type'] . ': ' . $err['message'], 'stripe'
            );
            $error = $err['message'];
        } catch (\Stripe\Error\Authentication $e) {
            CakeLog::error('Customer::Stripe_AuthenticationError: API key rejected!', 'stripe');
            $error = 'Payment processor API key error.';
        } catch (\Stripe\Error\ApiConnection $e) {
            CakeLog::error('Customer::Stripe_ApiConnectionError: Stripe could not be reached.', 'stripe');
            $error = 'Network communication with payment processor failed, try again later';
        } catch (\Stripe\Error\Base $e) {
            CakeLog::error('Customer::Stripe_Error: Stripe could be down.', 'stripe');
            $error = 'Payment processor error, try again later.';
        } catch (Exception $e) {
            CakeLog::error('Customer::Exception: Unknown error.', 'stripe');
            $error = 'There was an error, try again later.';
        }

        if ($error !== null) {
            // an error is always a string
            return (string) $error;
        }
        CakeLog::info('Customer: customer id ' . $customer->id, 'stripe');
        return $this->_formatResult($customer);
    }

    function updateCard($id, $token) {
        $customer = false;
        try {
            $customer = \Stripe\Customer::retrieve($id);
            $customer->source = $token;
            $customer->save();
        } catch (Exception $e) {
            return false;
        }
        return $customer;
    }

    public function customerRetrieve($id) {
        $customer = false;
        try {
            $customer = \Stripe\Customer::retrieve($id);
        } catch (Exception $e) {
            return false;
        }

        return $customer;
    }

    public function TxnRetrieve($id) {
        $txn = false;

        try {

            $txn = \Stripe\BalanceTransaction::retrieve($id);
        } catch (Exception $e) {
            return false;
        }

        return $txn;
    }

    public function createOrder($chargeData) {
        $error = null;
        try {
            $charge = \Stripe\Charge::create($chargeData);
        } catch (\Stripe\Error\Card $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];
            CakeLog::error(
                    'Charge::Stripe_CardError: ' . $err['type'] . ': ' . $err['code'] . ': ' . $err['message'], 'stripe'
            );
            $error = $err['message'];
        } catch (\Stripe\Error\InvalidRequest $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];
            CakeLog::error(
                    'Charge::Stripe_InvalidRequestError: ' . $err['type'] . ': ' . $err['message'], 'stripe'
            );
            $error = $err['message'];
        } catch (\Stripe\Error\Authentication $e) {
            CakeLog::error('Charge::Stripe_AuthenticationError: API key rejected!', 'stripe');
            $error = 'Payment processor API key error.';
        } catch (\Stripe\Error\ApiConnection $e) {
            CakeLog::error('Charge::Stripe_ApiConnectionError: Stripe could not be reached.', 'stripe');
            $error = 'Network communication with payment processor failed, try again later';
        } catch (\Stripe\Error\Base $e) {
            CakeLog::error('Charge::Stripe_Error: Stripe could be down.', 'stripe');
            $error = 'Payment processor error, try again later.';
        } catch (Exception $e) {
            CakeLog::error('Charge::Exception: Unknown error.', 'stripe');
            $error = 'There was an error, try again later.';
        }

        if ($error !== null) {
            echo json_encode(array('status' => 'fail', 'error' => $error));
            exit;
        }

        return $this->_formatResult($charge);
    }

    public function getOrder($chargeData) {
        $error = null;
        try {
            $charge = \Stripe\Charge::retrieve($chargeData);
        } catch (\Stripe\Error\Card $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];
            CakeLog::error(
                    'Charge::Stripe_CardError: ' . $err['type'] . ': ' . $err['code'] . ': ' . $err['message'], 'stripe'
            );
            $error = $err['message'];
        } catch (\Stripe\Error\InvalidRequest $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];
            CakeLog::error(
                    'Charge::Stripe_InvalidRequestError: ' . $err['type'] . ': ' . $err['message'], 'stripe'
            );
            $error = $err['message'];
        } catch (\Stripe\Error\Authentication $e) {
            CakeLog::error('Charge::Stripe_AuthenticationError: API key rejected!', 'stripe');
            $error = 'Payment processor API key error.';
        } catch (\Stripe\Error\ApiConnection $e) {
            CakeLog::error('Charge::Stripe_ApiConnectionError: Stripe could not be reached.', 'stripe');
            $error = 'Network communication with payment processor failed, try again later';
        } catch (\Stripe\Error\Base $e) {
            CakeLog::error('Charge::Stripe_Error: Stripe could be down.', 'stripe');
            $error = 'Payment processor error, try again later.';
        } catch (Exception $e) {
            CakeLog::error('Charge::Exception: Unknown error.', 'stripe');
            $error = 'There was an error, try again later.';
        }

        if ($error !== null) {
            echo json_encode(array('status' => 'fail', 'error' => $error));
            exit;
        }
        $this->fields = array('stripe_id' => 'id', 'status' => 'status', 'txn_id' => 'balance_transaction', 'amount' => 'amount', 'amount_refunded' => 'amount_refunded', 'application_fee' => 'application_fee'
            , 'created' => 'created', 'currency' => 'currency', 'usd' => 'usd', 'customer' => 'customer',
            'paid' => 'paid', 'source' => 'source');
        return $this->_formatResult($charge);
    }

    public function createToken($data) {

        if (isset($data['stripeToken'])) {
            $data['card'] = $data['stripeToken'];
            unset($data['stripeToken']);
        }

        $customer = null;
        try {
            $customer = \Stripe\Token::create(array(
                        "card" => array(
                            "number" => "4242424242424242",
                            "exp_month" => '12',
                            "exp_year" => 2019,
                            "cvc" => "123"
                        )
            ));
        } catch (\Stripe\Error\Card $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];

            CakeLog::error(
                    'Customer::Stripe_CardError: ' . $err['type'] . ': ' . $err['code'] . ': ' . $err['message'], 'stripe'
            );
            echo json_encode(array('status' => 'fail', 'error' => $err['message']));
            exit;
        }
        return $this->_formatResult($customer);
    }

    function createSplit($amount, $token, $destination, $stripe_account, $customer, $convenienceFee, $description = '') {
        $error = null;
        try {
            $charge = \Stripe\Charge::create(
                            array(
                                "amount" => $amount * 100, // amount in cents
                                "currency" => "usd",
                                "customer" => $customer,
                                "description" => $description,
                                "application_fee" => round(((($amount * 100) * (2.9)) / 100) + 30 + ($convenienceFee * 100)), // amount in cents
                                'destination' => trim($destination),
                            )
            );
        } catch (\Stripe\Error\Card $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];
            CakeLog::error(
                    'Charge::Stripe_CardError: ' . $err['type'] . ': ' . $err['code'] . ': ' . $err['message'], 'stripe'
            );
            $error = $err['message'];
        } catch (\Stripe\Error\InvalidRequest $e) {
            $body = $e->getJsonBody();
            $err = $body['error'];
            CakeLog::error(
                    'Charge::Stripe_InvalidRequestError: ' . $err['type'] . ': ' . $err['message'], 'stripe'
            );
            $error = $err['message'];
        } catch (\Stripe\Error\Authentication $e) {
            CakeLog::error('Charge::Stripe_AuthenticationError: API key rejected!', 'stripe');
            $error = 'Payment processor API key error.';
        } catch (\Stripe\Error\ApiConnection $e) {
            CakeLog::error('Charge::Stripe_ApiConnectionError: Stripe could not be reached.', 'stripe');
            $error = 'Network communication with payment processor failed, try again later';
        } catch (\Stripe\Error\Base $e) {
            CakeLog::error('Charge::Stripe_Error: Stripe could be down.', 'stripe');
            $error = 'Payment processor error, try again later.';
        } catch (Exception $e) {
            CakeLog::error('Charge::Exception: Unknown error.', 'stripe');
            $error = 'There was an error, try again later.';
        }

        if ($error !== null) {
            echo json_encode(array('status' => 'fail', 'error' => $error));
            exit;
        }

        return $this->_formatResult($charge);
    }

}
