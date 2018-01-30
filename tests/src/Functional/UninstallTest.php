<?php

namespace Drupal\Tests\commerce_rng\Functional;

use Drupal\Tests\commerce\Functional\UninstallTest as UninstallTestBase;

/**
 * Tests module uninstallation.
 *
 * @group commerce_rng
 */
class UninstallTest extends UninstallTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce',
    'commerce_price',
    'commerce_log',
    'commerce_store',
    'commerce_product',
    'commerce_order',
    'commerce_cart',
    'commerce_checkout',
    'commerce_payment',
    'commerce_tax',
    'commerce_rng',
  ];

  /**
   * Tests module uninstallation.
   *
   * @todo fails because commerce_order contains a view that references a
   * commerce_product_variaton entity.
   */
  public function testUninstall() {
    // Confirm that all Commerce modules have been installed successfully.
    $installed_modules = $this->container->get('module_handler')->getModuleList();
    foreach (self::$modules as $module) {
      $this->assertArrayHasKey($module, $installed_modules, t('Module @module installed successfully.', ['@module' => $module]));
    }

    // Uninstall all modules except the base module.
    $modules = array_slice(self::$modules, 1);
    $this->container->get('module_installer')->uninstall($modules);
    $this->rebuildContainer();
    // Purge field data in order to remove the commerce_remote_id field.
    field_purge_batch(50);
    // Uninstall the base module.
    $modules[] = 'commerce';
    $this->container->get('module_installer')->uninstall(['commerce']);
    $this->rebuildContainer();
    $installed_modules = $this->container->get('module_handler')->getModuleList();
    foreach ($modules as $module) {
      $this->assertArrayNotHasKey($module, $installed_modules, t('Module @module uninstalled successfully.', ['@module' => $module]));
    }

    // Reinstall the modules. If there was no trailing configuration left
    // behind after uninstall, then this too should be successful.
    $this->container->get('module_installer')->install(self::$modules);
    $this->rebuildContainer();
    $installed_modules = $this->container->get('module_handler')->getModuleList();
    foreach (self::$modules as $module) {
      $this->assertArrayHasKey($module, $installed_modules, t('Module @module reinstalled successfully.', ['@module' => $module]));
    }
  }

}