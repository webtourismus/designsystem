<?php

namespace Drupal\designsystem\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Component\Utility\Html;

/**
 * @Filter(
 *   id = "header1",
 *   title = @Translation("Transform header 1 to header 2"),
 *   description = @Translation("Converts all &lt;h1&gt; into &lt;h2 class='h1'&gt; to ensure one single header 1 on every page (rendered by Drupal title block)."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class Header1 extends FilterBase {

  public const CSS_CLASSNAME_HEADER1 = 'h1';

  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    $dom = Html::load($text);
    $elementsToRemove = [];

    /** @var $element \DOMElement */
    foreach ($dom->getElementsByTagName('h1') as $h1) {
      $h2 = $dom->createElement('h2');
      /** @var $attribute \DOMNode */
      foreach ($h1->attributes as $attribute) {
        $h2->setAttribute($attribute->nodeName, $attribute->nodeValue);
      }
      if (empty($h2->getAttribute('class'))) {
        $h2->setAttribute('class', static::CSS_CLASSNAME_HEADER1);
      }
      else {
        $h2->setAttribute('class', static::CSS_CLASSNAME_HEADER1 . ' ' . $h2->getAttribute('class'));
      }
      foreach($h1->childNodes as $child) {
        $h2->appendChild($child->cloneNode(TRUE));
      }
      $h1->parentNode->appendChild($h2);
      $h1->parentNode->removeChild($h1);
    }
    $result->setProcessedText(Html::serialize($dom));
    return $result;
  }
}
