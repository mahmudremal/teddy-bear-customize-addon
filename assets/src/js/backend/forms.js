class FWProject_Forms {
  constructor(thisClass) {
    this.setup_hooks();
  }
  setup_hooks() {
    this.impliment_conditions();
    this.impliment_wp_media_buttons();
  }
  impliment_wp_media_buttons() {
    document.querySelectorAll('button[data-image-select]').forEach((el, ei) => {
      el.dataset.handled = true;
      el.dataset.innertext = el.innerText;
      el.addEventListener('click', (event) => {
        event.preventDefault();
        if(typeof wp.media !== 'undefined') {
          var mediaUploader = wp.media({
              title: 'Select or Upload Media',
              button: {text: 'Use this Media'},
              multiple: false
          });
          mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            var url = attachment.url;el.value = url; // attachment.filename;
            el.innerHTML = el.dataset.innertext+' ('+attachment.filename+')';

            var img, node, cross, label, hidden;
            label = el.nextElementSibling;
            if(label?.querySelector('.imgpreview')) {
              label.querySelector('.imgpreview').remove();
            }
            cross = document.createElement('div');cross.title = 'Remove';
            cross.classList.add('dashicons-before', 'dashicons-dismiss');
            cross.addEventListener('click', (event) => {
                event.preventDefault();cross.parentElement.remove();
                el.innerHTML = el.dataset.innertext;
            });
            node = document.createElement('div');node.classList.add('imgpreview');
            img = document.createElement('img');img.src = url;img.alt = attachment.id;
            hidden = document.createElement('input');hidden.value = attachment.id;
            hidden.type = 'hidden';hidden.name = el.dataset?.name;
            node.appendChild(hidden);node.appendChild(img);node.appendChild(cross);
            // el.parentElement.appendChild(node);
            label.appendChild(node);
          });
          mediaUploader.open();
        } else {
          thisClass.toastify({text: "WordPress media library not initialized.",className: "info",style: {background: "linear-gradient(to right, #00b09b, #96c93d)"}}).showToast();
        }
      });
    });
    document.querySelectorAll('.dashicons-before.dashicons-dismiss').forEach((cross) => {
      cross.addEventListener('click', (event) => {
        event.preventDefault();cross.parentElement.remove();
      });
    });
  }
  impliment_conditions() {
    document.querySelectorAll('.fwp-form__field[data-condition]:not([data-condition="[]"])').forEach((el) => {
      const condition = JSON.parse(el.dataset.condition);
      condition.forEach((row) => {
        var field = document.querySelector('#' + row.field);
        if(field && field?.type) {
          switch (field.type) {
            case 'checkbox':
              if(field.checked) {
                el.style.display = 'block';
              }
              field.addEventListener('change', (event) => {
                if(field.checked) {
                  el.style.display = 'block';
                } else {
                  el.style.display = 'none';
                }
              });
              break;
            default:
              break;
          }
        }
      });
    });
  }
}

export default FWProject_Forms;