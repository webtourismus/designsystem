<?php

namespace Drupal\designsystem\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use enshrined\svgSanitize\Sanitizer;

/**
 * Custom twig functions.
 */
class SvgAttributes extends AbstractExtension {
  public function getFilters() {
    return [
      new TwigFilter('add_to_svg', [$this, 'addToSvg'], ['is_safe' => ['html']]),
      new TwigFilter('remove_from_svg', [$this, 'removeFromSvg'], ['is_safe' => ['html']]),
    ];
  }

  public function addToSvg(
    string $svg,
    string|array|null $classes = [],
    ?array $attributes = NULL,
  ) {
    if (empty($svg)) {
      return;
    }
    $dom = new \DOMDocument();
    $dom->loadXML($svg);
    $root = $dom->getElementsByTagName('svg')->item(0);
    if (!empty($classes)) {
      /** @var \DomElement $root */
      $oldClasses = $root->getAttribute('class') ?? '';
      $oldClasses = explode(' ', $oldClasses);
      $newClasses = $classes;
      if (is_string($newClasses)) {
        $newClasses = explode(' ', $newClasses);
      }
      $root->setAttribute('class', join(' ', array_unique(array_filter(array_merge($oldClasses, $newClasses)))));
    }
    if (!empty($attributes)) {
      foreach($attributes as $name => $value) {
        $root->setAttribute($name, $value);
      }
    }
    return $dom->saveXML($dom->documentElement);
  }


  public function removeFromSvg(
    string $svg,
    string|array|null $classes = [],
    string|array|null $attributes = []
  ) {
    if (empty($svg)) {
      return;
    }
    $dom = new \DOMDocument();
    $dom->loadXML($svg);
    $root = $dom->getElementsByTagName('svg')->item(0);
    if (!empty($classes)) {
      /** @var \DomElement $root */
      $oldClasses = $root->getAttribute('class') ?? '';
      $oldClasses = explode(' ', $oldClasses);
      $removeClasses = $classes;
      if (is_string($removeClasses)) {
        $removeClasses = explode(' ', $removeClasses);
      }
      $remainingClasses = array_filter(array_diff($oldClasses, $removeClasses));
      if (empty($remainingClasses)) {
        $root->removeAttribute('class');
      }
      else {
        $root->setAttribute('class', join($remainingClasses, ' '));
      }
    }
    if (!empty($attributes)) {
      if (is_string($attributes)) {
        $attributes = array_filter(explode(' ', $attributes));
      }
      foreach($attributes as $attr) {
        $root->removeAttribute($attr);
      }
    }
    return $dom->saveXML($dom->documentElement);
  }
}
