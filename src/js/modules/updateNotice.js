class updateNotice {
    constructor() {
    }

    setOuterContainer(el) {
        this.outerContainer = el;
    }

    createNotice() {
        this.noticeContainer = document.createElement('div');
        this.noticeContainer.classList.add('cornell-governance-notice', 'updated');
        const noticeText = document.createElement('p');
        noticeText.innerText = this.message;
        this.noticeContainer.appendChild(noticeText);
    }

    insertNotice(message) {
        this.message = message;
        this.createNotice();
        if (this.outerContainer) {
            this.outerContainer.appendChild(this.noticeContainer);
        } else {
            document.querySelector('body').appendChild(this.noticeContainer);
        }
        // We also need to update the CSS timing
        this.t = setTimeout(this.removeNotice.bind(this), 7000);
    }

    removeNotice() {
        document.querySelector('.cornell-governance-notice').remove();
    }
}

export default updateNotice;