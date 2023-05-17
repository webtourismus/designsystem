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
}
