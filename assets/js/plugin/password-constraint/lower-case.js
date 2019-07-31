/**
 * @file
 * Password constraint lower-case validator plugin.
 */

(function ($) {

  Drupal.behaviors.passwordEnhancementsLowerCasePlugin = {
    attach: function (context, settings) {
      if (context.nodeName === '#document' || context.id === 'password-policy-constraint-ajax-wrapper') {
        // Register our plugin.
        var $field = $('.constraint[data-constraint="lower_case"]');
        window.dispatchEvent(new CustomEvent('passwordEnhancementsPluginLoad', {
          detail: {
            type: 'lower_case',
            id: $field.attr('id'),
            plugin: new LowerCasePlugin($field)
          }
        }));
      }
    }
  };

  /**
   * Constructs the lower-case constraint plugin.
   */
  function LowerCasePlugin(field) {
    PasswordEnhancementsMinimumCharacters.call(this, field);
  }

  // Inherit methods.
  LowerCasePlugin.prototype = Object.create(PasswordEnhancementsMinimumCharacters.prototype);

  /**
   * Overrides parent validate method.
   */
  LowerCasePlugin.prototype.validate = function (value, settings) {
    // Get all lower-cased characters.
    var matches = value.match(/([a-z])/g);
    var characters = '';
    if (matches !== null) {
      characters = matches.join('');
    }

    return PasswordEnhancementsMinimumCharacters.prototype.validate.call(this, characters, settings);
  };

})(jQuery);
