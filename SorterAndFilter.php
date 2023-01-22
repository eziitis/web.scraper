<?php

class SorterAndFilter {
    private int $customer_id = 999559; // pirmais pieejamais ID, kas tiks izmantots Monitor G5 sistēmā
    private int $customer_type = 4; // norāde uz iegūtajiem kontaktiem no webscraper
    private string $abb_remark = "www.abb.com"; // vieglākai kārtošanai
    private string $legrand_remark = "www.legrand.com";
    private string $se_remark = "www.se.com";
    private array $statistics = [];

    public function handle():array
    {
        $this->delete_old_and_create_new_folder_structure();
        $this->process_abb_data();
        $this->process_legrand_data();
        $this->process_se_data();

        if (file_exists('statistics.txt')) {
            unlink('statistics.txt');
        }
        file_put_contents('statistics.txt', print_r($this->statistics, true));
        return $this->statistics;
    }

    private function delete_old_and_create_new_folder_structure():void
    {
        $this->clean_all_existing_result_files_and_mid_files('results');
        $this->clean_all_existing_result_files_and_mid_files('data/mid_data');

        $folder_paths = [
            'data/mid_data',
            'data/mid_data/email',
            'data/mid_data/phone_number',
            'results',
            'results/combined',
            'results/abb',
            'results/abb/duplicates',
            'results/abb/empty',
            'results/legrand',
            'results/legrand/duplicates',
            'results/legrand/empty',
            'results/se',
            'results/se/duplicates',
            'results/se/empty'
        ];
        foreach ($folder_paths as $path) {
            $this->create_folder($path);
        }
    }

    private function clean_all_existing_result_files_and_mid_files(string $file_path):void
    {
        if (file_exists($file_path)) {
            if (is_dir($file_path)) {
                $item_list = scandir($file_path);
                if (count($item_list) === 2) { // pat ja teorētiski tukšs tajā jebkurā gadijumā parādīsies vismaz 2
                    rmdir($file_path);
                } else {
                    unset($item_list[0]); // .
                    unset($item_list[1]); // ..
                    foreach ($item_list as $item) {
                        $this->clean_all_existing_result_files_and_mid_files($file_path . '/' . $item);
                    }
                }
            } else {
                unlink($file_path);
            }
        }
    }

    private function create_folder(string $path):void
    {
        if (!file_exists($path)) {
            mkdir($path);
        }
    }

    private function process_abb_data():void
    {
        $file_path = 'data/abb'; // šajā mapē ir visi ievāktie kontaki no abb mājaslapas, izmantojot scrape.js
        $directory_item_list = scandir($file_path);
        unset($directory_item_list[0]);
        unset($directory_item_list[1]);

        foreach ($directory_item_list as $directory_item) {
            $file_name = 'data/abb/'. $directory_item;
            $json = file_get_contents($file_name); //jānolasa viss fails, lai nākamajā rindā varētu to dekodēt kā json
            $json_data = json_decode($json,true);

            foreach($json_data['Items'] as $item) // abb mapē, json failos var būt līdz 50 vienībām
            {
                $shared_phone_number = false; //pārbaude, vai ir unikāls
                $shared_email = false;
                $phone_number_exists = true; // pārbaude, vai vispār eksistē
                $email_exists = true;

                if ($item['Address']['AddressCountry'] !== null) {
                    $country_name = strtolower($item['Address']['AddressCountry']);
                } else {
                    $country_name = 'unknown';
                }
                $this->create_registry_folders($country_name); // kopējais reģistrs, kurā tiks uzskaitīti unikālie tel. numuri un epasti

                $line_text = $this->customer_id . ';';
                $line_text .= $this->check_and_return_parameter_value($item['Name'], 'name');
                $line_text .= $this->customer_type . ';';
                $line_text .= $this->check_phone_number_or_email($item['Contact']['Telephone'], 'telephone', $country_name, $shared_phone_number, $phone_number_exists, $shared_email, $email_exists);
                $line_text .= $this->check_phone_number_or_email($item['Contact']['Email'], 'email', $country_name, $shared_phone_number, $phone_number_exists, $shared_email, $email_exists);
                $line_text .= $this->check_and_return_parameter_value($item['Contact']['Url'], 'url');
                $line_text .= $this->abb_remark . ';';
                $line_text .= $this->check_and_return_parameter_value($item['Address']['StreetAddress'], 'address');
                $line_text .= $this->check_and_return_parameter_value($item['Address']['PostalCode'], 'postal_code');
                $line_text .= $this->check_and_return_parameter_value($item['Address']['AddressLocality'], 'address_locality');
                $line_text .= $this->check_and_return_parameter_value($item['Address']['AddressRegion'], 'address_region');
                $line_text .= $this->check_and_return_parameter_value($item['Address']['AddressCountry'], 'country');

                $this->write_to_file_and_collect_statistic($shared_phone_number, $shared_email, $email_exists, $phone_number_exists, $country_name, $line_text, 'abb');

                $this->customer_id++;
            }
        }
    }

    private function process_legrand_data():void
    {
        $legrand_file_name = 'data/legrand/contact_information_1.json'; //legrand gadījumā ir tikai viens fails, kurā ir visa informācija
        $json = file_get_contents($legrand_file_name);
        $json_data = json_decode($json,true);

        foreach($json_data as $country_name) {
            foreach($country_name as $item) { //katrai valstij ir vismaz viens rezultāts (nav tukšas)
                $shared_phone_number = false;
                $shared_email = false;
                $phone_number_exists = true;
                $email_exists = true;

                $this->create_registry_folders($item['country']);

                $line_text = $this->customer_id . ';';
                $line_text .= $this->check_and_return_legrand_parameter_value($item, 'company_name');
                $line_text .= $this->customer_type . ';';
                $line_text .= $this->check_phone_number($item, $item['country'], $shared_phone_number, $phone_number_exists);
                $line_text .= $this->check_email($item, $item['country'], $shared_email, $email_exists);
                $line_text .= $this->check_and_return_legrand_parameter_value($item, 'website');
                $line_text .= $this->legrand_remark . ';';
                $line_text .= $this->check_and_return_legrand_parameter_value($item, 'address');
                $line_text .= ";";
                $line_text .= ";";
                $line_text .= ";";
                $line_text .= $item['country'] . ";";

                $this->write_to_file_and_collect_statistic($shared_phone_number, $shared_email, $email_exists, $phone_number_exists, $item['country'], $line_text, 'legrand');

                $this->customer_id++;
            }
        }
    }

    private function process_se_data():void
    {
        $countries_ids_list = scandir('data/se');
        unset($countries_ids_list[0]);
        unset($countries_ids_list[1]);

        foreach ($countries_ids_list as $country_code) {
            $country_name = $this->get_country_name($country_code);
            if ($country_name !== null && $country_name !== '') {
                $country_json_file_list = scandir('data/se/' . $country_code);
                unset($country_json_file_list[0]);
                unset($country_json_file_list[1]);

                $this->create_registry_folders($country_name);

                foreach ($country_json_file_list as $country_json_file_name) {
                    $shared_phone_number = false;
                    $shared_email = false;
                    $phone_number_exists = true;
                    $email_exists = true;

                    $file_name = 'data/se/'. $country_code . '/' . $country_json_file_name;
                    $json = file_get_contents($file_name);
                    $json_data = json_decode($json,true);

                    $line_text = $this->customer_id . ';';
                    $line_text .= $this->check_writable_data($json_data[0], 'companyName');
                    $line_text .= $this->customer_type . ';';
                    $line_text .= $this->check_se_contacts($json_data[0], $shared_email, $email_exists, $shared_phone_number, $phone_number_exists, $country_name, 'phone');
                    $line_text .= $this->check_se_contacts($json_data[0], $shared_email, $email_exists, $shared_phone_number, $phone_number_exists, $country_name, 'email');
                    $line_text .= $this->check_writable_data($json_data[0], 'webSite');
                    $line_text .= $this->se_remark . ';';
                    $line_text .= $this->check_writable_data($json_data[0], 'address1');
                    $line_text .= $this->check_writable_data($json_data[0], 'zipCode');
                    $line_text .= $this->check_writable_data($json_data[0], 'city');
                    $line_text .= $this->check_writable_data($json_data[0], 'administrativeRegion');
                    $line_text .= $country_name . ';';

                    $this->write_to_file_and_collect_statistic($shared_phone_number, $shared_email, $email_exists, $phone_number_exists, $country_name, $line_text, 'se');
                    $this->customer_id++;
                }
            }
        }
    }

    private function create_registry_folders(string $country_name):void
    {
        $email_file_path = 'data/mid_data/email/'.$country_name.'.txt';
        $this->create_if_needed($email_file_path, "Unique email list\n");
        $phone_number_file_path = 'data/mid_data/phone_number/'.$country_name.'.txt';
        $this->create_if_needed($phone_number_file_path, "Phone list\n");
    }

    private function check_and_return_parameter_value(?string $item, string $type):string // ?string - gadījumā ja $item ir null
    {
        if ($item === null) {
            $result = ';';
        } else {
            if ($type === 'postal_code' || $type === 'address_locality' || $type === 'address_region'  || $type === 'country') {
                $result = "$item;";
            } else {
                switch ($type) {
                    case 'url':
                        $result = $item;
                        $result = str_replace('http://','',$result);
                        $result = str_replace('https://','',$result);
                        $result = str_replace(' ','',$result);
                        $result .= ";";
                        break;
                    case 'address':
                        $result = $item;
                        $result = str_replace("\n",'',$result);
                        $result .= ";";
                        break;
                    case 'name':
                        $result = $item;
                        $result = str_replace(";",'',$result);
                        $result .= ";";
                        break;
                    default:
                        $result = ";";
                        break;
                }
            }
        }

        return $result;
    }

    private function check_phone_number_or_email(?string $item, string $type, string $country_name, bool &$shared_phone_number, bool &$phone_number_exists, bool &$shared_email, bool &$email_exists):string
    {
        if ($item === null) {
            if ($type === 'telephone') {
                $phone_number_exists = false;
            } elseif ($type === 'email') {
                $email_exists = false;
            }
            return ';';
        } else {
            if ($type === 'telephone') {
                $file_path = 'data/mid_data/phone_number/'.$country_name.'.txt';
            } else {
                $file_path = 'data/mid_data/email/'.$country_name.'.txt';
            }

            $result = str_replace(' ','',$item);

            $file = fopen($file_path, 'a+');
            fseek($file, 0);

            $duplicate = false;
            while(!feof($file)) { // feof - norāda uz faila beigām
                $line = preg_replace("/\n/","",fgets($file));
                if ($line == $result) {
                    if ($type === 'telephone') {
                        $shared_phone_number = true;
                    } elseif ($type === 'email') {
                        $shared_email = true;
                    }
                    $duplicate = true;
                }
            }
            if ($duplicate === false) {
                fwrite($file, $result . "\n");
            }
            fclose($file);

            return $result . ";";
        }
    }

    private function add_empty_or_duplicate(string $file_path,string $value):void
    {
        $mode = !file_exists($file_path) ? 'w' : 'a';
        $file = fopen($file_path, $mode);
        fwrite($file, $value);
        fclose($file);
    }

    private function create_if_needed(string $file_path, string $first_line_text):void
    {
        if (!file_exists($file_path)) {
            $file = fopen($file_path, 'w');
            fwrite($file, $first_line_text);
            fclose($file);
        }
    }

    private function add_statistics(string $site, string $country,string $type):void
    {
        if ($this->statistics === []) {
            $this->statistics = [
                'totals' => [ //lielie kopējie rādītāji, pa lapām un apvienotie
                    'grand_totals' => [
                        'added' => 0,
                        'duplicate' => 0,
                        'empty' => 0
                    ],
                    'abb' => [
                        'added' => 0,
                        'duplicate' => 0,
                        'empty' => 0
                    ],
                    'legrand' => [
                        'added' => 0,
                        'duplicate' => 0,
                        'empty' => 0
                    ],
                    'se' => [
                        'added' => 0,
                        'duplicate' => 0,
                        'empty' => 0
                    ]
                ], //
                'abb' => [], //katrai lapa atsevišķi rādītāji, kurā tie ir sadalīti pa valstīm
                'legrand' => [],
                'se' => [],
                'country_totals' => [] //katrai valstim savāktie rādītāji
            ];
            $this->add_statistics($site, $country, $type); //izsaukts vienreiz pēc sākotnējās struktūras izveides
        } else {
            $this->statistics['totals']['grand_totals'][$type]++; //palielina kopējo skaitītāju
            $this->statistics['totals'][$site][$type]++; //kopējais mājaslapai
            if (!array_key_exists($country, $this->statistics[$site])) { //katrai lapai individuāla valsts
                $this->statistics[$site][$country] = [
                    'added' => 0,
                    'duplicate' => 0,
                    'empty' => 0
                ];
            }
            $this->statistics[$site][$country][$type]++;
            if (!array_key_exists($country, $this->statistics['country_totals'])) { //kopējais valstu rādītājs
                $this->statistics['country_totals'][$country] = [
                    'added' => 0,
                    'duplicate' => 0,
                    'empty' => 0
                ];
            }
            $this->statistics['country_totals'][$country][$type]++;
        }
    }

    private function write_to_file_and_collect_statistic(bool $shared_phone_number, bool $shared_email, bool $email_exists, bool $phone_number_exists, string $country_name, string $line_text, string $site):void
    {
        if(($shared_phone_number && $shared_email) || ($shared_phone_number && !$email_exists) || ($shared_email && !$phone_number_exists)) {
            $duplicate_path = 'results/' . $site . '/duplicates/' . $country_name . '.csv';
            $this->add_empty_or_duplicate($duplicate_path, $line_text . "\n");
            $this->add_statistics($site, $country_name, 'duplicate');

        } elseif ($phone_number_exists || $email_exists) {
            $main_file_path = 'results/combined/'. $country_name .'.csv';
            if (!file_exists($main_file_path)) {
                $main_file = fopen($main_file_path, 'w');
                $header_text = "Customer number;Customer name;Customer type;Number;E-mail;Address;Remark;Address row 1;Zip code;City/Province;State/Region;Country;\n";
                fwrite($main_file, $header_text);
            } else {
                $main_file = fopen($main_file_path, 'a');
            }

            fwrite($main_file, $line_text . "\n");
            fclose($main_file);
            $this->add_statistics($site, $country_name, 'added');
        } else {
            $path_empty = 'results/' . $site . '/empty/'.$country_name.'.csv';
            $this->add_empty_or_duplicate($path_empty, $line_text . "\n");
            $this->add_statistics($site, $country_name, 'empty');
        }
    }

    private function check_and_return_legrand_parameter_value(array $item, string $type):string
    {
        if (array_key_exists($type,$item)) {
            return $item[$type] . ";";
        } else {
            return ";";
        }
    }

    private function check_phone_number(array $item, string $country_name, bool &$shared_phone_number, bool &$phone_number_exists):string
    {
        if (!array_key_exists('tel_and_fax', $item)) {
            $phone_number_exists = false;
            return ';';
        } else {
            $value = str_replace(' ', '', $item);
            $value = str_replace('.', '', $value);
            $value = str_replace('+', '', $value);
            $result = '';

            if (str_contains($result, 'Tel')) {
                if (str_contains($result, 'Fax')) {
                    $result = str_replace('Tel:','',$value);
                    $result = preg_replace('/Fax:[0-9]+/','',$result);
                    echo $result . "\n";
                } else {
                    $result = str_replace('Tel:','',$value);
                    echo $result . "\n";
                }
            }

            if ($result !== '') {
                $file = fopen('data/mid_data/phone_number/' . $country_name . '.txt', 'a+');
                fseek($file, 0);

                $duplicate = false;
                while(!feof($file)) {
                    $line = preg_replace("/\n/","",fgets($file));
                    if ($line == $result) {
                        $shared_phone_number = true;
                        $duplicate = true;
                    }
                }
                if ($duplicate === false) {
                    fwrite($file, $result . "\n");
                }
                fclose($file);
            }
            return $result . ";";
        }
    }

    private function check_email(array $item, string $country_name, bool &$shared_email, bool &$email_exists):string
    {
        if (!array_key_exists('email', $item)) {
            $email_exists = false;
            return ';';
        } else {
            $result = $item['email'];

            $file = fopen('data/mid_data/email/' . $country_name . '.txt', 'a+');
            fseek($file, 0);

            $duplicate = false;
            while(!feof($file)) {
                $line = preg_replace("/\n/","",fgets($file));
                if ($line == $result) {
                    $shared_email = true;
                    $duplicate = true;
                }
            }
            if ($duplicate === false) {
                fwrite($file, $result . "\n");
            }
            fclose($file);
            return $result . ";";
        }
    }

    private function get_country_name(string $country_code):?string {
        $country_name = '';
        $counter = 0;
        $file_name = 'data/se/'. $country_code . '/contact_information_0.json';
        $json = file_get_contents($file_name);
        $json_data = json_decode($json,true);
        if(array_key_exists(0, $json_data)) {
            do {
                if ($json_data[$counter]['country'] !== null) {
                    $country_name = $json_data[$counter]['country'];
                    $country_name = str_replace(' ','',$country_name);
                    $country_name = str_replace('-','_',$country_name);
                    $country_name = strtolower($country_name);
                }
                $counter++;
            } while($country_name === '' && $counter < 50);

            return $country_name;
        } else {
            return null;
        }
    }

    private function check_se_contacts(array $json_data, bool &$shared_email, bool &$email_exists, bool &$shared_phone_number, bool &$phone_number_exists, string $country_name, string $type):string {
        if ($type === 'phone') {
            $result = $json_data['partnerDetails']['partnerContact']['phone'];
        } else {
            $result = $json_data['partnerDetails']['partnerContact']['email'];
        }
        if ($result != null) {
            $result = str_replace(' ','',$result);

            if ($type === 'phone') {
                $file_path = 'data/mid_data/phone_number/'.$country_name.'.txt';
            } else {
                $file_path = 'data/mid_data/email/'.$country_name.'.txt';
            }

            $file = fopen($file_path, 'a+');
            fseek($file, 0);

            while(!feof($file)) {
                $test_line = preg_replace("/\n/","",fgets($file));
                if ($test_line == $result) {
                    if ($type === 'phone') {
                        $shared_phone_number = true;
                    } else {
                        $shared_email = true;
                    }
                }
            }
            if (($type === 'email' && !$shared_email) || ($type === 'phone' && !$shared_phone_number)) {
                fwrite($file,$result."\n");
            }
            fclose($file);

            return $result .= ";";
        } else {
            if ($type === 'phone') {
                $phone_number_exists = false;
            } else {
                $email_exists = false;
            }
            return ";";
        }
    }

    private function check_writable_data(array $json_data, string $type):string {
        if ($json_data[$type] === null) {
            return ";";
        } else {
            if ($type === 'zipCode' || $type === 'city' || $type === 'administrativeRegion') {
                return $json_data[$type] . ";";
            } else {
                switch ($type) {
                    case 'companyName':
                        $result = $json_data[$type];
                        $result = str_replace(';','',$result);
                        return $result . ";";
                    case 'webSite':
                        $result = $json_data[$type];
                        $result = str_replace('http://','',$result);
                        $result = str_replace(' ','',$result);
                        return $result .= ";";
                    case 'address1':
                        $result = $json_data[$type];
                        $result = str_replace(';','',$result);
                        $result = preg_replace("/\n/","",$result);
                        $result = str_replace('  ','',$result);
                        return $result .= ";";
                    default:
                        return ";";
                }
            }
        }
    }
}