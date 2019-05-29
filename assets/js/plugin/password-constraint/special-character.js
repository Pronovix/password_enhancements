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
    if (settings.hasOwnProperty('use_custom_special_characters') && settings['use_custom_special_characters']) {
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

    // We have to replace the string arguments here because the JS translation
    // API is not aligned with the Drupal backend translation API.
    var descriptionSingular = settings['descriptionSingular'].replace('@minimum_characters', '!minimum_characters')
      .replace('@special_characters', '!special_characters');
    var descriptionPlural = settings['descriptionPlural'].replace('@minimum_characters', '!minimum_characters')
      .replace('@special_characters', '!special_characters');
    var characterNumber = settings[this.settingName] - value.length;
    var message = Drupal.formatPlural(characterNumber < 1 ? 1 : characterNumber, descriptionSingular, descriptionPlural, {
      '!minimum_characters': '<span data-setting="minimum_characters">' + characterNumber + '</span>',
      '!special_characters': '<span data-setting="special_characters">' + settings['special_characters'] + '</span>'
    });

    if (this.field.html() !== message) {
      this.field.html(message);
    }

    return isValid;
  };

})(jQuery);
