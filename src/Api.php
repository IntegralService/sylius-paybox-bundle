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
        $iso3166Alpha2ToNumeric = array(
            "FR" => 250, // France
            "CH" => 756, // Suisse
            "BE" => 056, // Belgique
        );
        if (isset($iso3166Alpha2ToNumeric[$alpha2Code])) {
            return $iso3166Alpha2ToNumeric[$alpha2Code];
        } else {
            return 250;
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
