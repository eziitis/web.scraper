<?php

    $num = 1;
    $file_name = 'data/'. $num . '.json';
    $json = file_get_contents($file_name);
    $json_data = json_decode($json,true);
    $shorter_json = $json_data['Items'];

    

    $xml = new DOMDocument('1.0', 'utf-8');

    $xml_root = $xml->createElement("xml");
    $xml->appendChild( $xml_root );
    
    //cikls
    foreach($shorter_json = $json_data['Items'] as $item) 
    {
        $xml_elem = $xml->createElement("Result");
        $xml_root->appendChild( $xml_elem );
        $xml_elem->setAttribute("ID", $item['Id']);

        $xml_elem->appendChild( $xml->createElement("Name", htmlspecialchars($item['Name'])) );
        $xml_elem->appendChild( $xml->createElement("Description", htmlspecialchars($item['Description'])) );

        
        $xml_contact = $xml->createElement("Contact");
        $xml_elem->appendChild($xml_contact);
        $xml_contact->appendChild( $xml->createElement("Telephone", $item['Contact']['Telephone']) );
        $xml_contact->appendChild( $xml->createElement("Email", $item['Contact']['Email']) );
        $xml_contact->appendChild( $xml->createElement("Website", $item['Contact']['Url']) );

        $xml_address = $xml->createElement("Address");
        $xml_elem->appendChild($xml_address);
        $xml_address->appendChild( $xml->createElement("Street", htmlspecialchars($item['Address']['StreetAddress'])) );
        $xml_address->appendChild( $xml->createElement("Locality", $item['Address']['AddressLocality']) );
        $xml_address->appendChild( $xml->createElement("Region", $item['Address']['AddressRegion']) );
        $xml_address->appendChild( $xml->createElement("Country", $item['Address']['AddressCountry']) );
        $xml_address->appendChild( $xml->createElement("PostalCode", $item['Address']['PostalCode']) );

        $counter = 0;

        foreach ($item['Responsibilities'] as $lower_item)
        {
            $xml_responsibilities = $xml->createElement("Responsibilities");
            $xml_elem->appendChild($xml_responsibilities);
            $xml_responsibilities->setAttribute("Num", $counter);
            $counter++;
            $xml_responsibilities->appendChild( $xml->createElement("Name", $lower_item['Name']) );
            
            foreach ($lower_item['Channels'] as $lowest_item)
            {
                $xml_responsibilities->appendChild( $xml->createElement("Channel", $lowest_item) );
            }
        }
    }
    $xml->save("kontakti/1.xml");
    echo 'Worked fine';

?>