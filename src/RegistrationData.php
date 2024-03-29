<?php

namespace Drupal\commerce_rng;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_product\Entity\ProductVariationType;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rng\Entity\Registration;
use Drupal\rng\EventManagerInterface;
use Drupal\rng\Entity\RegistrationInterface;

/**
 * Service for managing registration data.
 */
class RegistrationData implements RegistrationDataInterface {

  /**
   * The RNG event manager.
   *
   * @var \Drupal\rng\EventManagerInterface
   */
  protected $eventManager;

  /**
   * The registration manager.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $registrationStorage;

  /**
   * The registrant manager.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $registrantStorage;

  /**
   * Constructs a new RegistrationData object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\rng\EventManagerInterface $event_manager
   *   The RNG event manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EventManagerInterface $event_manager) {
    $this->registrationStorage = $entity_type_manager->getStorage('registration');
    $this->registrantStorage = $entity_type_manager->getStorage('registrant');
    $this->eventManager = $event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function generateOrderRegistrations(OrderInterface $order) {
    foreach ($order->getItems() as $order_item) {
      $product = $this->orderItemGetEvent($order_item);
      if (!$product) {
        // Not an event.
        continue;
      }

      $order_item_id = $order_item->id();

      // Check for an existing registration on the order item.
      /** @var \Drupal\rng\Entity\Registration|null $registration */
      $registration = $this->getRegistrationByOrderItemId($order_item_id);
      if (!$registration) {
        // Create a new registration.
        $registration = $this->createRegistration($product);
        $registration->field_order_item = $order_item_id;
        $registration->save();
      }
    }
  }

  /**
   * Creates a registration for the given order item.
   *
   * @param \Drupal\Core\Entity\EntityInterface $event
   *   The event to create a registration entity for.
   *
   * @return \Drupal\rng\RegistrationInterface
   *   A registration instance.
   *
   * @todo fails if event is not configured.
   */
  protected function createRegistration(EntityInterface $event) {
    $registration_types = $this->eventManager->getMeta($event)->getRegistrationTypes();
    if (count($registration_types) > 1) {
      throw new \Exception('Multiple registration types not supported by UKKB Study.');
    }
    if (count($registration_types) === 0) {
      throw new \Exception('No registration types found.');
    }

    $registration_type = reset($registration_types);

    $registration = Registration::create([
      'type' => $registration_type->id(),
    ]);
    $registration->setEvent($event);

    return $registration;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderRegistrations(OrderInterface $order) {
    // Get all order items id's.
    $order_item_ids = array_column($order->order_items->getValue(), 'target_id');

    if (empty($order_item_ids)) {
      // No order items. Bail out to avoid invalid query.
      return [];
    }

    // Get all registrations referring these order id's.
    $registration_ids = $this->registrationStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('field_order_item', $order_item_ids, 'IN')
      ->execute();
    krsort($registration_ids);

    $registrations = $this->registrationStorage->loadMultiple($registration_ids);
    return $registrations;
  }

  /**
   * {@inheritdoc}
   */
  public function getRegistrationByOrderItemId($order_item_id) {
    // Get all registrations referring these order id's.
    $registration_ids = $this->registrationStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('field_order_item', $order_item_id)
      ->execute();

    if (!empty($registration_ids)) {
      $registration_id = reset($registration_ids);
      return $this->registrationStorage->load($registration_id);
    }
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
  public function orderItemGetEvent(OrderItemInterface $order_item) {
    $purchased_entity = $order_item->getPurchasedEntity();
    if ($purchased_entity instanceof ProductVariationInterface) {
      $product = $purchased_entity->getProduct();
      if ($product && $this->eventManager->isEvent($product)) {
        return $product;
      }
    }
  }

  /**
   * Builds registrant list per order item.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order to build a registrant list for.
   *
   * @return array
   *   A drupal render array.
   */
  public function buildRegistrantLists(OrderInterface $order) {
    // Get registrants per order item.
    $registrants_per_order_item = [];
    foreach ($order->getItems() as $item) {
      $order_item_id = $item->id();
      $registration = $this->getRegistrationByOrderItemId($order_item_id);
      if ($registration) {
        foreach ($registration->getRegistrants() as $registrant) {
          // Skip empty registrants.
          if (!$registrant->id()) {
            continue;
          }
          $identity = $registrant->getIdentity();
          if ($identity) {
            $registrants_per_order_item[$order_item_id][$registrant->id()] = $identity->label();
          }
          else {
            $registrants_per_order_item[$order_item_id][$registrant->id()] = $registrant->label();
          }
        }
      }
    }

    $list = [];
    if (!empty($registrants_per_order_item)) {
      foreach ($registrants_per_order_item as $order_item_id => $registrant_list) {
        $list[$order_item_id]['registrants'] = [
          '#theme' => 'item_list',
          '#title' => t('Registrants'),
          '#items' => $registrant_list,
        ];
      }
    }

    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function registrationGetOrderItem(RegistrationInterface $registration) {
    if ($registration->hasField('field_order_item')) {
      $items = $registration->field_order_item->referencedEntities();
      return reset($items);
    }
  }

  /**
   * Updates the order item quantity.
   *
   * This is based on the number of registrants for this item.
   *
   * @param \Drupal\commerce_order\Entity\OrderItemInterface $order_item
   *   The order item to update.
   */
  public function orderItemUpdateQuantity(OrderItemInterface $order_item) {
    $registration = $this->getRegistrationByOrderItemId($order_item->id());
    if ($registration) {
      $quantity = count($registration->getRegistrantIds());
      // Update the order item quantity in case it is above zero.
      if ($quantity > 0) {
        $order_item->setQuantity($quantity);
        $registration->setRegistrantQty($quantity);
        $registration->save();
      }
      else {
        // If no registrants exist for this item, the quantity is always one.
        // This is to prevent the order item from getting removed after deleting
        // a registrant.
        $order_item->setQuantity(1);
        // But on the registration, the quantity becomes zero. Else registrant
        // stubs get created that are missing identities.
        $registration->setRegistrantQty(0);
        $registration->save();
      }
    }
    elseif ($this->orderItemGetEvent($order_item)) {
      // If no registration for this item is known, the quantity is always one.
      $order_item->setQuantity(1);
    }
  }

  /**
   * Formats registration data in a simple format.
   *
   * @param \Drupal\rng\Entity\RegistrationInterface[] $registrations
   *   A list of registrations.
   *
   * @return array
   *   An array of registration data.
   */
  public function formatRegistrationData(array $registrations) {
    $data = [];

    foreach ($registrations as $registration) {
      $registration_type = $registration->get('type')->referencedEntities()[0];
      $conference = $registration->get('event')->referencedEntities()[0];
      $order_item = $registration->get('field_order_item')->referencedEntities()[0];
      $order = $order_item->getOrder();
      $product_variation = $order_item->getPurchasedEntity();
      $product_variation_type = ProductVariationType::load($product_variation->bundle());

      $billing_profile = NULL;
      if ($order->getBillingProfile()) {
        $billing_profile = $order->getBillingProfile()->get('address')[0];
      }

      $general_data = [
        'order_id' => $order->getOrderNumber(),
        'order_data' => $order->getCreatedTime(),
        'conference_id' => $conference->id(),
        'conference_name' => $conference->getTitle(),
        'registration_id' => $registration->id(),
        'registration_type' => $registration_type->label,
        'order_item_id' => $order_item->id(),
        'product_variation_id' => $product_variation->id(),
        'product_variation_title' => $product_variation->getTitle(),
        'product_variation_type' => $product_variation->bundle(),
        'product_variation_type_title' => $product_variation_type->label(),
        'registrant_company' => $billing_profile ? $billing_profile->getOrganization() : '',
      ];

      $registrants = $registration->getRegistrants();

      foreach ($registrants as $registrant) {
        $registrant_id = $registrant->id();
        $data[$registrant_id] = $general_data + [
          'registrant_id' => $registrant_id,
        ];

        $identity = $registrant->getIdentity();
        if ($identity) {
          $data[$registrant_id] += [
            'registrant_identity_id' => $identity->id(),
            'registrant_identity_type' => $identity->getEntityTypeId(),
            'registrant_label' => $identity->label(),
          ];
        }
        else {
          $data[$registrant_id] += [
            'registrant_identity_id' => 0,
            'registrant_identity_type' => '',
            'registrant_label' => $registrant->label(),
          ];
        }
      }
    }

    return $data;
  }

}
