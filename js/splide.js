((Drupal, drupalSettings, once) => {
  Drupal.behaviors.splide = {
    attach: function attach(context, settings) {
      if (!drupalSettings.path.currentPathIsAdmin) {
        once('splide', '.splide', context).forEach(elem => {
          let splideInstance = new Splide(elem);
          splideInstance.on('arrows:mounted arrows:updated', function() {
            try {
              const firstImage = splideInstance.Components.Elements.slides[0].querySelector('picture, img');
              /* setTimeout due race condition in firefox with responsive images */
              window.setTimeout(function() {
                if (firstImage?.height > 0) {
                  splideInstance.Components.Arrows.arrows.prev.style.top = (firstImage.height / 2) + 'px';
                  splideInstance.Components.Arrows.arrows.next.style.top = (firstImage.height / 2) + 'px';
                }
              }, 50);
            } catch (e) {/*silent fail*/}
          });
          splideInstance.mount();
          let ev = new CustomEvent('splide:mounted', {detail: {splide: splideInstance}});
          document.dispatchEvent(ev);
        });
      }
    }
  };
})(Drupal, drupalSettings, once);
