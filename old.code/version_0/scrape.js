const puppeteer = require('puppeteer');

let scrape = async () => {
    const browser = await puppeteer.launch({
        args: ['--user-agent=<user_agent_string>'], 
        devtools: false, 
        headless: true, 
        defaultViewport: null}
        );
    const page = await browser.newPage();
    let cycle_counter = 0;

    await page.goto('https://www.se.com/lv/lv/locate/api/partners/locations?config=262&sortType=companyName&sortDirection=up&countryCode=en&languageCode=en');
    const result = await page.evaluate(() => {
            
        let name = document.querySelector('body').innerText;
        return name;
    
    });
    let json_name = 'mid/ids.json';
    const fs = require('fs');
    fs.writeFileSync(json_name, result);

    const fsread = require('fs');
    let rawdata = fsread.readFileSync('mid/ids.json');
    let student = JSON.parse(rawdata);
    
    browser.close();

};

scrape().then(() => {
    console.log('Worked'); // Success!
});