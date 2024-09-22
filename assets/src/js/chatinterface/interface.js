import axios from "axios";
import Post from "./post";


class Interface extends Post {
    constructor() {
        super();
        this.axios = axios;
        this.base_url = "http://localhost:1234/v1";
        this.elements = {};
        this.init_frontend();
        this.init_interface();
        this.init_interface_hooks();
    }
    init_frontend() {
        this.elements.root = document.body;
        this.elements.reserved = document.createElement('div');
        // Clean the previous root contents.
        this.elements.root.innerHTML = '';        
    }
    init_interface() {
        this.elements.sidebar = document.createElement('div');
        this.elements.sidebar.classList.add('sb');
        // 
        this.elements.leftSB = document.createElement('div');
        this.elements.leftSB.classList.add('sb-l');
        this.elements.sidebar.appendChild(this.elements.leftSB);
        // 
        this.elements.contentArea = document.createElement('div');
        this.elements.contentArea.classList.add('sb-c');
        var w = {};
        w.wr = document.createElement('div');
        w.wr.classList.add('sb-c-wr');
        w.h = document.createElement('div');
        w.h.classList.add('sb-c-wh');
        w.wr.appendChild(w.h);
        w.b = document.createElement('div');
        w.b.classList.add('sb-c-wb');
        this.chat_message_body(w.b);
        w.wr.appendChild(w.b);
        w.f = document.createElement('div');
        w.f.classList.add('sb-c-wf');
        w.fb = document.createElement('button');
        w.fb.classList.add('sb-c-wf-b');
        w.fb.addEventListener('click', (event) => {
            event.preventDefault();event.stopPropagation();
            this.askQuestion();
        });
        w.fb.innerHTML = 'Ask Question';
        w.f.appendChild(w.fb);
        w.wr.appendChild(w.f);
        // 
        this.elements.contentArea.appendChild(w.wr);
        this.elements.sidebar.appendChild(this.elements.contentArea);
        // 
        this.elements.rightSB = document.createElement('div');
        this.elements.rightSB.classList.add('sb-r');
        this.elements.sidebar.appendChild(this.elements.rightSB);
        // 
        this.elements.root.appendChild(this.elements.sidebar);
    }
    chat_message_body(body) {
        this.elements.chatBody = document.createElement('div');
        this.elements.chatBody.classList.add('sb-c-wb-r');
        this.elements.chatPreScrn = document.createElement('div');
        this.elements.chatPreScrn.classList.add('sb-c-wb-pre');
        this.elements.chatPreScrn.innerHTML = `
            <h2>Chat with a Large Language Model</h2>
            <p>Prompt a local LLM in a multi-turn chat format</p>
        `;
        this.add_action('print_messages', (messages) => {
            this.elements.reserved.appendChild(this.elements.chatPreScrn);
            if (messages?.role) {messages = [messages];}
            messages.map(message => {
                this.chat_message_block(message);
            });
        }, 0, 1);
        this.add_action('getting_chunks', (messages) => {
            this.elements.reserved.appendChild(this.elements.chatPreScrn);
            if (messages?.role) {messages = [messages];}
            messages.map(message => {
                message.isChunked = true;
                this.chat_message_block(message);
            });
        }, 0, 1);
        this.elements.chatBody.appendChild(this.elements.chatPreScrn);
        body.appendChild(this.elements.chatBody);
    }
    chat_message_block(message) {
        var chat = {};
        chat.blk = document.createElement('div');
        chat.blk.classList.add('ct-blk');
        if (message.isChunked) {chat.blk.classList.add('ct-blk-chunks');}
        chat.rw = document.createElement('div');
        chat.rw.classList.add('ct-rw');
        chat.av = document.createElement('div');
        chat.av.classList.add('ct-av');
        chat.avImg = document.createElement('img');
        chat.avImg.classList.add('ct-av-img');
        chat.avImg.src = this.get_avater_image(message.role, 'url');
        chat.av.appendChild(chat.avImg);
        // 
        chat.msgBlk = document.createElement('div');
        chat.msgBlk.classList.add('ct-msg-blk');
        chat.msgBlk.innerHTML = message.content;
        // 
        chat.msgActs = document.createElement('div');
        chat.msgActs.classList.add('ct-msg-acts');
        chat.msgCp = document.createElement('div');
        chat.msgCp.classList.add('ct-i', 'ct-i-cp');
        chat.msgCp.addEventListener('click', (event) => {
            event.preventDefault();event.stopPropagation();
            this.do_action('chat_message_pre_copy_message', chat, message);
            navigator.clipboard.writeText(
                this.apply_filters('chat_message_copy_message_text', chat, message)
            );
            this.do_action('chat_message_copy_message', chat, message);
        });
        chat.msgActs.appendChild(chat.msgCp);
        // 
        chat.msgPen = document.createElement('div');
        chat.msgPen.classList.add('ct-i', 'ct-i-pn');
        chat.msgPen.addEventListener('click', (event) => {
            event.preventDefault();event.stopPropagation();
            this.do_action('chat_message_pre_shift_edit', chat, message);
            var txtArea = document.createElement('textarea');
            chat.msgBlk.innerHTML = '';txtArea.value = message?.content??'';
            txtArea.addEventListener('keypress', (event) => {
                if (event.keyCode === 13 && event.shiftKey == false) {
                    this.do_action('chat_message_pre_edit', chat, message);
                    var text = this.apply_filters('chat_message_edit_text', txtArea.value, message, chat);
                    txtArea.remove();chat.msgBlk.innerHTML = text;
                    this.do_action('chat_message_edited', chat, message);
                }
            })
            chat.msgBlk.appendChild(txtArea);
            this.do_action('chat_message_shift_edit', chat, message);
        });
        chat.msgActs.appendChild(chat.msgPen);
        // 
        chat.msgTr = document.createElement('div');
        chat.msgTr.classList.add('ct-i', 'ct-i-tr');
        chat.msgTr.addEventListener('click', (event) => {
            event.preventDefault();event.stopPropagation();
            this.do_action('chat_message_pre_delete', chat, message);
            chat.blk.remove();
            this.do_action('chat_message_deleted', chat, message);
        });
        chat.msgActs.appendChild(chat.msgTr);
        // 
        chat.rw.appendChild(chat.av);
        chat.rw.appendChild(chat.msgBlk);
        chat.rw.appendChild(chat.msgActs);
        chat.blk.appendChild(chat.rw);
        // 
        this.elements.chatBody.appendChild(chat.blk);
        if (message?.isChunked) {
            this.elements.chatChunks = chat.blk;
        }
        return chat;
    }
    get_avater_image(role, type = 'url') {
        return 'https://dubido.local/wp-content/uploads/2023/07/cropped-chrome_hmhnlu2d1b-1-180x180.png';
        // return location.href;
    }

    init_interface_hooks() {
        this.add_action('cleanup_chunks', (message) => {
            this.elements.chatChunks.remove();
        });
        this.add_action('cleared_chat_history', (message) => {
            this.elements.chatBody.innerHTML = '';
            this.elements.chatBody.appendChild(this.elements.chatPreScrn);
        });
    }
}

export default Interface;