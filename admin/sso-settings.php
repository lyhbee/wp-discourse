<?php
/**
 * SSO Settings.
 *
 * @package WPDiscourse
 */

namespace WPDiscourse\Admin;

use WPDiscourse\Utilities\Utilities as DiscourseUtilities;

/**
 * Class SSOSettings
 */
class SSOSettings {

	/**
	 * An instance of the FormHelper class.
	 *
	 * @access protected
	 * @var \WPDiscourse\Admin\FormHelper
	 */
	protected $form_helper;

	/**
	 * Gives access to the plugin options.
	 *
	 * @access protected
	 * @var mixed|void
	 */
	protected $options;

	protected $discourse_sso_settings_url;

	/**
	 * SSOSettings constructor.
	 *
	 * @param \WPDiscourse\Admin\FormHelper $form_helper An instance of the FormHelper class.
	 */
	public function __construct( $form_helper ) {
		$this->form_helper = $form_helper;

		add_action( 'admin_init', array( $this, 'register_sso_settings' ) );
		add_action( 'wpdc_options_page_append_settings_tabs', array( $this, 'sso_settings_secondary_tabs' ), 10, 2 );
		add_action( 'wpdc_options_page_after_tab_switch', array( $this, 'sso_settings_fields' ) );
	}

	public function sso_settings_fields( $tab ) {
		if ( 'sso_common' === $tab || 'sso_options' === $tab ) {
			settings_fields( 'discourse_sso_common' );
			do_settings_sections( 'discourse_sso_common' );
		}
		if ( 'sso_provider' === $tab ) {
			settings_fields( 'discourse_sso_provider' );
			do_settings_sections( 'discourse_sso_provider' );
		}
		if ( 'sso_client' === $tab ) {
			settings_fields( 'discourse_sso_client' );
			do_settings_sections( 'discourse_sso_client' );
		}
	}


	public function sso_settings_secondary_tabs( $tab, $parent_tab ) {
		if ( 'sso_options' === $tab || 'sso_options' === $parent_tab ) {
			?>
            <h3 class="nav-tab-wrapper nav-tab-second-level">
                <a href="?page=wp_discourse_options&tab=sso_common&parent_tab=sso_options"
                   class="nav-tab <?php echo 'sso_common' === $tab || 'sso_options' === $tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Common Options', 'wpdc' ); ?>
                </a>
                <a href="?page=wp_discourse_options&tab=sso_provider&parent_tab=sso_options"
                   class="nav-tab <?php echo 'sso_provider' === $tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'SSO Provider', 'wpdc' ); ?>
                </a>
                <a href="?page=wp_discourse_options&tab=sso_client&parent_tab=sso_options"
                   class="nav-tab <?php echo 'sso_client' === $tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'SSO Client', 'wpdc' ); ?>
                </a>
            </h3>
			<?php

		}
	}

	/**
	 * Add settings section, settings fields, and register the setting.
	 */
	public function register_sso_settings() {
		$this->options = DiscourseUtilities::get_options();

		$this->discourse_sso_settings_url = ! empty( $this->options['url'] ) ? $this->options['url'] . '/admin/site_settings/category/all_results?filter=sso' : null;

		add_settings_section( 'discourse_sso_common_settings_section', __( 'Common Settings', 'wp-discourse' ), array(
			$this,
			'common_settings_details',
		), 'discourse_sso_common' );

		add_settings_field( 'discourse_sso_secret', __( 'SSO Secret Key', 'wp-discourse' ), array(
			$this,
			'sso_secret_input',
		), 'discourse_sso_common', 'discourse_sso_common_settings_section' );

		register_setting( 'discourse_sso_common', 'discourse_sso_common', array(
			$this->form_helper,
			'validate_options'
		) );

		add_settings_section( 'discourse_sso_provider_settings_section', __( 'SSO Provider Settings', 'wp-discourse' ), array(
			$this,
			'sso_provider_settings_details',
		), 'discourse_sso_provider' );

		add_settings_field( 'discourse_enable_sso', __( 'Enable SSO Provider', 'wp-discourse' ), array(
			$this,
			'enable_sso_provider_checkbox',
		), 'discourse_sso_provider', 'discourse_sso_provider_settings_section' );

		add_settings_field( 'auto_create_sso_user', __( 'Create Discourse User on Login', 'wp-discourse' ), array(
			$this,
			'auto_create_sso_user_checkbox',
		), 'discourse_sso_provider', 'discourse_sso_provider_settings_section' );

		add_settings_field( 'auto_create_login_redirect', __( 'Redirect After Discourse Login', 'wp-discourse' ), array(
			$this,
			'auto_create_login_redirect_input',
		), 'discourse_sso_provider', 'discourse_sso_provider_settings_section' );

		add_settings_field( 'auto_create_welcome_redirect', __( 'New User Redirect', 'wp-discourse' ), array(
			$this,
			'auto_create_welcome_redirect',
		), 'discourse_sso_provider', 'discourse_sso_provider_settings_section' );

		add_settings_field( 'discourse_wp_login_path', __( 'Path to your login page', 'wp-discourse' ), array(
			$this,
			'wordpress_login_path',
		), 'discourse_sso_provider', 'discourse_sso_provider_settings_section' );

		add_settings_field( 'discourse_redirect_without_login', __( 'Redirect Without Login', 'wp-discourse' ), array(
			$this,
			'redirect_without_login_checkbox',
		), 'discourse_sso_provider', 'discourse_sso_provider_settings_section' );

		register_setting( 'discourse_sso_provider', 'discourse_sso_provider', array(
			$this->form_helper,
			'validate_options'
		) );

		add_settings_section( 'discourse_sso_client_settings_section', __( 'SSO Client Settings Section', 'wp-discourse' ), array(
			$this,
			'sso_client_settings_details',
		), 'discourse_sso_client' );

		add_settings_field( 'discourse_enable_discourse_sso', __( 'Enable SSO Client', 'wp-discourse' ), array(
			$this,
			'enable_sso_client_checkbox',
		), 'discourse_sso_client', 'discourse_sso_client_settings_section' );

		add_settings_field( 'enable_discourse_sso_login_form_change', __( 'Add "Login with Discourse" to the Login Form', 'wp-discourse' ), array(
			$this,
			'enable_discourse_sso_login_form_change_checkbox',
		), 'discourse_sso_client', 'discourse_sso_client_settings_section' );

		add_settings_field( 'discourse_enable_sso_sync', __( 'Sync Existing Users by Email', 'wp-discourse' ), array(
			$this,
			'sso_client_sync_by_email_checkbox',
		), 'discourse_sso_client', 'discourse_sso_client_settings_section' );

		register_setting( 'discourse_sso_client', 'discourse_sso_client', array(
			$this->form_helper,
			'validate_options'
		) );

//		register_setting( 'discourse_sso', 'discourse_sso', array(
//			$this->form_helper,
//			'validate_options',
//		) );
	}

	/**
	 * Common SSO settings fields.
	 */

	/**
	 * Outputs markup for the sso-secret input.
	 */
	public function sso_secret_input() {
		if ( ! empty( $this->discourse_sso_settings_url ) ) {
			$this->form_helper->input( 'sso-secret', 'discourse_sso_common', __( "A string of text, at least 10 characters long. Set the same key on your forum at ", 'wp-discourse' ) .
			                                                                 '<a href="' . esc_url( $this->discourse_sso_settings_url ) . '" target="_blank">' . esc_url( $this->discourse_sso_settings_url ) . '</a>' );
		} else {
			$forum_url = 'http://discourse.example.com/admin/site_settings/category/login';
			$this->form_helper->input( 'sso-secret', 'discourse_sso_common', __( "A string of text, at least 10 characters long. Set the same key on your forum at ", 'wp-discourse' ) .
			                                                                 esc_url( $forum_url ) );
		}
	}

	/**
	 * SSO Provider settings fields.
	 */

	/**
	 * Outputs markup for the enable-sso checkbox.
	 */
	public function enable_sso_provider_checkbox() {
		$description = __( 'Use this WordPress instance as the SSO provider for your Discourse forum.', 'wp-discourse' );
		$this->form_helper->checkbox_input( 'enable-sso', 'discourse_sso_provider', $description );
	}

	/**
	 * Outputs markup for the auto-create-sso-user checkbox.
	 */
	public function auto_create_sso_user_checkbox() {
		$description = __( "Automatically login users to Discourse when they login to your WordPress site. If the user does not yet
	    exist on Discourse, a new user will be created using their WordPress credentials. For this setting to work, you must enable the Discourse setting 'enable all return paths.'", 'wp-discourse' );
		$this->form_helper->checkbox_input( 'auto-create-sso-user', 'discourse_sso_provider', __( 'Automatically create and login users.', 'wp-discourse' ), $description );
	}

	/**
	 * Outputs markup for the auto-create-login-redirect input.
	 */
	public function auto_create_login_redirect_input() {
		$description = __( "This setting is only used when 'Create Discourse User on Login' is enabled. It sets the WordPress page
		to which users will be redirected after they are logged into Discourse. Enter a path on your site starting with '/'.
		If this setting is left blank, users will be redirected back to your homepage.", 'wp-discourse' );
		$this->form_helper->input( 'auto-create-login-redirect', 'discourse_sso_provider', $description );
	}

	/**
	 * Outputs markup for the auto-create-welcome-redirect input.
	 */
	public function auto_create_welcome_redirect() {
		$description = __( "This setting is only used when 'Create Discourse User on Login' is enabled. It sets an optional
		page on your WordPress site to which users will be redirected when their Discourse account if first created.
		Enter a path on your site starting with '/'. If this setting is left blank, users will be redirected to the
		'Redirect After Discourse Login' path.", 'wp-discourse' );
		$this->form_helper->input( 'auto-create-welcome-redirect', 'discourse_sso_provider', $description );
	}

	/**
	 * Outputs markup for the login-path input.
	 */
	public function wordpress_login_path() {
		$this->form_helper->input( 'login-path', 'discourse_sso_provider', __( "(Optional) If your site doesn't use the
		default WordPress login page at '/wp-login.php', you can set the path to your login page here. 
		It should start with '/'. Leave blank to use the default WordPress login page.", 'wp-discourse' ) );
	}


	/**
	 * Outputs markup for the redirect-without-login checkbox.
	 */
	public function redirect_without_login_checkbox() {
	    $description = __( "By default, when using WordPress as the SSO provider, the links to the Discourse comments automatically log
	    the user into Discourse. Select this setting to link to Discourse without loggin in the user.");
		$this->form_helper->checkbox_input( 'redirect-without-login', 'discourse_sso_provider', __( 'Do not force login for link to Discourse comments.' ), $description );
	}

	/**
	 * SSO Client settings fields.
	 */

	/**
	 * Outputs markup for sso-client-enabled checkbox.
	 */
	public function enable_sso_client_checkbox() {
		$description = __( 'Use your Discourse instance as an SSO provider for your WordPress site.
		To use this functionality, you must fill SSO Secret key field. (Currently, not working with multisite installations.)', 'wp-discourse' );
		$this->form_helper->checkbox_input( 'sso-client-enabled', 'discourse_sso_client', __( 'Enable SSO client.', 'wp-discourse' ), $description );
	}

	/**
	 * Outputs markup for sso-client-login-form-change
	 */
	public function enable_discourse_sso_login_form_change_checkbox() {
		$this->form_helper->checkbox_input( 'sso-client-login-form-change', 'discourse_sso_client', __( 'Add login link.', 'wp-discourse' ), __( 'When using Discourse as the SSO provider for your site, 
		enabling this setting will add a "Login with Discourse" link to your WordPress login form.', 'wp-discourse' ) );
	}

	/**
	 * Outputs markup for sso-client-sync-by-email checkbox.
	 */
	public function sso_client_sync_by_email_checkbox() {
		$this->form_helper->checkbox_input( 'sso-client-sync-by-email', 'discourse_sso_client', __( 'Sync existing users.', 'wp-discourse' ), __( "When using Discourse as the SSO provider for your site,
	    enabling this setting will sync existing accounts based on the user's email address.", 'wp-discourse' ) );
	}

	/**
	 * Details for the 'sso_options' tab.
	 */

	public function common_settings_details() {
		?>
        <p class="documentation-link">
            <em>
				<?php esc_html_e( "Your WordPress site can be used as either the SSO provider, or as an SSO client with your
				Discourse forum. When used as the SSO provider, all user management for your forum will be handled through WordPress.
				When used as an SSO client, user management for your WordPress site will be able to be handled through your forum.", 'wp-discourse' ); ?>
            </em>
        </p>
        <p class="documentation-link">
            <em>
				<?php esc_html_e( "All SSO functionality requires you to create a secret key that is shared between your forum
                and your website. Set the secret key both on this page and on your forum before enabling SSO.", 'wp-discourse' ); ?>
            </em>
        </p>
		<?php
	}

	public function sso_provider_settings_details() {
		?>
        <p class="documentation-link">
            <em>
				<?php esc_html_e( "Enabling your site to function as the SSO provider transfers all user management from
				Discourse to WordPress. To use this functionality requires some configuration. On your forum, you need to:", 'wp-discourse' ); ?>
            </em>
        </p>
        <ul class="wpdc-documentation-list">
            <li>
				<?php esc_html_e( "select the 'enable sso' setting", 'wp-discourse' ); ?>
            </li>
            <li>
				<?php esc_html_e( "add the base URL of your site (for example 'http://mysite.com') to the 'sso url' setting", 'wp-discourse' ); ?>
            </li>
            <li>
				<?php esc_html_e( "make sure that the 'sso secret' has been set, and that it's value matches the 'SSO Secret Key' setting on your WordPress site", 'wp-discourse' ); ?>
            </li>
        </ul>
		<?php if ( $this->discourse_sso_settings_url ) : ?>
            <p class="documentation-link">
                <em>
					<?php esc_html_e( "You can find your forum's SSO settings ", 'wp-discourse' ); ?>
                    <a href="<?php echo esc_url( $this->discourse_sso_settings_url ); ?>"
                       target="_blank"><?php esc_html_e( 'here', 'wp-discourse' ); ?></a>
                </em>
            </p>
		<?php endif; ?>
		<?php
	}

	public function sso_client_settings_details() {
		?>
        <p class="documentation-link">
            <em>
				<?php esc_html_e( "Enabling your site to function as an SSO client allows user authentication to be handled
                through either your Discourse forum, or your WordPress site.", 'wp-discourse' ); ?>
            </em>
        </p>
		<?php
	}
}
