const tasks = {};

export default class AppState {
    static addTask(forWho, task) {
        if (!tasks[forWho]) {
            tasks[forWho] = [];
        }
        tasks[forWho].push(task);
    }

    static shiftTask(whoIsAsking) {
        const myTasks = tasks[whoIsAsking] || [];
        return myTasks.shift();
    }
}
