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

      $toolbar = $editor_settings['toolbar'] ?? [];

      // Reorder toolbar items according to specific order
      $cke5_toolbar = $this->reorderToolbarItems($toolbar);
            
      // Activate and configure additional plugins
      $cke5_plugin_settings = $this->activateAdditionalPlugins($cke5_plugin_settings);

      // Configure advanced features with Plugin Pack
      $advanced_config = $this->configureAdvancedFeatures($cke5_toolbar, $cke5_plugin_settings);
 
      // Update editor settings
      $editor->setEditor('ckeditor5');
      $editor->setSettings([
        'toolbar' => [
          'items' => $advanced_config['toolbar'],
        ],
        'plugins' => $advanced_config['plugins'],
        'image_upload' => $advanced_config['image_upload'],
      ]);
      $editor->save();
    }
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
    $required_modules = [
      'ckeditor5_plugin_pack',
      'ckeditor5_plugin_pack_emoji',
      'ckeditor5_plugin_pack_fullscreen',
      'editor_advanced_link',
      'linkit',
    ];
    
    foreach ($required_modules as $module) {
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

    // Define the complete toolbar configuration with groups and line breaks
    $advanced_toolbar = [
      // Text formatting group
      'bold',
      'italic',
      'strikethrough',
      'underline',
      '|', // Divider
      
      // Structure and alignment group
      'heading',
      'style',
      'alignment',
      'horizontalLine',
      '|', // Divider
      
      // Lists and indentation group
      'bulletedList',
      'numberedList',
      'indent',
      'outdent',
      '|', // Divider
      
      // Insert tools group
      'link',
      'emoji',
      'insertTable',
      'sourceEditing',
      'fullscreen',
      '|', // Divider
      
      // History group
      'undo',
      'redo',
      '-', // Line break (wrapping)
    ];

    // Filter toolbar to only include items that exist in the original toolbar or are new features
    $filtered_toolbar = [];
    $always_include = ['|', '-', 'emoji', 'fullscreen', 'horizontalLine'];
    
    foreach ($advanced_toolbar as $item) {
      if (in_array($item, $always_include, TRUE) || in_array($item, $cke5_toolbar, TRUE)) {
        $filtered_toolbar[] = $item;
      }
    }

    // Merge with existing plugin settings and add advanced configurations
    $advanced_plugin_settings = array_merge($cke5_plugin_settings, [
      // Emoji plugin configuration
      'ckeditor5_plugin_pack_emoji_emoji' => [
        'definitionsUrl' => '',
        'dropdownLimit' => 6,
        'skinTone' => 'default',
        'useCustomFont' => FALSE,
        'version' => 16,
      ],
      
      // Style plugin with custom styles
      'ckeditor5_style' => [
        'styles' => [
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
        ],
      ],
      
      // Linkit integration
      'linkit_extension' => [
        'linkit_enabled' => TRUE,
        'linkit_profile' => 'default_linkit',
      ],
    ]);

    return [
      'toolbar' => $filtered_toolbar,
      'plugins' => $advanced_plugin_settings,
      'image_upload' => [
        'status' => FALSE,
      ],
    ];
  }

  /**
   * Reorders toolbar items according to a specific order.
   *
   * @param array $toolbar_items
   *   The current toolbar items.
   *
   * @return array
   *   The reordered toolbar items.
   */
  protected function reorderToolbarItems(array $toolbar_items): array {
    // Define your preferred toolbar order here
    $preferred_order = [
      'heading',
      'style',
      'link',
      'bold',
      'italic',
      'bulletedList',
      'numberedList',
      'alignment',
      'insertTable',
      'sourceEditing',
      'drupalInsertImage',
      'blockQuote',
    ];

    $ordered_items = [];
    
    // Add items in preferred order if they exist
    foreach ($preferred_order as $item) {
      if (in_array($item, $toolbar_items, TRUE)) {
        $ordered_items[] = $item;
      }
    }
    
    // Add any remaining items that weren't in the preferred order
    foreach ($toolbar_items as $item) {
      if (!in_array($item, $ordered_items, TRUE)) {
        $ordered_items[] = $item;
      }
    }
    
    return $ordered_items;
  }

  /**
   * Activates and configures additional editor plugins.
   *
   * @param array $plugin_settings
   *   The current plugin settings.
   *
   * @return array
   *   The updated plugin settings with additional plugins activated.
   */
  protected function activateAdditionalPlugins(array $plugin_settings): array {
    // Example: Activate and configure additional plugins
    
    // Enable alignment plugin
    $plugin_settings['ckeditor5_alignment'] = [
      'enabled_alignments' => [
        'center',
        'justify',
        'left',
        'right',
      ],
    ];
    
    // Configure heading plugin
    $plugin_settings['ckeditor5_heading'] = [
      'enabled_headings' => [
        'heading1',
        'heading2',
        'heading3',
        'heading4',
        'heading5',
        'heading6',
      ],
    ];
    
    // Configure image resize plugin
    $plugin_settings['ckeditor5_imageResize'] = [
      'allow_resize' => TRUE,
    ];
    
    // Configure list plugin
    $plugin_settings['ckeditor5_list'] = [
      'properties' => [
        'reversed' => TRUE,
        'startIndex' => TRUE,
      ],
      'multiBlock' => TRUE,
    ];

    // Configure paste filter plugin
    $plugin_settings['ckeditor5_paste_filter_pasteFilter'] = [
      'enabled' =>  TRUE,
      'filters' =>  [
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
    ];

    // Configure source editing plugin
    $plugin_settings['ckeditor5_sourceEditing'] = [
      'allowed_tags' => [],
    ];

    // Configure advanced link plugin
    $plugin_settings['editor_advanced_link_link'] = [
      'enabled_attributes' => [
        'rel',
        'target',
        'title',
      ],
    ];
    
    return $plugin_settings;
  }

}
