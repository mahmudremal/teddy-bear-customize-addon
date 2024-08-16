/**
 * SliderJS class made by Remal Mahmud
 * 
 * Copyright (c) 2024 - 2025 (https://github.com/mahmudremal/)
 * Author: @mahmudremal
 * @license MIT License
 */
import icons from "../frontend/icons";

const CONFIG = {winWidth: window.innerWidth, mobileWidth: 450, visibleWidth: 300};

/**
 * The Slider class is responsible for initializing and managing an image slider.
 * It takes an optional configuration object as a parameter, which can be used to customize the behavior and appearance of the slider.
 * The default configuration values are as follows:
 */
class Slider {
    /**
     * The constructor function is responsible for initializing the image slider.
     * It takes an optional configuration object as a parameter, which can be used to customize the behavior and appearance of the slider.
     * The default configuration values are as follows:
     *
     * @param {Object} [config] - an optional configuration object that can be used to customize the behavior and appearance of the slider
     * @returns {void} This function does not return any value. Its purpose is to initialize the image slider.
     */
    constructor(config = {}) {
        this.config = Object.assign({
            transition: 0.3,
            container: false,
            autoplay: false,
            arrows: false,
            slides: false,
            loop: false,
            dots: false,
            drag: false,
            perSlide: 1,
            perview: 1,
            speed: 300,
            space: 5,
            view: 2,
        }, config);
        // 
        this.init_objects();
        this.init_elements();
        this.init_resize();
        this.transition();
        this.init_drag__();
        this.autoplay();
        this.issues();
        this.update();
    }

    /**
     * The `init_objects` function is responsible for initializing the elements of the image slider.
     * It sets up the container and slides based on the provided configuration.
     *
     * @returns {void} This function does not return any value. Its purpose is to initialize the image slider's container and slides.
     */
    init_objects() {
        if (!this.config.container) {
            this.config.container = document.createElement('div');
        }
        this.config.container.classList.add('slider');
        // 
        this.config.wrap = document.createElement('div');
        this.config.wrap.classList.add('slider__wrapper');
        // If slides are not provided in the configuration, it sets the slides to the container's children.
        if (this.config.slides === false) {
            this.config.slides = this.config.container.querySelector('.slider__slides');
        }
        // If no slides are provided, it creates a new slides container.
        if (!this.config.slides) {
            this.config.slides = document.createElement('div');
            this.config.slides.classList.add('slider__slides');
            [...this.config.container.children].forEach(slide => this.config.slides.appendChild(slide));
            this.config.container.appendChild(this.config.slides);
        }
        this.config.wrap.appendChild(this.config.slides);
        this.config.container.appendChild(this.config.wrap);

        // If arrows are not provided as an object, it sets the arrows to default false values.
        if (typeof this.config.arrows !== 'object') {
            this.config.arrows = {prev: false, next: false};
        }

        this.totalSlides = this.config.slides.children.length;
        this.currentIndex = 0;
    }

    /**
     * The init_elements function is responsible for initializing the elements of the image slider.
     * It sets up the arrow buttons for navigating through the slides and adds event listeners to them.
     * If the arrow buttons are not provided in the configuration, it creates default arrow buttons.
     *
     * @returns {void} This function does not return any value. Its purpose is to initialize the elements of the image slider.
     */
    init_elements() {
        var next = false;var prev = false, arrows = this.config.container;
        // 
        arrows.classList.add('slider__arrows');
        // 
        if (this.config.arrows?.prev) {
            if (this.config.arrows.prev?.nodeType) {}
            if (typeof this.config.arrows.prev == 'string') {
                this.config.arrows.prev = document.querySelector(this.config.arrows.prev);
            }
        }
        if (!(this.config.arrows?.prev)) {
            prev = document.createElement('div');prev.innerHTML = icons.left;
            prev.classList.add('slider__arrows-prev');this.config.arrows.prev = prev;
        }
        this.config.arrows.prev.addEventListener('click', (event) => {
            event.preventDefault();event.stopPropagation();this.prev();
        });

        if (this.config.arrows?.next) {
            if (this.config.arrows.next?.nodeType) {}
            if (typeof this.config.arrows.next == 'string') {
                this.config.arrows.next = document.querySelector(this.config.arrows.next);
            }
        }
        if (!(this.config.arrows?.next)) {
            next = document.createElement('div');next.innerHTML = icons.right;
            next.classList.add('slider__arrows-next');this.config.arrows.next = next;
        }
        this.config.arrows.next.addEventListener('click', (event) => {
            event.preventDefault();event.stopPropagation();this.next();
        });
        if (next || prev) {
            var arrowOrder = this.isRTL()?[prev, next]:[next, prev];
            if (next?.nodeType && prev?.nodeType) {
                // arrowOrder.forEach(arrow => arrows.appendChild(arrow));
                this.config.container.insertBefore(arrowOrder[0], this.config.wrap);
                this.config.container.appendChild(arrowOrder[1]);
            } else {
                arrowOrder.forEach((arrow, index) => {
                    if (arrow?.nodeType) {
                        if (index == 0) {
                            this.config.container.insertBefore(arrowOrder[0], this.config.wrap);
                        } else {
                            this.config.container.appendChild(arrowOrder[1]);
                        }
                    }
                });
            }
        }
    }

    /**
     * The next function is responsible for navigating to the next slide in the image slider.
     * It updates the currentIndex by incrementing it by the value of perSlide.
     * If the currentIndex is greater than or equal to the total number of slides, it is set to the total number of slides minus the view plus one.
     * If the loop feature is enabled and the currentIndex is greater than or equal to the specified threshold, the transition function is called with the stop parameter set to true, effectively stopping any ongoing transition.
     * After updating the layout of the image slider, the update function is called to reflect the changes.
     *
     * @returns {void} This function does not return any value. Its purpose is to navigate to the next slide in the image slider.
     */
    next() {
        this.currentIndex += this.config.perSlide;
        if (this.config.loop && this.currentIndex >= this.totalSlides - this.config.view + 1) {
            this.transition(true).then(() => {
                this.currentIndex = 0;
                this.update().then(() => this.transition());
            });
        } else if (this.currentIndex >= this.totalSlides - this.config.view + 1) {
            this.currentIndex = this.totalSlides - this.config.view;
            this.update();
        } else {
            this.update();
        }
    }

    /**
     * The prev function is responsible for navigating to the previous slide in the image slider.
     * It updates the currentIndex by decrementing it by the value of perSlide.
     * If the currentIndex is less than 0, it is set to 0.
     * If the loop feature is enabled and the currentIndex is less than the specified threshold, the transition function is called with the stop parameter set to true, effectively stopping any ongoing transition.
     * After updating the layout of the image slider, the update function is called to reflect the changes.
     *
     * @returns {void} This function does not return any value. Its purpose is to navigate to the previous slide in the image slider.
     */
    prev() {
        this.currentIndex -= this.config.perSlide;
        if (this.config.loop && this.currentIndex < 0) {
            this.transition(true).then(() => {
                this.currentIndex = this.totalSlides - this.config.view;
                this.update().then(() => this.transition());
            });
        } else if (this.currentIndex < 0) {
            this.currentIndex = 0;
            this.update();
        } else {
            this.update();
        }
    }

    /**
     * The update function is responsible for updating the layout of the image slider.
     * It sets the gap between the slides, calculates the width of each slide,
     * and applies the necessary transformations to the slides container.
     * 
     * @returns {Promise} A Promise that resolves once the layout of the image slider has been updated.
     */
    update() {
        return new Promise((resolve) => {
            setTimeout(() => {
                CONFIG.winWidth = window.innerWidth;
                let visibleWidth = this.config.wrap.clientWidth;
                if (visibleWidth <= 0) {visibleWidth = 300;}
                this.config.slides.style.gap = `${this.config.space}px`;
                let calcWidth = (visibleWidth - (this.config.view - 1) * this.config.space) / this.config.view;
                [...this.config.slides.children].forEach(slide => slide.style.minWidth = slide.style.width = `${calcWidth}px`);
                let totalSlideWidth = calcWidth + this.config.space;
                let newTransform = - this.currentIndex * totalSlideWidth * (this.isRTL() ? -1 : 1);
                // 
                // console.log(visibleWidth, calcWidth, newTransform);
                // 
                this.config.slides.style.transform = `translateX(${newTransform}px)`;
                // 
                if ((this.currentIndex + this.config.view) >= this.totalSlides && this.config.loop && this.config.autoplay && this.autoplay) {
                    this.stopAutoplay();
                }
                // 
                resolve();
            }, 100);
        });
    }

    autoplay() {
        if (this.config.autoplay) {
            this.startAutoplay();
        }
    }
    
    /**
     * The startAutoplay function is responsible for initiating the autoplay feature of the image slider.
     * It sets an interval that triggers the next slide function at the specified speed.
     *
     * @returns {void} This function does not return any value. Its purpose is to start the autoplay feature of the image slider.
     */
    startAutoplay() {
        this.autoplay = setInterval(() => {
            this.next();
        }, this.config.speed);
    }

    /**
     * The stopAutoplay function is responsible for stopping the autoplay feature of the image slider.
     * It clears the interval that triggers the autoplay function and sets the autoplay property to false.
     *
     * @returns {void} This function does not return any value. Its purpose is to stop the autoplay feature of the image slider.
     */
    stopAutoplay() {
        clearInterval(this.autoplay);
        this.autoplay = false;
    }

    /**
     * The transition function is responsible for handling the transition effects of the image slider.
     * It updates the transition property of the slides container based on the provided parameters.
     *
     * @param {boolean} [stop=false] - A boolean parameter that determines whether the transition should be stopped immediately.
     * If true, the transition property will be set to 'unset', effectively stopping any ongoing transition.
     * If false (or not provided), the transition property will be set to 'transform <duration>s ease', where <duration> is the specified transition duration in seconds.
     *
     * @returns {Promise} A Promise that resolves once the transition property has been updated.
     */
    transition(stop = false) {
        return new Promise((resolve) => {
            if (this.config.transition === false) {stop = true;}
            setTimeout(() => {
                this.config.slides.style.transition = stop ? `unset` : `transform ${this.config.transition}s ease`;
                resolve();
            }, stop ? 1 : 100);
        });
    }

    /**
     * The `init_resize` function is responsible for handling the window resize event and updating the layout of the image slider accordingly.
     *
     * @param {Event} event - The event object representing the window resize event.
     */
    init_resize() {
        window.addEventListener('resize', (event) => this.update());
    }
    
    /**
     * The `init_drag` function is responsible for enabling the dragging feature of the image slider.
     * It sets up event listeners for mouse and touch events on the slides container, allowing users to interactively drag the slides.
     *
     * @returns {void} This function does not return any value. Its purpose is to enable the dragging feature of the image slider.
     */
    init_drag() {
        if (!this.config.drag) return;
    
        let isDragging = false;
        let startPos = 0;
        let currentTranslate = 0;
        let prevTranslate = 0;
    
        const touchStart = (event) => {
            if (!this.is_allowed_width()) return;
            isDragging = true;
            startPos = event.type.includes('mouse') ? event.pageX : event.touches[0].clientX;
            prevTranslate = this.currentIndex * -this.config.slides.children[0].offsetWidth;
            this.config.slides.style.transition = 'none';
            event.preventDefault();
        };
    
        const touchMove = (event) => {
            if (!this.is_allowed_width() || !isDragging) return;
            const currentPosition = event.type.includes('mouse') ? event.pageX : event.touches[0].clientX;
            currentTranslate = prevTranslate + (currentPosition - startPos);
            this.config.slides.style.transform = `translateX(${currentTranslate}px)`;
            event.preventDefault();
        };
    
        const touchEnd = () => {
            if (!this.is_allowed_width()) return;
            isDragging = false;
            const movedBy = currentTranslate - prevTranslate;
            const slideWidth = this.config.slides.children[0].offsetWidth + this.config.space;
            const movedSlides = Math.round(movedBy / slideWidth);
            const movedNegative = movedBy < 0;
    
            this.currentIndex -= movedSlides;
    
            if (this.config.loop) {
                if (this.currentIndex >= this.totalSlides) {
                    this.currentIndex = 0;
                } else if (this.currentIndex < 0) {
                    this.currentIndex = this.totalSlides - 1;
                }
            } else {
                if (this.currentIndex < 0) {
                    this.currentIndex = 0;
                } else if (this.currentIndex >= this.totalSlides - this.config.view + 1) {
                    this.currentIndex = this.totalSlides - this.config.view;
                }
            }
    
            this.config.slides.style.transition = `transform ${this.config.transition}s ease`;
            this.config.slides.style.transform = `translateX(${-this.currentIndex * slideWidth * (this.isRTL() ? -1 : 1)}px)`;
            this.update();
        };
    
        this.config.slides.addEventListener('mousedown', touchStart);
        this.config.slides.addEventListener('mousemove', touchMove);
        this.config.slides.addEventListener('mouseup', touchEnd);
        this.config.slides.addEventListener('mouseleave', touchEnd);
        this.config.slides.addEventListener('touchstart', touchStart);
        this.config.slides.addEventListener('touchmove', touchMove);
        this.config.slides.addEventListener('touchend', touchEnd);
    }
    
    init_drag__() {
        if (this.config.drag === false) {return;}
        let isDragging = false;
        let startPos = 0;
        let currentTranslate = 0;
        let prevTranslate = 0;

        const touchStart = (event) => {
            if (!this.is_allowed_width()) {return false;}
            isDragging = true;
            startPos = event.type.includes('mouse') ? event.pageX : event.touches[0].clientX;
            this.config.slides.style.transition = 'none';
            // return (event) => {};
        };

        const touchMove = (event) => {
            if (!this.is_allowed_width()) {return false;}
            if (isDragging === false) {return false;}
            const currentPosition = event.type.includes('mouse') ? event.pageX : event.touches[0].clientX;
            let movedBy = currentPosition - startPos;
            const isNegative = movedBy < 0;
            const toRight = !isNegative;
            // 
            // const movedSlides = Math.round(Math.abs(movedBy) / this.slide_width());
            // this.currentIndex += (isNegative)?-movedSlides:movedSlides;
            // 
            currentTranslate = prevTranslate + movedBy;
            // 
            // console.log({currentTranslate, prevTranslate, movedBy, toRight});
            // 
            if (toRight) {
                currentTranslate = Math.min((
                    this.slides_width() - (
                        this.slide_width_binded()
                    ) * this.config.view
                ), currentTranslate);
            } else {
                currentTranslate = Math.max(0, currentTranslate);
            }
            // Sortout current translate to current slide index.
            this.get_current_slide(currentTranslate);
            this.config.slides.style.transform = `translateX(${currentTranslate}px)`;
            // 
        };

        const touchEnd = (event) => {
            if (!this.is_allowed_width()) {return false;}
            isDragging = false;
            this.config.slides.style.transition = `transform ${this.config.transition}s ease`;
            prevTranslate = -this.currentIndex * this.slide_width_binded() * (this.isRTL() ? -1 : 1);
            this.update();
            // if (!this.config.dragFree) {}
            // if (this.allow_slides2visible()) {
            //     this.config.slides.style.transform = `translateX(${prevTranslate}px)`;
            // }
            // this.update();
            // 
            // const movedBy = currentTranslate - prevTranslate;
            // const isNegative = movedBy < 0;
            // const toRight = !isNegative;
            // // 
            // console.log({
            //     movedBy: movedBy,
            //     isNegative: isNegative,
            //     currentIndex: this.currentIndex,
            //     totalSlides: this.totalSlides
            // });
            // // 
            // if (!this.config.dragFree) {
            //     this.config.slides.style.transition = `transform ${this.config.transition}s ease`;
            //     prevTranslate = -this.currentIndex * (slideWidth + this.config.space) * (this.isRTL() ? -1 : 1);
            //     if (this.allow_slides2visible()) {
            //         this.config.slides.style.transform = `translateX(${prevTranslate}px)`;
            //     }
            //     this.update();
            // }
        };
        // 
        this.config.slides.addEventListener('mousedown', touchStart);
        this.config.slides.addEventListener('mousemove', touchMove);
        this.config.slides.addEventListener('mouseup', touchEnd);
        this.config.slides.addEventListener('mouseleave', touchEnd);
        this.config.slides.addEventListener('touchstart', touchStart);
        this.config.slides.addEventListener('touchmove', touchMove);
        this.config.slides.addEventListener('touchend', touchEnd);
    }
    is_allowed_width() {
        CONFIG.winWidth = window.innerWidth;
        return CONFIG.winWidth <= CONFIG.mobileWidth;
    }

    slide_width_binded() {
        return this.slide_width() + this.config.space;
    }
    slide_width() {
        return this.config.slides.children[0]?.offsetWidth??100;
    }
    slides_width() {
        return (
            this.totalSlides * this.slide_width_binded()
        );
    }

    allow_slides2visible() {
        return (this.currentIndex + this.config.view) <= this.totalSlides;
    }

    get_current_slide(translate = false) {
        let slideIndex = this.currentIndex;
        if (translate) {
            this.currentIndex = slideIndex = Math.round(translate / this.slide_width_binded());
        }
        return slideIndex;
    }

    /**
     * This function checks if the container's direction is right-to-left (RTL).
     * It uses the `getComputedStyle` method to retrieve the direction property of the container and compares it to 'rtl'.
     *
     * @returns {boolean} - A boolean value indicating whether the container's direction is right-to-left (true) or left-to-right (false).
     */
    isRTL() {
        return getComputedStyle(this.config.container).direction === 'rtl';
    }
    issues() {
        /**
         * Issue arrives when the container made through dom create element object and has not visible yet on screen.
         * 
         * @solution is to put interval event to acknowledge whenever it's in screen and get screen info and then update container
         */
        if (this.config.container.clientWidth <= 0) {
            let interval4Width = setInterval(() => {
                if (this.config.container.clientWidth >= 1) {
                    this.config.container.classList.remove('zero-width');
                    this.update();clearInterval(interval4Width);
                } else {
                    if (this.config.container.classList.contains('zero-width') == false) {
                        this.config.container.classList.add('zero-width');
                    }
                }
            }, 1000);
        }
    }
}
// 
export default Slider;