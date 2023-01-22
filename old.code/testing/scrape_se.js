const puppeteer = require('puppeteer');
const progressBar = require('cli-progress');
const fs = require('fs');

cleanExistingFiles('data/se');
const folders = ['data','data/se'];
ifNeededCreateFolder(folders);
getSeContacts().then(() => {
    console.log('Datu izgūšana pabeigta');
});

async function getSeContacts()
{
    const browser = await puppeteer.launch({
        args: ['--user-agent=<user_agent_string>'],
        headless: true
    });
    const page = await browser.newPage();
    await page.goto('https://www.se.com/ww/en/locate/api/partners/locations-group/?config=395&languageCode=en');

    const raw_result = await page.evaluate(() => {
        return document.querySelector('body').innerText;
    });
    let result = JSON.parse(raw_result);
    const bar1 = new progressBar.SingleBar({}, progressBar.Presets.shades_classic);

    bar1.start(result['references']['countriesIds'].length, 0);
    let counter = 0;

    for (let i = 0; i < result['references']['countriesIds'].length; i++) {
        let country_id = result['references']['countriesIds'][i];
        await page.goto('https://www.se.com/lv/lv/locate/api/partners/locations?config='+ country_id +'&sortType=companyName&sortDirection=up&countryCode=en&languageCode=en');
        const contact_id_raw_data = await page.evaluate(() => {
            return document.querySelector('body').innerText;
        });

        let contact_id_data = JSON.parse(contact_id_raw_data);
        if (contact_id_data['partnerLocations']) {
            let inner_counter = 0;
            let contact_id = 0;
            while (inner_counter < contact_id_data['partnerLocations'].length) {
                contact_id = contact_id_data['partnerLocations'][inner_counter]['id'];
                await page.goto('https://www.se.com/ww/en/locate/api/partners/id-list/?id=' + contact_id + '&configurationId=64&languageCode=en&countryCode=uk&ts=1671005912970');
                const raw_contact_information = await page.evaluate(() => {
                    return document.querySelector('body').innerText;
                });

                let json_name = 'data/se/' + country_id + '/contact_information_' + inner_counter + '.json';
                if (!fs.existsSync('data/se/'+ country_id)) {
                    fs.mkdirSync('data/se/' + country_id);
                }
                fs.writeFileSync(json_name, raw_contact_information);

                inner_counter++;
            }
        }
        counter++;
        bar1.update(counter);
    }
    bar1.update(result['partnerLocations'].length);
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