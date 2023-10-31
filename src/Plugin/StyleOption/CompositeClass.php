<?php

declare(strict_types=1);

namespace Drupal\designsystem\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\style_options\Plugin\StyleOptionPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define the Composite class style option plugin.
 *
 * This plugin transforms mulitple YAML options into Drupal form API elements
 *
 * @TODO: this concept only works for "(top|left)-(1|2|3)"
 *        but we also need things like "(top|left)-(1|2|3) sm:(top|left)-(2|4|6) md:(top|left)-(2|4|10)" etc.
 *        therefore not in use yet
 *
 * @code
 * my_example_composite_class_style_option:
 *   plugin: composite_class
 *   label: 'My custom HTML class composed by multiple input fields'
 *   description: 'A description for the editor, visible in the admin UI'
 *   # The name the render element object attribute. Defaults to '#attributes'.
 *   # This attribute can be used in Twig like '<div {{ wrapper_attributes }}>'
 *   twig_attribute_object: '#wrapper_attributes
 *   # Each partly value must be a direct subkey of "inputs".
 *   # Each partly value can be confiigured like the single input "value" in \Drupal\designsystem\Plugin\StyleOption\HtmlAttribute
 *   inputs:
 *     # add subkeys as desired
 *     part_1:
 *       # The form API render element type for this plugin.
 *       # @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21FormElementInterface.php/interface/implements/FormElementInterface/10
 *       # @see \Drupal\designsystem\Plugin\StyleOption\HtmlAttribute
 *     part_2:
 *       # The form API render element type for this plugin.
 *       # @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21FormElementInterface.php/interface/implements/FormElementInterface/10
 *       # @see \Drupal\designsystem\Plugin\StyleOption\HtmlAttribute
 *   # Final composite value _must_ contain all subkeys of "inputs" surrounded by "@"
 *   composite: "my_class_@part_1@_and_@part_2@_combined"
 *   # Sort elements by weight in the Style options admin UI. Defaults to 0.
 *   # Requires \Drupal\designsystem\Plugin\Layout\StyleOptionsLayout as layout plugin class.
 *   weight: 10
 * @endcode
 *
 * @StyleOption(
 *   id = "composite_class",
 *   label = @Translation("Composite CSS class")
 * )
 */
class CompositeClass extends StyleOptionPluginBase {

  /**
   * @var $designHelper DesignHelper
   */
  protected $designHelper;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->designHelper = $container->get('designsystem.helper');
    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {

    $containerTitle = NULL;
    if ($title = $this->getConfiguration('label')) {
      $containerTitle = Markup::create('<label class="form-item__label">' . $this->t((string)$title)->render() . '</label>');
    }
    $containerDescription = NULL;
    if ($desc = $this->getConfiguration('description')) {
      $containerDescription = Markup::create('<div class="form-item__description">' . $this->t((string)$desc)->render() . '</div>');
    }
    $form['composite_class'] = [
      '#type' => 'container',
      '#prefix' => $containerTitle,
      '#suffix' => $containerDescription,
      '#tree' => TRUE,
    ];
    $defaultContainerFormApiParameters = [
      '#attributes' => [
        'class' => [
          'flex',
          'flex-row',
          'flex-nowrap',
          'w-full',
          'gap-4'
        ]
      ]
    ];

    foreach($this->getConfiguration('inputs') as $key => $config) {
      $form['composite_class'][$key] = $this->getFormInput($key, $config, $form, $form_state);
    }

    return $form;
  }

  protected function getFormInput(string $key, array $config, array $form, FormStateInterface $form_state): array {
    $result = [
      '#type' => $config['input_type'] ?? 'textfield',
      '#title' => $config['label'] ?? NULL,
      '#default_value' => $this->getValue("composite_value_{$key}") ?? $config['default'] ?? NULL,
      '#description' => $config['description'] ?? NULL,
      '#required' => $config['required'] ?? NULL,
    ];
    $defaultFormApiParameters = [
      '#wrapper_attributes' => [
        'class' => [
          'basis-full',
          'grow',
          'shrink',
          'my-0',
        ]
      ]
    ];
    $formApiParameters = array_merge($defaultFormApiParameters, $config['form_api'] ?? []);
    if ($formApiParameters) {
      foreach ($formApiParameters as $hashParameter => $formApiParameter) {
        $result[$hashParameter] = $formApiParameter;
      }
    }

    $options = $config['options'] ?? NULL;
    if ($options) {
      if (!in_array($result['#type'], ['select', 'checkbox', 'checkboxes', 'radios'])) {
        $result['#type'] = 'select';
      }
      if (is_array($options)) {
        foreach ($options as $key => $option) {
          if (is_array($option)) {
            $options[$key] = $this->t(...$option);
          }
          elseif (is_string($option)) {
            $options[$key] = $this->t($option);
          }
        }
      }
      $result['#multiple'] = FALSE;
      $result['#options'] = $options;
    }
    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function submitConfigurationForm(
    array &$form,
    FormStateInterface $form_state
  ): void {
    $values = [];
    if ($form_state->hasValue('composite_class')) {
      foreach ($form_state->getValue('composite_class') as $key => $input) {
        $values["composite_value_{$key}"] = $form_state->getValue(['composite_class', $key]);
      }
    }
    $this->setValues($values);
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    if (empty($this->getValues())) {
      return $build;
    }
    if (empty($this->getConfiguration('composite'))) {
      return $build;
    }

    $twigAttributeVariableName = $this->getConfiguration('twig_attribute_object') ?? '#attributes';
    $build[$twigAttributeVariableName] = $this->designHelper->toAttributeObject($build[$twigAttributeVariableName] ?? []);

    $values = [];
    foreach ($this->getValues() as $key => $value) {
      if (!str_starts_with($key, 'composite_value_')) {
        continue;
      }
      $key = str_replace('composite_value_', '', $key);
      if ($preg_replace = $this->getConfiguration($key)['value_preg_replace'] ?? FALSE) {
        $values["@{$key}@"] = preg_replace($preg_replace['pattern'], $preg_replace['replacement'], $value, $preg_replace['limit'] ?? -1);
      }
      if (empty($values["@{$key}@"])) {
        $values["@{$key}@"] = 'INVALID_PREG_REPLACE_SUBJECT';
      }
      $build[$twigAttributeVariableName]->setAttribute("data-composite-class-{$key}", $values["@{$key}@"]);
    }

    if (empty($values)) {
      return $build;
    }

    $classes = strtr($this->getConfiguration('composite'), $values);
    $classes = explode(' ', $classes);
    $build[$twigAttributeVariableName]->addClass($classes);

    return $build;
  }

}
