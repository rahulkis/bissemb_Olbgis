<?php
if ( ! class_exists( 'InsightCore_Banner' ) ) {
	class InsightCore_Banner {
		protected static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function initialize() {
			add_action( 'wp_ajax_tm_themes_banner_notice', [ $this, 'themes_banner_notice' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
			add_action( 'admin_notices', [ $this, 'featured_themes_banner' ], 10 );

			add_action( 'wp_install', function() {
				if ( isset( $_GET['tm_show_themes_banner'] ) ) {
					$show_themes_banner = empty( $_GET['tm_show_themes_banner'] ) ? 0 : 1;
					update_user_meta( get_current_user_id(), 'show_featured_themes_banner', $show_themes_banner );
				} else {
					update_user_meta( get_current_user_id(), 'show_featured_themes_banner', 1 );
				}
			} );
		}

		public function admin_enqueue_scripts( $hook ) {
			// Don't load scripts in Insight Core Screen
			if ( strpos( $hook, 'page_insight-core' ) !== false ) {
				return;
			}

			if ( ! empty( $_GET['action'] ) ) {
				return;
			}

			wp_enqueue_style( 'slick', INSIGHT_CORE_PATH . 'includes/dashboard/slick/slick.css' );
			wp_enqueue_script( 'slick', INSIGHT_CORE_PATH . 'includes/dashboard/slick/slick.min.js', array( 'jquery' ), null, true );
			wp_enqueue_style( 'insight-core-dashboard', INSIGHT_CORE_PATH . 'includes/dashboard/style.css' );
			wp_enqueue_script( 'insight-core-dashboard', INSIGHT_CORE_PATH . 'includes/dashboard/script.js', array( 'jquery' ), null, true );
		}

		public function featured_themes_banner() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( strpos( $screen_id, 'page_insight-core' ) !== false ) {
				return;
			}

			if ( ! empty( $_GET['action'] ) ) {
				return;
			}

			$themes = get_transient( 'insight_core_featured_theme_banner' );

			if ( false === $themes ) {
				$request = wp_remote_get( 'https://api.thememove.com/dashboard/themes.json', array( 'timeout' => 120 ) );
				if ( ! is_wp_error( $request ) ) {
					$themes = json_decode( wp_remote_retrieve_body( $request ) );
					set_transient( 'insight_core_featured_theme_banner', $themes, 24 * HOUR_IN_SECONDS );
				}
			}

			$option = (int) get_user_meta( get_current_user_id(), 'show_featured_themes_banner', true );

			$hide = ( 0 === $option );

			$hidden_class = $hide ? 'hidden' : '';

			if ( is_object( $themes ) ) {
				?>
				<div class="tm-featured-themes-banner notice <?php echo esc_attr( $hidden_class ) ?>">
					<?php wp_nonce_field( 'tm-themes-banner', 'themes_banner_nonce', false ); ?>
					<a href="<?php echo esc_url( admin_url( '?tm_show_themes_banner=0' ) ); ?>"
					   class="notice-dismiss tm-featured-themes-banner__close">
						<span
							class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'insight-core' ) ?></span>
					</a>
					<div class="tm-featured-themes-banner__wrapper">
						<?php
						foreach ( $themes as $theme ) {
							$description = ! empty( $theme->description ) ? sprintf( '<p class="item__description">%s</p>', wp_kses_post( $theme->description ) ) : '';
							$demo_button = ! empty( $theme->demo_url ) ? sprintf( '<a href="%s" class="button purchase" target="_blank">%s</a>', $theme->demo_url, esc_html__( 'View Demo', 'insight-core' ) ) : '';

							printf(
								'<div class="item">
									<div class="item__wrapper">
										<div class="item__image"><a href="%s" target="_blank"><img src="%s" alt="%s"></a></div>
										<div class="item__content">
											<h4 class="item__title"><a href="%s" target="_blank">%s</a></h4>
											%s
											<div class="buttons">
												%s
												<a href="%s" class="button purchase" target="_blank">%s</a>
											</div>
										</div>
									</div>
								</div>',
								esc_url( $theme->url ),
								esc_url( $theme->img ),
								esc_html( $theme->name ),
								esc_url( $theme->url ),
								esc_html( $theme->name ),
								$description,
								$demo_button,
								esc_url( $theme->url ),
								esc_html__( 'Purchase Now', 'insight-core' )
							);
						}
						?>
					</div>
				</div>
				<?php
			}
		}

		public function themes_banner_notice() {
			check_ajax_referer( 'tm-themes-banner', 'nonce' );

			update_user_meta( get_current_user_id(), 'show_featured_themes_banner', empty( $_POST['visible'] ) ? 0 : 1 );

			wp_die( 1 );
		}
	}

	InsightCore_Banner::instance()->initialize();
}
