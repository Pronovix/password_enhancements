/**
 * @file
 * Base class for the plugins.
 */

/**
 * Base plugin object constructor.
 *
 * @param {jQuery} field
 *   Constraint field.
 */
function DpPasswordConstraintPlugin(field) {
  // Initialize variables.
  this.field = field;
  this.updateEffect = drupalSettings.passwordEnhancementsConstraint.updateEffect;
}

/**
 * Marks the constraint field passed.
 */
DpPasswordConstraintPlugin.prototype.validationPassed = function () {
  this.field.attr('data-validation-passed', 'yes');

  switch (this.updateEffect) {
    case 'hide':
      this.field.hide();
      break;

    case 'strikethrough':
      this.field.css('text-decoration', 'line-through');
      break;
  }
};

/**
 * Marks the constraint field not passed.
 */
DpPasswordConstraintPlugin.prototype.validationNotPassed = function () {
  this.field.attr('data-validation-passed', 'no');

  switch (this.updateEffect) {
    case 'hide':
      this.field.show();
      break;

    case 'strikethrough':
      this.field.css('text-decoration', 'initial');
      break;
  }
};
