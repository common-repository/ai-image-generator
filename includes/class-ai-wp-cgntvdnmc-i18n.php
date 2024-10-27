<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://cognitivedynamics.ai
 * @since      1.0.0
 *
 * @package    Ai_Image_Generator
 * @subpackage Ai_Image_Generator/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Ai_Image_Generator
 * @subpackage Ai_Image_Generator/includes
 * @author     Cognitive Dynamics <contact@cognitivedynamics.ai>
 */
class Ai_Image_Generator_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ai-wp-cgntvdnmc',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
