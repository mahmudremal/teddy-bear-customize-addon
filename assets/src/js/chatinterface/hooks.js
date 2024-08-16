/**
 * Javascript hooks inspired from wordpress hooks
 * 
 * @param {string} [o] Hook tag string
 * @param {int} [t] Priority of the function
 */

class Hooks {
    constructor() {
        this.hooks = {action: {}, filter: {}};
    }
    add_action( action, callable, priority, tag ) {
        this.addHook( 'action', action, callable, priority, tag );
    }
    add_filter( action, callable, priority, tag ) {
        this.addHook( 'filter', action, callable, priority, tag );
    }
    do_action( action ) {
        this.doHook( 'action', action, arguments );
    }
    apply_filters( action ) {
        return this.doHook( 'filter', action, arguments );
    }
    remove_action( action, tag ) {
        this.removeHook( 'action', action, tag );
    }
    remove_filter( action, priority, tag ) {
        this.removeHook( 'filter', action, priority, tag );
    }
    addHook( hookType, action, callable, priority, tag ) {
        if ( undefined == this.hooks[hookType][action] ) {
            this.hooks[hookType][action] = [];
        }
        var hooks = this.hooks[hookType][action];
        if ( undefined == tag ) {
            tag = action + '_' + hooks.length;
        }
        if( priority == undefined ){
            priority = 10;
        }

        this.hooks[hookType][action].push( { tag:tag, callable:callable, priority:priority });
    }
    doHook( hookType, action, args ) {

        // splice args from object into array and remove first index which is the hook name
        args = Array.prototype.slice.call(args, 1);

        if ( undefined != this.hooks[hookType][action] ) {
            var hooks = this.hooks[hookType][action], hook;
            //sort by priority
            hooks.sort(function(a,b){return a["priority"]-b["priority"]});

            hooks.forEach(hookItem => {
                if (typeof hookItem?.callable === 'object' && hookItem.callable[1] && hookItem.callable[1] in hookItem.callable[0]) {
                    hookItem.callable = hookItem.callable[0][hookItem.callable[1]];
                }
                hook = hookItem.callable;

                if(typeof hook != 'function')
                    hook = window[hook];
                if ( 'action' == hookType ) {
                    hook.apply(null, args);
                } else {
                    args[0] = hook.apply(null, args);
                }
            });
        }
        if ( 'filter'==hookType ) {
            return args[0];
        }
    }
    removeHook( hookType, action, priority, tag ) {
        if ( undefined != this.hooks[hookType][action] ) {
            var hooks = this.hooks[hookType][action];
            hooks = hooks.filter( function(hook, index, arr) {
                var removeHook = (undefined==tag||tag==hook.tag) && (undefined==priority||priority==hook.priority);
                return !removeHook;
            });
            this.hooks[hookType][action] = hooks;
        }
    }

}
export default Hooks;