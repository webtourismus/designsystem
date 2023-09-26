((Drupal, drupalSettings, once) => {
  Drupal.behaviors.aos = {
    attach: function attach(context, settings) {
      once('aos', document.documentElement, context).forEach(elem => {
        if (!drupalSettings.path.currentPathIsAdmin) {
          AOS.init(drupalSettings?.aos ?? {duration: 1000})
        }
      });
    },
  };
})(Drupal, drupalSettings, once);
