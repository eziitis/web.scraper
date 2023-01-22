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

        $duplicate_phone_check = false;
        $duplicate_web_check = false;
        $phone_check = true;
        $email_check = true;

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
        $legrand_address_country = strtolower($legrand_address_country);


        $path_web = 'results/index/web/'.$legrand_address_country.'.txt';
        if(!file_exists($path_web)) {
            $web_file_inital = fopen($path_web, 'w');
            fwrite($web_file_inital, "Web list\n");
            fclose($web_file_inital);
        }
    
        $path_phone = 'results/index/phone/'.$legrand_address_country.'.txt';
        if (!file_exists($path_phone)) {
            $phone_file_initial = fopen($path_phone, 'w');
            fwrite($phone_file_initial, "Phone list\n");
            fclose($phone_file_initial);
        }

        $text_file_name = 'results/combined_filtered/'.$legrand_address_country.'.txt';
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
                    $legrand_tel = str_replace(' ', '', $legrand_tel);
                    $legrand_tel = preg_replace("/\n/","",$legrand_tel);

                    $phone_file = fopen($path_phone, 'a+');
                    fseek($phone_file, 0);

                    while(!feof($phone_file)) {
                        $test_line = preg_replace("/\n/","",fgets($phone_file));
                        if ($test_line == $legrand_tel) {
                            $duplicate_phone_check = true;
                        }
                    }
                    if ($duplicate_phone_check == false) {
                        fwrite($phone_file,$legrand_tel."\n");
                        fclose($phone_file);
                    } else {
                        echo $legrand_tel."\n";
                        fclose($phone_file);
                    }

                    $legrand_tel .= ";";
                    break;
                case strpos($mid_elem, 'Fax'):
                    //needs adjusting
                    if ($customer_id == 999836) {
                        $text_check = false;
                    }
                    break;
                case strpos($mid_elem, 'E-mail'):
                    $legrand_email = str_replace('E-mail: ', '', $mid_elem);
                    $legrand_email = str_replace(' ', '', $legrand_email);
                    $legrand_email = preg_replace("/\n/","",$legrand_email);

                    $web_file = fopen($path_web, 'a+');
                    fseek($web_file, 0);

                    while(!feof($web_file)) {
                        $test_line = preg_replace("/\n/","",fgets($web_file));
                        if ($test_line == $legrand_email) {
                            $duplicate_web_check = true;
                        }
                    }
                    if ($duplicate_web_check == false) {
                        fwrite($web_file,$legrand_email."\n");
                        fclose($web_file);
                    } else {
                        echo $legrand_email."\n";
                        fclose($web_file);
                    }

                    $legrand_email .=  ";";
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

        if(($duplicate_phone_check == true && $duplicate_web_check == true) || ($duplicate_phone_check == true && $email_check == false) || ($duplicate_web_check == true && $phone_check == false)) {
            $duplicate_path = 'results/duplicates/'.$legrand_address_country.'.txt';
            if (!file_exists($duplicate_path)) {
                $duplicate_file = fopen($duplicate_path, 'w');
                fwrite($duplicate_file, $input_text);
                fclose($duplicate_file);
            } else {
                $duplicate_file = fopen($duplicate_path, 'a');
                fwrite($duplicate_file, $input_text);
                fclose($duplicate_file);
            }
        } elseif ($phone_check == true || $email_check == true) {
            fwrite($main_file, $input_text);
        } else {
            $path_no_contact = 'results/empty/'.$legrand_address_country.'.txt';
            if (!file_exists($path_no_contact)) {
                $empty_file = fopen($path_no_contact, 'w');
                fwrite($empty_file, $input_text); 
                fclose($empty_file);               
            } else {
                $empty_file = fopen($path_no_contact, 'a');
                fwrite($empty_file, $input_text); 
                fclose($empty_file); 
            }
        }

        fclose($main_file);
        
    }

    echo 'worked';
    fclose($main_read_file);
?>