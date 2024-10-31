<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}
function rake_live_chat_getBotStatus(&$bot, $hash, $env) {
  $parsedEnv;
  preg_match("/https?:\/\/widget-?(uat|test)?\.rake\.ai\/js\/widget\.js/",$env,$parsedEnv);
  if (count($parsedEnv) >= 1) {
    // $url = 'https://admin';
    // if (count($parsedEnv) == 2) {
    //   $url .= "-".$parsedEnv[1];
    // }
    $url = "https://admin.rake.ai/rake-live-chat/status?hash=".$hash."&info=workspace";
    $WP_Http = new WP_Http();
    $API_KEY = '72d06590-dc8b-433a-94b6-b7531b6fdb55';
    $headers = array('apikey' => $API_KEY);
    $result = $WP_Http->request( $url, array('headers' => $headers, 'sslverify' => false));
    // echo "url:$url, body";
    // print_r($result['body']);
    // echo "<br>";
    if (!is_wp_error($result) && $result['response']['code'] == '200') {
      $bot->exists = TRUE;
      $body = json_decode($result['body']);
      $bot->status = $body->platform && $body->workspaceStatus;
      $bot->workspace = isset($body->workspace->id) ? TRUE : FALSE;
    }
  }
}
?>