((Drupal, drupalSettings, once) => {
  Drupal.behaviors.splide = {
    attach: function attach(context, settings) {
      if (!drupalSettings.path.currentPathIsAdmin) {
        once('splide', '.splide', context).forEach(elem => {
          let splideInstance = new Splide(elem);
          splideInstance.on('arrows:mounted arrows:updated', function() {
            try {
              const firstImage = splideInstance.Components.Elements.slides[0].querySelector('img');
              if (firstImage.height) {
                splideInstance.Components.Arrows.arrows.prev.style.top = (firstImage.height / 2) + 'px';
                splideInstance.Components.Arrows.arrows.next.style.top = (firstImage.height / 2) + 'px';
              }
            } catch (e) {/*silent fail*/}
          });
          splideInstance.mount();
        });
      }
    }
  };
})(Drupal, drupalSettings, once);
