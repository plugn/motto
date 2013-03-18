/* jsoopii.js
 * @title  JavaScript OOP Inheritance Implementation
 * @author (c)Joshua Gertzen, 2006/08/15 at http://truecode.blogspot.com/2006/08/object-oriented-super-class-method.html
 */

// Defines the top level Class
function Class() { }
Class.prototype.construct = function() {};
Class.extend = function(def) {
    var classDef = function() {
        if (arguments[0] !== Class) { this.construct.apply(this, arguments); }
    };

    var proto = new this(Class);
    var superClass = this.prototype;

    for (var n in def) {
        var item = def[n];
        if (item instanceof Function) item.$ = superClass;
        proto[n] = item;
    }

    classDef.prototype = proto;

    //Give this new class the same static extend method
    classDef.extend = this.extend;
    return classDef;
};
