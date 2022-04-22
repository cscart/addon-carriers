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

/**
 * The "update_shipment_before_send_notification" hook handler.
 *
 * Actions performed:
 *     - Adds tracking url to the custom carrier info to be displayed in the new shipment notification
 *
 * @param array $shipment_data      Shipment data
 * @param int   $shipment_id        Shipment identifier
 * @param int   $group_key          Cart products group key
 * @param bool  $all_products       Whether to use all products to create the new shipment
 * @param array $force_notification Array with notification rules
 * @param array $order_info         Shipment order information
 * @param array $shipment           Notification shipment data
 * 
 * @see fn_update_shipment()
 *
 * @return void
 */
function fn_custom_carriers_update_shipment_before_send_notification($shipment_data, $shipment_id, $group_key, $all_products, $force_notification, $order_info, &$shipment)
{
    if (!empty($shipment_data['carrier']) && $shipment['carrier_info']['tracking_url'] === "") {
        $tracking_url = db_get_field('SELECT tracking_url FROM ?:shipping_services WHERE module = ?s', $shipment_data['carrier']);
        $shipment['carrier_info']['tracking_url'] = sprintf($tracking_url, $shipment_data['tracking_number']);
    }
}

/**
 * The "get_shipments_info_post" hook handler.
 *
 * Actions performed:
 *     - Adds tracking url to the custom carrier info to be displayed in the "order summary" document
 *
 * @param array $shipments Array of shipments
 * @param array $params    Shipments search params
 * 
 * @see fn_get_shipments_info()
 *
 * @return void
 */
function fn_custom_carriers_get_shipments_info_post(&$shipments, $params)
{
    foreach ($shipments as $id => $shipment) {
        if (!empty($shipment['carrier']) && $shipment['carrier_info']['tracking_url'] === "") {
            $tracking_url = db_get_field('SELECT tracking_url FROM ?:shipping_services WHERE module = ?s', $shipment['carrier']);
            $shipments[$id]['carrier_info']['tracking_url'] = sprintf($tracking_url, $shipment['tracking_number']);
        }
    }
}