<?php
/*
Plugin Name: Rake Live Chat
Description: Use the WordPress Rake Live Chat Plugin to enable real-time engagement tools like  live chat and visitor monitoring to your WordPress website with minimal setup required. Instantly engage with on-site visitors and customers and enable a quick resolution to their questions or concerns.
Plugin URI:  https://wordpress.org/plugins/rake-live-chat
Version: 1.4.1
*/
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

define('RAKE_LIVE_CHAT_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
define('RAKE_LIVE_CHAT_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
define('RAKE_LIVE_CHAT_PLUGINS_URL', plugins_url());


/**
 * Add menu page
 */
function rake_live_chat_add_menu_section(){
  $menuTitle = 'Rake Live Chat';
  $pageTitle = 'Rake Live Chat';
  $manageOption = 'manage_options';
  $menuSlug = 'rake-live-chat';
  $icon = 'dashicons-rake-menu';
  $position = 6;
  add_menu_page(
    $menuTitle, 
    $pageTitle, 
    $manageOption, 
    $menuSlug, 
    'rake_live_chat_menuSectionCb', 
    $icon, 
    $position
  );
}

function rake_live_chat_menuSectionCb () {
  include_once RAKE_LIVE_CHAT_PLUGIN_DIR_PATH.'/views/main.php';
}

function getGAScript ($GASettings) {
  $events = array_filter($GASettings->events, function($event){
    return isset($event->isEnabled) && $event->isEnabled;
  });
  $GAScript = "widget.parser = (str, data) => {
    const tokens = str.match(/{{[a-zA-Z]+}}/gm);
    if (tokens) { 
      const values = tokens.reduce((acc,cur) => {
        cur = cur.replace(/[{}]+/gm, '');
        if(data[cur] != null) { acc[cur] = data[cur] }
        return acc;
      }, {});
      
      tokens.forEach( token => {
        const value  = values[token.replace(/[{}]+/gm, '')];
        if (value != null) {
          const regex = new RegExp(token, 'gm');
          str = str.replace(regex, value);
        }
      });
    }
    return str;
  }\n";

  foreach ($events as $event){
    foreach ($event->subEvents as $subEvent) {
      if (isset($subEvent->event_action) && $subEvent->event_action != "" ) {
        $GAScript .= "widget.addEventListener('".$event->name."', (data) => {
          const eventAction = '".$subEvent->event_action."';
          const eventLabel = '".(isset($subEvent->event_label) ? $subEvent->event_label : "")."';
          const eventValue = '".(isset($subEvent->event_value) ? $subEvent->event_value : "''")."';\n";
          if (isset($subEvent->typeId)) {
            $GAScript .= "if (data.reasonId == ".$subEvent->typeId.") {\n";
          }
          $GAScript .=  "gtag( 'event', data ? widget.parser(eventAction,data) : eventAction,
              { 
                event_category: '".$GASettings->event_category."',
                event_label: data ? widget.parser(eventLabel,data) : eventLabel,
                event_value: data ? widget.parser(eventValue,data) : eventValue
              }
            );\n";
          if (isset($subEvent->typeId)) {
            $GAScript .= "}\n";
          } 
          $GAScript .= "});\n";
      }
    }
  }

  return $GAScript;
}

// Add widget script to website
function rake_live_chat_assets() {

  global $post;
  global $wpdb;
  $table = 'wp_rlc_settings';
  $result = $wpdb->get_row( "SELECT * FROM $table");
  if(!empty($result)){
    /**
     * Google Analytics section
     */
    $GASettings = json_decode(
      isset($result->googleAnalyticsSettings) && $result->googleAnalyticsSettings != ""? $result->googleAnalyticsSettings : '{}'
    );
    $GAScript = "";
    if(isset($GASettings->isEnabled) && $GASettings->isEnabled) {
      $GAScript .= getGAScript($GASettings);
    }
    /**
     * Google Analytics section end
     */
    

    /**
     * Hide widget on pages
     */
    $selectedPagesArray = explode(',',$result->pages);
    $status = $result->allPagesAndNew || in_array($post->ID, $selectedPagesArray);
    $additionalScript = "
        widget.addEventListener('load', async () => {
        widget.setSound(false);
        widget.hide();
        const canBeShowedOnPage = ".($status ? "true" : "false").";
        const isActiveSession = await new Promise((resolve, reject) => {
          widget.getCurrentSessionId((err, data) => {
            if (err) { return reject(err); }
            return resolve(data.sessionId != null);
          })
        });
      
        const showWidget = () => {
          widget.show();
          widget.setSound(true);
          if (".($result->maximizedOnLoad ? "1" : "0")." == 1) {
            if( !/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
              widget.open();
            }
          }
        };
      
        const getBusinessOpen = () => new Promise((resolve, reject) => {
          widget.isBusinessOpen((err, data) => err ? reject(err) : resolve(data.data));
        });
      
        const getAvailableAgent = () => new Promise((resolve, reject) => {
          widget.getAvailableAgentsCount((err, data) => err ? reject(err) : resolve(resolve(data.count > 0)));
        });
      
        if (isActiveSession) {
          return showWidget();
        }
      
        if (!canBeShowedOnPage) { return; }
        const widgetMode = ".$result->mode.";
        switch (widgetMode) {
          case 1: case 2: {
            const [isBusinessOpen, isAvailableAgent] = await Promise.all([getBusinessOpen(), getAvailableAgent()]);
            const condition = widgetMode == 1 ? isBusinessOpen && isAvailableAgent : isBusinessOpen || isAvailableAgent;
            if (condition) {
              return showWidget();
            }
            break;
          }
          case 3: {
            const isBusinessOpen = await getBusinessOpen();
            if (isBusinessOpen) { return showWidget(); }
            break;
          }
          case 4: {
            const isAvailableAgent = await getAvailableAgent();
            if (isAvailableAgent) { return showWidget(); }
            break;
          }
          case 5: {
            return showWidget();
          }
          case 6: { break; }
        }
      })";

    wp_enqueue_script('rlc-source-widget', 'https://widget.rake.ai/js/widget.js', '', '1.0');
    $script="
        var widget;
        window.addEventListener('load', () => {
          widget = new RakeLiveChatWidget('$result->hash');\n"
          .$additionalScript."
          ".$GAScript." 
        });";
    wp_add_inline_script('rlc-source-widget', $script);
  } 
}

add_action('admin_menu', 'rake_live_chat_add_menu_section');
add_action('wp_enqueue_scripts', 'rake_live_chat_assets');

function rake_live_chat_addAdminStyle($hook) {
  wp_enqueue_style( 'rlc-admin-style', RAKE_LIVE_CHAT_PLUGINS_URL.'/rake-live-chat/assets/css/rlc-admin-style.css', '', '1.0' );
  wp_enqueue_script('rlc-admin-script',RAKE_LIVE_CHAT_PLUGINS_URL.'/rake-live-chat/assets/js/rlc-admin-script.js', '', '1.0');
}
add_action( 'admin_enqueue_scripts', 'rake_live_chat_addAdminStyle' );

function rake_live_chat_onActivation () {
  global $wpdb;
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php');

  $sql = "CREATE TABLE `wp_rlc_settings` (
    `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `hash` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
    `mode` VARCHAR(1) NOT NULL DEFAULT '1' ,
    `allPagesAndNew` VARCHAR(1) NOT NULL DEFAULT '0',
    `pages` TEXT NOT NULL,
    `googleAnalyticsSettings` JSON,
    `maximizedOnLoad` VARCHAR(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

  dbDelta( $sql );
}

register_activation_hook(__FILE__, 'rake_live_chat_onActivation');

function rake_live_chat_onUpdate() {
  global $wpdb;
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php');

  $sql = "ALTER TABLE `wp_rlc_settings` ADD `googleAnalyticsSettings` JSON DEFAULT '{}'";

  dbDelta( $sql );

  $sql = "ALTER TABLE `wp_rlc_settings` ADD `maximizedOnLoad` VARCHAR(1) NOT NULL DEFAULT '0',";

  dbDelta( $sql );
}
add_action( 'upgrader_process_complete', 'rake_live_chat_onUpdate', 10, 2 );

function rake_live_chat_onDeactivation () {
  global $wpdb;

  $query = "DROP TABLE IF EXISTS `wp_rlc_settings`";
  $wpdb->query($query);
}

register_deactivation_hook( __FILE__, 'rake_live_chat_onDeactivation');  
register_uninstall_hook( __FILE__, 'rake_live_chat_onDeactivation');     

add_action( 'wp_ajax_rake_live_chat_get_bot_status_http_cb', 'rake_live_chat_get_bot_status_http_cb' );
add_action( 'wp_ajax_nopriv_rake_live_chat_get_bot_status_http_cb', 'rake_live_chat_get_bot_status_http_cb' );

include_once RAKE_LIVE_CHAT_PLUGIN_DIR_PATH.'/views/bot-status.php';
function rake_live_chat_get_bot_status_http_cb  () {
  if(filter_input(INPUT_GET, '$nonce')) {
    echo "{}";
    return wp_die();
  }
  if (!isset($_GET['env'], $_GET['hash'])) {
    echo "{ \"message\": \"Not found env or hash\" }";
    wp_die();
    return;
  }
  $env = filter_var($_GET['env'], FILTER_SANITIZE_STRING);
  $hash = filter_var($_GET['hash'], FILTER_SANITIZE_STRING);
  $bot = new stdClass();
  $bot->status = FALSE;
  $bot->exists = FALSE;
  $bot->workspace = FALSE;
  rake_live_chat_getBotStatus($bot, $hash, $env);
  echo "{\"status\":".($bot->status ? 'true' : 'false').", \"exists\":".($bot->exists ? 'true' : 'false') .", \"workspace\":".($bot->workspace? 'true' : 'false')."}";
  wp_die();
}
?>