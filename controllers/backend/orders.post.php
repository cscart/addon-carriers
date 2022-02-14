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

defined('BOOTSTRAP') or die('Access denied');

/**
 * @var string $mode
 */

if ($mode === 'details') {
    /** @var array<string, array<string, string>> $carriers **/
    $carriers = Tygh::$app['view']->getTemplateVars('carriers');
    $custom_carriers = fn_custom_carriers_get_carriers();
    foreach ($custom_carriers as $carrier_name => $carrier_info) {
        $carriers[$carrier_name] = $carrier_info;
    }
    Tygh::$app['view']->assign('carriers', $carriers);

    $shipments = Tygh::$app['view']->getTemplateVars('shipments');
    if (is_array($shipments)) {
        foreach ($shipments as $index => $shipment_info) {
            $carrier_name = $shipment_info['carrier'];
            $shipments[$index]['carrier_info']['name'] = $carrier_name;

            if (array_key_exists($carrier_name, $custom_carriers)) {
                $carrier_url = $custom_carriers[$carrier_name]['tracking_url'];
                $tracking_number = $shipment_info['tracking_number'];
                if (!empty($carrier_url)) {
                    $tracking_url = sprintf($carrier_url, $tracking_number);
                    $shipments[$index]['carrier_info']['tracking_url'] = $tracking_url;
                }
            }
        }
        Tygh::$app['view']->assign('shipments', $shipments);
    }
}
