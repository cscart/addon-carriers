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

use Tygh\Shippings\Shippings;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_custom_carriers_info()
{
    $text = __('general_info');

    return $text . '<hr/>' ;
}

function fn_custom_carriers_get_shipments_info_post(&$shipments, &$params) {
    $carriers = Shippings::getCarriers();

    $custom_carriers = [];
    for ($i = 1; $i <= 3; $i++) {
        $str_i = (string) $i;
        $carrier_name = Registry::get('addons.custom_carriers.carrier_name_' . $str_i);
        if (!empty($carrier_name)) {
            $custom_carriers[$carrier_name]['name'] = $carrier_name;
            $tracking_url = Registry::get('addons.custom_carriers.carrier_url_' . $str_i);
            $custom_carriers[$carrier_name]['tracking_url'] = $tracking_url;
        }
    }

    foreach ($custom_carriers as $carrier_name => $carrier_info)
    {
        if (!array_key_exists($carrier_name, $carriers)) {
            $carrier_data = [
                'status' => 'A',
                'module' => $carrier_name,
                'code' => 'default',
                'is_custom' => '1',
                'tracking_url' => $carrier_info['tracking_url']
            ];
            db_query("INSERT INTO ?:shipping_services ?e", $carrier_data);
            $carriers[$carrier_name]['name'] = $carrier_name;
        }
        if (empty($carriers[$carrier_name]['tracking_url']) ||
            $carriers[$carrier_name]['tracking_url'] !== $carrier_info['tracking_url'])
        {
            $carrier_data = [
                'tracking_url' => $carrier_info['tracking_url']
            ];
            db_query('UPDATE ?:shipping_services SET ?u WHERE module = ?s', $carrier_data, $carrier_name);
            $carriers[$carrier_name]['tracking_url'] = $carrier_info['tracking_url'];
        }

    }


    // найти старые кариеры
    // если is_custom = 1 и нет в массиве $custom_carriers
    // значит старый - удалить из БД и из массива

    if (!empty($custom_carriers)) {
        $shipments['custom_carriers'] = $carriers;
    }
}

//db_query("INSERT INTO ?:shipping_services (status, module, code, is_custom, tracking_url)
//                    VALUES ('A', ?s, 'default', '1', ?s)", $carrier_name, $carrier_info['tracking_url']);
//db_query('UPDATE ?:products SET ?u WHERE product_id = ?i', $data, $product_id);
/*
 * SecurityHelper::sanitizeObjectData('product', '');
            $product_info = db_get_row('SELECT company_id, shipping_params, qty_step, status FROM ?:products WHERE product_id = ?i', $product_id);
            $product_info = fn_normalize_product_overridable_fields($product_info);
 */