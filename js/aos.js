((Drupal, drupalSettings, once) => {
  Drupal.behaviors.aos = {
    attach: function attach(context, settings) {
      once('aos', document.documentElement, context).forEach(elem => {
        if (!drupalSettings.path.currentPathIsAdmin) {
          Aos.init(drupalSettings?.aos ?? {})
        }
      });
    },
  };
})(Drupal, drupalSettings, once);
