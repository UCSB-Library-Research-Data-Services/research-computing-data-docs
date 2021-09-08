<?php

namespace Drupal\site_settings\Tests;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the loading of Site Settings.
 *
 * @group SiteSettings
 */
class SiteSettingsUiTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Module list.
   *
   * @var array
   */
  public static $modules = [
    'site_settings',
    'site_settings_sample_data',
    'field_ui',
    'user',
  ];

  /**
   * Admin user.
   *
   * @var \Drupal\user\Entity\User|false
   */
  private $adminUser;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function setUp() {
    parent::setUp();

    // Create the user and login.
    $this->adminUser = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test site settings admin visibility.
   */
  public function testSiteSettingsAdminVisibility() {
    // Open the site settings list page.
    $this->drupalGet('admin/content/site-settings');

    // Make sure the fieldsets match.
    $this->assertRaw('<strong>Images</strong>');
    $this->assertRaw('<strong>Other</strong>');

    // Make sure the test plain text is as expected.
    $this->assertText('Test plain text');

    // Make sure the test textarea is as expected.
    $this->assertText('Test textarea name');

    // Make sure the test multiple entries contents are as expected.
    $this->assertText('Test multiple entries');
    $this->assertText('Test multiple entries name 2');

    // Make sure the test multiple entries and fields contents are as expected.
    $this->assertText('Test multiple entries and fields name 1');
    $this->assertText('Test multiple entries and fields name 2');

    // Make sure the test multiple fields contents are as expected.
    $this->assertText('Test multiple fields name');

    // Make sure the test image is as expected.
    $this->assertText('Test image');
    $this->assertText('Test images 1');
    $this->assertText('Test file');

  }

  /**
   * Test site settings add another.
   */
  public function testSiteSettingsAddAnother() {
    // Open the site settings list page.
    $this->drupalGet('admin/content/site-settings');

    // Click add another link.
    $this->clickLink('Add another', 2);

    // Make sure we can see the expected form.
    $this->assertText('Test multiple entries');
    $this->assertText('Testing');
    $params = [
      'field_testing[0][value]' => 'testSiteSettingsAddAnother',
    ];
    $this->drupalPostForm(NULL, $params, t('Save'));

    // Ensure we saved correctly.
    $this->assertText('Created the Test multiple entries Site Setting.');
    $this->assertText('testSiteSettingsAddAnother');
  }

  /**
   * Test site settings edit existing.
   */
  public function testSiteSettingsEditExisting() {
    // Open the site settings list page.
    $this->drupalGet('admin/content/site-settings');

    // Click add another link.
    $this->clickLink('Edit', 5);

    // Make sure we can see the expected form.
    $this->assertText('Test plain text');
    $this->assertText('Testing');
    $params = [
      'field_testing[0][value]' => 'testSiteSettingsEditExisting',
    ];
    $this->drupalPostForm(NULL, $params, t('Save'));

    // Ensure we saved correctly.
    $this->assertText('Saved the Test plain text Site Setting.');
    $this->assertText('testSiteSettingsEditExisting');
  }

  /**
   * Test site settings create new type and add a setting to that.
   */
  public function testSiteSettingsCreateNewTypeAndSetting() {
    // Open the site settings list page.
    $this->drupalGet('admin/structure/site_setting_entity_type/add');

    // Create the new site setting.
    $params = [
      'label' => 'testSiteSettingsCreateNewTypeAndSetting',
      'id' => 'testsitesettingscreatenew',
      'existing_fieldset' => 'Other',
    ];
    $this->drupalPostForm(NULL, $params, t('Save'));

    // Ensure we saved correctly.
    $this->assertText('Created the testSiteSettingsCreateNewTypeAndSetting Site Setting type.');

    // Add field.
    $this->drupalGet('admin/structure/site_setting_entity_type/testsitesettingscreatenew/edit/fields/add-field');
    $params = [
      'existing_storage_name' => 'field_testing',
      'existing_storage_label' => 'testSiteSettingsCreateNewTypeAndSettingLabel',
    ];
    $this->drupalPostForm(NULL, $params, t('Save and continue'));

    // Save field settings.
    $params = [];
    $this->drupalPostForm(NULL, $params, t('Save settings'));

    // Ensure we saved correctly.
    $this->assertText('Saved testSiteSettingsCreateNewTypeAndSettingLabel configuration.');
    $this->assertText('field_testing');

    // Open the site settings list page.
    $this->drupalGet('admin/content/site-settings');

    // Click add another link.
    $this->clickLink('Create setting');
    $this->assertText('testSiteSettingsCreateNewTypeAndSettingLabel');
    $params = [
      'field_testing[0][value]' => 'testSiteSettingsCreateNewTypeAndSettingValue',
    ];
    $this->drupalPostForm(NULL, $params, t('Save'));

    // Ensure we saved correctly.
    $this->assertText('Created the testSiteSettingsCreateNewTypeAndSetting Site Setting.');
    $this->assertText('testSiteSettingsCreateNewTypeAndSettingValue');
  }

}
