<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://cognitivedynamics.ai
 * @since      1.0.0
 *
 * @package    Ai_Image_Generator
 * @subpackage Ai_Image_Generator/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ai_Image_Generator
 * @subpackage Ai_Image_Generator/includes
 * @author     Cognitive Dynamics <contact@cognitivedynamics.ai>
 */
class Ai_Image_Generator {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ai_Image_Generator_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'AI_IMAGE_GENERATOR_VERSION' ) ) {
			$this->version = AI_IMAGE_GENERATOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ai-image-generator';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ai_Image_Generator_Loader. Orchestrates the hooks of the plugin.
	 * - Ai_Image_Generator_i18n. Defines internationalization functionality.
	 * - Ai_Image_Generator_Admin. Defines all hooks for the admin area.
	 * - Ai_Image_Generator_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for ecryption and decryption of the API key and other data.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/services/CgntvDnmcEncryption.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/controllers/ImageController.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ai-wp-cgntvdnmc-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ai-wp-cgntvdnmc-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ai-wp-cgntvdnmc-admin.php';

		$this->loader = new Ai_Image_Generator_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ai_Image_Generator_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ai_Image_Generator_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Ai_Image_Generator_Admin( $this->get_plugin_name(), $this->get_version() );
		$image_controller = new ImageController();
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		// $this->loader->add_action( 'print_media_templates', $plugin_admin, 'main_app_modal_template' );
		$this->loader->add_action( 'wp_enqueue_media', $plugin_admin, 'enqueue_ai_image_generator_on_enqueue_media' );

		$this->loader->add_action( 'wp_ajax_set_api_key', $plugin_admin, 'set_api_key' );
		$this->loader->add_action( 'wp_ajax_set_image_variations_settings', $plugin_admin, 'set_image_variations_settings' );
		
		$this->loader->add_action( 'wp_ajax_store_base64_image_in_media_library', $image_controller, 'store_base64_image_in_media_library' );
		$this->loader->add_action( 'wp_ajax_generate_image_variations', $image_controller, 'generate_image_variations' );
	
		$this->loader->add_action( 'wp_ajax_set_text_to_image_settings', $plugin_admin, 'set_text_to_image_settings' );
		$this->loader->add_action( 'wp_ajax_create_image_from_prompt', $image_controller, 'create_image_from_prompt' );
		
		aiwpcgntvdnmc_fs()->add_action('after_uninstall', 'aiwpcgntvdnmc_fs_uninstall_cleanup');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ai_Image_Generator_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
