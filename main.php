<?php

include_once('SorterAndFilter.php');

//pirms šī koda daļas palaišanas, noteikti jāpalaiz scrape.js, lai iegūtu jaunākos datus
$sorter_and_filter = new SorterAndFilter();
//$sorter_and_filter->handle();
print_r($sorter_and_filter->handle());
