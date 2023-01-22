const puppeteer = require('puppeteer');

async function getPic() {
    const browser = await puppeteer.launch({
    args: ['--user-agent=<user_agent_string>'],
    headless: true
    });

    const page = await browser.newPage();
    
    await page.goto('https://new.abb.com//channel-partners/search/_api/AbbPartners/Partners?skip=50&take=50');
    await page.screenshot({path: 'items/new2.png',fullPage:true});

    let result = await page.evaluate(() => {
        
        let name = document.querySelector('body').innerText;
        return name;

    });

    let counter = 2;
    let json_name = 'data/' + counter + '.json';
    const fs = require('fs');
    fs.writeFileSync(json_name, result);  

    await browser.close();
}

getPic();