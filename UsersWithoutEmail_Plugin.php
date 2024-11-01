<?php

include_once('UsersWithoutEmail_LifeCycle.php');

class UsersWithoutEmail_Plugin extends UsersWithoutEmail_LifeCycle {

  /**
   * @return array of option meta data.
   */
  public function getOptionMetaData() {
    return array(
      //'recaptcha_site_key' => array('reCAPTCHA Site Key')
    );
  }

//  public function getOptionValueI18nString($optionValue) {
//    $i18nValue = parent::getOptionValueI18nString($optionValue);
//    return $i18nValue;
//  }

  public function initOptions() {
    $options = $this->getOptionMetaData();
    if (!empty($options)) {
      foreach ($options as $key => $arr) {
        if (is_array($arr) && count($arr > 1)) {
          $this->addOption($key, $arr[1]);
        }
      }
    }
  }

  public function getPluginDisplayName() {
    return 'Users Without Email';
  }

  public function getMainPluginFileName() {
    return 'users-without-email.php';
  }

  public function getPluginCookieName() {
    return 'users-without-email';
  }

  public function falseEmailAddress() {
    return 'deleteme12345@deletemenow09876.com';
  }

  /**
   * Called by install() to create any database tables if needed.
   * Best Practice:
   * (1) Prefix all table names with $wpdb->prefix
   * (2) make table names lower case only
   * @return void
   */
  public function installDatabaseTables() {
    //    global $wpdb;
    //    $tableName = $this->prefixTableName('mytable');
    //    $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
    //      `id` INTEGER NOT NULL");
  }

  /**
   * Drop plugin-created tables on uninstall.
   * @return void
   */
  public function unInstallDatabaseTables() {
    //    global $wpdb;
    //    $tableName = $this->prefixTableName('mytable');
    //    $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
  }

  /**
   * Perform actions when upgrading from version X to version Y
   * @return void
   */
  public function upgrade() {
  }

  /**
  * Add custom field to registration form
  */
  public function show_password_field() {
    ?>
      <script type="text/javascript" src="http://www.technicalkeeda.com/js/javascripts/plugin/jquery.js"></script>
      <p>
        <label for="pass1">Password<br>
        <input type="password" name="pass1" id="pass1" class="input" value="" size="25" required></label>
      </p>
      <p>
        <label for="pass1">Repeat Password<br>
        <input type="password" name="pass2" id="pass2" class="input" value="" size="25" required></label>
      </p>
      <?php echo $this->honeypot(); ?>
      <input type="hidden" name="user_email" value="<?php echo $this->generate_random_string(); ?>@email.com">
      <input type="hidden" name="referrer" value="<?php echo $_SERVER['HTTP_REFERER']; ?>">
      <style>p#reg_passmail, label[for="user_email"] {display: none;}</style>
      <script>
        $('form#registerform').submit(function(event) {
          if($('input#pass1').val() == '') {
            output_error('Please enter a password.');
            event.preventDefault();
          } else if($('input#pass2').val() == '') {
            output_error('Please verify your password.');
            event.preventDefault();
          } else if($('input#pass1').val() != $('input#pass2').val()) {
            output_error('Your passwords do not match.');
            event.preventDefault();
          }
        }); 
        function output_error($text) {
          $('div#login_error').remove();
          $('form#registerform').before('<div id="login_error"><strong>ERROR</strong>: ' + $text + '<br></div>');
        }
      </script>
    <?php
  }

  //http://stackoverflow.com/questions/4356289/php-random-string-generator
  public function generate_random_string($length = 13) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  //https://wprecipies.wordpress.com/2010/09/03/wordpress-auto-login/
  public function auto_login($user_login) {
    if (!is_user_logged_in()) {
      ////get users password
      $user = new WP_User($user_login);
      $user_pass = md5($user->user_pass);
      
      //login, set cookies, and set current user
      wp_login($user_login, $user_pass, true);
      wp_setcookie($user_login, $user_pass, true);
      wp_set_current_user($user->ID, $user_login);
    }
  }

  public function get_user($user_login) {
    return new WP_User(0, $user_login);
  }

  public function login_reroute() {
    if(isset($_COOKIE[$this->getPluginCookieName()])) {
      $cookie = $_COOKIE[$this->getPluginCookieName()];
      setcookie($this->getPluginCookieName(), '', time() - (60 * 60 * 24 * 365));
      echo '<META http-equiv="refresh" content="0;URL=' . $cookie . '">';
    }
  }

  public function register_extra_fields($user_id, $password = "", $meta = array()) {
    if($_POST['plugin_feedback_' . $this->getPluginCookieName()] == '')  {  
      setcookie($this->getPluginCookieName(), $_POST['referrer']);
      $this->unset_email($user_id);
      if($_POST['pass1'] == $_POST['pass2']) wp_update_user(array('ID' => $user_id, 'user_pass' => $_POST['pass1']));
      $this->auto_login($_POST['user_login']);
    } else {
      require_once(ABSPATH . '/wp-admin/includes/user.php');
      wp_delete_user($user_id);
      echo '<meta http-equiv="refresh" content="0;url=' . home_url() . '" />';
    }
  }

  public function make_email_optional($user) {
    echo '<style>label[for="email"] span.description {display: none;}</style>';
    $this->unset_false_email($user);
  }

  public function set_false_email($user) {
    if($_POST['email'] == '') $_POST['email'] = $this->falseEmailAddress();
  }

  public function unset_false_email() {
    global $current_user;
    if($current_user->user_email == $this->falseEmailAddress()) {
      $this->unset_email($current_user->ID); 
      echo '<meta http-equiv="refresh" content="0">';
    }
  }

  public function unset_email($user_id) {
    wp_update_user(array('ID' => $user_id, 'user_email' => ''));
  }

  /*public function captcha() {
    if (($this->getOption('recaptcha_site_key') != '') && ($this->getOption('recaptcha_site_key') !== false)) return '<script src="https://www.google.com/recaptcha/api.js"></script><div class="g-recaptcha" data-sitekey="' . $this->getOption('recaptcha_site_key') . '" style="max-width: 272px;"></div>';
    return '';
  }*/

  public function honeypot() {
    return '<textarea name="plugin_feedback_' . $this->getPluginCookieName() . '" id="plugin_feedback_' . $this->getPluginCookieName() . '"></textarea><style>#plugin_feedback_' . $this->getPluginCookieName() . '{display: none;}</style>';
  }

  public function addActionsAndFilters() {
    // Add options administration page
    //add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

    // Add Actions & Filters
    add_action('register_form', array(&$this, 'show_password_field'));
    add_action('user_register', array(&$this, 'register_extra_fields'));
    add_action('login_head', array(&$this, 'login_reroute'));
    add_action('show_user_profile', array(&$this, 'make_email_optional'));
    add_action('personal_options_update', array(&$this, 'set_false_email'));

    // Adding scripts & styles to all pages

    // Register short codes

    // Register AJAX hooks
  }

}
