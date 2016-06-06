<?php
/**
 * Plugin Name:     EDD Download Link Securtiy
 * Plugin URI:      https://wordpress.org/plugins/edd-download-link-security
 * Description:     Automatically adds extra security to download file links
 * Version:         1.0.0
 * Author:          rubengc
 * Author URI:      http://rubengc.com
 * Text Domain:     download-link-security
 *
 * @package         EDD\DownloadLinkSecurity
 * @author          rubengc
 * @copyright       Copyright (c) rubengc
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_Download_Link_Security' ) ) {

    /**
     * Main EDD_Download_Link_Security class
     *
     * @since       1.0.0
     */
    class EDD_Download_Link_Security {

        /**
         * @var         EDD_Download_Link_Security $instance The one true EDD_Download_Link_Security
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_Download_Link_Security
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_Download_Link_Security();
                self::$instance->setup_constants();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_DOWNLOAD_LINK_SECURITY_VER', '1.0.0' );

            // Plugin path
            define( 'EDD_DOWNLOAD_LINK_SECURITY_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_DOWNLOAD_LINK_SECURITY_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
            // Register settings
            add_filter( 'edd_settings_extensions', array( $this, 'settings' ), 1 );

            // Register a custom download verification process
            add_action( 'edd_process_verified_download', array( $this, 'process_verified_download' ), 10, 3 );
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_DOWNLOAD_LINK_SECURITY_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_download_link_security_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd-download-link-security' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-download-link-security', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-download-link-security/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-download-link-security/ folder
                load_textdomain( 'edd-download-link-security', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-download-link-security/languages/ folder
                load_textdomain( 'edd-download-link-security', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-download-link-security', false, $lang_dir );
            }
        }


        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param       array $settings The existing EDD settings array
         * @return      array The modified EDD settings array
         */
        public function settings( $settings ) {
            $new_settings = array(
                array(
                    'id'    => 'edd_download_link_security_settings',
                    'name'  => '<strong>' . __( 'Download Link Security', 'edd-download-link-security' ) . '</strong>',
                    'desc'  => __( 'Configure Download Link Security', 'edd-download-link-security' ),
                    'type'  => 'header',
                ),
                'edd_download_link_security_redirect_page' => array(
                    'id'          => 'edd_download_link_security_redirect_page',
                    'name'        => __( 'Redirect Page', 'edd-download-link-security' ),
                    'desc'        => __( 'Page where users are sent when download verification process fails.', 'edd-download-link-security' ),
                    'type'        => 'select',
                    'options'     => self::get_pages(),
                    'chosen'      => true,
                ),
            );

            return array_merge( $settings, $new_settings );
        }

        protected function get_pages() {
            $pages_options = array();
            $pages_options[-2] = __( 'Default wordpress error page', 'edd-download-link-security' );
            $pages_options[-1] = __( 'Download\'s page', 'edd-download-link-security' );

            $pages = get_pages();
            if ( $pages ) {
                foreach ( $pages as $page ) {
                    $pages_options[ $page->ID ] = $page->post_title;
                }
            }

            return $pages_options;
        }

        /**
         * Custom download verification process
         *
         * @access      public
         * @since       1.0.0
         * @param       integer $download_id The ID of the download
         * @param       string $email The email of the current logged user
         * @param       integer|boolean $payment_id The ID of the payment, if not exists then false
         * @return      wp_die|wp_redirect
         */
        public function process_verified_download( $download_id, $email, $payment_id ) {
            global $edd_options;

            $redirectPage = $edd_options['edd_download_link_security_redirect_page'] ? $edd_options['edd_download_link_security_redirect_page'] : -2 ;

            $current_user = wp_get_current_user();
            $payment = new EDD_Payment($payment_id);
            $download_failure = false;

            if( 0 == $current_user->ID ) { 
                // Checks if user is logged
                $download_failure = true;
                $message = 'You must be logged in to download files.';
            } else if( ! edd_has_user_purchased( $current_user->ID, $download_id ) ) { 
                // Checks if user has been purchased this item
                $download_failure = true;
                $message = 'You have not purchased this product.';
            } else if( $email !== $current_user->user_email ) { 
                // Checks if purchase email matches with user email
                $download_failure = true;
                $message = 'This download file is not for you.';
            } else if( 'publish' !== $payment->status ) { 
                // Checks if payment has accepted
                $download_failure = true;
                $message = 'Payment has not accepted yet.';
            }

            if( $download_failure ) {
                if($redirectPage == -2) { 
                    // Default redirect to wp_die
                     wp_die( __( $message, 'edd-download-link-security' ), 'Error' );
                } else if($redirectPage == -1) { 
                    // Redirects to download's page
                    wp_redirect( get_permalink( $download_id ));
                } else { 
                    // Redirects to page
                    wp_redirect( get_permalink( $redirectPage ));
                }
            }
        }
    }
} // End if class_exists check


/**
 * The main function responsible for returning the one true EDD_Download_Link_Security
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_Download_Link_Security The one true EDD_Download_Link_Security
 */
function edd_download_link_security() {
    return EDD_Download_Link_Security::instance();
}
add_action( 'plugins_loaded', 'edd_download_link_security' );
