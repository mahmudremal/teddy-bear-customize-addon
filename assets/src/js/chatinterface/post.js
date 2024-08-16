import Hooks from "./hooks";

class Post extends Hooks {
    constructor() {
        super();
        this.eventElements = [];
    }
    /**
     * @param {FormData} data arguments that should be transsferred to server.
     * 
     * @returns json || Error
     */
    sendToServer(data, args = {}) {
      const PostClass = this;const $ = jQuery;
      args = Object.assign({}, {
        url: '',
        data: data,
        cache: false,
        type: "POST",
        dataType: 'json',
        contentType: false,
        processData: false,
      }, {...args});
      // PostClass.eventElements = [];
      return new Promise((resolve, reject) => {
        // console.log('Before');
        if (args.eventStream && !!window.EventSource) {
          var source = new EventSource(args.url);
  
          source.addEventListener('message', (event) => {
            // console.log(event, event.data);
            PostClass.do('event-message', event);
            if (event.data) {
              try {
                event.json = JSON.parse(event.data.trim());
                if (event.json?.hook && event.json.hook.includes('close_event')) {
                  PostClass.do('event-finish', event);
                  source.close();resolve(event.json);
                }
                if (event.json?.progress) {
                  event.percentComplete = event.json.progress;
                  PostClass.do('event-progress', event);
                }
              } catch (error) {
                console.error(error);
              }
            }
          }, false);
            
          source.addEventListener('open', event => PostClass.do('event-open', event), false);
          source.addEventListener('join', event => PostClass.do('event-join', event));
          source.addEventListener('leave', event => PostClass.do('event-leave', event));
          source.addEventListener("notice", event => PostClass.do('event-notice', event));
          source.addEventListener("update", event => PostClass.do('event-update', event));
  
          source.addEventListener('error', (event) => {
            PostClass.do('event-error', event);
            if (event.readyState == EventSource.CLOSED) {
              // console.log('Connection was closed.', event);
              source.close();reject(event);
            } else if (event.readyState == EventSource.CONNECTING) {
              // console.log('Reconnecting...', event);
            } else {
            }
          }, false);
        } else {
          // Result to xhr polling :(
            // https://api.jquery.com/jQuery.ajax/
          $.ajax({
            xhr: function() {
              // console.log('xhr');
              var xhr = $.ajaxSettings.xhr();
              // Upload progress
              xhr.upload.onprogress = (event) => {
                // console.log('upload.onprogress', event);
                if (event.lengthComputable) {event.percentComplete = (event.loaded / event.total) * 100;}
                PostClass.do('upload-progress', event);
              };
              // Download progress
              xhr.onprogress = (event) => {
                // console.log('onprogress', event);
                if (event.lengthComputable) {event.percentComplete = (event.loaded / event.total) * 100;}
                PostClass.do('progress', event);
              };
              return xhr;
            },
            success: (json, status, jqXHR) => {
              // console.log('Success', json);
              // Handle success logic (toasts, hooks, etc.)
            //   const message = PostClass.extractMessage(json);
              PostClass.do('success', {json: json, status: status, jqXHR: jqXHR});
              resolve(json); // Resolve with the parsed data
            },
            error: (jqXHR, status, err) => {
              console.error('Error', err);
              const errorText = PostClass.formatErrorText(err); // Refactored for clarity
              PostClass.do('error', err);
              reject(err); // Reject with the error
            },
            beforeSend: (xhr) => {
              // Effective for event-stream.
              // xhr.setRequestHeader("Range", "bytes=" + lastPosition + "-");
              // console.log('before send');
              // Show the progress bar.
              PostClass.do('beforesend', {});
            },
            complete: () => {
              // console.log('complete');
              // Hide the progress bar.
              PostClass.do('complete', {});
            },
            xhrFields: {
              // Getting on progress streaming response
              onprogress: (event) => {
                // console.log('onprogress', event);
                if (event.lengthComputable) {event.percentComplete = (event.loaded / event.total) * 100;}
                PostClass.do('progress', event);
              }
            },
            ...args
          }).done(data => {
            PostClass.do('done', data);
            PostClass.do('success', data);
            resolve(data);
          }).fail(error => {
            PostClass.do('fail', error);
            PostClass.do('error', error);
            reject(error);
          }).always((data = {}) => {
            PostClass.do('done', data);
            PostClass.do('success', data);
          });
        }
        
      });
    }
    extractMessage(json) {
      return ((json?.data??false)&&typeof json.data==='string')?json.data:(
        (typeof json.data?.message==='string')?json.data.message:false
      );
    }
    formatErrorText(err) {
      if (err.responseText && err.responseText !== '') {
        return err.responseText; // Prioritize response text if available
      }
      return 'Something went wrong!';
    }
    on(hooks, element, callback) {
      if (element === false) {
        element = document.createElement('div');
      }
      if (typeof element === "string") {
        element = document.querySelector(element);
      }
      if (!element) {
        console.warn(`Element not found for hook "${hook}"`);
        return;
      }
      if (typeof hooks !== 'object') {
        hooks = [hooks];
      }
      hooks.forEach(hook => {
        this.eventElements.push({hook: hook, element: element, callback: callback});
        element.addEventListener(hook, callback);
      });
      
    }
    off(hook, element, callback) {
      const index = this.eventElements.findIndex(
        (row) => row.hook === hook && row.element === element
      );
      if (index !== -1) {
        this.eventElements.splice(index, 1); // Remove the matching row
      } else {
        console.warn('No hook found with hook "' + hook + '" and element', element);
      }
    }
    do(hook, event = {}) {
      // if (!(event instanceof Object)) {event = {...event};}
      this.eventElements.filter(row => row.hook == hook).forEach(row => row.element.dispatchEvent(new CustomEvent(hook, {
        detail: {
          event: event,
        },
      })));
    }
    event(event) {
      return (event?.detail && event.detail?.event)?event.detail.event:event;
    }
  
    example() {
      // this.sendToServer(formdata)
      // .then((response) => {console.log("Success:", response);})
      // .catch((err) => {console.error("Error:", err);});

        // var formdata = new FormData();
        // formdata.append('action', 'sospopsproject/ajax/import/clean');
        // formdata.append('taxonomy', button.dataset.taxonomy);
        // formdata.append('clean', button.dataset.target);
        // formdata.append('_nonce'.ajaxNonce);
        // this.sendToServer(formdata, {
        //     // eventStream: true, url: ajaxUrl+`?action=sospopsproject/ajax/import/clean&clean=${button.dataset.target}&taxonomy=${button.dataset.taxonomy}`
        // });
    }
    
}

export default Post;