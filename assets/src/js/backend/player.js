/**
 * Webserfer JS Player
 * 
 * Copyright @mahmudremal
 * 
 * Licensed under MIT
 */

class Player {
    constructor(container) {
        this.config = {
            container: container
        };
        this.setup();
    }
    setup() {
        this.player = new WebflowPlayer(this.config.container);
    }
}