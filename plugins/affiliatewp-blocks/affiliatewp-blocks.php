<?php
/**
 * Plugin Name:     AffiliateWP - Blocks
 * Description:     Blocks for AffiliateWP
 * Version:         1.0.1
 * Author:          Sandhills Development, LLC
 * Author URI:      https://sandhillsdev.com
 * Plugin URI:      https://affiliatewp.com/add-ons/official-free/blocks
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     affiliatewp-blocks
 *
 * @package         affiliatewp-blocks
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AffiliateWP_Blocks' ) ) {

	/**
	 * Setup class.
	 *
	 * @since 1.0
	 */
	final class AffiliateWP_Blocks {

		/**
		 * Holds the instance.
		 *
		 * Ensures that only one instance of AffiliateWP_Blocks exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @access private
		 * @var    \AffiliateWP_Blocks
		 * @static
		 *
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * The version number.
		 *
		 * @access private
		 * @since  1.0
		 * @var    string
		 */
		private $version = '1.0.1';

		/**
		 * Generates the main AffiliateWP_Blocks instance.
		 *
		 * Insures that only one instance of AffiliateWP_Blocks exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access	public
		 * @since	1.0
		 * @static
		 *
		 * @return \AffiliateWP_Blocks The one true AffiliateWP_Blocks.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AffiliateWP_Blocks ) ) {

				self::$instance = new AffiliateWP_Blocks;
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->hooks();

			}

			return self::$instance;
		}

		/**
		 * Throws an error on object clone.
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
 		 * @access protected
		 * @since  1.0
		 *
		 * @return void
		 */
		protected function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-blocks' ), '1.0' );
		}

		/**
		 * Disables unserializing of the class.
		 *
		 * @access protected
		 * @since  1.0
		 *
		 * @return void
		 */
		protected function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliatewp-blocks' ), '1.0' );
		}

		/**
		 * Sets up the class.
		 *
		 * @access private
		 * @since  1.0
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Resets the instance of the class.
		 *
		 * @access public
		 * @since  1.0
		 * @static
		 */
		public static function reset() {
			self::$instance = null;
		}

		/**
		 * Setup plugin constants
		 *
		 * @access private
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function setup_constants() {
			// Plugin version
			if ( ! defined( 'AFFWP_BLOCKS_VERSION' ) ) {
				define( 'AFFWP_BLOCKS_VERSION', $this->version );
			}

			// Plugin Folder Path
			if ( ! defined( 'AFFWP_BLOCKS_PLUGIN_DIR' ) ) {
				define( 'AFFWP_BLOCKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL
			if ( ! defined( 'AFFWP_BLOCKS_PLUGIN_URL' ) ) {
				define( 'AFFWP_BLOCKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File
			if ( ! defined( 'AFFWP_BLOCKS_PLUGIN_FILE' ) ) {
				define( 'AFFWP_BLOCKS_PLUGIN_FILE', __FILE__ );
			}
		}

		/**
		 * Include necessary files.
		 *
		 * @access private
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function includes() {}

		/**
		 * Sets up the default hooks and actions.
		 *
		 * @access private
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function hooks() {

			add_action( 'init', array( $this, 'blocks_init' ) );

			add_filter( 'block_categories', array( $this, 'add_block_category' ), 10, 2 );

			// plugin meta
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta' ), null, 2 );

		}

		/**
		 * Modifies the plugin list table meta links.
		 *
		 * @access public
		 * @since  1.0.0
		 *
		 * @param array  $links The current links array.
		 * @param string $file  A specific plugin table entry.
		 * @return array The modified links array.
		 */
		public function plugin_meta( $links, $file ) {

		    if ( $file == plugin_basename( __FILE__ ) ) {

				$url = admin_url( 'admin.php?page=affiliate-wp-add-ons' );

				$plugins_link = array( '<a title="' . esc_attr__( 'Get more add-ons for AffiliateWP', 'affiliatewp-blocks' ) . '" href="' . esc_url( $url ) . '">' . __( 'More add-ons', 'affiliatewp-blocks' ) . '</a>' );

				$links = array_merge( $links, $plugins_link );
			}

			return $links;

		}

		/**
		 * Registers all block assets so that they can be enqueued through the block editor
		 * in the corresponding context.
		 *
		 * @since 1.0.0
		 *
		 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
		 */
		public function blocks_init() {

			$dir = dirname( __FILE__ );

			$script_asset_path = "$dir/build/index.asset.php";
			if ( ! file_exists( $script_asset_path ) ) {
				throw new Error(
					'You need to run `npm start` or `npm run build` for the "affiliatewp/affiliatewp-blocks" block first.'
				);
			}
			$index_js     = 'build/index.js';
			$script_asset = require( $script_asset_path );

			wp_register_script(
				'affiliatewp-blocks-editor',
				plugins_url( $index_js, __FILE__ ),
				array( 'wp-blocks', 'wp-components', 'wp-i18n', 'wp-polyfill', 'wp-element', 'wp-editor' ),
				$script_asset['version']
			);

			wp_localize_script( 'affiliatewp-blocks-editor', 'affwp_blocks', array(
				'terms_of_use'                 => affiliate_wp()->settings->get( 'terms_of_use' ) ? true : false,
				'terms_of_use_label'           => affiliate_wp()->settings->get( 'terms_of_use_label', __( 'Agree to our Terms of Use and Privacy Policy', 'affiliate-wp' ) ),
				'required_registration_fields' => affiliate_wp()->settings->get( 'required_registration_fields' ),
				'affiliate_area_forms'         => affiliate_wp()->settings->get( 'affiliate_area_forms' ),
				'allow_affiliate_registration' => affiliate_wp()->settings->get( 'allow_affiliate_registration' ),
				'affiliate_id'                 => affwp_get_affiliate_id( get_current_user_id() ),
				'affiliate_username'           => affwp_get_affiliate_username( affwp_get_affiliate_id( get_current_user_id() ) ),
				'referral_variable'            => affiliate_wp()->tracking->get_referral_var(),
				'referral_format'              => affwp_get_referral_format(),
				'pretty_referral_urls'         => affwp_is_pretty_referral_urls(),
			) );

			$editor_css = 'editor.css';
			wp_register_style(
				'affiliatewp-blocks-editor',
				plugins_url( $editor_css, __FILE__ ),
				array(),
				filemtime( "$dir/$editor_css" )
			);

			register_block_type(
				'affiliatewp/affiliate-area',
				array(
					'editor_script' => 'affiliatewp-blocks-editor',
					'editor_style'  => 'affiliatewp-blocks-editor',
					'render_callback' => array( $this, 'affiliate_area_block_render_callback' ),
				)
			);

			register_block_type(
				'affiliatewp/registration',
				array(
					'editor_script' => 'affiliatewp-blocks-editor',
					'editor_style'  => 'affiliatewp-blocks-editor',
					'render_callback' => array( $this, 'registration_block_render_callback' ),
					'attributes'      => array(
						'redirect'    => array(
							'type'      => 'string',
							'default'   => '',
						),
					),
				)
			);

			register_block_type(
				'affiliatewp/login',
				array(
					'editor_script' => 'affiliatewp-blocks-editor',
					'editor_style'  => 'affiliatewp-blocks-editor',
					'render_callback' => array( $this, 'login_block_render_callback' ),
					'attributes'      => array(
						'redirect'    => array(
							'type'      => 'string',
							'default'   => '',
						),
					),
				)
			);

			register_block_type(
				'affiliatewp/affiliate-content',
				array(
					'editor_script' => 'affiliatewp-blocks-editor',
					'editor_style'  => 'affiliatewp-blocks-editor',
					'render_callback' => array( $this, 'affiliate_content_block_render_callback' ),
				)
			);

			register_block_type(
				'affiliatewp/non-affiliate-content',
				array(
					'editor_script' => 'affiliatewp-blocks-editor',
					'editor_style'  => 'affiliatewp-blocks-editor',
					'render_callback' => array( $this, 'non_affiliate_content_block_render_callback' ),
				)
			);

			register_block_type(
				'affiliatewp/opt-in',
				array(
					'editor_script' => 'affiliatewp-blocks-editor',
					'editor_style'  => 'affiliatewp-blocks-editor',
					'render_callback' => array( $this, 'opt_in_block_render_callback' ),
					'attributes'      => array(
						'redirect'    => array(
							'type'      => 'string',
							'default'   => '',
						),
					),
				)
			);

			register_block_type(
				'affiliatewp/affiliate-referral-url',
				array(
					'editor_script'   => 'affiliatewp-blocks-editor',
					'editor_style'    => 'affiliatewp-blocks-editor',
					'render_callback' => array( $this, 'affiliate_referral_url_block_render_callback' ),
					'attributes' => array(
						'url' => array(
							'type'    => 'string',
							'default' => '',
						),
						'format' => array(
							'type'    => 'string',
							'default' => 'default'
						),
						'pretty' => array(
							'type'    => 'string',
							'default' => 'default'
						),
					),
				)
			);

			register_block_type(
				'affiliatewp/affiliate-creatives',
				array(
					'editor_script'   => 'affiliatewp-blocks-editor',
					'editor_style'    => 'affiliatewp-blocks-editor',
					'render_callback' => array( $this, 'affiliate_creatives_block_render_callback' ),
					'attributes' => array(
						'preview' => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'number' => array(
							'type'    => 'number',
							'default' => 20,
						),
					),
				)
			);

			register_block_type(
				'affiliatewp/affiliate-creative',
				array(
					'editor_script'   => 'affiliatewp-blocks-editor',
					'editor_style'    => 'affiliatewp-blocks-editor',
					'render_callback' => array( $this, 'affiliate_creative_block_render_callback' ),
					'attributes' => array(
						'id' => array(
							'type'    => 'integer',
						),
					),
				)
			);

			register_block_type(
				'affiliatewp/affiliate-conversion-script',
				array(
					'editor_script'   => 'affiliatewp-blocks-editor',
					'editor_style'    => 'affiliatewp-blocks-editor',
					'render_callback' => array( $this, 'affiliate_conversion_script_block_render_callback' ),
					'attributes' => array(
						'amount' => array(
							'type'    => 'number',
							'default' => '',
						),
						'description' => array(
							'type'    => 'string',
							'default' => '',
						),
						'reference' => array(
							'type'    => 'string',
							'default' => '',
						),
						'context' => array(
							'type'    => 'string',
							'default' => '',
						),
						'campaign' => array(
							'type'    => 'string',
							'default' => '',
						),
						'status' => array(
							'type'    => 'string',
							'default' => 'pending',
						),
						'type' => array(
							'type'    => 'string',
							'default' => 'sale',
						),
					),
				)
			);

		}

		/**
		 * Affiliate Area block callback
		 *
		 * @since 1.0.0
		 */
		public function affiliate_area_block_render_callback( $attributes, $content ) {
			return (new Affiliate_WP_Shortcodes())->affiliate_area( $attributes );
		}

		/**
		 * Registration block callback
		 *
		 * @since 1.0.0
		 */
		public function registration_block_render_callback( $attributes ) {
			return (new Affiliate_WP_Shortcodes())->affiliate_registration( $attributes );
		}

		/**
		 * Login block callback
		 *
		 * @since 1.0.0
		 */
		public function login_block_render_callback( $attributes ) {
			return (new Affiliate_WP_Shortcodes())->affiliate_login( $attributes );
		}

		/**
		 * Affiliate content block callback
		 *
		 * @since 1.0.0
		 */
		public function affiliate_content_block_render_callback( $attributes, $content ) {

			if ( ! ( affwp_is_affiliate() && affwp_is_active_affiliate() ) ) {
				return;
			}

			return $content;
		}

		/**
		 * Non affiliate content block callback
		 *
		 * @since 1.0.0
		 */
		public function non_affiliate_content_block_render_callback( $attributes, $content ) {

			if ( affwp_is_affiliate() && affwp_is_active_affiliate() ) {
				return;
			}

			return $content;
		}

		/**
		 * Opt-in block callback
		 *
		 * @since 1.0.0
		 */
		public function opt_in_block_render_callback( $attributes, $content ) {
			return (new Affiliate_WP_Shortcodes())->opt_in_form( $attributes );
		}

		/**
		 * Affiliate Referral URL block callback
		 *
		 * @since 1.0.0
		 */
		public function affiliate_referral_url_block_render_callback( $attributes ) {

			if ( 'default' === $attributes['pretty'] && true === affwp_is_pretty_referral_urls() ) {
				$attributes['pretty'] = 'yes';
			}

			if ( 'default' === $attributes['format'] && 'username' === affwp_get_referral_format() ) {
				$attributes['format'] = 'username';
			}

			$referral_url = (new Affiliate_WP_Shortcodes())->referral_url( $attributes );

			return '<p class="affiliate-referral-url">' . $referral_url . '</p>';
		}

		/**
		 * Affiliate Creatives block callback
		 *
		 * @since 1.0.0
		 */
		public function affiliate_creatives_block_render_callback( $attributes ) {
			$attributes['preview'] = true === $attributes['preview'] && isset( $attributes['preview'] ) ? 'yes' : 'no';

			return (new Affiliate_WP_Shortcodes())->affiliate_creatives( $attributes );
		}

		/**
		 * Affiliate Creative block callback
		 *
		 * @since 1.0.0
		 */
		public function affiliate_creative_block_render_callback( $attributes ) {
			return (new Affiliate_WP_Shortcodes())->affiliate_creative( $attributes );
		}

		/**
		 * Affiliate Conversion Script callback
		 *
		 * @since 1.0.0
		 */
		public function affiliate_conversion_script_block_render_callback( $attributes ) {
			return (new Affiliate_WP_Shortcodes())->conversion_script( $attributes );
		}

		/**
		 * Add new "AffiliateWP" category to the block editor.
		 */
		public function add_block_category( $categories, $post ) {
			$categories = array_merge(
				$categories,
				array(
					array(
						'slug'  => 'affiliatewp',
						'title' => __( 'AffiliateWP', 'affiliatewp-blocks' ),
					),
				)
			);

			return $categories;
		}

	}


	/**
	 * The main function responsible for returning the one true AffiliateWP_Blocks
	 * Instance to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $affiliatewp_blocks = affiliatewp_blocks(); ?>
	 *
	 * @since  1.0
	 *
	 * @return object The one true AffiliateWP_Blocks Instance
	 */
	function affiliatewp_blocks() {
		if ( ! class_exists( 'Affiliate_WP' ) ) {
			if ( ! class_exists( 'AffiliateWP_Activation' ) ) {
				require_once 'includes/class-activation.php';
			}

			$activation = new AffiliateWP_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
			$activation = $activation->run();
		} else {
			return AffiliateWP_Blocks::instance();
		}
	}
	add_action( 'plugins_loaded', 'affiliatewp_blocks', 100 );
}
