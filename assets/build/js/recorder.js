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

/***/ "./node_modules/wavesurfer.js/dist/base-plugin.js":
/*!********************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/base-plugin.js ***!
  \********************************************************/
/*! exports provided: BasePlugin, default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "BasePlugin", function() { return BasePlugin; });
/* harmony import */ var _event_emitter_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./event-emitter.js */ "./node_modules/wavesurfer.js/dist/event-emitter.js");

/** Base class for wavesurfer plugins */
class BasePlugin extends _event_emitter_js__WEBPACK_IMPORTED_MODULE_0__["default"] {
    /** Create a plugin instance */
    constructor(options) {
        super();
        this.subscriptions = [];
        this.options = options;
    }
    /** Called after this.wavesurfer is available */
    onInit() {
        return;
    }
    /** Do not call directly, only called by WavesSurfer internally */
    _init(wavesurfer) {
        this.wavesurfer = wavesurfer;
        this.onInit();
    }
    /** Destroy the plugin and unsubscribe from all events */
    destroy() {
        this.emit('destroy');
        this.subscriptions.forEach((unsubscribe) => unsubscribe());
    }
}
/* harmony default export */ __webpack_exports__["default"] = (BasePlugin);


/***/ }),

/***/ "./node_modules/wavesurfer.js/dist/decoder.js":
/*!****************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/decoder.js ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
/** Decode an array buffer into an audio buffer */
function decode(audioData, sampleRate) {
    return __awaiter(this, void 0, void 0, function* () {
        const audioCtx = new AudioContext({ sampleRate });
        const decode = audioCtx.decodeAudioData(audioData);
        return decode.finally(() => audioCtx.close());
    });
}
/** Normalize peaks to -1..1 */
function normalize(channelData) {
    const firstChannel = channelData[0];
    if (firstChannel.some((n) => n > 1 || n < -1)) {
        const length = firstChannel.length;
        let max = 0;
        for (let i = 0; i < length; i++) {
            const absN = Math.abs(firstChannel[i]);
            if (absN > max)
                max = absN;
        }
        for (const channel of channelData) {
            for (let i = 0; i < length; i++) {
                channel[i] /= max;
            }
        }
    }
    return channelData;
}
/** Create an audio buffer from pre-decoded audio data */
function createBuffer(channelData, duration) {
    // If a single array of numbers is passed, make it an array of arrays
    if (typeof channelData[0] === 'number')
        channelData = [channelData];
    // Normalize to -1..1
    normalize(channelData);
    return {
        duration,
        length: channelData[0].length,
        sampleRate: channelData[0].length / duration,
        numberOfChannels: channelData.length,
        getChannelData: (i) => channelData === null || channelData === void 0 ? void 0 : channelData[i],
        copyFromChannel: AudioBuffer.prototype.copyFromChannel,
        copyToChannel: AudioBuffer.prototype.copyToChannel,
    };
}
const Decoder = {
    decode,
    createBuffer,
};
/* harmony default export */ __webpack_exports__["default"] = (Decoder);


/***/ }),

/***/ "./node_modules/wavesurfer.js/dist/dom.js":
/*!************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/dom.js ***!
  \************************************************/
/*! exports provided: createElement, default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "createElement", function() { return createElement; });
function renderNode(tagName, content) {
    const element = content.xmlns
        ? document.createElementNS(content.xmlns, tagName)
        : document.createElement(tagName);
    for (const [key, value] of Object.entries(content)) {
        if (key === 'children') {
            for (const [key, value] of Object.entries(content)) {
                if (typeof value === 'string') {
                    element.appendChild(document.createTextNode(value));
                }
                else {
                    element.appendChild(renderNode(key, value));
                }
            }
        }
        else if (key === 'style') {
            Object.assign(element.style, value);
        }
        else if (key === 'textContent') {
            element.textContent = value;
        }
        else {
            element.setAttribute(key, value.toString());
        }
    }
    return element;
}
function createElement(tagName, content, container) {
    const el = renderNode(tagName, content || {});
    container === null || container === void 0 ? void 0 : container.appendChild(el);
    return el;
}
/* harmony default export */ __webpack_exports__["default"] = (createElement);


/***/ }),

/***/ "./node_modules/wavesurfer.js/dist/draggable.js":
/*!******************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/draggable.js ***!
  \******************************************************/
/*! exports provided: makeDraggable */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "makeDraggable", function() { return makeDraggable; });
function makeDraggable(element, onDrag, onStart, onEnd, threshold = 3, mouseButton = 0, touchDelay = 100) {
    if (!element)
        return () => void 0;
    const isTouchDevice = matchMedia('(pointer: coarse)').matches;
    let unsubscribeDocument = () => void 0;
    const onPointerDown = (event) => {
        if (event.button !== mouseButton)
            return;
        event.preventDefault();
        event.stopPropagation();
        let startX = event.clientX;
        let startY = event.clientY;
        let isDragging = false;
        const touchStartTime = Date.now();
        const onPointerMove = (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (isTouchDevice && Date.now() - touchStartTime < touchDelay)
                return;
            const x = event.clientX;
            const y = event.clientY;
            const dx = x - startX;
            const dy = y - startY;
            if (isDragging || Math.abs(dx) > threshold || Math.abs(dy) > threshold) {
                const rect = element.getBoundingClientRect();
                const { left, top } = rect;
                if (!isDragging) {
                    onStart === null || onStart === void 0 ? void 0 : onStart(startX - left, startY - top);
                    isDragging = true;
                }
                onDrag(dx, dy, x - left, y - top);
                startX = x;
                startY = y;
            }
        };
        const onPointerUp = (event) => {
            if (isDragging) {
                const x = event.clientX;
                const y = event.clientY;
                const rect = element.getBoundingClientRect();
                const { left, top } = rect;
                onEnd === null || onEnd === void 0 ? void 0 : onEnd(x - left, y - top);
            }
            unsubscribeDocument();
        };
        const onPointerLeave = (e) => {
            // Listen to events only on the document and not on inner elements
            if (!e.relatedTarget || e.relatedTarget === document.documentElement) {
                onPointerUp(e);
            }
        };
        const onClick = (event) => {
            if (isDragging) {
                event.stopPropagation();
                event.preventDefault();
            }
        };
        const onTouchMove = (event) => {
            if (isDragging) {
                event.preventDefault();
            }
        };
        document.addEventListener('pointermove', onPointerMove);
        document.addEventListener('pointerup', onPointerUp);
        document.addEventListener('pointerout', onPointerLeave);
        document.addEventListener('pointercancel', onPointerLeave);
        document.addEventListener('touchmove', onTouchMove, { passive: false });
        document.addEventListener('click', onClick, { capture: true });
        unsubscribeDocument = () => {
            document.removeEventListener('pointermove', onPointerMove);
            document.removeEventListener('pointerup', onPointerUp);
            document.removeEventListener('pointerout', onPointerLeave);
            document.removeEventListener('pointercancel', onPointerLeave);
            document.removeEventListener('touchmove', onTouchMove);
            setTimeout(() => {
                document.removeEventListener('click', onClick, { capture: true });
            }, 10);
        };
    };
    element.addEventListener('pointerdown', onPointerDown);
    return () => {
        unsubscribeDocument();
        element.removeEventListener('pointerdown', onPointerDown);
    };
}


/***/ }),

/***/ "./node_modules/wavesurfer.js/dist/event-emitter.js":
/*!**********************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/event-emitter.js ***!
  \**********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/** A simple event emitter that can be used to listen to and emit events. */
class EventEmitter {
    constructor() {
        this.listeners = {};
    }
    /** Subscribe to an event. Returns an unsubscribe function. */
    on(event, listener, options) {
        if (!this.listeners[event]) {
            this.listeners[event] = new Set();
        }
        this.listeners[event].add(listener);
        if (options === null || options === void 0 ? void 0 : options.once) {
            const unsubscribeOnce = () => {
                this.un(event, unsubscribeOnce);
                this.un(event, listener);
            };
            this.on(event, unsubscribeOnce);
            return unsubscribeOnce;
        }
        return () => this.un(event, listener);
    }
    /** Unsubscribe from an event */
    un(event, listener) {
        var _a;
        (_a = this.listeners[event]) === null || _a === void 0 ? void 0 : _a.delete(listener);
    }
    /** Subscribe to an event only once */
    once(event, listener) {
        return this.on(event, listener, { once: true });
    }
    /** Clear all events */
    unAll() {
        this.listeners = {};
    }
    /** Emit an event */
    emit(eventName, ...args) {
        if (this.listeners[eventName]) {
            this.listeners[eventName].forEach((listener) => listener(...args));
        }
    }
}
/* harmony default export */ __webpack_exports__["default"] = (EventEmitter);


/***/ }),

/***/ "./node_modules/wavesurfer.js/dist/fetcher.js":
/*!****************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/fetcher.js ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
function watchProgress(response, progressCallback) {
    return __awaiter(this, void 0, void 0, function* () {
        if (!response.body || !response.headers)
            return;
        const reader = response.body.getReader();
        const contentLength = Number(response.headers.get('Content-Length')) || 0;
        let receivedLength = 0;
        // Process the data
        const processChunk = (value) => __awaiter(this, void 0, void 0, function* () {
            // Add to the received length
            receivedLength += (value === null || value === void 0 ? void 0 : value.length) || 0;
            const percentage = Math.round((receivedLength / contentLength) * 100);
            progressCallback(percentage);
        });
        const read = () => __awaiter(this, void 0, void 0, function* () {
            let data;
            try {
                data = yield reader.read();
            }
            catch (_a) {
                // Ignore errors because we can only handle the main response
                return;
            }
            // Continue reading data until done
            if (!data.done) {
                processChunk(data.value);
                yield read();
            }
        });
        read();
    });
}
function fetchBlob(url, progressCallback, requestInit) {
    return __awaiter(this, void 0, void 0, function* () {
        // Fetch the resource
        const response = yield fetch(url, requestInit);
        if (response.status >= 400) {
            throw new Error(`Failed to fetch ${url}: ${response.status} (${response.statusText})`);
        }
        // Read the data to track progress
        watchProgress(response.clone(), progressCallback);
        return response.blob();
    });
}
const Fetcher = {
    fetchBlob,
};
/* harmony default export */ __webpack_exports__["default"] = (Fetcher);


/***/ }),

/***/ "./node_modules/wavesurfer.js/dist/player.js":
/*!***************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/player.js ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _event_emitter_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./event-emitter.js */ "./node_modules/wavesurfer.js/dist/event-emitter.js");
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};

class Player extends _event_emitter_js__WEBPACK_IMPORTED_MODULE_0__["default"] {
    constructor(options) {
        super();
        this.isExternalMedia = false;
        if (options.media) {
            this.media = options.media;
            this.isExternalMedia = true;
        }
        else {
            this.media = document.createElement('audio');
        }
        // Controls
        if (options.mediaControls) {
            this.media.controls = true;
        }
        // Autoplay
        if (options.autoplay) {
            this.media.autoplay = true;
        }
        // Speed
        if (options.playbackRate != null) {
            this.onMediaEvent('canplay', () => {
                if (options.playbackRate != null) {
                    this.media.playbackRate = options.playbackRate;
                }
            }, { once: true });
        }
    }
    onMediaEvent(event, callback, options) {
        this.media.addEventListener(event, callback, options);
        return () => this.media.removeEventListener(event, callback, options);
    }
    getSrc() {
        return this.media.currentSrc || this.media.src || '';
    }
    revokeSrc() {
        const src = this.getSrc();
        if (src.startsWith('blob:')) {
            URL.revokeObjectURL(src);
        }
    }
    canPlayType(type) {
        return this.media.canPlayType(type) !== '';
    }
    setSrc(url, blob) {
        const src = this.getSrc();
        if (url && src === url)
            return;
        this.revokeSrc();
        const newSrc = blob instanceof Blob && (this.canPlayType(blob.type) || !url) ? URL.createObjectURL(blob) : url;
        try {
            this.media.src = newSrc;
        }
        catch (e) {
            this.media.src = url;
        }
    }
    destroy() {
        this.media.pause();
        if (this.isExternalMedia)
            return;
        this.media.remove();
        this.revokeSrc();
        this.media.src = '';
        // Load resets the media element to its initial state
        this.media.load();
    }
    setMediaElement(element) {
        this.media = element;
    }
    /** Start playing the audio */
    play() {
        return __awaiter(this, void 0, void 0, function* () {
            return this.media.play();
        });
    }
    /** Pause the audio */
    pause() {
        this.media.pause();
    }
    /** Check if the audio is playing */
    isPlaying() {
        return !this.media.paused && !this.media.ended;
    }
    /** Jump to a specific time in the audio (in seconds) */
    setTime(time) {
        this.media.currentTime = time;
    }
    /** Get the duration of the audio in seconds */
    getDuration() {
        return this.media.duration;
    }
    /** Get the current audio position in seconds */
    getCurrentTime() {
        return this.media.currentTime;
    }
    /** Get the audio volume */
    getVolume() {
        return this.media.volume;
    }
    /** Set the audio volume */
    setVolume(volume) {
        this.media.volume = volume;
    }
    /** Get the audio muted state */
    getMuted() {
        return this.media.muted;
    }
    /** Mute or unmute the audio */
    setMuted(muted) {
        this.media.muted = muted;
    }
    /** Get the playback speed */
    getPlaybackRate() {
        return this.media.playbackRate;
    }
    /** Check if the audio is seeking */
    isSeeking() {
        return this.media.seeking;
    }
    /** Set the playback speed, pass an optional false to NOT preserve the pitch */
    setPlaybackRate(rate, preservePitch) {
        // preservePitch is true by default in most browsers
        if (preservePitch != null) {
            this.media.preservesPitch = preservePitch;
        }
        this.media.playbackRate = rate;
    }
    /** Get the HTML media element */
    getMediaElement() {
        return this.media;
    }
    /** Set a sink id to change the audio output device */
    setSinkId(sinkId) {
        // See https://developer.mozilla.org/en-US/docs/Web/API/HTMLMediaElement/setSinkId
        const media = this.media;
        return media.setSinkId(sinkId);
    }
}
/* harmony default export */ __webpack_exports__["default"] = (Player);


/***/ }),

/***/ "./node_modules/wavesurfer.js/dist/plugins/record.esm.js":
/*!***************************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/plugins/record.esm.js ***!
  \***************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return r; });
function t(t,i,e,s){return new(e||(e=Promise))((function(o,r){function n(t){try{d(s.next(t))}catch(t){r(t)}}function a(t){try{d(s.throw(t))}catch(t){r(t)}}function d(t){var i;t.done?o(t.value):(i=t.value,i instanceof e?i:new e((function(t){t(i)}))).then(n,a)}d((s=s.apply(t,i||[])).next())}))}"function"==typeof SuppressedError&&SuppressedError;class i{constructor(){this.listeners={}}on(t,i,e){if(this.listeners[t]||(this.listeners[t]=new Set),this.listeners[t].add(i),null==e?void 0:e.once){const e=()=>{this.un(t,e),this.un(t,i)};return this.on(t,e),e}return()=>this.un(t,i)}un(t,i){var e;null===(e=this.listeners[t])||void 0===e||e.delete(i)}once(t,i){return this.on(t,i,{once:!0})}unAll(){this.listeners={}}emit(t,...i){this.listeners[t]&&this.listeners[t].forEach((t=>t(...i)))}}class e extends i{constructor(t){super(),this.subscriptions=[],this.options=t}onInit(){}_init(t){this.wavesurfer=t,this.onInit()}destroy(){this.emit("destroy"),this.subscriptions.forEach((t=>t()))}}class s extends i{constructor(){super(...arguments),this.unsubscribe=()=>{}}start(){this.unsubscribe=this.on("tick",(()=>{requestAnimationFrame((()=>{this.emit("tick")}))})),this.emit("tick")}stop(){this.unsubscribe()}destroy(){this.unsubscribe()}}const o=["audio/webm","audio/wav","audio/mpeg","audio/mp4","audio/mp3"];class r extends e{constructor(t){var i,e,o,r,n,a;super(Object.assign(Object.assign({},t),{audioBitsPerSecond:null!==(i=t.audioBitsPerSecond)&&void 0!==i?i:128e3,scrollingWaveform:null!==(e=t.scrollingWaveform)&&void 0!==e&&e,scrollingWaveformWindow:null!==(o=t.scrollingWaveformWindow)&&void 0!==o?o:5,continuousWaveform:null!==(r=t.continuousWaveform)&&void 0!==r&&r,renderRecordedAudio:null===(n=t.renderRecordedAudio)||void 0===n||n,mediaRecorderTimeslice:null!==(a=t.mediaRecorderTimeslice)&&void 0!==a?a:void 0})),this.stream=null,this.mediaRecorder=null,this.dataWindow=null,this.isWaveformPaused=!1,this.lastStartTime=0,this.lastDuration=0,this.duration=0,this.timer=new s,this.subscriptions.push(this.timer.on("tick",(()=>{const t=performance.now()-this.lastStartTime;this.duration=this.isPaused()?this.duration:this.lastDuration+t,this.emit("record-progress",this.duration)})))}static create(t){return new r(t||{})}renderMicStream(t){var i;const e=new AudioContext,s=e.createMediaStreamSource(t),o=e.createAnalyser();s.connect(o),this.options.continuousWaveform&&(o.fftSize=32);const r=o.frequencyBinCount,n=new Float32Array(r);let a=0;this.wavesurfer&&(null!==(i=this.originalOptions)&&void 0!==i||(this.originalOptions=Object.assign({},this.wavesurfer.options)),this.wavesurfer.options.interact=!1,this.options.scrollingWaveform&&(this.wavesurfer.options.cursorWidth=0));const d=setInterval((()=>{var t,i,s,d;if(!this.isWaveformPaused){if(o.getFloatTimeDomainData(n),this.options.scrollingWaveform){const t=Math.floor((this.options.scrollingWaveformWindow||0)*e.sampleRate),i=Math.min(t,this.dataWindow?this.dataWindow.length+r:r),s=new Float32Array(t);if(this.dataWindow){const e=Math.max(0,t-this.dataWindow.length);s.set(this.dataWindow.slice(-i+r),e)}s.set(n,t-r),this.dataWindow=s}else if(this.options.continuousWaveform){if(!this.dataWindow){const e=this.options.continuousWaveformDuration?Math.round(100*this.options.continuousWaveformDuration):(null!==(i=null===(t=this.wavesurfer)||void 0===t?void 0:t.getWidth())&&void 0!==i?i:0)*window.devicePixelRatio;this.dataWindow=new Float32Array(e)}let e=0;for(let t=0;t<r;t++){const i=Math.abs(n[t]);i>e&&(e=i)}if(a+1>this.dataWindow.length){const t=new Float32Array(2*this.dataWindow.length);t.set(this.dataWindow,0),this.dataWindow=t}this.dataWindow[a]=e,a++}else this.dataWindow=n;if(this.wavesurfer){const t=(null!==(d=null===(s=this.dataWindow)||void 0===s?void 0:s.length)&&void 0!==d?d:0)/100;this.wavesurfer.load("",[this.dataWindow],this.options.scrollingWaveform?this.options.scrollingWaveformWindow:t).then((()=>{this.wavesurfer&&this.options.continuousWaveform&&(this.wavesurfer.setTime(this.getDuration()/1e3),this.wavesurfer.options.minPxPerSec||this.wavesurfer.setOptions({minPxPerSec:this.wavesurfer.getWidth()/this.wavesurfer.getDuration()}))})).catch((t=>{console.error("Error rendering real-time recording data:",t)}))}}}),10);return{onDestroy:()=>{clearInterval(d),null==s||s.disconnect(),null==e||e.close()},onEnd:()=>{this.isWaveformPaused=!0,clearInterval(d),this.stopMic()}}}startMic(i){return t(this,void 0,void 0,(function*(){let t;try{t=yield navigator.mediaDevices.getUserMedia({audio:!(null==i?void 0:i.deviceId)||{deviceId:i.deviceId}})}catch(t){throw new Error("Error accessing the microphone: "+t.message)}const{onDestroy:e,onEnd:s}=this.renderMicStream(t);return this.subscriptions.push(this.once("destroy",e)),this.subscriptions.push(this.once("record-end",s)),this.stream=t,t}))}stopMic(){this.stream&&(this.stream.getTracks().forEach((t=>t.stop())),this.stream=null,this.mediaRecorder=null)}startRecording(i){return t(this,void 0,void 0,(function*(){const t=this.stream||(yield this.startMic(i));this.dataWindow=null;const e=this.mediaRecorder||new MediaRecorder(t,{mimeType:this.options.mimeType||o.find((t=>MediaRecorder.isTypeSupported(t))),audioBitsPerSecond:this.options.audioBitsPerSecond});this.mediaRecorder=e,this.stopRecording();const s=[];e.ondataavailable=t=>{t.data.size>0&&s.push(t.data),this.emit("record-data-available",t.data)};const r=t=>{var i;const o=new Blob(s,{type:e.mimeType});this.emit(t,o),this.options.renderRecordedAudio&&(this.applyOriginalOptionsIfNeeded(),null===(i=this.wavesurfer)||void 0===i||i.load(URL.createObjectURL(o)))};e.onpause=()=>r("record-pause"),e.onstop=()=>r("record-end"),e.start(this.options.mediaRecorderTimeslice),this.lastStartTime=performance.now(),this.lastDuration=0,this.duration=0,this.isWaveformPaused=!1,this.timer.start(),this.emit("record-start")}))}getDuration(){return this.duration}isRecording(){var t;return"recording"===(null===(t=this.mediaRecorder)||void 0===t?void 0:t.state)}isPaused(){var t;return"paused"===(null===(t=this.mediaRecorder)||void 0===t?void 0:t.state)}isActive(){var t;return"inactive"!==(null===(t=this.mediaRecorder)||void 0===t?void 0:t.state)}stopRecording(){var t;this.isActive()&&(null===(t=this.mediaRecorder)||void 0===t||t.stop(),this.timer.stop())}pauseRecording(){var t,i;this.isRecording()&&(this.isWaveformPaused=!0,null===(t=this.mediaRecorder)||void 0===t||t.requestData(),null===(i=this.mediaRecorder)||void 0===i||i.pause(),this.timer.stop(),this.lastDuration=this.duration)}resumeRecording(){var t;this.isPaused()&&(this.isWaveformPaused=!1,null===(t=this.mediaRecorder)||void 0===t||t.resume(),this.timer.start(),this.lastStartTime=performance.now(),this.emit("record-resume"))}static getAvailableAudioDevices(){return t(this,void 0,void 0,(function*(){return navigator.mediaDevices.enumerateDevices().then((t=>t.filter((t=>"audioinput"===t.kind))))}))}destroy(){this.applyOriginalOptionsIfNeeded(),super.destroy(),this.stopRecording(),this.stopMic()}applyOriginalOptionsIfNeeded(){this.wavesurfer&&this.originalOptions&&(this.wavesurfer.setOptions(this.originalOptions),delete this.originalOptions)}}


/***/ }),

/***/ "./node_modules/wavesurfer.js/dist/renderer.js":
/*!*****************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/renderer.js ***!
  \*****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _draggable_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./draggable.js */ "./node_modules/wavesurfer.js/dist/draggable.js");
/* harmony import */ var _event_emitter_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./event-emitter.js */ "./node_modules/wavesurfer.js/dist/event-emitter.js");
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __rest = (undefined && undefined.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};


class Renderer extends _event_emitter_js__WEBPACK_IMPORTED_MODULE_1__["default"] {
    constructor(options, audioElement) {
        super();
        this.timeouts = [];
        this.isScrollable = false;
        this.audioData = null;
        this.resizeObserver = null;
        this.lastContainerWidth = 0;
        this.isDragging = false;
        this.subscriptions = [];
        this.subscriptions = [];
        this.options = options;
        const parent = this.parentFromOptionsContainer(options.container);
        this.parent = parent;
        const [div, shadow] = this.initHtml();
        parent.appendChild(div);
        this.container = div;
        this.scrollContainer = shadow.querySelector('.scroll');
        this.wrapper = shadow.querySelector('.wrapper');
        this.canvasWrapper = shadow.querySelector('.canvases');
        this.progressWrapper = shadow.querySelector('.progress');
        this.cursor = shadow.querySelector('.cursor');
        if (audioElement) {
            shadow.appendChild(audioElement);
        }
        this.initEvents();
    }
    parentFromOptionsContainer(container) {
        let parent;
        if (typeof container === 'string') {
            parent = document.querySelector(container);
        }
        else if (container instanceof HTMLElement) {
            parent = container;
        }
        if (!parent) {
            throw new Error('Container not found');
        }
        return parent;
    }
    initEvents() {
        const getClickPosition = (e) => {
            const rect = this.wrapper.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const relativeX = x / rect.width;
            const relativeY = y / rect.height;
            return [relativeX, relativeY];
        };
        // Add a click listener
        this.wrapper.addEventListener('click', (e) => {
            const [x, y] = getClickPosition(e);
            this.emit('click', x, y);
        });
        // Add a double click listener
        this.wrapper.addEventListener('dblclick', (e) => {
            const [x, y] = getClickPosition(e);
            this.emit('dblclick', x, y);
        });
        // Drag
        if (this.options.dragToSeek === true || typeof this.options.dragToSeek === 'object') {
            this.initDrag();
        }
        // Add a scroll listener
        this.scrollContainer.addEventListener('scroll', () => {
            const { scrollLeft, scrollWidth, clientWidth } = this.scrollContainer;
            const startX = scrollLeft / scrollWidth;
            const endX = (scrollLeft + clientWidth) / scrollWidth;
            this.emit('scroll', startX, endX, scrollLeft, scrollLeft + clientWidth);
        });
        // Re-render the waveform on container resize
        if (typeof ResizeObserver === 'function') {
            const delay = this.createDelay(100);
            this.resizeObserver = new ResizeObserver(() => {
                delay()
                    .then(() => this.onContainerResize())
                    .catch(() => undefined);
            });
            this.resizeObserver.observe(this.scrollContainer);
        }
    }
    onContainerResize() {
        const width = this.parent.clientWidth;
        if (width === this.lastContainerWidth && this.options.height !== 'auto')
            return;
        this.lastContainerWidth = width;
        this.reRender();
    }
    initDrag() {
        this.subscriptions.push(Object(_draggable_js__WEBPACK_IMPORTED_MODULE_0__["makeDraggable"])(this.wrapper, 
        // On drag
        (_, __, x) => {
            this.emit('drag', Math.max(0, Math.min(1, x / this.wrapper.getBoundingClientRect().width)));
        }, 
        // On start drag
        (x) => {
            this.isDragging = true;
            this.emit('dragstart', Math.max(0, Math.min(1, x / this.wrapper.getBoundingClientRect().width)));
        }, 
        // On end drag
        (x) => {
            this.isDragging = false;
            this.emit('dragend', Math.max(0, Math.min(1, x / this.wrapper.getBoundingClientRect().width)));
        }));
    }
    getHeight(optionsHeight, optionsSplitChannel) {
        var _a;
        const defaultHeight = 128;
        const numberOfChannels = ((_a = this.audioData) === null || _a === void 0 ? void 0 : _a.numberOfChannels) || 1;
        if (optionsHeight == null)
            return defaultHeight;
        if (!isNaN(Number(optionsHeight)))
            return Number(optionsHeight);
        if (optionsHeight === 'auto') {
            const height = this.parent.clientHeight || defaultHeight;
            if (optionsSplitChannel === null || optionsSplitChannel === void 0 ? void 0 : optionsSplitChannel.every((channel) => !channel.overlay))
                return height / numberOfChannels;
            return height;
        }
        return defaultHeight;
    }
    initHtml() {
        const div = document.createElement('div');
        const shadow = div.attachShadow({ mode: 'open' });
        const cspNonce = this.options.cspNonce && typeof this.options.cspNonce === 'string' ? this.options.cspNonce.replace(/"/g, '') : '';
        shadow.innerHTML = `
      <style${cspNonce ? ` nonce="${cspNonce}"` : ''}>
        :host {
          user-select: none;
          min-width: 1px;
        }
        :host audio {
          display: block;
          width: 100%;
        }
        :host .scroll {
          overflow-x: auto;
          overflow-y: hidden;
          width: 100%;
          position: relative;
        }
        :host .noScrollbar {
          scrollbar-color: transparent;
          scrollbar-width: none;
        }
        :host .noScrollbar::-webkit-scrollbar {
          display: none;
          -webkit-appearance: none;
        }
        :host .wrapper {
          position: relative;
          overflow: visible;
          z-index: 2;
        }
        :host .canvases {
          min-height: ${this.getHeight(this.options.height, this.options.splitChannels)}px;
        }
        :host .canvases > div {
          position: relative;
        }
        :host canvas {
          display: block;
          position: absolute;
          top: 0;
          image-rendering: pixelated;
        }
        :host .progress {
          pointer-events: none;
          position: absolute;
          z-index: 2;
          top: 0;
          left: 0;
          width: 0;
          height: 100%;
          overflow: hidden;
        }
        :host .progress > div {
          position: relative;
        }
        :host .cursor {
          pointer-events: none;
          position: absolute;
          z-index: 5;
          top: 0;
          left: 0;
          height: 100%;
          border-radius: 2px;
        }
      </style>

      <div class="scroll" part="scroll">
        <div class="wrapper" part="wrapper">
          <div class="canvases" part="canvases"></div>
          <div class="progress" part="progress"></div>
          <div class="cursor" part="cursor"></div>
        </div>
      </div>
    `;
        return [div, shadow];
    }
    /** Wavesurfer itself calls this method. Do not call it manually. */
    setOptions(options) {
        if (this.options.container !== options.container) {
            const newParent = this.parentFromOptionsContainer(options.container);
            newParent.appendChild(this.container);
            this.parent = newParent;
        }
        if (options.dragToSeek === true || typeof this.options.dragToSeek === 'object') {
            this.initDrag();
        }
        this.options = options;
        // Re-render the waveform
        this.reRender();
    }
    getWrapper() {
        return this.wrapper;
    }
    getWidth() {
        return this.scrollContainer.clientWidth;
    }
    getScroll() {
        return this.scrollContainer.scrollLeft;
    }
    setScroll(pixels) {
        this.scrollContainer.scrollLeft = pixels;
    }
    setScrollPercentage(percent) {
        const { scrollWidth } = this.scrollContainer;
        const scrollStart = scrollWidth * percent;
        this.setScroll(scrollStart);
    }
    destroy() {
        var _a, _b;
        this.subscriptions.forEach((unsubscribe) => unsubscribe());
        this.container.remove();
        (_a = this.resizeObserver) === null || _a === void 0 ? void 0 : _a.disconnect();
        (_b = this.unsubscribeOnScroll) === null || _b === void 0 ? void 0 : _b.call(this);
    }
    createDelay(delayMs = 10) {
        let timeout;
        let reject;
        const onClear = () => {
            if (timeout)
                clearTimeout(timeout);
            if (reject)
                reject();
        };
        this.timeouts.push(onClear);
        return () => {
            return new Promise((resolveFn, rejectFn) => {
                onClear();
                reject = rejectFn;
                timeout = setTimeout(() => {
                    timeout = undefined;
                    reject = undefined;
                    resolveFn();
                }, delayMs);
            });
        };
    }
    // Convert array of color values to linear gradient
    convertColorValues(color) {
        if (!Array.isArray(color))
            return color || '';
        if (color.length < 2)
            return color[0] || '';
        const canvasElement = document.createElement('canvas');
        const ctx = canvasElement.getContext('2d');
        const gradientHeight = canvasElement.height * (window.devicePixelRatio || 1);
        const gradient = ctx.createLinearGradient(0, 0, 0, gradientHeight);
        const colorStopPercentage = 1 / (color.length - 1);
        color.forEach((color, index) => {
            const offset = index * colorStopPercentage;
            gradient.addColorStop(offset, color);
        });
        return gradient;
    }
    getPixelRatio() {
        return Math.max(1, window.devicePixelRatio || 1);
    }
    renderBarWaveform(channelData, options, ctx, vScale) {
        const topChannel = channelData[0];
        const bottomChannel = channelData[1] || channelData[0];
        const length = topChannel.length;
        const { width, height } = ctx.canvas;
        const halfHeight = height / 2;
        const pixelRatio = this.getPixelRatio();
        const barWidth = options.barWidth ? options.barWidth * pixelRatio : 1;
        const barGap = options.barGap ? options.barGap * pixelRatio : options.barWidth ? barWidth / 2 : 0;
        const barRadius = options.barRadius || 0;
        const barIndexScale = width / (barWidth + barGap) / length;
        const rectFn = barRadius && 'roundRect' in ctx ? 'roundRect' : 'rect';
        ctx.beginPath();
        let prevX = 0;
        let maxTop = 0;
        let maxBottom = 0;
        for (let i = 0; i <= length; i++) {
            const x = Math.round(i * barIndexScale);
            if (x > prevX) {
                const topBarHeight = Math.round(maxTop * halfHeight * vScale);
                const bottomBarHeight = Math.round(maxBottom * halfHeight * vScale);
                const barHeight = topBarHeight + bottomBarHeight || 1;
                // Vertical alignment
                let y = halfHeight - topBarHeight;
                if (options.barAlign === 'top') {
                    y = 0;
                }
                else if (options.barAlign === 'bottom') {
                    y = height - barHeight;
                }
                ctx[rectFn](prevX * (barWidth + barGap), y, barWidth, barHeight, barRadius);
                prevX = x;
                maxTop = 0;
                maxBottom = 0;
            }
            const magnitudeTop = Math.abs(topChannel[i] || 0);
            const magnitudeBottom = Math.abs(bottomChannel[i] || 0);
            if (magnitudeTop > maxTop)
                maxTop = magnitudeTop;
            if (magnitudeBottom > maxBottom)
                maxBottom = magnitudeBottom;
        }
        ctx.fill();
        ctx.closePath();
    }
    renderLineWaveform(channelData, _options, ctx, vScale) {
        const drawChannel = (index) => {
            const channel = channelData[index] || channelData[0];
            const length = channel.length;
            const { height } = ctx.canvas;
            const halfHeight = height / 2;
            const hScale = ctx.canvas.width / length;
            ctx.moveTo(0, halfHeight);
            let prevX = 0;
            let max = 0;
            for (let i = 0; i <= length; i++) {
                const x = Math.round(i * hScale);
                if (x > prevX) {
                    const h = Math.round(max * halfHeight * vScale) || 1;
                    const y = halfHeight + h * (index === 0 ? -1 : 1);
                    ctx.lineTo(prevX, y);
                    prevX = x;
                    max = 0;
                }
                const value = Math.abs(channel[i] || 0);
                if (value > max)
                    max = value;
            }
            ctx.lineTo(prevX, halfHeight);
        };
        ctx.beginPath();
        drawChannel(0);
        drawChannel(1);
        ctx.fill();
        ctx.closePath();
    }
    renderWaveform(channelData, options, ctx) {
        ctx.fillStyle = this.convertColorValues(options.waveColor);
        // Custom rendering function
        if (options.renderFunction) {
            options.renderFunction(channelData, ctx);
            return;
        }
        // Vertical scaling
        let vScale = options.barHeight || 1;
        if (options.normalize) {
            const max = Array.from(channelData[0]).reduce((max, value) => Math.max(max, Math.abs(value)), 0);
            vScale = max ? 1 / max : 1;
        }
        // Render waveform as bars
        if (options.barWidth || options.barGap || options.barAlign) {
            this.renderBarWaveform(channelData, options, ctx, vScale);
            return;
        }
        // Render waveform as a polyline
        this.renderLineWaveform(channelData, options, ctx, vScale);
    }
    renderSingleCanvas(data, options, width, height, offset, canvasContainer, progressContainer) {
        const pixelRatio = this.getPixelRatio();
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(width * pixelRatio);
        canvas.height = Math.round(height * pixelRatio);
        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;
        canvas.style.left = `${Math.round(offset)}px`;
        canvasContainer.appendChild(canvas);
        const ctx = canvas.getContext('2d');
        this.renderWaveform(data, options, ctx);
        // Draw a progress canvas
        if (canvas.width > 0 && canvas.height > 0) {
            const progressCanvas = canvas.cloneNode();
            const progressCtx = progressCanvas.getContext('2d');
            progressCtx.drawImage(canvas, 0, 0);
            // Set the composition method to draw only where the waveform is drawn
            progressCtx.globalCompositeOperation = 'source-in';
            progressCtx.fillStyle = this.convertColorValues(options.progressColor);
            // This rectangle acts as a mask thanks to the composition method
            progressCtx.fillRect(0, 0, canvas.width, canvas.height);
            progressContainer.appendChild(progressCanvas);
        }
    }
    renderMultiCanvas(channelData, options, width, height, canvasContainer, progressContainer) {
        const pixelRatio = this.getPixelRatio();
        const { clientWidth } = this.scrollContainer;
        const totalWidth = width / pixelRatio;
        let singleCanvasWidth = Math.min(Renderer.MAX_CANVAS_WIDTH, clientWidth, totalWidth);
        let drawnIndexes = {};
        // Adjust width to avoid gaps between canvases when using bars
        if (options.barWidth || options.barGap) {
            const barWidth = options.barWidth || 0.5;
            const barGap = options.barGap || barWidth / 2;
            const totalBarWidth = barWidth + barGap;
            if (singleCanvasWidth % totalBarWidth !== 0) {
                singleCanvasWidth = Math.floor(singleCanvasWidth / totalBarWidth) * totalBarWidth;
            }
        }
        // Draw a single canvas
        const draw = (index) => {
            if (index < 0 || index >= numCanvases)
                return;
            if (drawnIndexes[index])
                return;
            drawnIndexes[index] = true;
            const offset = index * singleCanvasWidth;
            const clampedWidth = Math.min(totalWidth - offset, singleCanvasWidth);
            if (clampedWidth <= 0)
                return;
            const data = channelData.map((channel) => {
                const start = Math.floor((offset / totalWidth) * channel.length);
                const end = Math.floor(((offset + clampedWidth) / totalWidth) * channel.length);
                return channel.slice(start, end);
            });
            this.renderSingleCanvas(data, options, clampedWidth, height, offset, canvasContainer, progressContainer);
        };
        // Clear canvases to avoid too many DOM nodes
        const clearCanvases = () => {
            if (Object.keys(drawnIndexes).length > Renderer.MAX_NODES) {
                canvasContainer.innerHTML = '';
                progressContainer.innerHTML = '';
                drawnIndexes = {};
            }
        };
        // Calculate how many canvases to render
        const numCanvases = Math.ceil(totalWidth / singleCanvasWidth);
        // Render all canvases if the waveform doesn't scroll
        if (!this.isScrollable) {
            for (let i = 0; i < numCanvases; i++) {
                draw(i);
            }
            return;
        }
        // Lazy rendering
        const viewPosition = this.scrollContainer.scrollLeft / totalWidth;
        const startCanvas = Math.floor(viewPosition * numCanvases);
        // Draw the canvases in the viewport first
        draw(startCanvas - 1);
        draw(startCanvas);
        draw(startCanvas + 1);
        // Subscribe to the scroll event to draw additional canvases
        if (numCanvases > 1) {
            this.unsubscribeOnScroll = this.on('scroll', () => {
                const { scrollLeft } = this.scrollContainer;
                const canvasIndex = Math.floor((scrollLeft / totalWidth) * numCanvases);
                clearCanvases();
                draw(canvasIndex - 1);
                draw(canvasIndex);
                draw(canvasIndex + 1);
            });
        }
    }
    renderChannel(channelData, _a, width, channelIndex) {
        var { overlay } = _a, options = __rest(_a, ["overlay"]);
        // A container for canvases
        const canvasContainer = document.createElement('div');
        const height = this.getHeight(options.height, options.splitChannels);
        canvasContainer.style.height = `${height}px`;
        if (overlay && channelIndex > 0) {
            canvasContainer.style.marginTop = `-${height}px`;
        }
        this.canvasWrapper.style.minHeight = `${height}px`;
        this.canvasWrapper.appendChild(canvasContainer);
        // A container for progress canvases
        const progressContainer = canvasContainer.cloneNode();
        this.progressWrapper.appendChild(progressContainer);
        // Render the waveform
        this.renderMultiCanvas(channelData, options, width, height, canvasContainer, progressContainer);
    }
    render(audioData) {
        return __awaiter(this, void 0, void 0, function* () {
            var _a;
            // Clear previous timeouts
            this.timeouts.forEach((clear) => clear());
            this.timeouts = [];
            // Clear the canvases
            this.canvasWrapper.innerHTML = '';
            this.progressWrapper.innerHTML = '';
            // Width
            if (this.options.width != null) {
                this.scrollContainer.style.width =
                    typeof this.options.width === 'number' ? `${this.options.width}px` : this.options.width;
            }
            // Determine the width of the waveform
            const pixelRatio = this.getPixelRatio();
            const parentWidth = this.scrollContainer.clientWidth;
            const scrollWidth = Math.ceil(audioData.duration * (this.options.minPxPerSec || 0));
            // Whether the container should scroll
            this.isScrollable = scrollWidth > parentWidth;
            const useParentWidth = this.options.fillParent && !this.isScrollable;
            // Width of the waveform in pixels
            const width = (useParentWidth ? parentWidth : scrollWidth) * pixelRatio;
            // Set the width of the wrapper
            this.wrapper.style.width = useParentWidth ? '100%' : `${scrollWidth}px`;
            // Set additional styles
            this.scrollContainer.style.overflowX = this.isScrollable ? 'auto' : 'hidden';
            this.scrollContainer.classList.toggle('noScrollbar', !!this.options.hideScrollbar);
            this.cursor.style.backgroundColor = `${this.options.cursorColor || this.options.progressColor}`;
            this.cursor.style.width = `${this.options.cursorWidth}px`;
            this.audioData = audioData;
            this.emit('render');
            // Render the waveform
            if (this.options.splitChannels) {
                // Render a waveform for each channel
                for (let i = 0; i < audioData.numberOfChannels; i++) {
                    const options = Object.assign(Object.assign({}, this.options), (_a = this.options.splitChannels) === null || _a === void 0 ? void 0 : _a[i]);
                    this.renderChannel([audioData.getChannelData(i)], options, width, i);
                }
            }
            else {
                // Render a single waveform for the first two channels (left and right)
                const channels = [audioData.getChannelData(0)];
                if (audioData.numberOfChannels > 1)
                    channels.push(audioData.getChannelData(1));
                this.renderChannel(channels, this.options, width, 0);
            }
            // Must be emitted asynchronously for backward compatibility
            Promise.resolve().then(() => this.emit('rendered'));
        });
    }
    reRender() {
        var _a;
        (_a = this.unsubscribeOnScroll) === null || _a === void 0 ? void 0 : _a.call(this);
        delete this.unsubscribeOnScroll;
        // Return if the waveform has not been rendered yet
        if (!this.audioData)
            return;
        // Remember the current cursor position
        const { scrollWidth } = this.scrollContainer;
        const { right: before } = this.progressWrapper.getBoundingClientRect();
        // Re-render the waveform
        this.render(this.audioData);
        // Adjust the scroll position so that the cursor stays in the same place
        if (this.isScrollable && scrollWidth !== this.scrollContainer.scrollWidth) {
            const { right: after } = this.progressWrapper.getBoundingClientRect();
            let delta = after - before;
            // to limit compounding floating-point drift
            // we need to round to the half px furthest from 0
            delta *= 2;
            delta = delta < 0 ? Math.floor(delta) : Math.ceil(delta);
            delta /= 2;
            this.scrollContainer.scrollLeft += delta;
        }
    }
    zoom(minPxPerSec) {
        this.options.minPxPerSec = minPxPerSec;
        this.reRender();
    }
    scrollIntoView(progress, isPlaying = false) {
        const { scrollLeft, scrollWidth, clientWidth } = this.scrollContainer;
        const progressWidth = progress * scrollWidth;
        const startEdge = scrollLeft;
        const endEdge = scrollLeft + clientWidth;
        const middle = clientWidth / 2;
        if (this.isDragging) {
            // Scroll when dragging close to the edge of the viewport
            const minGap = 30;
            if (progressWidth + minGap > endEdge) {
                this.scrollContainer.scrollLeft += minGap;
            }
            else if (progressWidth - minGap < startEdge) {
                this.scrollContainer.scrollLeft -= minGap;
            }
        }
        else {
            if (progressWidth < startEdge || progressWidth > endEdge) {
                this.scrollContainer.scrollLeft = progressWidth - (this.options.autoCenter ? middle : 0);
            }
            // Keep the cursor centered when playing
            const center = progressWidth - scrollLeft - middle;
            if (isPlaying && this.options.autoCenter && center > 0) {
                this.scrollContainer.scrollLeft += Math.min(center, 10);
            }
        }
        // Emit the scroll event
        {
            const newScroll = this.scrollContainer.scrollLeft;
            const startX = newScroll / scrollWidth;
            const endX = (newScroll + clientWidth) / scrollWidth;
            this.emit('scroll', startX, endX, newScroll, newScroll + clientWidth);
        }
    }
    renderProgress(progress, isPlaying) {
        if (isNaN(progress))
            return;
        const percents = progress * 100;
        this.canvasWrapper.style.clipPath = `polygon(${percents}% 0, 100% 0, 100% 100%, ${percents}% 100%)`;
        this.progressWrapper.style.width = `${percents}%`;
        this.cursor.style.left = `${percents}%`;
        this.cursor.style.transform = `translateX(-${Math.round(percents) === 100 ? this.options.cursorWidth : 0}px)`;
        if (this.isScrollable && this.options.autoScroll) {
            this.scrollIntoView(progress, isPlaying);
        }
    }
    exportImage(format, quality, type) {
        return __awaiter(this, void 0, void 0, function* () {
            const canvases = this.canvasWrapper.querySelectorAll('canvas');
            if (!canvases.length) {
                throw new Error('No waveform data');
            }
            // Data URLs
            if (type === 'dataURL') {
                const images = Array.from(canvases).map((canvas) => canvas.toDataURL(format, quality));
                return Promise.resolve(images);
            }
            // Blobs
            return Promise.all(Array.from(canvases).map((canvas) => {
                return new Promise((resolve, reject) => {
                    canvas.toBlob((blob) => {
                        blob ? resolve(blob) : reject(new Error('Could not export image'));
                    }, format, quality);
                });
            }));
        });
    }
}
Renderer.MAX_CANVAS_WIDTH = 8000;
Renderer.MAX_NODES = 10;
/* harmony default export */ __webpack_exports__["default"] = (Renderer);


/***/ }),

/***/ "./node_modules/wavesurfer.js/dist/timer.js":
/*!**************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/timer.js ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _event_emitter_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./event-emitter.js */ "./node_modules/wavesurfer.js/dist/event-emitter.js");

class Timer extends _event_emitter_js__WEBPACK_IMPORTED_MODULE_0__["default"] {
    constructor() {
        super(...arguments);
        this.unsubscribe = () => undefined;
    }
    start() {
        this.unsubscribe = this.on('tick', () => {
            requestAnimationFrame(() => {
                this.emit('tick');
            });
        });
        this.emit('tick');
    }
    stop() {
        this.unsubscribe();
    }
    destroy() {
        this.unsubscribe();
    }
}
/* harmony default export */ __webpack_exports__["default"] = (Timer);


/***/ }),

/***/ "./node_modules/wavesurfer.js/dist/wavesurfer.js":
/*!*******************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/wavesurfer.js ***!
  \*******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _base_plugin_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./base-plugin.js */ "./node_modules/wavesurfer.js/dist/base-plugin.js");
/* harmony import */ var _decoder_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./decoder.js */ "./node_modules/wavesurfer.js/dist/decoder.js");
/* harmony import */ var _dom_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./dom.js */ "./node_modules/wavesurfer.js/dist/dom.js");
/* harmony import */ var _fetcher_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./fetcher.js */ "./node_modules/wavesurfer.js/dist/fetcher.js");
/* harmony import */ var _player_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./player.js */ "./node_modules/wavesurfer.js/dist/player.js");
/* harmony import */ var _renderer_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./renderer.js */ "./node_modules/wavesurfer.js/dist/renderer.js");
/* harmony import */ var _timer_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./timer.js */ "./node_modules/wavesurfer.js/dist/timer.js");
/* harmony import */ var _webaudio_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./webaudio.js */ "./node_modules/wavesurfer.js/dist/webaudio.js");
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};








const defaultOptions = {
    waveColor: '#999',
    progressColor: '#555',
    cursorWidth: 1,
    minPxPerSec: 0,
    fillParent: true,
    interact: true,
    dragToSeek: false,
    autoScroll: true,
    autoCenter: true,
    sampleRate: 8000,
};
class WaveSurfer extends _player_js__WEBPACK_IMPORTED_MODULE_4__["default"] {
    /** Create a new WaveSurfer instance */
    static create(options) {
        return new WaveSurfer(options);
    }
    /** Create a new WaveSurfer instance */
    constructor(options) {
        const media = options.media ||
            (options.backend === 'WebAudio' ? new _webaudio_js__WEBPACK_IMPORTED_MODULE_7__["default"]() : undefined);
        super({
            media,
            mediaControls: options.mediaControls,
            autoplay: options.autoplay,
            playbackRate: options.audioRate,
        });
        this.plugins = [];
        this.decodedData = null;
        this.subscriptions = [];
        this.mediaSubscriptions = [];
        this.abortController = null;
        this.options = Object.assign({}, defaultOptions, options);
        this.timer = new _timer_js__WEBPACK_IMPORTED_MODULE_6__["default"]();
        const audioElement = media ? undefined : this.getMediaElement();
        this.renderer = new _renderer_js__WEBPACK_IMPORTED_MODULE_5__["default"](this.options, audioElement);
        this.initPlayerEvents();
        this.initRendererEvents();
        this.initTimerEvents();
        this.initPlugins();
        // Read the initial URL before load has been called
        const initialUrl = this.options.url || this.getSrc() || '';
        // Init and load async to allow external events to be registered
        Promise.resolve().then(() => {
            this.emit('init');
            // Load audio if URL or an external media with an src is passed,
            // of render w/o audio if pre-decoded peaks and duration are provided
            const { peaks, duration } = this.options;
            if (initialUrl || (peaks && duration)) {
                // Swallow async errors because they cannot be caught from a constructor call.
                // Subscribe to the wavesurfer's error event to handle them.
                this.load(initialUrl, peaks, duration).catch(() => null);
            }
        });
    }
    updateProgress(currentTime = this.getCurrentTime()) {
        this.renderer.renderProgress(currentTime / this.getDuration(), this.isPlaying());
        return currentTime;
    }
    initTimerEvents() {
        // The timer fires every 16ms for a smooth progress animation
        this.subscriptions.push(this.timer.on('tick', () => {
            if (!this.isSeeking()) {
                const currentTime = this.updateProgress();
                this.emit('timeupdate', currentTime);
                this.emit('audioprocess', currentTime);
            }
        }));
    }
    initPlayerEvents() {
        if (this.isPlaying()) {
            this.emit('play');
            this.timer.start();
        }
        this.mediaSubscriptions.push(this.onMediaEvent('timeupdate', () => {
            const currentTime = this.updateProgress();
            this.emit('timeupdate', currentTime);
        }), this.onMediaEvent('play', () => {
            this.emit('play');
            this.timer.start();
        }), this.onMediaEvent('pause', () => {
            this.emit('pause');
            this.timer.stop();
        }), this.onMediaEvent('emptied', () => {
            this.timer.stop();
        }), this.onMediaEvent('ended', () => {
            this.emit('finish');
        }), this.onMediaEvent('seeking', () => {
            this.emit('seeking', this.getCurrentTime());
        }), this.onMediaEvent('error', (err) => {
            this.emit('error', err.error);
        }));
    }
    initRendererEvents() {
        this.subscriptions.push(
        // Seek on click
        this.renderer.on('click', (relativeX, relativeY) => {
            if (this.options.interact) {
                this.seekTo(relativeX);
                this.emit('interaction', relativeX * this.getDuration());
                this.emit('click', relativeX, relativeY);
            }
        }), 
        // Double click
        this.renderer.on('dblclick', (relativeX, relativeY) => {
            this.emit('dblclick', relativeX, relativeY);
        }), 
        // Scroll
        this.renderer.on('scroll', (startX, endX, scrollLeft, scrollRight) => {
            const duration = this.getDuration();
            this.emit('scroll', startX * duration, endX * duration, scrollLeft, scrollRight);
        }), 
        // Redraw
        this.renderer.on('render', () => {
            this.emit('redraw');
        }), 
        // RedrawComplete
        this.renderer.on('rendered', () => {
            this.emit('redrawcomplete');
        }), 
        // DragStart
        this.renderer.on('dragstart', (relativeX) => {
            this.emit('dragstart', relativeX);
        }), 
        // DragEnd
        this.renderer.on('dragend', (relativeX) => {
            this.emit('dragend', relativeX);
        }));
        // Drag
        {
            let debounce;
            this.subscriptions.push(this.renderer.on('drag', (relativeX) => {
                if (!this.options.interact)
                    return;
                // Update the visual position
                this.renderer.renderProgress(relativeX);
                // Set the audio position with a debounce
                clearTimeout(debounce);
                let debounceTime;
                if (this.isPlaying()) {
                    debounceTime = 0;
                }
                else if (this.options.dragToSeek === true) {
                    debounceTime = 200;
                }
                else if (typeof this.options.dragToSeek === 'object' && this.options.dragToSeek !== undefined) {
                    debounceTime = this.options.dragToSeek['debounceTime'];
                }
                debounce = setTimeout(() => {
                    this.seekTo(relativeX);
                }, debounceTime);
                this.emit('interaction', relativeX * this.getDuration());
                this.emit('drag', relativeX);
            }));
        }
    }
    initPlugins() {
        var _a;
        if (!((_a = this.options.plugins) === null || _a === void 0 ? void 0 : _a.length))
            return;
        this.options.plugins.forEach((plugin) => {
            this.registerPlugin(plugin);
        });
    }
    unsubscribePlayerEvents() {
        this.mediaSubscriptions.forEach((unsubscribe) => unsubscribe());
        this.mediaSubscriptions = [];
    }
    /** Set new wavesurfer options and re-render it */
    setOptions(options) {
        this.options = Object.assign({}, this.options, options);
        this.renderer.setOptions(this.options);
        if (options.audioRate) {
            this.setPlaybackRate(options.audioRate);
        }
        if (options.mediaControls != null) {
            this.getMediaElement().controls = options.mediaControls;
        }
    }
    /** Register a wavesurfer.js plugin */
    registerPlugin(plugin) {
        plugin._init(this);
        this.plugins.push(plugin);
        // Unregister plugin on destroy
        this.subscriptions.push(plugin.once('destroy', () => {
            this.plugins = this.plugins.filter((p) => p !== plugin);
        }));
        return plugin;
    }
    /** For plugins only: get the waveform wrapper div */
    getWrapper() {
        return this.renderer.getWrapper();
    }
    /** For plugins only: get the scroll container client width */
    getWidth() {
        return this.renderer.getWidth();
    }
    /** Get the current scroll position in pixels */
    getScroll() {
        return this.renderer.getScroll();
    }
    /** Set the current scroll position in pixels */
    setScroll(pixels) {
        return this.renderer.setScroll(pixels);
    }
    /** Move the start of the viewing window to a specific time in the audio (in seconds) */
    setScrollTime(time) {
        const percentage = time / this.getDuration();
        this.renderer.setScrollPercentage(percentage);
    }
    /** Get all registered plugins */
    getActivePlugins() {
        return this.plugins;
    }
    loadAudio(url, blob, channelData, duration) {
        return __awaiter(this, void 0, void 0, function* () {
            var _a;
            this.emit('load', url);
            if (!this.options.media && this.isPlaying())
                this.pause();
            this.decodedData = null;
            // Fetch the entire audio as a blob if pre-decoded data is not provided
            if (!blob && !channelData) {
                const fetchParams = this.options.fetchParams || {};
                if (window.AbortController && !fetchParams.signal) {
                    this.abortController = new AbortController();
                    fetchParams.signal = (_a = this.abortController) === null || _a === void 0 ? void 0 : _a.signal;
                }
                const onProgress = (percentage) => this.emit('loading', percentage);
                blob = yield _fetcher_js__WEBPACK_IMPORTED_MODULE_3__["default"].fetchBlob(url, onProgress, fetchParams);
            }
            // Set the mediaelement source
            this.setSrc(url, blob);
            // Wait for the audio duration
            const audioDuration = yield new Promise((resolve) => {
                const staticDuration = duration || this.getDuration();
                if (staticDuration) {
                    resolve(staticDuration);
                }
                else {
                    this.mediaSubscriptions.push(this.onMediaEvent('loadedmetadata', () => resolve(this.getDuration()), { once: true }));
                }
            });
            // Set the duration if the player is a WebAudioPlayer without a URL
            if (!url && !blob) {
                const media = this.getMediaElement();
                if (media instanceof _webaudio_js__WEBPACK_IMPORTED_MODULE_7__["default"]) {
                    media.duration = audioDuration;
                }
            }
            // Decode the audio data or use user-provided peaks
            if (channelData) {
                this.decodedData = _decoder_js__WEBPACK_IMPORTED_MODULE_1__["default"].createBuffer(channelData, audioDuration || 0);
            }
            else if (blob) {
                const arrayBuffer = yield blob.arrayBuffer();
                this.decodedData = yield _decoder_js__WEBPACK_IMPORTED_MODULE_1__["default"].decode(arrayBuffer, this.options.sampleRate);
            }
            if (this.decodedData) {
                this.emit('decode', this.getDuration());
                this.renderer.render(this.decodedData);
            }
            this.emit('ready', this.getDuration());
        });
    }
    /** Load an audio file by URL, with optional pre-decoded audio data */
    load(url, channelData, duration) {
        return __awaiter(this, void 0, void 0, function* () {
            try {
                return yield this.loadAudio(url, undefined, channelData, duration);
            }
            catch (err) {
                this.emit('error', err);
                throw err;
            }
        });
    }
    /** Load an audio blob */
    loadBlob(blob, channelData, duration) {
        return __awaiter(this, void 0, void 0, function* () {
            try {
                return yield this.loadAudio('', blob, channelData, duration);
            }
            catch (err) {
                this.emit('error', err);
                throw err;
            }
        });
    }
    /** Zoom the waveform by a given pixels-per-second factor */
    zoom(minPxPerSec) {
        if (!this.decodedData) {
            throw new Error('No audio loaded');
        }
        this.renderer.zoom(minPxPerSec);
        this.emit('zoom', minPxPerSec);
    }
    /** Get the decoded audio data */
    getDecodedData() {
        return this.decodedData;
    }
    /** Get decoded peaks */
    exportPeaks({ channels = 2, maxLength = 8000, precision = 10000 } = {}) {
        if (!this.decodedData) {
            throw new Error('The audio has not been decoded yet');
        }
        const maxChannels = Math.min(channels, this.decodedData.numberOfChannels);
        const peaks = [];
        for (let i = 0; i < maxChannels; i++) {
            const channel = this.decodedData.getChannelData(i);
            const data = [];
            const sampleSize = channel.length / maxLength;
            for (let i = 0; i < maxLength; i++) {
                const sample = channel.slice(Math.floor(i * sampleSize), Math.ceil((i + 1) * sampleSize));
                let max = 0;
                for (let x = 0; x < sample.length; x++) {
                    const n = sample[x];
                    if (Math.abs(n) > Math.abs(max))
                        max = n;
                }
                data.push(Math.round(max * precision) / precision);
            }
            peaks.push(data);
        }
        return peaks;
    }
    /** Get the duration of the audio in seconds */
    getDuration() {
        let duration = super.getDuration() || 0;
        // Fall back to the decoded data duration if the media duration is incorrect
        if ((duration === 0 || duration === Infinity) && this.decodedData) {
            duration = this.decodedData.duration;
        }
        return duration;
    }
    /** Toggle if the waveform should react to clicks */
    toggleInteraction(isInteractive) {
        this.options.interact = isInteractive;
    }
    /** Jump to a specific time in the audio (in seconds) */
    setTime(time) {
        super.setTime(time);
        this.updateProgress(time);
        this.emit('timeupdate', time);
    }
    /** Seek to a percentage of audio as [0..1] (0 = beginning, 1 = end) */
    seekTo(progress) {
        const time = this.getDuration() * progress;
        this.setTime(time);
    }
    /** Play or pause the audio */
    playPause() {
        return __awaiter(this, void 0, void 0, function* () {
            return this.isPlaying() ? this.pause() : this.play();
        });
    }
    /** Stop the audio and go to the beginning */
    stop() {
        this.pause();
        this.setTime(0);
    }
    /** Skip N or -N seconds from the current position */
    skip(seconds) {
        this.setTime(this.getCurrentTime() + seconds);
    }
    /** Empty the waveform */
    empty() {
        this.load('', [[0]], 0.001);
    }
    /** Set HTML media element */
    setMediaElement(element) {
        this.unsubscribePlayerEvents();
        super.setMediaElement(element);
        this.initPlayerEvents();
    }
    exportImage() {
        return __awaiter(this, arguments, void 0, function* (format = 'image/png', quality = 1, type = 'dataURL') {
            return this.renderer.exportImage(format, quality, type);
        });
    }
    /** Unmount wavesurfer */
    destroy() {
        var _a;
        this.emit('destroy');
        (_a = this.abortController) === null || _a === void 0 ? void 0 : _a.abort();
        this.plugins.forEach((plugin) => plugin.destroy());
        this.subscriptions.forEach((unsubscribe) => unsubscribe());
        this.unsubscribePlayerEvents();
        this.timer.destroy();
        this.renderer.destroy();
        super.destroy();
    }
}
WaveSurfer.BasePlugin = _base_plugin_js__WEBPACK_IMPORTED_MODULE_0__["default"];
WaveSurfer.dom = _dom_js__WEBPACK_IMPORTED_MODULE_2__;
/* harmony default export */ __webpack_exports__["default"] = (WaveSurfer);


/***/ }),

/***/ "./node_modules/wavesurfer.js/dist/webaudio.js":
/*!*****************************************************!*\
  !*** ./node_modules/wavesurfer.js/dist/webaudio.js ***!
  \*****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _event_emitter_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./event-emitter.js */ "./node_modules/wavesurfer.js/dist/event-emitter.js");
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};

/**
 * A Web Audio buffer player emulating the behavior of an HTML5 Audio element.
 */
class WebAudioPlayer extends _event_emitter_js__WEBPACK_IMPORTED_MODULE_0__["default"] {
    constructor(audioContext = new AudioContext()) {
        super();
        this.bufferNode = null;
        this.playStartTime = 0;
        this.playedDuration = 0;
        this._muted = false;
        this._playbackRate = 1;
        this._duration = undefined;
        this.buffer = null;
        this.currentSrc = '';
        this.paused = true;
        this.crossOrigin = null;
        this.seeking = false;
        this.autoplay = false;
        /** Subscribe to an event. Returns an unsubscribe function. */
        this.addEventListener = this.on;
        /** Unsubscribe from an event */
        this.removeEventListener = this.un;
        this.audioContext = audioContext;
        this.gainNode = this.audioContext.createGain();
        this.gainNode.connect(this.audioContext.destination);
    }
    load() {
        return __awaiter(this, void 0, void 0, function* () {
            return;
        });
    }
    get src() {
        return this.currentSrc;
    }
    set src(value) {
        this.currentSrc = value;
        this._duration = undefined;
        if (!value) {
            this.buffer = null;
            this.emit('emptied');
            return;
        }
        fetch(value)
            .then((response) => {
            if (response.status >= 400) {
                throw new Error(`Failed to fetch ${value}: ${response.status} (${response.statusText})`);
            }
            return response.arrayBuffer();
        })
            .then((arrayBuffer) => {
            if (this.currentSrc !== value)
                return null;
            return this.audioContext.decodeAudioData(arrayBuffer);
        })
            .then((audioBuffer) => {
            if (this.currentSrc !== value)
                return;
            this.buffer = audioBuffer;
            this.emit('loadedmetadata');
            this.emit('canplay');
            if (this.autoplay)
                this.play();
        });
    }
    _play() {
        var _a;
        if (!this.paused)
            return;
        this.paused = false;
        (_a = this.bufferNode) === null || _a === void 0 ? void 0 : _a.disconnect();
        this.bufferNode = this.audioContext.createBufferSource();
        if (this.buffer) {
            this.bufferNode.buffer = this.buffer;
        }
        this.bufferNode.playbackRate.value = this._playbackRate;
        this.bufferNode.connect(this.gainNode);
        let currentPos = this.playedDuration * this._playbackRate;
        if (currentPos >= this.duration) {
            currentPos = 0;
            this.playedDuration = 0;
        }
        this.bufferNode.start(this.audioContext.currentTime, currentPos);
        this.playStartTime = this.audioContext.currentTime;
        this.bufferNode.onended = () => {
            if (this.currentTime >= this.duration) {
                this.pause();
                this.emit('ended');
            }
        };
    }
    _pause() {
        var _a;
        this.paused = true;
        (_a = this.bufferNode) === null || _a === void 0 ? void 0 : _a.stop();
        this.playedDuration += this.audioContext.currentTime - this.playStartTime;
    }
    play() {
        return __awaiter(this, void 0, void 0, function* () {
            if (!this.paused)
                return;
            this._play();
            this.emit('play');
        });
    }
    pause() {
        if (this.paused)
            return;
        this._pause();
        this.emit('pause');
    }
    stopAt(timeSeconds) {
        var _a, _b;
        const delay = timeSeconds - this.currentTime;
        (_a = this.bufferNode) === null || _a === void 0 ? void 0 : _a.stop(this.audioContext.currentTime + delay);
        (_b = this.bufferNode) === null || _b === void 0 ? void 0 : _b.addEventListener('ended', () => {
            this.bufferNode = null;
            this.pause();
        }, { once: true });
    }
    setSinkId(deviceId) {
        return __awaiter(this, void 0, void 0, function* () {
            const ac = this.audioContext;
            return ac.setSinkId(deviceId);
        });
    }
    get playbackRate() {
        return this._playbackRate;
    }
    set playbackRate(value) {
        this._playbackRate = value;
        if (this.bufferNode) {
            this.bufferNode.playbackRate.value = value;
        }
    }
    get currentTime() {
        const time = this.paused
            ? this.playedDuration
            : this.playedDuration + (this.audioContext.currentTime - this.playStartTime);
        return time * this._playbackRate;
    }
    set currentTime(value) {
        const wasPlaying = !this.paused;
        wasPlaying && this._pause();
        this.playedDuration = value / this._playbackRate;
        wasPlaying && this._play();
        this.emit('seeking');
        this.emit('timeupdate');
    }
    get duration() {
        var _a, _b;
        return (_a = this._duration) !== null && _a !== void 0 ? _a : (((_b = this.buffer) === null || _b === void 0 ? void 0 : _b.duration) || 0);
    }
    set duration(value) {
        this._duration = value;
    }
    get volume() {
        return this.gainNode.gain.value;
    }
    set volume(value) {
        this.gainNode.gain.value = value;
        this.emit('volumechange');
    }
    get muted() {
        return this._muted;
    }
    set muted(value) {
        if (this._muted === value)
            return;
        this._muted = value;
        if (this._muted) {
            this.gainNode.disconnect();
        }
        else {
            this.gainNode.connect(this.audioContext.destination);
        }
    }
    canPlayType(mimeType) {
        return /^(audio|video)\//.test(mimeType);
    }
    /** Get the GainNode used to play the audio. Can be used to attach filters. */
    getGainNode() {
        return this.gainNode;
    }
    /** Get decoded audio */
    getChannelData() {
        const channels = [];
        if (!this.buffer)
            return channels;
        const numChannels = this.buffer.numberOfChannels;
        for (let i = 0; i < numChannels; i++) {
            channels.push(this.buffer.getChannelData(i));
        }
        return channels;
    }
}
/* harmony default export */ __webpack_exports__["default"] = (WebAudioPlayer);


/***/ }),

/***/ "./src/js/recorder.js":
/*!****************************!*\
  !*** ./src/js/recorder.js ***!
  \****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var wavesurfer_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! wavesurfer.js */ "./node_modules/wavesurfer.js/dist/wavesurfer.js");
/* harmony import */ var wavesurfer_js_dist_plugins_record_esm_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! wavesurfer.js/dist/plugins/record.esm.js */ "./node_modules/wavesurfer.js/dist/plugins/record.esm.js");


window.WaveSurfer = wavesurfer_js__WEBPACK_IMPORTED_MODULE_0__["default"];
window.RecordPlugin = wavesurfer_js_dist_plugins_record_esm_js__WEBPACK_IMPORTED_MODULE_1__["default"];

/***/ })

/******/ });
//# sourceMappingURL=recorder.js.map