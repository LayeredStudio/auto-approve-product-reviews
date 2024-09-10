<?php
/*
Plugin Name: Auto Approve Product Reviews
Plugin URI: https://layered.store/plugins/auto-approve-product-reviews
Requires Plugins: woocommerce
Description: Auto-approve WooCommerce product reviews with a minimum rating chosen by you
Version: 1.1.0
Author: Layered
Author URI: https://layered.store
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

class LayeredAutoApproveReviews {

	public static function start() {
		return new static;
	}

	public function __construct() {

		// Display settings to change auto approve options
		add_filter('woocommerce_get_settings_products', [$this, 'settings'], 10, 2);

		// Mark review as approved on submit
		add_filter('pre_comment_approved', [$this, 'checkReview'], 500, 2);

		// Display a shortcut Settings link on Plugin line
		add_filter('plugin_action_links_auto-approve-product-reviews/auto-approve-product-reviews.php', [$this, 'actionLinks']);

	}

	public function settings(array $settings, string $current_section) {

		if (!$current_section || $current_section === 'general') {

			// Basic detection for Reviews section end
			$productRatingEnd = count($settings) - 1;

			// Search the position of Reviews section end in Settings
			foreach ($settings as $index => $setting) {
				if ($setting['type'] === 'sectionend' && $setting['id'] === 'product_rating_options') {
					$productRatingEnd = $index;
				}
			}

			array_splice($settings, $productRatingEnd, 0, [[
				'title'		=>	'Auto approve rating',
				'desc'		=>	'Auto approve reviews with this minimum rating',
				'id'		=>	'woocommerce_reviews_auto_approve_rating',
				'class'		=>	'wc-enhanced-select',
				'default'	=>	'5',
				'type'		=>	'select',
				'options'	=> [
					'0'	=>	'Do not automatically approve',
					'1'	=>	'1',
					'2'	=>	'2',
					'3'	=>	'3',
					'4'	=>	'4',
					'5'	=>	'5',
				],
				'autoload'	=>	false,
			]]);
		}

		return $settings;
	}

	public function checkReview($approved, $commentdata) {
		$isUnapprovedReview = $commentdata['comment_type'] === 'review' && $approved == 0;

		if ($isUnapprovedReview && isset($_POST['rating']) && ($minRating = get_option('woocommerce_reviews_auto_approve_rating')) && $_POST['rating'] >= $minRating) {
			$approved = 1;
		}

		return $approved;
	}

	public function actionLinks(array $links) {
		return array_merge([
			'settings'	=>	'<a href="' . menu_page_url('wc-settings', false) . '&tab=products">Settings</a>'
		], $links);
	}

	public static function onActivation() {
		add_option('woocommerce_reviews_auto_approve_rating', 5);
	}

}

// Run the plugin
add_action('plugins_loaded', 'LayeredAutoApproveReviews::start');
register_activation_hook(__FILE__, 'LayeredAutoApproveReviews::onActivation');
