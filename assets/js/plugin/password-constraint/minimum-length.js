/**
 * @file
 * Password constraint minimum length validator plugin.
 */

(function ($) {

  Drupal.behaviors.passwordEnhancementsMinimumLengthPlugin = {
    attach: function (context, settings) {
      if (context.nodeName === '#document' || context.id === 'password-policy-constraint-ajax-wrapper') {
        // Register our plugin.
        var $field = $('.constraint[data-constraint="minimum_length"]');
        window.dispatchEvent(new CustomEvent('passwordEnhancementsPluginLoad', {
          detail: {
            type: 'minimum_length',
            id: $field.attr('id'),
            plugin: new MinimumLength($field)
          }
        }));
      }
    }
  };

  /**
   * Constructs the minimum_length constraint plugin.
   */
  function MinimumLength(field) {
    PasswordEnhancementsMinimumCharacters.call(this, field);
  }

  // Inherit methods.
  MinimumLength.prototype = Object.create(PasswordEnhancementsMinimumCharacters.prototype);

})(jQuery);
