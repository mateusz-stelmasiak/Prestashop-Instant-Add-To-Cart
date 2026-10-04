<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

// 1.1.0: instant add button on product miniatures.
function upgrade_module_1_1_0($module)
{
    Configuration::updateValue(InstantCart::K_LISTING, 1);

    return $module->registerHooks();
}
