class Tidio_Chat {
  constructor() {
    this.setup_hooks();
  }
  setup_hooks() {
    this.hide_brand_logo();
  }
  /**
   * Hide Tidio Brand Logo.
   */
  hide_brand_logo() {
    var theInterval = setInterval(() => {
      const iframe = document.querySelector('#tidio-chat #tidio-chat-iframe');
      if(iframe) {
        const iWindow = iframe.contentWindow;
        const iDocument = iWindow.document;
        iDocument.querySelectorAll('.footer-bottom .tidio-5hhiig').forEach((el) => el.remove());
        clearInterval(theInterval);
      }
    }, 1000);
  }
}
export default Tidio_Chat;