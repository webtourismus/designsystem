<?php

namespace Drupal\designsystem\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\linkit_attributes\Plugin\Field\FieldWidget\LinkitWithAttributesWidget;

/**
 * Plugin implementation of the 'link' widget.
 *
 * @FieldWidget(
 *   id = "linkit_attributes_2columns",
 *   label = @Translation("LinkIt (with Attributes, 2 columns layout)"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class LinkitAttributes2Columns extends LinkitWithAttributesWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // remove the excessive, noisy help text
    unset($element['uri']['#description']);
    unset($element['options']['attributes']['#open']);

    $element['options']['attributes']['#type'] = 'container';
    $element['options']['attributes']['#attributes']['class'][] = 'grid grid-cols-1 sm:grid-cols-2 gap-4 mt-[-1.5rem] sm:col-span-2';

    uksort($element['options']['attributes'], function($a, $b) {
      if ($a == 'class') {
        return -1;
      }
      if ($b == 'class') {
        return 1;
      }
      return 0;
    });
    $element['#type'] = 'container';
    $element['#attributes']['class'][] = 'grid grid-cols-1 sm:grid-cols-2 gap-4 mt-[-1.5rem]';
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    unset($settings['open_tab_onfill']);
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    unset($element['open_tab_onfill']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    /* "open tab" is the last summary line */
    array_pop($summary);
    return $summary;
  }

}
