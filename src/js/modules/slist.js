/**
 * Adapted from CodeBoxx sample
 * @see https://code-boxx.com/drag-drop-sortable-list-javascript/#:~:text=In%20the%20simplest%20design%2C%20drag-and-drop%20in%20HTML%20and,%28%22drop%22%29.ondrop%20%3D%20%28%29%20%3D%3E%20%7B%20DO%20SOMETHING%20%7D%3B
 */
class sortableList {
    /**
     * Instantiate our slist
     * @param el the element that contains the sortable list
     */
    constructor(el) {
        this.container = el;
        this.slist();
    }

    /**
     * Build the sortable list
     * @param target the element that contains the sortable list
     */
    slist () {
        const target = this.container;

        // (A) SET CSS + GET ALL LIST ITEMS
        target.classList.add("slist");
        let items = target.getElementsByTagName("li"), current = null;

        // (B) MAKE ITEMS DRAGGABLE + SORTABLE
        for (let i of items) {
            // (B1) ATTACH DRAGGABLE
            i.draggable = true;

            // (B2) DRAG START - YELLOW HIGHLIGHT DROPZONES
            i.ondragstart = e => {
                current = i;
                for (let it of items) {
                    if (it != current) { it.classList.add("hint"); }
                }
            };

            // (B3) DRAG ENTER - RED HIGHLIGHT DROPZONE
            i.ondragenter = e => {
                if (i != current) { i.classList.add("active"); }
            };

            // (B4) DRAG LEAVE - REMOVE RED HIGHLIGHT
            i.ondragleave = () => i.classList.remove("active");

            // (B5) DRAG END - REMOVE ALL HIGHLIGHTS
            i.ondragend = () => { for (let it of items) {
                it.classList.remove("hint");
                it.classList.remove("active");
            }};

            // (B6) DRAG OVER - PREVENT THE DEFAULT "DROP", SO WE CAN DO OUR OWN
            i.ondragover = e => e.preventDefault();

            // (B7) ON DROP - DO SOMETHING
            i.ondrop = e => {
                e.preventDefault();
                if (i != current) {
                    let currentpos = 0, droppedpos = 0;
                    for (let it=0; it<items.length; it++) {
                        if (current == items[it]) { currentpos = it; }
                        if (i == items[it]) { droppedpos = it; }
                    }
                    if (currentpos < droppedpos) {
                        i.parentNode.insertBefore(current, i.nextSibling);
                    } else {
                        i.parentNode.insertBefore(current, i);
                    }
                }
            };
        }
    }
}

export default sortableList;