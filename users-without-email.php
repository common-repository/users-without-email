<?php
/*
   Plugin Name: Users Without Email
   Plugin URI: http://wordpress.org/extend/plugins/users-without-email/
   Version: 1.0
   Author: <a href="http://tesladethray.com">Sara McCutcheon</a>
   Description: Allows users to sign up without needing to enter an email address
   Text Domain: users-without-email
   License: GPLv3
  */

$UsersWithoutEmail_minimalRequiredPhpVersion = '5.0';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function UsersWithoutEmail_noticePhpVersionWrong() {
  global $UsersWithoutEmail_minimalRequiredPhpVersion;
  echo '<div class="updated fade">' .
    __('Error: plugin "Users Without Email" requires a newer version of PHP to be running.',  'users-without-email').
      '<br/>' . __('Minimal version of PHP required: ', 'users-without-email') . '<strong>' . $UsersWithoutEmail_minimalRequiredPhpVersion . '</strong>' .
      '<br/>' . __('Your server\'s PHP version: ', 'users-without-email') . '<strong>' . phpversion() . '</strong>' .
     '</div>';
}


function UsersWithoutEmail_PhpVersionCheck() {
  global $UsersWithoutEmail_minimalRequiredPhpVersion;
  if (version_compare(phpversion(), $UsersWithoutEmail_minimalRequiredPhpVersion) < 0) {
    add_action('admin_notices', 'UsersWithoutEmail_noticePhpVersionWrong');
    return false;
  }
  return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *    http://codex.wordpress.org/I18n_for_WordPress_Developers
 *    http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
function UsersWithoutEmail_i18n_init() {
  $pluginDir = dirname(plugin_basename(__FILE__));
  load_plugin_textdomain('users-without-email', false, $pluginDir . '/languages/');
}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// First initialize i18n
UsersWithoutEmail_i18n_init();


// Next, run the version check.
// If it is successful, continue with initialization for this plugin
if (UsersWithoutEmail_PhpVersionCheck()) {
  // Only load and run the init function if we know PHP version can parse it
  include_once('users-without-email_init.php');
  UsersWithoutEmail_init(__FILE__);
}
