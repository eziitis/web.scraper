const puppeteer = require('puppeteer');
const fs = require('fs');

cleanExistingFiles('data/legrand');
const folders = ['data', 'data/legrand'];
ifNeededCreateFolder(folders);

getLegrandContacts().then(() => {
    console.log('Datu izgūšana pabeigta');
});

async function getLegrandContacts()
{
    const browser = await puppeteer.launch({
        args: ['--user-agent=<user_agent_string>'],
        headless: true
    });
    const page = await browser.newPage();
    await page.goto('https://www.legrandgroup.com/en/world-presence');

    const raw_result = await page.evaluate(() =>  Array.from(document.querySelectorAll('.address-card.width50')).map(elem => elem.className));
    let country_names = [];
    raw_result.forEach((element) => {
        element = element.replace('address-card width50 ','');
        element = element.replace('-','_');
        if (!country_names.includes(element)) {
            country_names.push(element);
        }
    });

    const json_contact_result = await page.evaluate((country_names) => {
        let data = {};
        let counter = 0;
        country_names.forEach((item) => {
            if (!data.hasOwnProperty(item)) {
                data[item] = {};
            }
            let query_selector = '.address-card.width50.' + item;
            let div_elements = document.querySelectorAll(query_selector);
            counter = 0;
            div_elements.forEach((div_element) => {
                data[item][counter] = {};
                data[item][counter]['company_name'] = getContactDetails(div_element.querySelector('h3').innerText);
                data[item][counter]['address'] = getContactDetails(div_element.querySelector('p').innerText);
                data[item][counter]['country'] = item;
                let divs = div_element.querySelectorAll('div');
                if (divs.length === 1) {
                    let link_test = divs[0].querySelectorAll('a');
                    if (link_test.length > 0) {
                        if (link_test.length === 1) {
                            let unknown = getContactDetails(link_test[0].innerText);
                            if (unknown.includes('@')) {
                                data[item][counter]['email'] = unknown;
                            } else {
                                data[item][counter]['website'] = unknown;
                            }
                        } else {
                            data[item][counter]['email'] = getContactDetails(link_test[0].innerText);
                            data[item][counter]['website'] = getContactDetails(link_test[1].innerText);
                        }
                    } else {
                        data[item][counter]['tel_and_fax'] = getContactDetails(divs[0].innerText, true);
                    }
                } else if (divs.length === 2) {
                    data[item][counter]['tel_and_fax'] = getContactDetails(divs[0].innerText, true);

                    let link_test = divs[1].querySelectorAll('a');
                    if (link_test.length === 1) {
                        let unknown = getContactDetails(link_test[0].innerText);
                        if (unknown.includes('@')) {
                            data[item][counter]['email'] = unknown;
                        } else {
                            data[item][counter]['website'] = unknown;
                        }
                    } else {
                        data[item][counter]['email'] = getContactDetails(link_test[0].innerText);
                        data[item][counter]['website'] = getContactDetails(link_test[1].innerText);
                    }
                }
                counter++;
            })
        });

        function getContactDetails(value, remove_dot = false) {
            value = value.replace('\n','');
            value = value.replace('\'','');
            value = value.replace('+','');
            if (remove_dot === true) {
                value = value.replace('.',' ');
            }
            value = value.replace(/\s+/g,' ').trim();
            return value;
        }
        return data;
    }, country_names);
    let result = JSON.stringify(json_contact_result);
    let json_name = 'data/legrand/contact_information_1.json';
    fs.writeFileSync(json_name, result);
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