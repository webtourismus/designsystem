((Drupal, drupalSettings, once) => {
  Drupal.behaviors.splide = {
    attach: function attach(context, settings) {
      if (!drupalSettings.path.currentPathIsAdmin) {
        function addSplideEvents(splideInstance) {
          splideInstance.on('arrows:mounted arrows:updated', function () {
            try {
              const firstImage = splideInstance.Components.Elements.slides[0].querySelector('picture, img');
              /* setTimeout due race condition in firefox with responsive images */
              window.setTimeout(function () {
                if (firstImage?.height > 0) {
                  splideInstance.Components.Arrows.arrows.prev.style.top = (firstImage.height / 2) + 'px';
                  splideInstance.Components.Arrows.arrows.next.style.top = (firstImage.height / 2) + 'px';
                }
              }, 50);
            } catch (e) {/*silent fail*/ }
          });
        };

        function dispatchCreatedEvent(splideInstance) {
          let ev = new CustomEvent('splide:mounted', { detail: { splide: splideInstance } });
          document.dispatchEvent(ev);
        }

        once('splide', '.splide', context).forEach(elem => {
          if (elem.dataset.isNavigation) {
            return;
          }

          let splideInstance = new Splide(elem);
          addSplideEvents(splideInstance);

          let navSplideInstance = null;

          if (elem.dataset.hasNavigation) {
            const navSplide = document.querySelector(elem.dataset.hasNavigation);
            navSplideInstance = new Splide(navSplide);
            addSplideEvents(navSplideInstance);

            // Sync with navigation.
            splideInstance.sync(navSplideInstance);
          }

          splideInstance.mount();
          dispatchCreatedEvent(splideInstance);

          // Mount if nav.
          if (navSplideInstance) {
            navSplideInstance.mount();
            dispatchCreatedEvent(splideInstance);
          }
        });
      }
    }
  };
})(Drupal, drupalSettings, once);
