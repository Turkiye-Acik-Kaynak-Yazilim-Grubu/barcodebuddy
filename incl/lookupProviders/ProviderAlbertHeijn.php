<?php

/**
 * Barcode Buddy for Grocy
 *
 * PHP version 7
 *
 * LICENSE: This source file is subject to version 3.0 of the GNU General
 * Public License v3.0 that is attached to this project.
 *
 * @author     Marc Ole Bulling
 * @copyright  2019 Marc Ole Bulling
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html  GNU GPL v3.0
 * @since      File available since Release 1.6
 */


require_once __DIR__ . "/../api.inc.php";

class ProviderAlbertHeijn extends LookupProvider {


    const USER_AGENT = "Appie/8.8.2 Model/phone Android/7.0-API24";

    function __construct(string $apiKey = null) {
        parent::__construct($apiKey);
        $this->providerName       = "Albert Heijn";
        $this->providerConfigKey  = "LOOKUP_USE_AH";
        $this->ignoredResultCodes = array("404");
    }

    /**
     * Looks up a barcode
     * @param string $barcode The barcode to lookup
     * @return array|null Name of product, null if none found
     */
    public function lookupBarcode(string $barcode): ?array {
        if (!$this->isProviderEnabled())
            return null;
        $authkey = $this->getAuthToken();
        if ($authkey == null)
            return null;

        $headers = array('Host' => 'api.ah.nl', 'Authorization' => 'Bearer ' . $authkey);
        $url     = "https://api.ah.nl/mobile-services/product/search/v1/gtin/" . $barcode;
        $result  = $this->execute($url, METHOD_GET, null, self::USER_AGENT, $headers);
        if (isset($result["title"]))
            return self::createReturnArray(sanitizeString($result["title"]));
        else
            return null;
    }

    private function getAuthToken(): ?string {
        $headers         = array('Host' => 'api.ah.nl',
                                'x-application' => 'AHWEBSHOP',
                                'x-dynatrace' => 'MT_3_4_772337796_1_fae7f753-3422-4a18-83c1-b8e8d21caace_0_1589_109');
        $json            = '{"clientId": "appie"}';
        $url             = "https://api.ah.nl/mobile-auth/v1/auth/token/anonymous";
        $authkeyResponse = $this->execute($url, METHOD_POST, null, self::USER_AGENT, $headers, true, $json);
    
        if (!isset($authkeyResponse["access_token"]))
            return null;
        return sanitizeString($authkeyResponse["access_token"]);
    }
}
