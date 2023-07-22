<?php
/**
 * LoadmorePosts
 *
 * @package TeddyBearCustomizeAddon
 */

namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;

use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
use \WP_Query;

class Stripe {

	use Singleton;
	private $userInfo;
	private $theTable;
	private $cancelUrl;
	private $lastResult;
	private $successUrl;
	private $stripeSecretKey;
	private $stripePublishAble;
	
	protected function __construct() {
		// Replace with your own Stripe secret key
		// sk_test_51MYvdBI8VOGXMyoFiYpojuTUhvmS1Cxwhke4QK6jfJopnRN4fT8Qq6sy2Rmf2uvyHBtbafFpWVqIHBFoZcHp0vqq00HaOBUh1P
		// pk_test_51LUu8gCBz3oLWOMl7XCRKB11tJrH9jByvD14FWXgD3jRrD5PO2Lzpwoaf0rhprQOS5ghTqUQKa61OAY2IJwU70TR00fPjGno9D
		// sk_test_51LUu8gCBz3oLWOMlRLD2MrYZDhsU0gzmNGcqFouh5vXboLGsylT1MGx5t0UKYsHABS2T67KXcYgjgKNZRig1K42600z53h5FzU
		// load class.
		add_action( 'init', [ $this, 'setup_hooks' ], 10, 0 );

		add_action( 'init', [ $this, 'init' ], 10, 0 );
	}

	public function setup_hooks() {
		global $wpdb;$this->theTable	= $wpdb->prefix . 'fwp_stripe_subscriptions';
		$this->stripePublishAble			= apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'stripe-publishablekey', false );
		$this->stripeSecretKey				= apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'stripe-secretkey', false );
		$this->productID							= 'prod_NJlPpW2S6i75vM';
		$this->lastResult							= false;$this->userInfo = false;
		$this->successUrl							= site_url( 'payment/stripe/{CHECKOUT_SESSION_ID}/success' );
		$this->cancelUrl							= site_url( 'payment/stripe/{CHECKOUT_SESSION_ID}/cancel' );



		add_filter( 'futurewordpress/project/quizandfiltersearch/payment/stripe/paymentlink', [ $this, 'thePaymentlink' ], 10, 2 );
		add_filter( 'futurewordpress/project/quizandfiltersearch/payment/stripe/handlesuccess', [ $this, 'handleSuccess' ], 10, 2 );
		add_filter( 'futurewordpress/project/quizandfiltersearch/payment/stripe/payment_methods', [ $this, 'paymentMethods' ], 10, 0 );
		add_filter( 'futurewordpress/project/quizandfiltersearch/payment/stripe/payment_history', [ $this, 'paymentHistory' ], 10, 2 );
		add_filter( 'futurewordpress/project/quizandfiltersearch/payment/stripe/subscriptionToggle', [ $this, 'subscriptionToggle' ], 10, 3 );
		add_filter( 'futurewordpress/project/quizandfiltersearch/payment/stripe/subscriptionCancel', [ $this, 'subscriptionCancel' ], 10, 3 );
		add_filter( 'futurewordpress/project/quizandfiltersearch/payment/stripe/getsubscriptionby', [ $this, 'getSubscriptionBy' ], 10, 2 );
		add_filter( 'futurewordpress/project/quizandfiltersearch/payment/stripe/switchpaymentcard', [ $this, 'switchPaymentCard' ], 10, 2 );

		add_filter( 'futurewordpress/project/quizandfiltersearch/payment/stripe/allowswitchpause', [ $this, 'allowSitchPause' ], 10, 3 );

		add_filter( 'futurewordpress/project/quizandfiltersearch/rewrite/rules', [ $this, 'rewriteRules' ], 10, 1 );
		add_filter( 'query_vars', [ $this, 'query_vars' ], 10, 1 );
		add_filter( 'template_include', [ $this, 'template_include' ], 10, 1 );
	}
	public function query_vars( $query_vars  ) {
		$query_vars[] = 'pay_retainer';
		$query_vars[] = 'payment_status';
		$query_vars[] = 'session_id';
    	return $query_vars;
	}
	public function template_include( $template ) {
    $pay_retainer = get_query_var( 'pay_retainer' );$payment_status = get_query_var( 'payment_status' );
		if ( $pay_retainer && ! empty( $pay_retainer ) && ( $file = TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/cards/pay_retainer.php' ) && file_exists( $file ) && ! is_dir( $file ) ) {
      return $file;
    } else if ( $payment_status && ! empty( $payment_status ) && ( $file = TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/dashboard/cards/payment_status.php' ) && file_exists( $file ) && ! is_dir( $file ) ) {
			return $file;
		} else {
			return $template;
		}
	}
	public function rewriteRules( $rules ) {
		$rules[] = [ 'payment/stripe/([^/]*)/([^/]*)/?', 'index.php?session_id=$matches[1]&payment_status=$matches[2]', 'top' ];
		return $rules;
	}
	public function paymentMethods() {
		$methods = [ 'acss_debit', 'affirm', 'afterpay_clearpay', 'alipay', 'au_becs_debit', 'bacs_debit', 'bancontact', 'blik', 'boleto', 'card', 'customer_balance', 'eps', 'fpx', 'giropay', 'grabpay', 'ideal', 'klarna', 'konbini', 'link', 'oxxo', 'p24', 'paynow', 'pix', 'promptpay', 'sepa_debit', 'sofort', 'us_bank_account', 'wechat_pay' ];
		$result = [];foreach( $methods as $method ) {$result[ $method ] = $method;}return $result;
	}
	public function allowSitchPause( $default, $todo, $user_id ) {
		if( $todo == 'unpause' ) {
			return true;
		} else {
			$lastdid = get_usermeta( $user_id, 'subscription_last_changed', true );
			$someDate = new \DateTime( date( 'Y-M-d H:i:s', (int) $lastdid ) );
			$now = new \DateTime();
			if( $someDate->diff($now)->days > 60 ) {
				// The date was more than 60 days ago.
				return true;
			}
			return false;
		}
	}

	private function insertIntoTable( $json ) {
		global $wpdb;$args = (array) json_decode( $json, true );
		$user_id = get_current_user_id();

		// $status = $wpdb->query( $wpdb->prepare(
		// 	"INSERT INTO {$table}(user_id, user_email, subsc_id, user_object, user_address, invoice, phone, archived) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
		// 	$user_id, $args[ 'email' ], $args[ 'id' ], $args[ 'object' ], $args[ 'address' ], $args[ 'invoice_prefix' ], $args[ 'phone' ], maybe_serialize( $json )
		// ) );
		foreach( $args as $key => $val ) {
			if( $val == NULL ) {
				$args[ $key ] = false;
			}
		}
		$wpdb->insert( $this->theTable, [
			'user_id' => $user_id,
			'user_email' => $args[ 'email' ],
			'subsc_id' => $args[ 'id' ],
			'user_object' => $args[ 'object' ],
			'user_address' => $args[ 'address' ],
			'invoice' => $args[ 'invoice_prefix' ],
			'phone' => $args[ 'phone' ],
			'archived' => $json
		] );
	}
	private function stripePaymentTable( $json ) {
		global $wpdb;$user_id = get_current_user_id();

		$record_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}fwp_stripe_payments WHERE session_id=%s AND status=%s;", $json[ 'id' ], $json[ 'payment_status' ] ) );

		if( $record_count <= 0 ) {
			$wpdb->insert( $wpdb->prefix . 'fwp_stripe_payments', [
				'user_id' => $user_id,
				'session_id' => $json[ 'id' ],
				'customer_email' => $json[ 'customer_details' ][ 'email' ],
				'amount' => $json[ 'amount_total' ],
				'currency' => $json[ 'currency' ],
				'status' => $json[ 'payment_status' ],
				'archived' => maybe_serialize( json_encode( $json ) )
			] );
		} else {
			$wpdb->update( $wpdb->prefix . 'fwp_stripe_payments', [
				'user_id' => $user_id,
				'session_id' => $json[ 'id' ],
				'customer_email' => $json[ 'customer_details' ][ 'email' ],
				'amount' => $json[ 'amount_total' ],
				'currency' => $json[ 'currency' ],
				'status' => $json[ 'payment_status' ],
				'archived' => maybe_serialize( json_encode( $json ) )
			], [
				'session_id' => $json[ 'id' ],
			], [ '%s' ] );
		}
	}
	public function getUserData( $user_id ) {
		global $wpdb;$args = (array) json_decode( $json );
		$user_id = get_current_user_id();
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->theTable} user_id=%d", $user_id ) );
		return $rows;
	}

	public function thePaymentlink( $args, $default = false ) {
		// Your secret key from the Dashboard
		$secret_key						= $this->stripeSecretKey;
		$stripe_public_key		= $this->stripePublishAble;
		
		// $product_id						= "prod_NJj26vTpYGKnfA"; // Your Stripe product/plan ID
		$session = $this->create_stripe_checkout_session( $args );
		// print_r( $session );
    // $payment_link = "https://checkout.stripe.com/pay/" . $session_id;
		if( isset( $session[ 'error' ] ) ) {
			// print_r( $session );return false;
		} else {
			$payment_link = isset( $session[ 'url' ] ) ? $session[ 'url' ] : 'https://checkout.stripe.com/pay/'  . $session[ 'id' ];return $payment_link;
		}
	}
	public function create_stripe_checkout_session( $args = false ) {
		$stripe_public_key = $this->stripePublishAble;
    $curl = curl_init();
		$param = [
			'success_url'								=> $this->successUrl,
			'cancel_url'								=> $this->cancelUrl,
			'payment_method_types'			=> [ apply_filters( 'futurewordpress/project/quizandfiltersearch/system/getoption', 'stripe-paymentmethod', 'card' ) ],
			'line_items'								=> [
				// [
				// 	'quantity'	=> 1,
				// 	'price_data' => [
				// 		'currency' => 'usd',
				// 		'unit_amount' => 300,
				// 		'product_data' => [
				// 			'name' => 'T-shirt',
				// 			'description' => 'Comfortable cotton t-shirt',
				// 			'images' => [ esc_url( TEDDY_BEAR_CUSTOMIZE_ADDON_BUILD_URI . '/icons/Online payment_Flatline.svg' ) ],
				// 		],
				// 	]
				// ]
			],
			'mode'											=> 'payment',
		];
		$param[ 'line_items' ] = false;
		if( $args ) {$param[ 'line_items' ] = [$args];}
		// if( $param[ 'line_items' ] === false ) {return false;}

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.stripe.com/v1/checkout/sessions",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => http_build_query( $param ),
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer {$this->stripeSecretKey}",
        "Content-Type: application/x-www-form-urlencoded"
      ),
    ));

    $result = curl_exec($curl);
    curl_close($curl);

    $result = json_decode( $result, true);
		$this->lastResult = $result;
		// if( $result[ 'error' ] ) {return false;}
    // $session_id = isset( $result[ 'id' ] ) ? $result[ 'id' ] : false;
    return $result;
	}
	public function handleSuccess( $sessionId, $args = [] ) {
		$curl = curl_init();

		if ( ! $sessionId ) {return false;}
		// Make a request to the Stripe API to retrieve the session details
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://api.stripe.com/v1/checkout/sessions/" . $sessionId);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [ "Authorization: Bearer {$this->stripeSecretKey}" ]);
		$response = curl_exec($ch);
		curl_close($ch);
		// Decode the JSON response
		$session = json_decode($response, true);
		// Check if the session was successful
		if( in_array( $session['status'], [ 'success', 'complete' ] ) || in_array( $session[ 'payment_status' ], [ 'paid', 'success' ] ) ) {
			if( $meta = get_user_meta( get_current_user_id(), 'payment_done', true ) && $meta && ! empty( $meta ) ) {
				update_user_meta( get_current_user_id(), 'payment_done', wp_date( 'M d, Y H:i:s' ) );
			} else {
				add_user_meta( get_current_user_id(), 'payment_done', wp_date( 'M d, Y H:i:s' ) );
			}
		}
		$this->stripePaymentTable( $session );
		

		return $session;
	}
	public function userPaymentIntend() {
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.stripe.com/v1/payment_intents",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "amount=999&currency=usd&payment_method_types[]=card&success_url=https://example.com/success&cancel_url=https://example.com/cancel",
			CURLOPT_HTTPHEADER => array(
				"Authorization: Bearer {$this->stripeSecretKey}",
				"Content-Type: application/x-www-form-urlencoded"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			echo $response;
		}
		return $response;
	}

	public function customerIDfromEmail( $email ) {
		$this->userInfo = $userInfo = get_user_by( 'email', $email );
		if( $userInfo && ! empty( $userInfo->ID ) ) {
			$customer_id = get_user_meta( $userInfo->ID, 'stripe_customer_id', true );
			if( $customer_id && ! empty( $customer_id ) ) {
				return $customer_id;
			}
		}
		$url = "https://api.stripe.com/v1/customers?email=" . urlencode($email);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer {$this->stripeSecretKey}",
			"Content-Type: application/x-www-form-urlencoded"
		));
		$response = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($response);
		$this->lastResult = $data;
		
		if( isset( $data->data[0] ) && ! empty( $data->data[0]->id ) ) {
			if( $userInfo && ! empty( $userInfo->ID ) ) {
				update_user_meta( $userInfo->ID, 'stripe_customer_id', $data->data[0]->id );
			}
			$customer_id = $data->data[0]->id;
			return $customer_id;
		} else {
			return false;
		}
	}
	public function stripe_payment_history( $customerID ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payments');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Authorization: Bearer {$this->stripeSecretKey}",
			'Content-Type: application/x-www-form-urlencoded',
		] );
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			// echo 'cURL error: ' . curl_error($ch);
		}
		$payments = json_decode( $response, true );
		if (isset($payments['error'])) {
			// echo 'API error: ' . $payments['error']['message'];
		}

		return $payments;
		
		curl_close($ch);
	}
	public function paymentHistoryfromCustmerID( $customerID ) {
		if( ! $customerID ) {return false;}
		$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/charges?customer=' . $customerID );
    curl_setopt($ch, CURLOPT_USERPWD, $this->stripeSecretKey . ':');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
		
		// return $data;
		
    if( isset( $data[ 'error' ] ) ) {
      return false;
    } else {
      return isset( $data[ 'data' ] ) ? $data : false;
    }
	}
	public function paymentHistory( $default, $email ) {
		// $email = 'figosim608@mirtox.com';
		if( ! $email ) {return $default;}
		$customerID = $this->customerIDfromEmail( $email );
		if( ! $customerID ) {return $default;}
		// $payment_history = $this->stripe_payment_history( $customerID );
		$payment_history = $this->paymentHistoryfromCustmerID( $customerID );
		// print_r( json_encode( $payment_history ) );
		return ( $payment_history !== false ) ? $payment_history : $default;
	}

	public function subscriptionToggle( $status, $email, $user_id = false ) {
		// $customer_id = $this->customerIDfromEmail( $email );
		// print_r( [$status, $email, $user_id] );
		// if( $customer_id && ! empty( $customer_id ) ) {
		// 	if( $user_id ) {
		// 		// if( get_user_meta( $user_id, 'customer_id', true ) ) {
		// 		// 	update_user_meta( $user_id, 'customer_id', $customer_id );
		// 		// }
		// 	}
		return ( $this->pauseSubscriptionUsingEmail( $status, $email ) );
		// }
	}
	public function getSubscriptionBy( $default, $args = [] ) {
		$args = (object) wp_parse_args( $args, [
			'by'						=> 'email',
			'email'					=> '',
			'customer_id'		=> '',
			'object'				=> 'first'
		] );
		switch( $args->by ) {
			case 'email':
				$customerID = $this->customerIDfromEmail( $args->email );
				break;
			default:
				$customerID = $args->customer_id;
				break;
		}
		$response = $this->getStripeSubscriptionIdByCustomerID( $customerID );
		$subscription = $this->lastResult->data[0];
		switch( $args->object ) {
			case 'list':
				return $this->lastResult;
				break;
			default:
				return isset( $this->lastResult->data[0] ) ? $this->lastResult->data[0] : false;
				break;
		}
	}
	protected function getStripeCustomerIdByEmail( $email ) {
		$url = "https://api.stripe.com/v1/customers?email=" . urlencode($email);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer {$this->stripeSecretKey}",
			"Content-Type: application/x-www-form-urlencoded"
		));
		$response = curl_exec($ch);
		curl_close($ch);
		$data = json_decode( $response, true );
		$this->lastResult = $data;
		$customer_id = $data->data[0]->id;
		return $customer_id;
	}
	protected function stripe_subscription_toggle( $customer_id, $status ) {
    $url = "https://api.stripe.com/v1/subscriptions?customer=" . urlencode( $customer_id );
  
    if ($status == "pause") {
        $data = array("pause_collection" => "now");
    } else if ($status == "unpause") {
        $data = array("resume" => "now");
    } else {
        // return "Invalid status provided";
				return false;
    }
  
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Authorization: Bearer {$this->stripeSecretKey}",
			"Content-Type: application/x-www-form-urlencoded"
		] );
  
    $response = curl_exec($ch);
    $response_data = json_decode($response);
    curl_close($ch);

		// print_r( $response );wp_die();

    if (isset($response_data->error)) {
			// return $response_data->error->message;
			return false;
    } else {
			return true;
			// return "Subscription successfully updated";
    }
	}
	public function subscriptionCancel( $status, $email, $user_id = false ) {
		$customer_id = $this->customerIDfromEmail( $email );
		if( ! $customer_id ) {return false;}
		$subscription_id = $this->getStripeSubscriptionIdByCustomerID( $customer_id );
		if( ! $subscription_id ) {return false;}
		// print_r( [$status, $email, $user_id, $customer_id, $subscription_id] );
		$is_success = $this->cancelStripeSubscription( $subscription_id );
		return ( $is_success );
	}
	private function cancelStripeSubscription( $subscription_id ) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.stripe.com/v1/subscriptions/" . urlencode( $subscription_id ),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "DELETE",
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/x-www-form-urlencoded",
				"Authorization: Bearer {$this->stripeSecretKey}"
			),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

		// print_r( $response );

    if( ! $err ) {
			$data = json_decode($response);
			if( ! empty( $data->status ) && $data->status == 'canceled' ) {
				return true;
			}
			if( ! empty( $data->deleted ) && $data->deleted == true) {
				return true;
			}
    }
    return false;
	}
	private function getStripeSubscriptionIdByCustomerID( $customer_id ) {
		$url = "https://api.stripe.com/v1/subscriptions?customer=" . urlencode( $customer_id );
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $this->stripeSecretKey,
			"Content-Type: application/x-www-form-urlencoded"
		));
		$response = curl_exec($ch);
		curl_close($ch);
		$data = json_decode( $response, true );
		$this->lastResult = $data;
		if( isset( $data[ 'error' ] ) ) {
			if( isset( $data[ 'error' ][ 'code' ] ) && $data[ 'error' ][ 'code' ] == 'resource_missing' && $this->userInfo !== false ) {
				update_user_meta( $this->userInfo->ID, 'stripe_customer_id', false );
			}
			return false;
		} else {
			if( isset( $data[ 'data' ][0] ) && isset( $data[ 'data' ][0][ 'id' ] ) ) {
				$subscription_id = $data[ 'data' ][0][ 'id' ];
				return $subscription_id; 
			} else {
				return false;
			}
		}
	}
	private function toggleStripeSubscriptionPause( $action, $subscription_id ) {
		$url = "https://api.stripe.com/v1/subscriptions/" . urlencode( $subscription_id );

		// $data = [ 'pause_collection' => [ 'behavior' => 'void' ] ];
		if ($action == "pause") {
			// $data = array("pause_collection" => "now");
			$data = [ 'pause_collection' => [ 'behavior' => 'mark_uncollectible' ] ];
		} else if ($action == "unpause") {
			// $data = array("resume" => "now");
			$data = [ 'pause_collection' => '' ]; // [ 'resumes_at' =>  date( 'c' ) ]
		} else {
			return false;
		}
		// print_r( $data );wp_die();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Authorization: Bearer " . $this->stripeSecretKey,
			"Content-Type: application/x-www-form-urlencoded"
		));

		$response = curl_exec($ch);
		curl_close($ch);

		// echo "Subscription Changed: " . $subscription_id;
		// print_r( $response );wp_die();
		return true;
	}
	public function pauseSubscriptionUsingEmail( $action, $email ) {
		$api_key = $this->stripeSecretKey;
		$customer_id = $this->customerIDfromEmail( $email );
		if( ! $customer_id ) {return false;}
		$subscription_id = $this->getStripeSubscriptionIdByCustomerID( $customer_id );
		// print_r( [$customer_id, $subscription_id, $email] );wp_die();
		$status = in_array( $action, [ 'pause', 'unpause' ] ) ? $action : false;
		if( ! $status ) {return $status;}
		$is_success = $this->toggleStripeSubscriptionPause( $status, $subscription_id );
		return ( $is_success );
	}
	public function get_all_subscriptions() {
		// Set the API endpoint URL and the cURL options
		$url = "https://api.stripe.com/v1/subscriptions";
		$curl_options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array(
				"Authorization: Bearer " . $this->stripeSecretKey
			)
		);
	
		// Initialize cURL session and set the URL and options
		$curl = curl_init($url);
		curl_setopt_array($curl, $curl_options);
	
		// Execute the cURL request and get the response
		$response = curl_exec($curl);
		curl_close($curl);

		// return $response;
		// Decode the JSON response and return the subscription data
		$subscriptions = json_decode($response, true);
		return $subscriptions['data'];
	}

	
	public function init() {
		if( ! isset( $_GET[ 'die_mode' ] ) ) {return;}
		// $notices = get_option( 'fwp_we_make_content_admin_notice', [] );
		// foreach( $notices as $i => $notice ) {
		// 	$notices[ $i ][ 'data' ][ 'time' ] = strtotime( $notices[ $i ][ 'data' ][ 'time' ] );
		// 	// if( $notice[ 'data' ][ 'time' ] && strtotime( '-15 days' ) >= $notice[ 'data' ][ 'time' ] ) {
		// 		// unset( $notices[ $i ] );
		// 		// print_r( [strtotime( '-15 days' ), $notice[ 'data' ][ 'time' ]]);
		// 	// }
		// }
		// print_r( $notices );
		$this->stripeSecretKey				= 'sk_test_51MYvdBI8VOGXMyoFiYpojuTUhvmS1Cxwhke4QK6jfJopnRN4fT8Qq6sy2Rmf2uvyHBtbafFpWVqIHBFoZcHp0vqq00HaOBUh1P';
		$this->stripePublishAble			= 'pk_test_51LUu8gCBz3oLWOMl7XCRKB11tJrH9jByvD14FWXgD3jRrD5PO2Lzpwoaf0rhprQOS5ghTqUQKa61OAY2IJwU70TR00fPjGno9D';

			// 'cus_NOgvvpyguFIFOL'
		// $response = $this->stripe_payment_history( $this->customerIDfromEmail( 'radvix.flow@gmail.com' ) );
		// print_r( $this->pauseSubscriptionUsingEmail( 'pause', 'nimoultv@gmail.com' ) );wp_die();
		// $subscription_data = $this->get_subscription_data_by_email( 'figosim608@mirtox.com' );print_r($subscription_data);
		// $response = $this->getStripeSubscriptionIdByCustomerID( $this->customerIDfromEmail( 'info@futurewordpress.com' ) );
		// print_r( $this->lastResult );
		// // $response = $this->get_all_subscriptions();
		// $subscription = $this->lastResult->data[0];
		// print_r( [
		// 	$subscription
		// ] );

		wp_die();
	}

	/**
	 * Payment Card Related functions.
	 */
	public function getOrCreateCardToken( $cardNumber, $expMonth, $expYear, $cvc ) {
		$url = 'https://api.stripe.com/v1/tokens';
		$headers = array(
			'Authorization: Bearer ' . $this->stripeSecretKey,
			'Content-Type: application/x-www-form-urlencoded'
		);
		$fields = [
			'card[number]'		=> $cardNumber,
			'card[exp_month]'	=> $expMonth,
			'card[exp_year]'	=> $expYear,
			'card[cvc]'				=> $cvc
		];
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($ch);
		
		curl_close($ch);
		
		$responseArray = json_decode( $response, true );
		$this->lastResult = $responseArray;
		
		if (isset($responseArray['error'])) {
			return false;
		} else {
			return $responseArray['id'];
		}
	}
	/**
	 * Update the expiration date for an existing card token
	 */
	public function updateCardExpiration( $cardToken, $expMonth, $expYear ) {
		$url = 'https://api.stripe.com/v1/tokens/' . $cardToken;
		$headers = array(
			'Authorization: Bearer ' . $this->stripeSecretKey,
			'Content-Type: application/x-www-form-urlencoded'
		);
		
		$fields = [
			'card[exp_month]' => $expMonth,
			'card[exp_year]' => $expYear
		];
		
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $fields) );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		
		$response = curl_exec( $ch );
		curl_close( $ch );
		$responseArray = json_decode( $response, true );
		$this->lastResult = $responseArray;
		if( isset( $responseArray[ 'error' ] ) ) {
			return false;
		} else {
			return true;
		}
	}
	public function getOrCreatePaymentMethod( $cardToken ) {
    // Create payment method from card token
    $url = 'https://api.stripe.com/v1/payment_methods';
    $headers = [
			'Authorization: Bearer ' . $this->stripeSecretKey,
			'Content-Type: application/x-www-form-urlencoded'
		];
    $fields = [
			'type' => 'card',
			'card[token]' => $cardToken
		];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    $this->lastResult = $paymentMethod = json_decode( $response, true );
		if( isset( $paymentMethod[ 'error' ] ) ) {
			return false;
		}
		return $paymentMethod[ 'id' ];
	}
	/**
	 * Attach payment method to customer
	 */
	public function attachPaymentMethodToCustomer( $customerId, $paymentMethodId ) {
    $url = "https://api.stripe.com/v1/payment_methods/$paymentMethodId/attach";
    $headers = [
			'Authorization: Bearer ' . $this->stripeSecretKey,
			'Content-Type: application/x-www-form-urlencoded'
		];
    $fields = [ 'customer' => $customerId ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    $this->lastResult = $response;
    if (isset($response['error'])) {
        return false;
    } else {
        return $response['id'];
    }
	}
	/**
	 * Update subscription to use payment method
	 */
	public function addPaymentMethodToSubscription( $subscriptionId, $paymentMethodId ) {
    $url = "https://api.stripe.com/v1/subscriptions/$subscriptionId";
    $headers = [
			'Authorization: Bearer ' . $this->stripeSecretKey,
			'Content-Type: application/x-www-form-urlencoded'
		];
    $fields = [ 'default_payment_method' => $paymentMethodId ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    $this->lastResult = $response = json_decode( $response, true );
		if( isset( $response[ 'error' ] ) ) {
			return false;
		} else {
			return $response[ 'id' ];
		}
	}



	/**
	 * Request for appling to switch or change payemtn card on a subscription.
	 */
	public function switchPaymentCard( $default, $args ) {
		$args = (object) $args;
		try {
			// $args->card_email
			$customerID = $this->customerIDfromEmail( $args->card_email );
			if( $customerID ) {
				$subscriptionId = $this->getStripeSubscriptionIdByCustomerID( $customerID );
				if( $subscriptionId ) {
					$cardToken = $this->getOrCreateCardToken( $args->card_number, $args->card_month, $args->card_year, $args->card_cvc );
					if( $cardToken ) {
						$paymentMethod = $this->getOrCreatePaymentMethod( $cardToken );
						if( $paymentMethod ) {
							$isAttachedMethod = $this->attachPaymentMethodToCustomer( $customerID, $paymentMethod );
							if( $isAttachedMethod ) {
								$addPayMethod2Subscribe = $this->addPaymentMethodToSubscription( $subscriptionId, $paymentMethod );
								if( $addPayMethod2Subscribe ) {
									return true;
								}
							}
						}
					}
				}
			}
			return $default;
		} catch(\Exception $e) {
			$this->lastError = $e->getMessage();
			// print_r( [$this->lastError, $this->lastResult] );
			return $default;
		}
	}
	


	
}
