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