class governanceTooltip {
    /**
     * Instantiate our tooltip element
     * @param el the element that contains the tooltip
     */
    constructor(el) {
        this.container = el;
        this.opener = el.querySelector('[aria-describedby]');
        this.content = el.querySelector('[role="tooltip"]');
        this.closer = this.content.querySelector('button[rel="' + this.content.getAttribute('id') + '"]');

        this.closeTooltip = this.closeTooltip.bind(this);
        this.toggleTooltip = this.toggleTooltip.bind(this);
        this.escTooltip = this.escTooltip.bind(this);
        this.focusTrapEvent = this.focusTrapEvent.bind(this);

        this.init();
    }

    init() {
        this.opener.addEventListener('click', this.toggleTooltip);
        this.closer.addEventListener('click', this.closeTooltip);
    }

    toggleTooltip(e) {
        e.preventDefault();

        if (this.content.classList.contains('open')) {
            this.closeTooltip();
        } else {
            this.openTooltip();
        }

        return false;
    }

    openTooltip() {
        this.trapFocus(this.content);
        this.content.classList.add('open');
        window.addEventListener('keydown',this.escTooltip);
    }

    closeTooltip(e) {
        e.preventDefault();

        this.untrapFocus(this.content);
        window.removeEventListener('keydown',this.escTooltip);
        this.content.classList.remove('open');
        this.opener.focus();

        return false;
    }

    escTooltip(e) {
        if ( ! this.content.classList.contains('open') ) {
            return;
        }

        switch (e.key) {
            case 'Esc':
            case 'Escape' :
                this.closer.click();
                break;
            default :
                return;
        }
    }

    focusTrapEvent(e, options) {
        const [focusableEls, firstFocusableEl, lastFocusableEl, KEYCODE_TAB] = options;

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
    }

    trapFocus(element) {
        var focusableEls = element.querySelectorAll('a[href]:not([disabled]), button:not([disabled]), textarea:not([disabled]), input[type="text"]:not([disabled]), input[type="radio"]:not([disabled]), input[type="checkbox"]:not([disabled]), select:not([disabled])');
        var firstFocusableEl = focusableEls[0];
        var lastFocusableEl = focusableEls[focusableEls.length - 1];
        var KEYCODE_TAB = 9;

        firstFocusableEl.focus();

        element.addEventListener('keydown', (e) => {
            this.focusTrapEvent(e, [focusableEls, firstFocusableEl, lastFocusableEl, KEYCODE_TAB]);
        });
    }

    untrapFocus(element) {
        var focusableEls = element.querySelectorAll('a[href]:not([disabled]), button:not([disabled]), textarea:not([disabled]), input[type="text"]:not([disabled]), input[type="radio"]:not([disabled]), input[type="checkbox"]:not([disabled]), select:not([disabled])');
        var firstFocusableEl = focusableEls[0];
        var lastFocusableEl = focusableEls[focusableEls.length - 1];
        var KEYCODE_TAB = 9;

        element.removeEventListener('keydown', (e) => {
            this.focusTrapEvent(e, [focusableEls, firstFocusableEl, lastFocusableEl, KEYCODE_TAB]);
        });
    }
}

export default governanceTooltip;