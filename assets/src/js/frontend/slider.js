export const keenSliderNavigation = (slider) => {
    let wrapper, dots, arrowLeft, arrowRight;
    function markup(remove) {
      wrapperMarkup(remove)
      dotMarkup(remove)
      arrowMarkup(remove)
    }
    function removeElement(elment) {
      elment.parentNode.removeChild(elment)
    }
    function createDiv(className) {
      var div = document.createElement("div")
      var classNames = className.split(" ")
      classNames.forEach((name) => div.classList.add(name))
      return div
    }
    function arrowMarkup(remove) {
      if (remove) {
        removeElement(arrowLeft)
        removeElement(arrowRight)
        return
      }
      arrowLeft = createDiv("keen-slider__arrow keen-slider__arrow--left")
      arrowLeft.addEventListener("click", () => slider.prev())
      arrowRight = createDiv("keen-slider__arrow keen-slider__arrow--right")
      arrowRight.addEventListener("click", () => slider.next())
  
      wrapper.appendChild(arrowLeft)
      wrapper.appendChild(arrowRight)
    }
    function wrapperMarkup(remove) {
      if (remove) {
        var parent = wrapper.parentNode
        while (wrapper.firstChild)
          parent.insertBefore(wrapper.firstChild, wrapper)
        removeElement(wrapper)
        return
      }
      wrapper = createDiv("navigation-wrapper")
      slider.container.parentNode.appendChild(wrapper)
      wrapper.appendChild(slider.container)
    }
    function dotMarkup(remove) {
      if (remove) {
        removeElement(dots)
        return
      }
      dots = createDiv("keen-slider__dots")
      slider.track.details.slides.forEach((_e, idx) => {
        var dot = createDiv("keen-slider__dots--single")
        dot.addEventListener("click", () => slider.moveToIdx(idx))
        dots.appendChild(dot)
      })
      wrapper.appendChild(dots)
    }
    function updateClasses() {
      var slide = slider.track.details.rel
      slide === 0
        ? arrowLeft.classList.add("keen-slider__arrow--left--disabled")
        : arrowLeft.classList.remove("keen-slider__arrow--left--disabled")
      slide === slider.track.details.slides.length - 1
        ? arrowRight.classList.add("keen-slider__arrow--right--disabled")
        : arrowRight.classList.remove("keen-slider__arrow--right--disabled")
      Array.from(dots.children).forEach(function (dot, idx) {
        idx === slide
          ? dot.classList.add("keen-slider__dots--single--active")
          : dot.classList.remove("keen-slider__dots--single--active")
      })
    }
    slider.on("created", () => {
      markup()
      updateClasses()
    })
    slider.on("optionsChanged", () => {
      console.log(2)
      markup(true)
      markup()
      updateClasses()
    })
    slider.on("slideChanged", () => {
      updateClasses()
    })
    slider.on("destroyed", () => {
      markup(true)
    })
}