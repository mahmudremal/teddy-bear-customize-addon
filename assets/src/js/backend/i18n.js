import icons from "../frontend/icons"
class i18nForm {
    constructor() {
        this.translations = [];
        this.setup_hooks(thisClass);
    }
    setup_hooks(thisClass) {
        this.init_events(thisClass);
    }
    init_events(thisClass) {
        const formClass = this;
        document.body.addEventListener('ajaxList-success', async (event) => {
            formClass.translations = thisClass.lastJson?.list??[];
            formClass.print_table(thisClass);
        });
        document.body.addEventListener('updateI18nList-failed', async (event) => {
            thisClass.Swal.hideLoading();
        });
        document.body.addEventListener('updateI18nList-success', async (event) => {
            formClass.translations = thisClass.lastJson?.list??[];
            thisClass.Swal.close();
        });
        if (document.querySelectorAll('input#repeater_translate').length >= 1) {
            this.getList(thisClass);
        }
    }
    getList(thisClass) {
        const formClass = this;
        var formdata = new FormData();
        formdata.append('action', 'futurewordpress/project/ajax/i18n/list');
        formdata.append('_nonce', thisClass.ajaxNonce);
        thisClass.sendToServer(formdata);
    }
    print_table(thisClass) {
        const formClass = this;
        document.querySelectorAll('input#repeater_translate').forEach(el => {
            el.addEventListener('click', (event) => {
                const tform = document.createElement('form');tform.classList.add('form-tform');
                const table = document.createElement('table');table.classList.add('form-table');
                const tbody = document.createElement('tbody');tbody.classList.add('form-tbody');
                tform.method = 'POST';tform.action = thisClass.ajaxUrl;

                Object.values(formClass.translations).forEach((row, index) => {
                    if (! row?.he_IL) {row.he_IL = '';}
                    var tr = document.createElement('tr');tr.classList.add('form-tr');
                    var td = document.createElement('td');td.classList.add('form-td');
                    var trash = document.createElement('span');trash.classList.add('form-trash');
                    trash.addEventListener('click', (event) => {
                        if (confirm(thisClass.i18n?.rusure??'Are you sure?')) {
                            trash.parentElement.parentElement.remove();
                        }
                    });
                    trash.innerHTML = icons.trash;td.appendChild(trash);
                    // 
                    Object.keys(row).forEach(langCode => {
                        var label = document.createElement('label');label.classList.add('form-label');
                        var span = document.createElement('span');span.classList.add('form-span');
                        var input = document.createElement('textarea');input.classList.add('form-input');
                        input.id = `i18n-${index}-${langCode}`;label.for = input.id;
                        input.name = `i18n[${index}][${langCode}]`;// input.type = 'text';
                        span.innerHTML = langCode.toUpperCase();label.appendChild(span);
                        input.innerHTML = row[langCode];label.appendChild(input);td.appendChild(label);
                    });
                    // 
                    tr.appendChild(td);tbody.appendChild(tr);
                });
                table.appendChild(tbody);tform.appendChild(table);

                thisClass.Swal.fire({
                    width: 800,
                    showCloseButton: true,
                    showCancelButton: true,
                    allowOutsideClick: false,
                    showConfirmButton: true,
                    cancelButtonColor: '#d33',
                    showLoaderOnConfirm: true,
                    confirmButtonColor: '#3085d6',
                    backdrop: 'rgba(0,0,123,0.4)',
                    title: thisClass.i18n?.translations??'Translations',
                    cancelButtonText: thisClass.i18n?.cancel??'Cancel',
                    confirmButtonText: thisClass.i18n?.submit??'Submit',
    
                    html: '<div class="fwp-i18n-popup"></div>',
                    didOpen: async () => {
                        document.querySelector('.fwp-i18n-popup').appendChild(tform);
                    },
                    preConfirm: async (login) => {
                        thisClass.Swal.showLoading();
                        return await new Promise((resolve) => {
                            setTimeout(() => {
                                var formdata = new FormData(tform);
                                formdata.append('action', 'futurewordpress/project/ajax/i18n/update');
                                formdata.append('_nonce', thisClass.ajaxNonce);
                                thisClass.sendToServer(formdata);
                                resolve();
                            }, 3000);
                        });
                    },
                    preDeny: () => confirm('Are you sure?'), // async (login) => {return false;}
                }).then(async (result) => {
                })
                
            });
        });
    }
    async request(thisClass) {
        const Jsonlist = [
            {
                "en_US": "Choose a Heart",
                "he_IL": "בחרו לב"
            },
            {
                "en_US": "Heart",
                "he_IL": "לב הלב"
            },
            {
                "en_US": "Choose a Heart",
                "he_IL": "בחרו לב"
            },
            {
                "en_US": "when you hug your DubiDo you'll feel its heartbeat!",
                "he_IL": "כשאתם מחבקים את הדובי שלכם אתם תרגישו את פעימות הלב שלכם!"
            },
            {
                "en_US": "Add your voice  +$9.95",
                "he_IL": "הוסף את הקול + $9.95"
            },
            {
                "en_US": " Record your voice",
                "he_IL": "רשום את הקול שלך"
            },
            {
                "en_US": "Input label",
                "he_IL": "תוויות Input"
            },
            {
                "en_US": "Choose a main outfit and footwear now. You can add more at checkout!",
                "he_IL": "בחרו עכשיו שמלה ונעליים. אתה יכול להוסיף עוד לבדיקה!"
            },
            {
                "en_US": "Placeholder text",
                "he_IL": "מקור טקסט"
            },
            {
                "en_US": "Choose an outfit",
                "he_IL": "בחרו שמלה"
            },
            {
                "en_US": "Field descriptions.",
                "he_IL": "תיאור שדה."
            },
            {
                "en_US": "Help us fill the details of your DubiDo with 4 quick questions",
                "he_IL": "עזרו לנו למלא את פרטי הדובי שלכם עם 4 שאלות מהירות"
            },
            {
                "en_US": "issue a certificate",
                "he_IL": "נושא תעודה"
            },
            {
                "en_US": "After your purchase is completed we'll send you your printable birth certificate as a pdf via email.",
                "he_IL": "לאחר השלמת הרכישה, אנו נשלח לך את תעודת הלידה המודפסת שלך כ- PDF באמצעות דואר אלקטרוני."
            }
        ];
        const target = 'he_IL';
        const from = 'en_US';
        await Jsonlist.map(async (row, index) => {
            var text = row[from];
            if (text && typeof row[target] === 'undefined' || row[target] == '') {
            row[target] = await fetch("https://libretranslate.com/translate", {
                "headers": {
                "accept": "*/*",
                "accept-language": "en-GB,en-US;q=0.9,en;q=0.8,bn;q=0.7",
                "cache-control": "no-cache",
                "content-type": "multipart/form-data; boundary=----WebKitFormBoundaryI5ne28WeiqBTE56N",
                "pragma": "no-cache",
                "sec-ch-ua": "\"Not_A Brand\";v=\"8\", \"Chromium\";v=\"120\", \"Google Chrome\";v=\"120\"",
                "sec-ch-ua-mobile": "?0",
                "sec-ch-ua-platform": "\"Windows\"",
                "sec-fetch-dest": "empty",
                "sec-fetch-mode": "cors",
                "sec-fetch-site": "same-origin"
                },
                "referrer": "https://libretranslate.com/?source=en&target=he&q=okay",
                "referrerPolicy": "strict-origin-when-cross-origin",
                "body": "------WebKitFormBoundaryI5ne28WeiqBTE56N\r\nContent-Disposition: form-data; name=\"q\"\r\n\r\n"+text+"\r\n------WebKitFormBoundaryI5ne28WeiqBTE56N\r\nContent-Disposition: form-data; name=\"source\"\r\n\r\n"+from+ "\r\n------WebKitFormBoundaryI5ne28WeiqBTE56N\r\nContent-Disposition: form-data; name=\"target\"\r\n\r\n"+target+"\r\n------WebKitFormBoundaryI5ne28WeiqBTE56N\r\nContent-Disposition: form-data; name=\"format\"\r\n\r\ntext\r\n------WebKitFormBoundaryI5ne28WeiqBTE56N\r\nContent-Disposition: form-data; name=\"api_key\"\r\n\r\n\r\n------WebKitFormBoundaryI5ne28WeiqBTE56N\r\nContent-Disposition: form-data; name=\"secret\"\r\n\r\n8OHGRHI\r\n------WebKitFormBoundaryI5ne28WeiqBTE56N--\r\n",
                "method": "POST",
                "mode": "cors",
                "credentials": "include"
            })
            .then(res => res.json()).then(json => {
                Jsonlist[index][target] = json.translatedText;
                return json.translatedText;
            })
            }
            return row;
        })
    }
}


export default i18nForm;