export class ConflictError extends Error {
    constructor(message) {
        super();
        this.message = message;
    }
}
