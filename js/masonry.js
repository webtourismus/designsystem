((Drupal, drupalSettings, once) => {
  Drupal.behaviors.masonry = {
    attach: function attach(context, settings) {
      once('masonry', '.area-masonry-main').forEach(elem => {
        drupalSettings.masonry = drupalSettings.masonry || {};
        drupalSettings.masonry.reflow = drupalSettings.masonry.reflow || function(masonryInstance, reloadItems = false) {
          if (!masonryInstance) {
            return;
          }
          if (masonryInstance?.isReflowing === true) {
            return;
          }
          masonryInstance.isReflowing = true;
          if (reloadItems) {
            masonryInstance.reloadItems();
          }
          masonryInstance.layout();
          masonryInstance.isReflowing = false;
        }
        drupalSettings.masonry.settings = drupalSettings.masonry.settings || {};
        drupalSettings.masonry.settings.itemSelector = drupalSettings.masonry.settings.itemSelector || '.component';
        let masonryInstance = new Masonry(elem, drupalSettings.masonry.settings);
        if (drupalSettings.path.currentPathIsAdmin || drupalSettings?.gin) {
          drupalSettings.masonry.reflow(masonryInstance);
        }
      });
      if (drupalSettings.path.currentPathIsAdmin) {
        once('window-trigger-reflow-after-pagebuilder-visible', 'a[href="#edit-group-full-content"]').forEach(elem => {
          elem.addEventListener('click', () => window.dispatchEvent(new Event('resize')));
        });
        once('masonry-trigger-areflow-after-editor-action', '.area-masonry-main').forEach(elem => {
          const observer = new MutationObserver((mutations, observer) => {
            // Do not instantly act on mutations, to prevent Masonry calculating mutated
            // components layout height as zero.
            window.setTimeout(drupalSettings.masonry.reflow, 100, Masonry.data(elem), true);
          });
          observer.observe(elem, {attributes: true, childList: true});
        });
      }
    },
  };
})(Drupal, drupalSettings, once);
