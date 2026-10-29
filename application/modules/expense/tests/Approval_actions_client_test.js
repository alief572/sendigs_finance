const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.resolve(__dirname, '../../../..');
const controller = fs.readFileSync(path.join(root, 'application/modules/expense/controllers/Expense.php'), 'utf8');
let checks = 0;
for (const [view, endpoint] of [
    ['index_approval.php', 'get_expense_app_finance'],
    ['index_approval_manage.php', 'get_dat_approval_expense_manage'],
]) {
    const html = fs.readFileSync(path.join(root, 'application/modules/expense/views', view), 'utf8');
    const script = html.match(/<script type="text\/javascript">([\s\S]*?)<\/script>/)[1];
    const body = controller.split('public function ' + endpoint + '(')[1].split('\n\tpublic function ')[0];
    const calls = [];
    const element = {
        ready() {}, hide() { return this; }, show() { return this; },
        load(url) { calls.push(url); return this; },
    };
    const context = vm.createContext({ siteurl: '/app/', document: {}, $: () => element });
    vm.runInContext(script, context);
    vm.runInContext(fs.readFileSync(path.join(root, 'assets/js/basic.js'), 'utf8'), context);

    for (const collision of [false, true]) {
        if (collision) {
            vm.runInContext(`
                function data_approve() { throw new Error('List called form submit handler'); }
                function data_view() { throw new Error('List called shared global handler'); }
            `, context);
        }
        for (const [title, route] of [['View', 'view'], ['Approve', 'approval']]) {
            const handler = body.match(new RegExp('title="' + title + '" onclick="(\\w+)\\('))[1];
            calls.length = 0;
            context[handler]('42');
            assert.deepEqual(calls, ['/app/expense/' + route + '/42'], `${view}: ${title}, collision=${collision}`);
            checks++;
            calls.length = 0;
            context[handler]('');
            assert.deepEqual(calls, [], `${view}: empty id must not load a form`);
            checks++;
        }
    }
}
console.log(`PASS Expense approval action routing: ${checks} checks`);
