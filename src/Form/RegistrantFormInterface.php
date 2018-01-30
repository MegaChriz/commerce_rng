<?php

namespace Drupal\commerce_rng\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Interface for a registrant form.
 */
interface RegistrantFormInterface extends FormInterface {

  /**
   * Returns the order for which the registrant is edited.
   *
   * @return \Drupal\commerce_order\Entity\OrderInterface
   *   A commerce order.
   */
  public function getOrder();

  /**
   * Returns the registrant that is being edited.
   *
   * @return \Drupal\rng\RegistrantInterface
   *   A registrant.
   */
  public function getRegistrant();

}