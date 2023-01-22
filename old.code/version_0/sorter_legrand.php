<?php
    
    //update customer_id and customer_type
    $customer_id = 1002555;
    $customer_type = 4;
    $remark = "www.legrand.com";

    $legrand_file_name = 'data/legrand/World.txt';
    $main_read_file = fopen($legrand_file_name, 'r');

    $legrand_address_street = "";
    $legrand_address_locality = "";
    $legrand_address_country = "";
    $legrand_tel = "";
    $legrand_email = "";
    $legrand_web = "";
    $legrand_name = "";


    while(!feof($main_read_file)) {

        $text_check = true;
        $legrand_tel = ";";
        $legrand_email = ";";
        $legrand_web = ";";

        $legrand_name = fgets($main_read_file) . ";";
        $legrand_name = preg_replace("/\n/","",$legrand_name);
        
        fgets($main_read_file);

        $legrand_address_street = fgets($main_read_file);
        $legrand_address_street = preg_replace("/\n/","",$legrand_address_street);
        $legrand_address_locality = fgets($main_read_file);
        $legrand_address_locality = preg_replace("/\n/","",$legrand_address_locality);
        $legrand_address_country = fgets($main_read_file);
        $legrand_address_country = preg_replace("/\n/","",$legrand_address_country);

        $text_file_name = 'results/legrand/countries/'.$legrand_address_country.'.txt';
        if (!file_exists($text_file_name)) {
            $main_file = fopen($text_file_name, 'w');
            $input_text = "Customer number;Customer name;Customer type;Number;E-mail;Address;Remark;Address row 1;Zip code;City/Province;State/Region;Country;\n";
            fwrite($main_file, $input_text);
        } else {
            $main_file = fopen($text_file_name, 'a');
        }
        
        $input_text = "";
        
        $input_text .=$customer_id.";";
        $input_text .=$legrand_name;
        $input_text .=$customer_type.";";

        do {
            switch ($mid_elem = fgets($main_read_file)) {
                case strpos($mid_elem, 'Tel'):
                    $legrand_tel = str_replace('Tel : ', '', $mid_elem);
                    $legrand_tel = preg_replace("/\n/","",$legrand_tel).";";
                    break;
                case strpos($mid_elem, 'Fax'):
                    //needs adjusting
                    if ($customer_id == 999836) {
                        $text_check = false;
                    }
                    break;
                case strpos($mid_elem, 'E-mail'):
                    $legrand_email = str_replace('E-mail: ', '', $mid_elem);
                    $legrand_email = preg_replace("/\n/","",$legrand_email).";";
                    break;    
                case strpos($mid_elem, 'Web'):
                    $legrand_web = str_replace('Web: ', '', $mid_elem);
                    $legrand_web = preg_replace("/\n/","",$legrand_web).";";
                    break;    
                default:
                    $text_check = false;                
            }
        } while($text_check == true);

        $input_text .=$legrand_tel;
        $input_text .=$legrand_email;
        $input_text .=$legrand_web;

        $input_text .=$remark.";";
        $input_text .=$legrand_address_street.";";
        $input_text .=";";
        $input_text .=$legrand_address_locality.";";
        $input_text .=";";
        $input_text .=$legrand_address_country.";"."\n";

        $customer_id++;     
        fwrite($main_file, $input_text);
        fclose($main_file);
        
    }

    echo 'worked';
    fclose($main_read_file);
?>