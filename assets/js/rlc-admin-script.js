const RakeLiveChat = {
  toggleApiEventSection: () => {
    document.getElementById('google_analytics').classList.toggle('hidden')
  },
  webPagesWatcher: (inputId, allPagesId, pagesClass, selectAllId, hashHandler) => {
    const input = document.getElementById(inputId);
    const allPages = document.getElementById(allPagesId);
    const selectAll = document.getElementById(selectAllId);
    const pages = Array.from(document.getElementsByClassName(pagesClass));

    allPages.addEventListener('change', (e) => {
      const status = e.target.checked;
      [...pages, selectAll].forEach(page => {
        page.disabled = status;
      });
      hashHandler();
    });

    selectAll.addEventListener('change', (e) => {
      const ids = [];
      pages.forEach(page => {
        page.checked = e.target.checked;
        if (e.target.checked) {
          ids.push(page.getAttribute('pageid'));
        }
      });
      input.value = ids;
      hashHandler();
    });

    pages.forEach(page => {
      page.addEventListener('change', (e) => {
        const status = e.target.checked;
        if (!status) {
          selectAll.checked = false;
        } else {
          selectAll.checked = !pages.find(page => !page.checked);
        }
        const pageId = e.target.getAttribute('pageid');
        input.value = Array.from(status ?
          new Set([...(input.value.split(',') || []), pageId]) :
          (input.value.split(',') || []).filter(e => e != pageId)
        ).join(',');
        hashHandler();
      });
    });
  }
};



function webPagesSetup(features, trigger) {
  const {
    webPages: webPagesKey,
    webPagesValues: webPagesValuesKey,
    webPagesAllCurrent: webPagesAllCurrentKey,
    webPagesAll: webPagesAllKey
  } = features;
  const webPagesAllCurrent = document.getElementById(webPagesAllCurrentKey);
  const webPagesAll = document.getElementById(webPagesAllKey);
  const webPagesValues = Array.from(document.getElementsByClassName(webPagesValuesKey));
  const webPages = document.getElementById(webPagesKey);

  webPagesAllCurrent.addEventListener('change', (e) => {
    webPagesValues.forEach(page => {
      page.checked = e.target.checked;
    });
    trigger();
  });

  webPagesAll.addEventListener('change', (e) => {
    const status = e.target.checked;
    [...webPagesValues, webPagesAllCurrent].forEach(page => {
      page.disabled = status;
    });
    trigger();
  });

  webPagesValues.forEach(page => {
    page.addEventListener('change', (e) => {

      if (!e.target.checked) {
        webPagesAllCurrent.checked = false;
      } else {
        if (!Array(...webPagesValues).find(page => !page.checked)) {
          webPagesAllCurrent.checked = true;
        }
      }

      const pageId = e.target.getAttribute('pageid');
      const token = `${pageId}`;
      webPages.value = (e.target.checked ? `${webPages.value},${token}` : webPages.value.replace(`${token}`,
        ''))
        .replace(/(^,+|,+$)/g, '')
        .replace(/,+/gm, ',');
      trigger();
    });
  });

}

function rlcValidationSetup() {

  const btn = document.getElementById('rlc-settings-save');
  const env = document.getElementById('rlc-env');
  const loader = document.getElementById('rlc-bot-status-loader');
  const hash = document.getElementById('rlc-hash');
  const hashSpan = document.getElementById('rlc-hash-span');

  const jsAPI = {
    widgetMode: document.getElementById('rlc-widget-mode'),
    hashInput: document.getElementById('rlc-hash-input'),
  };

  const hashObject = {
    status: {
      empty: document.getElementById('rlc-hash-span-text'),
    },
    img: {
      success: document.getElementById('rlc-hash-success'),
      error: document.getElementById('rlc-hash-error-img'),
      empty: document.getElementById('rlc-hash-empty-img'),
    },
    notifications: {
      error: document.getElementById('rlc-hash-error'),
      warning: document.getElementById('rlc-hash-warning')
    }
  };

  const workspaceObject = {
    status: {
      enabled: document.getElementById('rlc-bot-status-enabled'),
      disabled: document.getElementById('rlc-bot-status-disabled'),
      unknown: document.getElementById('rlc-bot-status-unknown'),
      empty: document.getElementById('rlc-bot-status-empty-text')
    },
    img: {
      fail: document.getElementById('rlc-bot-status-fail'),
      success: document.getElementById('rlc-bot-status-success'),
      empty: document.getElementById('rlc-bot-status-empty'),
    },
    notifications: {
      noWorkspace: document.getElementById('rlc-bot-status-no-workspace'),
      noInfo: document.getElementById('rlc-bot-status-no-info'),
    }
  };

  const hideAll = () => {
    [
      ...Object.values(workspaceObject.status),
      ...Object.values(workspaceObject.img),
      ...Object.values(workspaceObject.notifications),
      ...Object.values(hashObject.status),
      ...Object.values(hashObject.img),
      ...Object.values(hashObject.notifications),
    ].forEach(e => e.classList.add('hidden'));

    btn.disabled = true;
    workspaceObject.img.success.classList.remove('fillSuccess');
    workspaceObject.img.success.classList.remove('fillWarning');
  };
  const emptyHandler = {
    hash: (v) => {
      hashObject.status.empty.classList[v ? 'add' : 'remove']('hidden');
      hashObject.img.empty.classList[v ? 'add' : 'remove']('hidden');
    },
    workspace: (v) => {
      workspaceObject.status.empty.classList[v ? 'add' : 'remove']('hidden');
      workspaceObject.img.empty.classList[v ? 'add' : 'remove']('hidden');
    }
  };

  const hashHandler = (isRequiredValidation = true) => {
    const v = hash.value.trim();

    hideAll();

    emptyHandler.hash(v);
    emptyHandler.workspace(v);

    if (!v) {
      return;
    }

    const statusHashValidation = /^wwc_\d+_\d+$/.test(v);

    if (!statusHashValidation) {

      hashObject.img.error.classList.remove('hidden');
      hashObject.notifications.error.classList.remove('hidden');

      emptyHandler.workspace('');
      return;
    }

    hashObject.img.success.classList.remove('hidden');

    loader.classList.remove('hidden');

    const data = {
      action: 'rake_live_chat_get_bot_status_http_cb',
      hash: hash.value,
      env: env.value
    };

    jQuery.get(ajaxurl, data, function (response) {
      loader.classList.add('hidden');
      const {
        status,
        exists,
        workspace = {}
      } = JSON.parse(response);

      const isGreen = status && exists && workspace;
      const isGrey = status && exists && !workspace;

      btn.disabled = !(isGreen || isGrey);

      if (!exists) {
        workspaceObject.status.unknown.classList.remove('hidden');
        workspaceObject.img.fail.classList.remove('hidden');
        workspaceObject.notifications.noInfo.classList.remove('hidden');
        return;
      }

      if (!status) {
        workspaceObject.status.disabled.classList.remove('hidden');
        workspaceObject.img.fail.classList.remove('hidden');
        workspaceObject.notifications.noInfo.classList.remove('hidden');
        return;
      }

      workspaceObject.status.enabled.classList.remove('hidden');
      workspaceObject.img.success.classList.remove('hidden');

      if (isGreen) {
        workspaceObject.img.success.classList.add('fillSuccess');
        return;
      }

      if (isGrey) {
        workspaceObject.img.success.classList.add('fillWarning');
        workspaceObject.notifications.noWorkspace.classList.remove('hidden');
      }
    });
  }

  RakeLiveChat.webPagesWatcher(
    'rlc-web-pages',
    'rlc-web-pages-all',
    'rlc-web-pages-value',
    'rlc-web-pages-all-current',
    hashHandler
  );
  const hashParser = (e) => {
    const hashValue = jsAPI.hashInput.value;
    hashSpan.innerText = hashValue;
    hash.value = hashValue;

    hashHandler();
  };
  jsAPI.hashInput.addEventListener('input', hashParser);
  jsAPI.hashInput.addEventListener('change', hashParser);

  [jsAPI.widgetMode]
    .forEach(e => e.addEventListener('change', hashHandler));

  [hash]
    .forEach(e => {
      e.addEventListener('input', hashHandler);
      e.addEventListener('propertychange', hashHandler);
    });


  return hashHandler;
}
document.addEventListener('DOMContentLoaded', function (event) {
  const hashHandler = rlcValidationSetup();

  /**
   * Google analytics section
   */
  const gaEventsList = document.getElementsByClassName('rlc-ga-event');
  const cleanSelected = () => {
    for (const e of gaEventsList) {
      e.classList.remove('rlc-ga-event-selected');
    }
  }
  for (const e of gaEventsList) {
    e.addEventListener('click', () => {
      cleanSelected();
      e.classList.add('rlc-ga-event-selected');
    })
  }

  const gaEventsSettings = document.querySelectorAll('input[id*="rlc-ga-event"][type="text"],input[id*="rlc-ga-event"][type="checkbox"]');
  for (const e of gaEventsSettings) {
    e.addEventListener('change', hashHandler);
    e.addEventListener('input', hashHandler);
  }
  ['rlc-ga-enabled-input', 'rlc-ga-event_category-input', 'rlc-widget-maximized-on-load'].forEach(id => {
    const elem = document.getElementById(id);
    elem.addEventListener('change', hashHandler);
    elem.addEventListener('input', hashHandler);
  })

  document.getElementById("rlc-ga-enabled-input").addEventListener('change', (e) => {
    if (e.target.checked) {
      document.getElementById("rlc-ga-event_category-input").setAttribute("required", "required");
    } else {
      document.getElementById("rlc-ga-event_category-input").removeAttribute("required");
    }
  })

}, false);
