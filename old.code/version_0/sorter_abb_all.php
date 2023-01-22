<?php
    
    $num = 0;
    //update customer_id and customer_type
    $customer_id = 999559;
    $customer_type = 4;
    do 
    {
        $file_name = 'data/abb/'. $num . '.json';
        $json = file_get_contents($file_name);
        $json_data = json_decode($json,true);
        
        $remark = "www.abb.com";
        $counter = 1;

        if ($num == 0) {
            $text_file_name = 'results/abb/all/abb_contacts_main.txt';
            $main_file = fopen($text_file_name, 'w');
            $input_text = "Customer number;Customer name;Customer type;Number;E-mail;Address;Remark;Address row 1;Zip code;City/Province;State/Region;Country;\n";
            fwrite($main_file, $input_text);
        } else {
            $text_file_name = 'results/abb/all/abb_contacts_main.txt';
            $main_file = fopen($text_file_name, 'a');

        }
        
        foreach($json_data['Items'] as $item) 
        {

            $input_text = "";
            $input_text .=$customer_id.";";

            if ($item['Name']!=null) {
                $contact_info = $item['Name'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            $input_text .=$customer_type.";";

            if ($item['Contact']['Telephone']!=null) {
                $contact_info = $item['Contact']['Telephone'];
                $contact_info=str_replace(' ','',$contact_info);
                $contact_info .= ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;  

            if ($item['Contact']['Email']!=null) {
                $contact_info = $item['Contact']['Email'];
                $contact_info=str_replace(' ','',$contact_info);
                $contact_info .= ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($item['Contact']['Url']!=null) {
                $contact_info = $item['Contact']['Url'];
                $contact_info=str_replace('http://','',$contact_info);
                $contact_info=str_replace(' ','',$contact_info);
                $contact_info .= ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            $input_text .=$remark.";";

            if ($item['Address']['StreetAddress']!=null) {
                $contact_info = $item['Address']['StreetAddress'];
                $contact_info = preg_replace("/\n/","",$contact_info);
                $contact_info .= ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($item['Address']['PostalCode']!=null) {
                $contact_info = $item['Address']['PostalCode'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($item['Address']['AddressLocality']!=null) {
                $contact_info = $item['Address']['AddressLocality'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($item['Address']['AddressRegion']!=null) {
                $contact_info = $item['Address']['AddressRegion'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;
            if ($counter == count($json_data['Items']) && $num == 59) {

                if ($item['Address']['AddressCountry']!=null) {
                    $contact_info = $item['Address']['AddressCountry'] . ";";
                } else {
                    $contact_info = ";";
                }
            } else {

                if ($item['Address']['AddressCountry']!=null) {
                    $contact_info = $item['Address']['AddressCountry'] . ";\n";
                } else {
                    $contact_info = ";\n";
                }
            }     
            
            $input_text .=$contact_info;
        
            fwrite($main_file, $input_text);
            $customer_id++;
            $counter++;
        }

        fclose($main_file);
        $num++;
    } while($num < 60);

    echo $num.' Worked fine'."\n";

?>