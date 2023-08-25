<?php

namespace Drupal\designsystem\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Component\Utility\Html;

/**
 * @Filter(
 *   id = "responsive_table",
 *   title = @Translation("Responsive Table"),
 *   description = @Translation("Wraps all &lt;table class='responsive-table'&gt;s inside a &lt;div&gt; with a horizontal scrollbar."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class ResponsiveTable extends FilterBase {

  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    $wrapper = $dom->createElement('div');
    $wrapper->setAttribute('class', 'responsive-table-wrapper');

    /** @var  $element \DOMNode */
    foreach ($xpath->query("//table[contains(@class, 'responsive-table')]") as $element) {
      $clonedWrapper = $wrapper->cloneNode();
      $element->parentNode->replaceChild($clonedWrapper, $element);
      $clonedWrapper->appendChild($element);
    }
    $result->setProcessedText(Html::serialize($dom));
    $result->addAttachments(['library' => ['designsystem/responsive_table']]);
    return $result;
  }
}
