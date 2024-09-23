class imageLightbox {
    /**
     * Construct our lightbox module
     *
     * @param {} args an object containing the following settings:
     *      * selectors:
     *          * parent - a CSS selector pointing to the gallery container elements
     *          * links - a CSS selector pointing to the link elements inside the galleries
     *          * images - a CSS selector pointing to the images found within the galleries
     *          * captions - a CSS selector pointing to the captions found within the galleries
     *      * container - string - a CSS class to assign to the lightbox container, itself
     *      * gallery - bool (true) - whether to allow sliding between images within the lightbox
     *      * caption - bool (true) - whether to include the image caption in the lightbox
     */
    constructor( args ) {
        this.args = args;
        this.registerSettings();

        this.galleries = document.querySelectorAll( this.selectors.parent );
        if ( this.galleries.length <= 0 ) {
            return;
        }

        this.initLightbox = this.init.bind(this);
        this.addTrigger = this.addTrigger.bind(this);
        this.openLightbox = this.openLightbox.bind(this);
        this.closeLightbox = this.closeLightbox.bind(this);

        this.galleries.forEach( ( gallery ) => {
            this.initLightbox( gallery );
        } );
    }

    registerSettings() {
        if ( this.args.hasOwnProperty( 'selectors' ) ) {
            this.selectors = this.args.selectors;

            if ( ! this.selectors.hasOwnProperty( 'parent' ) ) {
                this.selectors.parent = '.gallery';
            }

            if ( ! this.selectors.hasOwnProperty( 'images' ) ) {
                this.selectors.images = 'img';
            }

            if ( ! this.selectors.hasOwnProperty( 'links' ) ) {
                this.selectors.links = 'a';
            }

            if ( ! this.selectors.hasOwnProperty( 'captions' ) ) {
                this.selectors.captions = 'figcaption';
            }
        }

        if ( this.args.hasOwnProperty( 'container' ) ) {
            this.containerClass = this.args.container;
        } else {
            this.containerClass = 'lightbox-container';
        }

        if ( this.args.hasOwnProperty( 'gallery' ) ) {
            this.useGallery = this.args.gallery;
        } else {
            this.useGallery = true;
        }

        if ( this.args.hasOwnProperty( 'caption' ) ) {
            this.useCaptions = this.args.caption;
        } else {
            this.useCaptions = true;
        }
    }

    init( gallery ) {
        const images = gallery.querySelectorAll( this.selectors.images );

        const links = gallery.querySelectorAll(this.selectors.links);
        console.log( links );

        const captions = gallery.querySelectorAll(this.selectors.captions);
        links.forEach( (link) => {
            this.addTrigger(link);
        });

        this.createLightbox();

        window.addEventListener('keydown', this.escLightbox.bind(this));
    }

    addTrigger(link) {
        link.setAttribute('aria-label','Open the full-sized image in a lightbox');
        link.addEventListener( 'click', this.openLightbox );
    }

    openLightbox(e) {
        e.preventDefault();

        let link = e.target;

        if ( e.target.tagName.toLowerCase() === this.selectors.images || e.target.tagName.toLowerCase() === this.selectors.captions ) {
            link = e.target.closest(this.selectors.links);
        }

        const caption = link.querySelector(this.selectors.captions);
        console.log(link);
        console.log(caption);

        this.opener = link;

        this.lightboxImage.setAttribute('src',link.getAttribute('href'));
        this.lightboxCaption.innerHTML = caption.innerHTML;

        this.lightbox.classList.remove('hidden');
        this.lightbox.classList.add('open');
        this.lightbox.setAttribute('aria-hidden','false');

        this.trapFocus(this.lightbox);

        return false;
    }

    closeLightbox(e) {
        this.lightbox.classList.add('hidden');
        this.lightbox.classList.add('open');
        this.lightbox.setAttribute('aria-hidden','true');

        this.opener.focus();
    }

    escLightbox(e) {
        if (this.lightbox.classList.contains('hidden')) {
            return;
        }

        switch (e.key) {
            case 'Esc':
            case 'Escape' :
                this.lightboxClose.click();
                break;
            default :
                return;
        }
    }

    createLightbox() {
        if ( document.querySelectorAll( this.containerClass ).length >= 1 ) {
            return;
        }

        this.lightbox = document.createElement( 'div' );
        this.lightbox.classList.add(this.containerClass);
        this.lightbox.classList.add('cornell-governance-lightbox');
        this.lightbox.classList.add('hidden');
        this.lightbox.setAttribute('role','dialog');
        this.lightbox.setAttribute('aria-describedby','lightbox-caption');
        this.lightbox.setAttribute('aria-hidden','true');

        this.lightboxClose = document.createElement('button');
        this.lightboxClose.innerText = 'Close';
        this.lightboxClose.addEventListener('click',this.closeLightbox);

        this.lightboxInner = document.createElement('div');
        this.lightboxInner.classList.add('lightbox');


        this.lightboxFigure = document.createElement('figure');
        this.lightboxImage = document.createElement('img');
        this.lightboxCaption = document.createElement('figcaption');
        this.lightboxCaption.id = 'lightbox-caption';
        if ( ! this.useCaptions ) {
            this.lightboxCaption.classList.add('screen-reader-text');
        }

        this.lightboxFigure.append(this.lightboxImage);
        this.lightboxFigure.append(this.lightboxCaption);

        this.lightboxInner.append(this.lightboxClose);
        this.lightboxInner.append(this.lightboxFigure);

        this.lightbox.append(this.lightboxInner);

        document.querySelector('body').append(this.lightbox);
    }

    trapFocus(element) {
        var focusableEls = element.querySelectorAll('a[href]:not([disabled]), button:not([disabled]), textarea:not([disabled]), input[type="text"]:not([disabled]), input[type="radio"]:not([disabled]), input[type="checkbox"]:not([disabled]), select:not([disabled])');
        var firstFocusableEl = focusableEls[0];
        var lastFocusableEl = focusableEls[focusableEls.length - 1];
        var KEYCODE_TAB = 9;

        element.addEventListener('keydown', function (e) {
            var isTabPressed = (e.key === 'Tab' || e.keyCode === KEYCODE_TAB);

            if (!isTabPressed) {
                return;
            }

            if (e.shiftKey) /* shift + tab */ {
                if (document.activeElement === firstFocusableEl) {
                    lastFocusableEl.focus();
                    e.preventDefault();
                }
            } else /* tab */ {
                if (document.activeElement === lastFocusableEl) {
                    firstFocusableEl.focus();
                    e.preventDefault();
                }
            }
        });
    }
}

export default imageLightbox;