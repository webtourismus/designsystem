<?php

namespace Drupal\designsystem\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\style_options\Plugin\Layout\StyleOptionLayoutPlugin;
Use Drupal\designsystem\DesignHelper;

/**
 * Provides an integration between Option Plugins and the Layout API.
 * Like parent, but reverted region picker UI back to vertical tabs.
 */
class StyleOptionsLayout extends StyleOptionLayoutPlugin {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['#process'][] = [$this, 'processForm'];
    return $form;
  }

  private function sortStyleOptions(array $optionIds) {
    if (is_array($optionIds)) {
      uksort($optionIds, function($a, $b) {
        $weightA = $this->getStyleOptionPlugin($a)->getConfiguration('weight') ?? 0;
        $weightB = $this->getStyleOptionPlugin($b)->getConfiguration('weight') ?? 0;
        if ($weightA == $weightB) {
          return 0;
        }
        return $weightA < $weightB ? -1 : 1;
      });
    }
    return $optionIds;
  }

  /**
   * Returns an array of options that apply to the overall layout.
   *
   * @return array
   *   An array of options.
   */
  protected function getLayoutContextDefinition() {
    $definition = parent::getLayoutContextDefinition();
    return $this->sortStyleOptions($definition);
  }

  /**
   * Returns a nested array of option ids for each region.
   *
   * @return array
   *   The option ids.
   */
  protected function getRegionContextDefinitions() {
    $definition = parent::getRegionContextDefinitions();
    foreach ($definition as $key => $region) {
      $definition[$key] = $this->sortStyleOptions($region);
    }
    return $definition;
  }


  /**
   * {@inheritDoc}
   */
  public function processForm(array $element, FormStateInterface $form_state): array {
    $layout_definition_ids = array_keys($this->getLayoutContextDefinition());
    $region_definitions = $this->getRegionContextDefinitions();

  if (count($layout_definition_ids) || count($region_definitions)) {
      $element['region_picker'] = [
        '#type' => 'vertical_tabs',
      ];
      $group = implode('][', array_merge($element['#parents'], ['region_picker']));
    }
    if (count($layout_definition_ids)) {
      $element['layout'] = [
        '#type' => 'details',
        '#title' => $this->t('Layout Settings'),
        '#group' => $group,
        '#open' => TRUE,
      ];
      foreach ($layout_definition_ids as $option_id) {
        $values = $this->configuration['layout'][$option_id] ?? [];
        $element['layout'][$option_id] = $this->getStyleOptionPluginForm($option_id, $element, $form_state, $values);
      }
    }
    if (count($region_definitions)) {
      $regions = $this->getPluginDefinition()->getRegions();
      foreach ($region_definitions as $region_id => $definitions) {
        $element[$region_id] = [
          '#type' => 'details',
          '#title' => $this->t((string)$regions[$region_id]['label']),
          '#group' => $group,
        ];
        foreach (array_keys($definitions) as $option_id) {
          $values = $this->configuration['regions'][$region_id][$option_id] ?? [];
          $element[$region_id][$option_id] = $this->getStyleOptionPluginForm($option_id, $element, $form_state, $values);
        }
      }
    }
    return $element;
  }

  /**
   * {@inheritDoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    foreach (array_keys($this->getLayoutContextDefinition()) as $option_id) {
      $subform_state = SubformState::createForSubform($form['layout'], $form, $form_state);
      $this->configuration['layout'][$option_id] = $this->submitStyleOptionPluginForm($option_id, $form['layout'], $subform_state);
    }
    foreach ($this->getPluginDefinition()->getRegions() as $region => $region_info) {
      foreach (array_keys($this->getRegionContextDefinition($region)) as $option_id) {
        $subform_state = SubformState::createForSubform($form[$region], $form, $form_state);
        $this->configuration['regions'][$region][$option_id] = $this->submitStyleOptionPluginForm($option_id, $form[$region], $subform_state);
      }
    }
    return $form;
  }

  public function build(array $regions) {
    // This is not a static call. Think of it as a "grandparent::build()",
    // having a "$this" context like a "parent::build()".
    // phpcs:ignore
    $build = LayoutDefault::build($regions);

    $layout_definition_ids = array_keys($this->getLayoutContextDefinition());
    $region_definitions = $this->getRegionContextDefinitions();

    foreach ($layout_definition_ids as $option_id) {
      $values = $this->configuration['layout'][$option_id] ?? [];
      $build = $this->buildStyleOptions($option_id, $values, $build, 'layout');
      if ($build['#region_attributes'] ?? []) {
        $build['#region_attributes'] = DesignHelper::toAttributeObject($build['#region_attributes'] ?? []);
        foreach ($region_definitions as $region_id => $definitions) {
          $build[$region_id]['#attributes'] = DesignHelper::toAttributeObject($build[$region_id]['#attributes'] ?? []);

          // Handed down region attributes from layout level should not overwrite self-owned attributes.
          $mergedAttributes = clone $build['#region_attributes'];
          $mergedAttributes->merge($build[$region_id]['#attributes']);
          $build[$region_id]['#attributes'] = $mergedAttributes;
        }
        unset($build['#region_attributes']);
      }
    }

    foreach ($region_definitions as $region_id => $definitions) {
      foreach (array_keys($definitions) as $option_id) {
        $values = $this->configuration['regions'][$region_id][$option_id] ?? [];
        $build[$region_id] = $this->buildStyleOptions($option_id, $values, $build[$region_id] ?? [], $region_id);
      }
    }

    return $build;
  }

  /**
   * Decorates a build array with the option value.
   *
   * @param string $option_id
   *   The option id.
   * @param mixed $values
   *   The option value.
   * @param array $build
   *   The build array to decorate.
   * @param ?string $region_id
   *   Either 'layout' or the region id the build array belongs to.
   *
   * @return array
   *   The decorated build array.
   */
  protected function buildStyleOptions(string $option_id, $values = [], array $build = [], string $region_id = NULL) {
    if ($instance = $this->getStyleOptionPlugin($option_id)) {
      $instance->setValues($values);
      return $instance->build($build, $region_id);
    }
    return[];
  }
}
