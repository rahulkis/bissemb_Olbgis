<?php
/*
Plugin Name: WPC Smart Wishlist for WooCommerce
Plugin URI: https://wpclever.net/
Description: WPC Smart Wishlist is a simple but powerful tool that can help your customer save products for buy later.
Version: 4.2.3
Author: WPClever
Author URI: https://wpclever.net
Text Domain: woo-smart-wishlist
Domain Path: /languages/
Requires at least: 4.0
Tested up to: 6.0
WC requires at least: 3.0
WC tested up to: 6.7
*/

defined( 'ABSPATH' ) || exit;

! defined( 'WOOSW_VERSION' ) && define( 'WOOSW_VERSION', '4.2.3' );
! defined( 'WOOSW_FILE' ) && define( 'WOOSW_FILE', __FILE__ );
! defined( 'WOOSW_URI' ) && define( 'WOOSW_URI', plugin_dir_url( __FILE__ ) );
! defined( 'WOOSW_DIR' ) && define( 'WOOSW_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'WOOSW_REVIEWS' ) && define( 'WOOSW_REVIEWS', 'https://wordpress.org/support/plugin/woo-smart-wishlist/reviews/?filter=5' );
! defined( 'WOOSW_CHANGELOG' ) && define( 'WOOSW_CHANGELOG', 'https://wordpress.org/plugins/woo-smart-wishlist/#developers' );
! defined( 'WOOSW_DISCUSSION' ) && define( 'WOOSW_DISCUSSION', 'https://wordpress.org/support/plugin/woo-smart-wishlist' );
! defined( 'WPC_URI' ) && define( 'WPC_URI', WOOSW_URI );

include 'includes/wpc-dashboard.php';
include 'includes/wpc-menu.php';
include 'includes/wpc-kit.php';

// plugin activate
register_activation_hook( __FILE__, 'woosw_plugin_activate' );

// plugin init
if ( ! function_exists( 'woosw_init' ) ) {
	add_action( 'plugins_loaded', 'woosw_init', 11 );

	function woosw_init() {
		// load text-domain
		load_plugin_textdomain( 'woo-smart-wishlist', false, basename( __DIR__ ) . '/languages/' );

		if ( ! function_exists( 'WC' ) || ! version_compare( WC()->version, '3.0', '>=' ) ) {
			add_action( 'admin_notices', 'woosw_notice_wc' );

			return;
		}

		if ( ! class_exists( 'WPCleverWoosw' ) ) {
			class WPCleverWoosw {
				protected static $added_products = array();
				protected static $localization = array();

				function __construct() {
					// add query var
					add_filter( 'query_vars', array( $this, 'query_vars' ), 1 );

					add_action( 'init', array( $this, 'init' ) );

					// menu
					add_action( 'admin_init', array( $this, 'register_settings' ) );
					add_action( 'admin_menu', array( $this, 'admin_menu' ) );

					// frontend scripts
					add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

					// backend scripts
					add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

					// quickview
					add_action( 'wp_ajax_wishlist_quickview', array( $this, 'wishlist_quickview' ) );

					// add
					add_action( 'wp_ajax_wishlist_add', array( $this, 'wishlist_add' ) );
					add_action( 'wp_ajax_nopriv_wishlist_add', array( $this, 'wishlist_add' ) );

					// add to wishlist
					add_action( 'template_redirect', array( $this, 'wishlist_add_by_link' ) );

					// added to cart
					if ( get_option( 'woosw_auto_remove', 'no' ) === 'yes' ) {
						add_action( 'woocommerce_add_to_cart', array( $this, 'add_to_cart' ), 10, 2 );
					}

					// remove
					add_action( 'wp_ajax_wishlist_remove', array( $this, 'wishlist_remove' ) );
					add_action( 'wp_ajax_nopriv_wishlist_remove', array( $this, 'wishlist_remove' ) );

					// empty
					add_action( 'wp_ajax_wishlist_empty', array( $this, 'wishlist_empty' ) );
					add_action( 'wp_ajax_nopriv_wishlist_empty', array( $this, 'wishlist_empty' ) );

					// load
					add_action( 'wp_ajax_wishlist_load', array( $this, 'wishlist_load' ) );
					add_action( 'wp_ajax_nopriv_wishlist_load', array( $this, 'wishlist_load' ) );

					// load count
					add_action( 'wp_ajax_wishlist_load_count', array( $this, 'wishlist_load_count' ) );
					add_action( 'wp_ajax_nopriv_wishlist_load_count', array( $this, 'wishlist_load_count' ) );

					// link
					add_filter( 'plugin_action_links', array( $this, 'action_links' ), 10, 2 );
					add_filter( 'plugin_row_meta', array( $this, 'row_meta' ), 10, 2 );

					// menu items
					add_filter( 'wp_nav_menu_items', array( $this, 'nav_menu_items' ), 99, 2 );

					// footer
					add_action( 'wp_footer', array( $this, 'wp_footer' ) );

					// product columns
					add_filter( 'manage_edit-product_columns', array( $this, 'product_columns' ), 10 );
					add_action( 'manage_product_posts_custom_column', array( $this, 'posts_custom_column' ), 10, 2 );
					add_filter( 'manage_edit-product_sortable_columns', array( $this, 'sortable_columns' ) );
					add_filter( 'request', array( $this, 'request' ) );

					// post states
					add_filter( 'display_post_states', array( $this, 'display_post_states' ), 10, 2 );

					// user login & logout
					add_action( 'wp_login', array( $this, 'wp_login' ), 10, 2 );
					add_action( 'wp_logout', array( $this, 'wp_logout' ), 10, 1 );

					// user columns
					add_filter( 'manage_users_columns', array( $this, 'users_columns' ) );
					add_filter( 'manage_users_custom_column', array( $this, 'users_columns_content' ), 10, 3 );

					// dropdown multiple
					add_filter( 'wp_dropdown_cats', array( $this, 'dropdown_cats_multiple' ), 10, 2 );

					// kses allowed html
					add_filter( 'wp_kses_allowed_html', array( $this, 'kses_allowed_html' ), 99, 2 );
				}

				function query_vars( $vars ) {
					$vars[] = 'woosw_id';

					return $vars;
				}

				function init() {
					// localization
					self::$localization = (array) get_option( 'woosw_localization' );

					// added products
					$key = isset( $_COOKIE['woosw_key'] ) ? sanitize_text_field( $_COOKIE['woosw_key'] ) : '#';

					if ( get_option( 'woosw_list_' . $key ) ) {
						self::$added_products = get_option( 'woosw_list_' . $key );
					}

					// rewrite
					if ( $page_id = self::get_page_id() ) {
						$page_slug = get_post_field( 'post_name', $page_id );

						if ( $page_slug !== '' ) {
							add_rewrite_rule( '^' . $page_slug . '/([\w]+)/?', 'index.php?page_id=' . $page_id . '&woosw_id=$matches[1]', 'top' );
						}
					}

					// shortcode
					add_shortcode( 'woosw', array( $this, 'shortcode' ) );
					add_shortcode( 'woosw_list', array( $this, 'list_shortcode' ) );

					// add button for archive
					$button_position_archive = apply_filters( 'woosw_button_position_archive', get_option( 'woosw_button_position_archive', apply_filters( 'woosw_button_position_archive_default', 'after_add_to_cart' ) ) );

					if ( ! empty( $button_position_archive ) ) {
						switch ( $button_position_archive ) {
							case 'after_title':
								add_action( 'woocommerce_shop_loop_item_title', array( $this, 'add_button' ), 11 );
								break;
							case 'after_rating':
								add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'add_button' ), 6 );
								break;
							case 'after_price':
								add_action( 'woocommerce_after_shop_loop_item_title', array(
									$this,
									'add_button'
								), 11 );
								break;
							case 'before_add_to_cart':
								add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_button' ), 9 );
								break;
							case 'after_add_to_cart':
								add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_button' ), 11 );
								break;
							default:
								add_action( 'woosw_button_position_archive_' . $button_position_archive, array(
									$this,
									'add_button'
								) );
						}
					}

					// add button for single
					$button_position_single = apply_filters( 'woosw_button_position_single', get_option( 'woosw_button_position_single', apply_filters( 'woosw_button_position_single_default', '31' ) ) );

					if ( ! empty( $button_position_single ) ) {
						if ( is_numeric( $button_position_single ) ) {
							add_action( 'woocommerce_single_product_summary', array(
								$this,
								'add_button'
							), (int) $button_position_single );
						} else {
							add_action( 'woosw_button_position_single_' . $button_position_single, array(
								$this,
								'add_button'
							) );
						}
					}
				}

				function localization( $key = '', $default = '' ) {
					$str = '';

					if ( ! empty( $key ) && ! empty( self::$localization[ $key ] ) ) {
						$str = self::$localization[ $key ];
					} elseif ( ! empty( $default ) ) {
						$str = $default;
					}

					return esc_html( apply_filters( 'woosw_localization_' . $key, $str ) );
				}

				function add_to_cart( $cart_item_key, $product_id ) {
					$key = self::get_key();

					if ( $key !== '#' ) {
						$products = array();

						if ( get_option( 'woosw_list_' . $key ) ) {
							$products = get_option( 'woosw_list_' . $key );
						}

						if ( array_key_exists( $product_id, $products ) ) {
							unset( $products[ $product_id ] );
							update_option( 'woosw_list_' . $key, $products );
							self::update_product_count( $product_id, 'remove' );
						}
					}
				}

				function wishlist_add_by_link() {
					if ( ! isset( $_REQUEST['add-to-wishlist'] ) && ! isset( $_REQUEST['add_to_wishlist'] ) ) {
						return false;
					}

					$key        = self::get_key();
					$product_id = absint( isset( $_REQUEST['add_to_wishlist'] ) ? (int) sanitize_text_field( $_REQUEST['add_to_wishlist'] ) : 0 );
					$product_id = absint( isset( $_REQUEST['add-to-wishlist'] ) ? (int) sanitize_text_field( $_REQUEST['add-to-wishlist'] ) : $product_id );

					if ( $product_id ) {
						if ( $key !== '#' && $key !== 'WOOSW' ) {
							$products = array();

							if ( get_option( 'woosw_list_' . $key ) ) {
								$products = get_option( 'woosw_list_' . $key );
							}

							if ( ! array_key_exists( $product_id, $products ) ) {
								// insert if not exists
								$products = array(
									            $product_id => array(
										            'time' => time(),
										            'note' => ''
									            )
								            ) + $products;
								update_option( 'woosw_list_' . $key, $products );
							}
						}
					}

					// redirect to wishlist page
					wp_safe_redirect( self::get_url( $key, true ) );
				}

				function wishlist_add() {
					$return = array( 'status' => 0 );
					$key    = self::get_key();

					if ( ( $product_id = (int) sanitize_text_field( $_POST['product_id'] ) ) > 0 ) {
						if ( $key === '#' ) {
							$return['status']  = 0;
							$return['notice']  = self::localization( 'login_message', esc_html__( 'Please log in to use the Wishlist!', 'woo-smart-wishlist' ) );
							$return['content'] = self::wishlist_content( $key, self::localization( 'empty_message', esc_html__( 'There are no products on the Wishlist!', 'woo-smart-wishlist' ) ) );
						} else {
							$products = array();

							if ( get_option( 'woosw_list_' . $key ) ) {
								$products = get_option( 'woosw_list_' . $key );
							}

							if ( ! array_key_exists( $product_id, $products ) ) {
								// insert if not exists
								$products = array(
									            $product_id => array(
										            'time' => time(),
										            'note' => ''
									            )
								            ) + $products;
								update_option( 'woosw_list_' . $key, $products );
								self::update_product_count( $product_id, 'add' );
								$return['notice'] = self::localization( 'added_message', esc_html__( '{name} has been added to Wishlist.', 'woo-smart-wishlist' ) );
							} else {
								$return['notice'] = self::localization( 'already_message', esc_html__( '{name} is already in the Wishlist.', 'woo-smart-wishlist' ) );
							}

							$return['status'] = 1;
							$return['count']  = count( $products );

							if ( get_option( 'woosw_button_action', 'list' ) === 'list' ) {
								$return['content'] = self::wishlist_content( $key );
							}
						}
					} else {
						$product_id       = 0;
						$return['status'] = 0;
						$return['notice'] = self::localization( 'error_message', esc_html__( 'Have an error, please try again!', 'woo-smart-wishlist' ) );
					}

					do_action( 'woosw_add', $product_id, $key );

					wp_send_json( $return );
					wp_die();
				}

				function wishlist_remove() {
					$return = array( 'status' => 0 );
					$key    = sanitize_text_field( $_POST['key'] );

					if ( empty( $key ) ) {
						$key = self::get_key();
					}

					if ( ( $product_id = (int) sanitize_text_field( $_POST['product_id'] ) ) > 0 ) {
						if ( $key === '#' ) {
							$return['notice'] = self::localization( 'login_message', esc_html__( 'Please log in to use the Wishlist!', 'woo-smart-wishlist' ) );
						} else {
							$products = array();

							if ( get_option( 'woosw_list_' . $key ) ) {
								$products = get_option( 'woosw_list_' . $key );
							}

							if ( array_key_exists( $product_id, $products ) ) {
								unset( $products[ $product_id ] );
								update_option( 'woosw_list_' . $key, $products );
								self::update_product_count( $product_id, 'remove' );
								$return['count']  = count( $products );
								$return['status'] = 1;
								$return['notice'] = self::localization( 'removed_message', esc_html__( 'Product has been removed from the Wishlist.', 'woo-smart-wishlist' ) );

								if ( empty( $products ) ) {
									$return['content'] = self::wishlist_content( $key, self::localization( 'empty_message', esc_html__( 'There are no products on the Wishlist!', 'woo-smart-wishlist' ) ) ) . '</div>';
								}
							} else {
								$return['notice'] = self::localization( 'not_exist_message', esc_html__( 'The product does not exist on the Wishlist!', 'woo-smart-wishlist' ) );
							}
						}
					} else {
						$product_id       = 0;
						$return['notice'] = self::localization( 'error_message', esc_html__( 'Have an error, please try again!', 'woo-smart-wishlist' ) );
					}

					do_action( 'woosw_remove', $product_id, $key );

					wp_send_json( $return );
					wp_die();
				}

				function wishlist_empty() {
					$return = array( 'status' => 0 );
					$key    = sanitize_text_field( $_POST['key'] );

					if ( empty( $key ) ) {
						$key = self::get_key();
					}

					if ( $key === '#' ) {
						$return['notice'] = self::localization( 'login_message', esc_html__( 'Please log in to use the Wishlist!', 'woo-smart-wishlist' ) );
					} else {
						if ( get_option( 'woosw_list_' . $key ) ) {
							$products = get_option( 'woosw_list_' . $key );

							if ( ! empty( $products ) ) {
								foreach ( array_keys( $products ) as $product_id ) {
									// update count
									self::update_product_count( $product_id, 'remove' );
								}
							}
						}

						// remove option
						update_option( 'woosw_list_' . $key, array() );
						$return['status']  = 1;
						$return['count']   = 0;
						$return['notice']  = self::localization( 'empty_notice', esc_html__( 'All products have been removed from the Wishlist!', 'woo-smart-wishlist' ) );
						$return['content'] = self::wishlist_content( $key, self::localization( 'empty_message', esc_html__( 'There are no products on the Wishlist!', 'woo-smart-wishlist' ) ) );
					}

					do_action( 'woosw_empty', $key );

					wp_send_json( $return );
					wp_die();
				}

				function wishlist_load() {
					$return = array( 'status' => 0 );
					$key    = self::get_key();

					if ( $key === '#' ) {
						$return['notice']  = self::localization( 'login_message', esc_html__( 'Please log in to use Wishlist!', 'woo-smart-wishlist' ) );
						$return['content'] = self::wishlist_content( $key, self::localization( 'empty_message', esc_html__( 'There are no products on the Wishlist!', 'woo-smart-wishlist' ) ) );
					} else {
						$products = array();

						if ( get_option( 'woosw_list_' . $key ) ) {
							$products = get_option( 'woosw_list_' . $key );
						}

						$return['status']  = 1;
						$return['count']   = count( $products );
						$return['content'] = self::wishlist_content( $key );
					}

					do_action( 'woosw_load', $key );

					wp_send_json( $return );
					wp_die();
				}

				function wishlist_load_count() {
					$return = array( 'status' => 0, 'count' => 0 );
					$key    = self::get_key();

					if ( $key === '#' ) {
						$return['notice'] = self::localization( 'login_message', esc_html__( 'Please log in to use Wishlist!', 'woo-smart-wishlist' ) );
					} else {
						$products = array();

						if ( get_option( 'woosw_list_' . $key ) ) {
							$products = get_option( 'woosw_list_' . $key );
						}

						$return['status'] = 1;
						$return['count']  = count( $products );
					}

					do_action( 'wishlist_load_count', $key );

					wp_send_json( $return );
					wp_die();
				}

				function add_note() {
					$note       = trim( isset( $_POST['note'] ) ? sanitize_text_field( $_POST['note'] ) : '' );
					$key        = isset( $_POST['woosw_key'] ) ? sanitize_text_field( $_POST['woosw_key'] ) : '';
					$product_id = isset( $_POST['product_id'] ) ? (int) sanitize_text_field( $_POST['product_id'] ) : 0;
					$products   = get_option( 'woosw_list_' . $key );

					if ( isset( $products[ $product_id ] ) ) {
						if ( is_array( $products[ $product_id ] ) ) {
							$products[ $product_id ]['note'] = $note;
						} else {
							// old version
							$time                    = $products[ $product_id ];
							$products[ $product_id ] = array(
								'time' => $time,
								'note' => $note
							);
						}

						update_option( 'woosw_list_' . $key, $products );
					}

					if ( empty( $note ) ) {
						echo self::localization( 'add_note', esc_html__( 'Add note', 'woo-smart-wishlist' ) );
					} else {
						echo nl2br( $note );
					}

					wp_die();
				}

				function manage_wishlists() {
					ob_start();
					self::manage_content();
					echo ob_get_clean();
					wp_die();
				}

				function add_wishlist() {
					$name = trim( isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '' );

					if ( $user_id = get_current_user_id() ) {
						$key  = self::get_key( true );
						$keys = get_user_meta( $user_id, 'woosw_keys', true ) ?: array();
						$max  = get_option( 'woosw_maximum_wishlists', '5' );

						if ( is_array( $keys ) && ( count( $keys ) < (int) $max ) ) {
							$keys[ $key ] = array(
								'name' => $name,
								'time' => time()
							);

							update_user_meta( $user_id, 'woosw_keys', $keys );
						}

						ob_start();
						self::manage_content();
						echo ob_get_clean();
					}

					wp_die();
				}

				function delete_wishlist() {
					$key = trim( isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : '' );

					if ( ! empty( $key ) && ( $user_id = get_current_user_id() ) ) {
						// delete key from user
						$keys = get_user_meta( $user_id, 'woosw_keys', true ) ?: array();

						if ( is_array( $keys ) && ( count( $keys ) > 1 ) ) {
							// don't remove primary key
							unset( $keys[ $key ] );
							update_user_meta( $user_id, 'woosw_keys', $keys );

							// delete wishlist
							delete_option( 'woosw_list_' . $key );
						}

						ob_start();
						self::manage_content();
						echo ob_get_clean();
					}

					wp_die();
				}

				function view_wishlist() {
					$key = trim( isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : '' );

					if ( ! empty( $key ) ) {
						echo self::wishlist_content( $key );
					}

					wp_die();
				}

				function set_default() {
					$return = [];
					$key    = trim( isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : '' );

					if ( $products = get_option( 'woosw_list_' . $key ) ) {
						$count = count( $products );
					} else {
						$products = array();
						$count    = 0;
					}

					if ( ! empty( $key ) && ( $user_id = get_current_user_id() ) ) {
						update_user_meta( $user_id, 'woosw_key', $key );

						// set cookie
						$secure   = apply_filters( 'woosw_cookie_secure', wc_site_is_https() && is_ssl() );
						$httponly = apply_filters( 'woosw_cookie_httponly', true );

						wc_setcookie( 'woosw_key', $key, time() + 604800, $secure, $httponly );

						ob_start();
						self::manage_content();
						$return['content']  = ob_get_clean();
						$return['count']    = $count;
						$return['products'] = array_keys( $products );
					}

					wp_send_json( $return );
					wp_die();
				}

				function add_button() {
					echo do_shortcode( '[woosw]' );
				}

				function shortcode( $attrs ) {
					$output = $product_name = $product_image = '';

					$attrs = shortcode_atts( array(
						'id'   => null,
						'type' => get_option( 'woosw_button_type', 'button' )
					), $attrs, 'woosw' );

					if ( ! $attrs['id'] ) {
						global $product;

						if ( $product ) {
							$attrs['id']      = $product->get_id();
							$product_name     = $product->get_name();
							$product_image_id = $product->get_image_id();
							$product_image    = wp_get_attachment_image_url( $product_image_id );
						}
					} else {
						if ( $_product = wc_get_product( $attrs['id'] ) ) {
							$product_name     = $_product->get_name();
							$product_image_id = $_product->get_image_id();
							$product_image    = wp_get_attachment_image_url( $product_image_id );
						}
					}

					if ( $attrs['id'] ) {
						// check cats
						$selected_cats = get_option( 'woosw_cats', array() );

						if ( ! empty( $selected_cats ) && ( $selected_cats[0] !== '0' ) ) {
							if ( ! has_term( $selected_cats, 'product_cat', $attrs['id'] ) ) {
								return '';
							}
						}

						$class = 'woosw-btn woosw-btn-' . esc_attr( $attrs['id'] );

						if ( array_key_exists( $attrs['id'], self::$added_products ) ) {
							$class .= ' woosw-added';
							$text  = apply_filters( 'woosw_button_text_added', self::localization( 'button_added', esc_html__( 'Browse wishlist', 'woo-smart-wishlist' ) ) );
						} else {
							$text = apply_filters( 'woosw_button_text', self::localization( 'button', esc_html__( 'Add to wishlist', 'woo-smart-wishlist' ) ) );
						}

						if ( get_option( 'woosw_button_class', '' ) !== '' ) {
							$class .= ' ' . esc_attr( get_option( 'woosw_button_class' ) );
						}

						if ( $attrs['type'] === 'link' ) {
							$output = '<a href="' . esc_url( '?add-to-wishlist=' . $attrs['id'] ) . '" class="' . esc_attr( $class ) . '" data-id="' . esc_attr( $attrs['id'] ) . '" data-product_name="' . esc_attr( $product_name ) . '" data-product_image="' . esc_attr( $product_image ) . '">' . esc_html( $text ) . '</a>';
						} else {
							$output = '<button class="' . esc_attr( $class ) . '" data-id="' . esc_attr( $attrs['id'] ) . '" data-product_name="' . esc_attr( $product_name ) . '" data-product_image="' . esc_attr( $product_image ) . '">' . esc_html( $text ) . '</button>';
						}
					}

					return apply_filters( 'woosw_button_html', $output, $attrs['id'], $attrs );
				}

				function list_shortcode() {
					if ( get_query_var( 'woosw_id' ) ) {
						$key = get_query_var( 'woosw_id' );
					} elseif ( isset( $_REQUEST['wid'] ) && ! empty( $_REQUEST['wid'] ) ) {
						$key = sanitize_text_field( $_REQUEST['wid'] );
					} else {
						$key = self::get_key();
					}

					$share_url_raw = self::get_url( $key, true );
					$share_url     = urlencode( $share_url_raw );
					$return_html   = '<div class="woosw-list">';
					$return_html   .= self::get_items( $key, 'table' );
					$return_html   .= '<div class="woosw-actions">';

					if ( get_option( 'woosw_page_share', 'yes' ) === 'yes' ) {
						$facebook  = esc_html__( 'Facebook', 'woo-smart-wishlist' );
						$twitter   = esc_html__( 'Twitter', 'woo-smart-wishlist' );
						$pinterest = esc_html__( 'Pinterest', 'woo-smart-wishlist' );
						$mail      = esc_html__( 'Mail', 'woo-smart-wishlist' );

						if ( get_option( 'woosw_page_icon', 'yes' ) === 'yes' ) {
							$facebook = $twitter = $pinterest = $mail = "<i class='woosw-icon'></i>";
						}

						$share_items = get_option( 'woosw_page_items' );

						if ( ! empty( $share_items ) ) {
							$return_html .= '<div class="woosw-share">';
							$return_html .= '<span class="woosw-share-label">' . esc_html__( 'Share on:', 'woo-smart-wishlist' ) . '</span>';
							$return_html .= ( in_array( 'facebook', $share_items ) ) ? '<a class="woosw-share-facebook" href="https://www.facebook.com/sharer.php?u=' . $share_url . '" target="_blank">' . $facebook . '</a>' : '';
							$return_html .= ( in_array( 'twitter', $share_items ) ) ? '<a class="woosw-share-twitter" href="https://twitter.com/share?url=' . $share_url . '" target="_blank">' . $twitter . '</a>' : '';
							$return_html .= ( in_array( 'pinterest', $share_items ) ) ? '<a class="woosw-share-pinterest" href="https://pinterest.com/pin/create/button/?url=' . $share_url . '" target="_blank">' . $pinterest . '</a>' : '';
							$return_html .= ( in_array( 'mail', $share_items ) ) ? '<a class="woosw-share-mail" href="mailto:?body=' . $share_url . '" target="_blank">' . $mail . '</a>' : '';
							$return_html .= '</div><!-- /woosw-share -->';
						}
					}

					if ( get_option( 'woosw_page_copy', 'yes' ) === 'yes' ) {
						$return_html .= '<div class="woosw-copy">';
						$return_html .= '<span class="woosw-copy-label">' . esc_html__( 'Wishlist link:', 'woo-smart-wishlist' ) . '</span>';
						$return_html .= '<span class="woosw-copy-url"><input id="woosw_copy_url" type="url" value="' . esc_attr( $share_url_raw ) . '" readonly/></span>';
						$return_html .= '<span class="woosw-copy-btn"><input id="woosw_copy_btn" type="button" value="' . esc_html__( 'Copy', 'woo-smart-wishlist' ) . '"/></span>';
						$return_html .= '</div><!-- /woosw-copy -->';
					}

					$return_html .= '</div><!-- /woosw-actions -->';
					$return_html .= '</div><!-- /woosw-list -->';

					return $return_html;
				}

				function register_settings() {
					// settings
					register_setting( 'woosw_settings', 'woosw_disable_unauthenticated' );
					register_setting( 'woosw_settings', 'woosw_auto_remove' );
					register_setting( 'woosw_settings', 'woosw_enable_multiple' );
					register_setting( 'woosw_settings', 'woosw_maximum_wishlists' );
					register_setting( 'woosw_settings', 'woosw_link' );
					register_setting( 'woosw_settings', 'woosw_show_note' );
					register_setting( 'woosw_settings', 'woosw_page_id' );
					register_setting( 'woosw_settings', 'woosw_page_share' );
					register_setting( 'woosw_settings', 'woosw_page_icon' );
					register_setting( 'woosw_settings', 'woosw_page_items' );
					register_setting( 'woosw_settings', 'woosw_page_copy' );
					register_setting( 'woosw_settings', 'woosw_button_type' );
					register_setting( 'woosw_settings', 'woosw_button_text' );
					register_setting( 'woosw_settings', 'woosw_button_action' );
					register_setting( 'woosw_settings', 'woosw_message_position' );
					register_setting( 'woosw_settings', 'woosw_button_text_added' );
					register_setting( 'woosw_settings', 'woosw_button_action_added' );
					register_setting( 'woosw_settings', 'woosw_button_class' );
					register_setting( 'woosw_settings', 'woosw_button_position_archive' );
					register_setting( 'woosw_settings', 'woosw_button_position_single' );
					register_setting( 'woosw_settings', 'woosw_cats' );
					register_setting( 'woosw_settings', 'woosw_popup_position' );
					register_setting( 'woosw_settings', 'woosw_perfect_scrollbar' );
					register_setting( 'woosw_settings', 'woosw_color' );
					register_setting( 'woosw_settings', 'woosw_empty_button' );
					register_setting( 'woosw_settings', 'woosw_continue_url' );
					register_setting( 'woosw_settings', 'woosw_menus' );
					register_setting( 'woosw_settings', 'woosw_menu_action' );

					// localization
					register_setting( 'woosw_localization', 'woosw_localization' );
				}

				function admin_menu() {
					add_submenu_page( 'wpclever', 'WPC Smart Wishlist', 'Smart Wishlist', 'manage_options', 'wpclever-woosw', array(
						$this,
						'admin_menu_content'
					) );
				}

				function admin_menu_content() {
					add_thickbox();
					$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings';
					?>
                    <div class="wpclever_settings_page wrap">
                        <h1 class="wpclever_settings_page_title"><?php echo 'WPC Smart Wishlist ' . WOOSW_VERSION; ?></h1>
                        <div class="wpclever_settings_page_desc about-text">
                            <p>
								<?php printf( esc_html__( 'Thank you for using our plugin! If you are satisfied, please reward it a full five-star %s rating.', 'woo-smart-wishlist' ), '<span style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span>' ); ?>
                                <br/>
                                <a href="<?php echo esc_url( WOOSW_REVIEWS ); ?>"
                                   target="_blank"><?php esc_html_e( 'Reviews', 'woo-smart-wishlist' ); ?></a> | <a
                                        href="<?php echo esc_url( WOOSW_CHANGELOG ); ?>"
                                        target="_blank"><?php esc_html_e( 'Changelog', 'woo-smart-wishlist' ); ?></a>
                                | <a href="<?php echo esc_url( WOOSW_DISCUSSION ); ?>"
                                     target="_blank"><?php esc_html_e( 'Discussion', 'woo-smart-wishlist' ); ?></a>
                            </p>
                        </div>
						<?php if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) { ?>
                            <div class="notice notice-success is-dismissible">
                                <p><?php esc_html_e( 'Settings updated.', 'woo-smart-wishlist' ); ?></p>
                            </div>
						<?php } ?>
                        <div class="wpclever_settings_page_nav">
                            <h2 class="nav-tab-wrapper">
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woosw&tab=settings' ); ?>"
                                   class="<?php echo esc_attr( $active_tab === 'settings' ? 'nav-tab nav-tab-active' : 'nav-tab' ); ?>">
									<?php esc_html_e( 'Settings', 'woo-smart-wishlist' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woosw&tab=localization' ); ?>"
                                   class="<?php echo esc_attr( $active_tab === 'localization' ? 'nav-tab nav-tab-active' : 'nav-tab' ); ?>">
									<?php esc_html_e( 'Localization', 'woo-smart-wishlist' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woosw&tab=premium' ); ?>"
                                   class="<?php echo esc_attr( $active_tab === 'premium' ? 'nav-tab nav-tab-active' : 'nav-tab' ); ?>"
                                   style="color: #c9356e">
									<?php esc_html_e( 'Premium Version', 'woo-smart-wishlist' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-kit' ); ?>" class="nav-tab">
									<?php esc_html_e( 'Essential Kit', 'woo-smart-wishlist' ); ?>
                                </a>
                            </h2>
                        </div>
                        <div class="wpclever_settings_page_content">
							<?php if ( $active_tab === 'settings' ) {
								if ( isset( $_REQUEST['settings-updated'] ) && ( sanitize_text_field( $_REQUEST['settings-updated'] ) === 'true' ) ) {
									flush_rewrite_rules();
								}
								?>
                                <form method="post" action="options.php">
                                    <table class="form-table">
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'General', 'woo-smart-wishlist' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Disable the wishlist for unauthenticated users', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_disable_unauthenticated">
                                                    <option value="yes" <?php echo esc_attr( get_option( 'woosw_disable_unauthenticated', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( 'woosw_disable_unauthenticated', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Auto remove', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_auto_remove">
                                                    <option value="yes" <?php echo esc_attr( get_option( 'woosw_auto_remove', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( 'woosw_auto_remove', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Auto remove product from the wishlist after adding to the cart.', 'woo-smart-wishlist' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th>
												<?php esc_html_e( 'Multiple Wishlist', 'woo-smart-wishlist' ); ?>
                                            </th>
                                            <td>
                                                <span class="description" style="color: #c9356e">This feature is only available on the Premium Version. Click <a
                                                            href="https://wpclever.net/downloads/smart-wishlist?utm_source=pro&utm_medium=woosw&utm_campaign=wporg"
                                                            target="_blank">here</a> to buy, just $29.</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Enable', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_enable_multiple">
                                                    <option value="yes" <?php echo esc_attr( get_option( 'woosw_enable_multiple', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( 'woosw_enable_multiple', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Enable/disable multiple wishlist.', 'woo-smart-wishlist' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Maximum wishlists per user', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="number" min="1" max="100" name="woosw_maximum_wishlists"
                                                       value="<?php echo esc_attr( get_option( 'woosw_maximum_wishlists', '5' ) ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th>
												<?php esc_html_e( 'Button', 'woo-smart-wishlist' ); ?>
                                            </th>
                                            <td>
												<?php esc_html_e( 'Settings for "Add to wishlist" button.', 'woo-smart-wishlist' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Type', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_button_type">
                                                    <option value="button" <?php echo esc_attr( get_option( 'woosw_button_type', 'button' ) === 'button' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Button', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="link" <?php echo esc_attr( get_option( 'woosw_button_type', 'button' ) === 'link' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Link', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Action', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_button_action">
                                                    <option value="message" <?php echo esc_attr( get_option( 'woosw_button_action', 'list' ) === 'message' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Show message', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="list" <?php echo esc_attr( get_option( 'woosw_button_action', 'list' ) === 'list' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Open wishlist popup', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( 'woosw_button_action', 'list' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Add to wishlist solely', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Action triggered by clicking on the wishlist button.', 'woo-smart-wishlist' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="woosw_button_action_hide woosw_button_action_message">
                                            <th scope="row"><?php esc_html_e( 'Message position', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_message_position">
                                                    <option value="right-top" <?php echo esc_attr( get_option( 'woosw_message_position', 'right-top' ) === 'right-top' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'right-top', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="right-bottom" <?php echo esc_attr( get_option( 'woosw_message_position', 'right-top' ) === 'right-bottom' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'right-bottom', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="fluid-top" <?php echo esc_attr( get_option( 'woosw_message_position', 'right-top' ) === 'fluid-top' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'center-top', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="fluid-bottom" <?php echo esc_attr( get_option( 'woosw_message_position', 'right-top' ) === 'fluid-bottom' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'center-bottom', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="left-top" <?php echo esc_attr( get_option( 'woosw_message_position', 'right-top' ) === 'left-top' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'left-top', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="left-bottom" <?php echo esc_attr( get_option( 'woosw_message_position', 'right-top' ) === 'left-bottom' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'left-bottom', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Action (added)', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_button_action_added">
                                                    <option value="popup" <?php echo esc_attr( get_option( 'woosw_button_action_added', 'popup' ) === 'popup' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Open wishlist popup', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="page" <?php echo esc_attr( get_option( 'woosw_button_action_added', 'popup' ) === 'page' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Open wishlist page', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Action triggered by clicking on the wishlist button after adding an item to the wishlist.', 'woo-smart-wishlist' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Extra class (optional)', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" name="woosw_button_class" class="regular-text"
                                                       value="<?php echo esc_attr( get_option( 'woosw_button_class', '' ) ); ?>"/>
                                                <span class="description"><?php esc_html_e( 'Add extra class for action button/link, split by one space.', 'woo-smart-wishlist' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Position on archive page', 'woo-smart-wishlist' ); ?></th>
                                            <td>
												<?php
												$position_archive  = apply_filters( 'woosw_button_position_archive', 'default' );
												$positions_archive = apply_filters( 'woosw_button_positions_archive', array(
													'after_title'        => esc_html__( 'Under title', 'woo-smart-wishlist' ),
													'after_rating'       => esc_html__( 'Under rating', 'woo-smart-wishlist' ),
													'after_price'        => esc_html__( 'Under price', 'woo-smart-wishlist' ),
													'before_add_to_cart' => esc_html__( 'Above add to cart button', 'woo-smart-wishlist' ),
													'after_add_to_cart'  => esc_html__( 'Under add to cart button', 'woo-smart-wishlist' ),
													'0'                  => esc_html__( 'None (hide it)', 'woo-smart-wishlist' ),
												) );
												?>
                                                <select name="woosw_button_position_archive" <?php echo( $position_archive !== 'default' ? 'disabled' : '' ); ?>>
													<?php
													if ( $position_archive === 'default' ) {
														$position_archive = get_option( 'woosw_button_position_archive', apply_filters( 'woosw_button_position_archive_default', 'after_add_to_cart' ) );
													}

													foreach ( $positions_archive as $k => $p ) {
														echo '<option value="' . esc_attr( $k ) . '" ' . ( ( $k === $position_archive ) || ( empty( $position_archive ) && empty( $k ) ) ? 'selected' : '' ) . '>' . esc_html( $p ) . '</option>';
													}
													?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Position on single page', 'woo-smart-wishlist' ); ?></th>
                                            <td>
												<?php
												$position_single  = apply_filters( 'woosw_button_position_single', 'default' );
												$positions_single = apply_filters( 'woosw_button_positions_single', array(
													'6'  => esc_html__( 'Under title', 'woo-smart-wishlist' ),
													'11' => esc_html__( 'Under rating', 'woo-smart-wishlist' ),
													'21' => esc_html__( 'Under excerpt', 'woo-smart-wishlist' ),
													'29' => esc_html__( 'Above add to cart button', 'woo-smart-wishlist' ),
													'31' => esc_html__( 'Under add to cart button', 'woo-smart-wishlist' ),
													'41' => esc_html__( 'Under meta', 'woo-smart-wishlist' ),
													'51' => esc_html__( 'Under sharing', 'woo-smart-wishlist' ),
													'0'  => esc_html__( 'None (hide it)', 'woo-smart-wishlist' ),
												) );
												?>
                                                <select name="woosw_button_position_single" <?php echo( $position_single !== 'default' ? 'disabled' : '' ); ?>>
													<?php
													if ( $position_single === 'default' ) {
														$position_single = get_option( 'woosw_button_position_single', apply_filters( 'woosw_button_position_single_default', '31' ) );
													}

													foreach ( $positions_single as $k => $p ) {
														echo '<option value="' . esc_attr( $k ) . '" ' . ( ( strval( $k ) === strval( $position_single ) ) || ( $k === $position_single ) || ( empty( $position_single ) && empty( $k ) ) ? 'selected' : '' ) . '>' . esc_html( $p ) . '</option>';
													}
													?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Shortcode', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <span class="description">
                                                    <?php printf( esc_html__( 'You can add a button manually by using the shortcode %s, eg. %s for the product whose ID is 99.', 'woo-smart-wishlist' ), '<code>[woosw id="{product id}"]</code>', '<code>[woosw id="99"]</code>' ); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Categories', 'woo-smart-wishlist' ); ?></th>
                                            <td>
												<?php
												$selected_cats = get_option( 'woosw_cats' );

												if ( empty( $selected_cats ) ) {
													$selected_cats = array( 0 );
												}

												wc_product_dropdown_categories(
													array(
														'name'             => 'woosw_cats',
														'hide_empty'       => 0,
														'value_field'      => 'id',
														'multiple'         => true,
														'show_option_all'  => esc_html__( 'All categories', 'woo-smart-wishlist' ),
														'show_option_none' => '',
														'selected'         => implode( ',', $selected_cats )
													) );
												?>
                                                <span class="description"><?php esc_html_e( 'Only show the wishlist button for products in selected categories.', 'woo-smart-wishlist' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th>
												<?php esc_html_e( 'Popup', 'woo-smart-wishlist' ); ?>
                                            </th>
                                            <td>
												<?php esc_html_e( 'Settings for the wishlist popup.', 'woo-smart-wishlist' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Position', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_popup_position">
                                                    <option value="center" <?php echo esc_attr( get_option( 'woosw_popup_position', 'center' ) === 'center' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Center', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="right" <?php echo esc_attr( get_option( 'woosw_popup_position', 'center' ) === 'right' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Right', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="left" <?php echo esc_attr( get_option( 'woosw_popup_position', 'center' ) === 'left' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Left', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Use perfect-scrollbar', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_perfect_scrollbar">
                                                    <option value="yes" <?php echo esc_attr( get_option( 'woosw_perfect_scrollbar', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( 'woosw_perfect_scrollbar', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php printf( esc_html__( 'Read more about %s', 'woo-smart-wishlist' ), '<a href="https://github.com/mdbootstrap/perfect-scrollbar" target="_blank">perfect-scrollbar</a>' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Color', 'woo-smart-wishlist' ); ?></th>
                                            <td>
												<?php $color_default = apply_filters( 'woosw_color_default', '#5fbd74' ); ?>
                                                <input type="text" name="woosw_color"
                                                       value="<?php echo esc_attr( get_option( 'woosw_color', $color_default ) ); ?>"
                                                       class="woosw_color_picker"/>
                                                <span class="description"><?php printf( esc_html__( 'Choose the color, default %s', 'woo-smart-wishlist' ), '<code>' . $color_default . '</code>' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Link to individual product', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_link">
                                                    <option value="yes" <?php echo esc_attr( get_option( 'woosw_link', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open in the same tab', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="yes_blank" <?php echo esc_attr( get_option( 'woosw_link', 'yes' ) === 'yes_blank' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open in the new tab', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="yes_popup" <?php echo esc_attr( get_option( 'woosw_link', 'yes' ) === 'yes_popup' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open quick view popup', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( 'woosw_link', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">If you choose "Open quick view popup", please install <a
                                                            href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=woo-smart-quick-view&TB_iframe=true&width=800&height=550' ) ); ?>"
                                                            class="thickbox" title="Install WPC Smart Quick View">WPC Smart Quick View</a> to make it work.</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Show note', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_show_note">
                                                    <option value="yes" <?php echo esc_attr( get_option( 'woosw_show_note', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( 'woosw_show_note', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Show note on each product for all visitors. Only wishlist owner can add/edit these notes.', 'woo-smart-wishlist' ); ?></span>
                                                <p class="description" style="color: #c9356e">
                                                    This feature is only available on the Premium Version. Click <a
                                                            href="https://wpclever.net/downloads/smart-wishlist?utm_source=pro&utm_medium=woosw&utm_campaign=wporg"
                                                            target="_blank">here</a> to buy, just $29.
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Empty wishlist button', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_empty_button">
                                                    <option value="yes" <?php echo esc_attr( get_option( 'woosw_empty_button', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( 'woosw_empty_button', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Show empty wishlist button on the popup?', 'woo-smart-wishlist' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Continue shopping link', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="url" name="woosw_continue_url"
                                                       value="<?php echo esc_attr( get_option( 'woosw_continue_url' ) ); ?>"
                                                       class="regular-text code"/>
                                                <span class="description"><?php esc_html_e( 'By default, the wishlist popup will only be closed when customers click on the "Continue Shopping" button.', 'woo-smart-wishlist' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th>
												<?php esc_html_e( 'Page', 'woo-smart-wishlist' ); ?>
                                            </th>
                                            <td>
												<?php esc_html_e( 'Settings for wishlist page.', 'woo-smart-wishlist' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Wishlist page', 'woo-smart-wishlist' ); ?></th>
                                            <td>
												<?php wp_dropdown_pages( array(
													'selected'          => get_option( 'woosw_page_id', '' ),
													'name'              => 'woosw_page_id',
													'show_option_none'  => esc_html__( 'Choose a page', 'woo-smart-wishlist' ),
													'option_none_value' => '',
												) ); ?>
                                                <span class="description"><?php printf( esc_html__( 'Add shortcode %s to display the wishlist on a page.', 'woo-smart-wishlist' ), '<code>[woosw_list]</code>' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Share buttons', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_page_share">
                                                    <option value="yes" <?php echo esc_attr( get_option( 'woosw_page_share', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( 'woosw_page_share', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Enable share buttons on the wishlist page?', 'woo-smart-wishlist' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Use font icon', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_page_icon">
                                                    <option value="yes" <?php echo esc_attr( get_option( 'woosw_page_icon', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( 'woosw_page_icon', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Social links', 'woo-smart-wishlist' ); ?></th>
                                            <td>
												<?php
												$share_items = get_option( 'woosw_page_items' );

												if ( empty( $share_items ) ) {
													$share_items = array();
												}
												?>
                                                <select multiple name="woosw_page_items[]" id='woosw_page_items'>
                                                    <option value="facebook" <?php echo esc_attr( in_array( 'facebook', $share_items ) ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Facebook', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="twitter" <?php echo esc_attr( in_array( 'twitter', $share_items ) ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Twitter', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="pinterest" <?php echo esc_attr( in_array( 'pinterest', $share_items ) ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Pinterest', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="mail" <?php echo esc_attr( in_array( 'mail', $share_items ) ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Mail', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Copy link', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_page_copy">
                                                    <option value="yes" <?php echo esc_attr( get_option( 'woosw_page_copy', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( 'woosw_page_copy', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Enable copy wishlist link to share?', 'woo-smart-wishlist' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th>
												<?php esc_html_e( 'Menu', 'woo-smart-wishlist' ); ?>
                                            </th>
                                            <td>
												<?php esc_html_e( 'Settings for the wishlist menu item.', 'woo-smart-wishlist' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Menu(s)', 'woo-smart-wishlist' ); ?></th>
                                            <td>
												<?php
												$nav_args    = array(
													'hide_empty' => false,
													'fields'     => 'id=>name',
												);
												$nav_menus   = get_terms( 'nav_menu', $nav_args );
												$saved_menus = get_option( 'woosw_menus', array() );

												foreach ( $nav_menus as $nav_id => $nav_name ) {
													echo '<input type="checkbox" name="woosw_menus[]" value="' . esc_attr( $nav_id ) . '" ' . ( is_array( $saved_menus ) && in_array( $nav_id, $saved_menus, false ) ? 'checked' : '' ) . '/><label>' . esc_html( $nav_name ) . '</label><br/>';
												}
												?>
                                                <span class="description"><?php esc_html_e( 'Choose the menu(s) you want to add the "wishlist menu" at the end.', 'woo-smart-wishlist' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Action', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <select name="woosw_menu_action">
                                                    <option value="open_page" <?php echo esc_attr( get_option( 'woosw_menu_action', 'open_page' ) === 'open_page' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Open wishlist page', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                    <option value="open_popup" <?php echo esc_attr( get_option( 'woosw_menu_action', 'open_page' ) === 'open_popup' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Open wishlist popup', 'woo-smart-wishlist' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Action when clicking on the "wishlist menu".', 'woo-smart-wishlist' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="submit">
                                            <th colspan="2">
												<?php settings_fields( 'woosw_settings' ); ?>
												<?php submit_button(); ?>
                                            </th>
                                        </tr>
                                    </table>
                                </form>
							<?php } elseif ( $active_tab === 'localization' ) { ?>
                                <form method="post" action="options.php">
                                    <table class="form-table">
                                        <tr class="heading">
                                            <th scope="row"><?php esc_html_e( 'Localization', 'woo-smart-wishlist' ); ?></th>
                                            <td>
												<?php esc_html_e( 'Leave blank to use the default text and its equivalent translation in multiple languages.', 'woo-smart-wishlist' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Button text', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[button]"
                                                       value="<?php echo esc_attr( self::localization( 'button' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Add to wishlist', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Button text (added)', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[button_added]"
                                                       value="<?php echo esc_attr( self::localization( 'button_added' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Browse wishlist', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Wishlist popup heading', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[popup_heading]"
                                                       value="<?php echo esc_attr( self::localization( 'popup_heading' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Wishlist', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Empty wishlist button', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[empty_button]"
                                                       value="<?php echo esc_attr( self::localization( 'empty_button' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'remove all', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Add note', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[add_note]"
                                                       value="<?php echo esc_attr( self::localization( 'add_note' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Add note', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Save note', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[save_note]"
                                                       value="<?php echo esc_attr( self::localization( 'save_note' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Save', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Open wishlist page', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[open_page]"
                                                       value="<?php echo esc_attr( self::localization( 'open_page' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Open wishlist page', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Continue shopping', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[continue]"
                                                       value="<?php echo esc_attr( self::localization( 'continue' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Continue shopping', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Menu item label', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[menu_label]"
                                                       value="<?php echo esc_attr( self::localization( 'menu_label' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Wishlist', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th scope="row"><?php esc_html_e( 'Multiple Wishlist', 'woo-smart-wishlist' ); ?></th>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Primary wishlist name', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" name="woosw_localization[primary_name]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::localization( 'primary_name' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Wishlist', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Manage wishlists', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" name="woosw_localization[manage_wishlists]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::localization( 'manage_wishlists' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Manage wishlists', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Set default', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" name="woosw_localization[set_default]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::localization( 'set_default' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'set default', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Default', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" name="woosw_localization[is_default]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::localization( 'is_default' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'default', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Delete', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" name="woosw_localization[delete]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::localization( 'delete' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'delete', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Wishlist name placeholder', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" name="woosw_localization[placeholder_name]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::localization( 'placeholder_name' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'New Wishlist', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><?php esc_html_e( 'Add new wishlist', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" name="woosw_localization[add_wishlist]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::localization( 'add_wishlist' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Add New Wishlist', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th scope="row"><?php esc_html_e( 'Message', 'woo-smart-wishlist' ); ?></th>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Added to the wishlist', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[added_message]"
                                                       value="<?php echo esc_attr( self::localization( 'added_message' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( '{name} has been added to Wishlist.', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Already in the wishlist', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[already_message]"
                                                       value="<?php echo esc_attr( self::localization( 'already_message' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( '{name} is already in the Wishlist.', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Removed from wishlist', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[removed_message]"
                                                       value="<?php echo esc_attr( self::localization( 'removed_message' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Product has been removed from the Wishlist.', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Empty wishlist confirm', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[empty_confirm]"
                                                       value="<?php echo esc_attr( self::localization( 'empty_confirm' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'This action cannot be undone. Are you sure?', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Empty wishlist notice', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[empty_notice]"
                                                       value="<?php echo esc_attr( self::localization( 'empty_notice' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'All products have been removed from the Wishlist!', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Empty wishlist', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[empty_message]"
                                                       value="<?php echo esc_attr( self::localization( 'empty_message' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'There are no products on the Wishlist!', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Delete wishlist confirm', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[delete_confirm]"
                                                       value="<?php echo esc_attr( self::localization( 'delete_confirm' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'This action cannot be undone. Are you sure?', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Product does not exist', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[not_exist_message]"
                                                       value="<?php echo esc_attr( self::localization( 'not_exist_message' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'The product does not exist on the Wishlist!', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Need to login', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[login_message]"
                                                       value="<?php echo esc_attr( self::localization( 'login_message' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Please log in to use the Wishlist!', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Copied wishlist link', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[copied]"
                                                       value="<?php echo esc_attr( self::localization( 'copied' ) ); ?>"
                                                       placeholder="<?php esc_html_e( 'Copied the wishlist link:', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Have an error', 'woo-smart-wishlist' ); ?></th>
                                            <td>
                                                <input type="text" class="regular-text"
                                                       name="woosw_localization[error_message]"
                                                       value="<?php echo esc_attr( self::localization( 'error_message' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Have an error, please try again!', 'woo-smart-wishlist' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr class="submit">
                                            <th colspan="2">
												<?php settings_fields( 'woosw_localization' ); ?>
												<?php submit_button(); ?>
                                            </th>
                                        </tr>
                                    </table>
                                </form>
							<?php } elseif ( $active_tab === 'premium' ) { ?>
                                <div class="wpclever_settings_page_content_text">
                                    <p>Get the Premium Version just $29! <a
                                                href="https://wpclever.net/downloads/smart-wishlist?utm_source=pro&utm_medium=woosw&utm_campaign=wporg"
                                                target="_blank">https://wpclever.net/downloads/smart-wishlist</a>
                                    </p>
                                    <p><strong>Extra features for Premium Version:</strong></p>
                                    <ul style="margin-bottom: 0">
                                        <li>- Enable multiple wishlist per user.</li>
                                        <li>- Enable note for each product.</li>
                                        <li>- Get lifetime update & premium support.</li>
                                    </ul>
                                </div>
							<?php } ?>
                        </div>
                    </div>
					<?php
				}

				function enqueue_scripts() {
					// perfect srollbar
					if ( get_option( 'woosw_perfect_scrollbar', 'yes' ) === 'yes' ) {
						wp_enqueue_style( 'perfect-scrollbar', WOOSW_URI . 'assets/libs/perfect-scrollbar/css/perfect-scrollbar.min.css' );
						wp_enqueue_style( 'perfect-scrollbar-wpc', WOOSW_URI . 'assets/libs/perfect-scrollbar/css/custom-theme.css' );
						wp_enqueue_script( 'perfect-scrollbar', WOOSW_URI . 'assets/libs/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js', array( 'jquery' ), WOOSW_VERSION, true );
					}

					// feather icons
					wp_enqueue_style( 'woosw-feather', WOOSW_URI . 'assets/libs/feather/feather.css' );

					if ( get_option( 'woosw_button_action', 'list' ) === 'message' ) {
						wp_enqueue_style( 'notiny', WOOSW_URI . 'assets/libs/notiny/notiny.css' );
						wp_enqueue_script( 'notiny', WOOSW_URI . 'assets/libs/notiny/notiny.js', array( 'jquery' ), WOOSW_VERSION, true );
					}

					// main style
					wp_enqueue_style( 'woosw-frontend', WOOSW_URI . 'assets/css/frontend.css', array(), WOOSW_VERSION );
					$color_default = apply_filters( 'woosw_color_default', '#5fbd74' );
					$color         = apply_filters( 'woosw_color', get_option( 'woosw_color', $color_default ) );
					$custom_css    = ".woosw-popup .woosw-popup-inner .woosw-popup-content .woosw-popup-content-bot .woosw-notice { background-color: {$color}; } ";
					$custom_css    .= ".woosw-popup .woosw-popup-inner .woosw-popup-content .woosw-popup-content-bot .woosw-popup-content-bot-inner a:hover { color: {$color}; border-color: {$color}; } ";
					wp_add_inline_style( 'woosw-frontend', $custom_css );

					// main js
					wp_enqueue_script( 'woosw-frontend', WOOSW_URI . 'assets/js/frontend.js', array( 'jquery' ), WOOSW_VERSION, true );

					// localize
					wp_localize_script( 'woosw-frontend', 'woosw_vars', array(
							'ajax_url'            => admin_url( 'admin-ajax.php' ),
							'menu_action'         => get_option( 'woosw_menu_action', 'open_page' ),
							'perfect_scrollbar'   => get_option( 'woosw_perfect_scrollbar', 'yes' ),
							'wishlist_url'        => self::get_url(),
							'button_action'       => get_option( 'woosw_button_action', 'list' ),
							'message_position'    => get_option( 'woosw_message_position', 'right-top' ),
							'button_action_added' => get_option( 'woosw_button_action_added', 'popup' ),
							'empty_confirm'       => self::localization( 'empty_confirm', esc_html__( 'This action cannot be undone. Are you sure?', 'woo-smart-wishlist' ) ),
							'delete_confirm'      => self::localization( 'delete_confirm', esc_html__( 'This action cannot be undone. Are you sure?', 'woo-smart-wishlist' ) ),
							'copied_text'         => self::localization( 'copied', esc_html__( 'Copied the wishlist link:', 'woo-smart-wishlist' ) ),
							'menu_text'           => apply_filters( 'woosw_menu_item_label', self::localization( 'menu_label', esc_html__( 'Wishlist', 'woo-smart-wishlist' ) ) ),
							'button_text'         => apply_filters( 'woosw_button_text', self::localization( 'button', esc_html__( 'Add to wishlist', 'woo-smart-wishlist' ) ) ),
							'button_text_added'   => apply_filters( 'woosw_button_text_added', self::localization( 'button_added', esc_html__( 'Browse wishlist', 'woo-smart-wishlist' ) ) ),
						)
					);
				}

				function admin_enqueue_scripts() {
					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_style( 'woosw-backend', WOOSW_URI . 'assets/css/backend.css', array(), WOOSW_VERSION );
					wp_enqueue_script( 'woosw-backend', WOOSW_URI . 'assets/js/backend.js', array(
						'jquery',
						'wp-color-picker',
						'jquery-ui-dialog'
					), WOOSW_VERSION, true );
				}

				function action_links( $links, $file ) {
					static $plugin;

					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}

					if ( $plugin === $file ) {
						$settings             = '<a href="' . admin_url( 'admin.php?page=wpclever-woosw&tab=settings' ) . '">' . esc_html__( 'Settings', 'woo-smart-wishlist' ) . '</a>';
						$links['wpc-premium'] = '<a href="' . admin_url( 'admin.php?page=wpclever-woosw&tab=premium' ) . '" style="color: #c9356e">' . esc_html__( 'Premium Version', 'woo-smart-wishlist' ) . '</a>';
						array_unshift( $links, $settings );
					}

					return (array) $links;
				}

				function row_meta( $links, $file ) {
					static $plugin;

					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}

					if ( $plugin === $file ) {
						$row_meta = array(
							'support' => '<a href="' . esc_url( WOOSW_DISCUSSION ) . '" target="_blank">' . esc_html__( 'Community support', 'woo-smart-wishlist' ) . '</a>',
						);

						return array_merge( $links, $row_meta );
					}

					return (array) $links;
				}

				function get_items( $key, $layout = null ) {
					ob_start();

					$products  = get_option( 'woosw_list_' . $key );
					$link      = get_option( 'woosw_link', 'yes' );
					$table_tag = $tr_tag = $td_tag = 'div';

					if ( $layout === 'table' ) {
						$table_tag = 'table';
						$tr_tag    = 'tr';
						$td_tag    = 'td';
					}

					do_action( 'woosw_before_items', $key, $products );

					if ( is_array( $products ) && ( count( $products ) > 0 ) ) {
						echo '<' . $table_tag . ' class="woosw-items">';

						do_action( 'woosw_wishlist_items_before', $key, $products );

						foreach ( $products as $product_id => $product_data ) {
							$product = wc_get_product( $product_id );

							if ( ! $product || $product->get_status() !== 'publish' ) {
								continue;
							}

							if ( is_array( $product_data ) && isset( $product_data['time'] ) ) {
								$product_time = date_i18n( get_option( 'date_format' ), $product_data['time'] );
							} else {
								// for old version
								$product_time = date_i18n( get_option( 'date_format' ), $product_data );
							}

							if ( is_array( $product_data ) && ! empty( $product_data['note'] ) ) {
								$product_note = $product_data['note'];
							} else {
								$product_note = '';
							}

							echo '<' . $tr_tag . ' class="' . esc_attr( 'woosw-item woosw-item-' . $product_id ) . '" data-id="' . esc_attr( $product_id ) . '">';

							if ( $layout !== 'table' ) {
								echo '<div class="woosw-item-inner">';
							}

							do_action( 'woosw_wishlist_item_before', $product, $product_id, $key );

							if ( self::can_edit( $key ) ) {
								// remove
								echo '<' . $td_tag . ' class="woosw-item--remove"><span></span></' . $td_tag . '>';
							}

							// image
							echo '<' . $td_tag . ' class="woosw-item--image">';

							if ( $link !== 'no' ) {
								echo '<a ' . ( $link === 'yes_popup' ? 'class="woosq-link" data-id="' . esc_attr( $product_id ) . '" data-context="woosw"' : '' ) . ' href="' . esc_url( $product->get_permalink() ) . '" ' . ( $link === 'yes_blank' ? 'target="_blank"' : '' ) . '>';
								echo wp_kses( apply_filters( 'woosw_item_image', $product->get_image() ), 'woosw' );
								echo '</a>';
							} else {
								echo wp_kses( apply_filters( 'woosw_item_image', $product->get_image() ), 'woosw' );
							}

							do_action( 'woosw_wishlist_item_image', $product, $product_id, $key );
							echo '</' . $td_tag . '>';

							// info
							echo '<' . $td_tag . ' class="woosw-item--info">';

							if ( $link !== 'no' ) {
								echo '<div class="woosw-item--name"><a ' . ( $link === 'yes_popup' ? 'class="woosq-link" data-id="' . esc_attr( $product_id ) . '" data-context="woosw"' : '' ) . ' href="' . esc_url( $product->get_permalink() ) . '" ' . ( $link === 'yes_blank' ? 'target="_blank"' : '' ) . '>' . esc_html( apply_filters( 'woosw_item_name', $product->get_name(), $product ) ) . '</a></div>';
							} else {
								echo '<div class="woosw-item--name">' . esc_html( apply_filters( 'woosw_item_name', $product->get_name(), $product ) ) . '</div>';
							}

							echo '<div class="woosw-item--price">' . wp_kses( apply_filters( 'woosw_item_price', $product->get_price_html(), $product ), 'woosw' ) . '</div>';

							echo '<div class="woosw-item--time">' . esc_html( apply_filters( 'woosw_item_time', $product_time, $product ) ) . '</div>';

							do_action( 'woosw_wishlist_item_info', $product, $product_id, $key );
							echo '</' . $td_tag . '>';

							// action
							echo '<' . $td_tag . ' class="woosw-item--actions">';
							echo '<div class="woosw-item--stock">' . apply_filters( 'woosw_item_stock', wc_get_stock_html( $product ), $product ) . '</div>';
							echo '<div class="woosw-item--add">' . apply_filters( 'woosw_item_add_to_cart', do_shortcode( '[add_to_cart style="" show_price="false" id="' . esc_attr( $product_id ) . '"]' ), $product ) . '</div>';
							do_action( 'woosw_wishlist_item_actions', $product, $product_id, $key );
							echo '</' . $td_tag . '>';

							do_action( 'woosw_wishlist_item_after', $product, $product_id, $key );

							if ( $layout !== 'table' ) {
								echo '</div><!-- /woosw-item-inner -->';
							}

							echo '</' . $tr_tag . '>';
						}

						do_action( 'woosw_wishlist_items_after', $key, $products );
						echo '</' . $table_tag . '>';
					} else {
						echo '<div class="woosw-popup-content-mid-massage">' . self::localization( 'empty_message', esc_html__( 'There are no products on the Wishlist!', 'woo-smart-wishlist' ) ) . '</div>';
					}

					do_action( 'woosw_after_items', $key, $products );

					return apply_filters( 'woosw_wishlist_items', ob_get_clean(), $key, $products );
				}

				function nav_menu_items( $items, $args ) {
					$selected    = false;
					$saved_menus = get_option( 'woosw_menus', array() );

					if ( ! is_array( $saved_menus ) || empty( $saved_menus ) || ! property_exists( $args, 'menu' ) ) {
						return $items;
					}

					if ( $args->menu instanceof WP_Term ) {
						// menu object
						if ( in_array( $args->menu->term_id, $saved_menus, false ) ) {
							$selected = true;
						}
					} elseif ( is_numeric( $args->menu ) ) {
						// menu id
						if ( in_array( $args->menu, $saved_menus, false ) ) {
							$selected = true;
						}
					} elseif ( is_string( $args->menu ) ) {
						// menu slug or name
						$menu = get_term_by( 'name', $args->menu, 'nav_menu' );

						if ( ! $menu ) {
							$menu = get_term_by( 'slug', $args->menu, 'nav_menu' );
						}

						if ( $menu && in_array( $menu->term_id, $saved_menus, false ) ) {
							$selected = true;
						}
					}

					if ( $selected ) {
						$menu_item = '<li class="' . esc_attr( apply_filters( 'woosw_menu_item_class', 'menu-item woosw-menu-item menu-item-type-woosw' ) ) . '"><a href="' . esc_url( self::get_url() ) . '"><span class="woosw-menu-item-inner" data-count="' . esc_attr( self::get_count() ) . '">' . esc_html( apply_filters( 'woosw_menu_item_label', self::localization( 'menu_label', esc_html__( 'Wishlist', 'woo-smart-wishlist' ) ) ) ) . '</span></a></li>';
						$items     .= apply_filters( 'woosw_menu_item', $menu_item );
					}

					return $items;
				}

				function wp_footer() {
					if ( is_admin() ) {
						return;
					}

					echo '<div id="woosw_wishlist" class="woosw-popup ' . esc_attr( 'woosw-popup-' . get_option( 'woosw_popup_position', 'center' ) ) . '"></div>';
				}

				function wishlist_content( $key = false, $message = '' ) {
					if ( empty( $key ) ) {
						$key = self::get_key();
					}

					if ( $products = get_option( 'woosw_list_' . $key ) ) {
						$count = count( $products );
					} else {
						$count = 0;
					}

					$name = self::localization( 'popup_heading', esc_html__( 'Wishlist', 'woo-smart-wishlist' ) );

					ob_start();
					?>
                    <div class="woosw-popup-inner" data-key="<?php echo esc_attr( $key ); ?>">
                        <div class="woosw-popup-content">
                            <div class="woosw-popup-content-top">
                                <span class="woosw-name"><?php echo esc_html( $name ); ?></span>
								<?php
								echo '<span class="woosw-count-wrapper">';
								echo '<span class="woosw-count">' . esc_html( $count ) . '</span>';

								if ( get_option( 'woosw_empty_button', 'no' ) === 'yes' ) {
									echo '<span class="woosw-empty"' . ( $count ? '' : ' style="display:none"' ) . '>' . self::localization( 'empty_button', esc_html__( 'remove all', 'woo-smart-wishlist' ) ) . '</span>';
								}

								echo '</span>';
								?>
                                <span class="woosw-popup-close"></span>
                            </div>
                            <div class="woosw-popup-content-mid">
								<?php if ( ! empty( $message ) ) {
									echo '<div class="woosw-popup-content-mid-massage">' . esc_html( $message ) . '</div>';
								} else {
									echo self::get_items( $key );
								} ?>
                            </div>
                            <div class="woosw-popup-content-bot">
                                <div class="woosw-popup-content-bot-inner">
                                    <a class="woosw-page" href="<?php echo esc_url( self::get_url( $key, true ) ); ?>">
										<?php echo self::localization( 'open_page', esc_html__( 'Open wishlist page', 'woo-smart-wishlist' ) ); ?>
                                    </a>
                                    <a class="woosw-continue"
                                       href="<?php echo esc_url( get_option( 'woosw_continue_url' ) ); ?>"
                                       data-url="<?php echo esc_url( get_option( 'woosw_continue_url' ) ); ?>">
										<?php echo self::localization( 'continue', esc_html__( 'Continue shopping', 'woo-smart-wishlist' ) ); ?>
                                    </a>
                                </div>
                                <div class="woosw-notice"></div>
                            </div>
                        </div>
                    </div>
					<?php
					return ob_get_clean();
				}

				function manage_content() {
					?>
                    <div class="woosw-popup-inner">
                        <div class="woosw-popup-content">
                            <div class="woosw-popup-content-top">
								<?php echo self::localization( 'manage_wishlists', esc_html__( 'Manage wishlists', 'woo-smart-wishlist' ) ); ?>
                                <span class="woosw-popup-close"></span>
                            </div>
                            <div class="woosw-popup-content-mid">
								<?php if ( ( $user_id = get_current_user_id() ) ) { ?>
                                    <table class="woosw-items">
										<?php
										$key  = get_user_meta( $user_id, 'woosw_key', true );
										$keys = get_user_meta( $user_id, 'woosw_keys', true ) ?: array();
										$max  = get_option( 'woosw_maximum_wishlists', '5' );

										if ( is_array( $keys ) && ! empty( $keys ) ) {
											foreach ( $keys as $k => $wl ) {
												if ( $products = get_option( 'woosw_list_' . $k ) ) {
													$count = count( $products );
												} else {
													$count = 0;
												}

												echo '<tr class="woosw-item">';
												echo '<td>';

												if ( isset( $wl['type'] ) && ( $wl['type'] === 'primary' ) ) {
													echo '<a class="woosw-view-wishlist" href="' . esc_url( self::get_url( $k, true ) ) . '" data-key="' . esc_attr( $k ) . '">' . self::localization( 'primary_name', esc_html__( 'Wishlist', 'woo-smart-wishlist' ) ) . '</a> - primary (' . $count . ')';
												} else {
													if ( ! empty( $wl['name'] ) ) {
														echo '<a class="woosw-view-wishlist" href="' . esc_url( self::get_url( $k, true ) ) . '" data-key="' . esc_attr( $k ) . '">' . $wl['name'] . '</a> (' . $count . ')';
													} else {
														echo '<a class="woosw-view-wishlist" href="' . esc_url( self::get_url( $k, true ) ) . '" data-key="' . esc_attr( $k ) . '">' . $k . '</a> (' . $count . ')';
													}
												}

												echo '</td><td style="text-align: end">';

												if ( $key === $k ) {
													echo '<span class="woosw-default">' . self::localization( 'is_default', esc_html__( 'default', 'woo-smart-wishlist' ) ) . '</span>';
												} else {
													echo '<a class="woosw-set-default" data-key="' . esc_attr( $k ) . '" href="#">' . self::localization( 'set_default', esc_html__( 'set default', 'woo-smart-wishlist' ) ) . '</a>';
												}

												echo '</td><td style="text-align: end">';

												if ( ( ! isset( $wl['type'] ) || ( $wl['type'] !== 'primary' ) ) && ( $key !== $k ) ) {
													echo '<a class="woosw-delete-wishlist" data-key="' . esc_attr( $k ) . '" href="#">' . self::localization( 'delete', esc_html__( 'delete', 'woo-smart-wishlist' ) ) . '</a>';
												}

												echo '</td></tr>';
											}
										}
										?>
                                        <tr <?php echo( is_array( $keys ) && ( count( $keys ) < (int) $max ) ? '' : 'class="woosw-disable"' ); ?>>
                                            <td colspan="100%">
                                                <div class="woosw-new-wishlist">
                                                    <input type="text" id="woosw_wishlist_name"
                                                           placeholder="<?php echo esc_attr( self::localization( 'placeholder_name', esc_html__( 'New Wishlist', 'woo-smart-wishlist' ) ) ); ?>"/>
                                                    <input type="button" id="woosw_add_wishlist"
                                                           value="<?php echo esc_attr( self::localization( 'add_wishlist', esc_html__( 'Add New Wishlist', 'woo-smart-wishlist' ) ) ); ?>"/>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
								<?php } ?>
                            </div>
                        </div>
                    </div>
					<?php
				}

				function update_product_count( $product_id, $action = 'add' ) {
					$meta_count = 'woosw_count';
					$meta_time  = ( $action === 'add' ? 'woosw_add' : 'woosw_remove' );
					$count      = get_post_meta( $product_id, $meta_count, true );
					$new_count  = 0;

					if ( $action === 'add' ) {
						if ( $count ) {
							$new_count = absint( $count ) + 1;
						} else {
							$new_count = 1;
						}
					} elseif ( $action === 'remove' ) {
						if ( $count && ( absint( $count ) > 1 ) ) {
							$new_count = absint( $count ) - 1;
						} else {
							$new_count = 0;
						}
					}

					update_post_meta( $product_id, $meta_count, $new_count );
					update_post_meta( $product_id, $meta_time, time() );
				}

				public static function generate_key() {
					$key         = '';
					$key_str     = apply_filters( 'woosw_key_characters', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' );
					$key_str_len = strlen( $key_str );

					for ( $i = 0; $i < apply_filters( 'woosw_key_length', 6 ); $i ++ ) {
						$key .= $key_str[ random_int( 0, $key_str_len - 1 ) ];
					}

					return apply_filters( 'woosw_generate_key', $key );
				}

				public static function exists_key( $key ) {
					if ( get_option( 'woosw_list_' . $key ) ) {
						return true;
					}

					return false;
				}

				public static function can_edit( $key ) {
					if ( is_user_logged_in() ) {
						if ( get_user_meta( get_current_user_id(), 'woosw_key', true ) === $key ) {
							return true;
						}

						if ( ( $keys = get_user_meta( get_current_user_id(), 'woosw_keys', true ) ) && isset( $keys[ $key ] ) ) {
							return true;
						}
					} else {
						if ( isset( $_COOKIE['woosw_key'] ) && ( sanitize_text_field( $_COOKIE['woosw_key'] ) === $key ) ) {
							return true;
						}
					}

					return false;
				}

				public static function get_page_id() {
					if ( get_option( 'woosw_page_id' ) ) {
						return absint( get_option( 'woosw_page_id' ) );
					}

					return false;
				}

				public static function get_key( $new = false ) {
					if ( $new ) {
						// get a new key for multiple wishlist
						$key = self::generate_key();

						while ( self::exists_key( $key ) ) {
							$key = self::generate_key();
						}

						return $key;
					} else {
						if ( ! is_user_logged_in() && ( get_option( 'woosw_disable_unauthenticated', 'no' ) === 'yes' ) ) {
							return '#';
						}

						if ( is_user_logged_in() && ( ( $user_id = get_current_user_id() ) > 0 ) ) {
							$key = get_user_meta( $user_id, 'woosw_key', true );

							if ( empty( $key ) ) {
								$key = self::generate_key();

								while ( self::exists_key( $key ) ) {
									$key = self::generate_key();
								}

								// set a new key
								update_user_meta( $user_id, 'woosw_key', $key );

								// multiple wishlist
								update_user_meta( $user_id, 'woosw_keys', array(
									$key => array(
										'type' => 'primary',
										'name' => '',
										'time' => ''
									)
								) );
							}

							return $key;
						}

						if ( isset( $_COOKIE['woosw_key'] ) ) {
							return trim( sanitize_text_field( $_COOKIE['woosw_key'] ) );
						}

						return 'WOOSW';
					}
				}

				public static function get_url( $key = null, $full = false ) {
					$url = home_url( '/' );

					if ( $page_id = self::get_page_id() ) {
						if ( $full ) {
							if ( ! $key ) {
								$key = self::get_key();
							}

							if ( get_option( 'permalink_structure' ) !== '' ) {
								$url = trailingslashit( get_permalink( $page_id ) ) . $key;
							} else {
								$url = get_permalink( $page_id ) . '&woosw_id=' . $key;
							}
						} else {
							$url = get_permalink( $page_id );
						}
					}

					return esc_url( apply_filters( 'woosw_wishlist_url', $url, $key ) );
				}

				public static function get_count( $key = null ) {
					if ( ! $key ) {
						$key = self::get_key();
					}

					if ( ( $key != '' ) && ( $products = get_option( 'woosw_list_' . $key ) ) && is_array( $products ) ) {
						$count = count( $products );
					} else {
						$count = 0;
					}

					return esc_html( apply_filters( 'woosw_wishlist_count', $count, $key ) );
				}

				function product_columns( $columns ) {
					$columns['woosw'] = esc_html__( 'Wishlist', 'woo-smart-wishlist' );

					return $columns;
				}

				function posts_custom_column( $column, $postid ) {
					if ( $column == 'woosw' ) {
						if ( ( $count = (int) get_post_meta( $postid, 'woosw_count', true ) ) > 0 ) {
							echo '<a href="#" class="woosw_action" data-pid="' . esc_attr( $postid ) . '">' . esc_html( $count ) . '</a>';
						}
					}
				}

				function sortable_columns( $columns ) {
					$columns['woosw'] = 'woosw';

					return $columns;
				}

				function request( $vars ) {
					if ( isset( $vars['orderby'] ) && 'woosw' == $vars['orderby'] ) {
						$vars = array_merge( $vars, array(
							'meta_key' => 'woosw_count',
							'orderby'  => 'meta_value_num'
						) );
					}

					return $vars;
				}

				function wp_login( $user_login, $user ) {
					if ( isset( $user->data->ID ) ) {
						$key = get_user_meta( $user->data->ID, 'woosw_key', true );

						if ( empty( $key ) ) {
							$key = self::generate_key();

							while ( self::exists_key( $key ) ) {
								$key = self::generate_key();
							}

							// set a new key
							update_user_meta( $user->data->ID, 'woosw_key', $key );
						}

						// multiple wishlist
						if ( ! get_user_meta( $user->data->ID, 'woosw_keys', true ) ) {
							update_user_meta( $user->data->ID, 'woosw_keys', array(
								$key => array(
									'type' => 'primary',
									'name' => '',
									'time' => ''
								)
							) );
						}

						$secure   = apply_filters( 'woosw_cookie_secure', wc_site_is_https() && is_ssl() );
						$httponly = apply_filters( 'woosw_cookie_httponly', true );

						if ( isset( $_COOKIE['woosw_key'] ) && ! empty( $_COOKIE['woosw_key'] ) ) {
							wc_setcookie( 'woosw_key_ori', trim( sanitize_text_field( $_COOKIE['woosw_key'] ) ), time() + 604800, $secure, $httponly );
						}

						wc_setcookie( 'woosw_key', $key, time() + 604800, $secure, $httponly );
					}
				}

				function wp_logout( $user_id ) {
					if ( isset( $_COOKIE['woosw_key_ori'] ) && ! empty( $_COOKIE['woosw_key_ori'] ) ) {
						$secure   = apply_filters( 'woosw_cookie_secure', wc_site_is_https() && is_ssl() );
						$httponly = apply_filters( 'woosw_cookie_httponly', true );

						wc_setcookie( 'woosw_key', trim( sanitize_text_field( $_COOKIE['woosw_key_ori'] ) ), time() + 604800, $secure, $httponly );
					} else {
						unset( $_COOKIE['woosw_key_ori'] );
						unset( $_COOKIE['woosw_key'] );
					}
				}

				function display_post_states( $states, $post ) {
					if ( 'page' == get_post_type( $post->ID ) && $post->ID === absint( get_option( 'woosw_page_id' ) ) ) {
						$states[] = esc_html__( 'Wishlist', 'woo-smart-wishlist' );
					}

					return $states;
				}

				function users_columns( $column ) {
					$column['woosw'] = esc_html__( 'Wishlist', 'woo-smart-wishlist' );

					return $column;
				}

				function users_columns_content( $val, $column_name, $user_id ) {
					if ( $column_name === 'woosw' ) {
						$key = get_user_meta( $user_id, 'woosw_key', true );

						if ( ! empty( $key ) && ( $products = get_option( 'woosw_list_' . $key, true ) ) ) {
							if ( is_array( $products ) && ( $count = count( $products ) ) ) {
								$val = '<a href="#" class="woosw_action" data-key="' . esc_attr( $key ) . '">' . esc_html( $count ) . '</a>';
							}
						}
					}

					return $val;
				}

				function wishlist_quickview() {
					check_admin_referer();

					global $wpdb;
					$wishlist_html = '';

					if ( isset( $_POST['key'] ) && $_POST['key'] != '' ) {
						ob_start();

						$key      = sanitize_text_field( $_POST['key'] );
						$products = get_option( 'woosw_list_' . $key, true );
						$count    = count( $products );

						if ( count( $products ) > 0 ) {
							echo '<div class="woosw-quickview-items">';

							$user = $wpdb->get_results( $wpdb->prepare( 'SELECT user_id FROM `' . $wpdb->prefix . 'usermeta` WHERE `meta_key` = "woosw_key" AND `meta_value` = "%s" LIMIT 1', $key ) );

							echo '<div class="woosw-quickview-item">';
							echo '<div class="woosw-quickview-item-image"><a href="' . esc_url( self::get_url( $key, true ) ) . '" target="_blank">#' . $key . '</a></div>';
							echo '<div class="woosw-quickview-item-info">';

							if ( ! empty( $user ) ) {
								$user_id   = $user[0]->user_id;
								$user_data = get_userdata( $user_id );

								echo '<div class="woosw-quickview-item-title"><a href="' . get_edit_user_link( $user_id ) . '" target="_blank">' . $user_data->user_login . '</a></div>';
								echo '<div class="woosw-quickview-item-data">' . $user_data->user_email . ' | ' . sprintf( _n( '%s product', '%s products', $count, 'woo-smart-wishlist' ), number_format_i18n( $count ) ) . '</div>';
							} else {
								echo '<div class="woosw-quickview-item-title">' . esc_html__( 'Guest', 'woo-smart-wishlist' ) . '</div>';
								echo '<div class="woosw-quickview-item-data">' . sprintf( _n( '%s product', '%s products', $count, 'woo-smart-wishlist' ), number_format_i18n( $count ) ) . '</div>';
							}

							echo '</div><!-- /woosw-quickview-item-info -->';
							echo '</div><!-- /woosw-quickview-item -->';

							foreach ( $products as $pid => $data ) {
								$_product = wc_get_product( $pid );

								if ( $_product ) {
									echo '<div class="woosw-quickview-item">';
									echo '<div class="woosw-quickview-item-image">' . $_product->get_image() . '</div>';
									echo '<div class="woosw-quickview-item-info">';
									echo '<div class="woosw-quickview-item-title"><a href="' . esc_url( $_product->get_permalink() ) . '" target="_blank">' . $_product->get_name() . '</a></div>';
									echo '<div class="woosw-quickview-item-data">' . date_i18n( get_option( 'date_format' ), $data['time'] ) . ' <span class="woosw-quickview-item-links">| ID: ' . $pid . ' | <a href="' . get_edit_post_link( $pid ) . '" target="_blank">' . esc_html__( 'Edit', 'woo-smart-wishlist' ) . '</a> | <a href="#" class="woosw_action" data-pid="' . esc_attr( $pid ) . '">' . esc_html__( 'See in wishlist', 'woo-smart-wishlist' ) . '</a></span></div>';
									echo '</div><!-- /woosw-quickview-item-info -->';
									echo '</div><!-- /woosw-quickview-item -->';
								}
							}

							echo '</div>';
						} else {
							echo '<div style="text-align: center">' . esc_html__( 'Empty Wishlist', 'woo-smart-wishlist' ) . '<div>';
						}

						$wishlist_html = ob_get_clean();
					} elseif ( isset( $_POST['pid'] ) ) {
						ob_start();

						$pid   = (int) sanitize_text_field( $_POST['pid'] );
						$keys  = $wpdb->get_results( $wpdb->prepare( 'SELECT option_name FROM `' . $wpdb->prefix . 'options` WHERE `option_name` LIKE "%woosw_list_%" AND `option_value` LIKE "%i:%d;%"', $pid ) );
						$count = count( $keys );

						if ( $count > 0 ) {
							echo '<div class="woosw-quickview-items">';

							$_product = wc_get_product( $pid );

							if ( $_product ) {
								echo '<div class="woosw-quickview-item">';
								echo '<div class="woosw-quickview-item-image">' . $_product->get_image() . '</div>';
								echo '<div class="woosw-quickview-item-info">';
								echo '<div class="woosw-quickview-item-title"><a href="' . esc_url( $_product->get_permalink() ) . '" target="_blank">' . $_product->get_name() . '</a></div>';
								echo '<div class="woosw-quickview-item-data">ID: ' . $pid . ' | ' . sprintf( _n( '%s wishlist', '%s wishlists', $count, 'woosw' ), number_format_i18n( $count ) ) . ' <span class="woosw-quickview-item-links">| <a href="' . get_edit_post_link( $pid ) . '" target="_blank">' . esc_html__( 'Edit', 'woo-smart-wishlist' ) . '</a></span></div>';
								echo '</div><!-- /woosw-quickview-item-info -->';
								echo '</div><!-- /woosw-quickview-item -->';
							}

							foreach ( $keys as $item ) {
								$products = get_option( $item->option_name );
								$count    = count( $products );
								$key      = str_replace( 'woosw_list_', '', $item->option_name );
								$user     = $wpdb->get_results( $wpdb->prepare( 'SELECT user_id FROM `' . $wpdb->prefix . 'usermeta` WHERE `meta_key` = "woosw_key" AND `meta_value` = "%s" LIMIT 1', $key ) );

								echo '<div class="woosw-quickview-item">';
								echo '<div class="woosw-quickview-item-image"><a href="' . esc_url( self::get_url( $key, true ) ) . '" target="_blank">#' . esc_html( $key ) . '</a></div>';
								echo '<div class="woosw-quickview-item-info">';

								if ( ! empty( $user ) ) {
									$user_id   = $user[0]->user_id;
									$user_data = get_userdata( $user_id );


									echo '<div class="woosw-quickview-item-title"><a href="' . get_edit_user_link( $user_id ) . '" target="_blank">' . $user_data->user_login . '</a></div>';
									echo '<div class="woosw-quickview-item-data">' . $user_data->user_email . '  | <a href="#" class="woosw_action" data-key="' . esc_attr( $key ) . '">' . sprintf( _n( '%s product', '%s products', $count, 'woo-smart-wishlist' ), number_format_i18n( $count ) ) . '</a></div>';
								} else {
									echo '<div class="woosw-quickview-item-title">' . esc_html__( 'Guest', 'woo-smart-wishlist' ) . '</div>';
									echo '<div class="woosw-quickview-item-data"><a href="#" class="woosw_action" data-key="' . esc_attr( $key ) . '">' . sprintf( _n( '%s product', '%s products', $count, 'woo-smart-wishlist' ), number_format_i18n( $count ) ) . '</a></div>';
								}

								echo '</div><!-- /woosw-quickview-item-info -->';
								echo '</div><!-- /woosw-quickview-item -->';
							}

							echo '</div>';
						}

						$wishlist_html = ob_get_clean();
					}

					echo apply_filters( 'woosw_wishlist_quickview', $wishlist_html );
					die();
				}

				function dropdown_cats_multiple( $output, $r ) {
					if ( isset( $r['multiple'] ) && $r['multiple'] ) {
						$output = preg_replace( '/^<select/i', '<select multiple', $output );
						$output = str_replace( "name='{$r['name']}'", "name='{$r['name']}[]'", $output );

						foreach ( array_map( 'trim', explode( ",", $r['selected'] ) ) as $value ) {
							$output = str_replace( "value=\"{$value}\"", "value=\"{$value}\" selected", $output );
						}
					}

					return $output;
				}

				function kses_allowed_html( $allowed, $context ) {
					if ( $context === 'woosw' ) {
						return array(
							'img'    => array( 'class' => array(), 'src' => array(), 'alt' => array() ),
							'div'    => array(
								'class' => array(),
								'id'    => array(),
							),
							'a'      => array(
								'class'   => array(),
								'id'      => array(),
								'data-id' => array(),
								'href'    => array(),
								'title'   => array()
							),
							'span'   => array( 'class' => array(), 'id' => array() ),
							'i'      => array( 'class' => array() ),
							'u'      => array( 'class' => array() ),
							's'      => array( 'class' => array() ),
							'strong' => array(),
							'del'    => array(),
							'ins'    => array(),
						);
					}

					return $allowed;
				}
			}

			new WPCleverWoosw();
		}
	}
}

if ( ! function_exists( 'woosw_plugin_activate' ) ) {
	function woosw_plugin_activate() {
		// create wishlist page
		$wishlist_page = get_page_by_path( 'wishlist', OBJECT );

		if ( empty( $wishlist_page ) ) {
			$wishlist_page_data = array(
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_name'      => 'wishlist',
				'post_title'     => esc_html__( 'Wishlist', 'woo-smart-wishlist' ),
				'post_content'   => '[woosw_list]',
				'post_parent'    => 0,
				'comment_status' => 'closed'
			);
			$wishlist_page_id   = wp_insert_post( $wishlist_page_data );

			update_option( 'woosw_page_id', $wishlist_page_id );
		}
	}
}

if ( ! function_exists( 'woosw_notice_wc' ) ) {
	function woosw_notice_wc() {
		?>
        <div class="error">
            <p><strong>WPC Smart Wishlist</strong> requires WooCommerce version 3.0 or greater.</p>
        </div>
		<?php
	}
}
