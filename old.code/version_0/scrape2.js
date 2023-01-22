const puppeteer = require('puppeteer');

let scrape = async () => {

    const browser = await puppeteer.launch({
        args: ['--user-agent=<user_agent_string>'], 
        devtools: false, 
        headless: true, 
        defaultViewport: null}
        );
    const page = await browser.newPage();

    let control_id = 36;
    do{
        const fs = require('fs');
        let rawdata3 = fs.readFileSync('new.json');
        let student3 = JSON.parse(rawdata3);

            //manual input 1
        /******************************/
        let country_code = student3[control_id]['id'];
        let country_name = student3[control_id]['name'];
        /******************************/

        let inital_url = 'https://www.se.com/lv/lv/locate/api/partners/locations?config='+ country_code +'&sortType=companyName&sortDirection=up&countryCode=en&languageCode=en';

        await page.goto(inital_url);
        const result0 = await page.evaluate(() => {
                
            let name = document.querySelector('body').innerText;
            return name;

        });

        let json_name0 = 'mid/real/'+ country_code +'/ids.json';
        if (!fs.existsSync('mid/real/'+ country_code)){
            fs.mkdirSync('mid/real/'+ country_code);
        }
        fs.writeFileSync(json_name0, result0);

        let rawdata = fs.readFileSync(json_name0);
        let student = JSON.parse(rawdata);
        let counter = 0;


        do {

            let id = student['partnerLocations'][counter]['id'];
            await page.goto('https://www.se.com/lv/lv/locate/api/partners/id-list?id='+ id +'&countryCode=lv&languageCode=lv');
            const result = await page.evaluate(() => {
                    
                let name = document.querySelector('body').innerText;
                return name;
            
            });
            

            let json_name = 'mid/' + country_code + '/real_id' + counter + '.json';
            if (!fs.existsSync('mid/' + country_code)){
                fs.mkdirSync('mid/' + country_code);
            }
            fs.writeFileSync(json_name, result);

            let rawdata2 = fs.readFileSync(json_name);
            let student2 = JSON.parse(rawdata2);
            console.log(id);

            

            let id_url = 'https://www.se.com/lv/lv/locate/api/partners/'+ student2[0]['accountBfoId'] +'?countryCode=lv&languageCode=lv';
            await page.goto(id_url);

            const result2 = await page.evaluate(() => {
                    
                let name = document.querySelector('body').innerText;
                return name;
            
            });

            let json_name2 = 'data/se/countries/'+ country_name +'/' + counter + '.json';
            if (!fs.existsSync('data/se/countries/'+ country_name)){
                fs.mkdirSync('data/se/countries/'+ country_name);
            }
            fs.writeFileSync(json_name2, result2);
                
            counter++;

        } while(counter<student['partnerLocations'].length);
        console.log(student3[control_id]['name']);
        control_id++;
        console.log('worked');
    } while(control_id<41); 
    
    browser.close();

};

scrape().then(() => {
    console.log('Worked'); // Success!
});