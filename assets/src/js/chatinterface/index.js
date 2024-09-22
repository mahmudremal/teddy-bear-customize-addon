/**
 * 
 */

import Interface from "./interface";

class Chat extends Interface {
    constructor() {
        super();
        this.history = [];
        // 
        window.Chat = this;
        this.init_chat_hooks();
    }
    read_file(path) {
        return new Promise((resolve, reject) => {
            // 
            fetch(path).then(res => res.json()).then(res => resolve(res)).catch(err => console.error(err));
            // 
        });
    }
    get_models() {
        return new Promise((resolve, reject) => {
            // 
            fetch(`${this.base_url}/models`).then(res => res.json()).then(res => resolve(res)).catch(err => console.error(err));
            // 
        });
    }
    init_chat_hooks() {
        this.add_action('chat_register_history', (response, prompt, images, data) => {
            var userMsg = data.messages.pop();
            if (! userMsg?.id) {userMsg.id = Date.now();}
            this.history.push(userMsg);
            var resMsg = response.data.choices[0].message;
            if (! resMsg?.id) {resMsg.id = userMsg.id + 1;}
            this.history.push(resMsg);
            this.do_action('print_messages', [resMsg], response, prompt, images, data);
        }, 10, 4);
        this.add_action('chat_stream_chunk', (chunk, prompt, images, data) => {
            console.log(chunk, prompt, images, data);
        }, 10, 4);
    }

    imageUrlToBase64(imageUrl) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            // img.cors = 'anonymous';
            // img.crossOrigin = 'anonymous';
            img.onload = () => {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                const dataURL = canvas.toDataURL('image/png');
                canvas.remove();resolve(dataURL);
            };
            img.onerror = () => {
                reject(new Error(`Error loading image: ${imageUrl}`));
            };
            img.src = imageUrl;
        });
    }

    get_completions(text, prompt = '', images = []) {
        return new Promise(async (resolve, reject) => {
            const url = `${this.base_url}/chat/completions`;
            if (! this?.models) {
                await this.get_models().then(json => json.data).then(models => models.map(model => model.id)).then(models => this.models = models).catch(error => reject(error));
            }
            const messages = [
                { "role": "system", "content": prompt.trim() == ''?"You are a helpful, smart, kind, and efficient AI assistant. You always fulfill the user's requests to the best of your ability.":prompt},
                ...this.history.map(chat => {
                    return {role: chat.role, content: chat.content};
                }),
                { "role": "user", "content": text}
            ];
            if (typeof images == 'string') {images = [images];}
            images = Object.values(images);
            await images.map(async image => {
                var dataImage = image;
                if (!image.startsWith('data:') && URL.canParse(image)) {
                    dataImage = await this.imageUrlToBase64(image);
                }
                return dataImage;
            }).map(image => {
                var lastMsg = messages.pop();
                if (typeof lastMsg.content == 'string') {
                    lastMsg.content = [{type: 'text', content: lastMsg.content}];
                }
                lastMsg.content.push({type: 'image_url', image_url: {url: image}});
                return image;
            })
            const data = {
                model: this.models[0],
                messages: messages,
                temperature: 0.7,
                max_tokens: -1,
                stream: false
            };
            if (data?.stream) {
                var chunks = {role: messages.at(-2).role, content: ''};
                this.do_action('getting_chunks', [chunks]);
            }
            this.axios.post(url, JSON.stringify(
                this.apply_filters('chat_completion_args', data, prompt, images)
            ), {
                responseType: 'stream',
                headers: {'Content-Type': 'application/json'},
                onUploadProgress: ({progress, rate}) => {
                    this.do_action('on_upload_progress', data, progress, rate);
                    console.log(`Upload [${(progress*100).toFixed(2)}%]: ${(rate / 1024).toFixed(2)}KB/s`)
                },
            })
            .then(response => {
                if (data?.stream) {
                    const reader = response.data.getReader();
                    const decoder = new TextDecoder('utf-8');
                    let result = '';

                    const readStream = () => {
                        reader.read().then(({ done, value }) => {
                            if (done) {
                                this.do_action('chat_completion_response', result, prompt, images, data);
                                this.do_action('chat_register_history', result, prompt, images, data);
                                this.do_action('cleanup_chunks', result);
                                resolve(result);
                                return;
                            }

                            const chunk = decoder.decode(value, { stream: true });
                            result += chunk;

                            this.do_action('chat_stream_chunk', chunk, prompt, images, data);
                            readStream();
                        }).catch(error => reject(error));
                    };
                    readStream();
                } else {
                    this.do_action('chat_completion_response', response, prompt, images, data);
                    this.do_action('chat_register_history', response, prompt, images, data);
                    this.do_action('cleanup_chunks', response.data.choices[0].message);
                    resolve(response.data);
                }
            })
            .catch(error => reject(error));
            // 
        });
    }

    askQuestion(ans = false, ask = '') {
        ask = prompt((ans == false) ? 'Ask Question' : `Your Question: ${ask}\n\nYour Answer: ${ans}`);
        if (!ask || ask == '') {return;}
        if (ask.startsWith('/')) {
            if (ask.startsWith('/clear')) {
                this.do_action('clear_chat_history_before');
                this.history = [];
                this.do_action('cleared_chat_history');
                alert('Chat history cleared!');
                this.askQuestion();
            } else if (ask.startsWith('/read')) {
                var file_path = prompt('Enter full file path: ');
                fetch(`http://testbed.local/wp-json/flutterwave/v1/filesystem?readfile=${file_path}`).then(data => data.json()).then(data => {
                    console.log(data.file_contents)
                    this.history.push({role: 'assistant', content: `File contents for the path ${file_path} is: \n${data.file_contents}`});
                    this.askQuestion();
                }).catch(err => console.error(err));
            } else {
                alert('Action didn\'t matched with any params! Please follow these params \n\r/clear for clean chat history\n\r/read for reading file content\n\r');
                this.askQuestion();
            }
        } else {
            this.get_completions(ask).then(data => this.askQuestion(data?.choices[0].message.content, ask)).catch(err => console.error(err));
        }
    }
    
    example_usage() {
        
    }
}
new Chat();











// var loadScript = (src) => {
//     return new Promise((resolve, reject) => {
//         var script = document.createElement('script');
//         script.src = src;script.type = 'text/javascript';
//         script.onload = () => {resolve();};
//         script.onerror = () => {reject();};
//         document.head.appendChild(script);
//     });
// }
// var loadStyle = (src) => {
//     return new Promise((resolve, reject) => {
//         var style = document.createElement('link');
//         style.rel = 'stylesheet';style.media = 'all';
//         style.href = src;
//         style.onload = () => {resolve();};
//         style.onerror = () => {reject();};
//         document.head.appendChild(style);
//     });
// }
// loadScript('https://dubido.local/wp-includes/js/jquery/jquery.min.js').then((res) => {
//     loadScript('https://dubido.local/wp-content/plugins/teddy/assets/build/js/chat.js?ver=' + Math.random(0, 9999)).then(res => {
//         loadStyle('https://dubido.local/wp-content/plugins/teddy/assets/build/css/chat.css?ver=' + Math.random(0, 9999)).then(res => {
//             setTimeout(() => {Chat.askQuestion();}, 1500);
//         }).catch(err => console.error(err));
        
//     }).catch(err => console.error(err));
// }).catch(error => console.error(error));