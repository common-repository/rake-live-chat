<?php

  if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
  }
  define('RAKE_LIVE_CHAT_SCRIPT_TEMPLATE', '<!-- Start of Rake Live Chat Widget -->&#13;&#10;<script src="{{ENV}}" type="text/javascript"></script>&#13;&#10;<script> var widget = new RakeLiveChatWidget("{{HASH}}");</script>&#13;&#10;<!-- End of Rake Live Chat Widget -->');

  define('RAKE_LIVE_CHAT_STATUS_ERROR', 'error');
  define('RAKE_LIVE_CHAT_STATUS_WARNING', 'warning');

  include_once RAKE_LIVE_CHAT_PLUGIN_DIR_PATH.'/views/helper.php';
  $statusObject = new stdClass();
  $statusObject->status = new stdClass();
  $statusObject->status->error = FALSE;
  $statusObject->status->warning = FALSE;
  $statusObject->isDBData = FALSE;
  $statusObject->env = new RakeLiveChatStatus(
    "Script Source is invalid.  Please check your widget code snippet.",
    "Hmm. The Script Source URL doesn't look right. Please check your widget code snippet."
  );
  $statusObject->hash = new RakeLiveChatStatus(
    "Something seems to be wrong. The \"hash\" value is not in the right format.",
    ""
  );
  $statusObject->pages = new RakeLiveChatStatus(
    "Page list has incorrect format. That shouldn't happen. Please check selected pages.",
    ""
  );
  $statusObject->permission = new RakeLiveChatStatus(
    "User not have permission to save settings",
    ""
  );

  $bot = new stdClass();
  $bot->status = FALSE;
  $bot->exists = FALSE;
  $bot->workspace = FALSE;
  
  $hash = '';
  $env='https://widget.rake.ai/js/widget.js';
  $widgetMode = 1;
  $selectedPages ='';
  $allPagesAndNew = 0;
  $GASettings = null;
  rake_live_chat_setup($hash, $widgetMode, $selectedPages, $allPagesAndNew, $bot, $env, $GASettings, $maximizedOnLoad, $statusObject);
  rake_live_chat_envValidation($statusObject, $env);
  $hash = rake_live_chat_hashValidation($statusObject,filter_var($hash, FILTER_SANITIZE_STRING));

  include_once RAKE_LIVE_CHAT_PLUGIN_DIR_PATH.'/views/save-settings.php';
 
?>


<div class='rlc-settings'>
  <div class="settings_top">
    <h2>Rake Live Chat Wordpress Plugin</h2>
    <img src="<?=RAKE_LIVE_CHAT_PLUGIN_DIR_URL?>assets/images/rake-logo.svg" width="200" alt="" class=""
      data-position="34" data-size="36">
    <h4>One-stop messaging platform for organizations.</h4>
    <p style="font-size: 18px;color: #444;">
      Rake is a free-forever live chat service that enables you to engage with visitors on your Wordpress website. When
      you are ready to expand beyond live chat, Rake also allows you to collaborate with coworkers, engage with
      customers on other platforms like SMS and Facebook Messenger, and add bots to your messaging conversations. To
      sign up for Rake and learn about all of its great features, visit <a target="_blank" rel="noopener noreferrer"
        href="https://rake.ai" target="_blank">rake.ai</a>. Let's get started!
    </p>
    <h2>Instructions:</h2>
    <ul style="font-size:18px">
      <li>
        <strong>Step one:</strong>
        If you have not already done so, you must first activate a workspace for your organization at
        <a target="_blank" rel="noopener noreferrer" href="https://app.rake.ai/auth/getting-started"
          target="_blank">https://app.rake.ai/auth/getting-started</a>.
      </li>
      <li>
        <strong>Step two:</strong>
        Set the <strong>Required Settings</strong> below. Simply paste your Rake Live Chat hash code from your Rake Workspace
        settings. Find it in Workspace Settings > Live Chat Widgets > My Widgets > [Select your widget] > Show Code >
        Copy Hash. Optionally, use the <strong>Advanced Settings</strong> for additional features like showing the widget
        after-hours or hiding the widget from a specific page(s).
      </li>
      <li><strong>Step three:</strong> SAVE the settings and view your page.</li>
    </ul>
  </div>

  <form name='rlc-settings-hash' method='post'>
    <hr>
    <h2 style="text-align: left; color: #222; margin: 2px; text-transform: capitalize;">
      current status:
    </h2>
    <div class="settings_current">
      <table width="100%" style=" font-size: 12px; color: #999; ">
        <tbody>
          <tr>
            <td style=" text-transform: uppercase; ">Script source:</td>
            <td style="">
              <input type='hidden' id='rlc-env' value='<?= $env ?>' name='rlc-env'>
              <span id='rlc-env-span'><?= $env ?></span>
            </td>
            <td>
              <?php 
                $errorEnvStatus = $statusObject->env->getStatus(RAKE_LIVE_CHAT_STATUS_ERROR);
                $warningEnvStatus = $statusObject->env->getStatus(RAKE_LIVE_CHAT_STATUS_WARNING);
              ?>
              <div id='rlc-env-success'
                class="success-img fillSuccess <? ($errorEnvStatus || $warningEnvStatus) ? 'hidden' : ''?>"></div>
              <div id='rlc-env-error-img'
                class='error-img fillError <?php if (!$errorEnvStatus && !$warningEnvStatus) { echo "hidden"; } ?>'>
              </div>
            </td>
            <td>
              <span id='rlc-env-error' class='error <?php if (!$errorEnvStatus) { echo "hidden"; } ?>'>
                <?= $statusObject->env->getMessage(RAKE_LIVE_CHAT_STATUS_ERROR) ?>
                <br>
              </span>
              <span id='rlc-env-warning' class='error <?php if (!$warningEnvStatus) { echo "hidden"; } ?>'>
                <?= $statusObject->env->getMessage(RAKE_LIVE_CHAT_STATUS_WARNING) ?>
                <br>
              </span>

            </td>
          </tr>
          <tr id='env-hash-section' style="">
            <td style=" text-transform: uppercase; ">Widget Hash:</td>
            <td>
              <input type='hidden' id='rlc-hash' value='<?= $hash ?>' name='rlc-hash'>
              <span id='rlc-hash-span'><?= $statusObject->isDBData ? $hash : '' ?></span>
              <span id='rlc-hash-span-text'><?= $statusObject->isDBData ? '' : 'Not yet set. Set below.' ?></span>
            </td>
            <td>
              <?php 
                $errorHashStatus = $statusObject->hash->getStatus(RAKE_LIVE_CHAT_STATUS_ERROR);
                $warningHashStatus = $statusObject->hash->getStatus(RAKE_LIVE_CHAT_STATUS_WARNING);
              ?>
              <div id='rlc-hash-success'
                class="success-img fillSuccess <?= (!$statusObject->isDBData || $errorHashStatus || $warningHashStatus) ? 'hidden' : ''?>">
              </div>
              <div id='rlc-hash-error-img'
                class='error-img fillError <?= (!$statusObject->isDBData || (!$errorHashStatus && !$warningHashStatus)) ? 'hidden' : ''?>'>
              </div>
              <div id='rlc-hash-empty-img' class='empty-img  <?= $statusObject->isDBData ? 'hidden' : ''?>'></div>
            </td>
            <td>
              <span id='rlc-hash-error' class='error <?php if (!$errorHashStatus) { echo "hidden"; } ?>'>
                <?= $statusObject->hash->getMessage(RAKE_LIVE_CHAT_STATUS_ERROR) ?>
                <br>
              </span>
              <span id='rlc-hash-warning' class='error <?php if (!$warningHashStatus) { echo "hidden"; } ?>'>
                <?= $statusObject->hash->getMessage(RAKE_LIVE_CHAT_STATUS_WARNING) ?>
                <br>
              </span>

            </td>

          </tr>
          <tr>
            <td style=" text-transform: uppercase; ">Workspace status:</td>
            <?php
              $isEnabled = $bot->exists && $bot->status;
              $isDisabled = $bot->exists && !$bot->status;
              $isGreen = $isEnabled && $bot->workspace;
              $isGrey = $isEnabled && !$bot->workspace;
              $isRed = !$bot->exists && !$bot->status;
            ?>
            <td>
              <span id='rlc-bot-status-enabled' class='<?= $isEnabled ? '' : 'hidden' ?>'>Enabled</span>
              <span id='rlc-bot-status-disabled' class='<?= $isDisabled ? '' : 'hidden' ?>'>Disabled</span>
              <span id='rlc-bot-status-unknown'
                class='<?= $statusObject->isDBData && $isRed ? '' : 'hidden' ?>'>Unknown</span>
              <span id='rlc-bot-status-empty-text' class='<?= !$statusObject->isDBData ? '' : 'hidden' ?>'>--</span>
            </td>
            <td>
              <div id='rlc-bot-status-loader' class="lds-dual-ring hidden"></div>
              <div id='rlc-bot-status-fail'
                class='error-img fillError <?= $statusObject->isDBData && ($isRed || $isDisabled) ? '' : 'hidden' ?>'>
              </div>
              <div id='rlc-bot-status-success'
                class='success-img <?= $statusObject->isDBData && ($isGrey || $isGreen) ? ($isGrey ? 'fillWarning' : 'fillSuccess') : 'hidden' ?>'>
              </div>
              <div id='rlc-bot-status-empty' class='empty-img   <?= $statusObject->isDBData ? 'hidden' : ''?>'></div>
            </td>
            <td>
              <span id='rlc-bot-status-no-workspace'
                class='warning <?= $statusObject->isDBData && $isGrey ? '' : 'hidden' ?>'>Not found related
                workspace.</span>
              <span id='rlc-bot-status-no-info'
                class='error <?= $statusObject->isDBData && $isRed ? '' : 'hidden' ?>'>We cannot get a workspace status.
                See if there are other errors or refresh.</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <hr>
    <h2>Required Settings:</h2>
    <div id="required" class='section'>
      <strong>WIDGET HASH:</strong><input id='rlc-hash-input' value="<?=$hash?>"
        placeholder='Paste or type the widget hash from Rake system here.'><br>
    </div>
    <hr>
    <h2>Display Settings:</h2>
    <div id="advanced" class='section'>
      <strong>When to display the widget:</strong><br>
      <select id="rlc-widget-mode" name="rlc-widget-mode">
        <option value='1' <?= $widgetMode == 1 ? 'selected="selected"' : '' ?>>
          When business hours are OPEN - and - when agents AVAILABLE
        </option>
        <option value='2' <?= $widgetMode == 2 ? 'selected="selected"' : '' ?>>
          When business hours are OPEN - or - when agents AVAILABLE
        </option>
        <option value='3' <?= $widgetMode == 3 ? 'selected="selected"' : '' ?>>
          Only when workspace hours are OPEN
        </option>
        <option value='4' <?= $widgetMode == 4 ? 'selected="selected"' : '' ?>>
          Only when agents available
        </option>
        <option value='5' <?= $widgetMode == 5 ? 'selected="selected"' : '' ?>>Always</option>
        <option value='6' <?= $widgetMode == 6 ? 'selected="selected"' : '' ?>>Hide</option>
      </select><br><br>
      <input type="checkbox" id="rlc-widget-maximized-on-load" name="rlc-widget-maximized-on-load" <?= ($maximizedOnLoad ? "checked" : "" )?>>
      <label for="rlc-widget-maximized-on-load"><strong>Open widget maximized on page-load</strong></label><br><br>
      <strong>Which pages will display the widget:</strong><br>
      <div class='pages'>
        <input type='hidden' id='rlc-web-pages' value='<?=$selectedPages?>' name='rlc-web-pages'>
        <div class='list'>
          <?php

            $mainPage = new stdClass();
            $mainPage->ID = 1;
            $mainPage->post_title = 'Home page';

            $pages = get_pages();
            array_unshift($pages, $mainPage);
            $selectedPagesArray = explode(',', $selectedPages);
            $allPagesStatus = true;          
            foreach ( $pages as $page ) {
              $status = in_array($page->ID, $selectedPagesArray);
              $allPagesStatus = $allPagesStatus && $status;
              $option = "<input type='checkbox' pageid='".$page->ID."' id='".$page->ID."' class='rlc-web-pages-value'".($status ? 'checked' : '')." ".(!$statusObject->isDBData || $allPagesAndNew? "disabled" : "" )." >";
              $option .= "<label for=".$page->ID."> ".$page->post_title."</label><br>";
              echo $option;
            }
          ?>
        </div>
        <div style='margin-bottom: 5px;'>
          <input type="checkbox" id="rlc-web-pages-all-current" name="rlc-web-pages-all-current"
            <?=($allPagesStatus ? 'checked' : '').(!$statusObject->isDBData ||  $allPagesAndNew? "disabled" : "" )?>>
          <label for="rlc-web-pages-all-current">All current pages</label>
        </div>
        <div>
          <input type="checkbox" id="rlc-web-pages-all" name="rlc-web-pages-all"
            <?=($allPagesAndNew || !$statusObject->isDBData ? 'checked' : '')?>>
          <label for="rlc-web-pages-all">All pages, including new pages</label>
          <hr>
        </div>
      </div>
      <br>
      <span id='rlc-hash-error'
        class='error <?php if (!$statusObject->pages->getStatus(RAKE_LIVE_CHAT_STATUS_ERROR)) { echo "hidden"; } ?>'>
        <?= $statusObject->pages->getMessage(RAKE_LIVE_CHAT_STATUS_ERROR) ?>
      </span>
    </div>
    <hr>
    <!-- Google Analytics section-->
    <h2>Google Analytics Events:</h2>
    <?php
        $GOOGLE_ANALYTICS_ENABLED = isset($GASettings->isEnabled) ? $GASettings->isEnabled : FALSE;
        function rake_live_chat_getGAEvent ($object, $eventName) {
          if (!isset($object)) {
            return new stdClass();
          }
          $arr = json_decode(json_encode($object), true);
          $i = array_search($eventName, array_column($arr, 'name'));
          $elem=$arr[$i];
          if(isset($elem) && is_array($elem)) {
            return json_decode(json_encode($elem));
          }

          return new stdClass();
        }

        function rake_live_chat_getGAValue ($value, $defaultValue) {
          return isset($value) ? $value : $defaultValue;
        }
      ?>
    <input type="checkbox" id='rlc-ga-enabled-input' name='rlc-ga-enabled-input' <?= $GOOGLE_ANALYTICS_ENABLED ? "checked" : "" ?>>
    <label for='rlc-ga-enabled-input'><strong>Google Analytics Events Enabled</strong></label>
    <p style="font-size: 18px;color: #444;">
     By default, Rake Web Chat widget will send chat related events when there is a <strong>Global Site Tag (gtag.js) on the page</strong>. Rake supports multiple chat related events, each event we send includes event_category, event_action, event_label (optional), and event_value (optional). For details about integrating the widget with Google's Global Site Tag (gtag.js) tracking code for a property, see our documentation (<a
        href="https://docs.rake.ai/platforms/rake-live-chat/use-case-scenarios">documentation</a>).
    </p>
    <span style="font-weight: bold;text-transform: uppercase; cursor: pointer; text-decoration: underline; color: #2196F3" onclick="RakeLiveChat.toggleApiEventSection()">
      show js-api event mapping
    </span>&nbsp;&nbsp;&nbsp;<em>for advanced users</em>
    <div id="google_analytics" class='section hidden'>
      <strong>Set the Event Category:</strong>
      <br><br>
      <span><em class="rlc-settings-required">event_category</em></span>
      <?php
        $EVENT_CATEGORY = isset($GASettings->event_category) ? $GASettings->event_category : 'rake-web-chat';
      ?>
      <input id='rlc-ga-event_category-input' name='rlc-ga-event_category-input' <?= $GOOGLE_ANALYTICS_ENABLED ? "required" : "" ?> value="<?= $EVENT_CATEGORY ?>" placeholder='Paste or type event category from google analytics'>
      <span style="color:grey"><em>This is the same value for all events sent from the Rake web chat widget.</em></span>
      <br><br>
      <strong>Set Actions, Labels, Values:</strong>
      <br><br>
      <div id="rlc-ga-event-list">
        <div id="rlc-ga-events">
          <label for="rlc-ga-event-open" class='rlc-ga-event rlc-ga-event-selected'>open</label>
          <label for="rlc-ga-event-ss" class='rlc-ga-event'>session start</label>
          <label for="rlc-ga-event-se" class='rlc-ga-event'>session end</label>
          <label for="rlc-ga-event-pm" class='rlc-ga-event'>proactive message played</label>
        </div>
        <div id="rlc-ga-events-description">
          <input type="radio" name="rlc-ga-sections" id="rlc-ga-event-open" checked>
          <div>
            <strong>Event: open</strong>&nbsp;&nbsp;&nbsp;
            <?php
              $RLC_GA_OPEN = rake_live_chat_getGAEvent($GASettings->events, 'open');
              $RLC_GA_EVENT_OPEN = isset($RLC_GA_OPEN) ? $RLC_GA_OPEN->isEnabled : true;
            ?>
            <input type="checkbox" id='rlc-ga-event-open-input' name='rlc-ga-event-open-input' <?= $RLC_GA_EVENT_OPEN ? "checked" : "" ?>>
            <label for='rlc-ga-event-open-input'><strong>Enabled</strong></label>
            <br>
            <div class='rlc-ga-event-description'>
              <div>
                <p>The open event precedes a conversation starting, so it is especially useful to know when, where, and what triggers those engagements. The following cases are supported:</p>
                <ul>
                  <li>"click-on-chat" - a user clicks or taps on the chat widget icon</li>
                  <li>"invite" - a user clicks or taps on a proactive message</li>
                  <li>"jsapi" - a user clicks or taps on a button or link on-screen that calls special javascript</li>
                  <li>"proactive-message" - a user clicks or taps a currently displayed proactive message</li>
                </ul>
              </div>
              <div class='rlc-ga-event-grid'>
                 <?php
                  $RLC_GA_OPEN_ACTION_DEFAULT = 'widget-open';
                  $RLC_GA_OPEN_EVENT_DEFAULT = '';

                  $RLC_GA_OPEN_LABEL_COC_DEFAULT = 'click-on-chat';
                  $RLC_GA_OPEN_LABEL_I_DEFAULT = 'invite';
                  $RLC_GA_OPEN_LABEL_API_DEFAULT = 'jsapi';
                  $RLC_GA_OPEN_LABEL_PM_DEFAULT = 'click-on-proactive-{{proactiveMessageName}}';
                ?>
                <div></div>
                <div class='rlc-ga-event-param'><em>event_action</em></div>
                <div class='rlc-ga-event-param'><em>event_label</em></div>
                <div class='rlc-ga-event-param'><em>event_value</em></div>

                <!--click-on-chat-->
                <div class='rlc-ga-event-label'><em>click-on-chat</em></div>
                <div><input type='text' id='rlc-ga-event-open-coc-ea-input'  name='rlc-ga-event-open-coc-ea-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_OPEN->subEvents, 'click-on-chat')->event_action,
                    $RLC_GA_OPEN_ACTION_DEFAULT
                  )
                ?>"></div>
                <div><input type='text' id='rlc-ga-event-open-coc-el-input'  name='rlc-ga-event-open-coc-el-input' value="<?=
                rake_live_chat_getGAValue(
                  rake_live_chat_getGAEvent($RLC_GA_OPEN->subEvents, 'click-on-chat')->event_label,
                  $RLC_GA_OPEN_LABEL_COC_DEFAULT
                )?>"></div>
                <div><input type='text' id='rlc-ga-event-open-coc-ev-input'  name='rlc-ga-event-open-coc-ev-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_OPEN->subEvents, 'click-on-chat')->event_value,
                    $RLC_GA_OPEN_EVENT_DEFAULT
                  )
                ?>"></div>

                <!--invite-->
                <div class='rlc-ga-event-label'><em>invite</em></div>
                <div><input type='text' id='rlc-ga-event-open-i-ea-input'  name='rlc-ga-event-open-i-ea-input' value="<?=
                   rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_OPEN->subEvents, 'invite')->event_action,
                    $RLC_GA_OPEN_ACTION_DEFAULT
                  )?>"></div>
                <div><input type='text' id='rlc-ga-event-open-i-el-input'  name='rlc-ga-event-open-i-el-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_OPEN->subEvents, 'invite')->event_label,
                    $RLC_GA_OPEN_LABEL_I_DEFAULT
                  )?>"></div>
                <div><input type='text' id='rlc-ga-event-open-i-ev-input'  name='rlc-ga-event-open-i-ev-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_OPEN->subEvents, 'invite')->event_value,
                    $RLC_GA_OPEN_EVENT_DEFAULT
                  )?>"></div>

                <!--jsapi-->
                <div class='rlc-ga-event-label'><em>jsapi</em></div>
                <div><input type='text' id='rlc-ga-event-open-api-ea-input'  name='rlc-ga-event-open-api-ea-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_OPEN->subEvents, 'jsapi')->event_action,
                    $RLC_GA_OPEN_ACTION_DEFAULT
                  )?>"></div>
                <div><input type='text' id='rlc-ga-event-open-api-el-input'  name='rlc-ga-event-open-api-el-input' value="<?=
                   rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_OPEN->subEvents, 'jsapi')->event_label,
                    $RLC_GA_OPEN_LABEL_API_DEFAULT
                  )?>"></div>
                <div><input type='text' id='rlc-ga-event-open-api-ev-input'  name='rlc-ga-event-open-api-ev-input' value="<?=rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_OPEN->subEvents, 'jsapi')->event_value,
                    $RLC_GA_OPEN_EVENT_DEFAULT
                  )?>"></div>

                <!--proactive-message-->
                <div class='rlc-ga-event-label'><em>proactive-message</em></div>
                <div><input type='text' id='rlc-ga-event-open-pm-ea-input'  name='rlc-ga-event-open-pm-ea-input' value="<?=
                   rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_OPEN->subEvents, 'proactive-message')->event_action,
                    $RLC_GA_OPEN_ACTION_DEFAULT
                  )?>"></div>
                <div><input type='text' id='rlc-ga-event-open-pm-el-input'  name='rlc-ga-event-open-pm-el-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_OPEN->subEvents, 'proactive-message')->event_label,
                    $RLC_GA_OPEN_LABEL_PM_DEFAULT
                  )?>"></div>
                <div><input type='text' id='rlc-ga-event-open-pm-ev-input'  name='rlc-ga-event-open-pm-ev-input' value="<?=rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_OPEN->subEvents, 'proactive-message')->event_value,
                    $RLC_GA_OPEN_EVENT_DEFAULT
                  )?>"></div>
              </div>
            </div>
          </div>
          <input type="radio" name="rlc-ga-sections" id="rlc-ga-event-ss">
          <div>
            <strong>Event: session start</strong>&nbsp;&nbsp;&nbsp;
            <?php
              $RLC_GA_SS = rake_live_chat_getGAEvent($GASettings->events, 'session-start');
              $RLC_GA_EVENT_SS = isset($RLC_GA_SS) ? $RLC_GA_SS->isEnabled : true;
            ?>
            <input type="checkbox" id='rlc-ga-event-ss-input' name='rlc-ga-event-ss-input' <?= $RLC_GA_EVENT_SS ? "checked" : "" ?>>
            <label for='rlc-ga-event-ss-input'><strong>Enabled</strong></label>
            <br>
            <div class='rlc-ga-event-description'>
              <div>
                <p>The session-start event fires when the first message is sent or received and a session is started. The following cases are supported:</p>
                <ul>
                  <li>"invite" - an agent sends an invitation message </li>
                  <li>"user" - a user types and sends the first message </li>
                  <li>"proactive-message" - a user clicks on a proactive message that has been programmed to start a session immediately.</li>
                </ul>
              </div>
              <div class='rlc-ga-event-grid'>
                 <?php
                  $RLC_GA_SS_ACTION_DEFAULT = 'session-start';
                  $RLC_GA_SS_EVENT_DEFAULT = '';

                  $RLC_GA_SS_LABEL_COC_DEFAULT = 'click-on-chat';
                  $RLC_GA_SS_LABEL_I_DEFAULT = 'invite';
                  $RLC_GA_SS_LABEL_PM_DEFAULT = 'click-on-proactive-{{proactiveMessageName}}';
                ?>
                <div></div>
                <div class='rlc-ga-event-param'><em>event_action</em></div>
                <div class='rlc-ga-event-param'><em>event_label</em></div>
                <div class='rlc-ga-event-param'><em>event_value</em></div>

                <!--click-on-chat-->
                <div class='rlc-ga-event-label'><em>click-on-chat</em></div>
                <div><input type='text' id='rlc-ga-event-ss-coc-ea-input'  name='rlc-ga-event-ss-coc-ea-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_SS->subEvents, 'click-on-chat')->event_action,
                    $RLC_GA_SS_ACTION_DEFAULT
                  )
                ?>"></div>
                <div><input type='text' id='rlc-ga-event-ss-coc-el-input'  name='rlc-ga-event-ss-coc-el-input' value="<?=
                rake_live_chat_getGAValue(
                  rake_live_chat_getGAEvent($RLC_GA_SS->subEvents, 'click-on-chat')->event_label,
                  $RLC_GA_SS_LABEL_COC_DEFAULT
                )?>"></div>
                <div><input type='text' id='rlc-ga-event-ss-coc-ev-input'  name='rlc-ga-event-ss-coc-ev-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_SS->subEvents, 'click-on-chat')->event_value,
                    $RLC_GA_SS_EVENT_DEFAULT
                  )
                ?>"></div>

                <!--invite-->
                <div class='rlc-ga-event-label'><em>invite</em></div>
                <div><input type='text' id='rlc-ga-event-ss-i-ea-input'  name='rlc-ga-event-ss-i-ea-input' value="<?=
                   rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_SS->subEvents, 'invite')->event_action,
                    $RLC_GA_SS_ACTION_DEFAULT
                  )?>"></div>
                <div><input type='text' id='rlc-ga-event-ss-i-el-input'  name='rlc-ga-event-ss-i-el-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_SS->subEvents, 'invite')->event_label,
                    $RLC_GA_SS_LABEL_I_DEFAULT
                  )?>"></div>
                <div><input type='text' id='rlc-ga-event-ss-i-ev-input'  name='rlc-ga-event-ss-i-ev-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_SS->subEvents, 'invite')->event_value,
                    $RLC_GA_SS_EVENT_DEFAULT
                  )?>"></div>

                <!--proactive-message-->
                <div class='rlc-ga-event-label'><em>proactive-message</em></div>
                <div><input type='text' id='rlc-ga-event-ss-pm-ea-input'  name='rlc-ga-event-ss-pm-ea-input' value="<?=
                   rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_SS->subEvents, 'proactive-message')->event_action,
                    $RLC_GA_SS_ACTION_DEFAULT
                  )?>"></div>
                <div><input type='text' id='rlc-ga-event-ss-pm-el-input'  name='rlc-ga-event-ss-pm-el-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_SS->subEvents, 'proactive-message')->event_label,
                    $RLC_GA_SS_LABEL_PM_DEFAULT
                  )?>"></div>
                <div><input type='text' id='rlc-ga-event-ss-pm-ev-input'  name='rlc-ga-event-ss-pm-ev-input' value="<?=rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_SS->subEvents, 'proactive-message')->event_value,
                    $RLC_GA_SS_EVENT_DEFAULT
                  )?>"></div>
              </div>
            </div>
          </div>
          <input type="radio" name="rlc-ga-sections" id="rlc-ga-event-se">
          <div>
            <strong>Event: session end</strong>&nbsp;&nbsp;&nbsp;
            <?php
              $RLC_GA_SE = rake_live_chat_getGAEvent($GASettings->events, 'session-end');
              $RLC_GA_EVENT_SE = isset($RLC_GA_SE) ? $RLC_GA_SE->isEnabled : true;
            ?>
            <input type="checkbox" id='rlc-ga-event-se-input' name='rlc-ga-event-se-input' <?= $RLC_GA_EVENT_SE ? "checked" : "" ?>>
            <label for='rlc-ga-event-se-input'><strong>Enabled</strong></label>
            <br>
            <div class='rlc-ga-event-description'>
              <div>
                <p>This event occurs when a chat session has ended or expired. Only one case is currently supported.</p>
              </div>
              <div class='rlc-ga-event-grid'>
                 <?php
                  $RLC_GA_SE_ACTION_DEFAULT = 'session-end';
                  $RLC_GA_SE_EVENT_DEFAULT = '';
                  $RLC_GA_SE_LABEL_ANY_DEFAULT = '';
                ?>
                <div></div>
                <div class='rlc-ga-event-param'><em>event_action</em></div>
                <div class='rlc-ga-event-param'><em>event_label</em></div>
                <div class='rlc-ga-event-param'><em>event_value</em></div>

                <!--any session end-->
                <div class='rlc-ga-event-label'><em>any session end</em></div>
                <div><input type='text' id='rlc-ga-event-se-ea-input'  name='rlc-ga-event-se-ea-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_SE->subEvents, 'session-end')->event_action,
                    $RLC_GA_SE_ACTION_DEFAULT
                  )
                ?>"></div>
                <div><input type='text' id='rlc-ga-event-se-el-input'  name='rlc-ga-event-se-el-input' value="<?=
                rake_live_chat_getGAValue(
                  rake_live_chat_getGAEvent($RLC_GA_SE->subEvents, 'session-end')->event_label,
                  $RLC_GA_SE_LABEL_ANY_DEFAULT
                )?>"></div>
                <div><input type='text' id='rlc-ga-event-se-ev-input'  name='rlc-ga-event-se-ev-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_SE->subEvents, 'session-end')->event_value,
                    $RLC_GA_SE_EVENT_DEFAULT
                  )
                ?>"></div>
              </div>
            </div>
          </div>
          <input type="radio" name="rlc-ga-sections" id="rlc-ga-event-pm">
          <div>
            <strong>Event: proactive message</strong>&nbsp;&nbsp;&nbsp;
            <?php
              $RLC_GA_PM = rake_live_chat_getGAEvent($GASettings->events, 'proactive-message-played');
              $RLC_GA_EVENT_PM = isset($RLC_GA_PM) ? $RLC_GA_PM->isEnabled : true;
            ?>
            <input type="checkbox" id='rlc-ga-event-pm-input' name='rlc-ga-event-pm-input' <?= $RLC_GA_EVENT_PM ? "checked" : "" ?>>
            <label for='rlc-ga-event-pm-input'><strong>Enabled</strong></label>
            <br>
            <div class='rlc-ga-event-description'>
              <div>
                <p>This event fires when a programmable proactive message is triggered and displayed to a user. Due to a nearly infinte possible options, the name value is passed-through from the Proactive Message Label applied to each message you create in the Rake system.</p>
                <p>If you must customize this, we recommend using a prefix to this pass-through value.</p>
              </div>
              <div class='rlc-ga-event-grid'>
                 <?php
                  $RLC_GA_PM_ACTION_DEFAULT = 'proactive-message-displayed';
                  $RLC_GA_PM_EVENT_DEFAULT = '';

                  $RLC_GA_PM_LABEL_PMD_DEFAULT = '{{proactiveMessageName}}';
                ?>
                <div></div>
                <div class='rlc-ga-event-param'><em>event_action</em></div>
                <div class='rlc-ga-event-param'><em>event_label</em></div>
                <div class='rlc-ga-event-param'><em>event_value</em></div>

                <!--proactive-message-play-->
                <div class='rlc-ga-event-label'><em>proactive-message-play</em></div>
                <div><input type='text' id='rlc-ga-event-pm-pmd-ea-input'  name='rlc-ga-event-pm-pmd-ea-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_PM->subEvents, 'proactive-message')->event_action,
                    $RLC_GA_PM_LABEL_PMD_DEFAULT
                  )
                ?>"></div>
                <div><input type='text' id='rlc-ga-event-pm-pmd-el-input'  name='rlc-ga-event-pm-pmd-el-input' value="<?=
                rake_live_chat_getGAValue(
                  rake_live_chat_getGAEvent($RLC_GA_PM->subEvents, 'proactive-message')->event_label,
                  $RLC_GA_PM_LABEL_PMD_DEFAULT
                )?>"></div>
                <div><input type='text' id='rlc-ga-event-pm-pmd-ev-input'  name='rlc-ga-event-pm-pmd-ev-input' value="<?=
                  rake_live_chat_getGAValue(
                    rake_live_chat_getGAEvent($RLC_GA_PM->subEvents, 'proactive-message')->event_value,
                    $RLC_GA_PM_LABEL_PMD_DEFAULT
                  )
                ?>"></div>
              </div>
            </div>
          </div>
        </div>
    </div>
    </div>
    <div>
      <?php wp_nonce_field( 'rake_live_chat_save_settings', 'nonce' ); ?>
      <input class='submitbtn' id='rlc-settings-save' type='submit' value='SAVE' name='rlc-settings-save' disabled>
      <br>
      <span id='rlc-save-error'
        class='error <?php if (!$statusObject->permission->getStatus(RAKE_LIVE_CHAT_STATUS_ERROR)) { echo "hidden"; } ?>'>
        <?= $statusObject->permission->getMessage(RAKE_LIVE_CHAT_STATUS_ERROR) ?>
      </span>
    </div>
  </form>
</div>