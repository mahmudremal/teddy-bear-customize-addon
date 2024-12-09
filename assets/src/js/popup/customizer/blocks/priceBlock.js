export default function PriceBlock({ price_html = '', discountTotal = 0, inTotal = 0 }) {
  const { useState, useEffect } = React;
  const [originalPrice, setOriginalPrice] = useState(0);
  const [currentPrice, setCurrentPrice] = useState(0);
  const [isBlinking, setIsBlinking] = useState(false);

  useEffect(() => {
    // Parse price from price_html
    const parser = new DOMParser();
    const doc = parser.parseFromString(price_html, 'text/html');
    
    // Get original price (from del tag if exists, otherwise from first price)
    const delPrice = doc.querySelector('del .woocommerce-Price-amount bdi');
    const firstPrice = doc.querySelector('.woocommerce-Price-amount bdi');
    const originalPriceStr = delPrice ? delPrice.textContent : firstPrice?.textContent;
    
    // Get current price (from ins tag if exists, otherwise same as original)
    const insPrice = doc.querySelector('ins .woocommerce-Price-amount bdi');
    const currentPriceStr = insPrice ? insPrice.textContent : originalPriceStr;

    // Convert price strings to numbers, removing currency symbol
    setOriginalPrice(parseFloat(originalPriceStr?.replace(/[^0-9.]/g, '') || 0));
    setCurrentPrice(parseFloat(currentPriceStr?.replace(/[^0-9.]/g, '') || 0));
  }, [price_html]);

  useEffect(() => {
    // Trigger blink animation when price changes
    setIsBlinking(true);
    const timer = setTimeout(() => setIsBlinking(false), 500);
    return () => clearTimeout(timer);
  }, [currentPrice, inTotal, discountTotal]);

  const finalPrice = currentPrice + inTotal - discountTotal;

  return (
    <div className="tb_flex tb_flex-col tb_gap-2">
      <div className={`price_amount ${isBlinking ? 'tb_animate-blink' : ''}`}>
        <style>
          {`
            @keyframes blink {
              0% { opacity: 1; }
              50% { opacity: 0.3; }
              100% { opacity: 1; }
            }
            .tb_animate-blink {
              animation: blink 0.5s ease-in-out;
            }
          `}
        </style>
        {originalPrice > finalPrice && (
          <>
            <del className="td_hidden" aria-hidden="true">
              <span className="woocommerce-Price-amount amount">
                <bdi>
                  <span className="woocommerce-Price-currencySymbol">$</span>
                  {originalPrice.toFixed(2)}
                </bdi>
              </span>
            </del>
            <span className="screen-reader-text">Original price was: ${originalPrice.toFixed(2)}.</span>
          </>
        )}
        <ins aria-hidden="true">
          <span className="woocommerce-Price-amount amount">
            <bdi>
              <span className="woocommerce-Price-currencySymbol">$</span>
              {finalPrice.toFixed(2)}
            </bdi>
          </span>
        </ins>
        <span className="screen-reader-text">Current price is: ${finalPrice.toFixed(2)}.</span>
      </div>
    </div>
  );
}