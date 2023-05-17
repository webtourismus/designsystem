<?php

declare(strict_types=1);

namespace Drupal\designsystem\Plugin\StyleOption;

/**
 * Defines the JSON string option plugin.
 *
 * Selected options will be encoded as JSON string and added as attribute.
 * For generic label / form api options @see \Drupal\designsystem\Plugin\StyleOption\HtmlAttribute
 *
 * @code
 * my_example_json_string_style_option:
 *   plugin: json_string
 *   label: 'Config encoded as JSON strin'
 *   # (string) attribute_name: mandatory name of the HTML attribute holding the JSON string
 *   attribute_name: 'data-foo'
 *   # (string|string[]) key: mandatory, name of the JSON key.
 *   #   The value must be a either be a string (meaning a root level key in JSON)
 *   #   or an array of strings (meaning a nested key in JSON)
 *   #   (the example below would result in a key "breakpoint.1024.perPage")
 *   key:
 *     - 'breakpoint'
 *     - '1024'
 *     - 'perPage'
 *   options:
 *     - 3
 *     - 4
 *   # (string merge|replace) mode: defaults to replace (overwrites existing values with same key_name)
 *   #   use merge when values with the same key_name should be added to existing values with the same key_name
 *   mode: replace
 * @endcode
 *
 * @StyleOption(
 *   id = "json_string",
 *   label = @Translation("JSON string")
 * )
 */
class JsonString extends HtmlAttribute {

  /**
   * {@inheritDoc}
   */
  public function build(array $build, string $region_id = NULL) {
    $attributeName = $this->getConfiguration('attribute_name') ?? NULL;
    $key = $this->getConfiguration('key') ?? NULL;
    if (empty($attributeName) || empty($key)) {
      return [];
    }
    $value = $this->getValue('html_attribute') ?? NULL;
    if (is_null($value)) {
      return $build;
    }

    if ($preg_replace = $this->getConfiguration('value_preg_replace')) {
      $value = preg_replace($preg_replace['pattern'], $preg_replace['replacement'], $value, $preg_replace['limit'] ?? -1);
    }

    $twigAttributeVariableName = $this->getConfiguration('twig_attribute_object') ?? '#attributes';
    $build[$twigAttributeVariableName] = $this->designHelper->toAttributeObject($build[$twigAttributeVariableName] ?? []);
    $existingValues = [];
    if ($build[$twigAttributeVariableName]->hasAttribute($attributeName)) {
      $existingValues = json_decode($build[$twigAttributeVariableName]->toArray()[$attributeName], TRUE);
    }

    $jsonValue = $value;
    if (is_string($key)) {
      $key = [$key];
    }
    foreach (array_reverse($key) as $possiblyNestedKey) {
      $jsonValue = [$possiblyNestedKey => $jsonValue];
    }
    if ($this->getValue('mode') === 'merge') {
      $combinedValues = array_merge_recursive($existingValues, $jsonValue);
    }
    else {
      $combinedValues = array_replace_recursive($existingValues, $jsonValue);
    }
    $build[$twigAttributeVariableName]->setAttribute($attributeName, json_encode($combinedValues));

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
