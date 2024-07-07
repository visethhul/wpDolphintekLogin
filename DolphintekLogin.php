<?php
/**
 * @package dolphintekLogin
 */

 /**
  * plugin Name: dolphintek-login
  * plugin URL: http://dolphintek.big
  * Description: Custome Login and dasboard to my own style
  * Version: 1.0.0
  * Author: HUL VISETH
  */
  
 if (!defined('ABSPATH')) {
    die('Hey, what are you doing here? you silly human');
}

  
  class DolphintekLogin{
   public function __construct() {
   //Hook to enqueue logo page styles
    add_action('login_enqueue_scripts', array($this, 'customize_login_page'));
   // filter to change the login logo URL
    add_filter('login_headerurl', array($this, 'custom_login_logo_url'));
   // Hook to enqueue custom admin scripts and style 
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
   // hook to customize the dashboard 
    add_action('wp_dashboard_setup', array($this, 'custom_dashboard_setup'));
    add_action('wp_dashboard_setup', array($this, 'custom_dashboard_widget'));
   // New hook to add the clone link 
   add_filter('post_row_actions', array($this, 'add_clone_link'), 10, 2);
   add_action('admin_action_clone_post', array($this, 'clone_post'));
  
  }
    //Add a clone link to the post row action
   public function add_clone_link($actions,$post){
    if(current_user_can('edit_posts')){
       $actions['clone'] = '<a href="' . wp_nonce_url('admin.php?action=clone_post&post=' . $post->ID, 'clone_post_' . $post->ID) . '">Clone</a>';
    
    }
    return $actions;
   }

    //Handle the cloning of post 
    public function clone_post() {
      //check for nonce security 
      $nonce = $_GET['_wpnonce'];
      $post_id =isset($_GET['post']) ? intval($_GET['post']) : 0;

      if(!wp_verify_nonce($nonce, 'clone_post_' .$post_id )){
        wp_die('Security check failed');
      }
      //Get the post to clone
      $post =  get_post($post_id);
      if(null === $post){
        wp_die('Post not found');
      }
      // Prepare the new post data
      $new_post = array(
        'post_title' => $post->post_title. '(Clone)',
        'post_content' => $post->post_content,
        'post_status' => 'draft',
        'post_type' => $post->post_type,
        'post_author'=> $post->post_author,

      );
      //Insert the new post 
      $new_post_id = wp_insert_post($new_post);
      //copy taxomies an metadata
      $taxonomies = get_object_taxonomies($post->post_type);
      foreach($taxonomies as $taxonomy){
        $terms = wp_get_post_terms($post_id,$taxonomy, array('fields'=>'ids'));
        wp_set_post_terms($new_post_id,$terms,$taxonomy);
      }
       $meta_data = get_post_meta($post_id);
        foreach ($meta_data as $key => $values) {
            foreach ($values as $value) {
                add_post_meta($new_post_id, $key, $value);
            }
        }

        // Redirect to the edit screen of the new post
        wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
        exit;

    }

   public function register (){
   // register any addionnal hooks or actions here
   }

   public function activate(){
    // Flush rewrite rules on activation 
    flush_rewrite_rules();
   }

   public function deactivate(){
   // Flush rewrite fule on deactivate 
   flush_rewrite_rules();
   }

   public function uninstall(){
   // uninstallation tasks,do not delete data
   }
   
   // function to customize login page styles 
   public function customize_login_page(){
    wp_enqueue_style('dolphinteklogin-login-style', plugins_url('/assets/login-style.css', __FILE__));
   }
   //Function to costomize login logo url
   public function custom_login_logo_url($url){
    return home_url('/');
   }

   //Function to equeue admin scripts and styles
   public function enqueue_admin_scripts(){
    wp_enqueue_style('dolphinteklogin-admin-style',plugins_url('/assets/admin-style.css',__FILE__));
    wp_enqueue_script('dolphinteklogin-admin-script',plugins_url('/assets/admin-script.js',__FILE__));

   }
   //function to customize the dashbard 
   public function custom_dashboard_setup(){
    wp_enqueue_style('dolphintek-dasboard-style',plugins_url('/assets/dashboard-style.css',__FILE__));
   }
   
   //function to add a custom dashoard widget
   public function custom_dashboard_widget(){
    wp_add_dashboard_widget(
     'custom_dashboard_wet', // widget slug
     'OUR SERVICES', // title
     array($this,'custom_dashboard_widget_display') // display function
    );
   }

   // function to display the custom dashboard widget content
   public function custom_dashboard_widget_display(){
     include(plugin_dir_path(__FILE__).'partials/dashboard-widget.php');
   }
  }
// Instantiate the dolphinteklogin class if it exits
if (class_exists('DolphintekLogin')){
   $dolphintekLogin = new DolphintekLogin();
   $dolphintekLogin->register();
}
// Hook to register activation, deactivation, and uninstallation functions
register_activation_hook(__FILE__, array($dolphintekLogin, 'activate'));
register_deactivation_hook(__FILE__, array($dolphintekLogin, 'deactivate'));
register_uninstall_hook(__FILE__, array($dolphintekLogin, 'uninstall'));
?>