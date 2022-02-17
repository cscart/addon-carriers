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

use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\YesNo;
use Tygh\Registry;
use Tygh\Settings;

defined('BOOTSTRAP') or die('Access denied');

/**
 * @var string $mode
 */

if (
    $mode === 'update'
    && $_SERVER['REQUEST_METHOD'] === 'POST'
    && $_REQUEST['addon'] === 'custom_carriers'
) {
    $new_carriers = [];
    for ($i = 1; $i <= 3; $i++) {
        $str_i = (string) $i;
        $carrier_name = Settings::instance()->getValue('carrier_name_' . $str_i, '');
        if (!empty($carrier_name)) {
            $new_carriers[$carrier_name]['name'] = $carrier_name;
            $tracking_url = Settings::instance()->getValue('carrier_url_' . $str_i, '');
            $new_carriers[$carrier_name]['tracking_url'] = $tracking_url;

        }
    }

    $old_carriers = fn_custom_carriers_get_carriers();

    foreach ($old_carriers as $carrier_name => $carrier_info) {
        if (!array_key_exists($carrier_name, $new_carriers)) {
            db_query('DELETE FROM ?:shipping_services WHERE module = ?s AND is_custom = ?s', $carrier_name, YesNo::YES);
        }
    }

    foreach ($new_carriers as $carrier_name => $carrier_info) {
        $tracking_url = (isset($carrier_info['tracking_url'])) ? $carrier_info['tracking_url'] : '';
        if (!array_key_exists($carrier_name, $old_carriers)) {
            $carrier_data = [
                'status' => ObjectStatuses::ACTIVE,
                'module' => $carrier_name,
                'code' => 'default',
                'is_custom' => YesNo::YES,
                'tracking_url' => $tracking_url
            ];
            db_query('INSERT INTO ?:shipping_services ?e', $carrier_data);
        }
        $old_tracking_url = (isset($old_carriers[$carrier_name]['tracking_url'])) ? $old_carriers[$carrier_name]['tracking_url'] : '';
        if ($old_tracking_url !== $tracking_url) {
            $carrier_data = [
                'tracking_url' => $tracking_url
            ];
            db_query('UPDATE ?:shipping_services SET ?u WHERE module = ?s', $carrier_data, $carrier_name);
        }
    }
}
