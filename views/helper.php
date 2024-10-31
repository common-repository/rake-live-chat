<?php
  include_once RAKE_LIVE_CHAT_PLUGIN_DIR_PATH.'/views/bot-status.php';
  function rake_live_chat_setup(
    &$hash,
    &$widgetMode,
    &$selectedPages,
    &$allPagesAndNew,
    &$bot,
    &$env,
    &$GASettings,
    &$maximizedOnLoad,
    &$statusObject
  ) {
    global $wpdb;
    $result = $wpdb->get_row( "SELECT * FROM wp_rlc_settings");
    $GASettings = json_decode('{"isEnabled":false,"event_category":"rakechat","events":[{"name":"open","isEnabled":true,"subEvents":[{"typeId":2,"name":"click-on-chat","event_action":"widget-open","event_label":"click-on-chat","event_value":""},{"typeId":1,"name":"invite","event_action":"widget-open","event_label":"invite","event_value":""},{"typeId":4,"name":"jsapi","event_action":"widget-open","event_label":"jsapi","event_value":""},{"typeId":3,"name":"proactive-message","event_action":"widget-open","event_label":"click-on-proactive-{{proactiveMessageName}}","event_value":""}]},{"name":"session-start","isEnabled":true,"subEvents":[{"typeId":2,"name":"user","event_action":"session-start","event_label":"click-on-chat","event_value":""},{"typeId":1,"name":"invite","event_action":"session-start","event_label":"invite","event_value":""},{"typeId":3,"name":"proactive-message","event_action":"session-start","event_label":"proactive-{{proactiveMessageName}}","event_value":""}]},{"name":"session-end","isEnabled":true,"subEvents":[{"name":"session-end","event_action":"session-end","event_label":"","event_value":""}]},{"name":"proactive-message-played","isEnabled":true,"subEvents":[{"name":"proactive-message","event_action":"proactive-message-play","event_label":"proactive-{{proactiveMessageName}}","event_value":""}]}]}');
    if(!empty($result)){
      $hash = $result->hash;
      $widgetMode = $result->mode;
      $selectedPages = $result->pages;
      $allPagesAndNew = $result->allPagesAndNew;
      rake_live_chat_getBotStatus($bot, $hash, $env);
      $statusObject->isDBData = TRUE;
      $maximizedOnLoad = $result->maximizedOnLoad;
      $GASettings = json_decode(
        isset($result->googleAnalyticsSettings) && $result->googleAnalyticsSettings != "" ? $result->googleAnalyticsSettings : '{}'
      );
    } else {
      // $statusObject->status->error = TRUE;
      // $statusObject->hash->error->status = TRUE;
    }
  }
  function rake_live_chat_envValidation(&$statusObject, $env) {
    /**
     * Must be url
     */
    if ( filter_var($env, FILTER_VALIDATE_URL) === FALSE ) {
      $statusObject->status->error = TRUE;
      $statusObject->env->error->status = TRUE;
      return;
    }
    /**
     * Check regular enviroment
     */
    if (filter_var(
      $env, 
      FILTER_VALIDATE_REGEXP,
      [
        "options" =>["regexp"=> "/https?:\/\/widget(-(uat|test))?\.rake\.ai\/js\/widget\.js/"]
      ]
    ) === FALSE ){
      $statusObject->status->warning = TRUE;
      $statusObject->env->warning->status = TRUE;
    }
  }
  function rake_live_chat_hashValidation(&$statusObject, $hash) {

    if (!$statusObject->isDBData) {
      return $hash;
    }

    if (filter_var(
      $hash, 
      FILTER_VALIDATE_REGEXP,
      [
        "options" =>["regexp"=> "/^wwc_\d+_\d+$/"]
      ]
    ) === FALSE ){
      $statusObject->status->error = TRUE;
      $statusObject->hash->error->status = TRUE;
      return '';
    }

    return $hash;
  }

  class RakeLiveChatStatus {
    public $error;
    public $warning;
    function __construct($errorMessage, $warningMessage) {
      $this->error = new stdClass();
      $this->warning = new stdClass();
      $this->error->status = 0;
      $this->warning->status = 0;

      $this->error->message = $errorMessage;
      $this->warning->message = $warningMessage;
    }
 
    public function getStatus($level) {
      switch($level) {
        case RAKE_LIVE_CHAT_STATUS_ERROR: {
          return $this->error->status;
        }
        case RAKE_LIVE_CHAT_STATUS_WARNING: {
          return $this->warning->status;
        }
        default: {
          return 0;
        }
      }
    }

    public function getMessage($level) {
      switch($level) {
        case RAKE_LIVE_CHAT_STATUS_ERROR: {
          return $this->error->message;
        }
        case RAKE_LIVE_CHAT_STATUS_WARNING: {
          return $this->warning->message;
        }
        default: {
          return 'Default message';
        }
      }
    }
  }
?>