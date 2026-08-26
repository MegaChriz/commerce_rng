<?php

namespace Drupal\commerce_rng;

use Drupal\commerce\Context;
use Drupal\commerce_order\AvailabilityCheckerInterface;
use Drupal\commerce_order\AvailabilityResult;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\rng\EventManagerInterface;
use Drupal\rng\EventMetaInterface;

/**
 * Checks if an event is open for registrations.
 *
 * @package Drupal\commerce_rng
 */
class EventAvailabilityChecker implements AvailabilityCheckerInterface {

  use StringTranslationTrait;

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
   * Constructs a new EventAvailabilityChecker object.
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
  public function check(OrderItemInterface $order_item, Context $context): AvailabilityResult {
    $product = $this->getEventProductFromOrderItem($order_item);
    if (!$product instanceof ProductInterface) {
      return AvailabilityResult::unavailable($this->t('This product is not an event.'));
    }

    $meta = $this->eventManager->getMeta($product);
    if (!$meta instanceof EventMetaInterface) {
      // No metadata available.
      return AvailabilityResult::unavailable($this->t('Event metadata is not available.'));
    }

    if (!$meta->isAcceptingRegistrations()) {
      return AvailabilityResult::unavailable($this->t('This event is not accepting registrations.'));
    }

    // Check for registration types.
    $types = $meta->getRegistrationTypeIds();
    if (empty($types)) {
      // No registration types.
      return AvailabilityResult::unavailable($this->t('This event has no registration types.'));
    }

    // Check if the current user is allowed to register.
    // @todo Check whether the current user may register for this event.
    return AvailabilityResult::neutral();
  }

}
