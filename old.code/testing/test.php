<?php

// $test = 'Tel : 964 78 1 99 111 22';
// $result = str_replace(' ', '', $test);
// $result = str_replace('.', '', $result);
// $result = str_replace('+', '', $result);

include_once('SorterAndFilter.php');

//before running this program, run scrape.js in console with command 'node scrape.js' for newest data
$sorter_and_filter = new SorterAndFilter();
//$sorter_and_filter->handle();
print_r($sorter_and_filter->handle());
echo "completed";




// if (str_contains($result, 'Tel')) {
//     if (str_contains($result, 'Fax')) {
//         $result = str_replace('Tel:','',$result);
//         $result = preg_replace('/Fax:[0-9]+/','',$result);
//         echo $result . "\n";
//     } else {
//         $result = str_replace('Tel:','',$result);
//         echo $result . "\n";
//     }
// }

// echo 'done';

// $file_name = 'data/se/7/contact_information_0.json';
// $json = file_get_contents($file_name);
// $json_data = json_decode($json,true);

// print_r(count($json_data));
// $test = fopen('test.txt', 'w');
// fwrite($test, '1002627;ACS AG;4;;;www.acs-ag.ch/cms/;www.se.com;Schönbühlring 59;9500;Wil;St. Gallen;switzerland;' . "\n");
// fclose($test);

//$country_name = '';
//$counter = 0;
//$file_name = 'data/se/111/contact_information_0.json';
//$json = file_get_contents($file_name);
//$json_data = json_decode($json,true);

//if(array_key_exists(0, $json_data)) {
//    echo "exists" . "\n";
//} else {
//    echo "doesnt" . "\n";
//}
//print_r($json_data);
// do {
//     if ($json_data[$counter]['country'] !== null) {
//         $country_name = $json_data[$counter]['country'];
//         $country_name = str_replace(' ','',$country_name);
//         $country_name = str_replace('-','_',$country_name);
//         $country_name = strtolower($country_name);
//     }
//     $counter++;
// } while($country_name === '');
//$countries_ids_list = scandir('test');
//unset($countries_ids_list[0]);
//print_r($countries_ids_list);
//echo is_dir('test.txt');
//echo "test\n";

//return $country_name;

//clean_all_existing_result_files_and_mid_files('results');
//clean_all_existing_result_files_and_mid_files('data/mid_data');
//clean_all_existing_result_files_and_mid_files('test');
//
//function clean_all_existing_result_files_and_mid_files(string $file_path):void
//{
//    if (file_exists($file_path)) {
//        if (is_dir($file_path)) {
//            $item_list = scandir($file_path);
//            if (count($item_list) !== 2) { // even if folder is empty it will still show two elements (this level and previous level)
//                unset($item_list[0]); // .
//                unset($item_list[1]); // ..
//                foreach ($item_list as $item) {
//                    clean_all_existing_result_files_and_mid_files($file_path . '/' . $item);
//                }
//            }
//            rmdir($file_path);
//        } else {
//            unlink($file_path);
//        }
//    }
//}
