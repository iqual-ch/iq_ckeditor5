<?php

/**
 * @file
 * Configurator service for CKEditor5.
 */

namespace Drupal\iq_ckeditor5;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\filter\Plugin\FilterInterface;

/**
 * Class Configurator.
 *
 * Configurator service for CKEditor5.
 */
class Configurator {

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Required modules for advanced CKEditor5 features.
   *
   * @var array
   */
  protected $requiredModules = [
    'ckeditor5_plugin_pack',
    'ckeditor5_plugin_pack_emoji',
    'ckeditor5_plugin_pack_fullscreen',
    'ckeditor5_plugin_pack_text_transformation',
    'ckeditor5_plugin_pack_word_count',
    'editor_advanced_link',
    'linkit',
  ];

  /**
   * Advanced toolbar configuration.
   *
   * @var array
   */
  protected $advancedToolbar = [
    'bold',
    'italic',
    'strikethrough',
    'underline',
    'subscript',
    'superscript',
    '|',
    'heading',
    'style',
    'removeFormat',
    'alignment',
    'horizontalLine',
    '|',
    'bulletedList',
    'numberedList',
    'indent',
    'outdent',
    '|',
    'link',
    'emoji',
    'insertTable',
    'sourceEditing',
    'fullscreen',
    '|',
    'undo',
    'redo',
  ];

  /**
   * Predefined styles for CKEditor5 style plugin.
   *
   * @var array
   */
  protected $predefinedStyles = [
    [
      'label' => 'Heading 1',
      'element' => '<span class="h1">',
    ],
    [
      'label' => 'Heading 2',
      'element' => '<span class="h2">',
    ],
    [
      'label' => 'Heading 3',
      'element' => '<span class="h3">',
    ],
    [
      'label' => 'Heading 4',
      'element' => '<span class="h4">',
    ],
    [
      'label' => 'Heading 5',
      'element' => '<span class="h5">',
    ],
    [
      'label' => 'Heading 6',
      'element' => '<span class="h6">',
    ],
    [
      'label' => 'Standard text',
      'element' => '<span class="standard">',
    ],
    [
      'label' => 'Small text',
      'element' => '<span class="small">',
    ],
    [
      'label' => 'Lead text',
      'element' => '<span class="lead">',
    ],
    [
      'label' => 'Deco font 1',
      'element' => '<span class="deco-font-1">',
    ],
    [
      'label' => 'Deco font 2',
      'element' => '<span class="deco-font-2">',
    ],
    [
      'label' => 'Deco font 3',
      'element' => '<span class="deco-font-3">',
    ],
  ];

  /**
   * Plugin settings for CKEditor5 features.
   *
   * @var array
   */
  protected $pluginSettings = [
    'ckeditor5_alignment' => [
      'enabled_alignments' => [
        'center',
        'justify',
        'left',
        'right',
      ],
    ],
    'ckeditor5_heading' => [
      'enabled_headings' => [
        'heading1',
        'heading2',
        'heading3',
        'heading4',
        'heading5',
        'heading6',
      ],
    ],
    'ckeditor5_list' => [
      'properties' => [
        'reversed' => TRUE,
        'startIndex' => TRUE,
      ],
      'multiBlock' => TRUE,
    ],
    'ckeditor5_paste_filter_pasteFilter' => [
      'enabled' => TRUE,
      'filters' => [
        [
          'enabled' => TRUE,
          'weight' => -10,
          'search' => '<o:p><\/o:p>',
          'replace' => '',
        ],
        [
          'enabled' => TRUE,
          'weight' => -9,
          'search' => '(<[^>]*) (style="[^"]*")',
          'replace' => '$1',
        ],
        [
          'enabled' => TRUE,
          'weight' => -8,
          'search' => '(<[^>]*) (face="[^"]*")',
          'replace' => '$1',
        ],
        [
          'enabled' => TRUE,
          'weight' => -7,
          'search' => '(<[^>]*) (class="[^"]*")',
          'replace' => '$1',
        ],
        [
          'enabled' => TRUE,
          'weight' => -6,
          'search' => '(<[^>]*) (valign="[^"]*")',
          'replace' => '$1',
        ],
        [
          'enabled' => TRUE,
          'weight' => -5,
          'search' => '<font[^>]*>',
          'replace' => '',
        ],
        [
          'enabled' => TRUE,
          'weight' => -4,
          'search' => '<\/font>',
          'replace' => '',
        ],
        [
          'enabled' => TRUE,
          'weight' => -3,
          'search' => '<span[^>]*>',
          'replace' => '',
        ],
        [
          'enabled' => TRUE,
          'weight' => -2,
          'search' => '<\/span>',
          'replace' => '',
        ],
        [
          'enabled' => TRUE,
          'weight' => -1,
          'search' => '<p>&nbsp;<\/p>',
          'replace' => '',
        ],
        [
          'enabled' => TRUE,
          'weight' => 0,
          'search' => '<p><\/p>',
          'replace' => '',
        ],
        [
          'enabled' => TRUE,
          'weight' => 1,
          'search' => '<b><\/b>',
          'replace' => '',
        ],
        [
          'enabled' => TRUE,
          'weight' => 2,
          'search' => '<i><\/i>',
          'replace' => '',
        ],
        [
          'enabled' => TRUE,
          'weight' => 3,
          'search' => '<a name="OLE_LINK[^"]*">(.*?)<\/a>',
          'replace' => '$1',
        ],
      ],
    ],
    'ckeditor5_plugin_pack_emoji_emoji' => [
      'definitionsUrl' => '',
      'dropdownLimit' => 6,
      'skinTone' => 'default',
      'useCustomFont' => FALSE,
      'version' => 16,
    ],
    'ckeditor5_plugin_pack_text_transformation__text_transformation' => [
      'enabled' => TRUE,
      'extra_transformations' => '',
      'extra_regex_transformations' => [],
      'groups' => [
        'typography' => [
          'transformations' => [
            'ellipsis' => ['enabled' => 1],
            'enDash' => ['enabled' => 1],
            'emDash' => ['enabled' => 1],
          ],
          'enabled' => 1,
        ],
        'quotes' => [
          'transformations' => [
            'quotesPrimary' => ['enabled' => 1],
            'quotesSecondary' => ['enabled' => 1],
          ],
          'enabled' => 1,
        ],
        'symbols' => [
          'transformations' => [
            'trademark' => ['enabled' => 1],
            'registeredTrademark' => ['enabled' => 1],
            'copyright' => ['enabled' => 1],
          ],
          'enabled' => 1,
        ],
        'mathematical' => [
          'transformations' => [
            'oneHalf' => ['enabled' => 1],
            'oneThird' => ['enabled' => 1],
            'twoThirds' => ['enabled' => 1],
            'oneFourth' => ['enabled' => 1],
            'threeQuarters' => ['enabled' => 1],
            'lessThanOrEqual' => ['enabled' => 1],
            'greaterThanOrEqual' => ['enabled' => 1],
            'notEqual' => ['enabled' => 1],
            'arrowLeft' => ['enabled' => 1],
            'arrowRight' => ['enabled' => 1],
          ],
          'enabled' => 1,
        ],
        'misc' => [
          'transformations' => [
            'quotesPrimaryEnGb' => ['enabled' => 0],
            'quotesSecondaryEnGb' => ['enabled' => 0],
            'quotesPrimaryPl' => ['enabled' => 0],
            'quotesSecondaryPl' => ['enabled' => 0],
          ],
          'enabled' => 0,
        ],
      ],
    ],
    'ckeditor5_plugin_pack_word_count__word_count' => [
      'word_count_enabled' => TRUE,
      'word_count_mode' => 'words_chars',
    ],
    'ckeditor5_sourceEditing' => [
      'allowed_tags' => [],
    ],
    'editor_advanced_link_link' => [
      'enabled_attributes' => [
        'rel',
        'target',
        'title',
      ],
    ],
    'linkit_extension' => [
      'linkit_enabled' => TRUE,
      'linkit_profile' => 'default_linkit',
    ],
  ];

  /**
   * Filter configuration for text formats.
   *
   * @var array
   */
  protected $filterConfig = [
    'filter_url' => [
      'id' => 'filter_url',
      'provider' => 'filter',
      'status' => TRUE,
      'weight' => 0,
      'settings' => [
        'filter_url_length' => 72,
      ],
    ],
    'linkit' => [
      'id' => 'linkit',
      'provider' => 'linkit',
      'status' => TRUE,
      'weight' => 0,
      'settings' => [
        'title' => TRUE,
        'media_substitution' => 'metadata',
      ],
    ],
  ];

  /**
   * Constructs a Migrator service.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller
   *   The module installer service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger channel factory service.
   */
  public function __construct(
    protected ModuleInstallerInterface $moduleInstaller,
    protected ModuleHandlerInterface $moduleHandler,
    protected EntityTypeManagerInterface $entityTypeManager,
    LoggerChannelFactoryInterface $loggerFactory,
  ) {
    $this->logger = $loggerFactory->get('iq_ckeditor5_configurator');
  }

  /**
   * Configure extensions for CKEditor 5.
   */
  public function configureExtensions() {
    if (!$this->moduleHandler->moduleExists('ckeditor5')) {
      return;
    }

    /** @var \Drupal\filter\FilterFormatInterface[] $formats */
    $formats = $this->entityTypeManager->getStorage('filter_format')->loadMultiple();
    $editorStorage = $this->entityTypeManager->getStorage('editor');

    foreach ($formats as $format) {
      /** @var \Drupal\editor\EditorInterface|null $editor */
      $editor = $editorStorage->load($format->id());
      if (!$editor || $editor->getEditor() !== 'ckeditor5') {
        continue;
      }

      $editor_settings = $editor->getSettings();
      $toolbar = $editor_settings['toolbar']['items'] ?? [];
      $plugin_settings = $editor_settings['plugins'] ?? [];

      // Configure advanced features with Plugin Pack
      $advanced_config = $this->configureAdvancedFeatures($toolbar, $plugin_settings);
 
      // Update editor settings
      $editor->setEditor('ckeditor5');
      $editor->setSettings([
        'toolbar' => [
          'items' => $advanced_config['toolbar'],
        ],
        'plugins' => $advanced_config['plugins'],
      ]);
      $editor->save();

      // Configure filters for the text format
      $this->configureFilters($format);
    }
  }

  /**
   * Configures filters for a text format.
   *
   * @param \Drupal\filter\FilterFormatInterface $format
   *   The text format to configure filters for.
   */
  protected function configureFilters($format) {
    // Get existing filters
    $existing_filters = $format->get('filters');

    // Merge with existing filters, preserving filters not in our configuration
    foreach ($this->filterConfig as $filter_id => $config) {
      $existing_filters[$filter_id] = $config;
    }

    // Set the filters
    $format->set('filters', $existing_filters);
    $format->save();

    $this->logger->info('Configured filters for text format @format', [
      '@format' => $format->id(),
    ]);
  }

  /**
   * Configures advanced CKEditor 5 features with Plugin Pack.
   *
   * @param array $cke5_toolbar
   *   The current CKEditor 5 toolbar items.
   * @param array $cke5_plugin_settings
   *   The current CKEditor 5 plugin settings.
   *
   * @return array
   *   Array containing updated toolbar and plugin settings.
   */
  protected function configureAdvancedFeatures(array $cke5_toolbar, array $cke5_plugin_settings): array {
    // Install required modules if not already installed
    foreach ($this->requiredModules as $module) {
      if (!$this->moduleHandler->moduleExists($module)) {
        try {
          $this->moduleInstaller->install([$module]);
          $this->logger->info('Installed module @module', ['@module' => $module]);
        } catch (\Exception $e) {
          $this->logger->warning('Could not install module @module: @message', [
            '@module' => $module,
            '@message' => $e->getMessage(),
          ]);
        }
      }
    }
    
    // Merge styles: predefined first, then existing ones that are different
    $merged_styles = $this->predefinedStyles;
    $existing_styles = $cke5_plugin_settings['ckeditor5_style']['styles'] ?? [];
    
    foreach ($existing_styles as $existing_style) {
      $is_duplicate = FALSE;
      foreach ($this->predefinedStyles as $predefined_style) {
        // Check if the existing style matches a predefined one (by element or label)
        if ($existing_style['element'] === $predefined_style['element'] ||
            $existing_style['label'] === $predefined_style['label']) {
          $is_duplicate = TRUE;
          break;
        }
      }
      // Add existing style only if it's not a duplicate
      if (!$is_duplicate) {
        $merged_styles[] = $existing_style;
      }
    }

    // Add the ckeditor5_style configuration with merged styles
    $plugin_settings_with_styles = $this->pluginSettings;
    $plugin_settings_with_styles['ckeditor5_style'] = [
      'styles' => $merged_styles,
    ];

    // Merge with existing plugin settings and add advanced configurations.
    $advanced_plugin_settings = array_merge($cke5_plugin_settings, $plugin_settings_with_styles);

    // Also preserve existing toolbar items that are not already included.
    $final_toolbar = $this->advancedToolbar;
    foreach ($cke5_toolbar as $item) {
      if (!in_array($item, $final_toolbar, TRUE)) {
        $final_toolbar[] = $item;
      }
    }

    // Add the wrap item at the end to make sure the toolbar is wrapped.
    $final_toolbar[] = '-';

    // Remove Fullscreen if it was added before.
    unset($final_toolbar['Fullscreen']);

    return [
      'toolbar' => $final_toolbar,
      'plugins' => $advanced_plugin_settings,
    ];
  }

}
