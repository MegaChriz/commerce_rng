<?php

namespace Drupal\commerce_rng;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\rng\RegistrationInterface;

/**
 * Interface for dealing with registrations on orders.
 */
interface RegistrationDataInterface {

  /**
   * Creates registrations for order items that don't have them yet.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order to create registrations for.
   */
  public function generateOrderRegistrations(OrderInterface $order);

  /**
   * Returns the order item from the registration.
   *
   * @param \Drupal\rng\RegistrationInterface $registration
   *   The registration entity.
   *
   * @return \Drupal\commerce_order\Entity\OrderItemInterface|null
   *   The order item associated with the registration or null, if the
   *   registration does not have an order item.
   */
  public function registrationGetOrderItem(RegistrationInterface $registration);

  /**
   * Returns all registrations.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order to find all registrations for.
   *
   * @return \Drupal\rng\Entity\Registration[]
   *   A list of registration entities.
   */
  public function getOrderRegistrations(OrderInterface $order);

  /**
   * Returns a single registration for the given order item ID.
   *
   * @param int $order_item_id
   *   The ID of the order item to find a registration for.
   *
   * @return \Drupal\rng\Entity\Registration|null
   *   A registration entity, if found. Null otherwise.
   */
  public function getRegistrationByOrderItemId($order_item_id);

}
