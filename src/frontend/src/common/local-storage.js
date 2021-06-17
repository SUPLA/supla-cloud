export class LocalStorageWithMemoryFallback {
    constructor() {
        try {
            localStorage.setItem('test', 'test');
            localStorage.removeItem('test');
            this.storage = new LocalStorageStorage();
        } catch (e) {
            this.storage = new MemoryStorage();
        }
    }

    get(key) {
        return this.storage.get(key);
    }

    set(key, value) {
        this.storage.set(key, value);
    }

    remove(key) {
        this.storage.remove(key);
    }
}

class LocalStorageStorage {
    get(key) {
        return localStorage.getItem(key);
    }

    set(key, value) {
        localStorage.setItem(key, value);
    }

    remove(key) {
        localStorage.removeItem(key);
    }
}

class MemoryStorage {
    constructor() {
        this.data = {};
    }

    get(key) {
        return this.data[key];
    }

    set(key, value) {
        this.data[key] = value;
    }

    remove(key) {
        delete this.data[key];
    }
}
