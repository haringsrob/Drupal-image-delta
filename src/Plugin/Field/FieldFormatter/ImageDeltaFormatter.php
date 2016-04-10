<?php

namespace Drupal\image_delta\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'realname_one_line' formatter.
 *
 * @FieldFormatter(
 *   id = "image_delta",
 *   label = @Translation("Image delta"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageDeltaFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'delta' => 0,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $element['delta'] = array(
      '#title' => t('The ID of the image to use.'),
      '#type' => 'textfield',
      '#size' => 2,
      '#default_value' => $this->getSetting('delta'),
    );

    return $element + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return ['string' => 'Delta #' . $this->getSetting('delta')] + parent::settingsSummary();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode) {
    // Initialize array.
    $delta_item = [];

    // Remove all items but the delta.
    if ($delta = $items->get($this->getSetting('delta'))) {
      $delta_item = $delta->getValue();
    }

    // Set the items to our delta.
    $items->setValue($delta_item);

    // Add the default image if needed.
    if ($items->isEmpty()) {
      $default_image = $this->getFieldSetting('default_image');
      // If we are dealing with a configurable field, look in both
      // instance-level and field-level settings.
      if (empty($default_image['uuid']) && $this->fieldDefinition instanceof FieldConfigInterface) {
        $default_image = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('default_image');
      }
      if (!empty($default_image['uuid']) && $file = \Drupal::entityManager()->loadEntityByUuid('file', $default_image['uuid'])) {
        // Clone the FieldItemList into a runtime-only object for the formatter,
        // so that the fallback image can be rendered without affecting the
        // field values in the entity being rendered.
        $items = clone $items;
        $items->setValue(array(
          'target_id' => $file->id(),
          'alt' => $default_image['alt'],
          'title' => $default_image['title'],
          'width' => $default_image['width'],
          'height' => $default_image['height'],
          'entity' => $file,
          '_loaded' => TRUE,
          '_is_default' => TRUE,
        ));
        $file->_referringItem = $items[0];
      }
    }

    return parent::getEntitiesToView($items, $langcode);
  }

}
