<?php


class QP_PluginStatus {



	private function getMerchantDetail()
	{
		$_this     = new QPGateways();
		$configNew = new QPBaseConfiguration( $_this );

		if ($configNew->isStage()){
			$boUrl= "https://stage.backoffice.qisstpay.com/merchants/";
		}else{
			$boUrl= "https://backoffice.qisstpay.com/merchants/";
		}

		$data['status'] = false;
		$data['msg'] = "data not found";

		try {

			$curl = curl_init();

			curl_setopt_array( $curl, array(
				CURLOPT_URL            => $configNew->getQPBaseUrl().'ms-external-service/merchant_info',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_HTTPHEADER     => array(
					'identity-token: '.$_this->get_option('merchant_token')
				),
			) );

			$response = curl_exec( $curl );

			curl_close( $curl );
			$response = json_decode($response);


			if ($response->success==1){
				$data['status'] = true;
				$data['business_name']= $response->data->business_name;
				$data['user_id']= $response->data->user_id;
				$data['msg'] = "data found";
				$data['bourl'] = $boUrl.$response->data->user_id;
			}


		} catch ( \Exception $e ) {

		}

		return $data;
	}



	public function on_deactivation() {

		$result = $this->getMerchantDetail();
		if ($result['status'] ) {
			$this->slackNotification($result);
		}

	}



	function slackNotification($data,$pluginStatus="deactivated") {
		try {

			$_this     = new QPGateways();
			$configNew = new QPBaseConfiguration( $_this );

			$desc = $data['business_name'].' has '.$pluginStatus.' plugin. ';
			$desc.="please click on ";
			$desc.=$data['bourl'];
			$desc.=" to see the detail of the merchant.";

			$json['message']['text'] = $desc;
			$jsonEnc = json_encode($json);


			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $configNew->getQPBaseUrl().'ms-external-service/slack_notification',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS =>$jsonEnc,
				CURLOPT_HTTPHEADER => array(
					'identity-token: '.$_this->get_option('merchant_token'),
					'Content-Type: application/json'
				),
			));

			$response = curl_exec($curl);

			curl_close($curl);


			curl_close( $curl );
		}catch (\Exception $exception) {

		}

	}


}
