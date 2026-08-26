<?php

namespace Drupal\commerce_rng\Plugin\IdentityChannel\CourierEmail;

use Drupal\Core\Entity\EntityInterface;
use Drupal\courier\ChannelInterface;
use Drupal\courier\EmailInterface;
use Drupal\courier\Exception\IdentityException;
use Drupal\courier\Plugin\IdentityChannel\IdentityChannelPluginInterface;

/**
 * Supports profile entities.
 *
 * @IdentityChannel(
 *   id = "identity:commerce_rng_profile:courier_email",
 *   label = @Translation("profile to courier_mail"),
 *   channel = "courier_email",
 *   identity = "profile",
 *   weight = 10
 * )
 */
class Profile implements IdentityChannelPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function applyIdentity(ChannelInterface &$message, EntityInterface $identity) {
    if (!$message instanceof EmailInterface) {
      throw new IdentityException('Message is not an email channel.');
    }

    if (isset($identity->field_email)) {
      $email = $identity->field_email;
      if (!empty($email->value)) {
        $message->setRecipientName($identity->label());
        $message->setEmailAddress($email->value);
      }
      else {
        throw new IdentityException('Contact missing email address.');
      }
    }
    else {
      throw new IdentityException('Contact type email field not configured.');
    }
  }

}
