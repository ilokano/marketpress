<?php
/**
 * Class MP_Store_Settings_Import
 *
 * @since   3.2.3
 * @package MarketPress
 */

if ( ! class_exists( 'MP_Store_Settings_Import' ) ) {
	return;
}

/** Load WordPress export API */
require_once( ABSPATH . 'wp-admin/includes/export.php' );

/**
 * Export settings to a file if all the security checks pass
 */
if ( ! empty( $_POST['mp-store-export'] ) ) { // Input var okay.
	if ( ! current_user_can( 'export' ) ) {
		return;
	}

	check_admin_referer( 'mp-store-export' );

	MP_Store_Settings_Import::download_export();
	die();
}

/**
 * Export products to a file if all the security checks pass
 */
if ( ! empty( $_POST['mp-store-export-products'] ) ) { // Input var oka.
	if ( ! current_user_can( 'export' ) ) {
		return;
	}

	check_admin_referer( 'mp-store-export' );

	$args['content'] = 'product';
	export_wp( $args );
	die();
}

class MP_Store_Settings_Import {
	/**
	 * Refers to a single instance of the class
	 *
	 * @since   3.2.3
	 * @access  private
	 * @var     object
	 */
	private static $_instance = null;

	/**
	 * Constructor function
	 *
	 * @since   3.2.3
	 * @access  private
	 */
	private function __construct() {

	}

	/**
	 * Gets the single instance of the class
	 *
	 * @since   3.2.3
	 * @access  public
	 * @return  object
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new MP_Store_Settings_Import();
		}
		return self::$_instance;
	}

	/**
	 * Gets the settings of the plugin
	 *
	 * Location settings, tax settings, currency settings, digital settings, download settings, miscellaneous settings
	 * and advanced settings.
	 *
	 * @since   3.2.3
	 * @access  private
	 * @param   string $option_name Where to find the plugin settings. Default 'mp_settings'.
	 * @return  string
	 */
	private static function get_settings( $option_name = 'mp_settings' ) {
		global $wpdb;

		$result = $wpdb->get_results( $wpdb->prepare( "
			SELECT option_value
			FROM $wpdb->options
			WHERE option_name = %s
		", $option_name ) );
		$settings = array_pop( $result );

		return $settings->option_value;
	}

	/**
	 * Download export file
	 *
	 * @since   3.2.3
	 * @access  public
	 */
	public static function download_export() {
		$sitename = sanitize_key( get_bloginfo( 'name' ) );
		if ( ! empty( $sitename ) ) {
			$sitename .= '.';
		}
		$date = date( 'Y-m-d' );
		$wp_filename = $sitename . 'marketpress.' . $date . '.xml';
		/**
		 * WordPress filter
		 *
		 * Filters the export filename.
		 *
		 * @since 4.4.0
		 *
		 * @param string $wp_filename The name of the file for download.
		 * @param string $sitename    The site name.
		 * @param string $date        Today's date, formatted.
		 */
		$filename = apply_filters( 'export_wp_filename', $wp_filename, $sitename, $date );

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

		echo self::get_settings();
	}

	/**
	 * Display import/export page
	 *
	 * @since   3.2.3
	 * @access  public
	 */
	public function display_settings() {
		?>
		<form method="post">
			<?php wp_nonce_field( 'mp-store-export' ) ?>

			<h2><?php esc_html_e( 'Import / Export Settings', 'mp' ); ?></h2>
			<p>
				<?php esc_html_e( 'Use the text below to export to a new installation. Or paste in the new configuration to import.', 'mp' ); ?>
			</p>
			<textarea title="mp-store-settings-text" cols="100" rows="10"><?php echo esc_textarea( $this->get_settings() ); ?></textarea><br>

			<input type="submit" class="button button-primary" name="mp-store-import" id="mp-store-import" value="<?php esc_attr_e( 'Import configuration', 'mp' ); ?>">
			<input type="submit" class="button" name="mp-store-export" id="mp-store-export" value="<?php esc_attr_e( 'Export settings to file', 'mp' ); ?>">
			<h2><?php esc_html_e( 'Import / Export Products', 'mp' ); ?></h2>
			<input type="submit" class="button" name="mp-store-export-products" id="mp-store-export-products" value="<?php esc_attr_e( 'Export products to file', 'mp' ); ?>">
		</form>
		<?php
	}


}

MP_Store_Settings_Import::get_instance();
