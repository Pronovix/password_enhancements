/**
 * @file
 * Password constraint number validator plugin.
 */

(function ($) {

  Drupal.behaviors.passwordEnhancementsNumberPlugin = {
    attach: function (context, settings) {
      if (context.nodeName === '#document' || context.id === 'password-policy-constraint-ajax-wrapper') {
        // Register our plugin.
        var $field = $('.constraint[data-constraint="number"]');
        window.dispatchEvent(new CustomEvent('passwordEnhancementsPluginLoad', {
          detail: {
            type: 'number',
            id: $field.attr('id'),
            plugin: new NumberPlugin($field)
          }
        }));
      }
    }
  };

  /**
   * Constructs the number constraint plugin.
   */
  function NumberPlugin(field) {
    DpPasswordMinimumCharacters.call(this, field);
  }

  // Inherit methods.
  NumberPlugin.prototype = Object.create(DpPasswordMinimumCharacters.prototype);

  /**
   * Overrides parent validate method.
   */
  NumberPlugin.prototype.validate = function (value, settings) {
    // Get all number characters.
    var matches = value.match(/([0-9])/g);
    var characters = '';
    if (matches !== null) {
      characters = matches.join('');
    }

    return DpPasswordMinimumCharacters.prototype.validate.call(this, characters, settings);
  };

})(jQuery);
