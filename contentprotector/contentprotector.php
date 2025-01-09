<?php
/*
Plugin Name: ContentProtector
Description: Protects pages or posts with a password using shortcodes. Includes a settings page for managing functionality.
Version: 1.0
Author: Anton Simonov
License: GPL2
Text Domain: contentprotector
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ContentProtector {

    public function __construct() {
        add_shortcode( 'cpwp_protect', array( $this, 'cpwp_protect_shortcode' ) );
        add_shortcode( 'cpwp_protect_content', array( $this, 'cpwp_protect_content_shortcode' ) );
        add_filter( 'the_content', array( $this, 'cpwp_protect_content' ) );
        add_action( 'admin_menu', array( $this, 'cpwp_add_menu' ) );
        add_action( 'admin_init', array( $this, 'cpwp_settings_init' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'cpwp_enqueue_styles' ) );
    }

    public function cpwp_enqueue_styles() {
        wp_enqueue_style( 'cpwp_styles', plugin_dir_url( __FILE__ ) . 'css/contentprotector.css', array(), '1.0' );
    }

    public function cpwp_protect_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'password' => '',
        ), $atts, 'cpwp_protect' );

        if ( ! empty( $atts['password'] ) ) {
            global $post;
            if ( $post ) {
                update_post_meta( $post->ID, '_cpwp_password', sanitize_text_field( $atts['password'] ) );
            }
        }

        return '';
    }

    public function cpwp_protect_content_shortcode( $atts, $content = null ) {
        $atts = shortcode_atts( array(
            'password' => '',
        ), $atts, 'cpwp_protect_content' );

        if ( empty( $atts['password'] ) ) {
            return $content;
        }

        $password = sanitize_text_field( $atts['password'] );

        if ( isset( $_POST['cpwp_submit_password_content'] ) ) {
            $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
            if ( wp_verify_nonce( $nonce, 'cpwp_password_content_form' ) ) {
                if ( isset( $_POST['cpwp_password'] ) && ! empty( $_POST['cpwp_password'] ) ) {
                    $entered_password = sanitize_text_field( wp_unslash( $_POST['cpwp_password'] ) );
                    if ( $entered_password === $password ) {
                        setcookie( 'cpwp_pass_content_' . md5( $content ), $password, time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
                        return $content;
                    } else {
                        $error = '<p class="cpwp-error">Incorrect password. Please try again.</p>';
                    }
                }
            }
        }

        if ( isset( $_COOKIE['cpwp_pass_content_' . md5( $content )] ) && $_COOKIE['cpwp_pass_content_' . md5( $content )] === $password ) {
            return $content;
        }

        $form = '<div class="cpwp-protection-block">';
        $form .= '<h2>Protected Content</h2>';
        if ( isset( $error ) ) {
            $form .= $error;
        }
        $form .= '<form method="post" class="cpwp-password-form">';
        $form .= wp_nonce_field( 'cpwp_password_content_form', '_wpnonce', true, false );
        $form .= '<p>Please enter the password to view this content:</p>';
        $form .= '<p><input type="password" name="cpwp_password" required /></p>';
        $form .= '<p><input type="submit" name="cpwp_submit_password_content" value="Submit" /></p>';
        $form .= '</form>';
        $form .= '</div>';

        return $form;
    }

    public function cpwp_protect_content( $content ) {
        $global_protection = get_option( 'cpwp_enable_global_protection', false );

        if ( ! $global_protection && has_shortcode( $content, 'cpwp_protect' ) ) {
            global $post;
            $password = get_post_meta( $post->ID, '_cpwp_password', true );

            if ( ! empty( $password ) ) {
                if ( ! isset( $_COOKIE['cpwp_pass_' . $post->ID] ) || $_COOKIE['cpwp_pass_' . $post->ID] !== $password ) {
                    return $this->cpwp_password_form( $password );
                }
            }
        }

        if ( $global_protection && ( is_single() || is_page() ) ) {
            global $post;
            $password = get_post_meta( $post->ID, '_cpwp_password', true );

            if ( empty( $password ) ) {
                $password = get_option( 'cpwp_global_password', '' );
            }

            if ( ! empty( $password ) ) {
                if ( ! isset( $_COOKIE['cpwp_pass_' . $post->ID] ) || $_COOKIE['cpwp_pass_' . $post->ID] !== $password ) {
                    return $this->cpwp_password_form( $password );
                }
            }
        }

        return $content;
    }

    private function cpwp_password_form( $password ) {
        if ( isset( $_POST['cpwp_submit_password'] ) ) {
            $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
            if ( ! wp_verify_nonce( $nonce, 'cpwp_password_form' ) ) {
                wp_die( 'Nonce verification failed!' );
            }

            if ( isset( $_POST['cpwp_password'] ) && ! empty( $_POST['cpwp_password'] ) ) {
                $entered_password = sanitize_text_field( wp_unslash( $_POST['cpwp_password'] ) );
                if ( $entered_password === $password ) {
                    setcookie( 'cpwp_pass_' . get_the_ID(), $password, time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
                    wp_redirect( get_permalink() );
                    exit;
                } else {
                    $error = '<p class="cpwp-error">Incorrect password. Please try again.</p>';
                }
            }
        }

        $form = '<div class="cpwp-protection-block">';
        $form .= '<h2>Content Protected</h2>';
        if ( isset( $error ) ) {
            $form .= $error;
        }
        $form .= '<form method="post" class="cpwp-password-form">';
        $form .= wp_nonce_field( 'cpwp_password_form', '_wpnonce', true, false );
        $form .= '<p>Please enter the password to view this content:</p>';
        $form .= '<p><input type="password" name="cpwp_password" required /></p>';
        $form .= '<p><input type="submit" name="cpwp_submit_password" value="Submit" /></p>';
        $form .= '</form>';
        $form .= '</div>';

        return $form;
    }

    public function cpwp_add_menu() {
        add_menu_page(
            'ContentProtector',
            'ContentProtector',
            'manage_options',
            'contentprotector',
            array( $this, 'cpwp_options_page' ),
            'dashicons-lock',
            25
        );
    }

    public function cpwp_settings_init() {
        register_setting(
            'cpwp_settings_group',
            'cpwp_enable_global_protection',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
            )
        );

        register_setting(
            'cpwp_settings_group',
            'cpwp_global_password',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            )
        );

        add_settings_section(
            'cpwp_settings_section',
            'Usage:',
            array( $this, 'cpwp_settings_section_callback' ),
            'contentprotector'
        );

        add_settings_field(
            'cpwp_enable_global_protection',
            'Enable Global Protection',
            array( $this, 'cpwp_enable_global_protection_render' ),
            'contentprotector',
            'cpwp_settings_section'
        );

        add_settings_field(
            'cpwp_global_password',
            'Global Password',
            array( $this, 'cpwp_global_password_render' ),
            'contentprotector',
            'cpwp_settings_section'
        );
    }

    public function cpwp_enable_global_protection_render() {
        $option = get_option( 'cpwp_enable_global_protection' );
        echo '<input type="checkbox" name="cpwp_enable_global_protection" value="1" ' . checked( 1, $option, false ) . ' />';
    }

    public function cpwp_global_password_render() {
        $option = get_option( 'cpwp_global_password' );
        echo '<input type="password" name="cpwp_global_password" value="' . esc_attr( $option ) . '" />';
        echo '<p class="description">Set a global password for all protected posts and pages.</p>';
    }

    public function cpwp_settings_section_callback() {
        echo '<p><strong>Use the following shortcodes to protect your content:</strong></p>';
        echo '<ul>';
        echo '<li><code>[cpwp_protect password="your_password"]</code>: Protect an entire page or post using the specified password.</li>';
        echo '<li><code>[cpwp_protect_content password="your_password"]</code>: Protect a specific portion of your content. Example:</li>';
        echo '<pre>[cpwp_protect_content password="123"]This content is protected by a password.[/cpwp_protect_content]</pre>';
        echo '</ul>';
    }

    public function cpwp_options_page() {
        ?>
        <div class="wrap">
            <h1>ContentProtector Settings</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'cpwp_settings_group' );
                do_settings_sections( 'contentprotector' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

}

new ContentProtector();