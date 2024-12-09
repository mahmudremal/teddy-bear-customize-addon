const PreviewCanvas = ({ images, baseImage, setCanvasBlob, activeTab }) => {
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

    context.clearRect(0, 0, canvas.width, canvas.height);

    try {
      // First draw base image
      const baseImg = await loadBaseImage();
      if (baseImg) {
        context.drawImage(baseImg, 0, 0, canvas.width, canvas.height);
      }

      // Then draw other images
      if (images && images.length > 0) {
        for (const imageSrc of images) {
          const img = await loadImage(imageSrc);
          context.drawImage(img, 0, 0, canvas.width, canvas.height);
        }
      }

      // Convert canvas to blob and set screenshot
      canvas.toBlob((blob) => {
        setCanvasBlob(blob);
      });

    } catch (error) {
      console.error("Error loading images:", error);
    }
  };

  useEffect(() => {
    drawImagesOnCanvas(images);
  }, [images, baseImage]);

  return (
    <div className={`tb_relative md:tb_aspect-square ${activeTab === null ? 'tb_aspect-square' : ''}`}>
      <div className="tb_absolute tb_inset-0 tb_z-1" onContextMenu={(e) => e.preventDefault()} />
      <canvas ref={canvasRef} className="tb_w-full tb_h-full tb_m-auto tb_rounded-md" />
    </div>
  );
};

export default PreviewCanvas;
