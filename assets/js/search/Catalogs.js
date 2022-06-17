export default class{

    constructor() {
        this.search_api_suggests_url = process.env.APP_MAIN_URL + '/api/v1/search/highlight';

        this.search_input = document.querySelector('.search-input');
        this.suggestions_container = document.querySelector('.suggestions');

        this.suggestDivClass = 'suggest-div';
        this.suggestItemClass = 'suggest-item';
        this.HighlightedSuggestItemClass = 'suggest-item-highlighted';

        this.search_input.addEventListener('input', this.debounce(this.showSuggestions, 200) );

        document.addEventListener('click', (event) => {

            const target = event.target;
            if (target.classList.contains(this.suggestItemClass)) {
                event.preventDefault();
                this.handleSuggestionSelect(target)
            }
            if (target.classList.contains(this.HighlightedSuggestItemClass)) {
                event.preventDefault();
                this.handleHighlightedSuggestionSelect(target)
            }
        });
    }

    async showSuggestions() {

        this.clearSuggests();

        const search_value = this.search_input.value;
        const suggests_response = await this.getSuggests( search_value );
        const suggests = JSON.parse(await suggests_response.text());

        this.clearSuggests();

        if (suggests.length !== 0) {
            suggests.forEach( suggest => {
                let suggest_div = document.createElement("div");
                let suggest_span = document.createElement("span");
                let suggest_highlight = document.createElement("b");

                suggest_div.classList.add(this.suggestDivClass);
                suggest_span.classList.add(this.suggestItemClass);
                suggest_highlight.classList.add(this.HighlightedSuggestItemClass);

                const split = suggest.split('<highlight>');

                suggest_highlight.innerText = split[1];
                suggest_span.innerText = split[0];

                suggest_span.addEventListener('click', function() {
                    document.getElementById('suggest').innerHTML = "";
                })

                suggest_span.appendChild(suggest_highlight);
                suggest_div.appendChild(suggest_span);

                this.suggestions_container.appendChild(suggest_div);
            });

            this.suggestions_container.style.display = 'block';
        }
    }

    clearSuggests(){
        this.suggestions_container.style.display = 'none';
        this.suggestions_container.replaceChildren();
    }

    handleSuggestionSelect(suggestionElement) {
        this.search_input.value = suggestionElement.innerText;
    }

    handleHighlightedSuggestionSelect(suggestionElement) {
        this.search_input.value = suggestionElement.parentElement.innerText;
    }

    async getSuggests(text) {
        return await fetch(this.search_api_suggests_url, {
            method: 'POST',
            cache: 'no-cache',
            redirect: 'follow',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                'search': text
            })
        })
    }

    debounce(func, wait = 500) {
        let timer;

        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, args), wait)
        }
    }

}