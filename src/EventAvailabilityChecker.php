<?php

namespace Drupal\commerce_rng;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_order\AvailabilityCheckerInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce\Context;
use Drupal\rng\EventManagerInterface;

/**
 * Checks if an event is open for registrations.
 *
 * @package Drupal\commerce_rng
 */
class EventAvailabilityChecker implements AvailabilityCheckerInterface {

  /**
   * The event manager.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;

  /**
   * The registration data service.
   *
   * @var \Drupal\commerce_rng\RegistrationDataInterface
   */
  protected $registrationData;

  /**
   * Constructs a new StockAvailabilityChecker object.
   *
   * @param \Drupal\rng\EventManagerInterface $event_manager
   *   The event manager.
   * @param \Drupal\commerce_rng\RegistrationDataInterface $registration_data
   *   The registration data service.
   */
  public function __construct(EventManagerInterface $event_manager, RegistrationDataInterface $registration_data) {
    $this->eventManager = $event_manager;
    $this->registrationData = $registration_data;
  }

  /**
   * Returns the order item's product if the product is a RNG event.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The order item to check for.
   *
   * @return \Drupal\commerce_product\Entity\ProductInterface|null
   *   The product entity if it is an event, or null.
   */
  protected function getEventProductFromOrderItem(OrderItemInterface $order_item): ?ProductInterface {
    return $this->registrationData->orderItemGetEvent($order_item);
  }

  /**
   * {@inheritdoc}
   */
  public function applies(OrderItemInterface $order_item) {
    if ($this->getEventProductFromOrderItem($order_item)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function check(OrderItemInterface $order_item, Context $context) {
    $product = $this->getEventProductFromOrderItem($order_item);
    if (!$product) {
      return FALSE;
    }

    /** @var \Drupal\rng\EventMetaInterface|null $meta */
    $meta = $this->eventManager->getMeta($product);
    if (!$meta) {
      // No metadata available.
      return FALSE;
    }

    if (!$meta->isAcceptingRegistrations()) {
      return FALSE;
    }

    // Check for registration types.
    $types = $meta->getRegistrationTypeIds();
    if (empty($types)) {
      // No registration types.
      return FALSE;
    }

    // Check if the current user is allowed to register.
    // @todo
  }

}
