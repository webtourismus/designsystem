<?php

namespace Drupal\designsystem\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\TextBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'text' input with flatpickr library.
 *
 * @WebformElement(
 *   id = "flatpickr",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Textfield.php/class/Textfield",
 *   label = @Translation("Text field with flatpickr"),
 *   description = @Translation("Library must be manually initialized with Javascript."),
 *   category = @Translation("Basic elements"),
 * )
 */
class Flatpickr extends TextBase {

  public function prepare(array &$element, WebformSubmissionInterface $webform_submission = NULL) {
    parent::prepare($element, $webform_submission);
    $element['#attached']['library'][] = 'designsystem/flatpickr';
  }
}
