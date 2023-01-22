const puppeteer = require('puppeteer');
const progressBar = require('cli-progress');
const fs = require('fs');

cleanExistingFiles('data/abb');
const folders = ['data','data/abb'];
ifNeededCreateFolder(folders);
getAbbContacts().then(() => {
    console.log('Datu izgūšana pabeigta');
});

async function getAbbContacts()
{
    const browser = await puppeteer.launch({
        args: ['--user-agent=<user_agent_string>'],
        headless: true
    });

    const page = await browser.newPage();
    await page.goto('https://new.abb.com//channel-partners/search/_api/AbbPartners/Partners');

    let raw_result = await page.evaluate(() => {
        let name = document.querySelector('body').innerText;
        return name;
    });

    let result = JSON.parse(raw_result);
    const bar1 = new progressBar.SingleBar({}, progressBar.Presets.shades_classic);

    bar1.start(result['Total'], 0);

    let controller = 0;
    let name_counter = 1;

    while(controller < result['Total']) {
        if (controller === 0) {
            await page.goto('https://new.abb.com//channel-partners/search/_api/AbbPartners/Partners');
        } else {
            await page.goto('https://new.abb.com//channel-partners/search/_api/AbbPartners/Partners?skip=' + controller + '&take=50');
        }

        let file_data = await page.evaluate(() => {
            let name = document.querySelector('body').innerText;
            return name;
        });

        let json_name = 'data/abb/' + name_counter + '.json';
        fs.writeFileSync(json_name, file_data);

        controller += 50;
        name_counter++;
        if (controller < result['Total']) {
            bar1.update(controller);
        } else {
            bar1.update(result['Total']);
        }
    }

    bar1.stop();
    await browser.close();
}

function cleanExistingFiles(file_path) {
    if (fs.existsSync(file_path)) {
        fs.rmSync(file_path, { recursive: true, force: true });
    }
}

function ifNeededCreateFolder(list) {
    list.forEach(element => {
        if (!fs.existsSync(element)) {
            fs.mkdirSync(element);
        }
    });
}