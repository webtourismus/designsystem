<?php

namespace Drupal\designsystem\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use enshrined\svgSanitize\Sanitizer;

/**
 * Custom twig functions.
 */
class AddToSvg extends AbstractExtension {
  public function getFilters() {
    return [
      new TwigFilter('add_to_svg', [$this, 'addToSvg'], ['is_safe' => ['html']]),
    ];
  }

  public function addToSvg(
    string $svg,
    string|array|null $classes = [],
    ?array $attributes = NULL
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
      array_walk($oldClasses, 'trim');
      $newClasses = $classes;
      if (is_string($newClasses)) {
        $newClasses = explode(' ', $newClasses);
      }
      array_walk($newClasses, 'trim');
      $root->setAttribute('class', join(' ', array_unique(array_merge($oldClasses, $newClasses))));
    }
    if (!empty($attributes)) {
      foreach($attributes as $name => $value)
      $root->setAttribute($name, $value);
    }
    return $dom->saveXML($dom->documentElement);
  }

}
