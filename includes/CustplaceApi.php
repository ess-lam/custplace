<?php

class CustplaceApi 
{
    

    function save($order, $client_id, $api_key )
    {
        $url ="https://apis.custplace.com/v3/$client_id/invitations";
        $data = array("type" => "post_review") + $order;
        
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 
            http_build_query($data)
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, 
            array(
                "Authorization: Bearer {$api_key}",
                "Accept: application/json"
            )
        );
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        $response = curl_exec( $ch );
        curl_close( $ch );

        $status = json_decode($response)->code; 
        return $status;

        // var_dump( $response, "\n", $data,"\n", json_decode($response)->code);
        // die();
    }
}

// array(
//     "account_id" => $options['id_client'],
//     "type"       => '',
//     "email"      => '',
//     "order_ref"  => '',
//     "firstname"  => '',
//     "lastname"   => '',
//     "products"  => [
//         "products[sku]"     => '',
//         "products[name]"    => '',
//         "products[website]" => '',
//     ]
// )
