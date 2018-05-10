<?php
/*-------------------------------------------------------+
| Project 60 - CiviBanking                               |
| Copyright (C) 2013-2015 SYSTOPIA                       |
| Author: B. Endres (endres -at- systopia.de)            |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL v3 license. You can redistribute it and/or  |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

require_once 'CRM/Core/Form.php';

/**
 * CiviBanking settings form
 *
 * @todo refactor to use CRM_Core_Form correctly
 */
class CRM_Admin_Form_Setting_BankingSettings extends CRM_Core_Form {


  function buildQuickForm() {

    // add Menu Switch
    $menu_position_options = array(
      0 => ts("In Main Menu", array('domain' => 'org.project60.banking')),
      1 => ts("In Contribution Menu", array('domain' => 'org.project60.banking')),
      2 => ts("No Menu", array('domain' => 'org.project60.banking')));

    $menu_position = $this->add(
      'select',
      'menu_position',
      ts('CiviBanking Menu Position', array('domain' => 'org.project60.banking')),
      $menu_position_options,
      false // is not required
    );

    // add JSON editor mode
    $json_editor_mode = array(
      'view' => ts("Tree View", array('domain' => 'org.project60.banking')),
      'text' => ts("Simple Text", array('domain' => 'org.project60.banking')),
      'code' => ts("JSON Code", array('domain' => 'org.project60.banking')),
      'tree' => ts("Tree Editor", array('domain' => 'org.project60.banking')),
      'form' => ts("Form Editor", array('domain' => 'org.project60.banking')),
    );

    $json_editor_mode = $this->add(
      'select',
      'json_editor_mode',
      ts('Configuration Editor Default Mode', array('domain' => 'org.project60.banking')),
      $json_editor_mode,
      TRUE
    );

    // logging
    $log_level = $this->add(
      'select',
      'banking_log_level',
      ts('Log Level', array('domain' => 'org.project60.banking')),
      CRM_Banking_Helpers_Logger::getLoglevels()
    );

    $log_file = $this->add(
      'text',
      'banking_log_file',
      ts('Log File', array('domain' => 'org.project60.banking')),
      "TEST"
    );

    // store bank accounts
    $this->addElement(
      'checkbox',
      'reference_store_disabled',
      ts("Don't store bank accounts automatically", array('domain' => 'org.project60.banking')),
      '');

    // normalise bank account references?
    $this->addElement(
      'checkbox',
      'reference_normalisation',
      ts('Normalise bank account references', array('domain' => 'org.project60.banking')),
      '');

    // validate bank account references?
    $this->addElement(
      'checkbox',
      'reference_validation',
      ts('Validate bank account references', array('domain' => 'org.project60.banking')),
      '');


    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts("Save", array('domain' => 'org.project60.banking')),
        'isDefault' => TRUE,
      ),
    ));

    parent::buildQuickForm();
  }


  /**
   * Overridden parent method to set default values
   * @return array
   */
  function setDefaultValues() {
    $defaults = array();

    $defaults['menu_position']            = CRM_Core_BAO_Setting::getItem('CiviBanking', 'menu_position');
    $defaults['json_editor_mode']         = CRM_Core_BAO_Setting::getItem('CiviBanking', 'json_editor_mode');
    $defaults['banking_log_level']        = CRM_Core_BAO_Setting::getItem('CiviBanking', 'banking_log_level');
    $defaults['banking_log_file']         = CRM_Core_BAO_Setting::getItem('CiviBanking', 'banking_log_file');
    $defaults['reference_store_disabled'] = CRM_Core_BAO_Setting::getItem('CiviBanking', 'reference_store_disabled');
    $defaults['reference_normalisation']  = CRM_Core_BAO_Setting::getItem('CiviBanking', 'reference_normalisation');
    $defaults['reference_validation']     = CRM_Core_BAO_Setting::getItem('CiviBanking', 'reference_validation');

    return $defaults;
  }


  /**
   * store settings
   */
  function postProcess() {
    $values = $this->exportValues();

    // process menu entry
    $old_menu_position = (int) CRM_Core_BAO_Setting::getItem('CiviBanking', 'menu_position');
    $new_menu_position = (int) $values['menu_position'];
    if ($old_menu_position != $new_menu_position) {
      CRM_Core_BAO_Setting::setItem($new_menu_position, 'CiviBanking', 'menu_position');
      CRM_Core_Invoke::rebuildMenuAndCaches();
    }

    // process menu entry
    CRM_Core_BAO_Setting::setItem($values['json_editor_mode'], 'CiviBanking', 'json_editor_mode');

    // log levels
    CRM_Core_BAO_Setting::setItem($values['banking_log_level'], 'CiviBanking', 'banking_log_level');
    CRM_Core_BAO_Setting::setItem($values['banking_log_file'],  'CiviBanking', 'banking_log_file');

    // process reference normalisation / validation
    CRM_Core_BAO_Setting::setItem(!empty($values['reference_store_disabled']),         'CiviBanking', 'reference_store_disabled');
    CRM_Core_BAO_Setting::setItem(!empty($values['reference_normalisation']), 'CiviBanking', 'reference_normalisation');
    CRM_Core_BAO_Setting::setItem(!empty($values['reference_validation']),    'CiviBanking', 'reference_validation');

    // log results
    $logger = CRM_Banking_Helpers_Logger::getLogger();
    $logger->logDebug("Log level changed to '{$values['banking_log_level']}', file is: {$values['banking_log_file']}");

    parent::postProcess();
  }
}
