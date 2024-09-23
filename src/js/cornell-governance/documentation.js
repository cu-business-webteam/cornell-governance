import imageLightbox from "../modules/imageLightbox";

class governanceDocs {
    constructor() {
        this.gallery = new imageLightbox({
            selectors: {
                parent: 'ol:has(li > a > img + em)',
                captions: 'em',
            }
        });
    }
}

export default governanceDocs;