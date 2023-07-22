<?php
/**
 * Theme Sidebars.
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
/**
 * Class Shortcode.
 */
class Shortcode {
	use Singleton;
	/**
	 * Construct method.
	 */
	protected function __construct() {
		$this->setup_hooks();
	}
	/**
	 * To register action/filter.
	 *
	 * @return void
	 */
	protected function setup_hooks() {
		/**
		 * Actions
		 */
		add_shortcode( 'checkout_video', [ $this, 'checkout_video' ] );
	}
	public function checkout_video( $args ) {
		$args = wp_parse_args( $args, [] );$errorHappens = false;
		$args[ 'post_id' ] = hexdec( get_query_var( 'clip' ) );
		if( ! $args[ 'post_id' ] || empty( $args[ 'post_id' ] ) ) {$errorHappens = __( 'Link that required video clip ID included.', 'teddybearsprompts' );}
		$meta = get_post_meta( $args[ 'post_id' ], 'checkout_video_clip', true );
		if( ! isset( $meta[ 'full_path' ] ) || $meta[ 'full_path' ] === false || ! file_exists( $meta[ 'full_path' ] ) || is_dir( $meta[ 'full_path' ] ) ) {$errorHappens = __( 'Video file not found. Maybe expired :( If so, message is file removed permanently from server.', 'teddybearsprompts' );}
		// if( ! is_user_logged_in() || get_current_user_id() != get_post_field( 'post_author', $args[ 'post_id' ] ) || ! current_user_can( 'manage_options' ) ) {$errorHappens = __( 'You don\'t have authorization to access this video. Only people who placed order or uploaded video and admin can access this video.', 'teddybearsprompts' );}
		wp_enqueue_script( 'FutureWordPressScratchProject' );wp_enqueue_script( 'FutureWordPressScratchProject-checkout' );
		ob_start();
		if( $errorHappens === false ) :
			$meta['type'] = apply_filters( 'futurewordpress/project/teddybearpopupaddon/validate/format', $meta['type'], $meta );
		?>
		<div class="fwp-video-player-wraper">
			<div class="fwp-video-wrap">
				<video playsinline class="video-js vjs-default-skin fwp-videojs-playing-field" controls preload="auto" data-temp-poster="" data-setup='{ "controls": true, "autoplay": false, "preload": "none", "fluid": true, "fill": true, "responsive": true }'>
					<source src="<?php echo esc_url( $meta['full_url'] ); ?>" type="<?php echo esc_attr( $meta['type'] ); ?>"></source>
					<p class="vjs-no-js">
						<?php esc_html_e( 'To view this video please enable JavaScript, and consider upgrading to a
						web browser that', 'teddybearsprompts' ); ?>
						<a href="https://videojs.com/html5-video-support/" target="_blank">
							<?php esc_html_e( 'supports HTML5 video', 'teddybearsprompts' ); ?>
						</a>
					</p>
				</video>
			</div>
		</div>
		<style>.--video{object-fit: fill;}.fwp-videojs-playing-field {margin: auto;max-width: 100%;position: relative;display: block;width: auto;height: unset;min-height: 300px;max-height: 100vh;aspect-ratio: 16/9;}</style>
		<?php
		else :
			echo wp_kses_post( $errorHappens );
		endif;
		return ob_get_clean();
	}
}
