<?php

declare(strict_types=1);

namespace Drupal\designsystem;

use \Drupal\Core\Template\Attribute;

class DesignHelper {
  /**
   * Returns a normalized Attribute object from an unknown set of HTML attributes.
   *
   * HTML attributes can be of various data types in Drupal. Using this function
   * allows succeding processors save usage of helper functions like getClass()
   * on attributes of unknown origin.
   *
   * @param null|array|Attribute $attribute
   * @return Attribute
   */
  public static function toAttributeObject(null|array|Attribute $attribute): Attribute
  {
    if ($attribute instanceof Attribute) {
      return clone $attribute;
    }
    return new Attribute($attribute ?? []);
  }

  /**
   * Returns the real name of the used view mode of a field render array.
   *
   * Drupal core provides $fieldRenderArray['element']['#view_mode'].
   * But this variable might not contain the real name of the view mode used for rendering in some scenarios:
   * - The field was rendered programmatically / in isolation (e.g. "ebr_teaser" module).
   * - The "view_mode_switch" module might change the view mode.
   * - The entity was rendered by core "views" module (entire views row rendered as entity with entity view mode).
   * In those cases, the view mode might be e.g. "_custom" or "default" (by intention due caching or simply by mistake).
   * This function tries to find the real view name using known workarounds.
   *
   * @return string
   */
  public static function getRealViewmode(array $renderArray): string {
    $viewMode = $renderArray['element']['#view_mode'];
    $entity = $renderArray['element']['#object'];
    if ($viewMode == 'default' && $entity?->hasField('field_viewmode') && !$entity->get('field_viewmode')->isEmpty()) {
      $viewMode = $entity->get('field_viewmode')->value;
    }
    return $viewMode;
  }
}
