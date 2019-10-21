/**
 * @file
 * Password enhancements admin form helper.
 */

(function ($) {

  Drupal.behaviors.passwordEnhancementsAdmin = {
    attach: function (context, settings) {
      $('input[name="password_enhancements[expire_password]"]', context).change(passwordEnhancementsAdminChange);
      $('input[name="password_enhancements[expire_warn_before_days]"]', context).change(passwordEnhancementsAdminChange);
    }
  };

  /**
   * Change event on the trigger fields.
   */
  function passwordEnhancementsAdminChange() {
    var expirePassword = $('input[name="password_enhancements[expire_password]"]:checked').length > 0;
    var expireWarnBeforeDays = $('input[name="password_enhancements[expire_warn_before_days]"]').val();
    var $expiryWarningMessage = $('textarea[name="password_enhancements[expiry_warning_message]"]');
    if (expireWarnBeforeDays > 0) {
      $expiryWarningMessage
        .attr('required', 'required')
        .attr('aria-required', 'true')
        .parent().siblings('label')
        .addClass('js-form-required')
        .addClass('form-required');
    }
    else {
      $expiryWarningMessage
        .removeAttr('required')
        .removeAttr('aria-required')
        .parent().siblings('label')
        .removeClass('js-form-required')
        .removeClass('form-required');
    }
  }

})(jQuery);
