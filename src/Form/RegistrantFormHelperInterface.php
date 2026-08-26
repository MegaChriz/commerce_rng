<?php

namespace Drupal\commerce_rng\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\rng\Entity\RegistrantInterface;

/**
 * Interface for building registrant forms.
 */
interface RegistrantFormHelperInterface {

  /**
   * Builds a form element for a single registrant.
   *
   * @param array $form
   *   The form structure to fill in.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The complete state of the form.
   * @param \Drupal\rng\Entity\RegistrantInterface $registrant
   *   The registrant to build the form for.
   *
   * @return array
   *   A form for creating or editing a registrant.
   */
  public function buildRegistrantForm(array $form, FormStateInterface $form_state, RegistrantInterface $registrant);

  /**
   * Submits registrant data.
   *
   * @param array $form
   *   The form structure to fill in.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The complete state of the form.
   *
   * @return \Drupal\rng\Entity\RegistrantInterface
   *   The created registrant.
   */
  public function submitRegistrantForm(array &$form, FormStateInterface $form_state);

  /**
   * Builds a form element for a single person based on registrant data.
   *
   * @param array $form
   *   The form structure to fill in.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The complete state of the form.
   * @param \Drupal\rng\Entity\RegistrantInterface $registrant
   *   The registrant to build the form for.
   *
   * @return array
   *   A form for creating or editing a person.
   */
  public function buildPersonFormByRegistrant(array $form, FormStateInterface $form_state, RegistrantInterface $registrant);

  /**
   * Builds a form element for a single person.
   *
   * @param array $form
   *   The form structure to fill in.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The complete state of the form.
   * @param \Drupal\Core\Entity\EntityInterface $event
   *   The entity that acts as the event.
   * @param \Drupal\Core\Entity\EntityInterface $person
   *   The person attending the event.
   *
   * @return array
   *   A form for creating or editing a person.
   */
  public function buildPersonForm(array $form, FormStateInterface $form_state, EntityInterface $event, EntityInterface $person);

  /**
   * Submits person data.
   *
   * @param array $form
   *   The form structure to fill in.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The complete state of the form.
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order that is associated with the registration.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The created person.
   */
  public function submitPersonForm(array &$form, FormStateInterface $form_state, OrderInterface $order);

  /**
   * Returns the default identity type for this event.
   *
   * @param \Drupal\Core\Entity\EntityInterface $event
   *   The entity that acts as the event.
   *
   * @return string[]
   *   An array that consists of the entity type ID and the bundle.
   */
  public function getIdentityType(EntityInterface $event);

  /**
   * Returns the event for the given registrant.
   *
   * @param \Drupal\rng\Entity\RegistrantInterface $registrant
   *   A registrant entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The event entity.
   */
  public function getEvent(RegistrantInterface $registrant);

  /**
   * Creates a new person for the registrant.
   *
   * @param \Drupal\rng\Entity\RegistrantInterface $registrant
   *   The registrant to create a person for.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The person entity.
   */
  public function createPersonForRegistrant(RegistrantInterface $registrant);

}
