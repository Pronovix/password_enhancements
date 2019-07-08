/**
 * @file
 * Defines the minimum characters base class for constraint plugins.
 */

/**
 * Constructs the base object for the related constraint plugins.
 *
 * @param {jQuery} field
 *   Constraint field.
 */
function PasswordEnhancementsMinimumCharacters(field) {
  PasswordEnhancementsConstraintPlugin.call(this, field);

  this.settingName = 'minimum_characters';
}

// Inherit methods.
PasswordEnhancementsMinimumCharacters.prototype = Object.create(PasswordEnhancementsConstraintPlugin.prototype);

/**
 * Plugin's validation callback.
 *
 * @param {string} value
 *   The value that needs to be validated.
 * @param {Array} settings
 *   Plugin related settings.
 * @param {boolean} customMessage
 *   Set if the child object has to build the message differently.
 *
 * @return {boolean}
 *   TRUE if the validation is valid, FALSE otherwise.
 */
PasswordEnhancementsMinimumCharacters.prototype.validate = function (value, settings, customMessage) {
  if (typeof customMessage === 'undefined') {
    customMessage = false;
  }

  if (settings.hasOwnProperty(this.settingName)) {
    // Update field style.
    var isValid;
    if (settings[this.settingName] <= value.length) {
      this.validationPassed();
      isValid = true;
    }
    else {
      this.validationNotPassed();
      isValid = false;
    }

    // Update field's value if available.
    var valueField = this.field.find('span[data-setting="' + this.settingName + '"]');
    var characterNumber = settings[this.settingName] - value.length;
    characterNumber = characterNumber >= 0 ? characterNumber : 0;
    if (valueField.length > 0) {
      valueField.html(characterNumber);
    }

    // Update message if needed.
    if (!customMessage) {
      var message = characterNumber > 1
        ? settings['descriptionPlural'].replace('@minimum_characters', '<span data-setting="minimum_characters">' + characterNumber + '</span>')
        : settings['descriptionSingular'];

      if (this.field.html() !== message) {
        this.field.html(message);
      }
    }
  }

  return isValid;
};
