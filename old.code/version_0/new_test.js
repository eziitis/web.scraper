const fs = require('fs');
let rawdata3 = fs.readFileSync('new.json');
let student3 = JSON.parse(rawdata3);
let control_id = 34;

//console.log(student3);
do{

    console.log(student3[control_id]['id']);
    console.log(student3[control_id]['name']);
    //console.log(student3[control_id])
    control_id++;
} while(control_id<41);    