/**
 * @file
 * Password constraint special-character validator plugin.
 */

(function ($) {

  Drupal.behaviors.passwordEnhancementsSpecialCharacterPlugin = {
    attach: function (context, settings) {
      if (context.nodeName === '#document' || context.id === 'password-policy-constraint-ajax-wrapper') {
        // As it is a non-unique constraint, we have to iterate through the
        // available plugins of this type.
        var $fields = $('.constraint[data-constraint="special_character"]');
        $fields.each(function (index, field) {
          // Register our plugin.
          if ($fields.hasOwnProperty(index)) {
            window.dispatchEvent(new CustomEvent('passwordEnhancementsPluginLoad', {
              detail: {
                type: 'special_character',
                id: field.id,
                plugin: new SpecialCharacterPlugin($(field))
              }
            }));
          }
        });
      }
    }
  };

  /**
   * Constructs the special_character constraint plugin.
   */
  function SpecialCharacterPlugin(field) {
    PasswordEnhancementsMinimumCharacters.call(this, field);
  }

  // Inherit methods.
  SpecialCharacterPlugin.prototype = Object.create(PasswordEnhancementsMinimumCharacters.prototype);

  /**
   * Overrides parent validate method.
   */
  SpecialCharacterPlugin.prototype.validate = function (value, settings) {
    var regex;
    if (settings.hasOwnProperty('use_custom_special_characters') && settings['use_custom_special_characters'] === 1) {
      var specialCharacters = settings['special_characters'].replace(/[-[\]{}()*+!<=:?.\/\\^$|#\s,]/g, '\\$&');
      regex = new RegExp('([' + specialCharacters + '])', 'g')
    }
    else {
      regex = new RegExp('([^a-z0-9])', 'gi');
    }

    // Get all special characters.
    var matches = value.match(regex);
    var characters = '';
    if (matches !== null) {
      characters = matches.join('');
    }

    var isValid = PasswordEnhancementsMinimumCharacters.prototype.validate.call(this, characters, settings, true);

    var characterNumber = settings[this.settingName] - value.length;
    var specialCharactersMarkup = '<span data-setting="special_characters">' + settings['special_characters'] + '</span>';
    var message = characterNumber > 1
      ? settings['descriptionPlural']
        .replace('@minimum_characters', '<span data-setting="minimum_characters">' + characterNumber + '</span>')
        .replace('@special_characters', specialCharactersMarkup)
      : settings['descriptionSingular']
        .replace('@special_characters', specialCharactersMarkup);

    if (this.field.html() !== message) {
      this.field.html(message);
    }

    return isValid;
  };

})(jQuery);
