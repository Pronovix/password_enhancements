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
        if (typeof $field !== 'undefined') {
          window.dispatchEvent(new CustomEvent('passwordEnhancementsPluginLoad', {
            detail: {
              type: 'lower_case',
              id: $field.attr('id'),
              plugin: new LowerCase($field)
            }
          }));
        }
      }
    }
  };

  /**
   * Constructs the lower-case constraint plugin.
   */
  function LowerCase(field) {
    DpPasswordMinimumCharacters.call(this, field);
  }

  // Inherit methods.
  LowerCase.prototype = Object.create(DpPasswordMinimumCharacters.prototype);

  /**
   * Overrides parent validate method.
   */
  LowerCase.prototype.validate = function (value, settings) {
    // Get all lower-cased characters.
    var matches = value.match(/([a-z])/g);
    var characters = '';
    if (matches !== null) {
      characters = matches.join('');
    }

    return DpPasswordMinimumCharacters.prototype.validate.call(this, characters, settings);
  };

})(jQuery);
