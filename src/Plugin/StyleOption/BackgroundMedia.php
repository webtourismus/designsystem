<?php

declare(strict_types=1);

namespace Drupal\designsystem\Plugin\StyleOption;

use Drupal\media\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Define the background image style option plugin.
 *
 * @StyleOption(
 *   id = "background_media",
 *   label = @Translation("Background Image")
 * )
 */
class BackgroundMedia extends HtmlAttribute {

  public const BACKGROUND_MEDIA_TWIG_NAME = 'background_media';

  /**
   * @var $entityTypeManager EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  public function build(array $build, string $region_id = NULL) {
    $mediaId = $this->getValue('html_attribute') ?? NULL;
    if (empty($mediaId)) {
      return $build;
    }

    $media = $this->entityTypeManager->getStorage('media')->load($mediaId);
    if ($media instanceof MediaInterface) {
      $viewBuilder = $this->entityTypeManager->getViewBuilder('media');
      $twigName = $this->getConfiguration('twig_variable') ?? self::BACKGROUND_MEDIA_TWIG_NAME;
      if (!str_starts_with($twigName, '#')) {
        $twigName = '#' . $twigName;
      }
      $viewMode = $this->getConfiguration('media_view_mode') ?? 'layout_bg';
      $build[$twigName] = $viewBuilder->view($media, $viewMode);
      $build['#attached']['library'][] = 'designsystem/layout_background_image';
      $build[$twigName]['#layout_background_image'] = TRUE;
    }

    return $build;
  }
}
