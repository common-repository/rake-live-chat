<?php
  if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
  }

  function rake_live_chat_getGoogleAnalyticsEventOpen () {
    $event = new stdClass();
    $event->name ='open';
    $event->isEnabled = isset($_POST["rlc-ga-event-open-input"]) == 1 ? TRUE : FALSE;
    $event_coc = new stdClass();
    $event_coc->typeId = 2;
    $event_coc->name = 'click-on-chat';
    $event_coc->event_action = $_POST['rlc-ga-event-open-coc-ea-input'];
    $event_coc->event_label = $_POST['rlc-ga-event-open-coc-el-input'];
    $event_coc->event_value = $_POST['rlc-ga-event-open-coc-ev-input'];

    $event_i = new stdClass();
    $event_i->typeId = 1;
    $event_i->name = 'invite';
    $event_i->event_action = $_POST['rlc-ga-event-open-i-ea-input'];
    $event_i->event_label = $_POST['rlc-ga-event-open-i-el-input'];
    $event_i->event_value = $_POST['rlc-ga-event-open-i-ev-input'];

    $event_api = new stdClass();
    $event_api->typeId = 4;
    $event_api->name = 'jsapi';
    $event_api->event_action = $_POST['rlc-ga-event-open-api-ea-input'];
    $event_api->event_label = $_POST['rlc-ga-event-open-api-el-input'];
    $event_api->event_value = $_POST['rlc-ga-event-open-api-ev-input'];

    $event_pm = new stdClass();
    $event_pm->typeId = 3;
    $event_pm->name = 'proactive-message';
    $event_pm->event_action = $_POST['rlc-ga-event-open-pm-ea-input'];
    $event_pm->event_label = $_POST['rlc-ga-event-open-pm-el-input'];
    $event_pm->event_value = $_POST['rlc-ga-event-open-pm-ev-input'];

    $event->subEvents = array( $event_coc, $event_i, $event_api, $event_pm );

    return $event;
  }

  function rake_live_chat_getGoogleAnalyticsEventSessionStart () {
    $event = new stdClass();
    $event->name ='session-start';
    $event->isEnabled = isset($_POST["rlc-ga-event-ss-input"]) == 1 ? TRUE : FALSE;
    
    $event_coc = new stdClass();
    $event_coc->typeId = 2;
    $event_coc->name = 'click-on-chat';
    $event_coc->event_action = $_POST['rlc-ga-event-ss-coc-ea-input'];
    $event_coc->event_label = $_POST['rlc-ga-event-ss-coc-el-input'];
    $event_coc->event_value = $_POST['rlc-ga-event-ss-coc-ev-input'];

    $event_i = new stdClass();
    $event_i->typeId = 1;
    $event_i->name = 'invite';
    $event_i->event_action = $_POST['rlc-ga-event-ss-i-ea-input'];
    $event_i->event_label = $_POST['rlc-ga-event-ss-i-el-input'];
    $event_i->event_value = $_POST['rlc-ga-event-ss-i-ev-input'];

    $event_pm = new stdClass();
    $event_pm->typeId = 3;
    $event_pm->name = 'proactive-message';
    $event_pm->event_action = $_POST['rlc-ga-event-ss-pm-ea-input'];
    $event_pm->event_label = $_POST['rlc-ga-event-ss-pm-el-input'];
    $event_pm->event_value = $_POST['rlc-ga-event-ss-pm-ev-input'];

    $event->subEvents = array( $event_coc, $event_i, $event_pm );

    return $event;
  }


  function rake_live_chat_getGoogleAnalyticsEventSessionEnd () {
    $event = new stdClass();
    $event->name ='session-end';
    $event->isEnabled = isset($_POST["rlc-ga-event-se-input"]) == 1 ? TRUE : FALSE;
    
    $event_se = new stdClass();
    $event_se->name = 'session-end';
    $event_se->event_action = $_POST['rlc-ga-event-se-ea-input'];
    $event_se->event_label = $_POST['rlc-ga-event-se-el-input'];
    $event_se->event_value = $_POST['rlc-ga-event-se-ev-input'];

    $event->subEvents = array( $event_se );

    return $event;
  }

  function rake_live_chat_getGoogleAnalyticsEventProactiveMessage () {
    $event = new stdClass();
    $event->name ='proactive-message-played';
    $event->isEnabled = isset($_POST["rlc-ga-event-pm-input"]) == 1 ? TRUE : FALSE;
    
    $event_pmd = new stdClass();
    $event_pmd->name = 'proactive-message';
    $event_pmd->event_action = $_POST['rlc-ga-event-pm-pmd-ea-input'];
    $event_pmd->event_label = $_POST['rlc-ga-event-pm-pmd-el-input'];
    $event_pmd->event_value = $_POST['rlc-ga-event-pm-pmd-ev-input'];

    $event->subEvents = array( $event_pmd );

    return $event;
  }

  function rake_live_chat_getGoogleAnalyticsStr () {

    $pack = new stdClass();
    $pack->isEnabled = isset($_POST["rlc-ga-enabled-input"]) == 1 ? TRUE : FALSE;
    $pack->event_category = $_POST["rlc-ga-event_category-input"];

    if(!isset($pack->event_category) || $pack->event_category == '') {
      // TODO Throw error
    }
    $pack->events = array(
      rake_live_chat_getGoogleAnalyticsEventOpen(),
      rake_live_chat_getGoogleAnalyticsEventSessionStart(),
      rake_live_chat_getGoogleAnalyticsEventSessionEnd(),
      rake_live_chat_getGoogleAnalyticsEventProactiveMessage(),
    );
    return json_encode($pack);
  }

  function rake_live_chat_update(&$statusObject, &$hash) {
    $nonce = $_POST['nonce'];

    if ( ! wp_verify_nonce( $nonce, 'rake_live_chat_save_settings' ) ) {
      return die ( 'Not have permission');
    }

    if( !current_user_can('manage_options') ){
      $statusObject->error->status = TRUE;
      $statusObject->permission->status = TRUE;
      return;
    }
    global $wpdb;

    $statusObject->status->error = FALSE;

    if (!isset($_POST["rlc-hash"])) {
      $statusObject->status->error = TRUE;
      $statusObject->hash->error->status = TRUE;
      return;
    }
    $hash = rake_live_chat_hashValidation($statusObject,filter_var($_POST["rlc-hash"], FILTER_SANITIZE_STRING));
    
    /**
     * Must be in format: number,number.. or empty
     */
    if (!isset($_POST["rlc-web-pages"])) {
      $_POST["rlc-web-pages"] = '';
    }
    $pages = filter_var($_POST["rlc-web-pages"], FILTER_SANITIZE_STRING);
    if (filter_var(
      $pages,
      FILTER_VALIDATE_REGEXP,
      [
        "options" =>["regexp"=> "/(^[0-9,]+$|^$)/"]
      ]
    ) === FALSE ){
      $statusObject->status->error = TRUE;
      $statusObject->pages->error->status = TRUE;
    }

    if ($statusObject->status->error) {
      return;
    }

    $allPagesAndNew = isset($_POST["rlc-web-pages-all"]) == 1 ? '1' : '0';
    $maximizedOnLoad =  isset($_POST["rlc-widget-maximized-on-load"]) == 1 ? '1' : '0';
    $widgetMode = 1;
    if (isset($_POST["rlc-widget-mode"])) {
      $widgetMode = filter_var($_POST["rlc-widget-mode"], FILTER_SANITIZE_STRING);
    }
    $result = $wpdb->get_row( "SELECT * FROM wp_rlc_settings");
    $table = 'wp_rlc_settings';

    $pack = array( 
      'hash' => $hash,
      'mode' => $widgetMode,
      'pages' => $pages,
      'allPagesAndNew' => $allPagesAndNew,
      'googleAnalyticsSettings' => rake_live_chat_getGoogleAnalyticsStr(),
      'maximizedOnLoad' => $maximizedOnLoad
    );

    if(!empty($result)){
      $query = array( 'ID' => $result->ID);
      $wpdb->update( $table, $pack, $query );
    } else {
      $wpdb->insert( $table, $pack );
    }
  }
  
  if(isset($_POST['rlc-settings-save'])){
    rake_live_chat_update($statusObject, $hash);
    rake_live_chat_setup($hash, $widgetMode, $selectedPages, $allPagesAndNew, $bot, $env, $GASettings, $maximizedOnLoad, $statusObject);
  }

?>
