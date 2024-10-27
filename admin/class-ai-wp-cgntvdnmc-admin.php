<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cognitivedynamics.ai
 * @since      1.0.0
 *
 * @package    Ai_Image_Generator
 * @subpackage Ai_Image_Generator/admin
 * @author     Cognitive Dynamics <contact@cognitivedynamics.ai>
 */
class Ai_Image_Generator_Admin {
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

    /** @var CgntvDnmcEncryption $encryption */
    private $encryption;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->encryption = CgntvDnmcEncryption::getInstance();
    }

    function add_admin_page() {
        add_submenu_page(
            'upload.php',
            __( 'AI Image Generator', 'ai-wp-cgntvdnmc' ),
            __( 'AI Image Generator', 'ai-wp-cgntvdnmc' ),
            'manage_options',
            'ai-wp-cgntvdnmc',
            array($this, 'main_app')
        );
    }

    function main_app() {
        ?>
        <div id="ai-wp-cgntvdnmc-main-app" class="ai-wp-cgntvdnmc-main-app"></div>
    <?php 
    }

    function set_api_key() {
        check_ajax_referer( '_wpnonce', 'security' );
        if ( !current_user_can( 'manage_options' ) ) {
            return new WP_Error('unauthorized', __( 'You are not authorized to perform this action.', 'ai-wp-cgntvdnmc' ));
        }
        if ( isset( $_POST['ai_wp_cgntvdnmc_api_key'] ) && !empty( $_POST['ai_wp_cgntvdnmc_api_key'] ) ) {
            $apiKey = sanitize_text_field( $_POST['ai_wp_cgntvdnmc_api_key'] );
            $image_controller = new ImageController();
            $apiKeyIsValid = $image_controller->validate_api_key( $apiKey );
            if ( isset( $apiKeyIsValid['error'] ) ) {
                $error = new WP_Error($apiKeyIsValid['error']['code'], $apiKeyIsValid['error']['message']);
                wp_send_json_error( $error );
            }
            update_option( 'ai_wp_cgntvdnmc_api_key', CgntvDnmcEncryption::getInstance()->encrypt( $apiKey ) );
            wp_send_json_success();
        } else {
            $error = new WP_Error('Error', __( 'API key cannot be empty', 'ai-wp-cgntvdnmc' ));
            wp_send_json_error( $error );
        }
    }

    function set_image_variations_settings() {
        check_ajax_referer( '_wpnonce', 'security' );
        if ( !current_user_can( 'manage_options' ) ) {
            return new WP_Error('unauthorized', __( 'You are not authorized to perform this action.', 'ai-wp-cgntvdnmc' ));
        }
        $errors = new WP_Error();
        $response = array(
            'success' => false,
        );
        if ( isset( $_POST['variationsResolution'] ) ) {
            $resolution = sanitize_text_field( $_POST['variationsResolution'] );
            if ( !in_array( $resolution, array('256x256', '512x512', '1024x1024') ) ) {
                $errors->add( 'invalid_resolution', __( 'Invalid image resolution selected.', 'ai-wp-cgntvdnmc' ) );
            } else {
                update_option( 'ai_wp_cgntvdnmc_resolution', $resolution );
            }
        }
        if ( isset( $_POST['noOfVariations'] ) ) {
            $no_of_variations = intval( $_POST['noOfVariations'] );
            if ( $no_of_variations < 1 || $no_of_variations > 10 ) {
                $errors->add( 'invalid_variations_number', __( 'Invalid number of image variations selected.', 'ai-wp-cgntvdnmc' ) );
            } else {
                update_option( 'ai_wp_cgntvdnmc_variations_num', $no_of_variations );
            }
        }
        if ( is_wp_error( $errors ) && count( $errors->get_error_codes() ) > 0 ) {
            $response['data'] = array(
                'success' => false,
                'message' => $errors->get_error_message(),
            );
        } else {
            $response['success'] = true;
            $response['data'] = array(
                'success' => true,
                'message' => __( 'Image variations settings saved successfully.' ),
            );
        }
        wp_send_json( $response );
    }

    function set_text_to_image_settings() {
        check_ajax_referer( '_wpnonce', 'security' );
        if ( !current_user_can( 'manage_options' ) ) {
            return new WP_Error('unauthorized', __( 'You are not authorized to perform this action.', 'ai-wp-cgntvdnmc' ));
        }
        $errors = new WP_Error();
        $response = array(
            'success' => false,
        );
        $acceptedResolutions = array('256x256', '512x512', '1024x1024');
        $acceptedQuality = array('standard');
        if ( isset( $_POST['textToImageResolution'] ) ) {
            $resolution = sanitize_text_field( $_POST['textToImageResolution'] );
            if ( !in_array( $resolution, $acceptedResolutions ) ) {
                $errors->add( 'invalid_resolution', __( 'Invalid image resolution selected.', 'ai-wp-cgntvdnmc' ) );
            } else {
                update_option( 'ai_wp_cgntvdnmc_text_to_image_resolution', $resolution );
            }
        }
        if ( isset( $_POST['chosenAiModel'] ) ) {
            $chosenAiModel = sanitize_text_field( $_POST['chosenAiModel'] );
            if ( !in_array( $chosenAiModel, array('dall-e-2', 'dall-e-3') ) ) {
                $errors->add( 'invalid_model', __( 'Invalid AI model selected.', 'ai-wp-cgntvdnmc' ) );
            } else {
                update_option( 'ai_wp_cgntvdnmc_chosen_ai_model', $chosenAiModel );
            }
        }
        if ( isset( $_POST['textToImageQuality'] ) ) {
            $textToImageQuality = sanitize_text_field( $_POST['textToImageQuality'] );
            if ( !in_array( $textToImageQuality, array('standard', 'hd') ) ) {
                $errors->add( 'invalid_quality', __( 'Invalid quality selected.', 'ai-wp-cgntvdnmc' ) );
            } else {
                update_option( 'ai_wp_cgntvdnmc_text_to_image_quality', $textToImageQuality );
            }
        }
        if ( isset( $_POST['textToImageStyle'] ) ) {
            $textToImageStyle = sanitize_text_field( $_POST['textToImageStyle'] );
            if ( !in_array( $textToImageStyle, array('vivid', 'natural') ) ) {
                $errors->add( 'invalid_style', __( 'Invalid style selected.', 'ai-wp-cgntvdnmc' ) );
            } else {
                update_option( 'ai_wp_cgntvdnmc_text_to_image_style', $textToImageStyle );
            }
        }
        if ( isset( $_POST['textToImageResults'] ) ) {
            $noOfResults = intval( $_POST['textToImageResults'] );
            if ( $noOfResults < 1 || $noOfResults > 10 ) {
                $errors->add( 'invalid_variations_number', __( 'Invalid number of image variations selected.', 'ai-wp-cgntvdnmc' ) );
            } else {
                update_option( 'ai_wp_cgntvdnmc_text_to_image_results', $noOfResults );
            }
        }
        if ( is_wp_error( $errors ) && count( $errors->get_error_codes() ) > 0 ) {
            $response['data'] = array(
                'success' => false,
                'message' => $errors->get_error_message(),
            );
        } else {
            $response['success'] = true;
            $response['data'] = array(
                'success' => true,
                'message' => __( 'Image variations settings saved successfully.' ),
            );
        }
        wp_send_json( $response );
    }

    /**
     * Register the plugin settings.
     */
    public function register_settings() {
        register_setting( 'ai_wp_cgntvdnmc', 'ai_wp_cgntvdnmc_settings', array($this, 'sanitize_options') );
    }

    /**
     * Sanitize the plugin options.
     *
     * @param array $input The raw input data.
     * @return array The sanitized input data.
     */
    public function sanitize_options( $input ) {
        $output = array();
        if ( isset( $input['ai_wp_cgntvdnmc_api_key'] ) ) {
            $output['ai_wp_cgntvdnmc_api_key'] = sanitize_text_field( $input['ai_wp_cgntvdnmc_api_key'] );
        }
        if ( isset( $input['ai_wp_cgntvdnmc_resolution'] ) ) {
            $output['ai_wp_cgntvdnmc_resolution'] = sanitize_text_field( $input['ai_wp_cgntvdnmc_resolution'] );
        }
        if ( isset( $input['ai_wp_cgntvdnmc_variations_num'] ) ) {
            $output['ai_wp_cgntvdnmc_variations_num'] = sanitize_text_field( intval( $input['ai_wp_cgntvdnmc_variations_num'] ) );
        }
        if ( isset( $input['ai_wp_cgntvdnmc_text_to_image_resolution'] ) ) {
            $output['ai_wp_cgntvdnmc_text_to_image_resolution'] = sanitize_text_field( $input['ai_wp_cgntvdnmc_text_to_image_resolution'] );
        }
        if ( isset( $input['ai_wp_cgntvdnmc_text_to_image_results'] ) ) {
            $output['ai_wp_cgntvdnmc_text_to_image_results'] = sanitize_text_field( intval( $input['ai_wp_cgntvdnmc_text_to_image_results'] ) );
        }
        return $output;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Ai_Image_Generator_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Ai_Image_Generator_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        // wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ai-wp-cgntvdnmc-admin.css', array(), $this->version, 'all');
    }

    function enqueue_ai_image_generator_on_enqueue_media() {
        $currentScreenBase = null;
        if ( function_exists( 'get_current_screen' ) ) {
            $currentScreen = get_current_screen();
            $currentScreenBase = $currentScreen->base;
        }
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/ai-wp-cgntvdnmc-admin.css',
            array(),
            $this->version,
            'all'
        );
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/ai-wp-cgntvdnmc-admin.js',
            array('jquery'),
            $this->version,
            false
        );
        $apiIsSet = ( !empty( get_option( 'ai_wp_cgntvdnmc_api_key', '' ) ) ? true : false );
        $options = array(
            'security'              => wp_create_nonce( '_wpnonce' ),
            'apiIsSet'              => $apiIsSet,
            'pluginDirUrl'          => plugin_dir_url( __DIR__ ),
            'isPremium'             => json_encode( aiwpcgntvdnmc_fs()->can_use_premium_code() ),
            'upgradeUrl'            => aiwpcgntvdnmc_fs()->get_upgrade_url(),
            'trialUrl'              => aiwpcgntvdnmc_fs()->get_trial_url(),
            'variationsResolution'  => get_option( 'ai_wp_cgntvdnmc_resolution', '' ),
            'chosenAiModel'         => get_option( 'ai_wp_cgntvdnmc_chosen_ai_model', '' ),
            'textToImageQuality'    => get_option( 'ai_wp_cgntvdnmc_text_to_image_quality', '' ),
            'noOfVariations'        => get_option( 'ai_wp_cgntvdnmc_variations_num', '' ),
            'textToImageResolution' => get_option( 'ai_wp_cgntvdnmc_text_to_image_resolution', '' ),
            'textToImageResults'    => get_option( 'ai_wp_cgntvdnmc_text_to_image_results', '' ),
            'currentScreen'         => $currentScreenBase,
        );
        wp_enqueue_script(
            'ai-wp-cgntvdnmc-react',
            plugin_dir_url( __DIR__ ) . 'dist/ai-wp-cgntvdnmc-react.js',
            array('wp-element', 'wp-api'),
            $this->version,
            false
        );
        wp_localize_script( 'ai-wp-cgntvdnmc-react', 'ai_wp_cgntvdnmc_object', $options );
        wp_add_inline_script( 'ai-wp-cgntvdnmc-react', 'const ai_wp_cgntvdnmcd = ' . json_encode( $options ), 'before' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Ai_Image_Generator_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Ai_Image_Generator_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $currentScreenBase = null;
        $currentScreenId = null;
        if ( function_exists( 'get_current_screen' ) ) {
            $currentScreen = get_current_screen();
            $currentScreenBase = $currentScreen->base;
            $currentScreenId = $currentScreen->id;
        }
        if ( $currentScreenBase === 'media_page_ai-wp-cgntvdnmc' ) {
            wp_enqueue_media();
        }
        if ( $currentScreenId === 'upload' ) {
            wp_enqueue_script(
                'ai-wp-cgntvdnmc-media-library',
                plugin_dir_url( __DIR__ ) . 'admin/js/ai-wp-cgntvdnmc-media-library.js',
                array('jquery'),
                $this->version,
                true
            );
            wp_localize_script( 'ai-wp-cgntvdnmc-media-library', 'cgntvdnmc_media_library', array(
                'buttonTitle' => __( 'Generate with AI', 'ai-wp-cgntvdnmc' ),
            ) );
        }
    }

}
