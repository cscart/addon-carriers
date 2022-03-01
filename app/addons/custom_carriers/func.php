<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

use Tygh\Enum\YesNo;

defined('BOOTSTRAP') or die('Access denied');

/**
 * Displays information about the module on the module settings page
 *
 * @return string
 */
function fn_custom_carriers_info()
{
    $text = __('custom_carriers.general_info');

    return $text . '<hr/>' ;
}

/**
 * Get an array of custom carriers in the same form as Shippings::getCarriers()
 *
 * @param bool $is_custom For select custom or not custom carriers
 *
 * @return array<string, array<string, string>> $normalize_carriers Normalize custom carriers
 */
function fn_custom_carriers_get_carriers($is_custom = true)
{
    $carriers = db_get_array('SELECT module, tracking_url FROM ?:shipping_services WHERE is_custom = ?s', YesNo::toId($is_custom));
    $normalize_carriers = [];

    foreach ($carriers as $carrier_info) {
        $carrier_name = $carrier_info['module'];
        $carrier_url = $carrier_info['tracking_url'];
        $normalize_carriers[$carrier_name]['name'] = $carrier_name;
        $normalize_carriers[$carrier_name]['tracking_url'] = $carrier_url;
    }

    return $normalize_carriers;
}
