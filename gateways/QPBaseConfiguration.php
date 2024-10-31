<?php

class QPBaseConfiguration extends WC_Payment_Gateway{

	/*
	 * Global variables and methods
	 * */

	private $_this;

	const QP_PAYMENTS_GATEWAYS = array(
	"Alfa",
	"EasyPaisa",
	"UBL",
	"Card",
	"Alfalah",
	"Nift",
  "PAY_IN_2"
	//     "Ubl"
	);

	function __construct($_this) {

		$this->_this = $_this;
	}


	function getQPBaseUrl()
	{
		$is_sandbox = $this->_this->get_option( 'merchant_token_is_sandbox' );
//		if ( isset( $is_sandbox ) && $is_sandbox == "yes" ) {

			//$paymentMethodBaseUrl = 'https://stage.apis.qisstpay.com/';
		//} else {
			$paymentMethodBaseUrl = 'https://apis.qisstpay.com/';
//		}
		return $paymentMethodBaseUrl;
	}

	function isStage()
	{
		$is_sandbox = $this->_this->get_option( 'merchant_token_is_sandbox' );
		if ( isset( $is_sandbox ) && $is_sandbox == "yes" ) {
			$result = true;
		} else {
			$result = false;
		}
		return $result;
	}

	function athenticatedToken()
	{
		$token = $this->_this->get_option( 'merchant_token' );
		$result = array(
			'status'=>false,
			'token'=>null,
			'msg'=>"Not a valid token"
		);
		try {

			$url = $this->getQPBaseUrl()."ms-external-service/merchant_authentication";

			$curl = curl_init();

			curl_setopt_array( $curl, array(
				CURLOPT_URL            => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_HTTPHEADER     => array(
					'identity-token:' . $token,
					'platform: wordpress',
					'cache-control: no-cache',
					'content-type: application/x-www-form-urlencoded',
					'accept: *',
					'accept-encoding: gzip, deflate',
					'Content-Length: 0'
				),
			) );

			$response = curl_exec( $curl );

			curl_close( $curl );

			$response = json_decode($response);
			if (isset($response->success) && $response->success==true){
				$result = array(
					'status'=>true,
					'token'=>$response->token,
					'msg'=>$response->message
				);
			}
		}catch (\Exception $exception){

		}

		return $result;
	}

	function merchantAllPaymentMethods() {
		$response_result = array(
			'status'          => false,
			'payment_methods' => array()
		);
		try {

			$resultToken = $this->athenticatedToken( );


			if ( $resultToken['status'] ) {

				$url = $this->getQPBaseUrl(). 'ms-external-service/stack_merchant_payment_methods';

				$curl = curl_init();

				curl_setopt_array( $curl, array(
					CURLOPT_URL            => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING       => '',
					CURLOPT_MAXREDIRS      => 10,
					CURLOPT_TIMEOUT        => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST  => 'POST',
					CURLOPT_HTTPHEADER     => array(
						'identity-token: ' . $this->_this->get_option( 'merchant_token' )
					),
				) );

				$response = curl_exec( $curl );

				curl_close( $curl );
				$response = json_decode( $response );


				if ( isset( $response->success ) && $response->success == true ) {


					return array(
						'status'          => true,
						'payment_methods' => $response->payment_methods
					);
				} else {
					return $response_result;
				}

			}
		} catch ( \Exception $exception ) {
			return $response_result;
		}
	}

	function getMerchantGatewaysView() {

		$result = [];
		try {

			$merchantToken = $this->_this->get_option( 'merchant_token' );


			$resultToken = $this->athenticatedToken();

			if ( $resultToken['status'] ) {

				$curl = curl_init();

				$url = $this->getQPBaseUrl() . "ms-web-external-apis/merchants/v1/payment/methods";

				curl_setopt_array( $curl, array(
					CURLOPT_URL            => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING       => '',
					CURLOPT_MAXREDIRS      => 10,
					CURLOPT_TIMEOUT        => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST  => 'GET',
					CURLOPT_HTTPHEADER     => array(
						'Accept: application/json, text/plain, */*',
						'Accept-Language: en-US,en;q=0.9',
						'Authorization:' . $resultToken['token'],
						'Connection: keep-alive',
//						'Origin: https://stage.tezcheckout.qisstpay.com',
//						'Referer: https://stage.tezcheckout.qisstpay.com/',
						'Sec-Fetch-Dest: empty',
						'Sec-Fetch-Mode: cors',
						'Sec-Fetch-Site: same-site',
//						'USER_ID: 4773591',
						'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
						'identity-token:' . $merchantToken,
						'sec-ch-ua: "Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',
						'sec-ch-ua-mobile: ?0',
						'sec-ch-ua-platform: "macOS"'
					),
				) );

				$response = curl_exec( $curl );
				curl_close( $curl );

				$response = json_decode( $response );

				if ( isset( $response->success ) && $response->success == 1 ) {
					if ( isset( $response->methods->Others ) ) {
						$result = $response->methods->Others;
					}
				}
			}
		} catch ( \Exception $exception ) {

		}

		return $result;
	}

	function createPayload( $order_id,$transaction_number,$callbackUrl=null) {
		$order = wc_get_order( $order_id );

		if ( $order ) {

			$wpLineItems = $order->get_items();

			$line_item= [];

			foreach ($wpLineItems as $row){
				$item_data = $row->get_data();
				$product        = $row->get_product();

				$productDetail = wc_get_product( $item_data['product_id'] );

//			$product_attr = get_post_meta( $item_data['product_id'], '_product_attributes' );

				$description = $productDetail->get_description();

				$terms = get_the_terms($item_data['product_id'], 'product_cat');

				//$product_cats_ids = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
				$categories = [];
				$sub_categories = [];

				foreach($terms as $term ) {
					if ($term->parent==0){
						$categories[] = $term;
					}else{
						$sub_categories[] = $term;
					}
				}

				if (is_array($categories) && is_array($categories) >0){

					$categories =  array_column($categories, 'name');
				}
				if (is_array($sub_categories) && is_array($sub_categories) >0){

					$sub_categories =  array_column($sub_categories, 'name');
				}

				$attributes = $this->getVariations($product);

				$imgUrl = null;
				$img = wp_get_attachment_image_src( get_post_thumbnail_id( $item_data['product_id'] ), 'single-post-thumbnail' );
				if (is_array($img) && count($img)>0)
				{
					$imgUrl = $img[0];
				}

				// Get SKU
				$sku = $productDetail->get_sku();

				$tags = [];

				$itemtags = $product->tag_ids;

				foreach($itemtags as $tag) {
					if (get_term($tag)) {
						$tags[] = get_term( $tag )->name;
					}
				}

				$weight_unit = get_option('woocommerce_weight_unit');

				$line_item[] = array(

					"id"=>$item_data['product_id'],
					"src"=>$imgUrl,
					"sku"=>$sku,
					"title"=> $item_data['name'],
					//"type"=> "NA",
					"quantity"=>$row->get_quantity(),
					//"category"=>$categories,
					//"subcategory"=>$sub_categories,
					//"description"=>$description,
//                    "color": "NA",
//                    "size": "NA",
//                    "brand": "NA",
					"price"=>$product->get_price(),
					//"amount"=>$row->get_total() ,
					"attributes"=>$attributes,
					//"tax_rate"=> 1,
					"variant_id"=>(string)$item_data['variation_id'],
					//"total_discount_amount"=> 0,
					//"total_tax_amount"=> (int)$row->get_total_tax(),
					//"shipping_attributes"=>array(
					"weight" => $product->get_weight(),
					"weight_unit" => $weight_unit,
						//"weight"=>"NA",
						//"dimensions"=>array(
							//"height"=> "NA",
							//"width"=>"NA",
							//"length"=>"NA"
						//)
					//),
					//"tags"=> $tags
				);
			}

			$line_item = json_encode($line_item);

			$paymentGtway = "ubl";

			$card_number='';
			$card_expiry = '';
			$card_cvv = '';

			$qp_nift_banks=null;
			$qp_account_number=null;
			$customer_cnic=null;

			$payload = '{
                    "merchant_platform_id": "'.$order_id.'", 
                    "gateway": "qisstpay_bnpl",
                    "gateway_credentials": {},
                    "source": "card",
                    "order_id": "' . $transaction_number . '",
                    "order_number": "' .$transaction_number.rand(1,10).'",
                    "tokenized_card": "false",
                    "card_number": "'.$card_number.'",
                    "expiry_month": "'.$month.'",
                    "expiry_year": "'.$year.'",
                    "cvv": "'.$card_cvv.'",
                    "account_number":"'.$_POST['qp_phone_number'].'",
                    "customer_phone":"'.$order->get_billing_phone().'",
                   "customer_name": "'.$order->get_billing_first_name().' '.$order->get_billing_last_name().'",
                    "customer_email": "'.$order->get_billing_email().'",
                    "customer_date_of_birth": "",
                    "amount": '.$order->get_total().',
                    "currency": "'.$order->get_currency().'",
                    "country": "'.$order->get_billing_country().'",
                    "address": {
                        "city": "'.$order->get_billing_city().'",
                        "country": "'.$order->get_billing_country().'",
                        "email": "'.$order->get_billing_email().'",
                        "phone": "'.$order->get_billing_phone().'",
                        "postal_code": "'.$order->get_billing_postcode().'",
                        "region": "'.$order->get_billing_state().'",
                        "street_address": "'.$order->get_formatted_billing_address().'",
                        "street_address2": ""
                    },
                     "line_items":'.$line_item.',
                    "redirect_url" :"https://webhook.site/2963f3b3-83c8-45ce-b43d-23b8d1ba3ff8",
                    "callback_url":"'.$callbackUrl.'",
                    "tax_amount": '.$order->get_total_tax().',
                    "shipping_amount":'.$order->get_shipping_total().',
                    "discount_amount":'.$order->get_discount_total().',
                    "ip_address": "'.$this->get_the_user_ip().'",
                    
                    "bank_id": "'.$qp_nift_banks.'",
				    "account_number": "'.$qp_account_number.'",
				    "customer_cnic": "'.$customer_cnic.'",
                    "platform":"wordpress"
                 }';

			//var_dump($order->get_total_tax());
			return $payload;
		} else {
			return false;
		}
	}

	function getVariations($product)
	{
		// Only for product variation
		$attr = [];
		if( $product->is_type('variation') ){
			// Get the variation attributes
			$variation_attributes = $product->get_variation_attributes();


			// Loop through each selected attributes
			foreach($variation_attributes as $attribute_taxonomy => $term_slug ){
				// Get product attribute name or taxonomy
				$taxonomy = str_replace('attribute_', '', $attribute_taxonomy );
				// The label name from the product attribute
				$attribute_name = wc_attribute_label( $taxonomy, $product );
				// The term name (or value) from this attribute
				if( taxonomy_exists($taxonomy) ) {
					$attribute_value = get_term_by( 'slug', $term_slug, $taxonomy )->name;
				} else {
					$attribute_value = $term_slug; // For custom product attributes
				}

				$attr[] = array($attribute_name=>$attribute_name);

			}
		}

		return $attr;
	}


	function get_the_user_ip() {

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {

			$ip = $_SERVER['HTTP_CLIENT_IP'];

		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {

			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

		} else {

			$ip = $_SERVER['REMOTE_ADDR'];

		}

		return apply_filters( 'wpb_get_ip', $ip );

	}

	function processQPOrderPayment($payload,$merchantToken,$resultToken)
	{
		$result = array(
			"success"=>false
		);
		$response = json_encode($result);
		try {
			$curl = curl_init();

			curl_setopt_array( $curl, array(
				CURLOPT_URL            => $this->getQPBaseUrl() . 'ms-external-service/process_merchant_payment',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_POSTFIELDS     => $payload,
				CURLOPT_HTTPHEADER     => array(
					'identity-token:' . $merchantToken,
					'Authorization:' . $resultToken['token'],
					'Content-Type: application/json'
				),
			) );

			$response = curl_exec( $curl );

			curl_close( $curl );
		}catch (\Exception $exception){
		}
		return json_decode( $response );
	}

}