<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://mehrdaddindar.ir
 * @since      1.0.0
 *
 * @package    Vanak
 * @subpackage Vanak/public
 */

use Telegram\Bot\Api;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Vanak
 * @subpackage Vanak/public
 * @author     Mehrdad Dindar <mehrdad.dindar@live.com>
 */
class Vanak_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Vanak_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Vanak_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/vanak-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Vanak_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Vanak_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/vanak-public.js', array( 'jquery' ), $this->version, false );

	}

	// sendNewOrder methode
	public function sendNewOrder($order_id) {
		try {
			$invoiceBody = $this->getInvoiceBody($order_id);

			$token = stm_wpcfto_get_options('vanak_settings')["token"];
			$chatID = get_option("vanak_chat_id");

            if (get_option("vanak_settings")["bot_type"]) {
                $bot = new Api($token);
            } else {
                $bot = new balebot($token);
            }
			$inlineKeyboardoption =	[
				$bot->buildInlineKeyBoardButton(__("Order Details","vanak"), get_site_url().'/wp-admin/post.php?post='
					.$order_id.'&action=edit','callback text' ),
			];
			$Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);

			$bot->sendText(array(
				'chat_id' => $chatID,
				'text'	=>	$invoiceBody,
				'reply_markup' =>$Keyboard
			));
		}catch (Exception $e) {
			wp_die(json_encode($e->getMessage()));
		}
	}

	private function getState(WC_Order $order,$type = "billing")
	{
		switch ($type){
			case "shipping":
				$country_code = $order->get_shipping_country();
				$state = $order->get_shipping_state();
                break;
            default:
				$country_code = $order->get_billing_country();
				$state = $order->get_billing_state();
                break;
		}
		$countries = new WC_Countries();
		$country_states = $countries->get_states( $country_code );
		return $country_states[$state];
	}

	private function getInvoiceBody($order_id)
	{
		$order = wc_get_order($order_id);
		$currency = " ".html_entity_decode(get_woocommerce_currency_symbol());

		// order details
		$text = "🛒 #سفارش_جدید\n🆔 #".$order->get_id()."\n\n";
		$text .= "*جزئیات سفارش*\n";
		$orderItems = $order->get_items();
		$no = 1;
		foreach ($orderItems as $orderItem) {
			$product = $orderItem->get_product();
			$percentage = round( ( (int)$product->sale_price  / (int)
					$product->regular_price)	* 100 );
			$percentage = $percentage ? " (%) " : "";

			$text .= tr_num($no++,"fa")." - [".$orderItem['name']."](".$product->get_permalink().")". $percentage
				." - تعداد :  *"
				.$orderItem->get_quantity()
				."*\n";
			$product_price = $product->get_price();
			$text .= tr_num(number_format($product_price)."		".number_format($orderItem->get_quantity())."		"
				.number_format($product_price *
					$orderItem->get_quantity())."	".$currency."\n\n", "fa");
		}

		$text .= "جمع کل :			*".tr_num(number_format($order->get_subtotal()).$currency,"fa")."*\n";

		$coupon_codes = $order->get_coupon_codes();

		if ($coupon_codes) {
			$text .= "تخفیف‌ها :		*-" . tr_num(number_format($order->get_discount_total()) . $currency, "fa") . "*\n";
		}

		$text .= "حمل و نقل :		*" . (intval($order->get_shipping_total()) > 0 ? tr_num(number_format($order->get_shipping_total()).$currency, "fa")  : $order->get_shipping_method())  . "*\n";

		$text .= "جمع کل سفارش:	*".tr_num(number_format($order->get_total()).$currency,"fa")."*\n";
		$text .= "نوع ارسال: 		".$order->get_shipping_method()."\n";
		$text .= "نوع پرداخت: 		".$order->get_payment_method_title()."\n";
		$text .= "وضعیت سفارش: 	*".wc_get_order_status_name($order->get_status())."*\n";
		$text .= "تاریخ سفارش: 		".jdate("Y/m/d H:i:s", ($order->get_date_created())->getTimestamp())."\n";
		if ($coupon_codes) {
			$text .= "کد تخفیف: " . implode(" , ", $coupon_codes) . "\n";
		}
		$text .= "\n";


		$text .= "*مشخصات مشتری*\n";
		$text.= "نام : ".$order->get_billing_first_name()."\n";
		$text.= "نام خانوادگی : #".$order->get_billing_last_name()."\n";
		$text.= "شماره تماس : ".tr_num(wc_format_phone_number($order->get_billing_phone()))."\n";
		$text.= "ایمیل : ".$order->get_billing_email()."\n\n";
		$text.= "--------------- *آدرس فاکتور* ---------------\n";

		$text .= "استان/شهر : 	".$this->getState($order)." / ".$order->get_billing_city()."\n";
		$text .= "آدرس ۱: ".$order->get_billing_address_1()."\n";
		if ($order->get_billing_address_2()){
			$text.= "آدرس ۲: ".$order->get_billing_address_2()."\n";
		}
		$text .= "کد پستی: ".$order->get_billing_postcode()."\n\n";

		if( $order->get_billing_address_1() != $order->get_shipping_address_1() ) {
			$text .= "--------------- *آدرس ارسال* ---------------\n";

			$text .= "استان/شهر : 	" . $this->getState($order, "shipping") . " / " . $order->get_shipping_city() . "\n";
			$text .= "آدرس ۱: " . $order->get_shipping_address_1() . "\n";
			if ($order->get_shipping_address_2()) {
				$text .= "آدرس ۲: " . $order->get_shipping_address_2() . "\n";
			}
			$text .= "کد پستی: " . $order->get_shipping_postcode() . "\n\n";
		}
		if ($order->get_customer_note()) {
			$text .= "--------------- *توضیحات* ---------------\n";
			$text .= "🛎️ " . $order->get_customer_note() . "\n";
		}
		return $text;
	}

	public function sendNewComment($comment_id, $comment_approved, $comment_data)
	{
		try {
			$commentBody = $this->getCommentBody($comment_id);


			$token = stm_wpcfto_get_options('vanak_settings')["token"];
			$chatID = get_option("vanak_chat_id");

			$bale = new balebot($token);
			$bale->sendMessage(array(
				"chat_id" => $chatID,
				"text" => $commentBody
			));
		}catch (Exception $e) {
			wp_die(json_encode($e->getMessage()));
		}
	}

	private function getCommentBody($comment_id)
	{
		$comment = get_comment($comment_id);

		$text = "💬 #دیدگاه_جدید\n🆔 #".$comment->comment_ID."\n\n";
		$text .= "*جزئیات دیدگاه*\n";
		$text .= "زمان ثبت : ".jdate("Y/m/d H:i:s", strtotime($comment->comment_date_gmt))."\n";

		$commentType = ucfirst($comment->comment_type);
		switch ($commentType){
			case "Review":
				$text .= "نوع : " . esc_html__($commentType, 'woocommerce')."\n";
				$rating = intval(get_comment_meta($comment_id, 'rating', true));
				$text .="امتیاز : " . str_repeat("★", $rating);
				$text .= str_repeat("☆", 5 - $rating);
				$text .= "\n";
				$commentType = esc_html__($commentType, 'woocommerce');
				break;
            default:
				$text .= "نوع : " . esc_html__($commentType)."\n";
				$commentType = esc_html__($commentType);

                break;
		}
		$text .= $comment->comment_content."\n\n";

		$user = get_user_by('id', $comment->user_id);

			$text .= "*مشخصات کاربر*\n";
		if ($user) {
			$text .= "نام : " . $user->user_firstname . "\n";
			$text .= "نام خانوادگی : #" . $user->last_name . "\n";
			$text .= "نام نمایشی : " . $user->display_name . "\n";
		}else{
			$text .= "نام : " . $comment->comment_author . "\n";
			$text .= "ایمیل : " . $comment->comment_author_email . "\n";
			$text .= "* _نویسنده ".$commentType." عضو سایت نیست_\n";
		}

		$text .= "IP : " . $comment->comment_author_IP . "\n\n";

		$post = get_post($comment->comment_post_ID);
		$text .= $commentType ." برای [" . $post->post_title . "](".get_comment_link($comment_id) .") نوشته شده است\n";

		return $text;
	}

	public function guard_check_function()
	{

		$license_token = stm_wpcfto_get_options('vanak_settings')['licence'];

		$result = Zhaket_License::isValid($license_token);

		if ($result->status=='successful') {
			update_option("vanak_license", "isValid");
			update_option("vanak_license_message", $result->message);
		} else {
			$this->licenceFailed($result->message);
			do_action("vanak_unscheduled_hook");
		}
	}

	private function licenceFailed($message)
	{
		// License not installed / show message
		if (!is_object($message)) {
			update_option("vanak_license_message", $message);
		} else {
			$msg = [];
			foreach ($message as $all_message) {
				foreach ($all_message as $mesag) {
					$msg[] = $mesag.'<br>';
				}
			}
			update_option("vanak_license_message", maybe_serialize($msg));
		}
		update_option("vanak_license", false);
		$settings = get_option("vanak_settings");
		$settings['licence'] = null;
		update_option('vanak_settings', $settings);
	}

	public function unscheduled_vanak_task()
	{
		$timestamp = wp_next_scheduled( 'vanak_guard_check_hook' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'vanak_guard_check_hook' );
		}

	}

}
