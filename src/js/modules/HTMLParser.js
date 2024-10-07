class HTMLParser {
    constructor(html) {
        this.html = html;
        this.parser = new DOMParser();
    }

    parseHTML() {
        return this.parser.parseFromString(this.html,'text/html');
    }
}

export default HTMLParser;