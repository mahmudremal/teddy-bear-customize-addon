const PreviewCanvas = ({ images, baseImage, setCanvasBlob, activeTab }) => {
  // console.log('PreviewCanvas', images, baseImage);
  const { useRef, useEffect } = React;
  const canvasRef = useRef(null);
  const imageInstancesRef = useRef(new Map()); // Keep track of loaded image instances
  const baseImageInstanceRef = useRef(null); // Keep base image instance

  const loadImage = (src) => {
    // Check if we already have this image loaded
    if (imageInstancesRef.current.has(src)) {
      return Promise.resolve(imageInstancesRef.current.get(src));
    }

    return new Promise((resolve, reject) => {
      const img = new Image();
      img.onload = () => {
        imageInstancesRef.current.set(src, img); // Store the loaded image
        resolve(img);
      };
      img.onerror = reject;
      img.src = src;
    });
  };

  const loadBaseImage = async () => {
    if (!baseImageInstanceRef.current && baseImage) {
      baseImageInstanceRef.current = await loadImage(baseImage);
    }
    return baseImageInstanceRef.current;
  };

  const drawImagesOnCanvas = async (images) => {
    const canvas = canvasRef.current;
    const context = canvas.getContext("2d");

    // Set canvas dimensions to 2x for high resolution
    const displayWidth = canvas.clientWidth;
    const displayHeight = canvas.clientHeight;
    
    // Set actual canvas dimensions to 2x for retina/high DPI displays
    canvas.width = displayWidth * 2;
    canvas.height = displayHeight * 2;

    // Scale context to ensure proper resolution
    context.scale(2, 2);

    context.clearRect(0, 0, displayWidth, displayHeight);

    try {
      // First draw base image
      const baseImg = await loadBaseImage();
      if (baseImg) {
        context.drawImage(baseImg, 0, 0, displayWidth, displayHeight);
      }

      // Then draw other images
      if (images && images.length > 0) {
        for (const imageSrc of images) {
          const img = await loadImage(imageSrc);
          context.drawImage(img, 0, 0, displayWidth, displayHeight);
        }
      }

      // Convert canvas to blob with high quality
      canvas.toBlob((blob) => {
        setCanvasBlob(blob);
      }, 'image/png', 1.0); // Use PNG format with max quality

    } catch (error) {
      console.error("Error loading images:", error);
    }
  };

  useEffect(() => {
    drawImagesOnCanvas(images);
  }, [images, baseImage]);

  return (
    <div className={`tb_relative md:tb_aspect-square ${activeTab === null ? 'tb_aspect-square' : ''}`}>
      {/* onContextMenu={(e) => e.preventDefault()} */}
      <div className="tb_absolute tb_inset-0 tb_z-1" />
      <canvas 
        ref={canvasRef}
        className="tb_w-full tb_h-full tb_m-auto tb_rounded-md"
        style={{width: '100%', height: '100%'}} // Ensure canvas scales properly
      />
    </div>
  );
};

export default PreviewCanvas;
