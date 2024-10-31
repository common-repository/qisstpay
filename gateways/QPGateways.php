<?php


class QPGateways extends WC_Payment_Gateway {

	/**
	 * Class constructor, more about it in Step 3
	 */

	public function __construct() {

		$this->id                 = 'qp'; // payment gateway plugin ID
		$this->icon               = ''; // URL of the icon that will be displayed on checkout page near your gateway name
		$this->has_fields         = true; // in case you need a custom credit card form
		$this->method_title       = 'QisstPay BNPL';
		$this->method_description = $this->getDesc(); //'Get payment through QP payment gateway'; // will be displayed on the options page

		// gateways can support subscriptions, refunds, saved payment methods,
		// but in this tutorial we begin with simple payments
		$this->supports = array(
			'products'
		);

		// Method with all the options fields
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();
		$this->title       = "QisstPay BNPL";
		$this->description = "Enable BNPL on your store.";
//			$this->enabled = $this->get_option( 'enabled' );
		$this->merchant_token_is_sandbox = $this->get_option( 'merchant_token_is_sandbox' );
		$this->merchant_token            = $this->get_option( 'merchant_token' );

		// This action hook saves the settings
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options'
		) );

		// We need custom JavaScript to obtain a token
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

		// You can also register a webhook here
		add_action( 'woocommerce_api_qp_payment_callback', array( $this, 'webhook' ) );


		//add_action( 'woocommerce_api_qp_code_verification', array( $this, 'niftCodeVerification' ) );
	}

	function getDesc() {
		$qpConf = new QPBaseConfiguration( $this );
		$token  = $qpConf->athenticatedToken();


		$p = "<p>Get payment through QP BNPL.</p>";

		if ( isset( $token['status'] ) && $token['status'] == 1 ) {
			$p .= "<p style='color: green;'>Merchant is authorized with Qisstpay</p>";
		} else {
			$p .= "<p style='color: red;'>Merchant is not authorized with Qisstpay</p>";
		}

		return $p;
	}

	/**
	 * Plugin options, we deal with it in Step 3 too
	 */
	public function init_form_fields() {

		$this->form_fields = array(

			'merchant_token'            => array(
				'title' => 'Merchant Token',
				'type'  => 'text'
			),
		/*	'merchant_token_is_sandbox' => array(
				'title'       => 'Test mode',
				'label'       => 'Enable Test Mode',
				'type'        => 'checkbox',
				'description' => 'Place the payment gateway in test mode using test API keys.',
				'default'     => true,
				'desc_tip'    => true,
			)*/

		);

	}

	/**
	 * You will need it if you want your custom credit card form, Step 4 is about it
	 */
	public function payment_fields() {
		$qpConf = new QPBaseConfiguration( $this );

		$existingPaymentMethod = $qpConf::QP_PAYMENTS_GATEWAYS;


//		if (!isset($_GET['wc-ajax'])) {
		//$paymentMethods = $qpConf->merchantAllPaymentMethods();
    //var_dump($paymentMethods);


//		}


		//if ( isset( $paymentMethods['status'] ) && $paymentMethods['status'] == 1 ) {

			// ok, let's display some description before the payment form
			if ( $this->description ) {
				$this->description = '<p style="font-size: smaller">Note: Unlock financial freedom with Qisstpay!</p>';

				// you can instructions for test mode, I mean test card numbers etc.
				if ( $this->merchant_token_is_sandbox && $this->merchant_token_is_sandbox=="yes") {
					$this->description .= '<p style="color: red">TEST MODE ENABLED</p>';
					$this->description = trim( $this->description );
				}
				// display the description with <p> tags etc.
				echo wpautop( wp_kses_post( $this->description ) );
			}

			// I will echo() the form, but you can close PHP tags and print it directly in HTML


		//}

	}

	public function _payment_fields() {
		$qpConf = new QPBaseConfiguration( $this );

		$existingPaymentMethod = $qpConf::QP_PAYMENTS_GATEWAYS;


//		if (!isset($_GET['wc-ajax'])) {
		$paymentMethods = $qpConf->getMerchantGatewaysView();
//		}


		if ( isset( $paymentMethods ) ) {

			// ok, let's display some description before the payment form
			if ( $this->description ) {
				// you can instructions for test mode, I mean test card numbers etc.
				if ( $this->merchant_token_is_sandbox && $this->merchant_token_is_sandbox=="yes") {
					$this->description .= '<p style="color: red">TEST MODE ENABLED</p>';
					$this->description = trim( $this->description );
				}
				// display the description with <p> tags etc.
				echo wpautop( wp_kses_post( $this->description ) );
			}

			// I will echo() the form, but you can close PHP tags and print it directly in HTML


		}

	}

	/*
	 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
	 */
	public function payment_scripts() {


	}

	/*
	  * Fields validation, more in Step 5
	 */
	public function validate_fields() {


		if ( $_POST['payment_method'] == "qp" ) {


		}
	}

	/*
	 * We're processing the payments here, everything about it is in Step 5
	 */
	public function process_payment( $order_id ) {

		try {
			$qpConf        = new QPBaseConfiguration( $this );
			$merchantToken = $this->get_option( 'merchant_token' );

			$resultToken = $qpConf->athenticatedToken( $merchantToken );


			if ( isset( $resultToken['status'] ) && $resultToken['status'] == 1 ) {


				$siteUrl = get_site_url();


				$transaction_number = time().$order_id;
				$callbackUrl = $siteUrl . "/wc-api/qp_payment_callback?order_id=" . $order_id . '&pm=' . strtolower( $_POST['payment_sub_method'] ).'&transaction_id='.$transaction_number;
        
				$payload     = json_decode($qpConf->createPayload( $order_id,$transaction_number,$callbackUrl));
		$order = wc_get_order( $order_id );
		
		$payment_method_fee_amount=0;
		$payment_method_fees = $order->get_fees();
		foreach ($payment_method_fees as $fee) {
			$payment_method_fee_amount = $fee->get_total();
			//var_dump($payment_method_fee_amount);
		}
		
        //var_dump($payment_method_fees->data);
        //return;
        
          //wc_delete_order($order_id,$force=true);
          //$delete_order = 'orders/'.$order_id;
          //$wc->delete($delete_order, ['force' => true]);
          
          $products = base64_encode(json_encode($payload->line_items, JSON_UNESCAPED_UNICODE));
          
          $price = $payload->amount;
          
          $currency = $payload->currency;
          
          $url = 'https://prod.wordpress.qisstpay.com/wp-json/qisstpay/teez/';
          
          $shipping_total = $payload->shipping_amount;
          //var_dump($shipping_total);
          //return;
          $rounded_payment_method_fee_amount = round($payment_method_fee_amount, 2);
		  if (!empty($rounded_payment_method_fee_amount)) {
    		$tax = $payload->tax_amount + $rounded_payment_method_fee_amount;
			} 
		 else {
    	// Handle the case when $rounded_payment_method_fee_amount is empty
    		$tax = $payload->tax_amount;
			}
          //$tax = $payload->tax_amount+$rounded_payment_method_fee_amount;
          //var_dump($tax);
		 //return;
          //$products_encoded= true;
          
          
        $checkout_data = array(
                              'customer_name'    => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                              'customer_email'   => $order->get_billing_email(),
                              'customer_phone'   => $order->get_billing_phone(),
                              'billing_address'  => array(
                                  'first_name' => $order->get_billing_first_name(),
                                  'last_name'  => $order->get_billing_last_name(),
                                  'address_1'  => $order->get_billing_address_1(),
                                  'address_2'  => $order->get_billing_address_2(),
                                  'city'       => $order->get_billing_city(),
                                  'state'      => $order->get_billing_state(),
                                  'postcode'   => $order->get_billing_postcode(),
                                  'country'    => $order->get_billing_country(),
                              ),
                              'shipping_address' => array(
                                  'first_name' => $order->get_shipping_first_name(),
                                  'last_name'  => $order->get_shipping_last_name(),
                                  'address_1'  => $order->get_shipping_address_1(),
                                  'address_2'  => $order->get_shipping_address_2(),
                                  'city'       => $order->get_shipping_city(),
                                  'state'      => $order->get_shipping_state(),
                                  'postcode'   => $order->get_shipping_postcode(),
                                  'country'    => $order->get_shipping_country(),
                             		 ),
                              );

       $checkout_data_json = json_encode($checkout_data);

        // Include the raw JSON string in the URL
        $user_data = 'user_data=' . $checkout_data_json;
              
        
          $query_url =base64_encode( 'products='.$products.'&price='.$price.'&currency='.$currency.'&products_encoded=1&url='.$url.'&shipping_total='.$shipping_total.'&tax='.$tax.'&user_data='.$user_data);
          
          $tez_link = 'https://ms.tezcheckout.qisstpay.com/?identity-token='.$merchantToken.'&queryUrl='.$query_url;
          wp_delete_post($order_id,true);
          return [
	                                    'result'   => 'success', // return success status
	                                    'redirect' => $tez_link
                                    ];
        






				// $response->html_snippet;


			} else {
				return false;
			}
		} catch ( \Exception $exception ) {
			wc_add_notice( $exception->getMessage(), 'error' );
			return false;
		}

	}


	private function removeHtmlIfram($html)
    {
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$iframes = $doc->getElementsByTagName('iframe');
		foreach ($iframes as $iframe) {
			$iframe->parentNode->removeChild($iframe);
		}


	    $form = $doc->getElementsByTagName('form');

	    foreach ($form as $link) {
		    $link->setAttribute('target', '_self');
	    }

	    $newHtml = $doc->saveHTML();
		return $newHtml;
    }



	/*
	 * In case you need a webhook, like PayPal IPN etc
	 */
	public function webhook() {



		$paymentGtway = "ubl";

		if (trim($_GET['pm']=="Alfa")){
			$paymentGtway = "alfa";
		}
		if (trim($_GET['pm']=="easypaisa")){
			$paymentGtway = "easypaisa";
		}
		if (trim($_GET['pm']=="card")){
			$paymentGtway = "stripe";
		}
		if (trim($_GET['pm']=="card")){
			$paymentGtway = "stripe";
		}
		if (trim($_GET['pm'])=="Alfalah" || trim($_GET['pm'])=="alfalah" ){
			$paymentGtway = "alfalah";
		}


		$qpConf        = new QPBaseConfiguration( $this );

		try {

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $qpConf->getQPBaseUrl().'ms-external-service/inquire_merchant_payment',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>'{
                    "gateway":"'.$paymentGtway.'",
                    "gateway_credentials":{},
                    "order_id":"'.$_GET['transaction_id'].'"
                }',
				CURLOPT_HTTPHEADER => array(
					'identity-token: '.$this->merchant_token,
					'Content-Type: application/json'
				),
			));

			$response = curl_exec($curl);


			curl_close($curl);
			$response = json_decode($response);



			global $woocommerce;

			$status_array = array('Complete','succeeded','Completed');

			$order = wc_get_order( $_GET['order_id'] );


			if (isset( $response->payment_status) && in_array($response->payment_status,$status_array) ) {

				$trans_id = "QP-" . strtoupper( $_GET['pm'] ) . '-' . $_GET['transaction_id'];

				$order->payment_complete( $trans_id );
				//$order->reduce_order_stock();
				$order->update_status( 'completed' );
				$order->add_order_note( 'Your order transaction number is '.$trans_id , true );
				$woocommerce->cart->empty_cart();
				$url = $this->get_return_url( $order );
				wp_redirect( $url );
				exit;

			} else {
				$order->needs_payment();
				$order->add_order_note( 'Tried but payment failed ', true );
				wc_add_notice( "Payment failed", 'error' );
				wp_redirect( wc_get_checkout_url() );
				exit;


			}
		} catch ( \Exception $exception ) {
			$order->add_order_note( 'Tried with QP-' . strtoupper( $_GET['pm'] ) . ' but payment failed ', true );

			wc_add_notice( "There is some issue.please try again later", 'error' );
			//WC()->cart->empty_cart();
			wp_redirect( wc_get_checkout_url() );
			exit;
		}


	}



	function qp_encryptString($plaintext, $password="QpPaymentStack", $encoding = null) {
		$iv = openssl_random_pseudo_bytes(16);
		$ciphertext = openssl_encrypt($plaintext, "AES-256-CBC", hash('sha256', $password, true), OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $ciphertext.$iv, hash('sha256', $password, true), true);
		return $encoding == "hex" ? bin2hex($iv.$hmac.$ciphertext) : ($encoding == "base64" ? base64_encode($iv.$hmac.$ciphertext) : $iv.$hmac.$ciphertext);
	}

	function qp_decryptString($ciphertext, $password="QpPaymentStack", $encoding = null) {
		$ciphertext = $encoding == "hex" ? hex2bin($ciphertext) : ($encoding == "base64" ? base64_decode($ciphertext) : $ciphertext);
		if (!hash_equals(hash_hmac('sha256', substr($ciphertext, 48).substr($ciphertext, 0, 16), hash('sha256', $password, true), true), substr($ciphertext, 16, 32))) return null;
		return openssl_decrypt(substr($ciphertext, 48), "AES-256-CBC", hash('sha256', $password, true), OPENSSL_RAW_DATA, substr($ciphertext, 0, 16));
	}
}
