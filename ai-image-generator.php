<?php

/**
 * @wordpress-plugin
 * Plugin Name:       AI Image Generator
 * Plugin URI:        https://cognitivedynamics.ai/ai-image-generator/
 * Description:       This plugin uses OpenAI to generate images based on a text prompt or an image.
 * Version:           1.0.6
 * Author:            Cognitive Dynamics
 * Author URI:        https://cognitivedynamics.ai
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ai-wp-cgntvdnmc
 * Domain Path:       /languages
 * 
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
if ( function_exists( 'aiwpcgntvdnmc_fs' ) ) {
    aiwpcgntvdnmc_fs()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    if ( !function_exists( 'aiwpcgntvdnmc_fs' ) ) {
        if ( !function_exists( 'aiwpcgntvdnmc_fs' ) ) {
            // Create a helper function for easy SDK access.
            function aiwpcgntvdnmc_fs() {
                global $aiwpcgntvdnmc_fs;
                if ( !isset( $aiwpcgntvdnmc_fs ) ) {
                    // Include Freemius SDK.
                    require_once dirname( __FILE__ ) . '/freemius/start.php';
                    $aiwpcgntvdnmc_fs = fs_dynamic_init( array(
                        'id'             => '12017',
                        'slug'           => 'ai-image-generator',
                        'type'           => 'plugin',
                        'public_key'     => 'pk_b377f9d6f526b0e991c55aafc2f26',
                        'is_premium'     => false,
                        'premium_suffix' => 'Professional',
                        'has_addons'     => false,
                        'has_paid_plans' => true,
                        'trial'          => array(
                            'days'               => 7,
                            'is_require_payment' => true,
                        ),
                        'menu'           => array(
                            'slug'       => 'ai-wp-cgntvdnmc',
                            'first-path' => 'upload.php?page=ai-wp-cgntvdnmc',
                            'contact'    => false,
                            'support'    => false,
                            'parent'     => array(
                                'slug' => 'upload.php',
                            ),
                        ),
                        'is_live'        => true,
                    ) );
                }
                return $aiwpcgntvdnmc_fs;
            }

            // Init Freemius.
            aiwpcgntvdnmc_fs();
            // Signal that SDK was initiated.
            do_action( 'aiwpcgntvdnmc_fs_loaded' );
        }
    }
    /**
     * Currently plugin version.
     * Start at version 1.0.0 and use SemVer - https://semver.org
     * Rename this for your plugin and update it as you release new versions.
     */
    define( 'AI_IMAGE_GENERATOR_VERSION', '1.0.6' );
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-ai-wp-cgntvdnmc-activator.php
     */
    function activate_ai_image_generator() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-ai-wp-cgntvdnmc-activator.php';
        Ai_Image_Generator_Activator::activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-ai-wp-cgntvdnmc-deactivator.php
     */
    function deactivate_ai_image_generator() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-ai-wp-cgntvdnmc-deactivator.php';
        Ai_Image_Generator_Deactivator::deactivate();
    }

    register_activation_hook( __FILE__, 'activate_ai_image_generator' );
    register_deactivation_hook( __FILE__, 'deactivate_ai_image_generator' );
    require_once plugin_dir_path( __FILE__ ) . '/lib/autoload.php';
    //use Orhanerday\OpenAi\OpenAi;
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-ai-wp-cgntvdnmc.php';
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function run_ai_image_generator() {
        $plugin = new Ai_Image_Generator();
        $plugin->run();
    }

    run_ai_image_generator();
}