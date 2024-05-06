<?php

declare(strict_types=1);

namespace Drupal\designsystem\Plugin\StyleOption;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\style_options\Plugin\StyleOptionPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the responsive class style option plugin.
 *
 * Allows an editor to apply Tailwind style responsive CSS class overwrites at a certain breakpoint.
 * The selected input values are rendered prefixed with a selectable responsive prefix.
 * E.g. if a user select "md" as prefix and "flex basis-1/2" as classes, the rendered output is "md:flex md:basis-1/2".
 *
 * @code
 * my_example_responsive_class_style_option:
 *   plugin: responsive_class
 *   label: 'My responsive CSS style'
 *   description: 'A description for the editor, visible in the admin UI'
 *   # Form API '<select>' element for the responsive prefix, similar to the "html_attribute" plugin.
 *   prefix:
 *     options:
 *       sm: mobile phones
 *       md: tablets
 *       lg: laptops
 *   # Form API render element for the classes, similar to the "html_attribute" plugin.
 *   # Automatically prefixed with the opposing responsive prefix.
 *   # (Classes already containing a responsive prefix will be rendered unmodified.)
 *   # E.g. if the editor selected "md" as prefix, the following three example options would be rendered as
 *   # 'md:block' | 'md:grid md:grid-cols-2' | 'md:grid md:grid-cols-2 lg:grid-cols-4'
 *   classes:
 *     type: select
 *     options:
 *       'block': 'one fullwidth block'
 *       'grid grid-cols-2': 'two columns'
 *       'grid grid-cols-2 lg:grid-cols-4': '2 columns normally, on large screens and above 4 columns'
 *   # The prefix for the user selected classes. 'min' (recommended, default) or 'max'.
 *   # If the prefix_mode is 'min', the resulting prefix is the prefix value with a colon, e.g. "sm:".
 *   # If the prefix_mode is 'max', the resulting prefix is "max-"+ prefix value + colon, e.g. "max-sm:".
 *   prefix_mode: 'min'
 *   # The classes for the standard design when not using the overwrite breakpoint.
 *   # Automatically prefixed with the opposing responsive prefix.
 *   # E.g. if the user selected "md" as overwrite prefix, and "flex basis-1/2" as overwrite class,
 *   # then the following example would also render this classes: "max-sm:block max-sm:w-full".
 *   # (Classes already containing a responsive prefix will be rendered unmodified.)
 *   otherwise_classes: 'display-block w-full'
 * @endcode
 *
 * @StyleOption(
 *   id = "responsive_class",
 *   label = @Translation("Responsive Class")
 * )
 */
class ResponsiveClass extends StyleOptionPluginBase {

  /**
   * @var $designHelper DesignHelper
   */
  protected $designHelper;

  /**
   * Default responsive options used if "prefix" options are missing
   */
  const DEFAULT_RESPONSIVE_OPTIONS = [
    'sm' => 'tablet portrait',
    'md' => 'tablet landscape',
    'lg' => 'laptop',
  ];

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
    $form['responsive_class'] = [
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
    $containerFormApiParameters = array_merge($defaultContainerFormApiParameters, $this->getConfiguration['form_api'] ?? []);
    if ($containerFormApiParameters) {
      foreach ($containerFormApiParameters as $hashParameter => $formApiParameter) {
        $form['responsive_class'][$hashParameter] = $formApiParameter;
      }
    }


    $form['responsive_class']['prefix'] = [
      '#type' => 'select',
      '#title' => $this->getConfiguration('prefix')['label'] ? $this->t($this->getConfiguration('prefix')['label']) : NULL,
      // @TODO: find out why responsive_class uses as paragraph behavior plugin does not do submitConfigurationForm()
      '#default_value' => $this->getValue('responsive_class_prefix') ?? $this->getValue('responsive_class')['prefix'] ?? $this->getConfiguration('prefix')['default'] ?? NULL,
      '#description' => $this->getConfiguration('prefix')['description'] ?? NULL,
    ];
    $prefixOptions = $this->getConfiguration('prefix')['options'] ?? $this::DEFAULT_RESPONSIVE_OPTIONS;
    if (!is_array($prefixOptions)) {
      $prefixOptions =  $this::DEFAULT_RESPONSIVE_OPTIONS;
    }
    foreach ($prefixOptions as $key => $option) {
      if (is_array($option)) {
        $prefixOptions[$key] = $this->t(...$option);
      }
      elseif (is_string($option)) {
        $prefixOptions[$key] = $this->t($option);
      }
    }
    $form['responsive_class']['prefix']['#options'] = $prefixOptions;
    $defaultPrefixFormApiParameters = [
      '#wrapper_attributes' => [
        'class' => [
          'basis-[10rem]',
          'grow-0',
          'shrink-0',
          'my-0',
        ]
      ]
    ];
    $prefixFormApiParameters = array_merge($defaultPrefixFormApiParameters, $this->getConfiguration('responsive_class')['prefix']['form_api'] ?? []);
    if ($prefixFormApiParameters) {
      foreach ($prefixFormApiParameters as $hashParameter => $formApiParameter) {
        $form['responsive_class']['prefix'][$hashParameter] = $formApiParameter;
      }
    }


    $form['responsive_class']['classes'] = [
      '#type' => $this->getConfiguration('classes')['input_type'] ?? 'textfield',
      '#title' => $this->getConfiguration('classes')['label'] ?? NULL,
      '#default_value' => $this->getValue('responsive_class_classes') ?? $this->getValue('responsive_class')['classes'] ?? $this->getConfiguration('classes')['default'] ?? NULL,
      '#description' => $this->getConfiguration('classes')['description'] ?? NULL,
      '#required' => $this->getConfiguration('classes')['required'] ?? NULL,
    ];
    $defaultClassesFormApiParameters = [
      '#wrapper_attributes' => [
        'class' => [
          'basis-full',
          'grow',
          'shrink',
          'my-0',
        ]
      ]
    ];
    $classesFormApiParameters = array_merge($defaultClassesFormApiParameters, $this->getConfiguration('classes')['form_api'] ?? []);
    if ($classesFormApiParameters) {
      foreach ($classesFormApiParameters as $hashParameter => $formApiParameter) {
        $form['responsive_class']['classes'][$hashParameter] = $formApiParameter;
      }
    }

    $classOptions = $this->getConfiguration('classes')['options'] ?? NULL;
    if ($classOptions) {
      if (!in_array($form['responsive_class']['classes']['#type'], ['select', 'checkbox', 'checkboxes', 'radios'])) {
        $form['responsive_class']['classes']['#type'] = 'select';
      }
      if (is_array($classOptions)) {
        foreach ($classOptions as $key => $option) {
          if (is_array($option)) {
            $classOptions[$key] = $this->t(...$option);
          }
          elseif (is_string($option)) {
            $classOptions[$key] = $this->t($option);
          }
        }
      }
      if ($this->getConfiguration('classes')['multiple'] ?? NULL) {
        $form['responsive_class']['classes']['#multiple'] = TRUE;
      }

      $form['responsive_class']['classes']['#options'] = $classOptions;
    }


    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitConfigurationForm(
    array &$form,
    FormStateInterface $form_state
  ): void {
    $values = [];
    if ($form_state->hasValue(['responsive_class', 'prefix'])) {
      $values['responsive_class_prefix'] = $form_state->getValue(['responsive_class', 'prefix']);
    }
    if ($form_state->hasValue(['responsive_class', 'classes'])) {
      $values['responsive_class_classes'] = $form_state->getValue(['responsive_class', 'classes']);
    }
    $this->setValues($values);
  }

  /**
   * {@inheritDoc}
   */
  public function build(array $build) {
    $prefix = $this->getValue('responsive_class_prefix') ?? NULL;
    $value = $this->getValue('responsive_class_classes') ?? '';
    // if rendered as paragraph behavior plugin, then the values are not post-processed by submitConfigurationForm()
    // and are still in array form
    // @TODO: find out why responsive_class uses as paragraph behavior plugin does not do submitConfigurationForm()
    if (empty($value) && is_array($this->getValue('responsive_class')) && !empty($this->getValue('responsive_class')['classes'])) {
      $prefix = $this->getValue('responsive_class')['prefix'] ?? NULL;
      $value = $this->getValue('responsive_class')['classes'] ?? '';
    }
    if (empty($value)) {
      return $build;
    }

    $twigAttributeVariableName = $this->getConfiguration('twig_attribute_object') ?? '#attributes';
    $build[$twigAttributeVariableName] = $this->designHelper->toAttributeObject($build[$twigAttributeVariableName] ?? []);

    $id = str_replace('_', '-', $this->getOptionId());
    $build[$twigAttributeVariableName]->setAttribute("data-responsive-{$id}", $prefix);

    $prefixOptions = $this->getConfiguration('prefix')['options'] ?? $this::DEFAULT_RESPONSIVE_OPTIONS;
    if (is_array($prefixOptions)) {
      $prefixRegex = join('|', array_keys($prefixOptions));
    }
    else {
      $prefixRegex = join('|', $prefixOptions);
    }
    $prefixMode = $this->getConfiguration('prefix_mode') ?? 'min';

    /**
     * add the default "below classes" with the prefix as upper boundary
     */
    $otherwiseClasses = $this->getConfiguration('otherwise_classes');
    if ($otherwiseClasses) {
      $otherwiseClasses = is_array($otherwiseClasses) ? $otherwiseClasses : explode(' ', $otherwiseClasses);
      if ($prefixMode == 'max') {
        $otherwiseMode = '';
      }
      else {
        $otherwiseMode = 'max-';
      }
      foreach ($otherwiseClasses as $class) {
        preg_match('/(?<responsive_match>(min-|max-)?(' . $prefixRegex . ')):.+/', $class, $matches);
        if (!isset($matches['responsive_match'])) {
          $class = $otherwiseMode . $prefix . ':' . $class;
        }
        $build[$twigAttributeVariableName]->addClass($class);
      }
    }

    if ($preg_replace = $this->getConfiguration('value_preg_replace')) {
      $value = preg_replace($preg_replace['pattern'], $preg_replace['replacement'], $value, $preg_replace['limit'] ?? -1);
    }
    if (empty($value)) {
      $value = 'INVALID_PREG_REPLACE_SUBJECT';
    }

    /**
     * All editor selected classes
     */
    $value = is_array($value) ? $value : explode(' ', $value);
    if ($prefixMode == 'max') {
      $responsiveMode = 'max-';
    }
    else {
      $responsiveMode = '';
    }
    foreach ($value as $class) {
      preg_match('/(?<responsive_match>(min-|max-)?(' . $prefixRegex . ')):.+/', $class, $matches);
      if (!isset($matches['responsive_match'])) {
        $class = $responsiveMode . $prefix . ':' . $class;
      }
      $build[$twigAttributeVariableName]->addClass($class);
    }
    return $build;
  }

}
