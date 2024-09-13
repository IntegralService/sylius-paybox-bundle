<?php

/*
 * This file is part of the Blast Project package.
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IntegralService\SyliusPayboxBundle;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;
use Payum\Core\Reply\HttpPostRedirect;
use RuntimeException;

class Api
{
    static $ISO3166Alpha2ToNumeric = array(
        "AF" => 4,
        "AL" => 8,
        "DZ" => 12,
        "AD" => 20,
        "AO" => 24,
        "AG" => 28,
        "AR" => 32,
        "AM" => 51,
        "AU" => 36,
        "AT" => 40,
        "AZ" => 31,
        "BS" => 44,
        "BH" => 48,
        "BD" => 50,
        "BB" => 52,
        "BY" => 112,
        "BE" => 56,
        "BZ" => 84,
        "BJ" => 204,
        "BT" => 64,
        "BO" => 68,
        "BA" => 70,
        "BW" => 72,
        "BR" => 76,
        "BN" => 96,
        "BG" => 100,
        "BF" => 854,
        "BI" => 108,
        "CV" => 132,
        "KH" => 116,
        "CM" => 120,
        "CA" => 124,
        "CF" => 140,
        "TD" => 148,
        "CL" => 152,
        "CN" => 156,
        "CO" => 170,
        "KM" => 174,
        "CG" => 178,
        "CD" => 180,
        "CR" => 188,
        "HR" => 191,
        "CU" => 192,
        "CY" => 196,
        "CZ" => 203,
        "DK" => 208,
        "DJ" => 262,
        "DM" => 212,
        "DO" => 214,
        "EC" => 218,
        "EG" => 818,
        "SV" => 222,
        "GQ" => 226,
        "ER" => 232,
        "EE" => 233,
        "SZ" => 748,
        "ET" => 231,
        "FJ" => 242,
        "FI" => 246,
        "FR" => 250,
        "GA" => 266,
        "GM" => 270,
        "GE" => 268,
        "DE" => 276,
        "GH" => 288,
        "GR" => 300,
        "GD" => 308,
        "GT" => 320,
        "GN" => 324,
        "GW" => 624,
        "GY" => 328,
        "HT" => 332,
        "HN" => 340,
        "HU" => 348,
        "IS" => 352,
        "IN" => 356,
        "ID" => 360,
        "IR" => 364,
        "IQ" => 368,
        "IE" => 372,
        "IL" => 376,
        "IT" => 380,
        "JM" => 388,
        "JP" => 392,
        "JO" => 400,
        "KZ" => 398,
        "KE" => 404,
        "KI" => 296,
        "KP" => 408,
        "KR" => 410,
        "KW" => 414,
        "KG" => 417,
        "LA" => 418,
        "LV" => 428,
        "LB" => 422,
        "LS" => 426,
        "LR" => 430,
        "LY" => 434,
        "LI" => 438,
        "LT" => 440,
        "LU" => 442,
        "MG" => 450,
        "MW" => 454,
        "MY" => 458,
        "MV" => 462,
        "ML" => 466,
        "MT" => 470,
        "MH" => 584,
        "MR" => 478,
        "MU" => 480,
        "MX" => 484,
        "FM" => 583,
        "MD" => 498,
        "MC" => 492,
        "MN" => 496,
        "ME" => 499,
        "MA" => 504,
        "MZ" => 508,
        "MM" => 104,
        "NA" => 516,
        "NR" => 520,
        "NP" => 524,
        "NL" => 528,
        "NZ" => 554,
        "NI" => 558,
        "NE" => 562,
        "NG" => 566,
        "NO" => 578,
        "OM" => 512,
        "PK" => 586,
        "PW" => 585,
        "PA" => 591,
        "PG" => 598,
        "PY" => 600,
        "PE" => 604,
        "PH" => 608,
        "PL" => 616,
        "PT" => 620,
        "QA" => 634,
        "RO" => 642,
        "RU" => 643,
        "RW" => 646,
        "KN" => 659,
        "LC" => 662,
        "VC" => 670,
        "WS" => 882,
        "SM" => 674,
        "ST" => 678,
        "SA" => 682,
        "SN" => 686,
        "RS" => 688,
        "SC" => 690,
        "SL" => 694,
        "SG" => 702,
        "SK" => 703,
        "SI" => 705,
        "SB" => 90,
        "SO" => 706,
        "ZA" => 710,
        "SS" => 728,
        "ES" => 724,
        "LK" => 144,
        "SD" => 729,
        "SR" => 740,
        "SE" => 752,
        "CH" => 756,
        "SY" => 760,
        "TJ" => 762,
        "TZ" => 834,
        "TH" => 764,
        "TL" => 626,
        "TG" => 768,
        "TO" => 776,
        "TT" => 780,
        "TN" => 788,
        "TR" => 792,
        "TM" => 795,
        "TV" => 798,
        "UG" => 800,
        "UA" => 804,
        "AE" => 784,
        "GB" => 826,
        "US" => 840,
        "UY" => 858,
        "UZ" => 860,
        "VU" => 548,
        "VE" => 862,
        "VN" => 704,
        "YE" => 887,
        "ZM" => 894,
        "ZW" => 716
    );

    static $phonePrefixes = array(
        "AF" => 93,
        "AL" => 355,
        "DZ" => 213,
        "AD" => 376,
        "AO" => 244,
        "AG" => 1268,
        "AR" => 54,
        "AM" => 374,
        "AU" => 61,
        "AT" => 43,
        "AZ" => 994,
        "BS" => 1242,
        "BH" => 973,
        "BD" => 880,
        "BB" => 1246,
        "BY" => 375,
        "BE" => 32,
        "BZ" => 501,
        "BJ" => 229,
        "BT" => 975,
        "BO" => 591,
        "BA" => 387,
        "BW" => 267,
        "BR" => 55,
        "BN" => 673,
        "BG" => 359,
        "BF" => 226,
        "BI" => 257,
        "CV" => 238,
        "KH" => 855,
        "CM" => 237,
        "CA" => 1,
        "CF" => 236,
        "TD" => 235,
        "CL" => 56,
        "CN" => 86,
        "CO" => 57,
        "KM" => 269,
        "CG" => 242,
        "CD" => 243,
        "CR" => 506,
        "HR" => 385,
        "CU" => 53,
        "CY" => 357,
        "CZ" => 420,
        "DK" => 45,
        "DJ" => 253,
        "DM" => 1767,
        "DO" => 1809,
        "EC" => 593,
        "EG" => 20,
        "SV" => 503,
        "GQ" => 240,
        "ER" => 291,
        "EE" => 372,
        "SZ" => 268,
        "ET" => 251,
        "FJ" => 679,
        "FI" => 358,
        "FR" => 33,
        "GA" => 241,
        "GM" => 220,
        "GE" => 995,
        "DE" => 49,
        "GH" => 233,
        "GR" => 30,
        "GD" => 1473,
        "GT" => 502,
        "GN" => 224,
        "GW" => 245,
        "GY" => 592,
        "HT" => 509,
        "HN" => 504,
        "HU" => 36,
        "IS" => 354,
        "IN" => 91,
        "ID" => 62,
        "IR" => 98,
        "IQ" => 964,
        "IE" => 353,
        "IL" => 972,
        "IT" => 39,
        "JM" => 1876,
        "JP" => 81,
        "JO" => 962,
        "KZ" => 7,
        "KE" => 254,
        "KI" => 686,
        "KP" => 850,
        "KR" => 82,
        "KW" => 965,
        "KG" => 996,
        "LA" => 856,
        "LV" => 371,
        "LB" => 961,
        "LS" => 266,
        "LR" => 231,
        "LY" => 218,
        "LI" => 423,
        "LT" => 370,
        "LU" => 352,
        "MG" => 261,
        "MW" => 265,
        "MY" => 60,
        "MV" => 960,
        "ML" => 223,
        "MT" => 356,
        "MH" => 692,
        "MR" => 222,
        "MU" => 230,
        "MX" => 52,
        "FM" => 691,
        "MD" => 373,
        "MC" => 377,
        "MN" => 976,
        "ME" => 382,
        "MA" => 212,
        "MZ" => 258,
        "MM" => 95,
        "NA" => 264,
        "NR" => 674,
        "NP" => 977,
        "NL" => 31,
        "NZ" => 64,
        "NI" => 505,
        "NE" => 227,
        "NG" => 234,
        "NO" => 47,
        "OM" => 968,
        "PK" => 92,
        "PW" => 680,
        "PA" => 507,
        "PG" => 675,
        "PY" => 595,
        "PE" => 51,
        "PH" => 63,
        "PL" => 48,
        "PT" => 351,
        "QA" => 974,
        "RO" => 40,
        "RU" => 7,
        "RW" => 250,
        "KN" => 1869,
        "LC" => 1758,
        "VC" => 1784,
        "WS" => 685,
        "SM" => 378,
        "ST" => 239,
        "SA" => 966,
        "SN" => 221,
        "RS" => 381,
        "SC" => 248,
        "SL" => 232,
        "SG" => 65,
        "SK" => 421,
        "SI" => 386,
        "SB" => 677,
        "SO" => 252,
        "ZA" => 27,
        "SS" => 211,
        "ES" => 34,
        "LK" => 94,
        "SD" => 249,
        "SR" => 597,
        "SE" => 46,
        "CH" => 41,
        "SY" => 963,
        "TJ" => 992,
        "TZ" => 255,
        "TH" => 66,
        "TL" => 670,
        "TG" => 228,
        "TO" => 676,
        "TT" => 1868,
        "TN" => 216,
        "TR" => 90,
        "TM" => 993,
        "TV" => 688,
        "UG" => 256,
        "UA" => 380,
        "AE" => 971,
        "GB" => 44,
        "US" => 1,
        "UY" => 598,
        "UZ" => 998,
        "VU" => 678,
        "VE" => 58,
        "VN" => 84,
        "YE" => 967,
        "ZM" => 260,
        "ZW" => 263
    );


    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest($method, array $fields)
    {
        $headers = [];

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    function getISO3166Numeric($alpha2Code) {
        if (isset(self::$ISO3166Alpha2ToNumeric[$alpha2Code])) {
            return self::$ISO3166Alpha2ToNumeric[$alpha2Code];
        } else {
            return 250;
        }
    }

    function getCountryCodeMobilePhone($alpha2Code) {
        if (isset(self::$phonePrefixes[$alpha2Code])) {
            return '+' . self::$phonePrefixes[$alpha2Code];
        } else {
            return '+' . 33;
        }
    }

    public function doPayment(array $fields, $order)
    {
        $fields[PayboxParams::PBX_SITE] = $this->options['site'];
        $fields[PayboxParams::PBX_RANG] = $this->options['rang'];
        $fields[PayboxParams::PBX_IDENTIFIANT] = $this->options['identifiant'];
        $fields[PayboxParams::PBX_HASH] = $this->options['hash'];
        $fields[PayboxParams::PBX_SOURCE] = $this->isMobileBrowser() ? PayboxParams::PBX_SOURCE_MOBILE : PayboxParams::PBX_SOURCE_DESKTOP;
        $fields[PayboxParams::PBX_RETOUR] = PayboxParams::PBX_RETOUR_VALUE;
        $fields[PayboxParams::PBX_TIME] = date('c');

        $billingXml = '<?xml version="1.0" encoding="utf-8" ?>
<Billing>
  <Address>
    <FirstName>'. $order->getBillingAddress()->getFirstName() .'</FirstName>
    <LastName>'. $order->getBillingAddress()->getLastName() .'</LastName>
    <Address1>'. $order->getBillingAddress()->getStreet() .'</Address1>
    <ZipCode>'. $order->getBillingAddress()->getPostcode() .'</ZipCode>
    <City>'. $order->getBillingAddress()->getCity() .'</City>
    <CountryCode>'. $this->getISO3166Numeric($order->getBillingAddress()->getCountryCode()) .'</CountryCode>
    <MobilePhone>'. $order->getBillingAddress()->getPhoneNumber() .'</MobilePhone>
    <CountryCodeMobilePhone>'. $this->getCountryCodeMobilePhone($order->getBillingAddress()->getCountryCode()) .'</CountryCodeMobilePhone>
  </Address>
</Billing>';

        $fields[PayboxParams::PBX_BILLING] =  str_replace("\n","",$billingXml);

        $quantity = 0;

        foreach($order->getItems() as $item) {
            $quantity += $item->getQuantity();
        }

        $shoppingCartXml =  '<?xml version="1.0" encoding="utf-8" ?>
<shoppingcart>
  <total>
    <totalQuantity>'. $quantity .'</totalQuantity>
  </total>
</shoppingcart>';

        $fields[PayboxParams::PBX_SHOPPINGCART] =  str_replace("\n","", $shoppingCartXml);
        $fields[PayboxParams::PBX_HMAC] = $this->computeHmac($this->options['hmac'], $fields);
        $authorizeTokenUrl = $this->getApiEndpoint();
        throw new HttpPostRedirect($authorizeTokenUrl, $fields);
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        $servers = $this->options['sandbox'] ? PayboxParams::SERVERS_PREPROD : PayboxParams::SERVERS_PROD;

        //TODO: add choice for paybox payment page (iframe, mobile or classic)
        $endpoint = PayboxParams::URL_CLASSIC;

        // Test if paybox server is available
        // otherwise, use fallback url
        foreach ($servers as $server) {
            $doc = new \DOMDocument();
            $doc->loadHTMLFile('https://' . $server . '/load.html');
            $element = $doc->getElementById('server_status');
            if ($element && 'OK' == $element->textContent) {
                return 'https://' . $server . '/' . $endpoint;
            }
        }
        throw new RuntimeException('No server available.');
    }

    /**
     * @param $hmac string hmac key
     * @param $fields array fields
     *
     * @return string
     */
    private function computeHmac($hmac, $fields)
    {
        // Si la clé est en ASCII, On la transforme en binaire
        $binKey = pack('H*', $hmac);
        $msg = $this->stringify($fields);

        return strtoupper(hash_hmac($fields[PayboxParams::PBX_HASH], $msg, $binKey));
    }

    /**
     * Makes an array of parameters become a querystring like string.
     *
     * @param array $array
     *
     * @return string
     */
    private function stringify(array $array)
    {
        $result = array();
        foreach ($array as $key => $value) {
            $result[] = sprintf('%s=%s', $key, str_replace("\n","",$value));
        }

        return implode('&', $result);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    private function isMobileBrowser()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];

        return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4));
    }
}
