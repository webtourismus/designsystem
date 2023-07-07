((Drupal, drupalSettings, once) => {
  Drupal.behaviors.glightbox = {
    attach: function attach(context, settings) {
      if (!drupalSettings.path.currentPathIsAdmin) {
        once('glightbox', document.documentElement, context).forEach(elem => {
          if (!drupalSettings.path.currentPathIsAdmin) {
            GLightbox({'selector': '[target="modal"]'});
          }
        });
      }
    }
  };
})(Drupal, drupalSettings, once);
