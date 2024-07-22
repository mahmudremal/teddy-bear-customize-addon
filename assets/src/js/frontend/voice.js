/**
 * Voice Recorder for frontend prompts.
 * 
 * @author @mahmudremal
 */
import icons from './icons';
import WaveSurfer from 'wavesurfer.js';
import RecordPlugin from 'wavesurfer.js/dist/plugins/record.esm.js';

class Voice {
  constructor(thisClass, row, root) {
    // console.log(root);return;
    row.cost = parseFloat(row.cost);
    row.product = parseInt(row.product);
    row.duration = parseFloat(row.duration);
    this.row = row;
    this.store = {};
    this.wave = false;
    this.duration = 20;
    this.meta_tag = 'Voice';
    this.recordedBlob = null;
    this.rootElement = false;
    this.WaveSurfer = WaveSurfer;
    this.RecordPlugin = RecordPlugin;
    this.product_id = thisClass.product?.id,
    this.init(thisClass, row, root);
  }
  init(thisClass, row, root) {
    // if (!(this?.WaveSurfer && this?.RecordPlugin)) {return;}
    const voiceClass = this;
    thisClass.cartIcon = icons.cart;
    this.audioExtensions = 'wav,bwf,raw,aiff,flac,m4a,pac,tta,wv,ast,aac,mp2,mp3,mp4,amr,s3m,3gp,act,au,dct,dss,gsm,m4p,mmf,mpc,ogg,oga,opus,ra,sln,vox'.split(',');
    voiceClass.implement_elements(root, thisClass);
  }
  do_store(mode, attach = false, thisClass) {
    this.row.attaced = this.row?.attaced??{};
    switch (mode) {
      case 'skip':
      case 'durExceed':
      case 'sizeExceed':
        this.row.attaced = {skip: true};
        break;
      case 'later':
        this.row.attaced = {later: true};
        break;
      case 'record':
      case 'upload':
        this.row.attaced = {blob: attach, method: mode};
        break;
      default:
        console.log('Unknown Request type', mode);
        break;
    }
    /**
     * Update cart calculated total on popup.
     */
    thisClass.updateTotalPrice();
    // 
  }
  implement_elements(rootElement, thisClass) {
    this.rootElement = rootElement;const voiceClass = this;
    this.actionButtons = document.createElement('div');
    this.contentsAreas = document.createElement('div');
    // 
    this.actionButtons.classList.add('do_recorder-actions');
    this.contentsAreas.classList.add('do_recorder-contents');
    // 
    this.rootElement.dataset.handled = true;
    // Create record button
    this.recordButton = document.createElement('button');
    this.recordButton.dataset.cost = ((rootElement.dataset?.cost??0) != '')?(rootElement.dataset?.cost??0):0;
    this.recordButton.type = 'button';
    this.recordButton.classList.add('do_recorder__record');
    this.recordButton.innerHTML = `${icons.misc}${thisClass.i18n?.record??'Record'}`;
    // this.recordButton.addEventListener('click', event => this.startRecording(event));
    this.actionButtons.appendChild(this.recordButton);

    // Create stop button
    // const stopButton = document.createElement('button');
    // stopButton.textContent = (thisClass.i18n?.stop??'Stop');stopButton.type = 'button';
    // stopButton.addEventListener('click', this.stopRecording);
    // this.actionButtons.appendChild(stopButton);

    // Instruction for recording audio
    this.audioInstructPreview = document.createElement('p');
    this.audioInstructPreview.classList.add('audio-record-instruction', 'd-none');
    this.audioInstructPreview.innerHTML = '';

    // Create release button
    this.playButton = document.createElement('button');
    this.playButton.innerHTML = icons.play;
    this.playButton.title = thisClass.i18n?.play??'Play';
    this.playButton.type = 'button';
    this.playButton.classList.add('do_recorder__play');
    this.playButton.addEventListener('click', (event) => {
      event.preventDefault();event.stopPropagation();
      voiceClass.playRecording(false, thisClass);
    });
    this.actionButtons.appendChild(this.playButton);

    // Download link
    this.downloadButton = document.createElement('a');
    this.downloadButton.innerHTML = thisClass.i18n?.download??'Download';
    this.downloadButton.classList.add('button', 'do_recorder__download');
    this.downloadButton.style.display = 'none';
    this.actionButtons.appendChild(this.downloadButton);

    // Upload Button
    this.uploadButton = document.createElement('button');
    this.uploadButton.classList.add('button', 'do_recorder__upload');
    this.uploadButton.type = 'button';
    this.uploadButton.innerHTML = icons.upload + (thisClass.i18n?.upload??'Upload');
    this.uploadInput = document.createElement('input');
    this.uploadInput.type = 'file';
    this.uploadInput.accept = 'audio/*;capture=microphone';
    this.uploadInput.dataset.cost = rootElement.dataset.cost;
    this.uploadButton.addEventListener('click', (event) => {
      event.preventDefault();event.stopPropagation();
      voiceClass.uploadInput.click();
    });
    this.uploadInput.addEventListener('change', (event) => {
      event.preventDefault();event.stopPropagation();
      Object.values(event.target.files).forEach(file => voiceClass.uploadAudio(file, thisClass));
    });
    this.actionButtons.appendChild(this.uploadButton);
    
    // Create release button
    this.releaseButton = document.createElement('button');
    // this.releaseButton.innerHTML = thisClass.i18n?.ipreferrecordlater??'I prefer to add my voice later';
    this.releaseButton.innerHTML = icons.mail + (thisClass.i18n?.add_later??'Add Later');
    this.releaseButton.type = 'button'; // 'i_dont_prefer_voice',
    this.releaseButton.classList.add('do_recorder__release');
    this.releaseButton.addEventListener('click', (event) => {
      event.preventDefault();event.stopPropagation();
      this.destroyWave();this.switchActive('later');
      this.releaseRecording(event, thisClass);
      this.hideError();
    });
    this.actionButtons.appendChild(this.releaseButton);
    
    // Create skip button
    this.skipButton = document.createElement('button');
    this.skipButton.innerHTML = icons.skip + (thisClass.i18n?.skip??'Skip');
    this.skipButton.type = 'button';
    this.skipButton.classList.add('do_recorder__skip');
    this.skipButton.addEventListener('click', (event) => {
      event.preventDefault();event.stopPropagation();
      this.destroyWave();
      this.playButtonAction('hide', thisClass);
      this.rootElement.classList.remove('visible_audio');
      // this.skipButton.classList.add('do_recorder__skipped');
      this.switchActive('skip');this.hideError();
      // this.releaseButton.classList.remove('do_recorder__released');
      // thisClass.removeAdditionalPrice(this.meta_tag, parseFloat(this.recordButton.dataset.cost), false, this.product_id);
      this.do_store('skip', false, thisClass);
      this.audioInstructPreview.classList.add('d-none');
      this.rootElement.parentElement.parentElement.parentElement.classList.remove('audio_instruction');
    });
    this.actionButtons.appendChild(this.skipButton);

    // Create audio element for preview
    const audioPreview = document.createElement('audio');
    // audioPreview.controls = true;
    // audioPreview.autoplay = true;
    // audioPreview.playsinline = true;
    audioPreview.style.display = 'none';
    this.contentsAreas.appendChild(audioPreview);

    // Instruction for recording audio
    this.contentsAreas.appendChild(this.audioInstructPreview);

    // Create audio element for preview
    this.wavePreview = document.createElement('div');
    this.wavePreview.classList.add('audio-preview');
    this.waveAudioPreview = document.createElement('div');
    this.waveAudioPreview.classList.add('audiopreview');
    this.waveAudioPreview.appendChild(this.playButton);
    var timer = document.createElement('div');timer.classList.add('audiopreview__timer');
    var seconds = document.createElement('span');seconds.classList.add('audiopreview__seconds');
    seconds.dataset.timerType = 's';timer.appendChild(seconds);

    this.waveAudioPreview.appendChild(this.wavePreview);
    this.waveAudioPreview.appendChild(timer);
    this.contentsAreas.appendChild(this.waveAudioPreview);
    // 
    this.audioPreview = audioPreview;
    this.recordedBlob = null;
    // 
    this.isRecording = false;
    voiceClass.recordButton.addEventListener('click', (event) => {
      event.preventDefault();event.stopPropagation();
      if (this.isRecording == false) {
        this.createWave();this.hideError();
        this.record = this.wave.registerPlugin(RecordPlugin.create());
        // 
        this.record.on('stopRecording', async () => {
          this.isRecording = false;
          this.downloadButton.href = this.audioPreview.src = this.record.getRecordedUrl();
          this.downloadButton.download = await this.recordedFileName();this.downloadButton.style.display = '';
          if (this?.recordInterval) {clearInterval(this.recordInterval);}
        });
        this.record.on('startRecording', () => {
          this.isRecording = true;
          this.rootElement.classList.add('visible_audio');
          this.downloadButton.href = this.audioPreview.src = '';
          this.downloadButton.download = '';this.downloadButton.style.display = 'none';
          let intervalSecond = 0,
          second = this.waveAudioPreview.querySelector('[data-timer-type="s"]');
          if (second) {
            second.innerHTML = '';
            this.recordInterval = setInterval(() => {
              intervalSecond++;second.innerHTML = (this.duration - intervalSecond) + ':00';
            }, 1000);
          }
          setTimeout(() => {
            if (this.record.isRecording()) {
              voiceClass.recordButton.click();
            }
          }, (this.duration * 1000));
        });
        // this.record.on('record-start', () => {});
        // this.record.on('record-end', () => {});
      }
      // 
      if (this.wave.isPlaying()) {
        this.wave.pause();
      }
      if (this.record.isRecording()) {
        this.record.stopRecording();
        this.playButtonAction('show', thisClass);
        voiceClass.recordButton.innerHTML = `${icons.misc}${thisClass.i18n?.record??'Record'}`;
        this.playButton.disabled = false;
        this.recordButton.dataset.cost = (
          this.recordButton.dataset.cost == ''
        )?'0':this.recordButton.dataset.cost;
        // thisClass.addAdditionalPrice(this.meta_tag, parseFloat(this.recordButton.dataset.cost), false, this.product_id);
        setTimeout(async () => {
          this.recordedBlob = await fetch(this.record.getRecordedUrl()).then(r => r.blob());
          this.do_store('record', this.recordedBlob, thisClass);
        }, 800);
        return;
      }
      voiceClass.recordButton.disabled = true;
      /**Or I can set on on ** markeed places */
      this.record.startRecording().then(() => {
        voiceClass.recordButton.innerHTML = `${icons.stop}${thisClass.i18n?.stop??'Stop'}`;
        voiceClass.recordButton.disabled = false;this.playButtonAction('hide', thisClass);
        // this.skipButton.classList.remove('do_recorder__skipped');
        this.switchActive('record');
        // this.releaseButton.classList.remove('do_recorder__released');
        this.audioInstructPreview.innerHTML = this.nl2br(wp.i18n.sprintf(thisClass.i18n?.audiorecord_instuction??'Please record your voice upto %s seconds.', this.duration));
        this.audioInstructPreview.classList.remove('d-none');
      }).catch(error => console.error(error));
    });
    // 
    rootElement.appendChild(this.actionButtons);
    rootElement.appendChild(this.contentsAreas);
  }
  destroyWave() {
    if (this.wave) {
      this.wave.destroy();
      this.wave = false;
    }
  }
  createWave() {
    this.destroyWave();
    this.wave = WaveSurfer.create({
      container: this.wavePreview,
      // waveColor: '#de424b',
      // progressColor: '#973137',
      waveColor: '#fec52e',
      progressColor: '#e63f51',
      barWidth: 5,
      // barHeight: 1,
      barRadius: 5,
      responsive: true,
      barGap: 1,
      height: 20,
    });
    this.wave.once('ready', () => {
      // this.playButton.onclick = () => {this.wave.playPause();}
      this.wave.on('play', () => {
        this.playButton.innerHTML = icons.pause;
        this.playButton.title = thisClass.i18n?.pause??'Pause';
      });
      this.wave.on('pause', () => {
        this.playButton.innerHTML = icons.play;
        this.playButton.title = thisClass.i18n?.play??'Play';
      });
      // this.wave.once('interaction', () => {this.wave.playPause()});
      this.wave.on('finish', () => {
        this.wave.setTime(0);
      });
    });
  }
  playRecording(type = false, thisClass) {
    if (!this.wave) {return;}
    switch (type) {
      case 'stop':
        this.wave.stop();
        break;
      case 'play':
        this.wave.play();
        break;
      default:
        this.wave.playPause();
        break;
    }
  }
  releaseRecording(event, thisClass) {
    this.playButtonAction('hide', thisClass);this.playRecording('stop', thisClass);
    this.recordedBlob = null;this.audioPreview.src = '';
    // thisClass.addAdditionalPrice(this.meta_tag, parseFloat(this.recordButton.dataset.cost), false, this.product_id);
    // thisClass.removeAdditionalPrice(thisClass.i18n?.voice??'Voice');
    this.do_store('later', false, thisClass);
    // this.releaseButton.innerHTML = icons.tick + this.releaseButton.innerHTML;
    // this.skipButton.classList.remove('do_recorder__skipped');
    // this.releaseButton.classList.add('do_recorder__released');
    const audiolater_instuction = thisClass.i18n?.audiolater_instuction??'1. Receive instructions & button in order email.\n2. Upload audio file anytime later.\n3. we will ship when your audio file is received.';
    this.audioInstructPreview.innerHTML = this.nl2br(audiolater_instuction);
    this.audioInstructPreview.classList.remove('d-none');
    this.rootElement.classList.remove('visible_audio');
    this.rootElement.parentElement.parentElement.parentElement.classList.remove('audio_instruction');
  }
  async recordedFileName() {
    const unique = (new Date()).getTime();const prefix = 'recorded-';
    // Math.floor(Math.random() * (999999999999 - 123 + 1) + 123);

    let extension = 'mp3';
    const mimeType = this.recordedBlob?.type??extension;
    const audioMimeTypeRegex = /^audio\//;
    const videoMimeTypeRegex = /^video\//;
    if (audioMimeTypeRegex.test(mimeType)) {
      extension = mimeType.split('/')[1];
      const suffix = (extension.trim() == '')?'.webm':'.' + extension;return prefix+unique+suffix;
    } else if (videoMimeTypeRegex.test(mimeType)) {
      extension = mimeType.split('/')[1];
      const suffix = (extension.trim() == '')?'.webm':'.' + extension;return prefix+unique+suffix;
    } else {
      extension = 'webm';
      this.recordedBlob = await new Blob([this.recordedBlob], { type: 'audio/webm' });
      const suffix = (extension.trim() == '')?'.webm':'.' + extension;return prefix+unique+suffix;
    }
    // const suffix = (extension.trim() == '')?'.webm':'.' + extension;return prefix+unique+suffix;
  }
  async uploadAudio(file, thisClass) {
    try {
      // Max file size
      if (!file) {
        throw new Error(thisClass.i18n?.file_not_found??'File not found');
      }
      // 
      var audioupload_instuction = thisClass.i18n?.audioupload_instuction??"You are permitted to record any message of your liking up to %d seconds, with the exclusion of profanity or copyrighted materials, which are prohibited. Please note your recording may be reviewed and screened (discreetly) by our DubiDo staff. We will not modify or edit your recording. In the event of copyright infringement, profanity, hate speech or recordings of the sort, we reserve the right to decline your recording and we will notify you of this decision within 48h of the submission of your recording. You will be given the opportunity to record a new message for additional review. For further information on your rights and privacy, please refer to our Privacy Policy. Please also refer to our Disclaimer for additional information on DubiDo’s liability with regard to recordings.";
      audioupload_instuction = audioupload_instuction.replace('%d', this.duration);
      this.audioInstructPreview.innerHTML = this.nl2br(audioupload_instuction);
      this.audioInstructPreview.classList.remove('d-none');
      this.rootElement.parentElement.parentElement.parentElement.classList.add('audio_instruction');
      // 
      // this.skipButton.classList.remove('do_recorder__skipped');
      this.switchActive('upload');
      // this.releaseButton.classList.remove('do_recorder__released');
      if (file.size > (1024 * 1024 * 20)) {
        /**
         * Remove prices on upload voice
         */
        this.recordedBlob = null;
        this.do_store('sizeExceed', false, thisClass);
        throw new Error(wp.i18n.sprintf(thisClass.i18n?.maxuploadmb??'Oh! The file you are trying to upload is too heavy. Put ♥ - the file must be up to %sMb', 20));
      }
      /**
       * Check if file is invalid or something error happens.
       */
      if (!file?.type) {
        throw new Error(thisClass.i18n?.invalid_file??'Invalid file');
      }
      /**
       * Make some preload before sleep operations.
       */
      this.waveAudioPreview.classList.add('loading-file');
      // 
      if (! file.type.startsWith('video/') && ! file.type.startsWith('audio/')) {
        // this.audioExtensions
        // throw new Error(thisClass.i18n?.invalid_file??'File is not audio, nor video.');
        throw new Error(thisClass.i18n?.invalid_file??'File is not audio, nor video.');
      }
      var loadingTimeouts = setTimeout(() => {
        this.waveAudioPreview.classList.remove('loading-file');
        this.showError({text: thisClass.i18n?.maybe_file_invalid??'Something went wrong or maybe file you uploaded is invaild.', duration: 45000, close: true, gravity: "top", position: "right", stopOnFocus: true, style: {background: 'linear-gradient(to right, rgb(255 180 117), rgb(251, 122, 72))'}});
      }, 20000);
      this.recordedBlob = file;this.createWave();
      const fileURL = URL.createObjectURL(file);
      this.audioPreview.src = fileURL;
      this.wave.load(fileURL).then(res=> {
        URL.revokeObjectURL(fileURL);clearTimeout(loadingTimeouts);
        this.rootElement.classList.add('visible_audio');
        var second = this?.waveAudioPreview.querySelector('[data-timer-type="s"]');
        this.waveAudioPreview.classList.remove('loading-file');
        /**
         * Reset Input value.
         */
        this.uploadInput.value = '';
        if (second) {
          this.playButtonAction('show', thisClass);
          second.innerHTML = (this.wave?.duration??0.00)?.toFixed(2).replace('.', ':')??'0:00';
        }
        if ((this.wave?.duration??0) > this.duration) {
          this.playButtonAction('hide', thisClass);this.playRecording('stop', thisClass);
          this.wave.stop();this.recordedBlob = null;this.wave.destroy();
          this.rootElement.classList.remove('visible_audio');
          /**
           * Remove prices on upload voice
           */
          // thisClass.removeAdditionalPrice(this.meta_tag, parseFloat(this.recordButton.dataset.cost), false, this.product_id);
          this.do_store('durExceed', false, thisClass);
          throw new Error(wp.i18n.sprintf(thisClass.i18n?.audioexcedduration??'Office! The file I uploaded is too long. Note ♥ The length of the recording does not exceed %s seconds.', this.duration));
        }
        /**
         * Add prices on upload voice
         */
        this.do_store('upload', file, thisClass);
        this.hideError();
      }).catch(err=> {
        this.showError({text: err?.message??'', duration: 45000, close: true, gravity: "top", position: "right", stopOnFocus: true, style: {background: 'linear-gradient(to right, rgb(255 180 117), rgb(251, 122, 72))'}});
        this.waveAudioPreview.classList.remove('loading-file');
      });
    } catch (err) {
      console.log('Hi there😒😒')
      var errorText = err?.message??'';// console.error(err);// err.message is text print
      if (errorText.trim() == '') {
        errorText = thisClass.i18n?.audiofile_invalid??"Invalid file selected. It seems you didn't select a valid audio file or file is not in these following format (%s).";
        errorText = wp.i18n.sprintf(errorText, this.audioExtensions.join(', '));
      }
      this.showError({text: errorText, duration: 45000, close: true, gravity: "top", position: "right", stopOnFocus: true, style: {background: 'linear-gradient(to right, rgb(255 180 117), rgb(251, 122, 72))'}});
      this.waveAudioPreview.classList.remove('loading-file');
    }
  }
  playButtonAction(type, thisClass) {
    switch(type) {
      case 'hide':
        this.playButton.style.display = 'none';
        break;
      case 'show':
        this.playButton.style.display = 'flex';
        break;
      default:
        break;
    }
  }
  nl2br(str) {
    if (typeof str === 'undefined' || str === null) {return '';}
    return (str + '').replaceAll(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2').replaceAll('\\n', '<br>').replaceAll('\n', '<br>').replaceAll('\ n', '<br>');
  }
  switchActive(key = false) {
    const buttons = {
      later: this.releaseButton,
      skip: this.skipButton,
      upload: this.uploadButton,
      record: this.recordButton,
      play: this.playButton
    };
    if (key && buttons[key]) {
      Object.values(buttons).forEach(button => button.classList.remove('active'));
      buttons[key].classList.add('active');
      return true;
    }
    return false;
  }
  hideError(args = false) {
    var target = (true)?Swal.getHtmlContainer().children[0]:this.row.tabElement;
    [...target.children].filter(element => element.classList.contains('error')).forEach(element => element.remove());
  }
  showError(args) {
    this.hideError(args);this.do_store('skip', false, thisClass);
    var config = (typeof fwpSiteConfig === 'object')?fwpSiteConfig:{};
    var target = (true)?Swal.getHtmlContainer().children[0]:this.row.tabElement;
    // thisClass.toastify(args).showToast();
    var error = document.createElement('div');
    error.classList.add('error', 'error-voice');
    var backDrop = document.createElement('div');
    backDrop.classList.add('error-backdrop');
    error.removeEase = () => {
      error.style.transition = 'all 2.3s ease';
      error.style.opacity = '0';
      // error.style.transform = 'translateY(100px)';
      setTimeout(() => {error.remove();backDrop.remove();}, 300);
    }
    backDrop.addEventListener('click', (event) => {
      event.preventDefault();event.stopPropagation();error.removeEase();
    });
    error.appendChild(backDrop);
    var errorCont = document.createElement('div');
    errorCont.classList.add('error-container');
    // errorCont.style.background = `#fff url(${config?.buildPath??''}/src/img/reactangle-bg.png) center/100% 100% no-repeat`;
    var errorWrap = document.createElement('div');
    errorWrap.classList.add('error-wrap');
    var imgWrap = document.createElement('div');
    imgWrap.classList.add('error-imgwrap');
    var image = document.createElement('img');
    image.classList.add('error-image');
    image.src = `${config?.buildPath??''}/src/img/stickers24jpg-06.png`;
    imgWrap.appendChild(image);errorWrap.appendChild(imgWrap);
    var txtWrap = document.createElement('div');
    txtWrap.classList.add('error-txtwrap');
    var errorText = document.createElement('span');
    errorText.classList.add('error-title');
    errorText.innerHTML = args.text;
    txtWrap.appendChild(errorText);
    // if (args?.close) {
    //   var errorClose = document.createElement('span');
    //   errorClose.classList.add('error-close');
    //   errorClose.innerHTML = 'X';
    //   errorClose.addEventListener('click', (event) => {
    //     event.preventDefault();event.stopPropagation();error.removeEase();
    //   });
    //   errorWrap.appendChild(errorClose);
    // }
    if (args?.duration) {setTimeout(() => error.removeEase(), args.duration);}
    var gotIt = document.createElement('button');gotIt.type = 'button';
    gotIt.classList.add('error-gotit');gotIt.innerHTML = thisClass.i18n?.gotit??'Got it';
    gotIt.addEventListener('click', (event) => {
      event.preventDefault();event.stopPropagation();error.removeEase();
    });
    txtWrap.appendChild(gotIt);
    errorWrap.appendChild(txtWrap);
    errorCont.appendChild(errorWrap);error.appendChild(errorCont);
    target.insertBefore(error, target.children[0]);
    // 
    // console.log(this, args);
    // 
  }
}

export default Voice;