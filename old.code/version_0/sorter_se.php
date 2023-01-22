<?php
    
    $num = 0;
    //update customer_id and customer_type

    $customer_id = 1002626;
    $customer_type = 4;
    $remark = "www.se.com";
    $countries_list = scandir('data/se/countries');
    unset($countries_list[0]);
    unset($countries_list[1]);
    
    foreach ($countries_list as $item) 
    {
        $counter = 1;
        $elem_list = scandir('data/se/countries/'.$item);
        unset($elem_list[0]);
        unset($elem_list[1]);

        $text_file_name = 'results/se/'.$item.'.txt';
        $main_file = fopen($text_file_name, 'w');
        $input_text = "Customer number;Customer name;Customer type;Number;E-mail;Address;Remark;Address row 1;Zip code;City/Province;State/Region;Country;\n";
        fwrite($main_file, $input_text);

        foreach ($elem_list as $elem)
        {

            $file_name = 'data/se/countries/'. $item .'/' .$elem;
            $json = file_get_contents($file_name);
            $json_data = json_decode($json,true);

            $input_text = "";
            $input_text .=$customer_id.";";

            if ($json_data['companyName']!=null) {
                $contact_info = $json_data['companyName'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;
            
            $input_text .=$customer_type.";";

            if ($json_data['partnerDetails']['partnerContact']['phone']!=null) {
                $contact_info = $json_data['partnerDetails']['partnerContact']['phone'];
                $contact_info=str_replace(' ','',$contact_info);
                $contact_info .= ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($json_data['partnerDetails']['partnerContact']['email']!=null) {
                $contact_info = $json_data['partnerDetails']['partnerContact']['email'];
                $contact_info=str_replace(' ','',$contact_info);
                $contact_info .= ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($json_data['webSite']!=null) {
                $contact_info = $json_data['webSite'];
                $contact_info=str_replace('http://','',$contact_info);
                $contact_info=str_replace(' ','',$contact_info);
                $contact_info .= ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            $input_text .=$remark.";";
            
            if ($json_data['address1']!=null) {
                $contact_info = $json_data['address1'];
                $contact_info=preg_replace("/\n/","",$contact_info);
                $contact_info=str_replace('  ','',$contact_info);
                $contact_info .= ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($json_data['zipCode']!=null) {
                $contact_info = $json_data['zipCode'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($json_data['city']!=null) {
                $contact_info = $json_data['city'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;

            if ($json_data['administrativeRegion']!=null) {
                $contact_info = $json_data['administrativeRegion'] . ";";
            } else {
                $contact_info = ";";
            }
            $input_text .=$contact_info;
           
            if ($counter == count($elem_list)) {

                if ($json_data['country']!=null) {
                    $json_data['country'] . ";";
                } else {
                    $contact_info = ";";
                }
            } else {

                if ($json_data['country']!=null) {
                    $contact_info = $json_data['country'] . ";\n";
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

    }

    //print_r($elem_list);
       

?>