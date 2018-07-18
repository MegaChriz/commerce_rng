# Commerce RNG

[![Build Status](https://travis-ci.org/MegaChriz/commerce_rng.svg?branch=8.x-1.x)](https://travis-ci.org/MegaChriz/afasprofit)

This is an example implementation of how an integration between RNG and Commerce
could work.

It makes the following assumptions:

  - The Commerce product type is called 'event'.
  - For RNG only a single identity type is configured.


## How it works

1. A customer adds a product of type 'event' to their cart.
2. The customer continues to checkout.
3. When arriving at the step 'Event registration', the customer can add, edit
   and remove registrants for each event in the cart.
4. The customer completes checkout.


## Patches

When using RNG 8.x-1.5 (the latest release at the time of writing), you'll need
to apply the patch "rng-152-event-type-get-identity-type-entity-form-mode.patch"
to RNG.

To ensure persons created by an anonymous customer get owned by the customer
after he/she creates an account at the end, you'll need to apply the patch
"commerce-checkout-pane-guest-registration-2857157-88.patch" to Commerce.


## Known issues

- When adding, editing or removing registrants on checkout via AJAX, the
  checkout panes are not updated.