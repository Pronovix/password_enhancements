/**
 * @file
 * Password constraint validation.
 */

(function ($) {

  // Plugin storage.
  var plugins = {};

  Drupal.behaviors.passwordEnhancementsRequirements = {
    attach: function (context, settings) {
      if (context.nodeName === '#document' || context.id === 'password-policy-constraint-ajax-wrapper') {
        // Always reset the ajaxComplete trigger, otherwise it would be bounded
        // every time when the behaviour being attached.
        $(window).unbind('ajaxComplete', ajaxComplete).ajaxComplete(ajaxComplete);

        // Add event listener for registering the password constraint plugins.
        window.addEventListener('passwordEnhancementsPluginLoad', passwordEnhancementsPluginLoad);

        // Add password field event listeners.
        $('input[name="pass[pass1]"]').keyup(passwordFieldKeyUp);
      }
    },
    detach: function (context, settings, trigger) {
      // On unload unbind events and reset variables.
      if (trigger === 'unload' && context.id === 'password-policy-constraint-ajax-wrapper') {
        window.removeEventListener('passwordEnhancementsPluginLoad', passwordEnhancementsPluginLoad);
        $('input[name="pass[pass1]"]').unbind('keyup', passwordFieldKeyUp);
        plugins = {};
        settings.passwordEnhancementsConstraint.plugins = {};
      }
    }
  };

  /**
   * Ajax complete event.
   *
   * @param {Event} event
   * @param {XMLHttpRequest} xhr
   * @param {object} ajaxOptions
   */
  function ajaxComplete(event, xhr, ajaxOptions) {
    var response = xhr.responseJSON;
    for (var c in response) {
      // Update our settings when ajax completed, by default Drupal merges the
      // settings which is not ideal in this case, because the different plugin
      // set would be mixed up with each other.
      if (response.hasOwnProperty(c) && response[c].command === 'settings' && typeof response[c].settings.passwordEnhancementsConstraint !== "undefined") {
        drupalSettings.passwordEnhancementsConstraint.plugins = response[c].settings.passwordEnhancementsConstraint.plugins;
        drupalSettings.passwordEnhancementsConstraint.minimumRequiredConstraints = response[c].settings.passwordEnhancementsConstraint.minimumRequiredConstraints;

        // Trigger an update on the field.
        $('input[name="pass[pass1]"]').trigger('keyup');
        break;
      }
    }
  }

  /**
   * Key up event on the password field.
   *
   * @param {Event} event
   *   Event object.
   */
  function passwordFieldKeyUp(event) {
    // Show/hide password requirements based on the fields value and
    // requiredness.
    var password = $(this).val();
    var isRequired = typeof $(this).attr('required') !== 'undefined';
    var $requirementsField = $('#password-policy-constraints');
    var isVisible = $requirementsField.css('display') !== 'none';

    if (isVisible && password.length === 0 && !isRequired) {
      $requirementsField.hide();
    }
    else if (!isVisible && password.length !== 0) {
      $requirementsField.show();
    }

    // Validate password with each plugin.
    var settings = drupalSettings.passwordEnhancementsConstraint.plugins;
    var validCount = 0;
    for (var type in plugins) {
      if (plugins.hasOwnProperty(type) && settings.hasOwnProperty(type)) {
        for (var id in plugins[type]) {
          if (plugins[type].hasOwnProperty(id) && settings[type].hasOwnProperty(id)) {
            validCount += plugins[type][id].validate($(this).val(), settings[type][id]) ? 1 : 0;
          }
        }
      }
    }

    // If the minimum constrains are valid mark the rest of the fields as
    // optional.
    if ($requirementsField.find('.constraint[data-validation-passed="yes"]').length >= drupalSettings.passwordEnhancementsConstraint.minimumRequiredConstraints) {
      var optionalFields = $requirementsField.find('.constraint:not([data-validation-passed="yes"])[data-required="no"]');
      optionalFields.attr('data-validation-passed', 'none');
      optionalFields.each(function (index, field) {
        var $field = $(field);
        if ($field.find('em.optional').length === 0) {
          $field.append('<em class="optional">&nbsp;(' + Drupal.t('optional') + ')</em>');
        }
      });
    }
  }

  /**
   * Register plugin validate callback when loaded.
   *
   * @param {CustomEvent} event
   *   Custom event object.
   */
  function passwordEnhancementsPluginLoad(event) {
    if (typeof plugins[event.detail.type] === 'undefined') {
      plugins[event.detail.type] = {};
    }

    plugins[event.detail.type][event.detail.id] = event.detail.plugin;
    $('input[name="pass[pass1]"]').trigger('keyup');
  }

})(jQuery);
