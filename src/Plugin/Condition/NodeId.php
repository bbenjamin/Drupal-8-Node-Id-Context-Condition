<?php

namespace Drupal\node_id_context\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Node Id' condition to context.
 *
 * @condition(
 *   id = "node_id",
 *   label = @Translation("Node Id"),
 *   context = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       required = FALSE,
 *       label = @translation("node"))
 *   }
 * )
 */
class NodeId extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
    $configuration,
    $plugin_id,
    $plugin_definition
    );
  }

  /**
   * Creates a new NodeId condition plugin.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
   public function __construct(array $configuration, $plugin_id, $plugin_definition) {
      parent::__construct($configuration, $plugin_id, $plugin_definition);
   }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['nids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Node Ids'),
      '#default_value' => $this->configuration['nids'],
      '#description' => $this->t("Enter one node id per line."),
    ];
    $extended_form = parent::buildConfigurationForm($form, $form_state);
    unset($extended_form['context_mapping']);
    unset($extended_form['negate']);
    return $extended_form;
   }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['nids'] = $form_state->getValue('nids');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['nids' => ''] + parent::defaultConfiguration();
  }

  /**
    * Evaluates the condition and returns TRUE or FALSE accordingly.
    *
    * @return bool
    *   TRUE if the condition has been met, FALSE otherwise.
    */
  public function evaluate() {
    $admin_context = \Drupal::service('router.admin_context');
    if (!$admin_context->isAdminRoute()) {
      $nids = ($this->configuration['nids']);
      if (!$nids) {
        return FALSE;
      }
      $node_ids = array_map('trim', explode("\n", $nids));

      if ($node = \Drupal::request()->attributes->get('node')) {
        $nid = $node->id();
        return in_array($nid, $node_ids);
      }
    }
    return FALSE;
  }

/**
 * Provides a human readable summary of the condition's configuration.
 */
  public function summary() {
    $nids = array_map('trim', explode("\n", $this->configuration['nids']));
    $nids = implode(', ', $nids);
    return $this->t('Return true on the following nids: @nids', ['@nidss' => $nids]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();
    $contexts[] = 'url.path';
    return $contexts;
  }

}
