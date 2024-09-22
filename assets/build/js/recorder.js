/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/js/recorder.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/wavesurfer.js/dist/plugins/record.esm.js":
/*!***************************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/plugins/record.esm.js ***!
  \***************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return i; });
function e(e,t,r,s){return new(r||(r=Promise))((function(i,n){function o(e){try{d(s.next(e))}catch(e){n(e)}}function a(e){try{d(s.throw(e))}catch(e){n(e)}}function d(e){var t;e.done?i(e.value):(t=e.value,t instanceof r?t:new r((function(e){e(t)}))).then(o,a)}d((s=s.apply(e,t||[])).next())}))}"function"==typeof SuppressedError&&SuppressedError;class t{constructor(){this.listeners={}}on(e,t){return this.listeners[e]||(this.listeners[e]=new Set),this.listeners[e].add(t),()=>this.un(e,t)}once(e,t){const r=this.on(e,t),s=this.on(e,(()=>{r(),s()}));return r}un(e,t){this.listeners[e]&&(t?this.listeners[e].delete(t):delete this.listeners[e])}unAll(){this.listeners={}}emit(e,...t){this.listeners[e]&&this.listeners[e].forEach((e=>e(...t)))}}class r extends t{constructor(e){super(),this.subscriptions=[],this.options=e}onInit(){}init(e){this.wavesurfer=e,this.onInit()}destroy(){this.emit("destroy"),this.subscriptions.forEach((e=>e()))}}const s=["audio/webm","audio/wav","audio/mpeg","audio/mp4","audio/mp3"];class i extends r{constructor(){super(...arguments),this.mediaRecorder=null,this.recordedUrl="",this.savedCursorWidth=1,this.savedInteractive=!0}static create(e){return new i(e||{})}preventInteraction(){this.wavesurfer&&(this.savedCursorWidth=this.wavesurfer.options.cursorWidth||1,this.savedInteractive=this.wavesurfer.options.interact||!0,this.wavesurfer.options.cursorWidth=0,this.wavesurfer.options.interact=!1)}restoreInteraction(){this.wavesurfer&&(this.wavesurfer.options.cursorWidth=this.savedCursorWidth,this.wavesurfer.options.interact=this.savedInteractive)}onInit(){this.preventInteraction()}loadBlob(e,t){var r;const s=new Blob(e,{type:t});this.recordedUrl=URL.createObjectURL(s),this.restoreInteraction(),null===(r=this.wavesurfer)||void 0===r||r.load(this.recordedUrl)}render(e){const t=new AudioContext({sampleRate:8e3}),r=t.createMediaStreamSource(e),s=t.createAnalyser();r.connect(s);const i=s.frequencyBinCount,n=new Float32Array(i),o=i/t.sampleRate;let a;const d=()=>{var e;s.getFloatTimeDomainData(n),null===(e=this.wavesurfer)||void 0===e||e.load("",[n],o),a=requestAnimationFrame(d)};return d(),()=>{a&&cancelAnimationFrame(a),r&&(r.disconnect(),r.mediaStream.getTracks().forEach((e=>e.stop()))),t&&t.close()}}cleanUp(){var e;this.stopRecording(),null===(e=this.wavesurfer)||void 0===e||e.empty(),this.recordedUrl&&(URL.revokeObjectURL(this.recordedUrl),this.recordedUrl="")}startRecording(){return e(this,void 0,void 0,(function*(){let e;this.preventInteraction(),this.cleanUp();try{e=yield navigator.mediaDevices.getUserMedia({audio:!0})}catch(e){throw new Error("Error accessing the microphone: "+e.message)}const t=this.render(e),r=new MediaRecorder(e,{mimeType:this.options.mimeType||s.find((e=>MediaRecorder.isTypeSupported(e))),audioBitsPerSecond:this.options.audioBitsPerSecond}),i=[];r.addEventListener("dataavailable",(e=>{e.data.size>0&&i.push(e.data)})),r.addEventListener("stop",(()=>{t(),this.loadBlob(i,r.mimeType),this.emit("stopRecording")})),r.start(),this.emit("startRecording"),this.mediaRecorder=r}))}isRecording(){var e;return"recording"===(null===(e=this.mediaRecorder)||void 0===e?void 0:e.state)}stopRecording(){var e;this.isRecording()&&(null===(e=this.mediaRecorder)||void 0===e||e.stop())}getRecordedUrl(){return this.recordedUrl}destroy(){super.destroy(),this.cleanUp()}}


/***/ }),

/***/ "./node_modules/wavesurfer.js/dist/wavesurfer.esm.js":
/*!***********************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/wavesurfer.esm.js ***!
  \***********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return h; });
function t(t,e,i,s){return new(i||(i=Promise))((function(n,r){function o(t){try{h(s.next(t))}catch(t){r(t)}}function a(t){try{h(s.throw(t))}catch(t){r(t)}}function h(t){var e;t.done?n(t.value):(e=t.value,e instanceof i?e:new i((function(t){t(e)}))).then(o,a)}h((s=s.apply(t,e||[])).next())}))}"function"==typeof SuppressedError&&SuppressedError;const e={decode:function(e,i){return t(this,void 0,void 0,(function*(){const t=new AudioContext({sampleRate:i}),s=t.decodeAudioData(e);return s.finally((()=>t.close())),s}))},createBuffer:function(t,e){return"number"==typeof t[0]&&(t=[t]),function(t){const e=t[0];if(e.some((t=>t>1||t<-1))){const i=e.length;let s=0;for(let t=0;t<i;t++){const i=Math.abs(e[t]);i>s&&(s=i)}for(const e of t)for(let t=0;t<i;t++)e[t]/=s}}(t),{duration:e,length:t[0].length,sampleRate:t[0].length/e,numberOfChannels:t.length,getChannelData:e=>null==t?void 0:t[e],copyFromChannel:AudioBuffer.prototype.copyFromChannel,copyToChannel:AudioBuffer.prototype.copyToChannel}}};const i={fetchBlob:function(e,i){return t(this,void 0,void 0,(function*(){return fetch(e,i).then((t=>t.blob()))}))}};class s{constructor(){this.listeners={}}on(t,e){return this.listeners[t]||(this.listeners[t]=new Set),this.listeners[t].add(e),()=>this.un(t,e)}once(t,e){const i=this.on(t,e),s=this.on(t,(()=>{i(),s()}));return i}un(t,e){this.listeners[t]&&(e?this.listeners[t].delete(e):delete this.listeners[t])}unAll(){this.listeners={}}emit(t,...e){this.listeners[t]&&this.listeners[t].forEach((t=>t(...e)))}}class n extends s{constructor(t){super(),t.media?this.media=t.media:this.media=document.createElement("audio"),t.autoplay&&(this.media.autoplay=!0),null!=t.playbackRate&&(this.media.playbackRate=t.playbackRate)}onMediaEvent(t,e,i){return this.media.addEventListener(t,e,i),()=>this.media.removeEventListener(t,e)}onceMediaEvent(t,e){return this.onMediaEvent(t,e,{once:!0})}getSrc(){return this.media.currentSrc||this.media.src||""}revokeSrc(){const t=this.getSrc();t.startsWith("blob:")&&URL.revokeObjectURL(t)}setSrc(t,e){if(this.getSrc()===t)return;this.revokeSrc();const i=e instanceof Blob?URL.createObjectURL(e):t;this.media.src=i,this.media.load()}destroy(){this.media.pause(),this.revokeSrc(),this.media.src="",this.media.load()}play(){return this.media.play()}pause(){this.media.pause()}isPlaying(){return this.media.currentTime>0&&!this.media.paused&&!this.media.ended}setTime(t){this.media.currentTime=t}getDuration(){return this.media.duration}getCurrentTime(){return this.media.currentTime}getVolume(){return this.media.volume}setVolume(t){this.media.volume=t}getMuted(){return this.media.muted}setMuted(t){this.media.muted=t}getPlaybackRate(){return this.media.playbackRate}setPlaybackRate(t,e){null!=e&&(this.media.preservesPitch=e),this.media.playbackRate=t}getMediaElement(){return this.media}setSinkId(t){return this.media.setSinkId(t)}}class r extends s{constructor(t){let e;if(super(),this.timeouts=[],this.isScrolling=!1,this.audioData=null,this.resizeObserver=null,this.isDragging=!1,this.options=t,"string"==typeof t.container?e=document.querySelector(t.container):t.container instanceof HTMLElement&&(e=t.container),!e)throw new Error("Container not found");this.parent=e;const[i,s]=this.initHtml();e.appendChild(i),this.container=i,this.scrollContainer=s.querySelector(".scroll"),this.wrapper=s.querySelector(".wrapper"),this.canvasWrapper=s.querySelector(".canvases"),this.progressWrapper=s.querySelector(".progress"),this.cursor=s.querySelector(".cursor"),this.initEvents()}initEvents(){this.wrapper.addEventListener("click",(t=>{const e=this.wrapper.getBoundingClientRect(),i=(t.clientX-e.left)/e.width;this.emit("click",i)})),this.initDrag(),this.scrollContainer.addEventListener("scroll",(()=>{const{scrollLeft:t,scrollWidth:e,clientWidth:i}=this.scrollContainer,s=t/e,n=(t+i)/e;this.emit("scroll",s,n)}));const t=this.createDelay(100);this.resizeObserver=new ResizeObserver((()=>{t((()=>this.reRender()))})),this.resizeObserver.observe(this.scrollContainer)}initDrag(){!function(t,e,i,s,n=5){let r=()=>{};if(!t)return r;const o=o=>{if(2===o.button)return;o.preventDefault(),o.stopPropagation();let a=o.clientX,h=o.clientY,l=!1;const c=s=>{s.preventDefault(),s.stopPropagation();const r=s.clientX,o=s.clientY;if(l||Math.abs(r-a)>=n||Math.abs(o-h)>=n){const{left:s,top:n}=t.getBoundingClientRect();l||(l=!0,null==i||i(a-s,h-n)),e(r-a,o-h,r-s,o-n),a=r,h=o}},d=t=>{l&&(t.preventDefault(),t.stopPropagation())},u=()=>{l&&(null==s||s()),r()};document.addEventListener("pointermove",c),document.addEventListener("pointerup",u),document.addEventListener("pointerleave",u),document.addEventListener("click",d,!0),r=()=>{document.removeEventListener("pointermove",c),document.removeEventListener("pointerup",u),document.removeEventListener("pointerleave",u),setTimeout((()=>{document.removeEventListener("click",d,!0)}),10)}};t.addEventListener("pointerdown",o)}(this.wrapper,((t,e,i)=>{this.emit("drag",Math.max(0,Math.min(1,i/this.wrapper.clientWidth)))}),(()=>this.isDragging=!0),(()=>this.isDragging=!1))}getHeight(){return null==this.options.height?128:isNaN(Number(this.options.height))?"auto"===this.options.height&&this.parent.clientHeight||128:Number(this.options.height)}initHtml(){const t=document.createElement("div"),e=t.attachShadow({mode:"open"});return e.innerHTML=`\n      <style>\n        :host {\n          user-select: none;\n        }\n        :host .scroll {\n          overflow-x: auto;\n          overflow-y: hidden;\n          width: 100%;\n          position: relative;\n          touch-action: none;\n        }\n        :host .noScrollbar {\n          scrollbar-color: transparent;\n          scrollbar-width: none;\n        }\n        :host .noScrollbar::-webkit-scrollbar {\n          display: none;\n          -webkit-appearance: none;\n        }\n        :host .wrapper {\n          position: relative;\n          overflow: visible;\n          z-index: 2;\n        }\n        :host .canvases {\n          min-height: ${this.getHeight()}px;\n        }\n        :host .canvases > div {\n          position: relative;\n        }\n        :host canvas {\n          display: block;\n          position: absolute;\n          top: 0;\n          image-rendering: pixelated;\n        }\n        :host .progress {\n          pointer-events: none;\n          position: absolute;\n          z-index: 2;\n          top: 0;\n          left: 0;\n          width: 0;\n          height: 100%;\n          overflow: hidden;\n        }\n        :host .progress > div {\n          position: relative;\n        }\n        :host .cursor {\n          pointer-events: none;\n          position: absolute;\n          z-index: 5;\n          top: 0;\n          left: 0;\n          height: 100%;\n          border-radius: 2px;\n        }\n      </style>\n\n      <div class="scroll" part="scroll">\n        <div class="wrapper">\n          <div class="canvases"></div>\n          <div class="progress" part="progress"></div>\n          <div class="cursor" part="cursor"></div>\n        </div>\n      </div>\n    `,[t,e]}setOptions(t){this.options=t,this.reRender()}getWrapper(){return this.wrapper}getScroll(){return this.scrollContainer.scrollLeft}destroy(){var t;this.container.remove(),null===(t=this.resizeObserver)||void 0===t||t.disconnect()}createDelay(t=10){const e={};return this.timeouts.push(e),i=>{e.timeout&&clearTimeout(e.timeout),e.timeout=setTimeout(i,t)}}convertColorValues(t){if(!Array.isArray(t))return t||"";if(t.length<2)return t[0]||"";const e=document.createElement("canvas"),i=e.getContext("2d").createLinearGradient(0,0,0,e.height),s=1/(t.length-1);return t.forEach(((t,e)=>{const n=e*s;i.addColorStop(n,t)})),i}renderBars(t,e,i){if(i.fillStyle=this.convertColorValues(e.waveColor),e.renderFunction)return void e.renderFunction(t,i);const s=t[0],n=t[1]||t[0],r=s.length,o=window.devicePixelRatio||1,{width:a,height:h}=i.canvas,l=h/2,c=e.barHeight||1,d=e.barWidth?e.barWidth*o:1,u=e.barGap?e.barGap*o:e.barWidth?d/2:0,p=e.barRadius||0,m=a/(d+u)/r;let g=1;if(e.normalize){g=0;for(let t=0;t<r;t++){const e=Math.abs(s[t]);e>g&&(g=e)}}const v=l/g*c,f=p&&"roundRect"in i?"roundRect":"rect";i.beginPath();let b=0,y=0,C=0;for(let t=0;t<=r;t++){const r=Math.round(t*m);if(r>b){const t=Math.round(y*v),s=t+Math.round(C*v)||1;let n=l-t;"top"===e.barAlign?n=0:"bottom"===e.barAlign&&(n=h-s),i[f](b*(d+u),n,d,s,p),b=r,y=0,C=0}const o=Math.abs(s[t]||0),a=Math.abs(n[t]||0);o>y&&(y=o),a>C&&(C=a)}i.fill(),i.closePath()}renderSingleCanvas(t,e,i,s,n,r,o,a){const h=window.devicePixelRatio||1,l=document.createElement("canvas"),c=t[0].length;l.width=Math.round(i*(r-n)/c),l.height=s*h,l.style.width=`${Math.floor(l.width/h)}px`,l.style.height=`${s}px`,l.style.left=`${Math.floor(n*i/h/c)}px`,o.appendChild(l);const d=l.getContext("2d");this.renderBars(t.map((t=>t.slice(n,r))),e,d);const u=l.cloneNode();a.appendChild(u);const p=u.getContext("2d");l.width>0&&l.height>0&&p.drawImage(l,0,0),p.globalCompositeOperation="source-in",p.fillStyle=this.convertColorValues(e.progressColor),p.fillRect(0,0,l.width,l.height)}renderWaveform(t,e,i){const s=document.createElement("div"),n=this.getHeight();s.style.height=`${n}px`,this.canvasWrapper.style.minHeight=`${n}px`,this.canvasWrapper.appendChild(s);const o=s.cloneNode();this.progressWrapper.appendChild(o);const{scrollLeft:a,scrollWidth:h,clientWidth:l}=this.scrollContainer,c=t[0].length,d=c/h;let u=Math.min(r.MAX_CANVAS_WIDTH,l);if(e.barWidth||e.barGap){const t=e.barWidth||.5,i=t+(e.barGap||t/2);u%i!=0&&(u=Math.floor(u/i)*i)}const p=Math.floor(Math.abs(a)*d),m=Math.floor(p+u*d),g=m-p,v=(r,a)=>{this.renderSingleCanvas(t,e,i,n,Math.max(0,r),Math.min(a,c),s,o)},f=this.createDelay(),b=this.createDelay(),y=(t,e)=>{v(t,e),t>0&&f((()=>{y(t-g,e-g)}))},C=(t,e)=>{v(t,e),e<c&&b((()=>{C(t+g,e+g)}))};y(p,m),m<c&&C(m,m+g)}render(t){this.timeouts.forEach((t=>t.timeout&&clearTimeout(t.timeout))),this.timeouts=[],this.canvasWrapper.innerHTML="",this.progressWrapper.innerHTML="",this.wrapper.style.width="";const e=window.devicePixelRatio||1,i=this.scrollContainer.clientWidth,s=Math.ceil(t.duration*(this.options.minPxPerSec||0));this.isScrolling=s>i;const n=this.options.fillParent&&!this.isScrolling,r=(n?i:s)*e;if(this.wrapper.style.width=n?"100%":`${s}px`,this.scrollContainer.style.overflowX=this.isScrolling?"auto":"hidden",this.scrollContainer.classList.toggle("noScrollbar",!!this.options.hideScrollbar),this.cursor.style.backgroundColor=`${this.options.cursorColor||this.options.progressColor}`,this.cursor.style.width=`${this.options.cursorWidth}px`,this.options.splitChannels)for(let e=0;e<t.numberOfChannels;e++){const i=Object.assign(Object.assign({},this.options),this.options.splitChannels[e]);this.renderWaveform([t.getChannelData(e)],i,r)}else{const e=[t.getChannelData(0)];t.numberOfChannels>1&&e.push(t.getChannelData(1)),this.renderWaveform(e,this.options,r)}this.audioData=t,this.emit("render")}reRender(){if(!this.audioData)return;const t=this.progressWrapper.clientWidth;this.render(this.audioData);const e=this.progressWrapper.clientWidth;this.scrollContainer.scrollLeft+=e-t}zoom(t){this.options.minPxPerSec=t,this.reRender()}scrollIntoView(t,e=!1){const{clientWidth:i,scrollLeft:s,scrollWidth:n}=this.scrollContainer,r=n*t,o=i/2;if(r>s+(e&&this.options.autoCenter&&!this.isDragging?o:i)||r<s)if(this.options.autoCenter&&!this.isDragging){const t=o/20;r-(s+o)>=t&&r<s+i?this.scrollContainer.scrollLeft+=t:this.scrollContainer.scrollLeft=r-o}else if(this.isDragging){const t=10;this.scrollContainer.scrollLeft=r<s?r-t:r-i+t}else this.scrollContainer.scrollLeft=r;{const{scrollLeft:t}=this.scrollContainer,e=t/n,s=(t+i)/n;this.emit("scroll",e,s)}}renderProgress(t,e){isNaN(t)||(this.progressWrapper.style.width=100*t+"%",this.cursor.style.left=100*t+"%",this.cursor.style.marginLeft=100===Math.round(100*t)?`-${this.options.cursorWidth}px`:"",this.isScrolling&&this.options.autoScroll&&this.scrollIntoView(t,e))}}r.MAX_CANVAS_WIDTH=4e3;class o extends s{constructor(){super(...arguments),this.unsubscribe=()=>{}}start(){this.unsubscribe=this.on("tick",(()=>{requestAnimationFrame((()=>{this.emit("tick")}))})),this.emit("tick")}stop(){this.unsubscribe()}destroy(){this.unsubscribe()}}const a={waveColor:"#999",progressColor:"#555",cursorWidth:1,minPxPerSec:0,fillParent:!0,interact:!0,autoScroll:!0,autoCenter:!0,sampleRate:8e3};class h extends n{static create(t){return new h(t)}constructor(t){var e,i;super({media:t.media,autoplay:t.autoplay,playbackRate:t.audioRate}),this.plugins=[],this.decodedData=null,this.duration=null,this.subscriptions=[],this.options=Object.assign({},a,t),this.timer=new o,this.renderer=new r(this.options),this.initPlayerEvents(),this.initRendererEvents(),this.initTimerEvents(),this.initPlugins();const s=this.options.url||(null===(e=this.options.media)||void 0===e?void 0:e.currentSrc)||(null===(i=this.options.media)||void 0===i?void 0:i.src);s&&this.load(s,this.options.peaks,this.options.duration)}setOptions(t){this.options=Object.assign({},this.options,t),this.renderer.setOptions(this.options),t.audioRate&&this.setPlaybackRate(t.audioRate)}initTimerEvents(){this.subscriptions.push(this.timer.on("tick",(()=>{const t=this.getCurrentTime();this.renderer.renderProgress(t/this.getDuration(),!0),this.emit("timeupdate",t),this.emit("audioprocess",t)})))}initPlayerEvents(){this.subscriptions.push(this.onMediaEvent("timeupdate",(()=>{const t=this.getCurrentTime();this.renderer.renderProgress(t/this.getDuration(),this.isPlaying()),this.emit("timeupdate",t)})),this.onMediaEvent("play",(()=>{this.emit("play"),this.timer.start()})),this.onMediaEvent("pause",(()=>{this.emit("pause"),this.timer.stop()})),this.onMediaEvent("ended",(()=>{this.emit("finish")})),this.onMediaEvent("seeking",(()=>{this.emit("seeking",this.getCurrentTime())})))}initRendererEvents(){this.subscriptions.push(this.renderer.on("click",(t=>{this.options.interact&&(this.seekTo(t),this.emit("interaction",this.getCurrentTime()),this.emit("click",t))})),this.renderer.on("scroll",((t,e)=>{const i=this.getDuration();this.emit("scroll",t*i,e*i)})),this.renderer.on("render",(()=>{this.emit("redraw")})));{let t;this.subscriptions.push(this.renderer.on("drag",(e=>{this.options.interact&&(this.renderer.renderProgress(e),clearTimeout(t),t=setTimeout((()=>{this.seekTo(e)}),this.isPlaying()?0:200),this.emit("interaction",e*this.getDuration()),this.emit("drag",e))})))}}initPlugins(){var t;(null===(t=this.options.plugins)||void 0===t?void 0:t.length)&&this.options.plugins.forEach((t=>{this.registerPlugin(t)}))}registerPlugin(t){return t.init(this),this.plugins.push(t),this.subscriptions.push(t.once("destroy",(()=>{this.plugins=this.plugins.filter((e=>e!==t))}))),t}getWrapper(){return this.renderer.getWrapper()}getScroll(){return this.renderer.getScroll()}getActivePlugins(){return this.plugins}load(s,n,r){return t(this,void 0,void 0,(function*(){this.isPlaying()&&this.pause(),this.decodedData=null,this.duration=null,this.emit("load",s);const t=n?void 0:yield i.fetchBlob(s,this.options.fetchParams);if(this.setSrc(s,t),this.duration=r||this.getDuration()||(yield new Promise((t=>{this.onceMediaEvent("loadedmetadata",(()=>t(this.getDuration())))})))||0,n)this.decodedData=e.createBuffer(n,this.duration);else if(t){const i=yield t.arrayBuffer();this.decodedData=yield e.decode(i,this.options.sampleRate),0!==this.duration&&this.duration!==1/0||(this.duration=this.decodedData.duration)}this.emit("decode",this.duration),this.decodedData&&this.renderer.render(this.decodedData),this.emit("ready",this.duration)}))}zoom(t){if(!this.decodedData)throw new Error("No audio loaded");this.renderer.zoom(t),this.emit("zoom",t)}getDecodedData(){return this.decodedData}getDuration(){return null!==this.duration?this.duration:super.getDuration()}toggleInteraction(t){this.options.interact=t}seekTo(t){const e=this.getDuration()*t;this.setTime(e)}playPause(){return t(this,void 0,void 0,(function*(){return this.isPlaying()?this.pause():this.play()}))}stop(){this.pause(),this.setTime(0)}skip(t){this.setTime(this.getCurrentTime()+t)}empty(){this.load("",[[0]],.001)}destroy(){this.emit("destroy"),this.plugins.forEach((t=>t.destroy())),this.subscriptions.forEach((t=>t())),this.timer.destroy(),this.renderer.destroy(),super.destroy()}}


/***/ }),

/***/ "./src/js/recorder.js":
/*!****************************!*\
  !*** ./src/js/recorder.js ***!
  \****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var wavesurfer_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! wavesurfer.js */ "./node_modules/wavesurfer.js/dist/wavesurfer.esm.js");
/* harmony import */ var wavesurfer_js_dist_plugins_record_esm_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! wavesurfer.js/dist/plugins/record.esm.js */ "./node_modules/wavesurfer.js/dist/plugins/record.esm.js");


window.WaveSurfer = wavesurfer_js__WEBPACK_IMPORTED_MODULE_0__["default"];
window.RecordPlugin = wavesurfer_js_dist_plugins_record_esm_js__WEBPACK_IMPORTED_MODULE_1__["default"];

/***/ })

/******/ });
//# sourceMappingURL=recorder.js.map