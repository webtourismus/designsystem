<?php

namespace Drupal\designsystem\Plugin\paragraphs\Behavior;

use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\style_options\Plugin\paragraphs\Behavior\StyleOptionBehavior;

/**
 * Provides a way to define grid based layouts.
 *
 * @ParagraphsBehavior(
 *   id = "inline_style_options",
 *   label = @Translation("Style Options (inline)"),
 *   description = @Translation("Integrates paragraphs with Style Options. Renders them inline below content fields. See issue 2928759"),
 *   weight = 0
 * )
 */
class InlineStyleOptionBehavior extends StyleOptionBehavior {
  /**
   * {@inheritDoc}
   */
  public function buildBehaviorForm(
    ParagraphInterface $paragraph,
    array &$form,
    FormStateInterface $form_state
  ) {
    parent::buildBehaviorForm($paragraph, $form, $form_state);
    return [];
  }
}
