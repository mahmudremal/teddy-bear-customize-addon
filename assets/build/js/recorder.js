!function(t){var e={};function i(s){if(e[s])return e[s].exports;var r=e[s]={i:s,l:!1,exports:{}};return t[s].call(r.exports,r,r.exports,i),r.l=!0,r.exports}i.m=t,i.c=e,i.d=function(t,e,s){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:s})},i.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var s=Object.create(null);if(i.r(s),Object.defineProperty(s,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)i.d(s,r,function(e){return t[e]}.bind(null,r));return s},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="",i(i.s=41)}({41:function(t,e,i){"use strict";i.r(e);var s=i(5),r=i(8);window.WaveSurfer=s.a,window.RecordPlugin=r.a},5:function(t,e,i){"use strict";function s(t,e,i,s){return new(i||(i=Promise))((function(r,n){function o(t){try{h(s.next(t))}catch(t){n(t)}}function a(t){try{h(s.throw(t))}catch(t){n(t)}}function h(t){var e;t.done?r(t.value):(e=t.value,e instanceof i?e:new i((function(t){t(e)}))).then(o,a)}h((s=s.apply(t,e||[])).next())}))}i.d(e,"a",(function(){return d})),"function"==typeof SuppressedError&&SuppressedError;const r={decode:function(t,e){return s(this,void 0,void 0,(function*(){const i=new AudioContext({sampleRate:e}),s=i.decodeAudioData(t);return s.finally((()=>i.close())),s}))},createBuffer:function(t,e){return"number"==typeof t[0]&&(t=[t]),function(t){const e=t[0];if(e.some((t=>t>1||t<-1))){const i=e.length;let s=0;for(let t=0;t<i;t++){const i=Math.abs(e[t]);i>s&&(s=i)}for(const e of t)for(let t=0;t<i;t++)e[t]/=s}}(t),{duration:e,length:t[0].length,sampleRate:t[0].length/e,numberOfChannels:t.length,getChannelData:e=>null==t?void 0:t[e],copyFromChannel:AudioBuffer.prototype.copyFromChannel,copyToChannel:AudioBuffer.prototype.copyToChannel}}},n={fetchBlob:function(t,e){return s(this,void 0,void 0,(function*(){return fetch(t,e).then((t=>t.blob()))}))}};class o{constructor(){this.listeners={}}on(t,e){return this.listeners[t]||(this.listeners[t]=new Set),this.listeners[t].add(e),()=>this.un(t,e)}once(t,e){const i=this.on(t,e),s=this.on(t,(()=>{i(),s()}));return i}un(t,e){this.listeners[t]&&(e?this.listeners[t].delete(e):delete this.listeners[t])}unAll(){this.listeners={}}emit(t,...e){this.listeners[t]&&this.listeners[t].forEach((t=>t(...e)))}}class a extends o{constructor(t){super(),t.media?this.media=t.media:this.media=document.createElement("audio"),t.autoplay&&(this.media.autoplay=!0),null!=t.playbackRate&&(this.media.playbackRate=t.playbackRate)}onMediaEvent(t,e,i){return this.media.addEventListener(t,e,i),()=>this.media.removeEventListener(t,e)}onceMediaEvent(t,e){return this.onMediaEvent(t,e,{once:!0})}getSrc(){return this.media.currentSrc||this.media.src||""}revokeSrc(){const t=this.getSrc();t.startsWith("blob:")&&URL.revokeObjectURL(t)}setSrc(t,e){if(this.getSrc()===t)return;this.revokeSrc();const i=e instanceof Blob?URL.createObjectURL(e):t;this.media.src=i,this.media.load()}destroy(){this.media.pause(),this.revokeSrc(),this.media.src="",this.media.load()}play(){return this.media.play()}pause(){this.media.pause()}isPlaying(){return this.media.currentTime>0&&!this.media.paused&&!this.media.ended}setTime(t){this.media.currentTime=t}getDuration(){return this.media.duration}getCurrentTime(){return this.media.currentTime}getVolume(){return this.media.volume}setVolume(t){this.media.volume=t}getMuted(){return this.media.muted}setMuted(t){this.media.muted=t}getPlaybackRate(){return this.media.playbackRate}setPlaybackRate(t,e){null!=e&&(this.media.preservesPitch=e),this.media.playbackRate=t}getMediaElement(){return this.media}setSinkId(t){return this.media.setSinkId(t)}}class h extends o{constructor(t){let e;if(super(),this.timeouts=[],this.isScrolling=!1,this.audioData=null,this.resizeObserver=null,this.isDragging=!1,this.options=t,"string"==typeof t.container?e=document.querySelector(t.container):t.container instanceof HTMLElement&&(e=t.container),!e)throw new Error("Container not found");this.parent=e;const[i,s]=this.initHtml();e.appendChild(i),this.container=i,this.scrollContainer=s.querySelector(".scroll"),this.wrapper=s.querySelector(".wrapper"),this.canvasWrapper=s.querySelector(".canvases"),this.progressWrapper=s.querySelector(".progress"),this.cursor=s.querySelector(".cursor"),this.initEvents()}initEvents(){this.wrapper.addEventListener("click",(t=>{const e=this.wrapper.getBoundingClientRect(),i=(t.clientX-e.left)/e.width;this.emit("click",i)})),this.initDrag(),this.scrollContainer.addEventListener("scroll",(()=>{const{scrollLeft:t,scrollWidth:e,clientWidth:i}=this.scrollContainer,s=t/e,r=(t+i)/e;this.emit("scroll",s,r)}));const t=this.createDelay(100);this.resizeObserver=new ResizeObserver((()=>{t((()=>this.reRender()))})),this.resizeObserver.observe(this.scrollContainer)}initDrag(){!function(t,e,i,s,r=5){let n=()=>{};if(!t)return n;t.addEventListener("pointerdown",(o=>{if(2===o.button)return;o.preventDefault(),o.stopPropagation();let a=o.clientX,h=o.clientY,l=!1;const c=s=>{s.preventDefault(),s.stopPropagation();const n=s.clientX,o=s.clientY;if(l||Math.abs(n-a)>=r||Math.abs(o-h)>=r){const{left:s,top:r}=t.getBoundingClientRect();l||(l=!0,null==i||i(a-s,h-r)),e(n-a,o-h,n-s,o-r),a=n,h=o}},d=t=>{l&&(t.preventDefault(),t.stopPropagation())},u=()=>{l&&(null==s||s()),n()};document.addEventListener("pointermove",c),document.addEventListener("pointerup",u),document.addEventListener("pointerleave",u),document.addEventListener("click",d,!0),n=()=>{document.removeEventListener("pointermove",c),document.removeEventListener("pointerup",u),document.removeEventListener("pointerleave",u),setTimeout((()=>{document.removeEventListener("click",d,!0)}),10)}}))}(this.wrapper,((t,e,i)=>{this.emit("drag",Math.max(0,Math.min(1,i/this.wrapper.clientWidth)))}),(()=>this.isDragging=!0),(()=>this.isDragging=!1))}getHeight(){return null==this.options.height?128:isNaN(Number(this.options.height))?"auto"===this.options.height&&this.parent.clientHeight||128:Number(this.options.height)}initHtml(){const t=document.createElement("div"),e=t.attachShadow({mode:"open"});return e.innerHTML=`\n      <style>\n        :host {\n          user-select: none;\n        }\n        :host .scroll {\n          overflow-x: auto;\n          overflow-y: hidden;\n          width: 100%;\n          position: relative;\n          touch-action: none;\n        }\n        :host .noScrollbar {\n          scrollbar-color: transparent;\n          scrollbar-width: none;\n        }\n        :host .noScrollbar::-webkit-scrollbar {\n          display: none;\n          -webkit-appearance: none;\n        }\n        :host .wrapper {\n          position: relative;\n          overflow: visible;\n          z-index: 2;\n        }\n        :host .canvases {\n          min-height: ${this.getHeight()}px;\n        }\n        :host .canvases > div {\n          position: relative;\n        }\n        :host canvas {\n          display: block;\n          position: absolute;\n          top: 0;\n          image-rendering: pixelated;\n        }\n        :host .progress {\n          pointer-events: none;\n          position: absolute;\n          z-index: 2;\n          top: 0;\n          left: 0;\n          width: 0;\n          height: 100%;\n          overflow: hidden;\n        }\n        :host .progress > div {\n          position: relative;\n        }\n        :host .cursor {\n          pointer-events: none;\n          position: absolute;\n          z-index: 5;\n          top: 0;\n          left: 0;\n          height: 100%;\n          border-radius: 2px;\n        }\n      </style>\n\n      <div class="scroll" part="scroll">\n        <div class="wrapper">\n          <div class="canvases"></div>\n          <div class="progress" part="progress"></div>\n          <div class="cursor" part="cursor"></div>\n        </div>\n      </div>\n    `,[t,e]}setOptions(t){this.options=t,this.reRender()}getWrapper(){return this.wrapper}getScroll(){return this.scrollContainer.scrollLeft}destroy(){var t;this.container.remove(),null===(t=this.resizeObserver)||void 0===t||t.disconnect()}createDelay(t=10){const e={};return this.timeouts.push(e),i=>{e.timeout&&clearTimeout(e.timeout),e.timeout=setTimeout(i,t)}}convertColorValues(t){if(!Array.isArray(t))return t||"";if(t.length<2)return t[0]||"";const e=document.createElement("canvas"),i=e.getContext("2d").createLinearGradient(0,0,0,e.height),s=1/(t.length-1);return t.forEach(((t,e)=>{const r=e*s;i.addColorStop(r,t)})),i}renderBars(t,e,i){if(i.fillStyle=this.convertColorValues(e.waveColor),e.renderFunction)return void e.renderFunction(t,i);const s=t[0],r=t[1]||t[0],n=s.length,o=window.devicePixelRatio||1,{width:a,height:h}=i.canvas,l=h/2,c=e.barHeight||1,d=e.barWidth?e.barWidth*o:1,u=e.barGap?e.barGap*o:e.barWidth?d/2:0,p=e.barRadius||0,m=a/(d+u)/n;let g=1;if(e.normalize){g=0;for(let t=0;t<n;t++){const e=Math.abs(s[t]);e>g&&(g=e)}}const v=l/g*c,f=p&&"roundRect"in i?"roundRect":"rect";i.beginPath();let y=0,b=0,w=0;for(let t=0;t<=n;t++){const n=Math.round(t*m);if(n>y){const t=Math.round(b*v),s=t+Math.round(w*v)||1;let r=l-t;"top"===e.barAlign?r=0:"bottom"===e.barAlign&&(r=h-s),i[f](y*(d+u),r,d,s,p),y=n,b=0,w=0}const o=Math.abs(s[t]||0),a=Math.abs(r[t]||0);o>b&&(b=o),a>w&&(w=a)}i.fill(),i.closePath()}renderSingleCanvas(t,e,i,s,r,n,o,a){const h=window.devicePixelRatio||1,l=document.createElement("canvas"),c=t[0].length;l.width=Math.round(i*(n-r)/c),l.height=s*h,l.style.width=`${Math.floor(l.width/h)}px`,l.style.height=`${s}px`,l.style.left=`${Math.floor(r*i/h/c)}px`,o.appendChild(l);const d=l.getContext("2d");this.renderBars(t.map((t=>t.slice(r,n))),e,d);const u=l.cloneNode();a.appendChild(u);const p=u.getContext("2d");l.width>0&&l.height>0&&p.drawImage(l,0,0),p.globalCompositeOperation="source-in",p.fillStyle=this.convertColorValues(e.progressColor),p.fillRect(0,0,l.width,l.height)}renderWaveform(t,e,i){const s=document.createElement("div"),r=this.getHeight();s.style.height=`${r}px`,this.canvasWrapper.style.minHeight=`${r}px`,this.canvasWrapper.appendChild(s);const n=s.cloneNode();this.progressWrapper.appendChild(n);const{scrollLeft:o,scrollWidth:a,clientWidth:l}=this.scrollContainer,c=t[0].length,d=c/a;let u=Math.min(h.MAX_CANVAS_WIDTH,l);if(e.barWidth||e.barGap){const t=e.barWidth||.5,i=t+(e.barGap||t/2);u%i!=0&&(u=Math.floor(u/i)*i)}const p=Math.floor(Math.abs(o)*d),m=Math.floor(p+u*d),g=m-p,v=(o,a)=>{this.renderSingleCanvas(t,e,i,r,Math.max(0,o),Math.min(a,c),s,n)},f=this.createDelay(),y=this.createDelay(),b=(t,e)=>{v(t,e),t>0&&f((()=>{b(t-g,e-g)}))},w=(t,e)=>{v(t,e),e<c&&y((()=>{w(t+g,e+g)}))};b(p,m),m<c&&w(m,m+g)}render(t){this.timeouts.forEach((t=>t.timeout&&clearTimeout(t.timeout))),this.timeouts=[],this.canvasWrapper.innerHTML="",this.progressWrapper.innerHTML="",this.wrapper.style.width="";const e=window.devicePixelRatio||1,i=this.scrollContainer.clientWidth,s=Math.ceil(t.duration*(this.options.minPxPerSec||0));this.isScrolling=s>i;const r=this.options.fillParent&&!this.isScrolling,n=(r?i:s)*e;if(this.wrapper.style.width=r?"100%":`${s}px`,this.scrollContainer.style.overflowX=this.isScrolling?"auto":"hidden",this.scrollContainer.classList.toggle("noScrollbar",!!this.options.hideScrollbar),this.cursor.style.backgroundColor=`${this.options.cursorColor||this.options.progressColor}`,this.cursor.style.width=`${this.options.cursorWidth}px`,this.options.splitChannels)for(let e=0;e<t.numberOfChannels;e++){const i=Object.assign(Object.assign({},this.options),this.options.splitChannels[e]);this.renderWaveform([t.getChannelData(e)],i,n)}else{const e=[t.getChannelData(0)];t.numberOfChannels>1&&e.push(t.getChannelData(1)),this.renderWaveform(e,this.options,n)}this.audioData=t,this.emit("render")}reRender(){if(!this.audioData)return;const t=this.progressWrapper.clientWidth;this.render(this.audioData);const e=this.progressWrapper.clientWidth;this.scrollContainer.scrollLeft+=e-t}zoom(t){this.options.minPxPerSec=t,this.reRender()}scrollIntoView(t,e=!1){const{clientWidth:i,scrollLeft:s,scrollWidth:r}=this.scrollContainer,n=r*t,o=i/2;if(n>s+(e&&this.options.autoCenter&&!this.isDragging?o:i)||n<s)if(this.options.autoCenter&&!this.isDragging){const t=o/20;n-(s+o)>=t&&n<s+i?this.scrollContainer.scrollLeft+=t:this.scrollContainer.scrollLeft=n-o}else if(this.isDragging){const t=10;this.scrollContainer.scrollLeft=n<s?n-t:n-i+t}else this.scrollContainer.scrollLeft=n;{const{scrollLeft:t}=this.scrollContainer,e=t/r,s=(t+i)/r;this.emit("scroll",e,s)}}renderProgress(t,e){isNaN(t)||(this.progressWrapper.style.width=100*t+"%",this.cursor.style.left=100*t+"%",this.cursor.style.marginLeft=100===Math.round(100*t)?`-${this.options.cursorWidth}px`:"",this.isScrolling&&this.options.autoScroll&&this.scrollIntoView(t,e))}}h.MAX_CANVAS_WIDTH=4e3;class l extends o{constructor(){super(...arguments),this.unsubscribe=()=>{}}start(){this.unsubscribe=this.on("tick",(()=>{requestAnimationFrame((()=>{this.emit("tick")}))})),this.emit("tick")}stop(){this.unsubscribe()}destroy(){this.unsubscribe()}}const c={waveColor:"#999",progressColor:"#555",cursorWidth:1,minPxPerSec:0,fillParent:!0,interact:!0,autoScroll:!0,autoCenter:!0,sampleRate:8e3};class d extends a{static create(t){return new d(t)}constructor(t){var e,i;super({media:t.media,autoplay:t.autoplay,playbackRate:t.audioRate}),this.plugins=[],this.decodedData=null,this.duration=null,this.subscriptions=[],this.options=Object.assign({},c,t),this.timer=new l,this.renderer=new h(this.options),this.initPlayerEvents(),this.initRendererEvents(),this.initTimerEvents(),this.initPlugins();const s=this.options.url||(null===(e=this.options.media)||void 0===e?void 0:e.currentSrc)||(null===(i=this.options.media)||void 0===i?void 0:i.src);s&&this.load(s,this.options.peaks,this.options.duration)}setOptions(t){this.options=Object.assign({},this.options,t),this.renderer.setOptions(this.options),t.audioRate&&this.setPlaybackRate(t.audioRate)}initTimerEvents(){this.subscriptions.push(this.timer.on("tick",(()=>{const t=this.getCurrentTime();this.renderer.renderProgress(t/this.getDuration(),!0),this.emit("timeupdate",t),this.emit("audioprocess",t)})))}initPlayerEvents(){this.subscriptions.push(this.onMediaEvent("timeupdate",(()=>{const t=this.getCurrentTime();this.renderer.renderProgress(t/this.getDuration(),this.isPlaying()),this.emit("timeupdate",t)})),this.onMediaEvent("play",(()=>{this.emit("play"),this.timer.start()})),this.onMediaEvent("pause",(()=>{this.emit("pause"),this.timer.stop()})),this.onMediaEvent("ended",(()=>{this.emit("finish")})),this.onMediaEvent("seeking",(()=>{this.emit("seeking",this.getCurrentTime())})))}initRendererEvents(){this.subscriptions.push(this.renderer.on("click",(t=>{this.options.interact&&(this.seekTo(t),this.emit("interaction",this.getCurrentTime()),this.emit("click",t))})),this.renderer.on("scroll",((t,e)=>{const i=this.getDuration();this.emit("scroll",t*i,e*i)})),this.renderer.on("render",(()=>{this.emit("redraw")})));{let t;this.subscriptions.push(this.renderer.on("drag",(e=>{this.options.interact&&(this.renderer.renderProgress(e),clearTimeout(t),t=setTimeout((()=>{this.seekTo(e)}),this.isPlaying()?0:200),this.emit("interaction",e*this.getDuration()),this.emit("drag",e))})))}}initPlugins(){var t;(null===(t=this.options.plugins)||void 0===t?void 0:t.length)&&this.options.plugins.forEach((t=>{this.registerPlugin(t)}))}registerPlugin(t){return t.init(this),this.plugins.push(t),this.subscriptions.push(t.once("destroy",(()=>{this.plugins=this.plugins.filter((e=>e!==t))}))),t}getWrapper(){return this.renderer.getWrapper()}getScroll(){return this.renderer.getScroll()}getActivePlugins(){return this.plugins}load(t,e,i){return s(this,void 0,void 0,(function*(){this.isPlaying()&&this.pause(),this.decodedData=null,this.duration=null,this.emit("load",t);const s=e?void 0:yield n.fetchBlob(t,this.options.fetchParams);if(this.setSrc(t,s),this.duration=i||this.getDuration()||(yield new Promise((t=>{this.onceMediaEvent("loadedmetadata",(()=>t(this.getDuration())))})))||0,e)this.decodedData=r.createBuffer(e,this.duration);else if(s){const t=yield s.arrayBuffer();this.decodedData=yield r.decode(t,this.options.sampleRate),0!==this.duration&&this.duration!==1/0||(this.duration=this.decodedData.duration)}this.emit("decode",this.duration),this.decodedData&&this.renderer.render(this.decodedData),this.emit("ready",this.duration)}))}zoom(t){if(!this.decodedData)throw new Error("No audio loaded");this.renderer.zoom(t),this.emit("zoom",t)}getDecodedData(){return this.decodedData}getDuration(){return null!==this.duration?this.duration:super.getDuration()}toggleInteraction(t){this.options.interact=t}seekTo(t){const e=this.getDuration()*t;this.setTime(e)}playPause(){return s(this,void 0,void 0,(function*(){return this.isPlaying()?this.pause():this.play()}))}stop(){this.pause(),this.setTime(0)}skip(t){this.setTime(this.getCurrentTime()+t)}empty(){this.load("",[[0]],.001)}destroy(){this.emit("destroy"),this.plugins.forEach((t=>t.destroy())),this.subscriptions.forEach((t=>t())),this.timer.destroy(),this.renderer.destroy(),super.destroy()}}},8:function(t,e,i){"use strict";i.d(e,"a",(function(){return o})),"function"==typeof SuppressedError&&SuppressedError;class s{constructor(){this.listeners={}}on(t,e){return this.listeners[t]||(this.listeners[t]=new Set),this.listeners[t].add(e),()=>this.un(t,e)}once(t,e){const i=this.on(t,e),s=this.on(t,(()=>{i(),s()}));return i}un(t,e){this.listeners[t]&&(e?this.listeners[t].delete(e):delete this.listeners[t])}unAll(){this.listeners={}}emit(t,...e){this.listeners[t]&&this.listeners[t].forEach((t=>t(...e)))}}class r extends s{constructor(t){super(),this.subscriptions=[],this.options=t}onInit(){}init(t){this.wavesurfer=t,this.onInit()}destroy(){this.emit("destroy"),this.subscriptions.forEach((t=>t()))}}const n=["audio/webm","audio/wav","audio/mpeg","audio/mp4","audio/mp3"];class o extends r{constructor(){super(...arguments),this.mediaRecorder=null,this.recordedUrl="",this.savedCursorWidth=1,this.savedInteractive=!0}static create(t){return new o(t||{})}preventInteraction(){this.wavesurfer&&(this.savedCursorWidth=this.wavesurfer.options.cursorWidth||1,this.savedInteractive=this.wavesurfer.options.interact||!0,this.wavesurfer.options.cursorWidth=0,this.wavesurfer.options.interact=!1)}restoreInteraction(){this.wavesurfer&&(this.wavesurfer.options.cursorWidth=this.savedCursorWidth,this.wavesurfer.options.interact=this.savedInteractive)}onInit(){this.preventInteraction()}loadBlob(t,e){var i;const s=new Blob(t,{type:e});this.recordedUrl=URL.createObjectURL(s),this.restoreInteraction(),null===(i=this.wavesurfer)||void 0===i||i.load(this.recordedUrl)}render(t){const e=new AudioContext({sampleRate:8e3}),i=e.createMediaStreamSource(t),s=e.createAnalyser();i.connect(s);const r=s.frequencyBinCount,n=new Float32Array(r),o=r/e.sampleRate;let a;const h=()=>{var t;s.getFloatTimeDomainData(n),null===(t=this.wavesurfer)||void 0===t||t.load("",[n],o),a=requestAnimationFrame(h)};return h(),()=>{a&&cancelAnimationFrame(a),i&&(i.disconnect(),i.mediaStream.getTracks().forEach((t=>t.stop()))),e&&e.close()}}cleanUp(){var t;this.stopRecording(),null===(t=this.wavesurfer)||void 0===t||t.empty(),this.recordedUrl&&(URL.revokeObjectURL(this.recordedUrl),this.recordedUrl="")}startRecording(){return function(t,e,i,s){return new(i||(i=Promise))((function(r,n){function o(t){try{h(s.next(t))}catch(t){n(t)}}function a(t){try{h(s.throw(t))}catch(t){n(t)}}function h(t){var e;t.done?r(t.value):(e=t.value,e instanceof i?e:new i((function(t){t(e)}))).then(o,a)}h((s=s.apply(t,e||[])).next())}))}(this,void 0,void 0,(function*(){let t;this.preventInteraction(),this.cleanUp();try{t=yield navigator.mediaDevices.getUserMedia({audio:!0})}catch(t){throw new Error("Error accessing the microphone: "+t.message)}const e=this.render(t),i=new MediaRecorder(t,{mimeType:this.options.mimeType||n.find((t=>MediaRecorder.isTypeSupported(t))),audioBitsPerSecond:this.options.audioBitsPerSecond}),s=[];i.addEventListener("dataavailable",(t=>{t.data.size>0&&s.push(t.data)})),i.addEventListener("stop",(()=>{e(),this.loadBlob(s,i.mimeType),this.emit("stopRecording")})),i.start(),this.emit("startRecording"),this.mediaRecorder=i}))}isRecording(){var t;return"recording"===(null===(t=this.mediaRecorder)||void 0===t?void 0:t.state)}stopRecording(){var t;this.isRecording()&&(null===(t=this.mediaRecorder)||void 0===t||t.stop())}getRecordedUrl(){return this.recordedUrl}destroy(){super.destroy(),this.cleanUp()}}}});