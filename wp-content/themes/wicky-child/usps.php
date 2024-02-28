<?php
   function _get_usps_address($address){
        $billing_address_1 = $address['billing_address_1'];
        $billing_address_2 = $address['billing_address_2'];
        $billing_state = $address['billing_state'];
        $billing_city = $address['billing_city'];
        $billing_postcode = $address['billing_postcode'];
       
        $userId = '819TESTI3249';
        $input_xml = "<AddressValidateRequest  USERID=\"$userId\">
        <Revision>1</Revision>

        <Address ID=\"0\">

        <Address1>$billing_address_1</Address1>

        <Address2>$billing_address_2</Address2>

        <City>$billing_city</City>

        <State>$billing_state</State>

        <Zip5>$billing_postcode</Zip5>

        <Zip4/>

        </Address>
        </AddressValidateRequest >";
        
        $fields = array(
            'API' => 'Verify',
            'XML' => $input_xml
        );
        $url = 'https://secure.shippingapis.com/ShippingAPI.dll?' . http_build_query($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10000);
        $data = curl_exec($ch);
        curl_close($ch);

        $array_data = json_decode(json_encode(simplexml_load_string($data)), true);

        return $array_data;
    }
