/**
 * LayeredCanvas attach and create customized image layers accordingly
 * 
 * @param {HTMLElement} [canvas] The canvas element if exists.
 * 
 * @return {LayeredCanvas} The instance.
 */

class LayeredCanvas {
    constructor(canvas = false) {
        if (typeof canvas === 'string') {
            this.canvas = canvas = document.querySelector(canvas);
        }
        if (!this.canvas) {
            this.canvas = canvas = document.createElement('canvas');
            // canvas.style.height = canvas.style.width = '300px';
            canvas.height = canvas.width = 300;
        }
        this.canvas = canvas;
        this.context = this.canvas.getContext('2d');
        this.layers = [];
    }
  
    /**
     * Adds a new layer to the canvas using the specified image source.
     * @param {string} src - The URL or path of the image to be added as a layer.
     * @param {number} [z=1] - The order (z-index) of the layer. If not provided, defaults to the order of the canvas.
     * @param {number} [x=0] - The x-coordinate of the top-left corner of the layer on the canvas. Defaults to 0.
     * @param {number} [y=0] - The y-coordinate of the top-left corner of the layer on the canvas. Defaults to 0.
     * @param {number} [w=false] - The width of the layer. If not provided, defaults to the width of the canvas.
     * @param {number} [h=false] - The height of the layer. If not provided, defaults to the height of the canvas.
     * @returns {Promise} A Promise that resolves when the layer is successfully added, and rejects if the image fails to load.
     */
    addLayer(src, z = 1, x = 0, y = 0, w = false, h = false) {
        return new Promise((resolve, reject) => {
            if (!w) {w = this.canvas.width;}
            if (!h) {h = this.canvas.height;}
            const img = new Image();
            img.src = src;
            img.onload = () => {
                this.layers.push({ img, x, y, w, h, z });
                this.drawLayers();
                resolve(img);
            };
            img.onerror = () => {
                reject(new Error(`Failed to load image: ${img.src}`));
            };
        });
    }
  
    /**
     * Draws all the layers on the canvas in the order they were added.
     * Each layer is drawn using the drawImage method of the canvas context.
     * @returns {void} This function does not return any value.
     */
    drawLayers() {
        // Clear the canvas
        this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
        // Draw each layer in order
        this.layers.sort((a, b) => a.z - b.z).forEach(layer => {
            this.context.drawImage(layer.img, layer.x, layer.y, layer.w, layer.h);
        });
    }

    /**
     * Exports the canvas as an image in the specified format (png, jpg, or svg).
     * If the format is png or jpg, the canvas is converted to a data URL of the specified format.
     * If the format is svg, the canvas is embedded in an SVG foreignObject and returned as a data URL.
     * If the format is unsupported, an error is thrown.
     * If the download parameter is true, the image is downloaded as a file.
     * Otherwise, the data URL is returned.
     * @param {string} [type='png'] - The format of the exported image.
     * @param {boolean} [download=false] - Whether to download the image as a file.
     * @returns {string|undefined} The data URL of the exported image, or undefined if download is true.
     */
    export(type = 'png', download = false) {
        let dataUrl = false;let canvasBlob = false;
        return new Promise((resolve, reject) => {
            if (type === 'png' || type === 'jpg') {
                const mimeType = type === 'png' ? 'image/png' : 'image/jpeg';
                if (download) {dataUrl = this.canvas.toDataURL(mimeType);}
            } else if (type === 'svg') {
                // SVG export is more complex since canvas doesn't directly support it
                const svgData = `
                <svg xmlns="http://www.w3.org/2000/svg" width="${this.canvas.width}" height="${this.canvas.height}">
                    <foreignObject width="100%" height="100%">
                    <canvas xmlns="http://www.w3.org/1999/xhtml" width="${this.canvas.width}" height="${this.canvas.height}">
                        ${this.canvas.outerHTML}
                    </canvas>
                    </foreignObject>
                </svg>`;
                if (download) {dataUrl = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgData);}
                canvasBlob = new Blob([svgData], {type: 'image/svg+xml'});
            } else {
                // throw new Error('Unsupported export format');
                reject('Unsupported export format');
            }
            if (download) {
                const link = document.createElement('a');
                link.href = dataUrl;
                link.download = `canvas.${type}`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } else {
                if (type != 'svg') {
                    this.canvas.toBlob(blob => {
                        blob.name = `${Date.now()}-canvas.${type}`
                        resolve(blob);
                    });
                } else {
                    resolve(canvasBlob);
                }
            }
        });
    }
}

export default LayeredCanvas;

// Usage example
// const layeredImage = new LayeredCanvas(canvas);
// const layered = new LayeredCanvas();
// // thisClass.lastJson.product.custom_fields.standing[2].groups[0].options.map(option => option.imageUrl).filter(imageUrl => imageUrl).forEach(imageUrl => layeredImage.addLayer(imageUrl, 0, 0));
// const images = [
//     'https://dubido.local/wp-content/uploads/2023/07/2-1.png',
//     'https://dubido.local/wp-content/uploads/2023/07/20064_201121113240.png',
//     'https://dubido.local/wp-content/uploads/2023/11/Con-blue-standing.png'
// ];
// // images.forEach(imageUrl => layered.addLayer(imageUrl, 0, 0));
// // 
// // layered.addLayer(images[0], 0, 0, 100, 100)
// // 
// console.log(layered)
// // 
// const target = document.querySelector('.dynamic_popup');
// target.innerHTML = '';target.appendChild(canvas);