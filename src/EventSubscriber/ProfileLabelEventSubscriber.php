<?php

namespace Drupal\commerce_rng\EventSubscriber;

use Drupal\profile\Event\ProfileEvents;
use Drupal\profile\Event\ProfileLabelEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to events from the profile module.
 */
class ProfileLabelEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = [
      ProfileEvents::PROFILE_LABEL => ['profileSetLabel', -100],
    ];

    return $events;
  }

  /**
   * Overrides the label of a profile.
   *
   * @param \Drupal\profile\Event\ProfileLabelEvent $event
   *   The profile label event.
   */
  public function profileSetLabel(ProfileLabelEvent $event) {
    /** @var \Drupal\profile\Entity\ProfileInterface $profile */
    $profile = $event->getProfile();

    if ($profile->bundle() != 'person') {
      return;
    }

    if (!$profile->hasField('field_name')) {
      return;
    }

    $value = $profile->get('field_name')->getString();
    if ($value !== '') {
      $event->setLabel($value);
    }
  }

}
