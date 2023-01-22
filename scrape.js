//programmai nepieciešamās bibliotēkas (kas ir ieinstelētas ar npm (Node package manager))
const puppeteer = require('puppeteer'); //svarīgākā bibliotēka, lai varētu atvērt pārlūku un automatizēt darbības tajā
const progressBar = require('cli-progress'); //opcionāla bibliotēka, lai varētu vieglāk sekot līdzi progresam
const fs = require('fs'); //nepieciešama bibliotēka, lai varētu saglabāk iegūtos datus failos

//Galvenā daļa, kas palaiž atsevišķās funkcijas
cleanExistingFiles('data/abb'); //nodzēs esošos failus, lai neveidotos dublēšanās vai kādas neparedzētas kļūdas
cleanExistingFiles('data/se');
cleanExistingFiles('data/legrand');
const folders = ['data','data/abb', 'data/se', 'data/legrand'];
ifNeededCreateFolder(folders); //Izsauc funkciju, lai izveidotu mapes, kurās glabāsies iegūtie dati
getAbbContacts().then(() => { //Lai funkcijas nedarbotos paralēli(asinhronas funkcijas), tās tiek secīgi izsauktas, kad iepriekšējā ir pabeigta
    getLegrandContacts().then(() => {
        getSeContacts().then(() => {
            console.log('Datu izgūšana pabeigta');
        })
    })
});


//----------------------------------------------------------------------------------------------------------------
// Funkcijas
//----------------------------------------------------------------------------------------------------------------

async function getAbbContacts() //tikai asinhronās funkcijas var izmantot await atslēgvārdu
{
    const browser = await puppeteer.launch({
        args: ['--user-agent=<user_agent_string>'], //default vērtība, user-agent mainot pārlūkprogramma var šo instanci uztvert citādāk (pie atkārtotas programmas izmantošanas)
        headless: true //šis paramters norādā, ka visas darbības jāveic neuzsākot pārlukprogrammu (viss tiks paveikts fonā)
    });

    const page = await browser.newPage();
    await page.goto('https://new.abb.com//channel-partners/search/_api/AbbPartners/Partners');

    let raw_result = await page.evaluate(() => { //raw_result ir viss body saturs, kas tiek paņemts ar innerText
        let name = document.querySelector('body').innerText;
        return name;
    });

    let result = JSON.parse(raw_result); //Json.parse efektīvi pārveito raw_result par viegli apstrādājamiem datiem (jo augstākesošā adrese aizved uz ABB API, kas atgriež Json formatētus datus)
    const bar1 = new progressBar.SingleBar({}, progressBar.Presets.shades_classic); //status bar objekta izveide

    bar1.start(result['Total'], 0);

    let controller = 0;
    let name_counter = 1;

    while(controller < result['Total']) {
        if (controller === 0) { //izņēmuma gadijums kad controller = 0, jeb pirmo reizi ejot cauri ciklam
            await page.goto('https://new.abb.com//channel-partners/search/_api/AbbPartners/Partners');
        } else {
            await page.goto('https://new.abb.com//channel-partners/search/_api/AbbPartners/Partners?skip=' + controller + '&take=50');
        }

        let file_data = await page.evaluate(() => {
            let name = document.querySelector('body').innerText;
            return name;
        });

        let json_name = 'data/abb/' + name_counter + '.json';
        fs.writeFileSync(json_name, file_data); //sinhrona funkcija, kas izveido failu(ja tāds jau neeksistē) un uzraksta uz tā norādītos datus. Tiek pieņemts, ka funkcija tiks palaista vienu reizi un tieši šādam failam nevajadzētu eksistēt

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
        await page.goto('https://www.se.com/lv/lv/locate/api/partners/locations?config='+ country_id +'&sortType=companyName&sortDirection=up&countryCode=en&languageCode=en'); //izmantojot country_id un SE API, var uzzināt visus piesaistītos kontaktus vienam valsts kodam
        const contact_id_raw_data = await page.evaluate(() => {
            return document.querySelector('body').innerText;
        });

        let contact_id_data = JSON.parse(contact_id_raw_data);
        if (contact_id_data['partnerLocations']) { //pārbauda vai contact_id_data nav tukšs
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

async function getLegrandContacts()
{
    const browser = await puppeteer.launch({
        args: ['--user-agent=<user_agent_string>'],
        headless: true
    });
    const page = await browser.newPage();
    await page.goto('https://www.legrandgroup.com/en/world-presence');

    const raw_result = await page.evaluate(() =>  Array.from(document.querySelectorAll('.address-card.width50')).map(elem => elem.className)); //pirmais posms ir iegūt visus valstu nosaukumus, tos var iegūt atlasot visus elementus, kuriem ir klases 'address-card' un 'width50', trešā klase klāt būs valsts nosaukums
    let country_names = [];
    raw_result.forEach((element) => {
        element = element.replace('address-card width50 ',''); //elements izskatās, piemēram, šādi - 'address-card width50 canada'
        element = element.replace('-','_');
        if (!country_names.includes(element)) {
            country_names.push(element);
        }
    });

    const json_contact_result = await page.evaluate((country_names) => { //page.evaluate izpilda koda daļu apskatāmās lapas robežās (sākotnēji norādītā Legrand lapa), jo vajadzēs apstrādat tālākos lapas elementus
        let data = {};
        let counter = 0;
        country_names.forEach((item) => {
            if (!data.hasOwnProperty(item)) { //dati tiek glabāti json formātā, lai tos vēlāk ir vieglāk apstrādāt, koda ziņā, sanāk ka ir jāveido obekts
                data[item] = {};
            }
            let query_selector = '.address-card.width50.' + item; //sameklē vienam valsts nosaukumam visus div elementus kurā tas parādās
            let div_elements = document.querySelectorAll(query_selector);
            counter = 0;
            div_elements.forEach((div_element) => {
                data[item][counter] = {};
                data[item][counter]['company_name'] = getContactDetails(div_element.querySelector('h3').innerText);
                data[item][counter]['address'] = getContactDetails(div_element.querySelector('p').innerText);
                data[item][counter]['country'] = item;
                let divs = div_element.querySelectorAll('div');
                if (divs.length === 1) {
                    let link_test = divs[0].querySelectorAll('a'); //link_test meklē elementus a, jo hipersaite būs tikai epastam vai mājaslapai
                    if (link_test.length > 0) {
                        if (link_test.length === 1) { //jāprecizē vai ir gan epasts, gan mājaslapa, vai arī tikai viens no tiem
                            let unknown = getContactDetails(link_test[0].innerText);
                            if (unknown.includes('@')) { //meklē pēc simbola '@', jo tas noteikti parādīsies e-pasta adresē
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
                    data[item][counter]['tel_and_fax'] = getContactDetails(divs[0].innerText, true); //pēc mājaslapas uzbūves ir zināms, ka pirmais div elements būs ar telefona numuru/-iem

                    let link_test = divs[1].querySelectorAll('a'); //nākamais būs e-pasts un/vai mājaslapa, kas ir jāpārbauda, vai ir viens, vai abi
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

        function getContactDetails(value, remove_dot = false) { //koda labākai pārskatāmībai izveidota funkcija, kura bieži atkārtojās, attīra iegūto teksta daļu
            value = value.replace('\n','');
            value = value.replace('\'','');
            value = value.replace('+','');
            if (remove_dot === true) {
                value = value.replace('.',' ');
            }
            value = value.replace(/\s+/g,' ').trim(); //visā teksta apmērā noņem liekās (vairākreizes izmantotos) tukšos laukus
            return value;
        }
        return data;
    }, country_names);
    let result = JSON.stringify(json_contact_result); //no json formāta ir jāpārveido formātā, ko varētu rakstīt uz faila
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