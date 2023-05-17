<?php

namespace Drupal\designsystem\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\designsystem\DesignHelper;
use Drupal\media\Entity\Media;
use Drupal\svg_image_field\Plugin\Field\FieldFormatter\SvgImageFieldFormatter;

/**
 * Plugin implementation of the 'svg_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "svg_image_field_formatter_css",
 *   label = @Translation("SVG Image Field formatter injecting field_css"),
 *   field_types = {
 *     "svg_image_field"
 *   }
 * )
 */
class SvgImageFieldFormatterWithCss extends SvgImageFieldFormatter {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $parentEntity = $items->getEntity();
    if (!($parentEntity instanceof Media) || !$parentEntity->hasField('field_css') || $parentEntity->get('field_css')->isEmpty()) {
      return $elements;
    }
    $classes = [];
    foreach (explode(' ', $parentEntity->get('field_css')->value) as $class) {
      $classes[] = trim($class);
    }
    $classes = array_filter($classes);
    if (empty($classes)) {
      return $elements;
    }


    foreach ($items as $delta => $item) {
      if (!$item->entity) {
        continue;
      }
      if (!$elements[$delta]['#inline']) {
        continue;
      }
      $attributes = DesignHelper::toAttributeObject($elements[$delta]['#attributes']);
      $attributes->addClass($classes);
      $elements[$delta]['#attributes'] = $attributes;
      $svgData = $elements[$delta]['#svg_data'];
      $dom = new \DOMDocument();
      libxml_use_internal_errors(TRUE);
      if (!empty($svgData)) {
        $dom->loadXML($svgData);
      }
      $classString = $dom->documentElement->getAttribute('class');
      if (!empty(trim($classString))) {
        $classes[] = trim($classString);
      }
      $dom->documentElement->setAttribute('class', join(' ', $classes));
      $elements[$delta]['#svg_data'] = $dom->saveXML($dom->documentElement);

    }

    return $elements;
  }
}
