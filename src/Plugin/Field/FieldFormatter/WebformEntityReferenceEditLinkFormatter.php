<?php

namespace Drupal\designsystem\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\Field\FieldFormatter\WebformEntityReferenceLinkFormatter;

/**
 * Plugin implementation of the 'Link to webform' formatter.
 *
 * @FieldFormatter(
 *   id = "webform_entity_reference_description",
 *   label = @Translation("Webform description"),
 *   description = @Translation("Display a textual description of the webform for admin preview."),
 *   field_types = {
 *     "webform"
 *   }
 * )
 */
class WebformEntityReferenceEditLinkFormatter extends WebformEntityReferenceLinkFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    unset($settings['dialog']);
    unset($settings['attributes']);
    unset($settings['dialog']);
    $settings['label'] = 'Webform [webform:title]';
    return $settings;
  }


  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['label']['#type'] = 'textarea';
    unset($form['attributes']);
    unset($form['dialog']);
    return $form;
  }

    /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $source_entity = $items->getEntity();
    $this->messageManager->setSourceEntity($source_entity);

    $elements = parent::viewElements($items, $langcode);

    /** @var \Drupal\webform\WebformInterface[] $entities */
    $entities = $this->getEntitiesToView($items, $langcode);
    foreach ($entities as $delta => $entity) {
      // Do not display the webform if the current user can't create submissions.
      if ($entity->id() && !$entity->access('update')) {
        continue;
      }
      $label = $this->getSetting('label');
      $elements[$delta] = [
        '#type' => '#markup',
        '#markup' => '<div>' . $this->tokenManager->replace($label, $entity) . '</div>'
      ];
    }
    return $elements;
  }
}
