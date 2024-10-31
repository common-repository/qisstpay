<?php
/*
 * @wordpress-plugin
 * Plugin Name: QisstPay
 * Plugin URI: https://qisstpay.com/
 * Description: QisstPay 1-Click Checkout
 * Version: 3.11
 * Author: QisstPay
 * Author URI: https://qisstpay.com/
 * Text Domain: QisstPay 1-Click Checkout
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */



/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */

add_filter( 'woocommerce_payment_gateways', function ( $gateways ) {


	$gateways[] = "QPGateways";

	return $gateways;

} );


//$curl = curl_init();
//
//curl_setopt_array($curl, array(
//	CURLOPT_URL => 'https://hooks.slack.com/services/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',
//	CURLOPT_RETURNTRANSFER => true,
//	CURLOPT_ENCODING => '',
//	CURLOPT_MAXREDIRS => 10,
//	CURLOPT_TIMEOUT => 0,
//	CURLOPT_FOLLOWLOCATION => true,
//	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//	CURLOPT_CUSTOMREQUEST => 'POST',
//	CURLOPT_POSTFIELDS =>'{"text":"Test!"}',
//	CURLOPT_HTTPHEADER => array(
//		'Content-type: application/json'
//	),
//));
//
//$response = curl_exec($curl);
//
//curl_close($curl);
//echo $response;

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', function () {



//	qp_is_this_plugin_active( 'QP Payment Stack', 'QP Configuration', 'qp-configuration/qp-configuration.php' );

	include_once( 'gateways/QPGateways.php' );

	include_once( 'gateways/QPBaseConfiguration.php' );
	include_once( 'gateways/QP_PluginStatus.php' );

} );


function qp_load_plugin_css() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'qp-style', $plugin_url . 'css/qp-style.css' );
}

add_action( 'wp_enqueue_scripts', 'qp_load_plugin_css' );

add_action('wp_enqueue_scripts', 'qpayment8911_my_load_scripts');
/**
 * Never worry about cache again!
 */
if(!function_exists('qpayment8911_my_load_scripts'))
{
    function qpayment8911_my_load_scripts($hook) {

        // create my own version codes
        $my_js_ver  = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'js/qisstpay_plugin_script.js' ));
        $my_css_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'css/qp-style.css' ));

        //
        wp_enqueue_script( 'custom_js', plugins_url( 'js/qisstpay_plugin_script.js', __FILE__ ), array(), $my_js_ver );
        wp_register_style( 'my_css',    plugins_url( 'css/qp-style.css',    __FILE__ ), false,   $my_css_ver );
        wp_enqueue_style ( 'my_css' );

    }
}

add_action('woocommerce_before_add_to_cart_button', 'qpayment8911_pgw_img_before_addtocart');


if (!function_exists('qpayment8911_pgw_img_before_addtocart')) {
	function qpayment8911_pgw_img_before_addtocart()
	{
	global $product;
	global $woocommerce;
	
	$product_id = $product->get_id();
	
	//$selected_variant_id = get_selected_variant_id();
	//echo 'Selected Variant ID: ' . $selected_variant_id;
	
	
	$image = plugin_dir_url(dirname(__FILE__)) . basename(dirname(__FILE__)) . '/images/QisstPay_logo_white_bg.png';
	$imageNew_qp = plugin_dir_url(dirname(__FILE__)) . basename(dirname(__FILE__)) . '/images/Qisstpay_DesktopTablet_wqp.jpg';
	$imgMobile_qp = plugin_dir_url(dirname(__FILE__)) . basename(dirname(__FILE__)) . '/images/qisstpay_mobileImg_wqp.jpg';
	$imgLogo_qp = plugin_dir_url(dirname(__FILE__)) . basename(dirname(__FILE__)) . '/images/qisstpayLogoHd.png';
	$imgLogo_qp_mob = plugin_dir_url(dirname(__FILE__)) . basename(dirname(__FILE__)) . '/images/qisstpay_mobileImg_wqp_header.png';
	
	echo '
	<div class="img-para">
	<div style="display:flex;align-items: center;gap:5px;width: -webkit-fill-available;">
	<p style="
	 margin: 0;
	 font-weight: 600;
	 font-size: 24px;
	 ">Or</p>
	</div>
	<p style="font-weight: 600;font-size: 24px;margin: 0;text-align: start;">Get in 2 Installments</p>
	<p style="font-weight: 600;font-size: 24px;margin: 0;">at RS <span style="font-weight: 900;font-size: 25px;">' . esc_html(ceil($product->get_price()) / 2) . '</span><span style="font-size:20px;"> per month.</span></p>
	<div style="
	 display: flex;
	 align-items: center;
	 gap: 10px;
	 margin-bottom: 20px;
	 "><p style="
	 font-weight: 900;
	 font-size: 25px;
	 margin: 0;
	 ">With</p>
	 <svg width="40" height="40" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
	<g clip-path="url(#clip0_581_1194)">
	<path d="M49.926 100C77.4994 100 99.8521 77.6142 99.8521 50C99.8521 22.3858 77.4994 0 49.926 0C22.3526 0 0 22.3858 0 50C0 77.6142 22.3526 100 49.926 100Z" fill="#111111"/>
	<path d="M39.3633 71.2734V71.395C39.3633 71.395 39.4241 71.395 39.4241 71.4557V71.3343C39.4241 71.2734 39.3633 71.2734 39.3633 71.2734Z" fill="#E72E80"/>
	<path d="M49.9834 32.7676C40.5077 32.7676 32.7822 40.5047 32.7822 49.9945C32.7822 50.3571 32.7822 51.1429 32.7822 51.1429V76.1067C32.8426 76.1671 32.9633 76.2276 33.0236 76.288C34.5325 77.2551 36.1621 78.1014 37.852 78.8267C38.3952 79.0685 38.9384 79.2498 39.4816 79.4311H39.542V79.3102L39.4816 71.3316C39.4816 71.3316 39.4213 71.3316 39.4213 71.2711V71.1502C39.4213 71.1502 39.4816 71.1502 39.4816 71.2107L39.4213 63.4736C42.3183 65.7707 46 67.1005 49.9834 67.1005C59.4594 67.1005 67.1848 59.3634 67.1848 49.8736C67.1848 40.3838 59.4594 32.7676 49.9834 32.7676ZM49.9834 60.6327C44.129 60.6327 39.3609 55.8576 39.3609 49.9945C39.3609 44.1314 44.129 39.3562 49.9834 39.3562C55.8381 39.3562 60.6061 44.1314 60.6061 49.9945C60.5458 55.8576 55.8381 60.6327 49.9834 60.6327Z" fill="#E72E80"/>
	<path d="M70.2634 73.7549H81.1876V81.25H49.9839C49.1992 81.25 48.475 81.25 47.6904 81.1896C47.3282 81.1896 46.9661 81.1291 46.6643 81.0687H46.604C44.1898 80.8269 41.8359 80.2829 39.5424 79.4367V79.3158L39.4821 71.3371V71.2162C42.6809 72.7878 46.2418 73.6944 49.9839 73.6944C63.0809 73.6944 73.7036 63.056 73.7036 49.9396C73.7036 36.8231 63.0205 26.2451 49.9839 26.2451C36.9472 26.2451 26.2038 36.8836 26.2038 50C26.2038 56.3467 28.6783 62.0889 32.7221 66.3804V76.1122C30.9718 74.9638 29.3422 73.634 27.8333 72.1229C24.9363 69.282 22.7032 65.8969 21.1339 62.2098C19.5647 58.3413 18.7197 54.2311 18.7197 50C18.7197 45.7689 19.5647 41.6587 21.1943 37.8507C22.7635 34.1029 24.9967 30.7784 27.8937 27.9376C30.7304 25.0362 34.1105 22.7998 37.7921 21.2282C41.6549 19.5962 45.759 18.75 49.9235 18.75C54.1484 18.75 58.2525 19.5962 62.0549 21.2282C65.7971 22.7998 69.1166 25.0362 71.9533 27.9376C74.8504 30.7784 77.0835 34.1633 78.6527 37.8507C80.2823 41.7191 81.1273 45.8293 81.1273 50C81.1273 54.1707 80.2823 58.3413 78.6527 62.1493C77.0835 65.8969 74.8504 69.2216 71.9533 72.0624C71.4705 72.6669 70.8669 73.2109 70.2634 73.7549Z" fill="#FCEBF3"/>
	</g>
	<defs>
	<clipPath id="clip0_581_1194">
	<rect width="100" height="100" fill="white"/>
	</clipPath>
	</defs>
	</svg>
	 <p style="font-size: 25px;  margin: 0; letter-spacing: 3px;">QISST<span style=" font-weight: 900; font-style: italic;  letter-spacing: 3px;">PAY</span></p>
	 </div>
	</div>';
	
	echo '<div id="qisstpay_popup__overLay_id" class="qisstpay___image__overLay_Popup" onclick="return QisstPay__ModalCloseOutsideClick();">
	<div class="qisstpay__popup_whatisQp">
	<a class="qisstpay_popupMOdal_close_btn" onclick="return QisstPay__CloseModalwqpModalBtn();">&times;</a>
	<div class="Logo_redirect_qisstPay"><a href="https://qisstpay.com" target="_blank"><img src="' . esc_attr($imgLogo_qp) . '" class="qisstpay___image__ForDesktop"></a></div>
	<img src="' . esc_attr($imageNew_qp) . '" class="qisstpay___image__ForDesktop">
	<div class="Logo_redirect_qisstPay_mob"><a href="https://qisstpay.com" target="_blank">
	<img src="' . esc_attr($imgLogo_qp_mob) . '" class="qisstpay___image__ForMobile"></div>
	</a>
	<img src="' . esc_attr($imgMobile_qp) . '" class="qisstpay___image__ForMobile">
	<p class="qisstpay_popup__paragraph_Styles" style="margin-top:20px">All you need to apply is your debit or credit card. We only accept Visa or Mastercard.</p>
	<p class="qisstpay_popup__paragraph_Styles"><a href="https://qisstpay.com/terms-conditions" target="_blank" style="text-decoration:underline;font-size:13px;margin-right:3px;color:#707986;">Terms and Conditions</a>.You can reach us on info@qisstpay.com.</p>
	</div>
	</div>';
	}
	}



function QP_ps_deactivation() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return false;
	}
	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	check_admin_referer( "deactivate-plugin_{$plugin}" );

	# Uncomment the following line to see the function in action

	$new    = new QP_PluginStatus();
	$new->on_deactivation();


	return true;

}

register_deactivation_hook( __FILE__, 'QP_ps_deactivation' );



