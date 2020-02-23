<?php
namespace PhpPot\Service;

require_once 'Stripeclass/vendor/stripe/stripe-php-master/init.php';

use \Stripe\Stripe;
use \Stripe\Customer;
use \Stripe\ApiOperations\Create;
use \Stripe\Charge;
use \Stripe\Transfer;
use \Stripe\PaymentIntent;

class StripePayment
{

    private $apiKey;

    private $stripeService;

    public function __construct()
    {
        require_once "config.php";
        $this->apiKey = STRIPE_SECRET_KEY;
        $this->stripeService = new \Stripe\Stripe();
        $this->stripeService->setVerifySslCerts(false);
        $this->stripeService->setApiKey($this->apiKey);
    }

    public function addCustomer($customerDetailsAry)
    {
        
        $customer = new Customer();
        
        $customerDetails = $customer->create($customerDetailsAry);
        
        return $customerDetails;
    }

    public function chargeAmountFromCard($cardDetails)
    {
        $customerDetailsAry = array(
            'email' => $cardDetails['email'],
            'source' => $cardDetails['token']
        );
        $customerResult = $this->addCustomer($customerDetailsAry);
        $charge = new Charge();
		$transfer = new Transfer();
		$paymentIntent = new PaymentIntent();

		
		$cardDetailsAry = array(
		  'amount' => 10000,
		  'currency' => 'usd',
		  'payment_method_types' => ['card'],
		  'transfer_group' => 'ORDER10',
		);
       $result = $paymentIntent->create($cardDetailsAry);


        /*$cardDetailsAry = array(
            'customer' => $customerResult->id,
            'amount' => $cardDetails['amount']*100 ,
            'currency' => $cardDetails['currency_code'],
            'description' => $cardDetails['item_name'],
            'metadata' => array(
                'order_id' => $cardDetails['item_number']
            )
        );
        $result = $charge->create($cardDetailsAry);
		*/
		
		 $cardDetailsAry1 = array(
				  'amount' => 100,
				  'currency' => 'usd',
				  'destination' => 'acct_1AGS7sIkvZ2VeT0k',
				  'transfer_group' => 'ORDER10',
			 );
		
		//print_r($cardDetailsAry1);
		
		$result = $transfer->create($cardDetailsAry1);	 
			
        return $result->jsonSerialize();
    }
}
