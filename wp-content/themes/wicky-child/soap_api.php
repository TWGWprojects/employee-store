<?php

global $username, $password, $api_base_url, $dev;
$username = get_option('_api_base_username');
$password = get_option('_api_base_password');
$api_base_url = get_option('_api_base_url','');
$ship_compliance_url = get_option('_ship_compliance_url','');

$dev = ' (Dev server)';
if(strpos($api_base_url, '10.254.171.78')){
    $username = get_option('_api_base_username','wpstores');
    $password = get_option('_api_base_password','tracyripon');
    $dev ='';
}

function stock_api_curl($sku, $unit_of_measure){
    global $username, $password, $api_base_url, $dev;

    $curl = curl_init();
    $xmlData='<?xml version = "1.0" encoding = "UTF-8"?>'.
    '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:ns2="http://oracle.e1.bssv.JP554121/">'.
    "<env:Header><ns1:Security><ns1:UsernameToken><ns1:Username>".$username."</ns1:Username><ns1:Password>".$password."</ns1:Password></ns1:UsernameToken></ns1:Security>".
    "</env:Header><env:Body>".
    "<ns2:getItemAvailability>".
    "<businessUnit>03</businessUnit>".
    "<itemNumber>$sku</itemNumber>".
    "<unitofMeasure>$unit_of_measure</unitofMeasure>".
    '</ns2:getItemAvailability></env:Body></env:Envelope>';
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER ,false);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST ,false);
    curl_setopt_array($curl, array(
    CURLOPT_URL => $api_base_url."/GetItemAvailabilityManager",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 3000,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>$xmlData ,
    CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: text/xml; charset=UTF-8",
        "SOAPAction:",
        "Content-length: ".strlen($xmlData),
        "x-httpanalyzer-rules: 1@localhost:8099",
    ),
    ));

    $response = curl_exec($curl);
    //custom-logs
    file_put_contents('../../../logs/products/stock-updates.txt', 'Date:-'.date("d/m/Y h:i:s").PHP_EOL.$xmlData.PHP_EOL.PHP_EOL.PHP_EOL.'Response:-'.PHP_EOL.$response.PHP_EOL.PHP_EOL.'API URL:-'.$api_base_url."/GetItemAvailabilityManager".PHP_EOL."==============".PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL , FILE_APPEND | LOCK_EX); 

    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
    echo "cURL Error #:" . $err;
    } else {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($response);
        $carriers=array();
        $node = $domDocument->getElementsByTagName("quantityAvailable");
        $itemNumber = $domDocument->getElementsByTagName("itemNumber");
        if($node->length > 0){
            $results = $node[0]->nodeValue;
        }else{
            // If no quantityAvailable
            $email_send_status = get_option('_inventory_email_status','');
            if($email_send_status == ''){
                $to = explode(',', get_option('_product_inventory_email',''));
                $subject = 'Error in Stock update API '.$dev;
                $body = 'Please check the error response : - <br>';
                 if ($response) {
                $body .= $response ;
                }

            // Check if there is node for quantityAvailable
               $error_message = 'quantityAvailable is not found for ' . $itemNumber[0]->nodevalue;
               $body .= $error_message;

               $headers = array('Content-Type: text/html; charset=UTF-8');
                if(count($to)>0)
                    wp_mail( $to, $subject, $body, $headers );
                update_option('_inventory_email_status','Yes');
            }

        $results = '';
        }
        return $results;
    }
    //
}



function check_obsolete_api($sku){

    global $username, $password, $api_base_url;
   
    $curl = curl_init();
    $xmlData = '<?xml version = "1.0" encoding = "UTF-8"?>
        <env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:ns2="http://oracle.e1.bssv.JP410000/">
        <env:Header>
            <ns1:Security>
                <ns1:UsernameToken>
                    <ns1:Username>'.$username.'</ns1:Username>
                    <ns1:Password>'.$password.'</ns1:Password>
                </ns1:UsernameToken>
            </ns1:Security>
        </env:Header>
        <env:Body>
            <ns2:getBranchPlantItem>
                <branchPlant>03</branchPlant>
                <item>
                    <itemProduct>'.$sku.'</itemProduct>
                </item>
            </ns2:getBranchPlantItem>
        </env:Body>
        </env:Envelope>';
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER ,false);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST ,false);
    curl_setopt_array($curl, array(
    CURLOPT_URL => $api_base_url."/InventoryManager?wsdl",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 1000,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>$xmlData ,
    CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: text/xml; charset=UTF-8",
        "SOAPAction:",
        "Content-length: ".strlen($xmlData),
        "x-httpanalyzer-rules: 1@localhost:8099",
    ),
    ));

    $response = curl_exec($curl);

    
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
    echo "cURL Error #:" . $err;
    } else {

        $status = false;
        $doc = new DOMDocument();
        $doc->loadXML($response);
        $parent = $doc->getElementsByTagName('itemBranch')->item(0);
        foreach($parent->childNodes as $nodename)
        {
            if($nodename->nodeName == 'stockingTypeCode' && $nodename->nodeValue == 'O'){
                $status = true;
            }
        }
        return $status;
    }
}

function order_api_curl($products, $shipment){
    
    global $username, $password, $api_base_url, $dev;

    if(!is_null($user2) && !is_null($pass2)){
        $username = $user2;
        $password = $pass2;
    }

    $billing_state = $shipment['billing_state'];

    $state_code = get_state_short($billing_state);

    //var_dump($billing_state);
    $shipment['billing_state'] = $state_code;

    $current_user = wp_get_current_user();
    $processV = ($shipment['billing_country'] == 'US')?'TWG420023' : 'TWG420025';
    $curl = curl_init();
    $xmlData='<?xml version = "1.0" encoding = "UTF-8"?>'.
    '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:ns2="http://oracle.e1.bssv.JP420000/">'.
    "<env:Header><ns1:Security><ns1:UsernameToken><ns1:Username>".$username."</ns1:Username><ns1:Password>".$password."</ns1:Password></ns1:UsernameToken></ns1:Security>".
    "</env:Header>   <env:Body>
    <ns2:processSalesOrderV4>
    <header>
    <businessUnit>17</businessUnit>
    <customerPO>".$shipment['user_gl_number']."</customerPO>";
    foreach ($products as $key => $value) {
        $xmlData .="
        <detail>
            <businessUnit>03</businessUnit>
            <processing>
                <actionType>A</actionType>
            </processing>
            <product>
                <item>
            <itemProduct>".$value['sku']."</itemProduct>
                </item>
            </product>
            <quantityOrdered>".$value['qty']."</quantityOrdered>
            <unitOfMeasureCodeTransaction>EA</unitOfMeasureCodeTransaction>
            <reference>".$shipment['order_id']."</reference>
        </detail>";
    }
    $xmlData .= "<orderTakenBy>WEBSAMPLE</orderTakenBy>
    <orderedBy>".substr($shipment['billing_first_name'],0,8)." ".substr($shipment['billing_last_name'],0,1)."</orderedBy>
    <processing>
        <actionType>A</actionType>
        <processingVersion>".$processV."</processingVersion>
    </processing>
    <attachmentText>".$shipment['order_note']."</attachmentText>
    <dateRequested>".$shipment['order_delivery_date']."</dateRequested>
    <shipTo>
        <mailingName>".$shipment['billing_first_name']."</mailingName>
        <addressLine1>".$shipment['billing_address_1']."</addressLine1>
        <addressLine2>".$shipment['billing_address_2']."</addressLine2>
        <addressLine4>".$shipment['billing_phone']."</addressLine4>
        <city>".$shipment['billing_city']."</city>
        <countryCode>".$shipment['billing_country']."</countryCode>
        <countyCode>".$shipment['billing_country']."</countyCode>
        <customer>
            <entityId>".$shipment['user_cu_no']."</entityId>
        </customer>
        <postalCode>".$shipment['billing_postcode']."</postalCode>
        <stateCode>".$shipment['billing_state']."</stateCode>
    </shipTo>";
    if(1){
        $xmlData .= "<soldTo>
        <mailingName>".$shipment['billing_first_name']."</mailingName>
        <addressLine1>".$shipment['billing_address_1']."</addressLine1>
        <addressLine2>".$shipment['billing_address_2']."</addressLine2>
        <addressLine4>".$shipment['billing_phone']."</addressLine4>
        <city>".$shipment['billing_city']."</city>
        <countryCode>".$shipment['billing_country']."</countryCode>
        <countyCode>".$shipment['billing_country']."</countyCode>
        <customer>
            <entityId>510674</entityId>
        </customer>
        <postalCode>".$shipment['billing_postcode']."</postalCode>
        <stateCode>".$shipment['billing_state']."</stateCode>
    </soldTo >";
    }
    $xmlData .= "</header>
    </ns2:processSalesOrderV4>
    </env:Body></env:Envelope>";
    file_put_contents('apilogs.txt', $xmlData.PHP_EOL."==============".PHP_EOL);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER ,false);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST ,false);
    curl_setopt_array($curl, array(
    CURLOPT_URL => $api_base_url."/SalesOrderManager",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 300,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>$xmlData ,
    CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: text/xml; charset=UTF-8",
        "SOAPAction:",
        "Content-length: ".strlen($xmlData),
        "x-httpanalyzer-rules: 1@localhost:8099",
    ),
    ));
    //echo $xmlData; die;

    $response = curl_exec($curl);
    
    file_put_contents('logs/orders/sales-order.txt', 'Date:-'.date("d/m/Y h:i:s").PHP_EOL.$xmlData.PHP_EOL.PHP_EOL.PHP_EOL.'Response:-'.PHP_EOL.$response.PHP_EOL.'API URL:-'.$api_base_url."/SalesOrderManager".PHP_EOL."==============".PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL , FILE_APPEND | LOCK_EX); 

    $err = curl_error($curl);

    curl_close($curl);

    $result=['status' => false];
    if ($err) {
    "cURL Error #:" . $err;
    } else {
        $doc = new DOMDocument();
        $doc->loadXML($response);
        $parent = $doc->getElementsByTagName('header')->item(0);
        //echo $response;
        file_put_contents('jdelogs.txt', $xmlData.PHP_EOL."==============".PHP_EOL , FILE_APPEND | LOCK_EX);
        file_put_contents('jdelogs.txt', $response.PHP_EOL."***************".PHP_EOL , FILE_APPEND | LOCK_EX);
        if($parent){
            foreach($parent->childNodes as $nodename)
            {
                if($nodename->nodeName=='salesOrderKey'){
                    foreach($nodename->childNodes as $subNodes)
                    {
                        if($subNodes->nodeName == 'documentNumber' && $subNodes->nodeValue > 0){
                            $result['status'] = true;
                            $result['document_no'] = $subNodes->nodeValue;
                        }
                    }
                }
            }
        }else{
            $user_info = get_userdata($shipment['user_id']);
            $user_email = ($user_info) ? $user_info->user_email : '';
            $parent = $doc->getElementsByTagName('faultstring')->item(0);
            $result['error_msg'] = $parent->nodeValue;
            $to = ['pragya.shukla@thewinegroup.com'];
            if($dev=='')
                $to = ['pragya.shukla@thewinegroup.com','k.balakrishnan@thewinegroup.com',$user_email];
            $subject = 'Error in order sync for order id - '.$shipment['order_id'].$dev;
            $body = 'Please check the error response : - '.$parent->nodeValue ;
            $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail( $to, $subject, $body, $headers );
        }

    }
    return $result;
}

function get_state_short($billing_state){
    $state_arr = array(
        'HONG KONG' => 'H1',
        'KOWLOON' => 'H2',
        'NEW TERRITORIES' => 'H3'
    );
       
    if(isset($state_arr[$billing_state])){
        $state_code = $state_arr[$billing_state];
    }else{
        $state_code = $billing_state;
    }

    return $state_code;
}

function _get_ship_compliance($shipments, $address){
    
    global $dev,$ship_compliance_url;
      
    $billing_first_name = $address['billing_first_name'];
    $billing_last_name = $address['billing_last_name'];
    $billing_country = $address['billing_country'];
    $billing_address_1 = $address['billing_address_1'];
    $billing_address_2 = $address['billing_address_2'];
    $billing_state = $address['billing_state'];
    $billing_city = $address['billing_city'];
    $billing_postcode = $address['billing_postcode'];
    $billing_phone = $address['billing_phone'];
    $billing_email = $address['billing_email'];

    //Data, connection, auth
    $soapUrl = $ship_compliance_url; // asmx URL of WSDL
    $soapUser = "TWGWSsamples@thewinegroup.com";  //  username
    $soapPassword = "Twg4596!"; // password

    // xml post structure
    $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <CheckComplianceOfSalesOrder xmlns="http://ws.shipcompliant.com/">
        <Request>
        <Security>
        <Username>TWGWSsamples@thewinegroup.com</Username>
                    <Password>Twg4596!</Password>
        </Security>
            <AddressOption>
            <IgnoreStreetLevelErrors>false</IgnoreStreetLevelErrors>
            <RejectIfAddressSuggested>true</RejectIfAddressSuggested>
            </AddressOption>
            <IncludeSalesTaxRates>true</IncludeSalesTaxRates>
            <PersistOption>OverrideExisting</PersistOption>
            <SalesOrder>
            <BillTo>
                <City>'.$billing_city.'</City>
                <Company />
                <Country>'.$billing_country.'</Country>
                <County />
                <DateOfBirth>1753-01-01T00:00:00</DateOfBirth>
                <Email>'.$billing_email.'</Email>
                <Fax />
                <FirstName>'.$billing_first_name.'</FirstName>
                <LastName>'.$billing_last_name.'</LastName>
                <Phone>'.$billing_phone.'</Phone>
                <State>'.$billing_state.'</State>
                <Street1>'.$billing_address_1.'</Street1>
                <Street2>'.$billing_address_2.'</Street2>
                <Zip1>'.$billing_postcode.'</Zip1>
                <Zip2 />
            </BillTo>
            <CashierKey />
            <CustomerKey>516814</CustomerKey>

            <ExternalCustomerKey>11111</ExternalCustomerKey>
            <ExternalOfferKeys>
                <string>11111</string>
                <string>11111</string>
            </ExternalOfferKeys>
            <ExternalSalesOrderKey>11111</ExternalSalesOrderKey>
            <FulfillmentType>Null</FulfillmentType>
            <OrderType>InPerson</OrderType>

            <PurchaseDate>2020-02-22T16:42:00</PurchaseDate>
            <ReferenceNumber>11111</ReferenceNumber>
            <RefundedOrderReference>11111</RefundedOrderReference>
            <RegisterID>string</RegisterID>
            <SalesAssociateKey>11111</SalesAssociateKey>
            <SalesOrderKey>8871853</SalesOrderKey>
            <SalesTaxCollected>0.0000</SalesTaxCollected>
            <SettlementBatchNumber>11111</SettlementBatchNumber>
            <Shipments>
            <Shipment>
            <Freight>10</Freight>
            <GiftNote />
            <InsuredAmount>0</InsuredAmount>
            <LicenseRelationship>Default</LicenseRelationship>
            <Packages />
            <ShipDate>2020-02-24T09:56:33.7612532-06:00</ShipDate>
            <ShipmentItems>';
            $email_product = '';
            foreach($shipments as $shipment){
                $xml_post_string .= '<ShipmentItem>
                <BrandKey>'.$shipment['brand'].'</BrandKey>
                <ProductKey>'.$shipment['sku'].'</ProductKey>
                <ProductQuantity>'.$shipment['qty'].'</ProductQuantity>
                <ProductUnitPrice>0</ProductUnitPrice>
                </ShipmentItem>';
                $email_product .= 'Brand :'.$shipment['brand'].' / SKU :'.$shipment['sku'].' / QTY :'.$shipment['qty'].'<br>';
            }
            $xml_post_string .= '</ShipmentItems>
            <ShipmentStatus>PaymentAccepted</ShipmentStatus>
            <ShippingService>UPS</ShippingService>
            <ShipTo>
                <City>'.$billing_city.'</City>
                <Company />
                <Country>'.$billing_country.'</Country>
                <DateOfBirth>1753-01-01T00:00:00</DateOfBirth>
                <Email>'.$billing_email.'</Email>
                <FirstName>'.$billing_first_name.'</FirstName>
                <LastName>'.$billing_last_name.'</LastName>
                <Phone>'.$billing_phone.'</Phone>
                <State>'.$billing_state.'</State>
                <Street1>'.$billing_address_1.'</Street1>
                <Street2>'.$billing_address_2.'</Street2>
                <Zip1>'.$billing_postcode.'</Zip1>
            </ShipTo>
            <SpecialInstructions />
            </Shipment>
            </Shipments>

            </SalesOrder>
        </Request>
        </CheckComplianceOfSalesOrder>
    </soap:Body>
    </soap:Envelope>';   // data from the form, e.g. some ID number


            $headers = array(
                            "Content-type: text/xml;charset=\"utf-8\"",
                            "Accept: text/xml",
                            "Cache-Control: no-cache",
                            "Pragma: no-cache",
                        //~ // "stream_context: ".stream_context_create($context),
                            "UsernameToken: ABCL",
                            "SOAPAction: http://ws.shipcompliant.com/CheckComplianceOfSalesOrder",
                            "Content-length: ".strlen($xml_post_string)
                        ); //SOAPAction: your op URL

                $url = $soapUrl;

                //~ // PHP cURL  for https connection with auth
                $ch = curl_init();
                //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
                curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


    try{
    $response = curl_exec($ch);
    $err = curl_error($ch);

    curl_close($ch);

        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
           // print_r($response);
            $doc = new DOMDocument();
            $doc->loadXML($response);
            $result = array('msg'=>array());
            if($doc->getElementsByTagName('ResponseStatus')->item(0)->nodeValue == 'Success'){
                $result['status'] = $doc->getElementsByTagName('IsCompliant')->item(0)->nodeValue;
                if($result['status'] == 'false'){
                    $el = $doc->getElementsByTagName('RuleDescription');
                    for ($i=0; $i < $el->length; $i++) {
                        $result['msg'][] = $el->item($i)->nodeValue;
                    }
                }
            }else{
                $result['status'] = 'false';
                $el = $doc->getElementsByTagName('Message');
                for ($i=0; $i < $el->length; $i++) {
                    $result['msg'][] = $el->item($i)->nodeValue;
                }
            }

            if($result['status'] == 'false'){
                $to = explode(',', get_option('_ship_compliance_email',''));
                 // Redirect the error email to user logged in
                $current_user_email = wp_get_current_user()->user_email;
                $to[] = $current_user_email;
                // $to[] = $address['billing_email'];
                $subject = 'Error in Ship compliance'.$dev;
                $body = 'Please check the error response : - <br>';
                $body .= implode('<br>',$result['msg']).'<br>' ;
                $body .= '<h3>Shipping details:-</h3>';
                $body .= 'First name : '.$address['billing_first_name'].'<br>';
                $body .= 'Last name : '.$address['billing_last_name'].'<br>';
                $body .= 'Country : '.$address['billing_country'].'<br>';
                $body .= 'Address 1 : '.$address['billing_address_1'].'<br>';
                $body .= 'Address 2 : '. $address['billing_address_2'].'<br>';
                $body .= 'State : '.$address['billing_state'].'<br>';
                $body .= 'City : '.$address['billing_city'].'<br>';
                $body .= 'Postal code : '.$address['billing_postcode'].'<br>';
                $body .= 'Phone : '.$address['billing_phone'].'<br>';
                $body .= 'Email : '.$address['billing_email'].'<br>';
                $body .= '<h3>Product details:-</h3>';
                $body .= $email_product;
                $headers = array('Content-Type: text/html; charset=UTF-8');

                wp_mail( $to, $subject, $body, $headers );
            }

            return $result;
        }

    }catch(\Exception $e){
        echo '===='.$e->getMessage();
    }
}

function _ship_compliance_commit($address, $shipments, $jde_no){
    //Data, connection, auth
    $soapUrl = "https://ws.shipcompliant.com/Services/1.2/SalesOrderService.asmx?op=CheckComplianceOfAndCommitSalesOrder"; // asmx URL of WSDL
    $soapUser = "TWGWSsamples@thewinegroup.com";  //  username
    $soapPassword = "Twg4596!"; // password
    // xml post structure
    $name = explode(' ', $address['billing_first_name']);
    $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
      <soap:Body>
        <CheckComplianceOfAndCommitSalesOrder xmlns="http://ws.shipcompliant.com/">
          <Request>
         <Security>
           <PartnerKey></PartnerKey>
           <Password>Twg4596!</Password>
           <Username>TWGWSsamples@thewinegroup.com</Username>
         </Security>
         <AddressOption>
           <IgnoreStreetLevelErrors>false</IgnoreStreetLevelErrors>
           <RejectIfAddressSuggested>true</RejectIfAddressSuggested>
         </AddressOption>
         <CommitOption>AllShipments</CommitOption>
         <PersistOption>OverrideExisting</PersistOption>
         <SalesOrder>
           <BillTo>
             <City>'.$address['billing_city'].'</City>
             <Company />
             <Country>'.$address['billing_country'].'</Country>
             <DateOfBirth>1987-09-09T00:00:00</DateOfBirth>
             <Email>'.$address['billing_email'].'</Email>
             <FirstName>'.$name[0].'</FirstName>
             <LastName>'.(isset($name[1]) ? $name[1]: '').'</LastName>
             <Phone>'.$address['billing_phone'].'</Phone>
             <State>'.$address['billing_state'].'</State>
             <Street1>'.$address['billing_address_1'].'</Street1>
             <Street2>'.$address['billing_address_2'].'</Street2>
             <Zip1>'.$address['billing_postcode'].'</Zip1>
           </BillTo>
           <CustomerKey>'.$address['user_id'].'</CustomerKey>
           <FulfillmentType>Null</FulfillmentType>
           <OrderType>Internet</OrderType>
           <PurchaseDate>'.$address['billing_date'].'</PurchaseDate>
           <SalesOrderKey>'.$jde_no.'</SalesOrderKey>
           <SalesTaxCollected>0</SalesTaxCollected>
           <Shipments>
             <Shipment>
               <Freight>10</Freight>
               <GiftNote />
               <InsuredAmount>0</InsuredAmount>
               <LicenseRelationship>Default</LicenseRelationship>
               <Packages />
               <ShipDate>'.$address['order_delivery_date'].'</ShipDate>
               <ShipmentItems>';
               foreach($shipments as $shipment){
                    $xml_post_string .= '<ShipmentItem>
                    <BrandKey></BrandKey>
                    <ProductKey>'.$shipment['sku'].'</ProductKey>
                    <ProductQuantity>'.$shipment['qty'].'</ProductQuantity>
                    <ProductUnitPrice>'.$shipment['price'].'</ProductUnitPrice>
                    </ShipmentItem>';
                }
            $xml_post_string .='
               </ShipmentItems>
               <ShipmentStatus>PaymentAccepted</ShipmentStatus>
               <ShippingService>CST1</ShippingService>
               <ShipTo>
                 <City>'.$address['billing_city'].'</City>
                 <Company />
                 <Country>'.$address['billing_country'].'</Country>
                 <DateOfBirth>1979-09-09T00:00:00</DateOfBirth>
                 <Email>'.$address['billing_email'].'</Email>
                 <FirstName>'.$name[0].'</FirstName>
                 <LastName>'.(isset($name[1]) ? $name[1]: '').'</LastName>
                 <Phone>'.$address['billing_phone'].'</Phone>
                 <State>'.$address['billing_state'].'</State>
                 <Street1>'.$address['billing_address_1'].'</Street1>
                 <Street2>'.$address['billing_address_2'].'</Street2>
                 <Zip1>'.$address['billing_postcode'].'</Zip1>
               </ShipTo>
               <SpecialInstructions />
             </Shipment>
           </Shipments>
           <Tags />
         </SalesOrder>
       </Request>
        </CheckComplianceOfAndCommitSalesOrder>
      </soap:Body>
    </soap:Envelope>';


    $headers = array(
        "Content-type: text/xml;charset=\"utf-8\"",
        "Accept: text/xml",
        "Cache-Control: no-cache",
        "Pragma: no-cache",
    //~ // "stream_context: ".stream_context_create($context),
        "UsernameToken: ABCL",
        "SOAPAction: http://ws.shipcompliant.com/CheckComplianceOfAndCommitSalesOrder",
        "Content-length: ".strlen($xml_post_string)
    ); //SOAPAction: your op URL

    $url = $soapUrl;

    //~ // PHP cURL  for https connection with auth
    $ch = curl_init();
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


    try{
        $response = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
            $doc = new DOMDocument();
            $doc->loadXML($response);
            if($doc->getElementsByTagName('ResponseStatus')->item(0)->nodeValue == 'Failure'){
                $parent = $doc->getElementsByTagName('Error');
                $msg = array();
                foreach($parent as $result)
                {
                    foreach($result->childNodes as $childnode){
                        if( $childnode->nodeName == 'Message'){
                            $msg[] = trim($childnode->nodeValue);
                        }
                    }
                }

                $to = explode(',', get_option('_ship_compliance_email',''));
                $subject = 'Error in Ship compliance commit'.$dev;
                $body = 'Please check the error response : - <br>';
                $body .= implode('<br>',$msg).'<br>' ;
                $headers = array('Content-Type: text/html; charset=UTF-8');

                wp_mail( $to, $subject, $body, $headers );
            }
            if($doc->getElementsByTagName('ResponseStatus')->item(0)->nodeValue == 'Success'){

            }
        }
    }catch(\Exception $e){
        echo '===='.$e->getMessage();
    }
}

function _add_update_product(){
    global $username, $password, $api_base_url;
    $curl = curl_init();
    $xmlData='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:orac="http://oracle.e1.bssv.JP5541T1/">
    <soapenv:Header>
    <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">
      <wsse:UsernameToken>
         <wsse:Username>'.$username.'</wsse:Username>
         <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$password.'</wsse:Password>
      </wsse:UsernameToken>
    </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
    <orac:getItemTagInfo>
      <!--Optional:-->
      <businessUnit>03</businessUnit>
    </orac:getItemTagInfo>
    </soapenv:Body>
    </soapenv:Envelope>';
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER ,false);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST ,false);
    curl_setopt_array($curl, array(
      CURLOPT_URL => $api_base_url."/ItemTagManager?WSDL",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS =>$xmlData ,
      CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: text/xml; charset=UTF-8",
        "SOAPAction:",
        "Content-length: ".strlen($xmlData),
        "x-httpanalyzer-rules: 1@localhost:8099",
      ),
    )); 

    
    $response = curl_exec($curl);
     // custom log
     file_put_contents('../../../logs/products/products-add.txt', 'Date:-'.date("d/m/Y h:i:s").PHP_EOL.$xmlData.PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL.'Response:-'.PHP_EOL.$response.PHP_EOL.PHP_EOL.'API URL:-'.$api_base_url."/ItemTagManager".PHP_EOL."==============".PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL , FILE_APPEND | LOCK_EX); 
    
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
    } else {
     // echo $response;
      $doc = new DOMDocument();
        $doc->loadXML($response);
        $parent = $doc->getElementsByTagName('result');
        //echo $response;
        $products=[];
        if($parent){
            foreach($parent as $result)
            {
                $product=[];
                foreach($result->childNodes as $childnode){
                    switch ($childnode->nodeName) {
                        case "identifier2ndItem":
                            $product['SKU'] = trim($childnode->nodeValue);
                            break;
                        case "descriptionLine1":
                            $product['name'] = trim($childnode->nodeValue);
                            $product['description'] = trim($childnode->nodeValue);
                            break;
                        case "brandDesc":
                            $product['brand'] = trim($childnode->nodeValue);
                            break;
                        case "catCode10Desc":
                            $product['master_code'] = trim($childnode->nodeValue);
                            break;
                        case "catCode7Desc":
                            $product['pack_description'] = trim($childnode->nodeValue);
                            break;
                        case "varietalOrFlavorDesc":
                            $product['varietal_flavor_description'] = trim($childnode->nodeValue);
                            break;
                        case "tierDesc":
                            $product['tier_description'] = trim($childnode->nodeValue);
                            break;
                        case "sizeDesc":
                            $product['size_description'] = trim($childnode->nodeValue);
                            break;
                        case "vintageDesc":
                            $product['vintage'] = trim($childnode->nodeValue);
                            break;
                        case "catCode6Desc":
                            $product['appellation'] = trim($childnode->nodeValue);
                            break;
                        case "stockingType":
                            $product['stockingtype'] = trim($childnode->nodeValue);
                            break;
                        case "unitOfMeasurePricing":
                            $product['unit_of_measure'] = 'EA';//trim($childnode->nodeValue);
                            break;
                        case "categoryCode0Apparel":
                            $product['package_format'] = trim($childnode->nodeValue);
                            break;
                        case "commClassDesc":
                            $product['export'] = trim($childnode->nodeValue);
                            break;
                        case "SCCDesc":
                            $product['sccdesc'] = trim($childnode->nodeValue);
                            break;
                    }
                }
                $products[] = $product;
            }
        }
        //echo '<pre>';
        //print_r($products);
        return $products;
    }

}

function _order_shipped($order_id, $jde_no){
    global $username, $password, $api_base_url;

    $date = date("Y-m-d");
    $xmlData = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:orac="http://oracle.e1.bssv.JP554211/">
    <soapenv:Header>
    <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsse:UsernameToken wsu:Id="UsernameToken-8C626B472A6858299E16062426451351">
            <wsse:Username>'.$username.'</wsse:Username>
            <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$password.'</wsse:Password>
            </wsse:UsernameToken>
    </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
    <orac:getShippedOrders>
        <!--Optional:-->
        <jdeOrderNumber>'.$jde_no.'</jdeOrderNumber>
        <storeOrderNumber>'.$order_id.'</storeOrderNumber>
    </orac:getShippedOrders>
    </soapenv:Body>
    </soapenv:Envelope>';

    $curl = curl_init();
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER ,false);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST ,false);
    curl_setopt_array($curl, array(
    CURLOPT_URL => $api_base_url."/GetShippedOrdersManager?wsdl",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>$xmlData ,
    CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: text/xml; charset=UTF-8",
        "SOAPAction:",
        "Content-length: ".strlen($xmlData),
        "x-httpanalyzer-rules: 1@localhost:8099",
    ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
    echo "cURL Error #:" . $err;
    } else {
        $status = false;
        //echo $response;
        $doc = new DOMDocument();
        $doc->loadXML($response);
        //echo $results=$doc->getElementsByTagName("quantityAvailable")[0]->nodeValue;
        //$order_ids = [1520, 1515, 1512 ];
        $parent = $doc->getElementsByTagName('shippedOrdersResult');
        $data = [];
        foreach($parent as $shipped)
        {
            // $key = array_rand($order_ids);
            $post_id = "";
            $track_no ="";
            $sku = trim($shipped->getElementsByTagName('itemNumber')[0]->nodeValue);
            foreach($shipped->childNodes as $childnode)
                {
                    if($childnode->nodeName == 'sampleStoreOrder'){
                        $post_id = trim($childnode->nodeValue);
                    }else{
                        $post_id = $order_id;
                    }

                    if($childnode->nodeName == 'trackingNumber'){
                       $track_no = trim($childnode->nodeValue);
                       $data[$post_id][$sku]['track_no'] = $track_no;
                    }
                    if($childnode->nodeName == 'shippedQuantity'){
                       $s_qty = (int)trim($childnode->nodeValue);
                       $data[$post_id][$sku]['s_qty'] = $s_qty;
                    }
                    if($childnode->nodeName == 'cancelledQuantity'){
                       $c_qty = (int)trim($childnode->nodeValue);
                       $data[$post_id][$sku]['c_qty'] = $c_qty;
                    }

                }

        }
        if(!empty($data)){
            $order = wc_get_order($order_id);
            foreach($data as $post_id => $trackarray){
                $metadata = get_post_meta($post_id, '_ship_track_numbers', true);
                if($metadata){
                    $array = unserialize($metadata);
                }else{
                    $array=[];
                }
                $array = array_merge($array, $trackarray);
                // print_r($array);
                // $array = array_values(array_unique($array));
                // print_r(serialize($array));
                update_post_meta($post_id, '_ship_track_numbers', serialize($array));
                $cancel = true;
                foreach($array as $sku => $val){
                    if($val['s_qty'] >0 && !empty($val['track_no'])){
                        $cancel = false;
                    }
                }
                if($cancel){
                    // $order->update_status('cancelled');
                }else{
                    $order->update_status('shipped');
                    $order->add_order_note('Shipping email sent');
                }
                $order->save();
            }
            // $array = unserialize('a:4:{s:9:"P10471-09";a:3:{s:8:"track_no";i:1234512345;s:5:"s_qty";i:1;s:5:"c_qty";i:0;}s:9:"P12318-01";a:3:{s:5:"c_qty";s:6:"0.0000";s:5:"s_qty";s:6:"1.0000";s:8:"track_no";s:10:"1234512345";}s:9:"P12261-02";a:3:{s:5:"c_qty";s:6:"0.0000";s:5:"s_qty";s:6:"1.0000";s:8:"track_no";s:10:"1234512345";}s:9:"P12148-01";a:3:{s:5:"c_qty";s:6:"0.0000";s:5:"s_qty";s:6:"1.0000";s:8:"track_no";s:10:"1234512345";}}');
            // update_post_meta($post_id, '_ship_track_numbers', serialize($array));
            // $order->add_order_note('Shipping email send');
            // $order->update_status('shipped');
            // $order->save();
        }
        return $data;
    }

}


function _order_jde_resync_check($order_id, $customer_po){

    global $username, $password, $api_base_url;
    $xmlData='<?xml version = "1.0" encoding = "UTF-8"?><env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:orac="http://oracle.e1.bssv.JP420000/">
    <env:Header><ns1:Security>
    <ns1:UsernameToken><ns1:Username>'.$username.'</ns1:Username>
    <ns1:Password>'.$password.'</ns1:Password>
    </ns1:UsernameToken></ns1:Security>
    </env:Header>  
    <env:Body>
    <orac:getSalesOrder>
    <header>
    <customerPO>'.$customer_po.'</customerPO></header>
    <agreementId>'.$order_id.'</agreementId>
    </orac:getSalesOrder>
    </env:Body></env:Envelope>';
    file_put_contents('jdelogs.txt', $xmlData.PHP_EOL."==============".PHP_EOL , FILE_APPEND | LOCK_EX);
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER ,false);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST ,false);
    curl_setopt_array($curl, array(
    CURLOPT_URL => $api_base_url."/SalesOrderManager",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>$xmlData ,
    CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: text/xml; charset=UTF-8",
        "SOAPAction:",
        "Content-length: ".strlen($xmlData),
        "x-httpanalyzer-rules: 1@localhost:8099",
    ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        //echo $response;
        $doc = new DOMDocument();
        $doc->loadXML($response);
        $number = 'Error';
        $parent = $doc->getElementsByTagName('header')->item(0);
        //echo $response;
        if($parent){
            if($doc->getElementsByTagName('salesOrderKey')->item(0)){
                $el = $doc->getElementsByTagName('salesOrderKey')->item(0);
                foreach($el->childNodes as $childnode){
                    if($childnode->nodeName == 'documentNumber')
                    $number = $childnode->nodeValue;
                }
            }
        }
        return $number;
    }
}