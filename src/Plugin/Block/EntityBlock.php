<?php

/**
 * @file
 * Contains Drupal\entity_block\Plugin\Block\EntityBlock.
 */

namespace Drupal\entity_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an entity block.
 *
 * @Block(
 *  id = "entity_block",
 *  admin_label = @Translation("Entity block"),
 * )
 */
class EntityBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $entity_types = [];
    $view_modes = [];
    $definitions = \Drupal::entityManager()->getDefinitions();

    // Build entity types and view modes options.
    foreach ($definitions as $entity_type => $definition) {
      if ($definition->getGroup() == 'content') {
        $entity_types[$entity_type] = $definition->getLabel();

        $view_modes[$entity_type] = \Drupal::entityManager()->getViewModeOptions($entity_type);
      }
    }

    $form['entity_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Entity type'),
      '#required' => TRUE,
      '#options' => $entity_types,
      '#default_value' => isset($this->configuration['entity_type']) ? $this->configuration['entity_type'] : '',
    );

    $form['view_mode'] = array(
      '#type' => 'select',
      '#title' => $this->t('View mode'),
      '#required' => TRUE,
      '#options' => $view_modes,
      '#default_value' => isset($this->configuration['view_mode']) ? $this->configuration['view_mode'] : '',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['entity_type'] = $form_state->getValue('entity_type');
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $entity_type = $this->configuration['entity_type'];
    $view_mode = $this->configuration['view_mode'];

    // Load entity from route.
    $entity = \Drupal::routeMatch()->getParameter($entity_type);

    // Render entity in given view mode if found.
    if ($entity) {
      $render_controller = \Drupal::entityManager()->getViewBuilder($entity_type);
      $build = $render_controller->view($entity, $view_mode);
    }

    return $build;
  }

}
