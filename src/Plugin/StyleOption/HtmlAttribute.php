<?php

declare(strict_types=1);

namespace Drupal\designsystem\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\style_options\Plugin\StyleOptionPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define the HTML attribute style option plugin.
 *
 * This plugin transforms YAML options to a Drupal form API element
 *
 * @code
 * my_example_html_attribute_style_option:
 *   plugin: html_attribute
 *   label: 'My custom HTML attribute'
 *   description: 'A description for the editor, visible in the admin UI'
 *   # The name of the HTML attribute, defaults to "class".
 *   attribute_name: data-my-attribute
 *   # The name the render element object attribute. Defaults to '#attributes'.
 *   # This attribute can be used in Twig like '<div {{ wrapper_attributes }}>'
 *   twig_attribute_object: '#wrapper_attributes
 *   # The form API render element type for this plugin.
 *   # @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21FormElementInterface.php/interface/implements/FormElementInterface/10
 *   # If no options are given, the default input type is a textfield.
 *   # If options are given, the default input type is select.
 *   input_type: select
 *   # For <select>, '<input type=checkbox>' and similar. Flat array or associative array.
 *   # If options is an associative array, the array key is used as HTML attribute value
 *   # and the array value as translatable admin label.
 *   options:
 *     light: light mode
 *     dark: dark mode
 *   # Defaults to FALSE.
 *   multiple: TRUE
 *   # Defaults to FALSE.
 *   required: TRUE
 *   # Advanced options to pass arbitrary "hash keys" to the form API render element.
 *   # E.g. when using a number element this could be used for
 *   form_api:
 *     '#min': 0
 *     '#max': 100
 *     '#step': 10
 *   # Preprocess the value with PHP's preg_place before rendering. E.g. massage an number input field
 *   # to a Tailwind CSS class. In the following example an input "1700" will be rendered as "max-w-[1700px]".
 *   value_preg_replace:
 *     pattern: '/^(\d+)$/'
 *     replacement: 'max-w-[${0}px]'
 *   # Sort elements by weight in the Style options admin UI. Defaults to 0.
 *   # Requires \Drupal\designsystem\Plugin\Layout\StyleOptionsLayout as layout plugin class.
 *   weight: 10
 * @endcode
 *
 * @StyleOption(
 *   id = "html_attribute",
 *   label = @Translation("CSS Class")
 * )
 */
class HtmlAttribute extends StyleOptionPluginBase {

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

    $form['html_attribute'] = [
      '#type' => $this->getConfiguration('input_type') ?? 'textfield',
      '#title' => $this->getLabel() ? $this->t($this->getLabel()) : NULL,
      '#default_value' => $this->getValue('html_attribute') ?? $this->getDefaultValue(),
      '#description' => $this->getConfiguration('description') ? $this->t((string)$this->getConfiguration('description')) : NULL,
      '#required' => $this->getConfiguration('required'),
    ];
    if ($this->hasConfiguration('form_api')) {
      $formApiParameters = $this->getConfiguration('form_api');
      if (is_array($formApiParameters)) {
        foreach ($formApiParameters as $hashParameter => $formApiParameter) {
          if ($hashParameter == '#empty_option') {
            $formApiParameter = $this->t($formApiParameter);
          }
          $form['html_attribute'][$hashParameter] = $formApiParameter;
        }
      }
    }

    if ($this->hasConfiguration('options')) {
      if (!in_array($form['html_attribute']['#type'], ['select', 'checkbox', 'checkboxes', 'radios'])) {
        $form['html_attribute']['#type'] = 'select';
      }
      $options = $this->getConfiguration('options');
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
      if ($this->getConfiguration('multiple')) {
        $form['html_attribute']['#multiple'] = TRUE;
      }

      $form['html_attribute']['#options'] = $options;
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build, string $region_id = NULL) {
    $value = $this->getValue('html_attribute') ?? NULL;
    if (empty($value)) {
      return $build;
    }

    $attributeName = $this->getConfiguration('attribute_name') ?? 'class';

    if ($preg_replace = $this->getConfiguration('value_preg_replace')) {
      $value = preg_replace($preg_replace['pattern'], $preg_replace['replacement'], $value, $preg_replace['limit'] ?? -1);
    }

    $twigAttributeVariableName = $this->getConfiguration('twig_attribute_object') ?? '#attributes';
    $build[$twigAttributeVariableName] = $this->designHelper->toAttributeObject($build[$twigAttributeVariableName] ?? []);

    if ($attributeName == 'class') {
      // Ensure $classes is an array so it can be easily manipulated later.
      $classes = is_array($value) ? $value : explode(' ', $value);
      foreach ($classes as $class) {
        /** @var $build[] Attribute */
        $build[$twigAttributeVariableName]->addClass($class);
      }
    }
    else {
      $attributeValue = is_array($value) ? join(' ', $value) : (string) $value;
      $build[$twigAttributeVariableName]->setAttribute($attributeName, $attributeValue);
    }

    if ($libraries = $this->getConfiguration('library')) {
      if (is_string($libraries)) {
        $libraries = [$libraries];
      }
      foreach ($libraries as $library) {
        $build['#attached']['library'][] = $library;
      }
    }


    return $build;
  }
}
