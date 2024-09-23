class wysiwygSort {
    constructor(container) {
        if ( typeof tinymce === "undefined" ) {
            console.log( 'TinyMCE does not appear to be active on this page' );
            return;
        }

        this.container = document.querySelector(container);
        this.editors = container.querySelectorAll('.mce-container-body');

        this.sortStart = this.removeEditors.bind(this);
        this.sortEnd = this.addEditors.bind(this);

        this.addEventListeners();
    }

    addEventListeners() {
        this.container.addEventListener('sortstart',this.sortStart);
        this.container.addEventListener('sortstop',this.sortEnd);
    }

    removeEventListeners() {
        this.container.removeEventListener('sortstart',this.sortStart);
        this.container.removeEventListener('sortstop',this.sortEnd);
    }

    removeEditors() {
        this.editors.forEach((ed) => {
            if ( ! ed.isHidden() ) {
                ed.save();
            }

            tinymce.remove( ed );
            tinymce.init( tinyMCEPreInit.mceInit[ this.id ] );
        });
    }

    addEditors() {

    }
}