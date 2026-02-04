(function ($, Drupal, once) {
  Drupal.behaviors.eventRegistration = {
    attach: function (context, settings) {
      // Find elements with the 'er-form' class once
      const forms = once('eventRegInit', '.er-form', context);

      forms.forEach(function (form) {
        console.log('Event Registration frontend initialized.');

        // Example: Add a "active" class to form items when focused
        const inputs = form.querySelectorAll('.form-element');
        inputs.forEach(input => {
          input.addEventListener('focus', () => {
            input.closest('.form-item').classList.add('is-focused');
          });
          input.addEventListener('blur', () => {
            input.closest('.form-item').classList.remove('is-focused');
          });
        });
      });
    }
  };
})(jQuery, Drupal, once);