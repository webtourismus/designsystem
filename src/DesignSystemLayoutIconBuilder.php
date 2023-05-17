<?php

namespace Drupal\designsystem;


use Drupal\Core\Layout\Icon\SvgIconBuilder;

/**
 * Builds SVG layout icons, with the option to define "_blank" (whitespace) regions in the icon map.
 */
class DesignSystemLayoutIconBuilder extends SvgIconBuilder {
  /**
   * {@inheritdoc}
   */
  public function build(array $icon_map) {
    $regions = parent::calculateSvgValues($icon_map, $this->width, $this->height, $this->strokeWidth, $this->padding);
    return $this->buildRenderArray($regions, $this->width, $this->height, $this->strokeWidth);
  }

  /**
   * {@inheritdoc}
   */
  protected function buildRenderArray(array $regions, $width, $height, $stroke_width) {
    $build = parent::buildRenderArray($regions, $width, $height, $stroke_width);
    foreach ($build['region'] as $key => $value) {
     if ($key === '_blank') {
        unset($build['region'][$key]);
      }
    }
    return $build;
  }
}
