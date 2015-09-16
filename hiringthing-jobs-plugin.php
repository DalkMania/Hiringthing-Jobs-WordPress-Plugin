<?php
/*
Plugin Name: HiringThing Jobs Plugin
Description: HiringThing is online software that helps companies post jobs online, manage applicants and hire great employees. If you don't yet have a HiringThing account, visit <a target="_blank" href="http://www.hiringthing.com">http://www.hiringthing.com</a> for a free trial. 
Plugin URI: http://www.niklasdahlqvist.com
Author: Niklas Dahlqvist
Author URI: http://www.niklasdahlqvist.com
Version: 1.0.0
Requires at least: 4.2
License: GPL
*/

/*
   Copyright 2015  Niklas Dahlqvist  (email : dalkmania@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
* Ensure class doesn't already exist
*/
if(! class_exists ("HiringThing_Jobs_Plugin") ) {

  class HiringThing_Jobs_Plugin {
    private $options;

    /**
     * Start up
     */
    public function __construct() {
      $this->options = get_option( 'hiringthing_settings' );;
      $this->site_url = $this->options['site_url'];

      add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
      add_action( 'admin_init', array( $this, 'page_init' ) );
      add_action('admin_print_styles', array($this,'plugin_admin_styles'));
      add_shortcode('hiringthing_jobs', array( $this,'JobsShortCode') );
    }

    public function plugin_admin_styles() {
      wp_enqueue_style('hiringthing-admin-styles', $this->getBaseUrl() . '/assets/css/plugin-admin-styles.css');
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_management_page(
            'HiringThing Settings Admin', 
            'HiringThing Settings', 
            'manage_options', 
            'hiringthing-settings-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        // Set class property
        $this->options = get_option( 'hiringthing_settings' );
        ?>
        <div class="wrap hiringthing-settings">
          <h2>HiringThing Settings</h2>           
          <form method="post" action="options.php">
          <?php
              // This prints out all hidden setting fields
              settings_fields( 'hiringthing_settings_group' );   
              do_settings_sections( 'hiringthing-settings-admin' );
              submit_button(); 
          ?>
          </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {

      register_setting(
          'hiringthing_settings_group', // Option group
          'hiringthing_settings', // Option name
          array( $this, 'sanitize' ) // Sanitize
      );

      add_settings_section(
          'hiringthing_section', // ID
          'HiringThing Settings', // Title
          array( $this, 'print_section_info' ), // Callback
          'hiringthing-settings-admin' // Page
      );  

      add_settings_field(
          'site_url', // ID
          'HiringThing Site URL', // Title 
          array( $this, 'hiringthing_site_url_callback' ), // Callback
          'hiringthing-settings-admin', // Page
          'hiringthing_section' // Section           
      );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ) {
      $new_input = array();
      if( isset( $input['site_url'] ) )
          $new_input['site_url'] = sanitize_text_field( $input['site_url'] );

      return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info() {
      echo '<p>Enter your settings below:';
      echo '<br />and then use the <strong>[hiringthing_jobs]</strong> shortcode and / or the <strong>widget</strong> to display the content.</p>';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function hiringthing_site_url_callback() {
      printf(
          '<small>http://</small><input type="text" id="site_url" class="narrow-fat" name="hiringthing_settings[site_url]" value="%s" /><small>.hiringthing.com</small>',
          isset( $this->options['site_url'] ) ? esc_attr( $this->options['site_url']) : ''
      );
    }

    public function JobsShortCode($atts, $content = null) {

      $output = '';

      if(isset($this->site_url) && $this->site_url != '') {
        $output .= '<!-- HiringThing Jobs Widget -->';
        $output .= '<script type="text/javascript">';
        $output .= 'var ht_settings = ( ht_settings || new Object() );';
        $output .= 'ht_settings.site_url = "'.$this->site_url.'";';
        $output .= 'ht_settings.src_code = "wordpress";';
        $output .= '</script>';
        $output .= '<script src="http://assets.hiringthing.com/javascripts/embed.js" type="text/javascript"></script>';
        $output .= '<div id="hiringthing-jobs"></div>';
        $output .= '<link rel="stylesheet" type="text/css" media="all" href="http://assets.hiringthing.com/stylesheets/embed.css" />';
        $output .= '<!-- end HiringThing Jobs Widget -->';
      } else {
        $output .= '<p>Please Enter your HiringThing Account URL in the Widgets Section.</p>';
      }
    
      return $output;
    }


    //Returns the url of the plugin's root folder
    protected function getBaseUrl() {
      return plugins_url(null, __FILE__);
    }

    //Returns the physical path of the plugin's root folder
    protected function getBasePath() {
      $folder = basename(dirname(__FILE__));
      return WP_PLUGIN_DIR . "/" . $folder;
    }

  } //End Class

  /**
   * Instantiate this class to ensure the action and shortcode hooks are hooked.
   * This instantiation can only be done once (see it's __construct() to understand why.)
   */
  new HiringThing_Jobs_Plugin();

} // End if class exists statement

/**
* Ensure Widget class doesn't already exist
*/
if(! class_exists ("HiringThing_Jobs_Widget") ) {

  add_action('widgets_init', create_function('', 'register_widget("HiringThing_Jobs_Widget");'));
  class HiringThing_Jobs_Widget extends WP_Widget {
    /**
     * Register widget with WordPress.
     */
    public function __construct() {
      parent::__construct('hiringthing_jobs_widget', 'HiringThing Jobs Widget', array('description' => 'HiringThing is online software that helps companies post jobs online, manage applicants and hire great employees.') // Args
      );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance) {
      extract($args);
      $title = apply_filters('widget_title', $instance['title']);
      $site_url = $instance['site_url'];

      echo $before_widget;
      if (!empty($title)) { echo $before_title.$title.$after_title; }
      if($site_url != '') {
        echo '<!-- HiringThing Jobs Widget -->';
        echo '<script type="text/javascript">';
          echo 'var ht_settings = ( ht_settings || new Object() );';
          echo 'ht_settings.site_url = "'.$site_url.'";';
          echo 'ht_settings.src_code = "wordpress";';
        echo '</script>';
        echo '<script src="http://assets.hiringthing.com/javascripts/embed.js" type="text/javascript"></script>';
        echo '<div id="hiringthing-jobs"></div>';
        echo '<link rel="stylesheet" type="text/css" media="all" href="http://assets.hiringthing.com/stylesheets/embed.css" />';
        echo '<!-- end HiringThing Jobs Widget -->';
      } else {
        echo '<p>Please Enter your HiringThing Account URL in the Widgets Section.</p>';
      }
      echo $after_widget;
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance) {
      $instance = array();
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['site_url'] = strip_tags($new_instance['site_url']);
      return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance) {
      if (isset($instance['title'])) {
        $title = $instance['title'];
      }   
      if (isset($instance['site_url'])) {
        $site_url = $instance['site_url'];
      }
      echo '<p>';
        echo '<label for="'.$this->get_field_id('title').'">Title:</label>';
        echo '<input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr($title).'" />';
      echo '</p>';
      echo '<p>';
        echo '<label for="'.$this->get_field_id('site_url').'">HiringThing Account URL</label><br />';
        echo '<small>http://</small><input class="narrowfat" id="'.$this->get_field_id('site_url').'" name="'.$this->get_field_name('site_url').'" type="text" value="'.esc_attr($site_url).'" /><small>.hiringthing.com</small>';
      echo '</p>';
    }
  }
} // End if class exists statement